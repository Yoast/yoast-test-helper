<?php
/**
 * The main plugin file.
 *
 * @package Yoast\Version_Controller
 */

namespace Yoast\Test_Helper;

use Yoast\Test_Helper\WordPress_Plugins\Local_SEO;
use Yoast\Test_Helper\WordPress_Plugins\News_SEO;
use Yoast\Test_Helper\WordPress_Plugins\Video_SEO;
use Yoast\Test_Helper\WordPress_Plugins\WooCommerce_SEO;
use Yoast\Test_Helper\WordPress_Plugins\Yoast_SEO;

/**
 * Bootstrap for the entire plugin.
 */
class Plugin implements Integration {
	/**
	 * List of integrations
	 *
	 * @var Integration[]
	 */
	protected $integrations = array();

	/**
	 * Constructs the class.
	 */
	public function __construct() {
		$this->load_integrations();

		add_action( 'yoast_version_controller_notifications', array( $this, 'admin_page_blocks' ) );
	}

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		array_map(
			function ( Integration $integration ) {
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
			if ( method_exists( $integration, 'get_controls' ) ) {
				$admin_page->add_admin_page_block( array( $integration, 'get_controls' ) );
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

		$plugin_version_control = new WordPress_Plugin_Version_Control(
			$plugins,
			new WordPress_Plugin_Version(),
			new WordPress_Plugin_Options()
		);

		$option = new Option();

		$this->integrations[] = $plugin_version_control;
		$this->integrations[] = new Admin_Notifications();
		$this->integrations[] = new Upgrade_Detector();
		$this->integrations[] = new Admin_Page();
		$this->integrations[] = new WordPress_Plugin_Features( $plugins );
		$this->integrations[] = new Admin_Debug_Info( $option );
		$this->integrations[] = new Plugin_Toggler( $option );
		$this->integrations[] = new Feature_Toggler( $option );
		$this->integrations[] = new Post_Types( $option );
		$this->integrations[] = new Taxonomies( $option );
		$this->integrations[] = new Schema( $option );
		$this->integrations[] = new XML_Sitemaps( $option );
	}

	/**
	 * Retrieves all the plugins.
	 *
	 * @return array
	 */
	private function get_plugins() {
		return array(
			new Yoast_SEO(),
			new Local_SEO(),
			new Video_SEO(),
			new News_SEO(),
			new WooCommerce_SEO(),
		);
	}
}
