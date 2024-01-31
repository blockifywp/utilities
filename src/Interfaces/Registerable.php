<?php

declare( strict_types=1 );

namespace Blockify\Core\Interfaces;

use Blockify\Core\Container;

interface Registerable {

	public function register( Container $container ): void;

}
