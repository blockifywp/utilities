<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Factories;

use Blockify\Utilities\Container;
use Blockify\Utilities\Interfaces\Creatable;
use Blockify\Utilities\Interfaces\Instantiable;
use Blockify\Utilities\Logger;

/**
 * Container factory.
 *
 * @since 0.1.0
 */
class ContainerFactory implements Creatable, Instantiable {

	/**
	 * Returns an instance of a container.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id      Identifier.
	 * @param mixed  ...$args Arguments.
	 *
	 * @return Instantiable
	 */
	public static function create( string $id, ...$args ): Instantiable {
		static $containers = [];

		if ( ! isset( $containers[ $id ] ) ) {

			/**
			 * @var Container $container
			 */
			$container = Factory::create( Container::class );

			/**
			 * @var Logger $logger
			 */
			$logger = LoggerFactory::create( $id );

			$container->setLogger( $logger );

			$containers[ $id ] = $container;
		}

		return $containers[ $id ];
	}

}
