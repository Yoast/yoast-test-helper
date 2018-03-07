<?php

namespace Yoast\Version_Controller\Plugin;

interface Plugin {
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
	 * @param string $feature
	 *
	 * @return void
	 */
	public function reset_feature( $feature );
}
