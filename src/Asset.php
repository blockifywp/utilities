<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

/**
 * Asset utility class.
 *
 * @since 1.0.0
 */
class Asset {

	/**
	 * Returns asset version for cache busting.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $default_version Default version.
	 *
	 * @return string
	 */
	public static function get_version( ?string $default_version = '' ): string {
		$wp_debug     = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$version      = ( $wp_debug || $script_debug ) ? time() : $default_version;

		return (string) $version;
	}
}
