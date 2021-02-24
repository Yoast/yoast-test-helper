<?php

namespace Yoast\WP\Test_Helper;

/**
 * Shows admin notifications on the proper page.
 */
class Admin_Notifications implements Integration {

	/**
	 * List of notifications.
	 *
	 * @var Notification[]
	 */
	protected $notifications;

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action( 'Yoast\WP\Test_Helper\notification', [ $this, 'add_notification' ], 10, 2 );
		\add_action( 'Yoast\WP\Test_Helper\notifications', [ $this, 'display_notifications' ] );
	}

	/**
	 * Adds a notification to the stack.
	 *
	 * @param Notification $notification Notification to add.
	 *
	 * @return void
	 */
	public function add_notification( Notification $notification ) {
		$notifications   = $this->get_notifications();
		$notifications[] = $notification;

		$this->save_notifications( $notifications );
	}

	/**
	 * Displays a notification.
	 *
	 * @return void
	 */
	public function display_notifications() {
		$notifications = $this->get_notifications();
		if ( ! $notifications ) {
			return;
		}

		foreach ( $notifications as $notification ) {
			if ( ! \is_a( $notification, Notification::class ) ) {
				continue;
			}
		}

		echo '<div style="margin: 15px 0 15px -15px;">';
		foreach ( $notifications as $notification ) {
			echo '<div class="notice notice-', \esc_attr( $notification->get_type() ), '"><p>', \wp_kses_post( $notification->get_message() ), '</p></div>';
		}
		echo '</div>';

		\delete_user_meta( \get_current_user_id(), $this->get_option_name() );
	}

	/**
	 * Retrieves the list of notifications.
	 *
	 * @return Notification[] List of notifications.
	 */
	protected function get_notifications() {
		$saved = \get_user_meta( \get_current_user_id(), $this->get_option_name(), true );
		if ( ! \is_array( $saved ) ) {
			return [];
		}

		return $saved;
	}

	/**
	 * Returns the name of the option that saves the notifications.
	 *
	 * @return string The name of the option.
	 */
	protected function get_option_name() {
		return 'yoast_test_helper_notifications';
	}

	/**
	 * Saves the notifications for the next page request.
	 *
	 * @param Notification[] $notifications Notifications to save.
	 *
	 * @return void
	 */
	protected function save_notifications( $notifications ) {
		\update_user_meta( \get_current_user_id(), $this->get_option_name(), $notifications );
	}
}
