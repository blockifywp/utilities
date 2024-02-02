<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

class Factory {

	/**
	 * Create an instance of a class.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class Class name.
	 * @param array  $args  Arguments.
	 *
	 * @return object
	 */
	public static function make( string $class, array $args = [] ): object {
		static $instances = [];

		if ( ! isset( $instances[ $class ] ) ) {
			$instances[ $class ] = new $class( ...$args );
		}

		return $instances[ $class ];
	}

}
