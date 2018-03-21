<?php

namespace Yoast\Test_Helper\WordPress_Plugins;

interface WordPress_Plugin {
	/**
	 * @return string
	 */
	public function get_identifier();

	/**
	 * @return string
	 */
	public function get_name();

	/**
	 * @return string
	 */
	public function get_version_option_name();

	/**
	 * @return string
	 */
	public function get_version_key();

	/**
	 * @return array
	 */
	public function get_options();

	/**
	 * @return array
	 */
	public function get_features();

	/**
	 * @return string
	 */
	public function get_version_constant();

	/**
	 * @param string $feature
	 *
	 * @return bool
	 */
	public function reset_feature( $feature );
}
