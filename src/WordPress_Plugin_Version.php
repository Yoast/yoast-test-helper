<?php

namespace Yoast\Version_Controller;

use Yoast\Version_Controller\WordPress_Plugins\WordPress_Plugin;

class WordPress_Plugin_Version {
	/**
	 * @param WordPress_Plugin $plugin
	 *
	 * @return string
	 */
	public function get_version( WordPress_Plugin $plugin ) {
		$data = get_option( $plugin->get_version_option_name() );
		if ( isset( $data[ $plugin->get_version_key() ] ) ) {
			return $data[ $plugin->get_version_key() ];
		}

		return '';
	}

	/**
	 * @param WordPress_Plugin $plugin
	 * @param string           $version
	 *
	 * @return bool
	 */
	public function update_version( WordPress_Plugin $plugin, $version ) {
		$option_name = $plugin->get_version_option_name();
		$data        = get_option( $option_name );

		if ( $data[ $plugin->get_version_key() ] === $version ) {
			return false;
		}

		$data[ $plugin->get_version_key() ] = $version;

		$option_instance = false;
		// Unhook option sanitization, otherwise the version cannot be changed.
		if ( class_exists( '\WPSEO_Options' ) ) {
			$option_instance = \WPSEO_Options::get_option_instance( $option_name );
			remove_filter( 'sanitize_option_' . $option_name, array( $option_instance, 'validate' ) );
		}

		$success = update_option( $option_name, $data );

		// Restore option sanitization.
		if ( $option_instance ) {
			add_filter( 'sanitize_option_' . $option_name, array( $option_instance, 'validate' ) );
		}

		return $success;
	}
}
