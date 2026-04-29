<?php
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fwrite,WordPress.WP.AlternativeFunctions.file_system_operations_fclose,WordPress.WP.AlternativeFunctions.file_system_operations_fread,WordPress.WP.AlternativeFunctions.file_system_operations_is_writable,WordPress.WP.AlternativeFunctions.file_system_operations_touch -- This is a file-backed logger; the WP_Filesystem API has no streaming flock/seek primitives.

namespace Yoast\WP\Test_Helper\Logger;

/**
 * Stores log entries in a single append-only file.
 *
 * When the file grows beyond MAX_BYTES the oldest half is dropped on the next write.
 */
class File_Log_Storage implements Log_Storage {

	/**
	 * Maximum file size before the oldest half of the log is dropped, in bytes.
	 *
	 * @var int
	 */
	public const MAX_BYTES = 5_242_880;

	/**
	 * Read window used by tail() to find the last N lines, in bytes.
	 *
	 * @var int
	 */
	private const READ_CHUNK = 65_536;

	/**
	 * The absolute filesystem path to the log file.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Constructs the storage.
	 *
	 * @param string $path Absolute path to the log file.
	 */
	public function __construct( $path ) {
		$this->path = (string) $path;
	}

	/**
	 * Ensures the log file exists and is writable.
	 *
	 * @return void
	 */
	public function setup() {
		if ( $this->path === '' ) {
			return;
		}

		$directory = \dirname( $this->path );
		if ( ! \is_dir( $directory ) || ! \is_writable( $directory ) ) {
			return;
		}

		if ( ! \file_exists( $this->path ) ) {
			\touch( $this->path );
		}
	}

	/**
	 * Appends a log entry to the file, rotating the file in half when it overflows.
	 *
	 * @param string                                  $level     PSR-3 log level.
	 * @param string                                  $message   The interpolated message.
	 * @param array<string, scalar|array|object|null> $context   The original context array.
	 * @param int                                     $timestamp Unix timestamp at which the entry was captured.
	 *
	 * @return void
	 */
	public function write( $level, $message, array $context, $timestamp ) {
		if ( $this->path === '' ) {
			return;
		}

		// phpcs:ignore Yoast.Yoast.JsonEncodeAlternative.Found -- Compact storage; format_json_encode pretty-prints, which would inject newlines into the per-line file format.
		$encoded = \wp_json_encode(
			[
				'logged_at' => \gmdate( 'Y-m-d H:i:s', $timestamp ),
				'level'     => $level,
				'message'   => $message,
				'context'   => (object) $context,
			],
		);
		if ( $encoded === false ) {
			return;
		}

		$line = $encoded . "\n";

		// Open in 'cb+' so the file is created if missing, but the position is at the start; we seek to end before writing.
		// 'ab' would also open-for-append, but rotation needs a r/w handle that can both fseek+fread and ftruncate, so we use the same handle for both.
		$handle = \fopen( $this->path, 'cb+' );
		if ( $handle === false ) {
			return;
		}

		try {
			if ( ! \flock( $handle, \LOCK_EX ) ) {
				return;
			}

			try {
				\fseek( $handle, 0, \SEEK_END );
				\fwrite( $handle, $line );
				\fflush( $handle );

				// Rotation runs under the same lock so racing writers block until it's done.
				$size = (int) \ftell( $handle );
				if ( $size > self::MAX_BYTES ) {
					$this->rotate_locked( $handle, $size );
				}
			} finally {
				\flock( $handle, \LOCK_UN );
			}
		} finally {
			\fclose( $handle );
		}
	}

