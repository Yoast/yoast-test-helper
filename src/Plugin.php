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
			new WooCommerce_SEO(),
			new Video_SEO(),
			new News_SEO(),
			new Local_SEO()
		];

		$this->integrations[] = new Admin_Page( $plugins, new Option_Control() );
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
