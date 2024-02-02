<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Factories;

use Blockify\Utilities\Interfaces\Creatable;
use Blockify\Utilities\Interfaces\Instantiable;

/**
 * Generic factory.
 *
 * @since 0.1.0
 */
class Factory implements Creatable {

	/**
	 * Returns an instance of a class.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id      Class name.
	 * @param mixed  ...$args Arguments.
	 *
	 * @return Instantiable
	 */
	public static function create( string $id, ...$args ): Instantiable {
		static $instances = [];

		if ( ! isset( $instances[ $id ] ) ) {
			$instances[ $id ] = new $id( ...$args );
		}

		return $instances[ $id ];
	}

}
