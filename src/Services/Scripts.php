<?php

declare( strict_types=1 );

namespace Blockify\Core\Services;

use Blockify\Core\Utilities\Icon;
use Blockify\Core\Utilities\Str;

class Scripts {

	public const HOOK = 'blockify_scripts';

	private string $handle;

	private array $callbacks = [];

	public function __construct( Data $data, Hooks $hooks ) {
		$this->handle = $data->slug;

		$hooks->add_annotations( $this );
	}

	public function add_callback( callable $callback ): void {
		$this->callbacks[] = $callback;
	}

	public function register(): void {
		wp_register_script( $this->handle, '' );
		wp_add_inline_script( $this->handle, $this->get_inline_scripts() );
	}

	private function get_inline_scripts(): string {
		$js      = '';
		$scripts = $this->get_scripts();

		foreach ( $scripts as $script ) {
			$js .= Str::remove_line_breaks( $script );
		}

		return $js;
	}

	private function get_scripts(): array {
		$scripts       = [];
		$template_html = $GLOBALS['template_html'] ?? '';

		foreach ( $this->callbacks as $callback ) {
			$id             = is_array( $callback ) ? $callback[0] : $callback;
			$scripts[ $id ] = $callback( $template_html );
		}

		return $scripts;
	}

	private function localize(): array {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		$default_data = [
			'siteUrl'       => trailingslashit( home_url() ),
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'adminUrl'      => trailingslashit( admin_url() ),
			'nonce'         => wp_create_nonce( 'blockify' ),
			'siteEditor'    => $current_screen && $current_screen->base === 'site-editor',
			'excerptLength' => apply_filters( 'excerpt_length', 55 ),
			'icon'          => Icon::get_icon( 'social', 'blockify' ),
		];

		/**
		 * Filters editor data.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data Editor data.
		 */
		return apply_filters( 'blockify_editor_data', $default_data );
	}
}
