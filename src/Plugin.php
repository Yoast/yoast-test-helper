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
		$plugins = array(
			new Yoast_SEO(),
			new Local_SEO(),
			new Video_SEO(),
			new News_SEO(),
			new WooCommerce_SEO(),
		);

		$plugin_features        = new WordPress_Plugin_Features( $plugins );
		$plugin_version_control = new WordPress_Plugin_Version_Control(
			$plugins,
			new WordPress_Plugin_Version(),
			new WordPress_Plugin_Options()
		);

		$admin_page = new Admin_Page();
		$admin_page->add_admin_page_block( array( $plugin_version_control, 'get_controls' ) );
		$admin_page->add_admin_page_block( array( $plugin_features, 'get_controls' ) );

		$this->integrations[] = new Admin_Notifications();
		$this->integrations[] = new Upgrade_Detector();
		$this->integrations[] = $admin_page;
		$this->integrations[] = $plugin_features;
		$this->integrations[] = $plugin_version_control;
		$this->integrations[] = new Plugin_Toggler();
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
}
