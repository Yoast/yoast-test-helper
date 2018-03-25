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
	 * Holds our options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->options = Option::get_option();
	}

	/**
	 * Add the required hooks
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'wpseo_admin_below_content', array( $this, 'show_debug_info' ), 90, 1 );

		add_action(
			'admin_post_yoast_seo_debug_settings',
			array( $this, 'handle_submit' )
		);

	}

	/**
	 * Shows debug info about the current option
	 *
	 * @param Yoast_Form $form Form instance.
	 *
	 * @return void
	 */
	public function show_debug_info( $form ) {
		if ( $this->options['show_options_debug'] ) {
			$xdebug = ( extension_loaded( 'xdebug' ) ? true : false );
			echo '<div id="wpseo-debug-info" class="yoast-container">';
			echo '<h2>Debug Information</h2>';
			echo '<div>';
			echo '<h3 class="wpseo-debug-heading">Current option: <span class="wpseo-debug">' . esc_html( $form->option_name ) . '</span></h3>';
			echo( ( $xdebug ) ? '' : '<pre>' );
			// @codingStandardsIgnoreLine.
			var_dump( $form->get_option() );
			echo( ( $xdebug ) ? '' : '</pre>' );
			echo '</div></div>';
		}
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

		$output .= '<input type="checkbox" ' . checked( $this->options['show_options_debug'], true, false ) . ' name="show_options_debug" id="show_options_debug"/> <label for="show_options_debug">Show options on admin screens for debugging.</label>';
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
			$this->options['show_options_debug'] = false;
			if ( isset( $_POST['show_options_debug'] ) ) {
				$this->options['show_options_debug'] = true;
			}
			Option::set_option( $this->options );
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}
}
