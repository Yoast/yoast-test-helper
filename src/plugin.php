<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\Test_Helper\WordPress_Plugins\Local_SEO;
use Yoast\WP\Test_Helper\WordPress_Plugins\News_SEO;
use Yoast\WP\Test_Helper\WordPress_Plugins\Video_SEO;
use Yoast\WP\Test_Helper\WordPress_Plugins\WooCommerce_SEO;
use Yoast\WP\Test_Helper\WordPress_Plugins\WordPress_Plugin;
use Yoast\WP\Test_Helper\WordPress_Plugins\Yoast_SEO;
use Yoast\WP\Test_Helper\WordPress_Plugins\Yoast_SEO_Premium;

/**
 * Bootstrap for the entire plugin.
 */
class Plugin implements Integration {

	/**
	 * List of integrations
	 *
	 * @var Integration[]
	 */
	protected $integrations = [];

	/**
	 * Constructs the class.
	 */
	public function __construct() {
		$this->load_integrations();

		\add_action( 'Yoast\WP\Test_Helper\notifications', [ $this, 'admin_page_blocks' ] );
	}

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\array_map(
			static function ( Integration $integration ) {
				$integration->add_hooks();
			},
			$this->integrations
		);
	}

	/**
	 * Adds the blocks to the admin page.
	 *
	 * @param Admin_Page $admin_page The current admin page.
	 */
	public function admin_page_blocks( Admin_Page $admin_page ) {
		foreach ( $this->integrations as $integration ) {
			if ( \method_exists( $integration, 'get_controls' ) ) {
				$admin_page->add_admin_page_block( [ $integration, 'get_controls' ] );
			}
		}
	}

	/**
	 * Loads all the integrations.
	 *
	 * @return void
	 */
	private function load_integrations() {
		$plugins = $this->get_plugins();

		$plugin_version_control = new Plugin_Version_Control(
			$plugins,
			new WordPress_Plugin_Version(),
			new WordPress_Plugin_Options()
		);

		$option = new Option();

		$this->integrations[] = $plugin_version_control;
		$this->integrations[] = new Admin_Page();
		$this->integrations[] = new Support_Session( $option );
		$this->integrations[] = new Admin_Notifications();
		$this->integrations[] = new Upgrade_Detector();
		$this->integrations[] = new Development_Mode( $option );
		$this->integrations[] = new Plugin_Toggler( $option );
		$this->integrations[] = new WordPress_Plugin_Features( $plugins );
		$this->integrations[] = new Schema( $option );
		$this->integrations[] = new XML_Sitemaps( $option );
		$this->integrations[] = new Feature_Toggler( $option );
		$this->integrations[] = new Post_Types( $option );
		$this->integrations[] = new Taxonomies( $option );
		$this->integrations[] = new Domain_Dropdown( $option );
		$this->integrations[] = new Inline_Script( $option );
		$this->integrations[] = new Admin_Debug_Info( $option );
		$this->integrations[] = new Indexing_Reason_Integration();
		$this->integrations[] = new Query_Monitor();
	}

	/**
	 * Retrieves all the plugins.
	 *
	 * @return WordPress_Plugin[]
	 */
	private function get_plugins() {
		return [
			new Yoast_SEO(),
			new Yoast_SEO_Premium(),
			new Local_SEO(),
			new Video_SEO(),
			new News_SEO(),
			new WooCommerce_SEO(),
		];
	}
}
