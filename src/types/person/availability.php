<?php

namespace elite42\breezeway\types\person;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class availability extends jsonDeserialize {

	public array $friday    = [];
	public array $monday    = [];
	public array $saturday  = [];
	public array $sunday    = [];
	public array $thursday  = [];
	public array $tuesday   = [];
	public array $wednesday = [];

}
