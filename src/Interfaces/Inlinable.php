<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Interfaces;

/**
 * Inlinable interface.
 *
 * @since 1.0.0
 */
interface Inlinable {

	/**
	 * Register inline styles from file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file      Callable with access to template HTML.
	 * @param array  $strings   Array of strings to check for in the template HTML.
	 * @param bool   $condition Condition to check for.
	 *
	 * @return self
	 */
	public function add_file( string $file, array $strings = [], bool $condition = true ): self;

	/**
	 * Register inline styles from callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback Receives $template_html string and $load_all boolean.
	 *
	 * @return self
	 */
	public function add_callback( callable $callback ): self;

	/**
	 * Register inline styles from string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string  String to add to the template HTML.
	 * @param array  $strings Array of strings to check for in the template HTML.
	 *
	 * @return self
	 */
	public function add_string( string $string, array $strings = [] ): self;

}
