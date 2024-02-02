<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Interfaces;

/**
 * Interface Hookable
 *
 * @package Blockify\Utilities\Interfaces
 */
interface Hookable {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function hooks(): void;

}
