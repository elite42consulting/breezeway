<?php

namespace elite42\breezeway\types\person;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class group extends jsonDeserialize {

	public ?int    $id              = null;
	public ?string $name            = null;
	public ?int    $parent_group_id = null;

}
