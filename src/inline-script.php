<?php

namespace Yoast\WP\Test_Helper;

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
		\add_action( 'admin_enqueue_scripts', [ $this, 'add_inline_script' ] );
		\add_action( 'admin_post_yoast_seo_test_inline_script', [ $this, 'handle_submit' ] );
	}

	/**
	 * Add an inline script after the specified script.
	 */
	public function add_inline_script() {
		if ( $this->option->get( 'add_inline_script' ) !== true ) {
			return;
		}
		\wp_add_inline_script(
			$this->option->get( 'inline_script_handle' ),
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
			'add_inline_script',
			\esc_html__( 'Add the inline script specified below after the script selected here.', 'yoast-test-helper' ),
			$this->option->get( 'add_inline_script' )
		) . '<br/>';

		$output .= '<label for="inline_script_handle">' . \__( 'After script', 'yoast-test-helper' ) . ': </label>';
		$output .= $this->select_script( $this->option->get( 'inline_script_handle' ) );
		$output .= '<br><br>';

		$value = $this->option->get( 'inline_script' );

		$output .= '<label for="inline_script">';
		/* translators: %1$s expands to the `script` tag. */
		$output .= \sprintf( \esc_html__( 'Script (do not include %1$s tags):', 'yoast-test-helper' ), '<code>&lt;script&gt;</code>' );
		$output .= '</label><br/>';
		$output .= '<textarea style="width: 100%; min-height: 300px; font-family: monospace;" name="inline_script" id="inline_script">' . \esc_html( $value ) . '</textarea><br/>';

		return Form_Presenter::get_html( \__( 'Inline script', 'yoast-test-helper' ), 'yoast_seo_test_inline_script', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_inline_script' ) !== false ) {
			$this->option->set( 'add_inline_script', isset( $_POST['add_inline_script'] ) );
			$this->option->set( 'inline_script_handle', \filter_input( \INPUT_POST, 'inline_script_handle', \FILTER_SANITIZE_STRING ) );
			$this->option->set( 'inline_script', \filter_input( \INPUT_POST, 'inline_script', \FILTER_SANITIZE_STRING ) );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Return a select with all the scripts currently registered to WP.
	 *
	 * @param string $value The currently selected value, if any.
	 *
	 * @return string
	 */
	private function select_script( $value ) {
		$output  = '<select name="inline_script_handle" id="inline_script_handle">';
		$scripts = \wp_scripts();
		foreach ( \array_keys( $scripts->registered ) as $script ) {
			$sel = '';
			if ( $value === $script ) {
				$sel = 'selected';
			}
			$output .= '<option value="' . \esc_attr( $script ) . '" ' . $sel . '>' . \esc_html( $script ) . '</option>';
		}
		$output .= '</select>';

		return $output;
	}
}
