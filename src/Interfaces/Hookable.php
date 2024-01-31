<?php

declare( strict_types=1 );

namespace Blockify\Core\Interfaces;

/**
 * Interface Hookable
 *
 * @package Blockify\Core\Interfaces
 */
interface Hookable {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function hooks(): void;

}
