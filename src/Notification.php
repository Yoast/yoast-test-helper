<?php

namespace Yoast\Test_Helper;

class Notification {
	/** @var string */
	protected $message = '';

	/** @var string */
	protected $type = '';

	/**
	 * Notification constructor.
	 *
	 * @param string $message
	 * @param string $type
	 */
	public function __construct( $message, $type = 'info' ) {
		$this->message = $message;
		$this->type    = $type;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}
}
