<?php

namespace elite42\breezeway\types;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class unit extends jsonDeserialize {

	public ?string $address1  = null;
	public ?string $address2  = null;
	public ?string $city      = null;
	public ?int    $companyId = null;
	public ?string $country   = null;
	public ?string $display   = null;

	/** @var array $notes */
	public array   $groups    = [];
	public ?int    $id        = null;
	public ?float  $latitude  = null;
	public ?float  $longitude = null;
	public ?string $name      = null;

	/** @var array $notes */
	public array $notes = [];

	/** @var \elite42\breezeway\types\unit\photo[] $photos */
	public array   $photos                         = [];
	public ?string $reference_company_id           = null;
	public ?string $reference_external_property_id = null;
	public ?string $state                          = null;
	public ?string $status                         = null;
	public ?string $wifi_name                      = null;
	public ?string $wifi_password                  = null;
	public ?string $zipcode                        = null;

}
