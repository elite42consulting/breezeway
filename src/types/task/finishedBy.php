<?php

namespace elite42\breezeway\types\task;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class finishedBy extends jsonDeserialize {

	public ?int    $id   = null;
	public ?string $name = null;

}
