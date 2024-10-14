<?php

namespace elite42\breezeway\types;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class person extends jsonDeserialize {

	public bool $accept_decline_tasks  = false;
	public bool $active  = false;
	public ?person\availability $availability  = null;

	/** @var string[] $emails  */
	public array $emails  = [];

	public ?string $employee_code      = null;
	public ?string    $first_name = null;

	/** @var \elite42\breezeway\types\person\group[] $groups  */
	public array    $groups = [];
	public ?int $id   = null;
	public ?string $last_name   = null;
	public array $shifts   = [];

	/** @var string[] $type_departments */
	public array $type_departments   = [];
	public ?string $type_role   = null;
}
