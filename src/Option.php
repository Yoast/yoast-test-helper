<?php
/**
 * Option handler
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Option {
	/**
	 * The name of our option.
	 *
	 * @var string
	 */
	public static $option_name = 'yoast_test_helper';

	/**
	 * Returns the Test Helper option.
	 *
	 * @return array The Test Helper options.
	 */
	public static function get_option() {
		return get_option( self::$option_name );
	}

	/**
	 * Sets the Test Helper option.
	 *
	 * @param array $options The Test Helper options to save.
	 *
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public static function set_option( $options ) {
		return update_option( self::$option_name, $options );
	}
}
