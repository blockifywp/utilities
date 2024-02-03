<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use function debug_backtrace;
use function json_encode;

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
		$stacktrace = self::stacktrace();

		echo '<script>';
		//echo 'console.log("%cData", "color:blue");';
		echo 'console.log(' . json_encode( $data ) . ');';

		if ( $stacktrace ) {
			//echo 'console.log("%cStacktrace", "color:blue");';

			foreach ( $stacktrace as $trace ) {
				echo 'console.log(' . json_encode( $trace ) . ');';
			}
		}

		echo '</script>';
	}

	public static function stacktrace(): array {
		$backtrace  = debug_backtrace();
		$stacktrace = [];

		foreach ( $backtrace as $index => $trace ) {
			if ( ! isset( $trace['file'] ) || ! isset( $trace['line'] ) ) {
				continue;
			}

			if ( 0 === $index ) {
				continue;
			}

			$stacktrace[] = $trace['file'] . ': ' . $trace['line'] . "\n";
		}

		return $stacktrace;
	}

}
