<?php

namespace Yoast\Version_Controller\Plugin;

class Yoast_SEO implements Plugin {
	/**
	 * @return string
	 */
	public function get_identifier() {
		return 'wordpress-seo';
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'Yoast SEO';
	}

	/**
	 * @return string
	 */
	public function get_version_option_name() {
		return 'wpseo';
	}

	/**
	 * @return string
	 */
	public function get_version_key() {
		return 'version';
	}

	/**
	 * @return array
	 */
	public function get_options() {
		return [
			'wpseo',
			'wpseo_xml',
			'wpseo_rss',
			'wpseo_ms',
			'wpseo_internallinks',
			'wpseo_permalinks',
			'wpseo_titles',
		];
	}

	/**
	 * @return array
	 */
	public function get_features() {
		return [
			'internal_link_count' => 'Internal link counter',
			'prominent_words_calculation' => 'Prominent words calculation',
		];
	}

	/**
	 * @param string $feature
	 *
	 * @return bool
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
	 *
	 */
	private function reset_internal_link_count() {
		global $wpdb;

		$wpdb->query( 'UPDATE ' . $wpdb->prefix . 'yoast_seo_meta SET internal_link_count = NULL' );
	}

	/**
	 *
	 */
	private function reset_prominent_words_calculation() {
		global $wpdb;

		$wpdb->delete( $wpdb->prefix . 'postmeta', [ 'meta_key' => '_yst_prominent_words_version' ] );
	}

	/**
	 * @return string
	 */
	public function get_version_constant() {
		return defined( 'WPSEO_VERSION' ) ? WPSEO_VERSION : 'not active';
	}
}
