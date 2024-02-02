<?php

declare( strict_types=1 );

namespace Blockify\Core\Services;

use Blockify\Core\Data\Plugin;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Utilities\Interfaces\Hookable;

/**
 * Class I18n.
 *
 * @since 1.0.0
 */
class I18n implements Hookable {

	use HookAnnotations;

	private Plugin $plugin;

	/**
	 * I18n constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Plugin $plugin Plugin instance.
	 *
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
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
			$this->plugin->slug,
			false,
			$this->plugin->dir . $this->plugin->domain_path
		);
	}

}
