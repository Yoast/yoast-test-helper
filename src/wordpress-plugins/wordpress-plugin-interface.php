<?php

namespace Yoast\WP\Test_Helper\WordPress_Plugins;

/**
 * Interface defining the API for WordPress Plugins.
 */
interface WordPress_Plugin {

	/**
	 * Retrieves the plugin identifier.
	 *
	 * @return string The plugin identifier.
	 */
	public function get_identifier();

	/**
	 * Retrieves the plugin name.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_name();

	/**
	 * Retrieves the version option name.
	 *
	 * @return string The name that holds the version.
	 */
	public function get_version_option_name();

	/**
	 * Retrieves the version key.
	 *
	 * @return string The version key.
	 */
	public function get_version_key();

	/**
	 * Retrieves the options.
	 *
	 * @return array The options.
	 */
	public function get_options();

	/**
	 * Retrieves the list of features.
	 *
	 * @return string[] List of features.
	 */
	public function get_features();

	/**
	 * Retrieves the active version of the plugin.
	 *
	 * @return string The current version of the plugin.
	 */
	public function get_version_constant();

	/**
	 * Resets a feature.
	 *
	 * @param string $feature Feature to reset.
	 *
	 * @return bool True on succes.
	 */
	public function reset_feature( $feature );
}
