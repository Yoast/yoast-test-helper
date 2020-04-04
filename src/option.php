<?php

namespace Yoast\WP\Test_Helper;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Option {

	/**
	 * The name of our option.
	 *
	 * @var string
	 */
	private $option_name;

	/**
	 * Holds our options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Option constructor.
	 *
	 * @param string $option_name The option to construct for.
	 */
	public function __construct( $option_name = 'yoast_test_helper' ) {
		$this->option_name = $option_name;
		$this->options     = $this->get_option();
	}

	/**
	 * Retrieve a single option.
	 *
	 * @param string $key The key to retrieve.
	 *
	 * @return mixed|null The content of the retrieved key.
	 */
	public function get( $key ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : null;
	}

	/**
	 * Sets a single option.
	 *
	 * @param string $key   The key to set.
	 * @param mixed  $value The value to set key to.
	 *
	 * @return mixed|null The content of the retrieved key.
	 */
	public function set( $key, $value ) {
		$this->options[ $key ] = $value;

		return $this->save_options();
	}

	/**
	 * Returns the Test Helper option.
	 *
	 * @return array The Test Helper options.
	 */
	private function get_option() {
		return \get_option( $this->option_name );
	}

	/**
	 * Sets the Test Helper option.
	 *
	 * @return bool False if value was not updated and true if value was updated.
	 */
	private function save_options() {
		return \update_option( $this->option_name, $this->options, true );
	}
}
