<?php

declare( strict_types=1 );

namespace Blockify\Utilities;

use Blockify\Utilities\Interfaces\Instantiable;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Stringable;

/**
 * Logger.
 *
 * @since 0.1.0
 */
class Logger extends AbstractLogger implements Instantiable {

	/**
	 * Log.
	 *
	 * @var array
	 */
	private array $log = [];

	/**
	 * Logs with an arbitrary level.
	 *
	 * @throws InvalidArgumentException
	 *
	 * @param mixed             $level
	 * @param string|Stringable $message
	 * @param mixed[]           $context
	 *
	 * @return void
	 *
	 */
	public function log( $level, $message, $context = [] ): void {
		$this->log[] = [
			'level'   => $level,
			'message' => $message,
			'context' => $context,
		];
	}

	/**
	 * @return array
	 */
	public function get_log(): array {
		return $this->log;
	}
}
