<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use function _wp_to_kebab_case;
use function array_key_last;
use function array_merge;
use function count;
use function explode;
use function file_exists;
use function get_template_directory;
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function is_string;
use function rtrim;
use function str_contains;
use function str_replace;
use function wp_json_file_decode;

/**
 * CSS trait.
 *
 * @since 1.0.0
 */
class CSS {

	/**
	 * Converts array of CSS rules to string.
	 *
	 * @since 0.0.22
	 *
	 * @param array $styles [ 'color' => 'red', 'background' => 'blue' ].
	 * @param bool  $trim   Whether to trim the trailing semicolon.
	 *
	 * @return string
	 */
	public static function array_to_string( array $styles, bool $trim = false ): string {
		$css = '';

		foreach ( $styles as $property => $value ) {
			if ( is_null( $value ) || is_array( $value ) ) {
				continue;
			}

			$value     = self::format_custom_property( (string) $value );
			$semicolon = $trim && $property === array_key_last( $styles ) ? '' : ';';
			$css       .= $property . ':' . $value . $semicolon;
		}

		return rtrim( $css, ';' );
	}

	/**
	 * Formats custom properties for unsupported blocks.
	 *
	 * @since 0.9.10
	 *
	 * @param string $custom_property Custom property value to format.
	 *
	 * @return string
	 */
	public static function format_custom_property( string $custom_property ): string {
		if ( str_contains( $custom_property, 'var:' ) ) {
			return str_replace(
				[ 'var:', '|', ],
				[ 'var(--wp--', '--', ],
				$custom_property . ')'
			);
		}

		static $global_settings = null;
		static $theme_json = null;

		if ( is_null( $global_settings ) ) {
			$global_settings = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings() : [];
		}

		if ( ! $global_settings ) {
			return $custom_property;
		}

		if ( is_null( $theme_json ) ) {
			$theme_json_file = get_template_directory() . '/theme.json';
			$theme_json      = [];

			if ( file_exists( $theme_json_file ) ) {
				$theme_json = wp_json_file_decode( $theme_json_file );
			}
		}

		if ( ! $theme_json ) {
			return $custom_property;
		}

		if ( ! isset( $global_settings['color']['palette']['theme'] ) && ! isset( $theme_json->settings->color->palette ) ) {
			return $custom_property;
		}

		$colors = array_merge(
			(array) ( $global_settings['color']['palette']['theme'] ?? [] ),
			(array) $theme_json->settings->color->palette
		);

		$system_colors = Color::SYSTEM_COLORS;

		if ( in_array( $custom_property, $system_colors, true ) ) {
			if ( $custom_property === 'current' ) {
				return 'currentcolor';
			}
		}

		$color_slugs = array_diff(
			wp_list_pluck( $colors, 'slug' ),
			$system_colors
		);

		if ( in_array( $custom_property, $color_slugs, true ) ) {
			return "var(--wp--preset--color--{$custom_property})";
		}

		return $custom_property;
	}

	/**
	 * Converts string of CSS rules to an array.
	 *
	 * @since 0.0.2
	 *
	 * @param string $css 'color:red;background:blue'.
	 *
	 * @return array
	 */
	public static function string_to_array( string $css ): array {
		$array = [];

		// Prevent svg url strings from being split.
		$css = str_replace( 'xml;', 'xml$', $css );

		$elements = explode( ';', $css );

		foreach ( $elements as $element ) {
			$parts = explode( ':', $element, 2 );

			if ( isset( $parts[1] ) ) {
				$property = $parts[0];
				$value    = $parts[1];

				if ( $value !== '' && $value !== 'null' ) {
					$value = str_replace( 'xml$', 'xml;', $value );
					$value = self::format_custom_property( (string) $value );

					if ( $value ) {
						$array[ $property ] = $value;
					}
				}
			}
		}

		return $array;
	}

	/**
	 * Adds shorthand CSS properties.
	 *
	 * @param array        $styles   Existing CSS array.
	 * @param string       $property CSS property to add. E.g. 'margin'.
	 * @param array|string $values   CSS values to add.
	 *
	 * @return array
	 */
	public static function add_shorthand_property( array $styles, string $property, $values ): array {
		if ( empty( $values ) || isset( $styles[ $property ] ) ) {
			return $styles;
		}

		if ( is_string( $values ) ) {
			$styles[ $property ] = self::format_custom_property( $values );

			return $styles;
		}

		$sides = [ 'top', 'right', 'bottom', 'left' ];

		if ( count( $values ) === 1 ) {
			foreach ( $values as $side => $value ) {
				if ( ! in_array( $side, $sides, true ) ) {
					continue;
				}

				$styles[ $property . '-' . $side ] = self::format_custom_property( $value );
			}

			return $styles;
		}

		$has_top    = isset( $values['top'] );
		$has_right  = isset( $values['right'] );
		$has_bottom = isset( $values['bottom'] );
		$has_left   = isset( $values['left'] );

		if ( ! $has_top && ! $has_right && ! $has_bottom && ! $has_left ) {
			return $styles;
		}

		$top    = self::format_custom_property( $values['top'] ?? '0' );
		$right  = self::format_custom_property( $values['right'] ?? '0' );
		$bottom = self::format_custom_property( $values['bottom'] ?? '0' );
		$left   = self::format_custom_property( $values['left'] ?? '0' );

		unset( $styles[ $property . '-top' ] );
		unset( $styles[ $property . '-right' ] );
		unset( $styles[ $property . '-bottom' ] );
		unset( $styles[ $property . '-left' ] );

		if ( $top === $right && $right === $bottom && $bottom === $left ) {
			$styles[ $property ] = self::format_custom_property( $top );
		} else {
			if ( $top === $bottom && $left === $right ) {
				$styles[ $property ] = "$top $right";
			} else {
				$styles[ $property ] = "$top $right $bottom $left";
			}
		}

		return $styles;
	}

