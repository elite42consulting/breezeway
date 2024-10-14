<?php

namespace elite42\breezeway\types\person;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class shifts extends jsonDeserialize {

	public ?shift $friday    = null;
	public ?shift $monday    = null;
	public ?shift $saturday  = null;
	public ?shift $sunday    = null;
	public ?shift $thursday  = null;
	public ?shift $tuesday   = null;
	public ?shift $wednesday = null;

}
