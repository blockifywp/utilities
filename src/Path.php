<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use function array_slice;
use function content_url;
use function dirname;
use function explode;
use function implode;
use function trailingslashit;
use function trim;
use const DIRECTORY_SEPARATOR;

/**
 * Package class.
 *
 * @since 1.0.0
 */
class Path {

	/**
	 * Returns the package directory path.
	 *
	 * @param string $project_dir Main plugin or theme file.
	 * @param string $package_dir Package src directory.
	 *
	 * @return string
	 */
	public static function get_package_dir( string $project_dir, string $package_dir ): string {
		return trailingslashit(
			implode(
				DIRECTORY_SEPARATOR,
				[
					$project_dir,
					static::get_segments( $package_dir, -3 ),
				]
			)
		);
	}

	/**
	 * Returns the package URI.
	 *
	 * @param string $project_dir Package directory.
	 * @param string $package_dir Package src directory.
	 *
	 * @return string
	 */
	public static function get_package_url( string $project_dir, string $package_dir ): string {
		$package_path = static::get_segments( $package_dir, -3, true );
		return static::get_project_url( $project_dir ) . Str::unleadingslashit( $package_path );
	}

	/**
	 * Returns the project directory path.
	 *
	 * @param string $package_dir Package dir.
	 *
	 * @return string
	 */
	public static function get_project_dir( string $package_dir ): string {
		return trailingslashit( dirname( $package_dir, 3 ) );
	}

	/**
	 * Returns the project URI.
	 *
	 * @param string $project_dir Project dir.
	 *
	 * @return string
	 */
	public static function get_project_url( string $project_dir ): string {
		return content_url( static::get_segments( $project_dir, -2, true ) );
	}

	/**
	 * Extracts specific number of segments from a path.
	 *
	 * @param string $path   The input path.
	 * @param int    $number Positive for first segments, negative for last segments.
	 * @param bool   $slash  Whether to include leading and trailing slash.
	 *
	 * @return string
	 */
	public static function get_segments( string $path, int $number, bool $slash = false ): string {
		$path_segments = explode( DIRECTORY_SEPARATOR, trim( $path, DIRECTORY_SEPARATOR ) );

		if ( $number > 0 ) {
			$extracted_segments = array_slice( $path_segments, 0, $number );
		} else {
			$extracted_segments = array_slice( $path_segments, $number );
		}

		$slash = $slash ? DIRECTORY_SEPARATOR : '';

		return $slash . implode( DIRECTORY_SEPARATOR, $extracted_segments ) . $slash;
	}
}
