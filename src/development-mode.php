<?php

namespace Yoast\WP\Test_Helper;

/**
 * Shows admin notifications on the proper page.
 */
class Development_Mode implements Integration {

	/**
	 * Holds our option instance.
	 *
	 * @var Option
	 */
	private $option;

	/**
	 * Class constructor.
	 *
	 * @param Option $option Our option array.
	 */
	public function __construct( Option $option ) {
		$this->option = $option;
	}

	/**
	 * Enabling this plugin means you are in development mode.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( $this->option->get( 'enable_development_mode' ) ) {
			\add_filter( 'yoast_seo_development_mode', '__return_true' );
		}
		if ( $this->option->get( 'use_ai_staging_api' ) ) {
			\add_filter( 'Yoast\WP\SEO\ai_api_url', [ $this, 'use_ai_staging_api' ] );
		}

		\add_action( 'admin_post_yoast_seo_test_development_mode', [ $this, 'handle_submit' ] );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'enable_development_mode',
			\esc_html__( 'Enable development mode.', 'yoast-test-helper' ),
			$this->option->get( 'enable_development_mode' )
		);

		$fields .= Form_Presenter::create_checkbox(
			'use_ai_staging_api',
			\esc_html__( 'Switch to AI staging API', 'yoast-test-helper' ),
			$this->option->get( 'use_ai_staging_api' )
		);

		return Form_Presenter::get_html( \__( 'Development settings', 'yoast-test-helper' ), 'yoast_seo_test_development_mode', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_development_mode' ) !== false ) {
			$this->set_bool_option( 'enable_development_mode' );
			$this->set_bool_option( 'use_ai_staging_api' );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Uses the AI staging API.
	 *
	 * @return string
	 */
	public function use_ai_staging_api() {
		return 'https://ai-staging.yoa.st/api/v1';
	}

	/**
	 * Sets a boolean option based on a POST parameter.
	 *
	 * @param string $option The option to check and set.
	 *
	 * @return void
	 */
	private function set_bool_option( $option ) {
		// The nonce is checked in the handle_submit function.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$this->option->set( $option, isset( $_POST[ $option ] ) );
	}
}
