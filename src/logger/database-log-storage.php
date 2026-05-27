<?php

namespace Yoast\WP\Test_Helper\Logger;

use Yoast\WP\Test_Helper\Option;

/**
 * Stores log entries in a dedicated custom table.
 */
class Database_Log_Storage implements Log_Storage {

	/**
	 * Suffix for the custom table, appended to the WordPress table prefix.
	 *
	 * @var string
	 */
	public const TABLE_SUFFIX = 'yoast_test_helper_log';

	/**
	 * Schema version, used to short-circuit dbDelta on subsequent calls.
	 *
	 * @var string
	 */
	public const SCHEMA_VERSION = '1';

	/**
	 * Default maximum number of entries kept in the table.
	 *
	 * Callers can override the effective cap via the `Yoast\WP\Test_Helper\logger_max_rows` filter.
	 *
	 * @var int
	 */
	public const DEFAULT_MAX_ROWS = 1000;

	/**
	 * Option key tracking the last applied schema version.
	 *
	 * @var string
	 */
	private const SCHEMA_OPTION = 'logger_db_version';

	/**
	 * The shared option store, used to remember the applied schema version.
	 *
	 * @var Option
	 */
	private $option;

	/**
	 * Constructs the storage.
	 *
	 * @param Option $option The shared option store.
	 */
	public function __construct( Option $option ) {
		$this->option = $option;
	}

	/**
	 * Creates or upgrades the log table when the stored schema version is out of date.
	 *
	 * @return void
	 */
	public function setup() {
		if ( $this->option->get( self::SCHEMA_OPTION ) === self::SCHEMA_VERSION ) {
			return;
		}

		require_once \ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;
		$table_name      = $this->get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			logged_at DATETIME NOT NULL,
			level VARCHAR(16) NOT NULL,
			message TEXT NOT NULL,
			context LONGTEXT NULL,
			PRIMARY KEY  (id),
			KEY logged_at (logged_at),
			KEY level (level)
		) {$charset_collate};";

		\dbDelta( $sql );

		$this->option->set( self::SCHEMA_OPTION, self::SCHEMA_VERSION );
	}

	/**
	 * Inserts a log entry and trims the table to MAX_ROWS.
	 *
	 * @param string                                  $level     PSR-3 log level.
	 * @param string                                  $message   The interpolated message.
	 * @param array<string, scalar|array|object|null> $context   The original context array.
	 * @param int                                     $timestamp Unix timestamp at which the entry was captured.
	 *
	 * @return void
	 */
	public function write( $level, $message, array $context, $timestamp ) {
		global $wpdb;
		$table_name = $this->get_table_name();

		$wpdb->insert(
			$table_name,
			[
				'logged_at' => \gmdate( 'Y-m-d H:i:s', $timestamp ),
				'level'     => $level,
				'message'   => $message,
				// phpcs:ignore Yoast.Yoast.JsonEncodeAlternative.Found -- Compact storage; format_json_encode pretty-prints, which bloats the row.
				'context'   => \wp_json_encode( $context ),
			],
			[ '%s', '%s', '%s', '%s' ],
		);

		$max_rows = (int) \apply_filters( 'Yoast\WP\Test_Helper\logger_max_rows', self::DEFAULT_MAX_ROWS );
		if ( $max_rows < 1 ) {
			return;
		}

		// Trim using a derived subquery — MySQL cannot otherwise reference the target table in a DELETE subquery.
		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM %i WHERE id NOT IN (
					SELECT id FROM ( SELECT id FROM %i ORDER BY id DESC LIMIT %d ) AS keep_ids
				)',
				$table_name,
				$table_name,
				$max_rows,
			),
		);
	}

	/**
	 * Reads the most recent entries, newest first.
	 *
	 * @param int $limit Maximum number of entries to return.
	 *
	 * @return array<int, array{logged_at: string, level: string, message: string, context: array<string, scalar|array|object|null>}> The entries.
	 */
	public function read( $limit = 50 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT logged_at, level, message, context FROM %i ORDER BY id DESC LIMIT %d',
				$this->get_table_name(),
				(int) $limit,
			),
			\ARRAY_A,
		);

		if ( ! \is_array( $rows ) ) {
			return [];
		}

		return \array_map( [ self::class, 'hydrate_row' ], $rows );
	}

	/**
	 * Empties the log table.
	 *
	 * @return void
	 */
	public function clear() {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare( 'TRUNCATE TABLE %i', $this->get_table_name() ),
		);
	}

	/**
	 * Converts a database row into the entry shape returned by read().
	 *
	 * @param array<string, scalar|array|object|null> $row The database row.
	 *
	 * @return array{logged_at: string, level: string, message: string, context: array<string, scalar|array|object|null>} The entry.
	 */
	private static function hydrate_row( array $row ) {
		$context = [];
		if ( isset( $row['context'] ) && $row['context'] !== '' ) {
			$decoded = \json_decode( (string) $row['context'], true );
			if ( \is_array( $decoded ) ) {
				$context = $decoded;
			}
		}

		return [
			'logged_at' => (string) $row['logged_at'],
			'level'     => (string) $row['level'],
			'message'   => (string) $row['message'],
			'context'   => $context,
		];
	}

	/**
	 * Builds the fully qualified table name.
	 *
	 * @return string The prefixed table name.
	 */
	private function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_SUFFIX;
	}
}
