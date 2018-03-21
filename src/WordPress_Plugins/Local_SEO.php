<?php

namespace Yoast\Test_Helper\WordPress_Plugins;

class Local_SEO implements WordPress_Plugin {
	/**
	 * @return string
	 */
	public function get_identifier() {
		return 'wpseo-local';
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'Yoast SEO: Local';
	}

	/**
	 * @return string
	 */
	public function get_version_option_name() {
		return 'wpseo_local';
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
		return defined( 'WPSEO_LOCAL_VERSION' ) ? WPSEO_LOCAL_VERSION : 'not active';
	}
}
