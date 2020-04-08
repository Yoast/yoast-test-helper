<?php

namespace Yoast\WP\Test_Helper;

use WPSEO_Utils;

/**
 * Class to manage registering and rendering the admin page in WordPress.
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
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$output = Form_Presenter::create_checkbox(
			'replace_schema_domain',
			'Replace .test domain name with example.com in Schema output.',
			$this->option->get( 'replace_schema_domain' )
		);

		$select_options = [
			'none' => 'Don\'t influence',
			'show' => 'Always include',
			'hide' => 'Never include',
		];

		$output .= Form_Presenter::create_select(
			'is_needed_breadcrumb',
			'Influence the Breadcrumb Graph piece: ',
			$select_options,
			$this->option->get( 'is_needed_breadcrumb' )
		);

		$output .= Form_Presenter::create_select(
			'is_needed_webpage',
			'Influence the WebPage Graph piece: ',
			$select_options,
			$this->option->get( 'is_needed_webpage' )
		);

		return Form_Presenter::get_html( 'Schema', 'yoast_seo_test_schema', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_schema' ) !== false ) {
			$this->option->set( 'replace_schema_domain', isset( $_POST['replace_schema_domain'] ) );
		}

		$is_needed_breadcrumb = $this->validate_submit( \filter_input( \INPUT_POST, 'is_needed_breadcrumb' ) );
		$is_needed_webpage    = $this->validate_submit( \filter_input( \INPUT_POST, 'is_needed_webpage' ) );

		$this->option->set( 'is_needed_breadcrumb', $is_needed_breadcrumb );
		$this->option->set( 'is_needed_webpage', $is_needed_webpage );

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Make sure we only store data we know how to deal with.
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
	 * Deep replace strings in array.
	 *
	 * @param string $needle      The needle to replace.
	 * @param string $replacement The replacement.
	 * @param array  $array       The array to replace in.
	 *
	 * @return array The array with needle replaced by replacement in strings.
	 */
	private function array_value_str_replace( $needle, $replacement, $array ) {
		if ( \is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				if ( \is_array( $value ) ) {
					$array[ $key ] = $this->array_value_str_replace( $needle, $replacement, $array[ $key ] );
				}
				else {
					if ( \strpos( $value, $needle ) !== false ) {
						$array[ $key ] = \str_replace( $needle, $replacement, $value );
					}
				}
			}
		}

		return $array;
	}
}
