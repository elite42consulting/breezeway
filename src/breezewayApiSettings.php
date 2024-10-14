<?php

namespace elite42\breezeway;


class breezewayApiSettings {

	/**
	 * @param string $url
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param bool   $enableCaching
	 * @param string $cachePath
	 * @param bool   $debugLogging
	 * @param string $debugLogPath
	 *
	 * @throws \elite42\breezeway\breezewayException
	 */
	public function __construct(
		private string          $url,
		private readonly string $clientId,
		private readonly string $clientSecret,
		private readonly bool   $enableCaching = false,
		private readonly string $cachePath     = '',
		private readonly bool   $debugLogging  = false,
		private readonly string $debugLogPath  = '',
	) {
		if( empty( trim( $url, ' /' ) ) ) {
			throw new breezewayException( 'URL required' );
		}
		if( empty( trim( $clientId ) ) ) {
			throw new breezewayException( 'API client_id required' );
		}
		if( empty( trim( $clientSecret ) ) ) {
			throw new breezewayException( 'API client_secret required' );
		}

		if( $enableCaching && empty( trim( $cachePath ) ) ) {
			throw new breezewayException( 'Cache path is required' );
		}

		$this->url    = rtrim( $url, '/' );
	}

	public function getUrl() : string {
		return $this->url;
	}

	public function getClientId() : string {
		return $this->clientId;
	}

	public function getClientSecret() : string {
		return $this->clientSecret;
	}

	public function isEnableCaching() : bool {
		return $this->enableCaching;
	}

	public function getCachePath() : string {
		return $this->cachePath;
	}

	public function isDebugLogging() : bool {
		return $this->debugLogging;
	}

	public function getDebugLogPath() : string {
		return $this->debugLogPath;
	}

}
