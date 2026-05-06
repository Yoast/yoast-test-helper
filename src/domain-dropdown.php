<?php

namespace Yoast\WP\Test_Helper;

/**
 * Sends myYoast requests to a chosen testing domain.
 *
 * Owns the active MyYoast environment selection for the entire test helper.
 * Other integrations (e.g. MyYoast_OAuth_Overrides) read the selected domain
 * via {@see self::get_active_domain()} so that the dropdown stays the single
 * source of truth for "which environment am I pointed at?".
 */
class Domain_Dropdown implements Integration {

	/**
	 * The default production domain. When this is the active value the integration
	 * is effectively a no-op — outgoing requests are not rewritten.
	 *
	 * @var string
	 */
	public const DEFAULT_DOMAIN = 'https://my.yoast.com';

	/**
	 * Sentinel value selected from the dropdown to mean "use the custom URL field".
	 *
	 * @var string
	 */
	private const DOMAIN_CUSTOM = 'custom';

	/**
	 * Predefined MyYoast environments.
	 *
	 * @var array<string, string>
	 */
	private const PREDEFINED_DOMAINS = [
		self::DEFAULT_DOMAIN                 => 'live',
		'https://staging.yoast.com'          => 'staging',
		'https://staging-plugins.yoast.com'  => 'staging-plugins',
		'https://staging-platform.yoast.com' => 'staging-platform',
		'https://staging-4-my.yoast.com'     => 'staging-4',
		'https://staging-5-my.yoast.com'     => 'staging-5',
		'http://my.yoast.test'               => 'local',
	];

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
	 * Returns the active MyYoast domain — the resolved value of whichever entry
	 * (predefined or custom) the user picked. Other integrations should read this
	 * rather than peeking at the underlying option keys.
	 *
	 * @return string The active domain URL, or the production default when nothing is configured.
	 */
	public function get_active_domain() {
		$selected = $this->option->get( 'myyoast_test_domain' );
		if ( ! \is_string( $selected ) || $selected === '' ) {
			return self::DEFAULT_DOMAIN;
		}

		if ( $selected === self::DOMAIN_CUSTOM ) {
			$custom = $this->option->get( 'myyoast_test_custom_domain' );

			return ( \is_string( $custom ) && $custom !== '' ) ? $custom : self::DEFAULT_DOMAIN;
		}

		return ( isset( self::PREDEFINED_DOMAINS[ $selected ] ) ) ? $selected : self::DEFAULT_DOMAIN;
	}

