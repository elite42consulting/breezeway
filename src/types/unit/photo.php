<?php

namespace elite42\breezeway\types\unit;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class photo extends jsonDeserialize {

	public ?string $caption      = '';
	public ?bool   $default      = false;
	public ?string $id           = null;
	public ?string $original_url = null;
	public ?string $url          = null;

}
