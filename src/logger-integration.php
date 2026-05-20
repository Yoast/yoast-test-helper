<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\Test_Helper\Logger\Database_Log_Storage;
use Yoast\WP\Test_Helper\Logger\Test_Helper_Logger;

/**
 * Captures logs forwarded through Yoast SEO's wpseo_logger filter to a database-backed store.
 */
class Logger_Integration implements Integration {

	/**
	 * Nonce action for the settings form.
	 *
	 * @var string
	 */
	private const SETTINGS_ACTION = 'yoast_test_helper_logger_settings';

	/**
	 * Nonce action for the clear button form.
	 *
	 * @var string
	 */
	private const CLEAR_ACTION = 'yoast_test_helper_logger_clear';

	/**
	 * Maximum number of entries shown in the popover viewer.
	 *
	 * @var int
	 */
	private const VIEWER_LIMIT = 1000;

	/**
	 * The shared option store.
	 *
	 * @var Option
	 */
	private $option;

	/**
	 * Constructs the integration.
	 *
	 * @param Option $option The shared option store.
	 */
	public function __construct( Option $option ) {
		$this->option = $option;
	}

	/**
	 * Registers WordPress hooks.
	 *
	 * The wpseo_logger filter is only registered when the prefixed PSR-3 classes shipped by
	 * Yoast SEO are actually loaded — otherwise our logger class cannot be instantiated.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( $this->option->get( 'logger_enabled' ) && \class_exists( '\YoastSEO_Vendor\Psr\Log\AbstractLogger' ) ) {
			\add_filter( 'wpseo_logger', [ $this, 'provide_logger' ] );
		}

		\add_action( 'admin_post_' . self::SETTINGS_ACTION, [ $this, 'handle_submit' ] );
		\add_action( 'admin_post_' . self::CLEAR_ACTION, [ $this, 'handle_clear' ] );
	}

	/**
	 * Provides the logger to Yoast SEO via the wpseo_logger filter.
	 *
	 * Sets up the storage lazily so the table is only materialized when Yoast SEO
	 * actually requests the logger.
	 *
	 * @return Test_Helper_Logger The replacement logger.
	 */
	public function provide_logger() {
		$storage = $this->get_storage();
		$storage->setup();

		return new Test_Helper_Logger( $storage, $this->get_threshold() );
	}

	/**
	 * Renders the admin block.
	 *
	 * @return string The HTML to render.
	 */
	public function get_controls() {
		$level_options = [];
		foreach ( Test_Helper_Logger::levels() as $level ) {
			$level_options[ $level ] = \esc_html( \ucfirst( $level ) );
		}

		$fields  = Form_Presenter::create_checkbox(
			'logger_enabled',
			\esc_html__( 'Enable the Yoast SEO logger.', 'yoast-test-helper' ),
			(bool) $this->option->get( 'logger_enabled' ),
		);
		$fields .= Form_Presenter::create_select(
			'logger_level',
			\esc_html__( 'Minimum level captured: ', 'yoast-test-helper' ),
			$level_options,
			$this->get_threshold(),
		);

		$output  = Form_Presenter::get_html(
			\__( 'Logger', 'yoast-test-helper' ),
			self::SETTINGS_ACTION,
			$fields,
		);
		$output .= $this->render_viewer();

		return $output;
	}

	/**
	 * Persists the settings form.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( self::SETTINGS_ACTION ) === false ) {
			$this->redirect_back();
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified above.
		$enabled = isset( $_POST['logger_enabled'] );

		$level = isset( $_POST['logger_level'] ) ? \sanitize_key( \wp_unslash( $_POST['logger_level'] ) ) : 'debug';
		if ( ! \in_array( $level, Test_Helper_Logger::levels(), true ) ) {
			$level = 'debug';
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$this->option->set( 'logger_enabled', $enabled );
		$this->option->set( 'logger_level', $level );

		$this->redirect_back();
	}

	/**
	 * Empties the log table.
	 *
	 * @return void
	 */
	public function handle_clear() {
		if ( \check_admin_referer( self::CLEAR_ACTION ) === false ) {
			$this->redirect_back();
			return;
		}

		$storage = $this->get_storage();
		$storage->setup();
		$storage->clear();

		\do_action(
			'Yoast\WP\Test_Helper\notification',
			new Notification( \esc_html__( 'Cleared all captured logs.', 'yoast-test-helper' ), 'success' ),
		);

		$this->redirect_back();
	}

	/**
	 * Builds the database storage instance.
	 *
	 * @return Database_Log_Storage The storage.
	 */
	private function get_storage() {
		return new Database_Log_Storage( $this->option );
	}

	/**
	 * Returns the configured level threshold, falling back to debug.
	 *
	 * @return string The PSR-3 level name.
	 */
	private function get_threshold() {
		$level = (string) $this->option->get( 'logger_level' );
		if ( ! \in_array( $level, Test_Helper_Logger::levels(), true ) ) {
			return 'debug';
		}

		return $level;
	}

