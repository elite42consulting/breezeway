<?php

namespace elite42\breezeway\types\task;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class assignment extends jsonDeserialize {

	public ?int    $assignee_id           = null;
	public ?string $employee_code         = null;
	public ?string $expires_at            = null;
	public ?int    $id                    = null;
	public ?string $name                  = null;
	public ?string $type_task_user_status = null;

}
