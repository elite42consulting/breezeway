<?php

namespace elite42\breezeway\types\request;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class createTask extends jsonDeserialize {

	public ?int    $home_id               = null;
	public ?int    $reference_property_id = null;
	public string  $name                  = '';
	public ?string $type_department       = '';
	public ?string $type_priority         = '';
	public ?string $description           = '';
	public ?int    $template_id           = null;
	public ?string $scheduled_date        = '';
	public ?string $scheduled_time        = '';

	/** @var int[] $assignments */
	public array $assignments = [];

	/** @var int[] $tags */
	public array $tags = [];

	public ?int    $subdepartment_id = null;
	public ?float  $rate_paid        = null;
	public ?string $rate_type        = null;
	public ?string $requested_by     = null;

	public function __toQueryParams(): array {
		$params = [];
		foreach($this as $key=>$value){
			if(!empty($value)) {
				if($key=='home_id') {
					$value = (string)$value;
				}
				if($key=='reference_property_id') {
					$value = (string)$value;
				}
				$params[ $key ] = $value;
			}
		}
		return $params;
	}

}
