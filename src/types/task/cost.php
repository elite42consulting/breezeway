<?php

namespace elite42\breezeway\types\task;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class cost extends jsonDeserialize {

	public ?float    $cost           = null;
	public ?string $created_at         = null;
	public ?string $description            = null;
	public ?int    $id                    = null;
	public ?cost\costType $type_cost                  = null;
	public ?\DateTimeImmutable $updated_at  = null;


}
