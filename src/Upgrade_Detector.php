<?php

namespace Yoast\Version_Controller;

class Upgrade_Detector implements Integration {

	/**
	 * @return mixed
	 */
	public function add_hooks() {
		add_action( 'wpseo_run_upgrade', [ $this, 'add_upgrade_ran_notification' ] );
	}

	/**
	 *
	 */
	public function add_upgrade_ran_notification() {
		$notification = new Notification( 'The WPSEO upgrade routine was executed.', 'success' );
		do_action( 'yoast_version_controller_notification', $notification );
	}
}
