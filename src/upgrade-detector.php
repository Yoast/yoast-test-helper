<?php

namespace Yoast\WP\Test_Helper;

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
		\add_action( 'wpseo_run_upgrade', [ $this, 'yoast_seo_upgrade_ran' ] );
		\add_action( 'update_option_wpseo_premium_version', [ $this, 'yoast_seo_premium_upgrade_ran' ] );
	}

	/**
	 * Adds the notification to the stack.
	 *
	 * @return void
	 */
	public function yoast_seo_upgrade_ran() {
		$this->add_notification( \esc_html__( 'The Yoast SEO upgrade routine was executed.', 'yoast-test-helper' ) );
	}

	/**
	 * Adds the notification to the stack.
	 *
	 * @return void
	 */
	public function yoast_seo_premium_upgrade_ran() {
		$this->add_notification( \esc_html__( 'Yoast SEO Premium updated its version number, which should mean the upgrade routine was executed.', 'yoast-test-helper' ) );
	}

	/**
	 * Adds a success notification for an upgrade.
	 *
	 * @param string $notification_text The notification text to show.
	 *
	 * @return void
	 */
	private function add_notification( $notification_text ) {
		$notification = new Notification( $notification_text, 'success' );
		\do_action( 'Yoast\WP\Test_Helper\notification', $notification );
	}
}
