<?php
namespace elite42\breezeway;

use andrewsauder\jsonDeserialize\exceptions\jsonDeserializeException;
use GuzzleHttp\Exception\GuzzleException;

class breezewayApi {

	private string $jwtAccessToken = '';
	private string $jwtRefreshToken = '';

	private breezewayApiSettings $settings;

	private \Monolog\Logger $logger;

	private breezewayApiCache $cache;


	public function __construct( breezewayApiSettings $settings ) {
		$this->settings = $settings;

		//log setup
		if( $settings->isDebugLogging() ) {
			$logChannel = 'elite42.breezeway';

			// Create the logger
			$this->logger = new \Monolog\Logger( $logChannel );

			// save the log entries to a file
			$this->logger->pushHandler( new \Monolog\Handler\StreamHandler( trim( $settings->getDebugLogPath(), '/\\' ) . '/' . $logChannel . '.log', \Monolog\Logger::DEBUG ) );

			//enable debugging on json deserialize
			\andrewsauder\jsonDeserialize\config::setDebugLogPath( $settings->getDebugLogPath() );
			\andrewsauder\jsonDeserialize\config::setDebugLogging( true );
			\andrewsauder\jsonDeserialize\config::setLogJsonMissingProperty( false );
		}

		if( $settings->isEnableCaching() ) {
			$this->cache = new breezewayApiCache( $settings->getCachePath() );
		}
	}


	private function buildUrl( string $url, array $queryParams = [] ): string {
		$finalUrl = $url;

		if( count( $queryParams )>0 ) {
			$appendJoiner = '?';
			if( str_contains( $url, '?' ) ) {
				$appendJoiner = '&';
			}

			$finalUrl .= $appendJoiner . http_build_query( $queryParams );
		}

		return $finalUrl;
	}


