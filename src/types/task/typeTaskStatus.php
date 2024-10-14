<?php

namespace elite42\breezeway\types\task;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class typeTaskStatus extends jsonDeserialize {

	public ?string   $code            = null;
	public ?string   $name            = null;
	public ?string   $stage            = null;

}
