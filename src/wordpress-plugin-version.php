<?php

namespace Yoast\WP\Test_Helper;

use WPSEO_Options;
use Yoast\WP\Test_Helper\WordPress_Plugins\WordPress_Plugin;

/**
 * Class that retrieves and stores a plugin version.
 */
class WordPress_Plugin_Version {

	/**
	 * Retrieves the version of a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin Plugin to retrieve the version of.
	 *
	 * @return string The version.
	 */
	public function get_version( WordPress_Plugin $plugin ) {
		$data = \get_option( $plugin->get_version_option_name() );
		if ( isset( $data[ $plugin->get_version_key() ] ) ) {
			return $data[ $plugin->get_version_key() ];
		}

		return '';
	}

	/**
	 * Stores a plugin version.
	 *
	 * @param WordPress_Plugin $plugin  Plugin to store the version of.
	 * @param string           $version The version to store.
	 *
	 * @return bool True on succes.
	 */
	public function update_version( WordPress_Plugin $plugin, $version ) {
		$option_name = $plugin->get_version_option_name();
		$data        = \get_option( $option_name );

		if ( empty( $version ) ) {
			return false;
		}

		if ( $plugin->get_version_key() === '' ) {
			return \update_option( $plugin->get_version_option_name(), $version );
		}

		if ( $data[ $plugin->get_version_key() ] === $version ) {
			return false;
		}

		$data[ $plugin->get_version_key() ] = $version;

		$option_instance = false;
		// Unhook option sanitization, otherwise the version cannot be changed.
		if ( \class_exists( WPSEO_Options::class ) ) {
			$option_instance = WPSEO_Options::get_option_instance( $option_name );
			\remove_filter( 'sanitize_option_' . $option_name, [ $option_instance, 'validate' ] );
		}

		$success = \update_option( $option_name, $data );

		// Restore option sanitization.
		if ( $option_instance ) {
			\add_filter( 'sanitize_option_' . $option_name, [ $option_instance, 'validate' ] );
		}

		return $success;
	}
}
