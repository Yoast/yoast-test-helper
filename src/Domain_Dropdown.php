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

		$domain = $this->option->get('myyoast_test_domain');
		if ( $domain !== null && $domain !== 'https://my.yoast.com/') {
			add_action( 'requests-requests.before_request', array ( $this, 'modify_myyoast_request' ), 10, 2 );
		} else {
			remove_action( 'requests-requests.before_request', array ( $this, 'modify_myyoast_request' ), 10 );
		}
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$select_options = array(
			'my.yoast.com/' => 'live',
			'staging-my.yoast.com' => 'staging',
			'staging-plugins-my.yoast.com/' => 'staging-plugins',
			'staging-platform-my.yoast.com/' => 'staging-platform',
			'my.yoast.test:3000' => 'local',
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

	/**
	 * If a testing domain is set, modify any request to myYoast to go to the testing domain.
	 *
	 * Attached to the `requests-requests.before_request` filter.
	 * @param string &$url URL of request about to be made
	 * @param array  &$headers Headers of request about to be made
	 * @return void
	 */
	public function modify_myyoast_request( &$url, &$headers ) {
		$domain = $this->option->get( 'myyoast_test_domain' );
		if ( ! $domain || $domain === 'https://my.yoast.com/') {
			return;
		}

		$original_url = $url;
		$request_parameters = $this->replace_domain( $domain, $url, $headers );
		$url = $request_parameters['url'];

		if ( $request_parameters['host'] ) {
			$headers['Host'] = $request_parameters['host'];
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( "SANDBOXING via '%s': '%s'", $domain, $original_url ) );
			}
		}
	}

	/**
	 * @param string $domain Testing domain to take place in the request.
	 * @param string $url URL of request about to be made
	 * @param array  $headers Headers of request about to be made
	 * @return array [ 'url' => new URL, 'host' => new Host ]
	 */
	function replace_domain( $domain, $url, $headers ) {
		$host = '';
		$url_host = wp_parse_url( $url, PHP_URL_HOST );

		switch ( $url_host ) {
			case 'my.yoast.com' :
			case 'my.yoast.test:3000' :
				$host = isset( $headers['Host'] ) ? $headers['Host'] : $url_host;
				$url = preg_replace(
					'@^(https?://)' . preg_quote( $url_host, '@' ) . '(?=[/?#].*|$)@',
					'\\1' . $domain,
					$url,
					1
				);
		}
		return compact( 'url', 'host' );
	}
}

