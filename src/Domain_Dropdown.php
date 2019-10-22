<?php
/**
 * Toggles features on and off based on feature flags.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Toggles the features on and off.
 */
class Domain_Dropdown implements Integration {

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
	 * Registers WordPress hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'admin_post_yoast_seo_domain_dropdown', array( $this, 'handle_submit' ) );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$select_options = array(
			'https://my.yoast.com/' => 'live',
			'https://staging-my.yoast.com' => 'staging',
			'https://staging-plugins-my.yoast.com/' => 'staging-plugins',
			'https://staging-platform-my.yoast.com/' => 'staging-platform',
			'http://my.yoast.test:3000' => 'local',
		);

		$output = Form_Presenter::create_select(
			'myyoast_test_domain',
			'Set the myYoast testing domain to: ',
			$select_options,
			$this->option->get( 'myyoast_test_domain' )
		);

		return Form_Presenter::get_html( 'Domain Dropdown', 'yoast_seo_domain_dropdown', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
			if ( check_admin_referer( 'yoast_seo_domain_dropdown' ) !== false ) {
			$this->option->set( 'myyoast_test_domain', $_POST['myyoast_test_domain'] );
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}
}
