<?php

declare( strict_types=1 );

namespace Blockify\Core\Services;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function add_filter;
use function is_object;
use function is_string;
use function preg_match;

/**
 * Class Hooks.
 *
 * @package Blockify\Core
 */
class Hooks {

	/**
	 * Hook methods based on annotation.
	 *
	 * @param object|string $object_or_class Object or class name.
	 *
	 * @return void
	 */
	public function add_annotations( $object_or_class ): void {
		if ( ! is_object( $object_or_class ) && ! is_string( $object_or_class ) ) {
			return;
		}

		try {
			$reflection = new ReflectionClass( $object_or_class );
		} catch ( ReflectionException $e ) {
			return;
		}

		// Look for hook tag in all public methods.
		foreach ( $reflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ) {

			// Do not hook constructors.
			if ( $method->isConstructor() ) {
				continue;
			}

			$meta_data = $this->get_metadata( (string) $method->getDocComment() );

			if ( $meta_data === null ) {
				continue;
			}

			add_filter(
				$meta_data['tag'],
				[ $this, $method->name ],
				$meta_data['priority'],
				$method->getNumberOfParameters()
			);
		}
	}

	/**
	 * Read hook tag from docblock.
	 *
	 * @param string $doc_comment Docblock of a method.
	 *
	 * @return ?array{tag: string, priority: int}|null
	 */
	private function get_metadata( string $doc_comment ): ?array {
		$regex   = '/^\s+\*\s+@hook\s+([\w\/._=-]+)(?:\s+(\d+|first|last))?\s*$/m';
		$matches = [];

		if ( preg_match( $regex, $doc_comment, $matches ) !== 1 ) {
			return null;
		}

		return [
			'tag'      => $matches[1] ?? '',
			'priority' => $matches[2] ?? 10,
		];
	}

}
