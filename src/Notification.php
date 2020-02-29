<?php

namespace Yoast\WP\Test_Helper;

/**
 * Test Helper Notification.
 */
class Notification {

	/**
	 * The notification copy.
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * The type of the notification.
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Constructs a notification.
	 *
	 * @param string $message Notification message.
	 * @param string $type    Notification type.
	 */
	public function __construct( $message, $type = 'info' ) {
		$this->message = $message;
		$this->type    = $type;
	}

	/**
	 * Returns the notification type.
	 *
	 * @return string The notification type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Returns the notification copy.
	 *
	 * @return string The notification copy.
	 */
	public function get_message() {
		return $this->message;
	}
}
