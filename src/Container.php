<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use Blockify\Utilities\Exceptions\ContainerException;
use Blockify\Utilities\Interfaces\Instantiable;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use function is_callable;
use function is_object;

/**
 * Simple auto-wiring dependency injection container.
 *
 * @since 0.1.0
 */
class Container implements ContainerInterface, LoggerAwareInterface, Instantiable {

	use LoggerAwareTrait;

	/**
	 * Instances.
	 *
	 * @var array
	 */
	private array $instances = [];

	/**
	 * Sets logger.
	 *
	 * @param LoggerInterface $logger Logger.
	 *
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * Retrieves an instance.
	 *
	 * @param string $id Abstract class name.
	 *
	 * @return mixed
	 */
	public function get( string $id ) {
		if ( ! $this->has( $id ) ) {
			$this->logger->error( "Class {$id} does not in container." );
		}

		return $this->instances[ $id ];
	}

	/**
	 * Checks if instance exists.
	 *
	 * @param string $id Abstract class name.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->instances[ $id ] );
	}

	/**
	 * Creates a new instance or returns an existing one.
	 *
	 * @param string $id   Abstract class name.
	 * @param ?array $args Optional arguments.
	 *
	 * @return mixed
	 */
	public function make( string $id, ?array $args = null ) {
		if ( ! $this->has( $id ) ) {
			$this->instances[ $id ] = null;
		}

		if ( is_object( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		try {
			$reflector = new ReflectionClass( $id );
		} catch ( ReflectionException $e ) {
			$this->logger->error( "Class {$id} does not exist.", [ 'exception' => $e ] );

			return null;
		}

		if ( ! $reflector->isInstantiable() ) {
			$this->logger->error( "Class {$id} is not instantiable." );

			return null;
		}

		$condition = true;

		if ( $reflector->hasMethod( 'condition' ) ) {
			$method = $reflector->getMethod( 'condition' );

			if ( $method->isStatic() ) {
				try {
					$condition = $method->invoke( null );
				} catch ( ReflectionException $e ) {
					$this->logger->error( "Cannot invoke condition method for {$id}.", [ 'exception' => $e ] );

					return null;
				}
			}
		}

		if ( ! $condition ) {
			return null;
		}

		if ( is_callable( $this->instances[ $id ] ) ) {
			return $this->instances[$id]();
		}

		$constructor = $reflector->getConstructor();

		try {
			if ( $args ) {
				$instance = $reflector->newInstanceArgs( $args );
			} elseif ( $constructor ) {
				$parameters   = $constructor->getParameters();
				$dependencies = $this->resolve_parameters( $parameters );
				$instance     = $reflector->newInstanceArgs( $dependencies );
			} else {
				$instance = $reflector->newInstance();
			}
		} catch ( ReflectionException | ContainerException $e ) {
			$this->logger->error( "Cannot instantiate class {$id}.", [ 'exception' => $e ] );

			return null;
		}

		if ( ! is_object( $instance ) ) {
			$this->logger->error( "Class {$id} is not an object." );

			return null;
		}

		$this->instances[ $id ] = $instance;

		return $instance;
	}

	/**
	 * Resolves dependencies for class constructor.
	 *
	 * @throws ContainerException If a dependency cannot be resolved.
	 *
	 * @param ReflectionParameter[] $parameters Constructor parameters.
	 *
	 * @return array
	 */
	private function resolve_parameters( array $parameters ): array {
		$dependencies = [];

		foreach ( $parameters as $parameter ) {
			if ( ! $parameter instanceof ReflectionParameter ) {
				continue;
			}

			$type = $parameter->getType();

			if ( ! $type || $type->isBuiltin() ) {
				if ( $parameter->isDefaultValueAvailable() ) {
					$dependencies[] = $parameter->getDefaultValue();
				} else {
					$type_name  = $type ? $type->getName() : 'mixed';
					$class_name = $parameter->getDeclaringClass()->name;

					// TODO: Add support for primitive types.
					throw new ContainerException( "Cannot auto-resolve primitive type: '{$type_name}' for {$class_name}." );
				}
			} else {
				$resolved = $this->make( $type->getName() );

				if ( $resolved ) {
					$dependencies[] = $resolved;
				}
			}
		}

		return $dependencies;
	}
}
