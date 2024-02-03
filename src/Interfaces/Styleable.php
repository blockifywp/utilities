<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Interfaces;

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
	 * @param Inlinable $styles Inlinable service.
	 *
	 * @return void
	 */
	public function styles( Inlinable $styles ): void;

}
