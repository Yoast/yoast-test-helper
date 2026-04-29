<?php

namespace Yoast\WP\Test_Helper\Logger;

use Throwable;
use YoastSEO_Vendor\Psr\Log\AbstractLogger;
use YoastSEO_Vendor\Psr\Log\LogLevel;

/**
 * PSR-3 logger that forwards entries above a configured threshold to a Log_Storage backend.
 *
 * Extends the prefixed PSR-3 classes shipped with Yoast SEO (YoastSEO_Vendor\Psr\Log) rather than
 * stock psr/log, so the resulting object satisfies the interface that Yoast SEO's wpseo_logger
 * filter is typed against. Loading this class therefore requires Yoast SEO to be active.
 */
class Test_Helper_Logger extends AbstractLogger {

	/**
	 * PSR-3 levels mapped to RFC 5424 severities for threshold comparison.
	 *
	 * @var array<string, int>
	 */
	private const LEVELS = [
		LogLevel::DEBUG     => 0,
		LogLevel::INFO      => 1,
		LogLevel::NOTICE    => 2,
		LogLevel::WARNING   => 3,
		LogLevel::ERROR     => 4,
		LogLevel::CRITICAL  => 5,
		LogLevel::ALERT     => 6,
		LogLevel::EMERGENCY => 7,
	];

	/**
	 * The backend writes are forwarded to.
	 *
	 * @var Log_Storage
	 */
	private $storage;

	/**
	 * Numeric severity below which entries are dropped.
	 *
	 * @var int
	 */
	private $threshold;

	/**
	 * Constructs the logger.
	 *
	 * @param Log_Storage $storage   The backend writes are forwarded to.
	 * @param string      $threshold PSR-3 level name; entries below this are dropped.
	 */
	public function __construct( Log_Storage $storage, $threshold ) {
		$this->storage   = $storage;
		$this->threshold = ( self::LEVELS[ $threshold ] ?? self::LEVELS[ LogLevel::DEBUG ] );
	}

	/**
	 * Returns the list of supported PSR-3 level names, debug → emergency.
	 *
	 * @return string[] The level names.
	 */
	public static function levels() {
		return \array_keys( self::LEVELS );
	}

	/**
	 * Logs a single entry.
	 *
	 * @param string                                  $level   PSR-3 log level.
	 * @param string                                  $message The message, with optional `{placeholder}` tokens.
	 * @param array<string, scalar|array|object|null> $context Context replacements and supplementary data.
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = [] ) {
		$level_name = (string) $level;
		if ( ! isset( self::LEVELS[ $level_name ] ) ) {
			return;
		}

		if ( self::LEVELS[ $level_name ] < $this->threshold ) {
			return;
		}

		$interpolated = self::interpolate( (string) $message, $context );

		try {
			$this->storage->write( $level_name, $interpolated, $context, \time() );
		} catch ( Throwable $e ) {
			if ( \defined( 'WP_DEBUG' ) && \WP_DEBUG ) {
				\error_log( 'Yoast Test Helper logger failed: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}

	/**
	 * Substitutes `{placeholder}` tokens in the message per PSR-3 §1.2.
	 *
	 * Only scalar and `__toString`-able context values are substituted; other placeholders are left intact.
	 *
	 * @param string                                  $message The raw message.
	 * @param array<string, scalar|array|object|null> $context The context array.
	 *
	 * @return string The interpolated message.
	 */
	private static function interpolate( $message, array $context ) {
		if ( \strpos( $message, '{' ) === false ) {
			return $message;
		}

		return (string) \preg_replace_callback(
			'/\{([A-Za-z0-9_.]+)\}/',
			static function ( array $matches ) use ( $context ) {
				return self::resolve_placeholder( $matches, $context );
			},
			$message,
		);
	}

	/**
	 * Resolves a single `{placeholder}` match against the context array.
	 *
	 * Per PSR-3 §1.2 scalar and `__toString`-able values substitute directly. Non-scalar values
	 * (arrays, plain objects, DateTime, etc.) get JSON-encoded so they still produce a useful
	 * message — Monolog's LineFormatter does the same.
	 *
	 * @param array<int, string>                      $matches The regex match: full match, then the captured key.
	 * @param array<string, scalar|array|object|null> $context The context array.
	 *
	 * @return string The replacement, or the original placeholder when nothing fits.
	 */
	private static function resolve_placeholder( array $matches, array $context ) {
		$key = $matches[1];
		if ( ! \array_key_exists( $key, $context ) ) {
			return $matches[0];
		}

		$value = $context[ $key ];
		if ( $value === null || \is_scalar( $value ) ) {
			return (string) $value;
		}

		if ( \is_object( $value ) && \method_exists( $value, '__toString' ) ) {
			return (string) $value;
		}

		// phpcs:ignore Yoast.Yoast.JsonEncodeAlternative.Found -- Inline interpolation needs compact JSON; format_json_encode pretty-prints, which breaks the single-line file format.
		$encoded = \wp_json_encode( $value );
		if ( $encoded !== false ) {
			return $encoded;
		}

		return $matches[0];
	}
}
