<?php

namespace Yoast\Version_Controller;

class Admin_Notifications {
	/** @var Notification[] */
	protected $notifications;

	public function add_hooks() {
		add_action( 'yoast_version_controller-notification', [ $this, 'add_notification' ], 10, 2 );

		add_action( 'yoast_version_controller-notifications', [ $this, 'display_notifications' ] );
	}

	public function add_notification( Notification $notification ) {
		$notifications   = $this->get_notifications();
		$notifications[] = $notification;

		$this->save_notifications( $notifications );
	}

	public function display_notifications() {
		$notifications = $this->get_notifications();
		if ( ! $notifications ) {
			return;
		}

		echo '<div style="margin: 15px 0 15px -15px;">';
		foreach ( $notifications as $notification ) {
			echo '<div class="notice notice-' . $notification->get_type() . '"><p>' . $notification->get_message() . '</p></div>';
		}
		echo '</div>';

		delete_user_meta( get_current_user_id(), $this->get_option_name() );
	}

	/**
	 * @return Notification[] List of notifications.
	 */
	protected function get_notifications() {
		$saved = get_user_meta( get_current_user_id(), $this->get_option_name(), true );
		if ( ! is_array( $saved ) ) {
			return array();
		}

		return $saved;
	}

	protected function get_option_name() {
		return 'wpseo_version_control_notifications';
	}

	/**
	 * @param $notifications
	 */
	protected function save_notifications( $notifications ) {
		update_user_meta( get_current_user_id(), $this->get_option_name(), $notifications );
	}
}
