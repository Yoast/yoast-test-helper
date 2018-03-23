<?php
/**
 * Interface Integration
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

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