	/**
	 * Reads the most recent entries by tailing the file.
	 *
	 * @param int $limit Maximum number of entries to return.
	 *
	 * @return array<int, array{logged_at: string, level: string, message: string, context: array<string, scalar|array|object|null>}> The entries, newest first.
	 */
	public function read( $limit = 50 ) {
		if ( $this->path === '' || ! \is_readable( $this->path ) ) {
			return [];
		}

		$lines = $this->tail( (int) $limit );
		if ( $lines === [] ) {
			return [];
		}

		$entries = [];
		foreach ( $lines as $line ) {
			$line = \rtrim( $line, "\r\n" );
			if ( $line === '' ) {
				continue;
			}

			$decoded = \json_decode( $line, true );
			if ( ! \is_array( $decoded ) || ! isset( $decoded['message'] ) ) {
				$entries[] = [
					'logged_at' => '',
					'level'     => '',
					'message'   => $line,
					'context'   => [],
				];
				continue;
			}

			$context = [];
			if ( isset( $decoded['context'] ) && \is_array( $decoded['context'] ) ) {
				$context = $decoded['context'];
			}

			$entries[] = [
				'logged_at' => isset( $decoded['logged_at'] ) ? (string) $decoded['logged_at'] : '',
				'level'     => isset( $decoded['level'] ) ? \strtolower( (string) $decoded['level'] ) : '',
				'message'   => (string) $decoded['message'],
				'context'   => $context,
			];
		}

		return \array_reverse( $entries );
	}

	/**
	 * Empties the log file by truncating it under an exclusive lock.
	 *
	 * @return void
	 */
	public function clear() {
		if ( $this->path === '' || ! \file_exists( $this->path ) ) {
			return;
		}

		$handle = \fopen( $this->path, 'cb+' );
		if ( $handle === false ) {
			return;
		}

		try {
			if ( ! \flock( $handle, \LOCK_EX ) ) {
				return;
			}

			try {
				\ftruncate( $handle, 0 );
				\fflush( $handle );
			} finally {
				\flock( $handle, \LOCK_UN );
			}
		} finally {
			\fclose( $handle );
		}
	}

	/**
	 * Drops the oldest half of the file by rewriting the trailing half-window.
	 *
	 * Caller must already hold LOCK_EX on $handle. The handle is left open and locked on return.
	 *
	 * @param resource $handle Open r+ handle to the log file.
	 * @param int      $size   Current size of the file in bytes.
	 *
	 * @return void
	 */
	private function rotate_locked( $handle, $size ) {
		\fseek( $handle, (int) ( $size / 2 ) );
		// Drop the partial line at the cut point.
		\fgets( $handle );
		$keep = \stream_get_contents( $handle );
		if ( $keep === false ) {
			$keep = '';
		}

		\ftruncate( $handle, 0 );
		\rewind( $handle );
		\fwrite( $handle, $keep );
		\fflush( $handle );
	}

	/**
	 * Reads the last $limit lines from the file.
	 *
	 * Grows the read window until enough newlines are seen or the start of the file is reached.
	 *
	 * @param int $limit Maximum number of lines to return.
	 *
	 * @return string[] The lines, oldest first within the returned window.
	 */
	private function tail( $limit ) {
		$size = (int) \filesize( $this->path );
		if ( $size === 0 ) {
			return [];
		}

		$handle = \fopen( $this->path, 'rb' );
		if ( $handle === false ) {
			return [];
		}

		$buffer   = '';
		$lines    = [];
		$position = $size;
		$max_read = (int) self::MAX_BYTES;
		$bytes_in = 0;

		try {
			$line_count = 0;
			while ( $position > 0 && $line_count <= $limit && $bytes_in < $max_read ) {
				$chunk_size = (int) \min( self::READ_CHUNK, $position );
				$position  -= $chunk_size;
				\fseek( $handle, $position );
				$chunk      = (string) \fread( $handle, $chunk_size );
				$bytes_in  += $chunk_size;
				$buffer     = $chunk . $buffer;
				$lines      = \explode( "\n", $buffer );
				$line_count = \count( $lines );
			}
		} finally {
			\fclose( $handle );
		}

		// Drop trailing empty entry produced by a final newline.
		if ( $lines !== [] && \end( $lines ) === '' ) {
			\array_pop( $lines );
		}

		if ( \count( $lines ) > $limit ) {
			$lines = \array_slice( $lines, -$limit );
		}

		return $lines;
	}
}
