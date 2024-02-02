<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use ReflectionClass;
use ReflectionMethod;
use function add_filter;
use function explode;
use function is_object;
use function trim;

/**
 * Class Hooks.
 *
 * @package Blockify\Core
 */
class Hook {

	/**
	 * Hook methods based on annotation.
	 *
	 * @param object|string $object Object or class name.
	 *
	 * @return void
	 */
	public static function annotations( $object ): void {
		if ( ! is_object( $object ) ) {
			return;
		}

		$reflection = new ReflectionClass( $object );

		// Look for hook tag in all public methods.
		foreach ( $reflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ) {

			// Do not hook constructors.
			if ( $method->isConstructor() ) {
				continue;
			}

			$meta_data = self::get_metadata( (string) $method->getDocComment() );

			if ( $meta_data === null ) {
				continue;
			}

			add_filter(
				$meta_data['tag'],
				[ $object, $method->name ],
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
	private static function get_metadata( string $doc_comment ): ?array {
		$line  = Str::between( '@hook', '*', $doc_comment, true );
		$parts = explode( ' ', trim( $line ) );
		$tag   = trim( $parts[0] ?? '' );

		return $tag ? [
			'tag'      => $tag,
			'priority' => $parts[1] ?? 10,
		] : null;
	}

}
