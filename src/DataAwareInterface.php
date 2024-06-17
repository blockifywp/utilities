<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

interface DataAwareInterface {

	/**
	 * Get data.
	 *
	 * @since 1.0.0
	 *
	 * @return Data
	 */
	public function get_data(): Data;

	/**
	 * Set data.
	 *
	 * @since 1.0.0
	 *
	 * @param Data $data Data.
	 *
	 * @return void
	 */
	public function set_data( Data $data ): void;

}
