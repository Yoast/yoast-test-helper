<?php
/**
 * Admin page hander.
 *
 * @package Yoast\Yoast_SEO_Admin_Bar_Debug_Panel
 *
 * Cannot use a namespace because the DebugBar plugin uses classnames as CSS selectors and the slashes this returns
 * for namespaced classes cause havoc everywhere.
 */

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Yoast_SEO_Admin_Bar_Debug_Panel extends \Debug_Bar_Panel {
	/**
	 * Admin_Bar_Debug_Panel constructor.
	 */
	public function __construct() {
		$this->set_visible( true );
		parent::__construct( 'Yoast SEO' );
	}

	/**
	 * Renders the debug panel.
	 */
	public function render() {
		echo '<h2>Debug Information</h2>';
		echo '<div class="clear"></div>';
		echo '<ul>';
		foreach ( WPSEO_Options::get_option_names() as $option ) {
			printf( '<li><a style="text-decoration: none !important;" href="#%1$s">%2$s</a></li>', esc_attr( $option ), esc_html( $option ) );
		}
		echo '</ul>';
		foreach ( WPSEO_Options::get_option_names() as $option ) {
			echo '<h3 id="' . esc_attr( $option ) . '">Option: <span class="wpseo-debug">' . esc_html( $option ) . '</span></h3>';
			echo '<pre>';
			// @codingStandardsIgnoreLine.
			echo var_export( get_option( $option ) );
			echo '</pre>';
		}
	}
}