<?php

namespace Yoast\Version_Controller;

use Yoast\Version_Controller\Plugin\Local_SEO;
use Yoast\Version_Controller\Plugin\News_SEO;
use Yoast\Version_Controller\Plugin\Video_SEO;
use Yoast\Version_Controller\Plugin\WooCommerce_SEO;
use Yoast\Version_Controller\Plugin\Yoast_SEO;

/**
 * Class Plugin
 * @package Yoast\Version_Controller
 */
class Plugin {
	protected $integrations = [];

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$plugins = [
			new Yoast_SEO(),
			new Local_SEO(),
			new Video_SEO(),
			new News_SEO(),
			new WooCommerce_SEO(),
		];

		$plugin_features = new Plugin_Features( $plugins );

		$this->integrations[] = new Admin_Page(
			$plugins,
			new Plugin_Options(),
			new Plugin_Version(),
			$plugin_features
		);

		$this->integrations[] = $plugin_features;
	}

	/**
	 *
	 */
	public function add_hooks() {
		array_map(
			function ( $integration ) {
				$integration->add_hooks();
			},
			$this->integrations
		);
	}
}
