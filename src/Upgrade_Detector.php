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
		add_action( 'wpseo_run_upgrade', array( $this, 'yoast_seo_upgrade_ran' ) );
		add_action( 'update_option_wpseo_premium_version', array( $this, 'yoast_seo_premium_upgrade_ran' ) );
	}

	/**
	 * Adds the notification to the stack.
	 *
	 * @return void
	 */
	public function yoast_seo_upgrade_ran() {
		$this->add_notification( 'The Yoast SEO upgrade routine was executed.' );
	}

	/**
	 * Adds the notification to the stack.
	 *
	 * @return void
	 */
	public function yoast_seo_premium_upgrade_ran() {
		$this->add_notification( 'The Yoast SEO Premium upgrade routine was executed.' );
	}

	/**
	 * Adds a success notitifcation for an upgrade.
	 *
	 * @param string $notification_text The notification text to show.
	 *
	 * @return void
	 */
	private function add_notification( $notification_text ) {
		$notification = new Notification( $notification_text, 'success' );
		do_action( 'yoast_version_controller_notification', $notification );
	}
}
