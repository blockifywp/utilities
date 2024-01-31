<?php

declare( strict_types=1 );

namespace Blockify\Core\Utilities;

use function apply_filters;
use function basename;
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

class Icon {

	/**
	 * Returns array of all icon sets and their directory path.
	 *
	 * @since 0.9.10
	 *
	 * @return array [ 'set' => 'path' ]
	 */
	public static function get_icon_sets(): array {
		$theme = [
			[
				'label' => 'WordPress',
				'value' => 'wordpress',
			],
			[
				'label' => 'Social',
				'value' => 'social',
			],
		];

		$child_theme = glob( get_stylesheet_directory() . '/assets/icons/*', GLOB_ONLYDIR );

		foreach ( $child_theme as $dir ) {
			$slug = basename( $dir );

			$theme[] = [
				'label' => Str::to_title_case( $slug ),
				'value' => $slug,
			];
		}

		$options   = get_option( 'blockify' )['iconSets'] ?? $theme;
		$icon_sets = [];

		foreach ( $options as $option ) {
			$value = $option['value'] ?? null;

			if ( is_null( $value ) ) {
				continue;
			}

			$parent = get_template_directory() . '/assets/icons/' . $value;
			$child  = get_stylesheet_directory() . '/assets/icons/' . $value;

			if ( file_exists( $parent ) ) {
				$icon_sets[ $value ] = $parent;
			}

			if ( file_exists( $child ) ) {
				$icon_sets[ $value ] = $child;
			}
		}

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

		$label = Str::to_title_case( $name ) . __( ' Icon', 'blockify' );
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

}
