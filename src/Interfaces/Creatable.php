<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Interfaces;

/**
 * Creatable interface.
 *
 * @since 0.1.0
 */
interface Creatable {

	/**
	 * Returns an instance of a class.
	 *
	 * @since 0.1.0
	 *
	 * @param string      $id      Identifier.
	 * @param ?mixed|null ...$args Arguments.
	 *
	 * @return Instantiable
	 */
	public static function create( string $id, ...$args ): Instantiable;

}
