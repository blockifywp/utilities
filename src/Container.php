<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use Blockify\Utilities\Exceptions\ContainerException;
use Blockify\Utilities\Exceptions\NotFoundException;
use Blockify\Utilities\Interfaces\Registerable;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use WP_Error;
use function is_callable;
use function is_object;

/**
 * Simple auto-wiring dependency injection container.
 *
 * @since 0.1.0
 */
class Container implements ContainerInterface {

	/**
	 * Instances.
	 *
	 * @var array
	 */
	private array $instances = [];

	/**
	 * Factory constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $id Instance ID.
	 *
	 * @return self
	 */
	public static function instance( string $id ): self {
		static $instances = [];

		if ( ! isset( $instances[ $id ] ) ) {
			$instances[ $id ] = new self();
		}

		return $instances[ $id ] ?? new self();
	}

	/**
	 * Retrieves an instance.
	 *
	 * @throws NotFoundException
	 *
	 * @param string $id Abstract class name.
	 *
	 * @return mixed
	 */
	public function get( string $id ) {
		if ( ! $this->has( $id ) ) {
			throw new NotFoundException( $id );
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
	 * Creates a new instance.
	 *
	 * @param string $id   Abstract class name.
	 * @param ?array $args Optional arguments.
	 *
	 * @return mixed
	 */
	public function make( string $id, ?array $args = null ) {
		try {
			$this->instances[ $id ] = $this->resolve( $id, $args );
		} catch ( ContainerException | ReflectionException $e ) {
			new WP_Error( static::class, $e->getMessage() );

			return null;
		}

		if ( $this->instances[ $id ] instanceof Registerable ) {
			$this->instances[ $id ]->register( $this );
		}

		return $this->instances[ $id ];
	}

	/**
	 * Creates a new instance or returns an existing one.
	 *
	 * @throws ReflectionException|ContainerException If an instance cannot be resolved.
	 *
	 * @param string $id   Abstract class name.
	 * @param ?array $args Optional arguments.
	 *
	 * @return mixed
	 */
	private function resolve( string $id, ?array $args = null ) {
		if ( ! $this->has( $id ) ) {
			$this->instances[ $id ] = null;
		}

		if ( is_object( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		$reflector = new ReflectionClass( $id );

		if ( ! $reflector->isInstantiable() ) {
			throw new ContainerException( "Class {$id} is not instantiable." );
		}

		$condition = true;

		if ( $reflector->hasMethod( 'condition' ) ) {
			$method = $reflector->getMethod( 'condition' );

			if ( $method->isStatic() ) {
				$condition = $method->invoke( null );
			}
		}

		if ( ! $condition ) {
			return null;
		}

		if ( is_callable( $this->instances[ $id ] ) ) {
			return $this->instances[$id]();
		}

		$constructor = $reflector->getConstructor();

		if ( $args ) {
			$instance = $reflector->newInstanceArgs( $args );
		} elseif ( $constructor ) {
			$parameters   = $constructor->getParameters();
			$dependencies = $this->resolve_parameters( $parameters );
			$instance     = $reflector->newInstanceArgs( $dependencies );
		} else {
			$instance = $reflector->newInstance();
		}

		if ( ! is_object( $instance ) ) {
			throw new ContainerException( "Class {$id} is not instantiable." );
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
				$class_name = $type->getName();

				try {
					$dependencies[] = $this->resolve( $class_name );
				} catch ( ContainerException | NotFoundException | ReflectionException $e ) {
					throw new ContainerException( "Cannot auto-resolve class: '{$class_name}' for {$parameter->getDeclaringClass()->name}." );
				}
			}
		}

		return $dependencies;
	}
}
