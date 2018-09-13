<?php
/**
 * Detects if an upgrade is ran.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Class to add an inline script after a wordpress-seo script.
 */
class Inline_Script implements Integration {
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
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( $this->option->get( 'add_inline_script' ) === true ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'add_inline_script' ) );
		}

		add_action( 'admin_post_yoast_seo_test_inline_script', array( $this, 'handle_submit' ) );
	}

	/**
	 * Add an inline script after the specified script.
	 */
	public function add_inline_script() {
		wp_add_inline_script(
			'yoast-seo-' . $this->option->get( 'inline_script_handle' ),
			$this->option->get( 'inline_script' )
		);
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$output = Form_Presenter::create_checkbox(
			'add_inline_script', 'Add the specified inline script',
			$this->option->get( 'add_inline_script' )
		);

		$value = $this->option->get( 'inline_script_handle' );

		$output .= '<label for="inline_script_handle">Handle: </label>';
		$output .= '<input value="' . $value . '" name="inline_script_handle" id="inline_script_handle"/><br/>';

		$value = $this->option->get( 'inline_script' );

		$output .= '<label for="inline_script">Script:</label><br/>';
		$output .= '<textarea style="width: 100%; min-height: 300px; font-family: monospace;" name="inline_script" id="inline_script">' . stripslashes( $value ) . '</textarea><br/>';

		return Form_Presenter::get_html( 'Inline script', 'yoast_seo_test_inline_script', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( check_admin_referer( 'yoast_seo_test_inline_script' ) !== false ) {
			$this->option->set( 'add_inline_script', isset( $_POST['add_inline_script'] ) );
			$this->option->set( 'inline_script_handle', (string) $_POST['inline_script_handle'] );
			$this->option->set( 'inline_script', stripslashes( (string) $_POST['inline_script'] ) );
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}
}
