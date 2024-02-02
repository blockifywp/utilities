<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Interfaces;

/**
 * Scriptable interface.
 *
 * @since 1.0.0
 */
interface Scriptable {

	/**
	 * Register scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_html Template HTML.
	 *
	 * @return string
	 */
	public function scripts( string $template_html ): string;

}
