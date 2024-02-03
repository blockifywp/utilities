<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Traits;

use Blockify\Utilities\Str;

/**
 * InlineAssets trait.
 *
 * @since 1.0.0
 */
trait InlineAsset {

	/**
	 * Directory.
	 *
	 * @var string
	 */
	public string $dir;

	/**
	 * Handle.
	 *
	 * @var string
	 */
	private string $handle;

	/**
	 * Callbacks.
	 *
	 * @var callable[]
	 */
	private array $callbacks = [];

	/**
	 * Files.
	 *
	 * @var array <string, array<array, boolean>>
	 */
	private array $files = [];

	/**
	 * Strings.
	 *
	 * @var array <string, array>
	 */
	private array $strings = [];

	/**
	 * Localized data.
	 *
	 * @param array $data <string, array<array, array>>
	 */
	private array $data = [];

	/**
	 * Register inline assets from file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file    Callable with access to template HTML.
	 * @param array  $strings Array of strings to check for in the template HTML.
	 *
	 * @return self
	 */
	public function add_file( string $file, array $strings = [], bool $condition = true ): self {
		$this->files[ $file ] = [ $strings, $condition ];

		return $this;
	}

	/**
	 * Register inline assets from callback.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback Callable with access to global template HTML variable.
	 * @param bool     $load_all Whether to load all assets.
	 *
	 * @return self
	 */
	public function add_callback( callable $callback, bool $load_all ): self {
		$this->callbacks[] = $callback;

		return $this;
	}

	/**
	 * Register inline assets from string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string  String to add to the template HTML.
	 * @param array  $strings Array of strings to check for in the template HTML.
	 *
	 * @return self
	 */
	public function add_string( string $string, array $strings = [] ): self {
		$this->strings[ $string ] = $strings;

		return $this;
	}

	/**
	 * Adds l10n data for scripts and custom properties for styles.
	 *
	 * @param string $key       Array key.
	 * @param array  $value     Array of data to localize.
	 * @param array  $strings   Array of strings to check for in the template HTML.
	 * @param bool   $condition Other condition to check for.
	 *
	 * @return self
	 */
	public function add_data(
		string $key,
		array  $value,
		array  $strings = [],
		bool   $condition = true
	): self {
		$this->data[ $key ] = [ $value, $strings, $condition ];

		return $this;
	}

	/**
	 * Enqueue inline assets.
	 *
	 * @hook enqueue_block_assets
	 *
	 * @return void
	 */
	abstract public function enqueue(): void;

	/**
	 * Returns string containing all inline assets.
	 *
	 * @param string $template_html Global template HTML variable.
	 * @param bool   $load_all      Load all assets.
	 *
	 * @return string
	 */
	private function get_inline_assets( string $template_html, bool $load_all ): string {
		$css    = '';
		$assets = $this->get_assets( $template_html, $load_all );

		foreach ( $assets as $asset ) {
			$css .= Str::remove_line_breaks( $asset );
		}

		return $css;
	}

	/**
	 * Returns array of inline assets.
	 *
	 * @param string $template_html Global template HTML variable.
	 * @param bool   $load_all      Load all assets.
	 *
	 * @return array
	 */
	private function get_assets( string $template_html, bool $load_all ): array {
		$assets = [];

		foreach ( $this->callbacks as $callback ) {
			$id            = is_array( $callback ) ? $callback[0] : $callback;
			$assets[ $id ] = $callback( $template_html, $load_all );
		}

		foreach ( $this->files as $file => $args ) {
			$strings   = $args[0] ?? [];
			$condition = $args[1] ?? true;

			if ( ! $condition ) {
				continue;
			}

			if ( ! $load_all && ! Str::contains_any( $template_html, ...$strings ) ) {
				continue;
			}

			if ( file_exists( $this->dir . $file ) ) {
				$assets[ $file ] = file_get_contents( $this->dir . $file );
			}
		}

		foreach ( $this->strings as $string => $strings ) {
			if ( $load_all || Str::contains_any( $template_html, ...$strings ) ) {
				$assets[ $string ] = $string;
			}
		}

		return $assets;
	}

}
