<?php

namespace Yoast\Version_Controller\Plugin;

class Video_SEO implements Plugin {
	/**
	 * @return string
	 */
	public function get_identifier() {
		return 'wpseo-video';
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'Yoast SEO: Video';
	}

	/**
	 * @return string
	 */
	public function get_version_option_name() {
		return 'wpseo_video';
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
	 * @return void
	 */
	public function reset_feature( $feature ) {
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
		return defined( 'WPSEO_VIDEO_VERSION' ) ? WPSEO_VIDEO_VERSION : 'not active';
	}
}
