<?php

declare( strict_types=1 );

namespace Blockify\Core\Interfaces;

use Blockify\Core\Services\InlineStyles;

/**
 * Styleable interface.
 *
 * @since 1.0.0
 */
interface Styleable {

	/**
	 * Register styles.
	 *
	 * @since 1.0.0
	 *
	 * @param InlineStyles $styles Styles instance.
	 *
	 * @return void
	 */
	public function styles( InlineStyles $styles ): void;

}
