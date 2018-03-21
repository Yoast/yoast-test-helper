<?php

namespace Yoast\Test_Helper;

use Yoast\Test_Helper\WordPress_Plugins\WordPress_Plugin;

class WordPress_Plugin_Features implements Integration {
	/** @var WordPress_Plugin[] */
	protected $plugins;

	public function __construct( $plugins ) {
		$this->plugins = $plugins;
	}

	public function add_hooks() {
		foreach ( $this->plugins as $plugin ) {
			add_action(
				'admin_post_' . $plugin->get_identifier() . '-feature-reset',
				[ $this, 'handle_reset_feature' ]
			);
		}
	}

	public function get_controls() {
		$output = array_map( [ $this, 'get_plugin_features' ], $this->plugins );

		return implode( '', $output );
	}

	/**
	 * @param WordPress_Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_plugin_features( WordPress_Plugin $plugin ) {
		$features = $plugin->get_features();
		if ( [] === $features ) {
			return '';
		}

		$form = '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">' .
		        '<input type="hidden" name="action" value="' . $plugin->get_identifier() . '-feature-reset">';

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
	 *
	 */
	public function handle_reset_feature() {
		foreach ( $this->plugins as $plugin ) {
			if ( $_POST['action'] !== $plugin->get_identifier() . '-feature-reset' ) {
				continue;
			}

			$this->reset_feature( $plugin );
			break;
		}

		wp_safe_redirect( self_admin_url( '?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}

	/**
	 * @param $plugin
	 */
	protected function reset_feature( WordPress_Plugin $plugin ) {
		foreach ( $plugin->get_features() as $feature => $name ) {
			if ( ! isset( $_POST[ $feature ] ) ) {
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
