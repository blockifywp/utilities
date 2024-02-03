<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

/**
 * Class Debug.
 *
 * @since 1.0.0
 */
class Debug {

	/**
	 * Log data to the console.
	 *
	 * @param mixed $data Data to log.
	 *
	 * @return void
	 */
	public static function console_log( $data ): void {
		echo '<script>';
		echo 'console.log(' . json_encode( $data ) . ')';
		echo '</script>';
	}

}
