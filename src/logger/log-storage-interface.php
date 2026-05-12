<?php

namespace Yoast\WP\Test_Helper\Logger;

/**
 * Storage backend for captured log entries.
 */
interface Log_Storage {

	/**
	 * Prepares the backend for writing (creates the table, ensures the file exists, etc.).
	 *
	 * @return void
	 */
	public function setup();

	/**
	 * Persists a single log entry.
	 *
	 * @param string                                  $level     PSR-3 log level.
	 * @param string                                  $message   The interpolated message.
	 * @param array<string, scalar|array|object|null> $context   The original context array.
	 * @param int                                     $timestamp Unix timestamp at which the entry was captured.
	 *
	 * @return void
	 */
	public function write( $level, $message, array $context, $timestamp );

	/**
	 * Reads the most recent entries, newest first.
	 *
	 * @param int $limit Maximum number of entries to return.
	 *
	 * @return array<int, array{logged_at: string, level: string, message: string, context: array<string, scalar|array|object|null>}> The entries.
	 */
	public function read( $limit = 50 );

	/**
	 * Removes all entries from the backend.
	 *
	 * @return void
	 */
	public function clear();
}
