<?php

namespace elite42\breezeway\types\unit;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class notes extends jsonDeserialize {

	public ?string $access      = '';
	public ?string $general      = '';
	public ?string $wifi      = '';
	public ?string $about      = '';
	public ?string $direction      = '';
	public ?string $trash_info      = '';

}
