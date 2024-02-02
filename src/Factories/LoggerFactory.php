<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Factories;

use Blockify\Utilities\Interfaces\Creatable;
use Blockify\Utilities\Interfaces\Instantiable;
use Blockify\Utilities\Logger;

/**
 * Logger factory.
 *
 * @since 0.1.0
 */
class LoggerFactory implements Creatable, Instantiable {

	/**
	 * Returns an instance of a logger.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id      Identifier.
	 * @param mixed  ...$args Arguments.
	 *
	 * @return Instantiable
	 */
	public static function create( string $id, ...$args ): Instantiable {
		static $loggers = [];

		if ( ! isset( $loggers[ $id ] ) ) {

			/**
			 * @var Logger $logger
			 */
			$loggers[ $id ] = Factory::create( Logger::class );
		}

		return $loggers[ $id ];
	}

}
