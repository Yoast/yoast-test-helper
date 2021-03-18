<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\Test_Helper\WordPress_Plugins\WordPress_Plugin;

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
			\add_action(
				'admin_post_' . $plugin->get_identifier() . '-feature-reset',
				[ $this, 'handle_reset_feature' ]
			);
		}
	}

	/**
	 * Retrieves controls.
	 *
	 * @return string Combined features.
	 */
	public function get_controls() {
		$output = \array_map( [ $this, 'get_plugin_features' ], $this->plugins );

		return \implode( '', $output );
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
		if ( $features === [] ) {
			return '';
		}

		$action = $plugin->get_identifier() . '-feature-reset';

		$fields = \implode(
			'',
			\array_map(
				static function ( $name, $feature ) {
					return \sprintf(
						'<button id="%s" name="%s" type="submit" class="button secondary">' . \esc_html__( 'Reset', 'yoast-test-helper' ) . ' %s</button> ',
						\esc_attr( $feature ) . '_button',
						\esc_attr( $feature ),
						\esc_html( $name )
					);
				},
				$features,
				\array_keys( $features )
			)
		);

		return Form_Presenter::get_html( $plugin->get_name(), $action, $fields, false );
	}

	/**
	 * Handles resetting a feature.
	 *
	 * @return void
	 */
	public function handle_reset_feature() {
		foreach ( $this->plugins as $plugin ) {
			$action = $plugin->get_identifier() . '-feature-reset';

			if ( \check_admin_referer( $action ) === false ) {
				continue;
			}

			if ( isset( $_POST['action'] ) && $action !== $_POST['action'] ) {
				continue;
			}

			$this->reset_feature( $plugin );
			break;
		}

		\wp_safe_redirect(
			\self_admin_url(
				'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' )
			)
		);
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

			if ( \check_admin_referer( $plugin->get_identifier() . '-feature-reset' ) === false ) {
				continue;
			}

			$notification = new Notification(
				\sprintf(
					/* translators: %1$s expands to the plugin name, %2$s to the feature name. */
					\esc_html__( '%1$s feature %2$s could not be reset.', 'yoast-test-helper' ),
					$plugin->get_name(),
					'<strong>' . $name . '</strong>'
				),
				'error'
			);

			if ( $plugin->reset_feature( $feature ) ) {
				$notification = new Notification(
					\sprintf(
						/* translators: %1$s expands to the plugin name, %2$s to the feature name. */
						\esc_html__( '%1$s feature %2$s has been reset.', 'yoast-test-helper' ),
						$plugin->get_name(),
						'<strong>' . $name . '</strong>'
					),
					'success'
				);
			}

			\do_action( 'Yoast\WP\Test_Helper\notification', $notification );
		}
	}
}
