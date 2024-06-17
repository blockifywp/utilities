<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

/**
 * Data trait.
 *
 * @since 1.0.0
 */
trait DataTrait {

	/**
	 * Data.
	 *
	 * @since 1.0.0
	 *
	 * @var ?Data
	 */
	protected ?Data $data = null;

	/**
	 * Get data.
	 *
	 * @since 1.0.0
	 *
	 * @return Data
	 */
	public function get_data(): Data {
		return $this->data;
	}

	/**
	 * Set data.
	 *
	 * @since 1.0.0
	 *
	 * @param Data $data Data.
	 *
	 * @return void
	 */
	public function set_data( Data $data ): void {
		$this->data = $data;
	}

}