	/**
	 * Builds the viewer HTML: a compact summary, a "View log entries" button that opens a popover, and the Clear form.
	 *
	 * @return string The viewer HTML.
	 */
	private function render_viewer() {
		$storage = $this->get_storage();
		$storage->setup();

		// Read VIEWER_LIMIT + 1 so we can detect overflow and show "1000+" without counting the whole backend.
		$entries  = $storage->read( self::VIEWER_LIMIT + 1 );
		$overflow = \count( $entries ) > self::VIEWER_LIMIT;
		if ( $overflow ) {
			$entries = \array_slice( $entries, 0, self::VIEWER_LIMIT );
		}
		$shown = \count( $entries );

		$output  = '<h3>' . \esc_html__( 'Recent logs', 'yoast-test-helper' ) . '</h3>';
		$output .= '<p>' . \esc_html( $this->captured_summary( $shown, $overflow ) ) . '</p>';

		$output .= '<div style="display:flex;gap:1.5em;align-items:center;">';
		if ( $shown > 0 ) {
			$output .= '<button type="button" class="button-link" popovertarget="yoast_test_helper_logger_popover">'
				. \esc_html__( 'View captured logs', 'yoast-test-helper' )
				. '</button>';
		}

		$output .= '<form action="' . \esc_url( \admin_url( 'admin-post.php' ) ) . '" method="POST" style="display:inline;">';
		$output .= \str_replace( 'id="_wpnonce"', '', \wp_nonce_field( self::CLEAR_ACTION, '_wpnonce', true, false ) );
		$output .= '<input type="hidden" name="action" value="' . \esc_attr( self::CLEAR_ACTION ) . '">';
		$output .= '<button class="button-link button-link-delete" type="submit">' . \esc_html__( 'Delete all captured logs', 'yoast-test-helper' ) . '</button>';
		$output .= '</form>';
		$output .= '</div>';

		if ( $shown > 0 ) {
			$output .= $this->render_viewer_popover( $entries );
		}

		return $output;
	}

	/**
	 * Builds the "N logs captured" summary, marking the count as a lower bound when overflow was hit.
	 *
	 * @param int  $shown    Number of entries returned (capped at VIEWER_LIMIT).
	 * @param bool $overflow True when the backend has more entries than the viewer cap.
	 *
	 * @return string The translated summary text.
	 */
	private function captured_summary( $shown, $overflow ) {
		if ( $overflow ) {
			return \sprintf(
				/* translators: %d: viewer cap; the actual count is at least this many. */
				\__( '%d+ logs captured. Showing the most recent ones.', 'yoast-test-helper' ),
				self::VIEWER_LIMIT,
			);
		}

		return \sprintf(
			/* translators: %d: number of captured logs. */
			\_n( '%d log captured.', '%d logs captured.', $shown, 'yoast-test-helper' ),
			$shown,
		);
	}

	/**
	 * Builds the popover element with the full table inside.
	 *
	 * Uses the native HTML popover API: clicking the button (popovertarget) opens it, ESC and click-outside close it,
	 * the close button uses popovertargetaction="hide". No JavaScript required.
	 *
	 * @param array<int, array{logged_at: string, level: string, message: string, context: array<string, scalar|array|object|null>}> $entries The entries to render.
	 *
	 * @return string The popover HTML.
	 */
	private function render_viewer_popover( array $entries ) {
		$output  = '<div id="yoast_test_helper_logger_popover" popover style="width:90vw;max-width:1400px;height:90vh;padding:1em;border:1px solid #c3c4c7;">';
		$output .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5em;">';
		$output .= '<h2 style="margin:0;">' . \esc_html__( 'Yoast SEO logs', 'yoast-test-helper' ) . '</h2>';
		$output .= '<button type="button" class="button" popovertarget="yoast_test_helper_logger_popover" popovertargetaction="hide">'
			. \esc_html__( 'Close', 'yoast-test-helper' )
			. '</button>';
		$output .= '</div>';

		$output .= '<div style="overflow:auto;height:calc(90vh - 4em);">';
		$output .= '<table class="widefat striped" style="table-layout:auto;">';
		$output .= '<thead><tr>';
		$output .= '<th style="width:11em;">' . \esc_html__( 'Time (UTC)', 'yoast-test-helper' ) . '</th>';
		$output .= '<th style="width:6em;">' . \esc_html__( 'Level', 'yoast-test-helper' ) . '</th>';
		$output .= '<th>' . \esc_html__( 'Message', 'yoast-test-helper' ) . '</th>';
		$output .= '<th style="width:30%;">' . \esc_html__( 'Context', 'yoast-test-helper' ) . '</th>';
		$output .= '</tr></thead><tbody>';

		foreach ( $entries as $entry ) {
			$context_json = '';
			if ( ! empty( $entry['context'] ) ) {
				// phpcs:ignore Yoast.Yoast.JsonEncodeAlternative.FoundWithAdditionalParams -- Pretty-printed output is needed for the admin viewer; format_json_encode does not accept flags.
				$context_json = (string) \wp_json_encode( $entry['context'], ( \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES ) );
			}

			$output .= '<tr>';
			$output .= '<td>' . \esc_html( $entry['logged_at'] ) . '</td>';
			$output .= '<td>' . \esc_html( \strtoupper( $entry['level'] ) ) . '</td>';
			$output .= '<td style="word-break:break-word;">' . \esc_html( $entry['message'] ) . '</td>';
			$output .= '<td>';
			if ( $context_json !== '' ) {
				$output .= '<pre style="margin:0;white-space:pre-wrap;word-break:break-word;">' . \esc_html( $context_json ) . '</pre>';
			}
			$output .= '</td>';
			$output .= '</tr>';
		}

		$output .= '</tbody></table>';
		$output .= '</div></div>';

		return $output;
	}

	/**
	 * Sends the user back to the test helper admin page.
	 *
	 * @return void
	 */
	private function redirect_back() {
		\wp_safe_redirect(
			\self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ),
		);
	}
}
