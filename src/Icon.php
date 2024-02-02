<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use WP_REST_Request;
use WP_REST_Server;
use function add_action;
use function apply_filters;
use function basename;
use function current_user_can;
use function file_exists;
use function file_get_contents;
use function get_stylesheet_directory;
use function get_template_directory;
use function glob;
use function implode;
use function is_null;
use function strtolower;
use function trim;
use function uniqid;
use const GLOB_ONLYDIR;

/**
 * Icon utility class.
 *
 * @since 1.0.0
 */
class Icon {

	/**
	 * Returns array of all icon sets and their directory path.
	 *
	 * @since 0.9.10
	 *
	 * @return array <string, string>
	 */
	public static function get_icon_sets(): array {
		$utility_dir    = dirname( __DIR__ ) . '/public/icons/';
		$template_dir   = get_template_directory() . '/assets/icons/';
		$stylesheet_dir = get_stylesheet_directory() . '/assets/icons/';

		$dirs = [
			...glob( $utility_dir . '*', GLOB_ONLYDIR ),
			...glob( $template_dir . '*', GLOB_ONLYDIR ),
			...glob( $stylesheet_dir . '*', GLOB_ONLYDIR ),
		];

		$found = [];

		foreach ( $dirs as $dir ) {
			$slug = basename( $dir );

			$found[] = [
				'label' => Str::title_case( $slug ),
				'value' => $slug,
			];
		}

		$options   = get_option( 'blockify' )['iconSets'] ?? $found;
		$icon_sets = [];

		foreach ( $options as $option ) {
			$value = $option['value'] ?? null;

			if ( is_null( $value ) ) {
				continue;
			}

			$utility = $utility_dir . '/public/icons/' . $value;
			$parent  = $template_dir . '/assets/icons/' . $value;
			$child   = $stylesheet_dir . '/assets/icons/' . $value;

			if ( file_exists( $utility ) ) {
				$icon_sets[ $value ] = $utility;
			}

			if ( file_exists( $parent ) ) {
				$icon_sets[ $value ] = $parent;
			}

			if ( file_exists( $child ) ) {
				$icon_sets[ $value ] = $child;
			}
		}

		/**
		 * Filters the icon sets.
		 *
		 * @since 0.9.10
		 *
		 * @param array $icon_sets <string, string> Set name, set path.
		 */
		return apply_filters( 'blockify_icon_sets', $icon_sets );
	}

	/**
	 * Returns svg string for given icon.
	 *
	 * @since 0.9.10
	 *
	 * @param string          $set  Icon set.
	 * @param string          $name Icon name.
	 * @param string|int|null $size Icon size.
	 *
	 * @return string
	 */
	public static function get_icon( string $set, string $name, $size = null ): string {
		$set = strtolower( $set );

		static $cache = [];

		$cache_key = implode( '-', [ $set, $name, $size ] );

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$icon_sets = self::get_icon_sets();

		if ( ! isset( $icon_sets[ $set ] ) ) {
			return '';
		}

		$dir  = $icon_sets[ $set ];
		$file = $dir . '/' . $name . '.svg';

		if ( ! file_exists( $file ) ) {
			return '';
		}

		$icon = file_get_contents( $file );
		$dom  = DOM::create( $icon );
		$svg  = DOM::get_element( 'svg', $dom );

		if ( ! $svg ) {
			return '';
		}

		$unique_id = 'icon-' . uniqid();

		$svg->setAttribute( 'role', 'img' );
		$svg->setAttribute( 'aria-labelledby', $unique_id );
		$svg->setAttribute( 'data-icon', $set . '-' . $name );

		$label = Str::title_case( $name ) . __( ' Icon', 'blockify' );
		$title = DOM::create_element( 'title', $dom );

		$title->appendChild( $dom->createTextNode( $label ) );
		$title->setAttribute( 'id', $unique_id );

		$svg->insertBefore( $title, $svg->firstChild );

		if ( $size ) {
			$has_unit = Str::contains_any( (string) $size, 'px', 'em', 'rem', '%', 'vh', 'vw' );

			if ( $has_unit ) {
				$styles = CSS::string_to_array( $svg->getAttribute( 'style' ) );

				$styles['min-width'] = $size;
				$styles['height']    = $size;

				$svg->setAttribute( 'style', CSS::array_to_string( $styles ) );
			} else {
				$svg->setAttribute( 'width', (string) $size );
				$svg->setAttribute( 'height', (string) $size );
			}
		}

		$fill = $svg->getAttribute( 'fill' );

		if ( ! $fill ) {
			$svg->setAttribute( 'fill', 'currentColor' );
		}

		$cache[ $cache_key ] = trim( $dom->saveHTML() );

		return $cache[ $cache_key ];
	}

	/**
	 * Registers icon REST endpoint.
	 *
	 * @since 0.0.1
	 *
	 * @param string $namespace Route namespace.
	 * @param string $route     Route path.
	 *
	 * @return void
	 */
	public static function register_rest_route( string $namespace = 'blockify/v1', string $route = '/icons/' ): void {
		static $registered = [];

		if ( isset( $registered[ $namespace ] ) ) {
			return;
		}

		$registered[ $namespace ] = $route;

		$args = [
			'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
			'callback'            => static fn( WP_REST_Request $request ): array => self::get_icon_data( $request ),
			'methods'             => WP_REST_Server::READABLE,
			[
				'args' => [
					'sets' => [
						'required' => false,
						'type'     => 'string',
					],
					'set'  => [
						'required' => false,
						'type'     => 'string',
					],
				],
			],
		];

		add_action(
			'rest_api_init',
			static fn() => register_rest_route( $namespace, $route, $args )
		);
	}

	/**
	 * Returns icon data for rest endpoint
	 *
	 * @since 0.4.8
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed array|string
	 */
	private static function get_icon_data( WP_REST_Request $request ) {
		$icon_data = [];
		$icon_sets = Icon::get_icon_sets();

		foreach ( $icon_sets as $icon_set => $set_dir ) {
			$icons = glob( $set_dir . '/*.svg' );

			foreach ( $icons as $icon ) {
				$name = basename( $icon, '.svg' );
				$icon = file_get_contents( $icon );

				if ( $icon_set === 'WordPress' ) {
					$icon = str_replace(
						[ 'fill="none"' ],
						[ 'fill="currentColor"' ],
						$icon
					);
				}

				// Remove comments.
				$icon = preg_replace( '/<!--(.|\s)*?-->/', '', $icon );

				// Remove new lines.
				$icon = preg_replace( '/\s+/', ' ', $icon );

				// Remove tabs.
				$icon = preg_replace( '/\t+/', '', $icon );

				// Remove spaces between tags.
				$icon = preg_replace( '/>\s+</', '><', $icon );

				$icon_data[ $icon_set ][ $name ] = trim( $icon );
			}
		}

		if ( $request->get_param( 'set' ) ) {
			$set = $request->get_param( 'set' );

			if ( $request->get_param( 'icon' ) ) {

				// TODO: Is string being used anywhere?
				return $icon_data[ $set ][ $request->get_param( 'icon' ) ];
			}

			return $icon_data[ $set ];
		}

		if ( $request->get_param( 'sets' ) ) {
			return array_keys( $icon_data );
		}

		return $icon_data;
	}

}
