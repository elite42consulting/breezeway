<?php

namespace elite42\breezeway\types\task\cost;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class costType extends jsonDeserialize {

	public ?string $code = null;
	public ?int    $id   = null;
	public ?string $name = null;

}
