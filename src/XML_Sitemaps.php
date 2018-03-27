<?php
/**
 * Handler for the XML Sitemaps testing functions.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class XML_Sitemaps implements Integration {
	/**
	 * Holds our option instance.
	 *
	 * @var Option
	 */
	private $option;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->option = new Option();
	}

	/**
	 * Adds the required hooks for this class.
	 */
	public function add_hooks() {
		if ( $this->option->get( 'disable_xml_sitemap_cache' ) === true ) {
			add_filter( 'wpseo_enable_xml_sitemap_transient_caching', '__return_false' );
		}

		add_action( 'admin_post_yoast_seo_test_xml_sitemaps', array( $this, 'handle_submit' ) );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$output  = '<h2>XML Sitemaps</h2>';
		$output .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= wp_nonce_field( 'yoast_seo_test_xml_sitemaps', '_wpnonce', true, false );
		$output .= '<input type="hidden" name="action" value="yoast_seo_test_xml_sitemaps">';

		$output .= '<input type="checkbox" ' . checked( $this->option->get( 'disable_xml_sitemap_cache' ), true, false ) . ' name="disable_xml_sitemap_cache" id="disable_xml_sitemap_cache"/>';
		$output .= '<label for="disable_xml_sitemap_cache">Disable the XML sitemaps cache.</label>';
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
		if ( check_admin_referer( 'yoast_seo_test_xml_sitemaps' ) !== false ) {
			$this->option->set( 'disable_xml_sitemap_cache', isset( $_POST['disable_xml_sitemap_cache'] ) );
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}
}
