<?php
/**
 * Detects if an upgrade is ran.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Upgrade detector, spawns a notification if an upgrade is being run.
 */
class Upgrade_Detector implements Integration {
	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'wpseo_run_upgrade', array( $this, 'add_upgrade_ran_notification' ) );
	}

	/**
	 * Adds the notification to the stack.
	 *
	 * @return void
	 */
	public function add_upgrade_ran_notification() {
		$notification = new Notification( 'The WPSEO upgrade routine was executed.', 'success' );
		do_action( 'yoast_version_controller_notification', $notification );
	}
}
