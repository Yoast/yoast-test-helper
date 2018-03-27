<?php
/**
 * Admin page hander.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

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
	 */
	public function __construct() {
		$this->option = new Option();
	}

	/**
	 * Add the required hooks
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_filter( 'debug_bar_panels', array( $this, 'add_debug_panel' ) );

		add_action(
			'admin_post_yoast_seo_debug_settings',
			array( $this, 'handle_submit' )
		);
	}

	/**
	 * Makes the debug info appear in a Debug Bar panel.
	 *
	 * @param array $panels Existing debug bar panels.
	 *
	 * @return array Panels array.
	 */
	public function add_debug_panel( $panels ) {
		if ( $this->option->get( 'show_options_debug' ) === true ) {
			require_once 'Yoast_SEO_Admin_Bar_Debug_Panel.php';
			$panels[] = new \Yoast_SEO_Admin_Bar_Debug_Panel();
		}
		return $panels;
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$output  = '<h2>Debug info</h2>';
		$output .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= wp_nonce_field( 'debug_settings', '_wpnonce', true, false );
		$output .= '<input type="hidden" name="action" value="yoast_seo_debug_settings">';

		$output .= '<input type="checkbox" ' . checked( $this->option->get( 'show_options_debug' ), true, false ) . ' name="show_options_debug" id="show_options_debug"/> <label for="show_options_debug">Show options on admin screens for debugging.</label>';
		$output .= '<br/><br/>';
		$output .= '<button class="button button-primary">Save</button>';
		$output .= '</form>';

		return $output;
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( check_admin_referer( 'debug_settings' ) !== false ) {
			$this->option->set( 'show_options_debug', isset( $_POST['show_options_debug'] ) );
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}
}
