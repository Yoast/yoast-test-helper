<?php

namespace Yoast\Version_Controller\Plugin;

class News_SEO implements Plugin {
	/**
	 * @return string
	 */
	public function get_identifier() {
		return 'wpseo-news';
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'Yoast SEO: News';
	}

	/**
	 * @return string
	 */
	public function get_version_option_name() {
		return 'wpseo_news';
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
		return class_exists( '\WPSEO_News' ) ? \WPSEO_News::VERSION : 'not active';
	}
}
