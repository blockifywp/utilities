<?php

declare( strict_types=1 );

namespace Blockify\Core\Services;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Stringable;

class Logger extends AbstractLogger {

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
