<?php

namespace elite42\breezeway\types\person;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class shift extends jsonDeserialize {

	public ?bool    $active              = false;
	public array    $shifts              = [];

}
