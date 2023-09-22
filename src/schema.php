<?php

namespace Yoast\WP\Test_Helper;

use WPSEO_Utils;

/**
 * Class to influence the Schema functionality of Yoast SEO (Premium).
 */
class Schema implements Integration {

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
	 * Adds the required hooks for this class.
	 */
	public function add_hooks() {
		if ( $this->option->get( 'replace_schema_domain' ) === true ) {
			\add_filter( 'wpseo_debug_json_data', [ $this, 'replace_domain' ] );
		}

		if ( $this->option->get( 'enable_schema_endpoint' ) === true ) {
			\add_action( 'template_redirect', [ $this, 'send_json_ld' ] );
			\add_action( 'init', [ $this, 'init_rewrite' ] );
		}

		switch ( $this->option->get( 'is_needed_breadcrumb' ) ) {
			case 'show':
			case 'hide':
				\add_filter( 'wpseo_schema_needs_breadcrumb', [ $this, 'filter_is_needed_breadcrumb' ] );
				break;
			default:
				\remove_filter( 'wpseo_schema_needs_breadcrumb', [ $this, 'filter_is_needed_breadcrumb' ] );
				break;
		}

		switch ( $this->option->get( 'is_needed_webpage' ) ) {
			case 'show':
			case 'hide':
				\add_filter( 'wpseo_schema_needs_webpage', [ $this, 'filter_is_needed_webpage' ] );
				break;
			default:
				\remove_filter( 'wpseo_schema_needs_webpage', [ $this, 'filter_is_needed_webpage' ] );
				break;
		}

		\add_action( 'admin_post_yoast_seo_test_schema', [ $this, 'handle_submit' ] );
	}

	/**
	 * Registers the schema endpoint if needed.
	 *
	 * @return void
	 */
	public function init_rewrite() {
		\add_rewrite_endpoint( 'schema', \EP_ALL );
	}

	/**
	 * Send the Yoast SEO Schema.
	 *
	 * @return void
	 */
	public function send_json_ld() {
		global $wp_query;

		if ( ! isset( $wp_query->query_vars['schema'] ) ) {
			return;
		}

		\header( 'Content-Type: application/ld+json' );
		$url = \YoastSEO()->meta->for_current_page()->canonical;
		if ( ! empty( $url ) ) {
			\header( 'Link: <' . $url . '>; rel="canonical"' );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This is our self generated Schema, no need for escaping.
		echo WPSEO_Utils::format_json_encode( \YoastSEO()->meta->for_current_page()->schema );
		exit;
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$output = Form_Presenter::create_checkbox(
			'replace_schema_domain',
			\esc_html__( 'Replace .test domain name with example.com in Schema output.', 'yoast-test-helper' ),
			$this->option->get( 'replace_schema_domain' )
		);

		$output .= Form_Presenter::create_checkbox(
			'enable_schema_endpoint',
			/* translators: %s is replaced by `<code>/schema/</code>` */
			\sprintf( \esc_html__( 'Enable the %s endpoint for every URL.', 'yoast-test-helper' ), '<code>/schema/</code>' ),
			$this->option->get( 'enable_schema_endpoint' )
		);

		$select_options = [
			'none' => \esc_html__( 'Don\'t influence', 'yoast-test-helper' ),
			'show' => \esc_html__( 'Always include', 'yoast-test-helper' ),
			'hide' => \esc_html__( 'Never include', 'yoast-test-helper' ),
		];

		$output .= Form_Presenter::create_select(
			'is_needed_breadcrumb',
			\esc_html__( 'Influence the Breadcrumb Graph piece: ', 'yoast-test-helper' ),
			$select_options,
			$this->option->get( 'is_needed_breadcrumb' )
		);

		$output .= Form_Presenter::create_select(
			'is_needed_webpage',
			\esc_html__( 'Influence the WebPage Graph piece: ', 'yoast-test-helper' ),
			$select_options,
			$this->option->get( 'is_needed_webpage' )
		);

		return Form_Presenter::get_html( \__( 'Schema', 'yoast-test-helper' ), 'yoast_seo_test_schema', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_schema' ) !== false ) {
			$this->option->set( 'replace_schema_domain', isset( $_POST['replace_schema_domain'] ) );
			$this->option->set( 'enable_schema_endpoint', isset( $_POST['enable_schema_endpoint'] ) );
		}

		if ( isset( $_POST['is_needed_breadcrumb'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- validation is done in validate_submit.
			$validated_is_needed_breadcrumb = $this->validate_submit( $_POST['is_needed_breadcrumb'] );
			$this->option->set( 'is_needed_breadcrumb', $validated_is_needed_breadcrumb );
		}

		if ( isset( $_POST['is_needed_webpage'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- validation is done in validate_submit.
			$validated_is_needed_webpage = $this->validate_submit( $_POST['is_needed_webpage'] );
			$this->option->set( 'is_needed_webpage', $validated_is_needed_webpage );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Makes sure we only store data we know how to deal with.
	 *
	 * @param string $value The submitted value.
	 *
	 * @return string The validated submit value.
	 */
	private function validate_submit( $value ) {
		$value = (string) $value;
		if ( \in_array( $value, [ 'none', 'show', 'hide' ], true ) ) {
			return $value;
		}
		return 'none';
	}

	/**
	 * Replaces your .test domain name with example.com in JSON output.
	 *
	 * @param array $data Data to replace the domain in.
	 *
	 * @return array Data to replace the domain in.
	 */
	public function replace_domain( $data ) {
		$source = WPSEO_Utils::get_home_url();
		$target = 'https://example.com';

		if ( $source[ ( \strlen( $source ) - 1 ) ] === '/' ) {
			$source = \substr( $source, 0, -1 );
		}

		return $this->array_value_str_replace( $source, $target, $data );
	}

	/**
	 * Returns the current breadcrumb option as boolean.
	 *
	 * @return bool
	 */
	public function filter_is_needed_breadcrumb() {
		return $this->option->get( 'is_needed_breadcrumb' ) === 'show';
	}

	/**
	 * Returns the current webpage option as boolean.
	 *
	 * @return bool
	 */
	public function filter_is_needed_webpage() {
		return $this->option->get( 'is_needed_webpage' ) === 'show';
	}

	/**
	 * Deep replaces strings in an array.
	 *
	 * @param string $needle      The needle to replace.
	 * @param string $replacement The replacement.
	 * @param array  $subject     The array to replace in.
	 *
	 * @return array The array with needle replaced by replacement in strings.
	 */
	private function array_value_str_replace( $needle, $replacement, $subject ) {
		if ( \is_array( $subject ) ) {
			foreach ( $subject as $key => $value ) {
				if ( \is_array( $value ) ) {
					$subject[ $key ] = $this->array_value_str_replace( $needle, $replacement, $subject[ $key ] );
				}
				elseif ( \strpos( $value, $needle ) !== false ) {
					$subject[ $key ] = \str_replace( $needle, $replacement, $value );
				}
			}
		}

		return $subject;
	}
}
