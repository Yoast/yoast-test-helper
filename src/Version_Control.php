<?php

namespace Yoast\Version_Controller;

use Yoast\Version_Controller\Plugin\Plugin;

class Version_Control {
	/**
	 * @param Plugin $plugin
	 *
	 * @return string
	 */
	public function get_version( Plugin $plugin ) {
		$data = get_option( $plugin->get_version_option_name() );
		if ( isset( $data[ $plugin->get_version_key() ] ) ) {
			return $data[ $plugin->get_version_key() ];
		}

		return '';
	}

	/**
	 * @param Plugin $plugin
	 * @param        $version
	 */
	public function update_version( Plugin $plugin, $version ) {
		$data                               = get_option( $plugin->get_version_option_name() );
		$data[ $plugin->get_version_key() ] = $version;

		update_option( $plugin->get_version_option_name(), $data );
	}
}
