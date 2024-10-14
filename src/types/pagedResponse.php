<?php

namespace elite42\breezeway\types;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class pagedResponse extends jsonDeserialize {

	public ?int  $limit         = 100;
	public ?int  $page          = 1;
	public ?int  $total_pages   = 1;
	public ?int  $total_results = 1;
	public array $results       = [];

}
