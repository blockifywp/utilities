<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use function apply_filters;
use function rtrim;
use function str_replace;
use function trim;

/**
 * JS Utility.
 *
 * @since 1.0.0
 */
class JS {

	/**
	 * Formats inline JS.
	 *
	 * @since 1.0.0
	 *
	 * @param string $js JS.
	 *
	 * @return string
	 */
	public static function format_inline_js( string $js ): string {
		$js = str_replace( '"', "'", $js );
		$js = trim( rtrim( $js, ';' ) );
		$js = Str::reduce_whitespace( $js );
		$js = Str::remove_line_breaks( $js );

		/**
		 * Allows additional minification of inline JS. (Eg JShrink).
		 *
		 * @var string $js Formatted JS.
		 */
		$js = apply_filters( 'blockify_format_inline_js', $js );

		return $js;
	}
}
