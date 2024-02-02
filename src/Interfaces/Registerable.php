<?php

declare( strict_types=1 );

namespace Blockify\Utilities\Interfaces;

use Blockify\Core\Container;

interface Registerable {

	public function register( Container $container ): void;

}
