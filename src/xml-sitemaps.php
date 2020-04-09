<?php

namespace Yoast\WP\Test_Helper;

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
		if ( $this->option->get( 'disable_xml_sitemap_cache' ) === true ) {
			\add_filter( 'wpseo_enable_xml_sitemap_transient_caching', '__return_false' );
		}
		\add_filter( 'wpseo_sitemap_entries_per_page', [ $this, 'xml_sitemap_entries' ], 10, 1 );

		\add_action( 'admin_post_yoast_seo_test_xml_sitemaps', [ $this, 'handle_submit' ] );
	}

	/**
	 * Filter the number of XML sitemap entries.
	 *
	 * @param int $number The current number of XML sitemap entries.
	 *
	 * @return int The current number of XML sitemap entries.
	 */
	public function xml_sitemap_entries( $number ) {
		if ( $this->option->get( 'xml_sitemap_entries' ) > 0 ) {
			return $this->option->get( 'xml_sitemap_entries' );
		}

		return $number;
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using WPSEO hook.
		$placeholder = \apply_filters( 'wpseo_sitemap_entries_per_page', 1000 );

		$value = '';
		if ( $this->option->get( 'xml_sitemap_entries' ) > 0 ) {
			$value = $this->option->get( 'xml_sitemap_entries' );
		}

		$output  = Form_Presenter::create_checkbox(
			'disable_xml_sitemap_cache',
			'Disable the XML sitemaps cache.',
			$this->option->get( 'disable_xml_sitemap_cache' )
		);
		$output .= '<label for="xml_sitemap_entries">Maximum entries per XML sitemap:</label>';
		$output .= '<input type="number" size="5" value="' . $value . '" placeholder="' . $placeholder . '" name="xml_sitemap_entries" id="xml_sitemap_entries"/><br/>';

		return Form_Presenter::get_html( 'XML Sitemaps', 'yoast_seo_test_xml_sitemaps', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_xml_sitemaps' ) !== false ) {
			$this->option->set( 'disable_xml_sitemap_cache', isset( $_POST['disable_xml_sitemap_cache'] ) );
			$xml_sitemap_entries = null;
			if ( isset( $_POST['xml_sitemap_entries'] ) ) {
				$xml_sitemap_entries = \filter_input( \INPUT_POST, 'xml_sitemap_entries', \FILTER_SANITIZE_NUMBER_INT );
			}
			$this->option->set( 'xml_sitemap_entries', $xml_sitemap_entries );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}
}
