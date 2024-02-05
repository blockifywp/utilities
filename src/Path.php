<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use function basename;
use function content_url;
use function dirname;
use function implode;
use function str_replace;
use function trailingslashit;
use const DIRECTORY_SEPARATOR;
use const WP_CONTENT_DIR;

/**
 * Package class.
 *
 * @since 1.0.0
 */
class Path {

	/**
	 * Returns the package directory path.
	 *
	 * @param string $file Main plugin or theme file.
	 * @param string $src  Package src directory.
	 *
	 * @return string
	 */
	public static function get_package_dir( string $file, string $src ): string {
		return trailingslashit(
			implode(
				DIRECTORY_SEPARATOR,
				[
					dirname( $file ),
					implode(
						DIRECTORY_SEPARATOR,
						[
							basename( dirname( $src, 3 ) ),
							basename( dirname( $src, 2 ) ),
							basename( dirname( $src, 1 ) ),
						]
					),
				]
			)
		);
	}

	/**
	 * Returns the package URI.
	 *
	 * @param string $dir Package directory.
	 *
	 * @return string
	 */
	public static function get_package_url( string $dir ): string {
		return str_replace( WP_CONTENT_DIR, content_url(), $dir );
	}
}
