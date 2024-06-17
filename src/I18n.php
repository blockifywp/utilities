<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use Blockify\Hooks\Hook;
use function load_plugin_textdomain;

/**
 * Class I18n.
 *
 * @since 1.0.0
 */
class I18n {

	private Data $data;

	/**
	 * I18n constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Data $data Plugin instance.
	 *
	 * @return void
	 */
	public function __construct( Data $data ) {
		$this->data = $data;
	}

	/**
	 * Register factory.
	 *
	 * @since 1.0.0
	 *
	 * @param Data $data Plugin instance.
	 *
	 * @return self
	 */
	public static function register( Data $data ): self {
		static $instances = [];

		if ( ! isset( $instances[ $data->slug ] ) ) {
			$instances[ $data->slug ] = new self( $data );

			Hook::annotations( $instances[ $data->slug ] );
		}

		return $instances[ $data->slug ];
	}

	/**
	 * Loads plugin textdomain.
	 *
	 * @since 1.0.0
	 *
	 * @hook  plugins_loaded
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			$this->data->slug,
			false,
			$this->data->dir . $this->data->domain_path
		);
	}

}
