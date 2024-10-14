<?php

namespace elite42\breezeway\types\task;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class supply extends jsonDeserialize {

	public ?bool   $billable            = null;
	public ?string $description         = null;
	public ?int    $id                  = null;
	public ?string $markup_pricing_type = null;
	public ?float  $markup_rate         = null;
	public ?string $name                = null;
	public ?int    $quantity            = null;
	public ?string $size                = null;
	public ?int    $supply_id           = null;
	public ?float  $total_price         = null;
	public ?float  $unit_cost           = null;

}
