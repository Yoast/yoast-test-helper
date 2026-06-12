<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\Test_Helper\Conditionals\Conditional_Aware;
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
		\add_action( 'plugins_loaded', [ $this, 'load_integrations' ], 10 );

		\add_action( 'Yoast\WP\Test_Helper\notifications', [ $this, 'admin_page_blocks' ] );
	}

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\array_walk(
			$this->integrations,
			static function ( Integration $integration ) {
				$integration->add_hooks();
			},
		);
	}

	/**
	 * Adds the blocks to the admin page.
	 *
	 * @param Admin_Page $admin_page The current admin page.
	 *
	 * @return void
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
	public function load_integrations(): void {
		$plugins = $this->get_plugins();

		$plugin_version_control = new Plugin_Version_Control(
			$plugins,
			new WordPress_Plugin_Version(),
			new WordPress_Plugin_Options(),
		);

		$option = new Option();

		$integrations    = [];
		$integrations[]  = $plugin_version_control;
		$integrations[]  = new Admin_Page();
		$integrations[]  = new Admin_Notifications();
		$integrations[]  = new Upgrade_Detector();
		$integrations[]  = new Development_Mode( $option );
		$integrations[]  = new Plugin_Toggler( $option );
		$integrations[]  = new WordPress_Plugin_Features( $plugins );
		$integrations[]  = new Schema( $option );
		$integrations[]  = new XML_Sitemaps( $option );
		$integrations[]  = new Feature_Toggler( $option );
		$integrations[]  = new Post_Types( $option );
		$integrations[]  = new Taxonomies( $option );
		$domain_dropdown = new Domain_Dropdown( $option );
		$integrations[]  = $domain_dropdown;
		$integrations[]  = new Inline_Script( $option );
		$integrations[]  = new MyYoast_OAuth_Overrides( $option, $domain_dropdown );
		$integrations[]  = new Admin_Debug_Info( $option );
		$integrations[]  = new Logger_Integration( $option );
		$integrations[]  = new Indexing_Reason_Integration();
		$integrations[]  = new Query_Monitor();
		$integrations[]  = new Downgrader();

		$this->integrations = \array_filter(
			$integrations,
			static function ( Integration $integration ) {
				if ( ! $integration instanceof Conditional_Aware ) {
					return true;
				}
				foreach ( $integration::get_conditionals() as $conditional ) {
					if ( ! $conditional->is_met() ) {
						return false;
					}
				}
				return true;
			},
		);
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
