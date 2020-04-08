<?php

namespace Yoast\WP\Test_Helper;

use Debug_Bar_Panel;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Admin_Debug_Info implements Integration {

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
	 * Add the required hooks
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_filter( 'debug_bar_panels', [ $this, 'add_debug_panel' ] );

		\add_action(
			'admin_post_yoast_seo_debug_settings',
			[ $this, 'handle_submit' ]
		);
	}

	/**
	 * Makes the debug info appear in a Debug Bar panel.
	 *
	 * @param Debug_Bar_Panel[] $panels Existing debug bar panels.
	 *
	 * @return Debug_Bar_Panel[] Panels array.
	 */
	public function add_debug_panel( $panels ) {
		if ( $this->option->get( 'show_options_debug' ) === true && \defined( 'WPSEO_VERSION' ) ) {
			$panels[] = new Admin_Bar_Panel();
		}

		return $panels;
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'show_options_debug',
			'Add Yoast SEO panel to <a href="https://wordpress.org/plugins/debug-bar/">Debug Bar</a>.',
			$this->option->get( 'show_options_debug' )
		);

		return Form_Presenter::get_html( 'Debug Bar integration', 'yoast_seo_debug_settings', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_debug_settings' ) !== false ) {
			$this->option->set( 'show_options_debug', isset( $_POST['show_options_debug'] ) );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}
}
