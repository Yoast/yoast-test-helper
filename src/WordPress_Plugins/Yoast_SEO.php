<?php
/**
 * Yoast SEO plugin
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper\WordPress_Plugins;

/**
 * Class to represent Yoast SEO.
 */
class Yoast_SEO implements WordPress_Plugin {
	/**
	 * Retrieves the plugin identifier.
	 *
	 * @return string The plugin identifier.
	 */
	public function get_identifier() {
		return 'wordpress-seo';
	}

	/**
	 * Retrieves the plugin name.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_name() {
		return 'Yoast SEO';
	}

	/**
	 * Retrieves the version option name.
	 *
	 * @return string The name that holds the version.
	 */
	public function get_version_option_name() {
		return 'wpseo';
	}

	/**
	 * Retrieves the version key.
	 *
	 * @return string The version key.
	 */
	public function get_version_key() {
		return 'version';
	}

	/**
	 * Retrieves the options.
	 *
	 * @return array The options.
	 */
	public function get_options() {
		return array(
			'wpseo',
			'wpseo_xml',
			'wpseo_rss',
			'wpseo_ms',
			'wpseo_internallinks',
			'wpseo_permalinks',
			'wpseo_titles',
		);
	}

	/**
	 * Retrieves the list of features.
	 *
	 * @return array List of features.
	 */
	public function get_features() {
		return array(
			'internal_link_count'         => 'Internal link counter',
			'prominent_words_calculation' => 'Prominent words calculation',
		);
	}

	/**
	 * Resets a feature.
	 *
	 * @param string $feature Feature to reset.
	 *
	 * @return bool True on succes.
	 */
	public function reset_feature( $feature ) {
		switch ( $feature ) {
			case 'internal_link_count':
				$this->reset_internal_link_count();
				return true;
			case 'prominent_words_calculation':
				$this->reset_prominent_words_calculation();
				return true;
		}

		return false;
	}

	/**
	 * Retrieves the active version of the plugin.
	 *
	 * @return string The current version of the plugin.
	 */
	public function get_version_constant() {
		return defined( 'WPSEO_VERSION' ) ? WPSEO_VERSION : 'not active';
	}

	/**
	 * Resets the internal link count.
	 *
	 * @return void
	 */
	private function reset_internal_link_count() {
		global $wpdb;

		$wpdb->query( 'UPDATE ' . $wpdb->prefix . 'yoast_seo_meta SET internal_link_count = NULL' );
	}

	/**
	 * Resets the prominent words calculation.
	 *
	 * @return void
	 */
	private function reset_prominent_words_calculation() {
		global $wpdb;

		$wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => '_yst_prominent_words_version' ) );
	}
}