	/**
	 * Gets responsive classes for a given property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $block_content HTML content.
	 * @param array  $block         Block data.
	 * @param array  $options       Block options.
	 * @param bool   $image         Is an image block.
	 *
	 * @return string
	 */
	public static function add_responsive_classes( string $block_content, array $block, array $options, bool $image = false ): string {
		$dom   = DOM::create( $block_content );
		$first = DOM::get_element( '*', $dom );

		if ( ! $first ) {
			return $block_content;
		}

		$element = $first;

		if ( $image ) {
			$link    = DOM::get_element( 'a', $first );
			$element = $link ? DOM::get_element( 'img', $link ) : DOM::get_element( 'img', $first );
		}

		if ( ! $element ) {
			return $block_content;
		}

		$style   = $block['attrs']['style'] ?? [];
		$classes = explode( ' ', $element->getAttribute( 'class' ) );

		foreach ( $options as $key => $args ) {
			if ( ! isset( $style[ $key ] ) || $style[ $key ] === '' ) {
				continue;
			}

			$property = _wp_to_kebab_case( $key );

			if ( isset( $args['options'] ) ) {
				$both    = $style[ $key ]['all'] ?? '';
				$mobile  = $style[ $key ]['mobile'] ?? '';
				$desktop = $style[ $key ]['desktop'] ?? '';

				if ( $both ) {
					$classes[] = "has-{$property}-{$both}";
				}

				if ( $mobile ) {
					$classes[] = "has-{$property}-{$mobile}-mobile";
				}

				if ( $desktop ) {
					$classes[] = "has-{$property}-{$desktop}-desktop";
				}
			} else {
				$classes[] = "has-{$property}";
			}

			$element->setAttribute( 'class', implode( ' ', $classes ) );

			$block_content = $dom->saveHTML();
		}

		return $block_content;
	}

	/**
	 * Adds responsive styles to DOM.
	 *
	 * @since 1.0.0
	 *
	 * @param string $block_content HTML content.
	 * @param array  $block         Block data.
	 * @param array  $options       Block options.
	 *
	 * @return string
	 */
	public static function add_responsive_styles( string $block_content, array $block, array $options ): string {
		$style = $block['attrs']['style'] ?? [];

		if ( ! $style ) {
			return $block_content;
		}

		foreach ( $options as $key => $args ) {

			if ( ! isset( $style[ $key ] ) ) {
				continue;
			}

			// Has utility class.
			if ( isset( $args['options'] ) ) {
				continue;
			}

			$dom   = DOM::create( $block_content );
			$first = DOM::get_element( '*', $dom );

			if ( ! $first ) {
				continue;
			}

			$styles   = CSS::string_to_array( $first->getAttribute( 'style' ) );
			$property = _wp_to_kebab_case( $key );
			$both     = $style[ $key ]['all'] ?? '';
			$mobile   = $style[ $key ]['mobile'] ?? '';
			$desktop  = $style[ $key ]['desktop'] ?? '';

			if ( $both ) {
				$styles[ '--' . $property ] = $both;
			}

			if ( $mobile ) {
				$styles[ '--' . $property . '-mobile' ] = $mobile;
			}

			if ( $desktop ) {
				$styles[ '--' . $property . '-desktop' ] = $desktop;
			}

			$first->setAttribute( 'style', CSS::array_to_string( $styles ) );

			$block_content = $dom->saveHTML();
		}

		return $block_content;
	}

	/**
	 * Quick and dirty way to mostly minify CSS.
	 *
	 * @author Gary Jones
	 *
	 * @link   https://github.com/GaryJones/Simple-PHP-CSS-Minification
	 *
	 * @since  1.0.0
	 *
	 * @param string $css CSS to minify.
	 *
	 * @return string Minified CSS
	 */
	public static function minify( string $css ): string {

		// Normalize whitespace.
		$css = preg_replace( '/\s+/', ' ', $css );

		// Remove spaces before and after comment.
		$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );

		// Remove comment blocks, everything between /* and */, unless.
		// preserved with /*! ... */ or /** ... */.
		$css = preg_replace( '~/\*(?![!|*])(.*?)\*/~', '', $css );

		// Remove ; before }.
		$css = preg_replace( '/;(?=\s*})/', '', $css );

		// Remove space after , : ; { } */ >.
		$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );

		// Remove space before , ; { } ( ) >.
		$css = preg_replace( '/ ([,;{}()>])/', '$1', $css );

		// Strips leading 0 on decimal values (converts 0.5px into .5px).
		$css = preg_replace( '/([: ])0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );

		// Strips units if value is 0 (converts 0px to 0).
		$css = preg_replace( '/([: ])(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );

		// Converts all zeros value into shorthand.
		$css = preg_replace( '/0 0 0 0/', '0', $css );

		// Shorten 6-character hex color codes to 3-character where possible.
		$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );

		return trim( $css );
	}
}
