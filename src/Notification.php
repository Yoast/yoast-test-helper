<?php

namespace Yoast\Version_Controller;

class Notification {
	protected $message;
	protected $type;

	public function __construct( $message, $type = 'info' ) {
		$this->message = $message;
		$this->type    = $type;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_message() {
		return $this->message;
	}
}
