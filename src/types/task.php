<?php

namespace elite42\breezeway\types;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class task extends jsonDeserialize {

	/** @var \elite42\breezeway\types\task\assignment[] $assignments */
	public array $assignments = [];

	public ?string $bill_to = null;

	/** @var \elite42\breezeway\types\task\cost[] $costs */
	public array               $costs       = [];
	public ?\DateTimeImmutable $created_at  = null;
	public ?task\finishedBy    $created_by  = null;
	public ?string             $description = null;
	public ?\DateTimeImmutable $finished_at = null;
	public ?task\finishedBy    $finished_by = null;

	public ?int                $home_id               = null;
	public ?int                $id                    = null;
	public ?string             $name                  = null;
	public ?bool               $paused                = null;
	public array               $photos                = [];
	public ?string             $rate_paid             = null;
	public ?string             $rate_type             = null;
	public ?string             $reference_property_id = null;
	public ?string             $report_url            = null;
	public ?string             $requested_by          = null;
	public ?string             $scheduled_date        = null;
	public ?string             $scheduled_time        = null;
	public ?\DateTimeImmutable $started_at            = null;
	public ?task\finishedBy    $subdepartment         = null;

	/** @var \elite42\breezeway\types\task\supply[] $supplies */
	public array $supplies = [];

	/** @var string[] $tags */
	public array $tags = [];

	/** @var \elite42\breezeway\types\task\finishedBy[] $tags */
	public array $task_tags = [];

	public ?int                 $template_id      = null;
	public ?string              $total_time       = null;
	public ?string              $type_department  = null;
	public ?string              $type_priority    = null;
	public ?task\typeTaskStatus $type_task_status = null;
	public ?\DateTimeImmutable  $updated_at       = null;

}