	/**
	 * Perform a single API call
	 *
	 * @param string   $httpMethod HTTP Method to use
	 * @param string   $apiUrl     Ex: /pms/units?sortColumn=name&sortDirection=asc
	 * @param string[] $params     [optional] Associative array of parameters to pass. DO NOT INCLUDE TOKENS!
	 * @param int      $_attempt
	 *
	 * @return mixed
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function call( string $httpMethod, string $apiUrl, array $params = [], int $_attempt = 1 ): mixed {
		if( str_starts_with( $apiUrl, 'http' ) ) {
			$callUrl = $apiUrl;
		}
		else {
			$callUrl = $this->settings->getUrl() . $apiUrl;
		}

		//check the runtime cache and return its value if not null
		if( $this->settings->isEnableCaching() && strtoupper( $httpMethod )=='GET' ) {
			$cacheResponse = $this->cache->get( 'breezeway', $callUrl, $params );
			if( $cacheResponse!==null ) {
				if( $this->settings->isDebugLogging() ) {
					$this->logger->debug( $httpMethod . ' [cached]: ' . $apiUrl, $params );
				}

				return $cacheResponse;
			}
		}

		if( $this->settings->isDebugLogging() ) {
			$this->logger->debug( $httpMethod . ': ' . $apiUrl, $params );
		}

		$client = new \GuzzleHttp\Client();

		$jwtToken = $this->getBreezewayAccessToken();

		$options = [
			'headers' => [
				'Accept' => 'application/json',
				'Authorization'=>'JWT ' . $jwtToken,
			]
		];

		if( count( $params )>0 ) {
			if( strtoupper( $httpMethod )==='GET' ) {
				$options[ 'query' ] = $params;
			}
			elseif( strtoupper( $httpMethod )==='POST' ) {
				$options[ 'json' ] = $params;
			}
			elseif( strtoupper( $httpMethod )==='PUT' ) {
				$options[ 'json' ] = $params;
			}
			elseif( strtoupper( $httpMethod )==='PATCH' ) {
				$options[ 'json' ] = $params;
			}
			elseif( strtoupper( $httpMethod )==='DELETE' ) {
				$options[ 'query' ] = $params;
			}
		}

		try {
			$response = $client->request( strtoupper( $httpMethod ), $callUrl, $options );

			$body = new \stdClass();
			if( $response->getStatusCode()==200 ) {
				$body = json_decode( $response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR );
			}

			//set api cache
			if( $this->settings->isEnableCaching() && strtoupper( $httpMethod )=='GET' ) {
				$this->logger->debug( 'Create cache ' . $httpMethod . ': ' . $callUrl, $params );
				$this->cache->set( 'breezeway', $callUrl, $params, $body );
			}

			return $body;
		}
		catch( GuzzleException|\JsonException $e ) {
			if( $_attempt<3 ) {
				$this->logger->error( $httpMethod . ' ' . $apiUrl . ' failed: ' . $e->getCode() . ' ' . $e->getMessage() );
				$this->logger->error( '--waiting 3 seconds and then sending request again' );
				sleep( 3 );
				return $this->call( $httpMethod, $apiUrl, $params, $_attempt + 1 );
			}
			throw new breezewayException( $e->getMessage(), $e->getCode(), $e );
		}
	}


	/**
	 * @throws \elite42\breezeway\breezewayException
	 */
	private function getBreezewayAccessToken(): string {
		if(!empty($this->jwtAccessToken)) {
			$this->logger->debug( 'Auth: use in memory JWT Access Token' );
			return $this->jwtAccessToken;
		}

		$callUrl = $this->settings->getUrl() . '/auth/v1/';

		//check if our cached version is still valid
		if(!isset($this->cache)) {
			$this->cache = new breezewayApiCache( $this->settings->getCachePath() );
		}
		$cachedResponse = $this->cache->get( 'breezeway', $callUrl, null, 39600 ); //cache for 11 hours - it expires after 12
		if($cachedResponse!=null) {
			$this->logger->debug( 'Auth: use file cached JWT Access Token' );
			$this->jwtAccessToken = $cachedResponse->access_token;
			$this->jwtRefreshToken = $cachedResponse->refresh_token;
			return $this->jwtAccessToken;
		}

		//get a new jwt
		$this->logger->debug( 'Auth: getting new JWT Access Token' );
		try {
			$client   = new \GuzzleHttp\Client();
			$options  = [
				'json' => [
					'client_id'     => $this->settings->getClientId(),
					'client_secret' => $this->settings->getClientSecret()
				]
			];
			$response = $client->request( 'POST', $callUrl, $options );

			$body = new \stdClass();
			if( $response->getStatusCode()==200 ) {
				$body = json_decode( $response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR );
				$this->cache->set( 'breezeway', $callUrl, null, $body );
			}
		}
		catch(\Exception|GuzzleException $e) {
			$this->logger->debug( 'Auth: failed to get new JWT Access Token' );
			throw new breezewayException( 'Failed to get new access and refresh token', 1000, $e );
		}

		$this->logger->debug( 'Auth: use new JWT Access Token' );

		$this->jwtAccessToken = $body->access_token;
		$this->jwtRefreshToken = $body->refresh_token;


		return $this->jwtAccessToken;
	}


	/**
	 * @throws \elite42\breezeway\breezewayException
	 */
	private function getBreezewayRefreshToken(): string {
		if(!empty($this->jwtRefreshToken)) {
			return $this->jwtRefreshToken;
		}

		$this->getBreezewayAccessToken();

		return $this->jwtRefreshToken;
	}

