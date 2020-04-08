<?php

namespace Yoast\WP\Test_Helper;

use WPSEO_Options;
use Yoast\WP\Test_Helper\WordPress_Plugins\WordPress_Plugin;

/**
 * Store and retrieve plugin options.
 */
class WordPress_Plugin_Options {

	/**
	 * Saves the options for a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin The plugin to save options of.
	 *
	 * @return bool True if options were saved.
	 */
	public function save_options( WordPress_Plugin $plugin ) {
		return $this->save_data( $plugin, $this->collect_data( $plugin->get_options() ) );
	}

	/**
	 * Collects the data from specified options.
	 *
	 * @param array $options Options to collect.
	 *
	 * @return array Data collected.
	 */
	protected function collect_data( array $options ) {
		$data = [];

		foreach ( $options as $option ) {
			$option_value = $this->get_option( $option );
			if ( $option_value !== [] ) {
				$data[ $option ] = $option_value;
			}
		}

		return $data;
	}

	/**
	 * Stores the data of a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin Plugin to store data of.
	 * @param array            $data   Data to store.
	 *
	 * @return bool True if stored.
	 */
	protected function save_data( WordPress_Plugin $plugin, $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		$option_name = $this->get_option_name( $plugin );

		$current_data            = (array) \get_option( $option_name, [] );
		$current_data[ \time() ] = $data;

		// Only keep the 10 latest entries.
		$current_data = \array_slice( $current_data, -6, 6, true );

		return \update_option( $this->get_option_name( $plugin ), $current_data, false );
	}

	/**
	 * Retrieves saved options for a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin Plugin to retrieve options of.
	 *
	 * @return array Stored data.
	 */
	public function get_saved_options( WordPress_Plugin $plugin ) {
		return $this->get_option( $this->get_option_name( $plugin ) );
	}

	/**
	 * Retrieves the data of a specific option.
	 *
	 * Does not use the WordPress API to make sure raw data is retrieved.
	 *
	 * @param string $name Name of the option to retrieve.
	 *
	 * @return mixed[] Contents of the option.
	 */
	protected function get_option( $name ) {
		global $wpdb;

		$result = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
				$name
			)
		);

		if ( empty( $result ) ) {
			return [];
		}

		return \maybe_unserialize( $result[0] );
	}

	/**
	 * Restores options of a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin    Plugin to restore options of.
	 * @param int              $timestamp Specific save point to restore.
	 *
	 * @return bool True on succes.
	 */
	public function restore_options( WordPress_Plugin $plugin, $timestamp ) {
		$history = $this->get_saved_options( $plugin );
		if ( ! isset( $history[ $timestamp ] ) ) {
			return false;
		}

		foreach ( $history[ $timestamp ] as $option_name => $option_value ) {
			$this->unhook_option_sanitization( $option_name );

			if ( $option_value === [] ) {
				\delete_option( $option_name );
			}
			else {
				\update_option( $option_name, $option_value, false );
			}

			$this->hook_option_sanitization( $option_name );
		}

		return true;
	}

	/**
	 * Unhooks option sanitization filters.
	 *
	 * @param string $option_name Option name to unhook the filters of.
	 *
	 * @return void
	 */
	public function unhook_option_sanitization( $option_name ) {
		// Unhook option sanitization, otherwise the version cannot be changed.
		if ( \class_exists( WPSEO_Options::class ) ) {
			$option_instance = WPSEO_Options::get_option_instance( $option_name );
			\remove_filter( 'sanitize_option_' . $option_name, [ $option_instance, 'validate' ] );
		}
	}

	/**
	 * Hooks option sanitization filters.
	 *
	 * @param string $option_name Option name to hook the filters of.
	 *
	 * @return void
	 */
	public function hook_option_sanitization( $option_name ) {
		if ( \class_exists( WPSEO_Options::class ) ) {
			$option_instance = WPSEO_Options::get_option_instance( $option_name );
			\add_filter( 'sanitize_option_' . $option_name, [ $option_instance, 'validate' ] );
		}
	}

	/**
	 * Returns the option name which stores the option data of a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin The plugin.
	 *
	 * @return string The option name the data should be stored in.
	 */
	protected function get_option_name( WordPress_Plugin $plugin ) {
		return 'yoast_version_backup-' . $plugin->get_identifier();
	}
}
