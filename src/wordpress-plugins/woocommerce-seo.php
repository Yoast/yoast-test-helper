<?php

namespace Yoast\WP\Test_Helper\WordPress_Plugins;

use Yoast_WooCommerce_SEO;

/**
 * Class to represent WooCommerce SEO.
 */
class WooCommerce_SEO implements WordPress_Plugin {

	/**
	 * Retrieves the plugin identifier.
	 *
	 * @return string The plugin identifier.
	 */
	public function get_identifier() {
		return 'wpseo-woocommerce';
	}

	/**
	 * Retrieves the plugin name.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_name() {
		return 'Yoast SEO: WooCommerce';
	}

	/**
	 * Retrieves the version option name.
	 *
	 * @return string The name that holds the version.
	 */
	public function get_version_option_name() {
		return 'wpseo_woo';
	}

	/**
	 * Retrieves the version key.
	 *
	 * @return string The version key.
	 */
	public function get_version_key() {
		return 'woo_dbversion';
	}

	/**
	 * Retrieves the options.
	 *
	 * @return array The options.
	 */
	public function get_options() {
		return [ $this->get_version_option_name() ];
	}

	/**
	 * Resets a feature.
	 *
	 * @param string $feature Feature to reset.
	 *
	 * @return bool True on success.
	 */
	public function reset_feature( $feature ) {
		return false;
	}

	/**
	 * Retrieves the list of features.
	 *
	 * @return string[] List of features.
	 */
	public function get_features() {
		return [];
	}

	/**
	 * Retrieves the active version of the plugin.
	 *
	 * @return string The current version of the plugin.
	 */
	public function get_version_constant() {
		return \class_exists( Yoast_WooCommerce_SEO::class ) ? Yoast_WooCommerce_SEO::VERSION : \__( 'not active', 'yoast-test-helper' );
	}
}
