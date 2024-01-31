<?php

declare( strict_types=1 );

namespace Blockify\Core\Interfaces;

use Blockify\Core\Services\Styles;

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
	 * @param Styles $styles Styles instance.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void;

}
