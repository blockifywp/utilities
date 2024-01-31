<?php

declare( strict_types=1 );

namespace Blockify\Core\Services;

use Blockify\Core\Utilities\Str;
use function is_array;

class Styles {

	public const HOOK = 'blockify_styles';

	private string $handle;

	private array $callbacks = [];

	private array $styles = [];

	public function __construct( Data $data, Hooks $hooks ) {
		$this->handle = $data->slug;

		$hooks->add_annotations( $this );
	}

	public function add( string $handle ): Style {
		$this->styles[ $handle ] = new Style( $handle );

		return $this->styles[ $handle ];
	}

	public function add_callback( callable $callback ): void {
		$this->callbacks[] = $callback;
	}

	/**
	 * @hook enqueue_block_assets
	 */
	public function enqueue(): void {
		wp_register_style( $this->handle, '' );
		wp_add_inline_style( $this->handle, $this->get_inline_styles() );
	}

	private function get_inline_styles(): string {
		$css    = '';
		$styles = $this->get_styles();

		foreach ( $styles as $style ) {
			$css .= Str::remove_line_breaks( $style );
		}

		return $css;
	}

	private function get_styles(): array {
		$styles        = [];
		$template_html = $GLOBALS['template_html'] ?? '';

		foreach ( $this->callbacks as $callback ) {
			$id            = is_array( $callback ) ? $callback[0] : $callback;
			$styles[ $id ] = $callback( $template_html );
		}

		return $styles;
	}
}