	/**
	 * Registers WordPress hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action( 'admin_post_yoast_seo_domain_dropdown', [ $this, 'handle_submit' ] );

		if ( $this->get_active_domain() !== self::DEFAULT_DOMAIN ) {
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
		$stored        = $this->option->get( 'myyoast_test_domain' );
		$custom        = $this->option->get( 'myyoast_test_custom_domain' );
		$is_predefined = \is_string( $stored ) && isset( self::PREDEFINED_DOMAINS[ $stored ] );

		$dropdown_value = '';
		if ( $is_predefined ) {
			$dropdown_value = $stored;
		}
		elseif ( $stored === self::DOMAIN_CUSTOM ) {
			$dropdown_value = self::DOMAIN_CUSTOM;
		}

		$select_options                        = self::PREDEFINED_DOMAINS;
		$select_options[ self::DOMAIN_CUSTOM ] = \esc_html__( 'Custom URL…', 'yoast-test-helper' );

		$output = Form_Presenter::create_select(
			'myyoast_test_domain',
			\esc_html__( 'Set the MyYoast testing domain to: ', 'yoast-test-helper' ),
			$select_options,
			$dropdown_value,
		);

		$custom_value  = \is_string( $custom ) ? $custom : '';
		$custom_hidden = ( $dropdown_value === self::DOMAIN_CUSTOM ) ? '' : ' hidden';
		$output       .= \sprintf(
			'<div id="myyoast_test_custom_domain_row"%1$s><label for="myyoast_test_custom_domain">%2$s</label> <input type="url" size="30" id="myyoast_test_custom_domain" name="myyoast_test_custom_domain" value="%3$s" placeholder="https://my.yoast.test"/><br/></div>',
			$custom_hidden,
			\esc_html__( 'Custom MyYoast URL:', 'yoast-test-helper' ),
			\esc_attr( $custom_value ),
		);

		$output .= $this->render_custom_toggle_script();

		return Form_Presenter::get_html( \__( 'Domain Dropdown', 'yoast-test-helper' ), 'yoast_seo_domain_dropdown', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( ! \check_admin_referer( 'yoast_seo_domain_dropdown' ) ) {
			return;
		}

		if ( isset( $_POST['myyoast_test_domain'] ) && \is_string( $_POST['myyoast_test_domain'] ) ) {
			$selected = \sanitize_text_field( \wp_unslash( $_POST['myyoast_test_domain'] ) );
			$this->option->set( 'myyoast_test_domain', $selected );
		}

		if ( isset( $_POST['myyoast_test_custom_domain'] ) && \is_string( $_POST['myyoast_test_custom_domain'] ) ) {
			$custom = $this->normalize_custom_domain( \esc_url_raw( \wp_unslash( $_POST['myyoast_test_custom_domain'] ) ) );
			$this->option->set( 'myyoast_test_custom_domain', $custom );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * If a testing domain is set, modify any request to myYoast to go to the testing domain.
	 * Attached to the `requests-requests.before_request` filter.
	 *
	 * @param string                                   $url     URL of the request about to be made.
	 * @param array<string, string|array<int, string>> $headers Headers of the request about to be made.
	 *
	 * @return void
	 */
	public function modify_myyoast_request( &$url, &$headers ) {
		$domain = $this->get_active_domain();

		if ( $domain === self::DEFAULT_DOMAIN ) {
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
	 * @param string                                   $domain  Testing domain to take place in the request.
	 * @param string                                   $url     URL of request about to be made.
	 * @param array<string, string|array<int, string>> $headers Headers of request about to be made.
	 *
	 * @return array<string, string> Format: [ 'url' => new URL, 'host' => new Host ]
	 */
	private function replace_domain( $domain, $url, $headers ) {
		$host     = '';
		$url_host = \wp_parse_url( $url, \PHP_URL_HOST );
		$new_host = \wp_parse_url( $domain, \PHP_URL_HOST );

		if ( $new_host === false || $new_host === null ) {
			$new_host = '';
		}

		if ( $url_host === 'my.yoast.com' ) {
			$host = ( $headers['Host'] ?? $new_host );
			$url  = \str_replace( 'https://' . $url_host, $domain, $url );
		}

		return [
			'url'  => $url,
			'host' => $host,
		];
	}

	/**
	 * Normalizes a custom domain to scheme + host (+ optional port). Strips any
	 * path, query, fragment, and trailing slash so the value can safely be used
	 * as both the URL-rewrite base and the OAuth issuer key without producing
	 * double-paths or split per-issuer credential records.
	 *
	 * Returns an empty string when the input doesn't parse to scheme + host.
	 *
	 * @param string $url The raw user input (already passed through esc_url_raw).
	 *
	 * @return string The normalized origin, or an empty string when invalid.
	 */
	private function normalize_custom_domain( $url ) {
		if ( $url === '' ) {
			return '';
		}

		$parts = \wp_parse_url( $url );
		if ( ! \is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return '';
		}

		$origin = $parts['scheme'] . '://' . $parts['host'];
		if ( isset( $parts['port'] ) ) {
			$origin .= ':' . $parts['port'];
		}

		return $origin;
	}

	/**
	 * Inline script that toggles the custom domain row based on the dropdown value.
	 *
	 * @return string The script tag HTML.
	 */
	private function render_custom_toggle_script() {
		return <<<'HTML'
<script>
( function() {
	var select = document.getElementById( "myyoast_test_domain" );
	var row    = document.getElementById( "myyoast_test_custom_domain_row" );
	if ( ! select || ! row ) {
		return;
	}
	var sync = function() {
		row.toggleAttribute( "hidden", select.value !== "custom" );
	};
	select.addEventListener( "change", sync );
	sync();
} )();
</script>
HTML;
	}
}
