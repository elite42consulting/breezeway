<?php

namespace elite42\breezeway\types;

use andrewsauder\jsonDeserialize\jsonDeserialize;

class taskRequirement extends jsonDeserialize {

	/** @var string|string[]|null $action */
	public string|array|null $action = null;

	public ?string $homeElementName = null;

	public ?string $note = null;

	public bool $photoRequired = false;

	//todo: unknown type
	public array $photos;

	public ?string $response = null;

	public ?string $sectionName = null;

	public ?string $typeRequirement = null;


	public function getActionName(): ?string {
		if( is_string( $this->action ) ) {
			return trim( $this->action );
		}
		elseif( is_array( $this->action ) ) {
			$actions = $this->action;
			foreach( $actions as $i => $action ) {
				$actions[ $i ] = trim( $action );
			}
			return implode( ', ', $actions );
		}
		else {
			return null;
		}
	}

}
