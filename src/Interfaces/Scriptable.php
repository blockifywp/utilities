<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Interfaces;

/**
 * Scriptable interface.
 *
 * @since 1.0.0
 */
interface Scriptable {

	/**
	 * Register scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param Inlinable $scripts Inlinable service.
	 *
	 * @return void
	 */
	public function scripts( Inlinable $scripts ): void;

}