	/**
	 * Perform an API call that will follow top level paging 'next' links until all pages have been requested
	 * Returns an array where each value is an API response. Array is returned even if there is only one page of results
	 *
	 * @param string   $httpMethod   HTTP Method to use
	 * @param string   $apiUrl       Ex: /pms/units
	 * @param string[] $params       Associative array of parameters to pass as json or query params
	 * @param array    $apiResponses Used by the function for recursion - ignore
	 *
	 * @return \elite42\breezeway\types\pagedResponse[]
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function callAndFollowPaging( string $httpMethod, string $apiUrl, array $params = [], array $apiResponses = [] ): array {
		$apiResponse = $this->call( $httpMethod, $apiUrl, $params );

		$apiResponses[] = $apiResponse;

		if( isset( $apiResponse->page ) && $apiResponse->total_pages > $apiResponse->page ) {
			$params[ 'page' ] = $apiResponse->page + 1;
			return $this->callAndFollowPaging( $httpMethod, $apiUrl, $params, $apiResponses );
		}

		return $apiResponses;
	}


	private function getCacheResponse( string $method, string $url ) {
		if( $this->settings->isEnableCaching() ) {
			if( str_starts_with( $url, 'http' ) ) {
				$callUrl = $url;
			}
			else {
				$callUrl = $this->settings->getUrl() . $url;
			}

			$cacheResponse = $this->cache->get( 'breezeway', $method . $callUrl, [] );
			if( $cacheResponse!==null ) {
				if( $this->settings->isDebugLogging() ) {
					$this->logger->debug( $method . ' [cached]' . $callUrl );
				}
				return $cacheResponse;
			}
		}
		return null;
	}


	private function createCacheResponse( string $method, string $url, mixed $value ) {
		//set api cache
		if( $this->settings->isEnableCaching() ) {
			if( str_starts_with( $url, 'http' ) ) {
				$callUrl = $url;
			}
			else {
				$callUrl = $this->settings->getUrl() . $url;
			}

			$this->logger->debug( 'Create cache ' . $method . ': ' . $callUrl, [] );
			$this->cache->set( 'breezeway', $method . $callUrl, [], $value );
		}
	}


	/**
	 * @param string|int $unitId
	 *
	 * @return \elite42\breezeway\types\unit
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function getUnit( string|int $unitId ): types\unit {
		$url = $this->buildUrl( '/inventory/v1/property/' . $unitId );

		$cacheResponse = $this->getCacheResponse( __METHOD__, $url );
		if( $cacheResponse!==null ) {
			return $cacheResponse;
		}

		$apiResponse = $this->call( 'GET', $url );

		try {
			$unit = types\unit::jsonDeserialize( $apiResponse );
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\unit', 500, $e );
		}

		$this->createCacheResponse( __METHOD__, $url, $unit );

		return $unit;

	}


	/**
	 * @param array $queryParams Key value pairs of breezeway api query params https://developer.breezewayhs.com/reference/getunits
	 *
	 * @return \elite42\breezeway\types\unit[]
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function getUnits( array $queryParams = [] ): array {
		$url = $this->buildUrl( '/inventory/v1/property', $queryParams );

		$cacheResponse = $this->getCacheResponse( __METHOD__, $url );
		if( $cacheResponse!==null ) {
			return $cacheResponse;
		}

		$apiResponses = $this->callAndFollowPaging( 'GET', $url );

		$units = [];
		try {
			foreach( $apiResponses as $apiResponse ) {
				if( isset( $apiResponse->results ) ) {
					foreach( $apiResponse->results as $unit ) {
						$units[] = types\unit::jsonDeserialize( $unit );
					}
				}
			}
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\unit', 500, $e );
		}

		$this->createCacheResponse( __METHOD__, $url, $units );

		return $units;
	}


	/**
	 * @param string|int $personId
	 *
	 * @return \elite42\breezeway\types\person
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function getPerson( string|int $personId ): types\person {
		$url = $this->buildUrl( '/inventory/v1/people/' . $personId );

		$cacheResponse = $this->getCacheResponse( __METHOD__, $url );
		if( $cacheResponse!==null ) {
			return $cacheResponse;
		}

		$apiResponse = $this->call( 'GET', $url );

		try {
			$person = types\person::jsonDeserialize( $apiResponse );
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\people', 500, $e );
		}

		$this->createCacheResponse( __METHOD__, $url, $person );

		return $person;

	}


	/**
	 * @param array $queryParams Key value pairs of breezeway api query params https://developer.breezewayhs.com/reference/getunits
	 *
	 * @return \elite42\breezeway\types\person[]
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function getPeople( array $queryParams = [] ): array {
		$url = $this->buildUrl( '/inventory/v1/people', $queryParams );

		$cacheResponse = $this->getCacheResponse( __METHOD__, $url );
		if( $cacheResponse!==null ) {
			return $cacheResponse;
		}

		$apiResponses = $this->callAndFollowPaging( 'GET', $url );

		$people = [];
		try {
			foreach( $apiResponses as $apiResponse ) {
				foreach( $apiResponse as $person ) {
					$people[] = types\person::jsonDeserialize( $person );
				}
			}
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\people', 500, $e );
		}

		$this->createCacheResponse( __METHOD__, $url, $people );

		return $people;
	}


	/**
	 * @param \elite42\breezeway\types\request\createTask $task
	 *
	 * @return \elite42\breezeway\types\task
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function createTask( types\request\createTask $task ): types\task {
		$url = $this->buildUrl( '/inventory/v1/task/' );

		$apiResponse = $this->call( 'POST', $url, $task->__toQueryParams() );

		try {
			$task = types\task::jsonDeserialize( $apiResponse );
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\people', 500, $e );
		}

		return $task;

	}


	/**
	 * @param int $taskId
	 *
	 * @return \elite42\breezeway\types\task
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function deleteTask( int $taskId ): mixed {
		$url = $this->buildUrl( '/inventory/v1/task/'.$taskId );

		$apiResponse = $this->call( 'DELETE', $url );

		return $apiResponse;

	}

	/**
	 * @param string|int $taskId
	 *
	 * @return \elite42\breezeway\types\task
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function getTask( string|int $taskId ): types\task {
		$url = $this->buildUrl( '/inventory/v1/task/' . $taskId );

		$cacheResponse = $this->getCacheResponse( __METHOD__, $url );
		if( $cacheResponse!==null ) {
			return $cacheResponse;
		}

		$apiResponse = $this->call( 'GET', $url );

		try {
			$task = types\task::jsonDeserialize( $apiResponse );
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\task', 500, $e );
		}

		$this->createCacheResponse( __METHOD__, $url, $task );

		return $task;

	}


	/**
	 * @param array $queryParams Key value pairs of breezeway api query params https://developer.breezewayhs.com/reference/gettasks
	 *
	 * @return \elite42\breezeway\types\task[]
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function getTasks( array $queryParams = [] ): array {
		$url = $this->buildUrl( '/inventory/v1/task/', $queryParams );

		$cacheResponse = $this->getCacheResponse( __METHOD__, $url );
		if( $cacheResponse!==null ) {
			return $cacheResponse;
		}

		$apiResponses = $this->callAndFollowPaging( 'GET', $url );

		$tasks = [];
		try {
			foreach( $apiResponses as $apiResponse ) {
				if( isset( $apiResponse->results ) ) {
					foreach( $apiResponse->results as $task ) {
						$tasks[] = types\task::jsonDeserialize( $task );
					}
				}
			}
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\task', 500, $e );
		}

		$this->createCacheResponse( __METHOD__, $url, $tasks );

		return $tasks;
	}


	/**
	 * @param int $taskId
	 *
	 * @return \elite42\breezeway\types\taskRequirement[]
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function getTaskRequirements( int $taskId ): array {
		$url = $this->buildUrl( '/inventory/v1/task/'.$taskId.'/requirements' );

		$cacheResponse = $this->getCacheResponse( __METHOD__, $url );
		if( $cacheResponse!==null ) {
			return $cacheResponse;
		}

		$apiResponses = $this->call( 'GET', $url );

		$taskRequirements = [];
		try {
			foreach( $apiResponses as $apiResponse ) {
				$taskRequirements[] = types\taskRequirement::jsonDeserialize( $apiResponse );
			}
		}
		catch( jsonDeserializeException $e ) {
			throw new breezewayException( 'Failed to convert JSON API response to \elite42\breezeway\types\taskRequirement', 500, $e );
		}

		$this->createCacheResponse( __METHOD__, $url, $taskRequirements );

		return $taskRequirements;
	}

}
