<?php

namespace Yoast\Version_Controller;

use Yoast\Version_Controller\Plugin\Plugin;

class Plugin_Options {
	/**
	 * @param Plugin $plugin
	 *
	 * @return bool
	 */
	public function save_options( Plugin $plugin ) {
		return $this->save_data( $plugin, $this->collect_data( $plugin->get_options() ) );
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	protected function collect_data( array $options ) {
		$data = [];

		foreach ( $options as $option ) {
			$data[ $option ] = get_option( $option, array() );
		}

		return $data;
	}

	/**
	 * @param Plugin $plugin
	 * @param        $data
	 *
	 * @return bool
	 */
	protected function save_data( Plugin $plugin, $data ) {
		$option_name = $this->get_option_name( $plugin );

		$current_data           = (array) get_option( $option_name, array() );
		$current_data[ time() ] = $data;

		// Only keep the 10 latest entries.
		$current_data = array_slice( $current_data, -6, 6, true );

		return update_option( $this->get_option_name( $plugin ), $current_data );
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return array
	 */
	public function get_saved_options( Plugin $plugin ) {
		return (array) get_option( $this->get_option_name( $plugin ), array() );
	}

	/**
	 * @param Plugin $plugin
	 * @param int    $timestamp
	 *
	 * @return bool
	 */
	public function restore_options( Plugin $plugin, $timestamp ) {
		$history = $this->get_saved_options( $plugin );
		if ( ! isset( $history[ $timestamp ] ) ) {
			return false;
		}

		foreach ( $history[ $timestamp ] as $option_name => $option_value ) {
			$this->unhook_option_sanitization( $option_name );
			update_option( $option_name, $option_value );
			$this->hook_option_sanitization( $option_name );
		}

		return true;
	}

	/**
	 * @param $option_name
	 */
	public function unhook_option_sanitization( $option_name ) {
		// Unhook option sanitization, otherwise the version cannot be changed.
		if ( class_exists( '\WPSEO_Options' ) ) {
			$option_instance = \WPSEO_Options::get_option_instance( $option_name );
			remove_filter( 'sanitize_option_' . $option_name, array( $option_instance, 'validate' ) );
		}
	}

	/**
	 * @param $option_name
	 */
	public function hook_option_sanitization( $option_name ) {
		if ( class_exists( '\WPSEO_Options' ) ) {
			$option_instance = \WPSEO_Options::get_option_instance( $option_name );
			add_filter( 'sanitize_option_' . $option_name, array( $option_instance, 'validate' ) );
		}
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_option_name( Plugin $plugin ) {
		return 'yoast_version_backup-' . $plugin->get_identifier();
	}
}
