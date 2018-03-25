<?php
/**
 * Adds the plugin features to the admin page.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

use Yoast\Test_Helper\WordPress_Plugins\WordPress_Plugin;

/**
 * Render plugin features HTML.
 */
class WordPress_Plugin_Features implements Integration {
	/**
	 * Plugins to use.
	 *
	 * @var WordPress_Plugin[]
	 */
	protected $plugins;

	/**
	 * WordPress_Plugin_Features constructor.
	 *
	 * @param WordPress_Plugin[] $plugins Plugins to use.
	 */
	public function __construct( $plugins ) {
		$this->plugins = $plugins;
	}

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		foreach ( $this->plugins as $plugin ) {
			add_action(
				'admin_post_' . $plugin->get_identifier() . '-feature-reset',
				array( $this, 'handle_reset_feature' )
			);
		}
	}

	/**
	 * Retrieves controls.
	 *
	 * @return string Combined features.
	 */
	public function get_controls() {
		$output = array_map( array( $this, 'get_plugin_features' ), $this->plugins );

		return implode( '', $output );
	}

	/**
	 * Retrieves the plugin features of a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin Plugin to retrieve the features of.
	 *
	 * @return string Combined plugin features.
	 */
	protected function get_plugin_features( WordPress_Plugin $plugin ) {
		$features = $plugin->get_features();
		if ( array() === $features ) {
			return '';
		}

		$action = $plugin->get_identifier() . '-feature-reset';
		$form   = '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$form  .= '<input type="hidden" name="action" value="' . $action . '">';
		$form  .= wp_nonce_field( $action, '_wpnonce', true, false );

		return sprintf(
			'<h2>%s</h2>%s%s</form>',
			esc_html( $plugin->get_name() ),
			$form,
			implode(
				'', array_map(
					function ( $name, $feature ) {
						return sprintf(
							'<button name="%s" type="submit" class="button">Reset %s</button> ',
							$feature,
							$name
						);
					}, $features, array_keys( $features )
				)
			)
		);
	}

	/**
	 * Handles resetting a feature.
	 *
	 * @return void
	 */
	public function handle_reset_feature() {
		foreach ( $this->plugins as $plugin ) {
			$action = $plugin->get_identifier() . '-feature-reset';

			if ( check_admin_referer( $action ) === false ) {
				continue;
			}

			if ( $_POST['action'] !== $action ) {
				continue;
			}

			$this->reset_feature( $plugin );
			break;
		}

		wp_safe_redirect( self_admin_url( '?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}

	/**
	 * Detects if a feature must be reset for a specific plugin.
	 *
	 * @param WordPress_Plugin $plugin Plugin to reset a feature of.
	 *
	 * @return void
	 */
	protected function reset_feature( WordPress_Plugin $plugin ) {
		foreach ( $plugin->get_features() as $feature => $name ) {
			if ( ! isset( $_POST[ $feature ] ) ) {
				continue;
			}

			if ( check_admin_referer( $plugin->get_identifier() . '-feature-reset' ) === false ) {
				continue;
			}

			$notification = new Notification(
				$plugin->get_name() . ' feature <strong>' . $name . '</strong> could not be reset.',
				'error'
			);

			if ( $plugin->reset_feature( $feature ) ) {
				$notification = new Notification(
					$plugin->get_name() . ' feature <strong>' . $name . '</strong> has been reset.',
					'success'
				);
			}

			do_action( 'yoast_version_controller_notification', $notification );
		}
	}
}
