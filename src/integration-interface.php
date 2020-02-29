<?php

namespace Yoast\WP\Test_Helper;

/**
 * WordPress Integration interface.
 */
interface Integration {

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks();
}
