<?php

namespace Yoast\Version_Controller\WordPress_Plugins;

class WooCommerce_SEO implements WordPress_Plugin {
	/**
	 * @return string
	 */
	public function get_identifier() {
		return 'wpseo-woocommerce';
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'Yoast SEO: WooCommerce';
	}

	/**
	 * @return string
	 */
	public function get_version_option_name() {
		return 'wpseo_woo';
	}

	/**
	 * @return string
	 */
	public function get_version_key() {
		return 'dbversion';
	}

	/**
	 * @return array
	 */
	public function get_options() {
		return [ $this->get_version_option_name() ];
	}

	/**
	 * @param string $feature
	 *
	 * @return bool
	 */
	public function reset_feature( $feature ) {
		return false;
	}

	/**
	 * @return array
	 */
	public function get_features() {
		return [];
	}

	/**
	 * @return string
	 */
	public function get_version_constant() {
		return class_exists( '\Yoast_WooCommerce_SEO' ) ? \Yoast_WooCommerce_SEO::VERSION : 'not active';
	}
}
