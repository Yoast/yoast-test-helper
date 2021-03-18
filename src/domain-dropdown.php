<?php

namespace Yoast\WP\Test_Helper;

/**
 * Sends myYoast requests to a chosen testing domain.
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
		\add_action( 'admin_post_yoast_seo_domain_dropdown', [ $this, 'handle_submit' ] );

		$domain = $this->option->get( 'myyoast_test_domain' );
		if ( ! empty( $domain ) && $domain !== 'https://my.yoast.com' ) {
			\add_action( 'requests-requests.before_request', [ $this, 'modify_myyoast_request' ], 10, 2 );
		}
		else {
			\remove_action( 'requests-requests.before_request', [ $this, 'modify_myyoast_request' ], 10 );
		}
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$select_options = [
			'https://my.yoast.com'                  => 'live',
			'https://staging-my.yoast.com'          => 'staging',
			'https://staging-plugins-my.yoast.com'  => 'staging-plugins',
			'https://staging-platform-my.yoast.com' => 'staging-platform',
			'http://my.yoast.test:3000'             => 'local',
		];

		$output = Form_Presenter::create_select(
			'myyoast_test_domain',
			\esc_html__( 'Set the myYoast testing domain to: ', 'yoast-test-helper' ),
			$select_options,
			$this->option->get( 'myyoast_test_domain' )
		);

		return Form_Presenter::get_html( \__( 'Domain Dropdown', 'yoast-test-helper' ), 'yoast_seo_domain_dropdown', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_domain_dropdown' ) !== false ) {
			$this->option->set( 'myyoast_test_domain', \filter_input( \INPUT_POST, 'myyoast_test_domain', \FILTER_SANITIZE_STRING ) );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * If a testing domain is set, modify any request to myYoast to go to the testing domain.
	 * Attached to the `requests-requests.before_request` filter.
	 *
	 * @param string $url     URL of the request about to be made.
	 * @param array  $headers Headers of the request about to be made.
	 * @return void
	 */
	public function modify_myyoast_request( &$url, &$headers ) {
		$domain = $this->option->get( 'myyoast_test_domain' );

		if ( empty( $domain ) || $domain === 'https://my.yoast.com' ) {
			return;
		}

		$original_url       = $url;
		$request_parameters = $this->replace_domain( $domain, $url, $headers );
		$url                = $request_parameters['url'];

		if ( $request_parameters['host'] ) {
			$headers['Host'] = $request_parameters['host'];
			if ( \defined( 'WP_DEBUG' ) && \WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				\error_log( \sprintf( "SANDBOXING via '%s': '%s'", $domain, $original_url ) );
			}
		}
	}

	/**
	 * Replace the domain of the url with the passed domain for my-yoast urls.
	 *
	 * @param string $domain  Testing domain to take place in the request.
	 * @param string $url     URL of request about to be made.
	 * @param array  $headers Headers of request about to be made.
	 * @return array [ 'url' => new URL, 'host' => new Host ]
	 */
	private function replace_domain( $domain, $url, $headers ) {
		$host     = '';
		$url_host = \wp_parse_url( $url, \PHP_URL_HOST );
		$new_host = \wp_parse_url( $domain, \PHP_URL_HOST );

		if ( $url_host === 'my.yoast.com' ) {
			$host = isset( $headers['Host'] ) ? $headers['Host'] : $new_host;
			$url  = \str_replace( 'https://' . $url_host, $domain, $url );
		}

		return [
			'url'  => $url,
			'host' => $host,
		];
	}
}

