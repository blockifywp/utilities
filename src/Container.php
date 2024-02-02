<?php

declare( strict_types=1 );

namespace Blockify\Core;

use Blockify\Core\Exceptions\ContainerException;
use Blockify\Core\Exceptions\NotFoundException;
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
	 * @param string $id Abstract class name.
	 *
	 * @return mixed
	 */
	public function create( string $id ) {
		try {
			$this->instances[ $id ] = $this->resolve( $id );
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
	 * @param string $id Abstract class name.
	 *
	 * @return mixed
	 */
	private function resolve( string $id ) {
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

		if ( $constructor ) {
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
