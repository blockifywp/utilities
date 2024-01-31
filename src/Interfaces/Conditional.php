<?php

declare( strict_types=1 );

namespace Blockify\Core\Interfaces;

interface Conditional {

	public static function condition(): bool;

}
