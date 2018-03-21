<?php

namespace Yoast\Test_Helper;

use Yoast\Test_Helper\WordPress_Plugins\WordPress_Plugin;

class WordPress_Plugin_Version_Control implements Integration {
	/** @var WordPress_Plugin_Version */
	protected $plugin_version;

	/** @var WordPress_Plugin_Options */
	protected $plugin_options;

	/** @var WordPress_Plugin[] */
	protected $plugins;

	/**
	 * WordPress_Plugin_Version_Control constructor.
	 *
	 * @param array                    $plugins
	 * @param WordPress_Plugin_Version $plugin_version
	 * @param WordPress_Plugin_Options $plugin_options
	 */
	public function __construct(
		array $plugins,
		WordPress_Plugin_Version $plugin_version,
		WordPress_Plugin_Options $plugin_options
	) {
		$this->plugins        = $plugins;
		$this->plugin_version = $plugin_version;
		$this->plugin_options = $plugin_options;
	}

	/**
	 * @return mixed|void
	 */
	public function add_hooks() {
		add_action( 'admin_post_yoast_version_control', [ $this, 'handle_submit' ] );
	}

	/**
	 * @return string
	 */
	public function get_controls() {
		$output = '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= '<input type="hidden" name="action" value="yoast_version_control">';

		$output .= '<table>';
		$output .= '<thead><tr>' .
		           '<th style="text-align:left;">Plugin</th>' .
		           '<th style="text-align:left;">DB Version</th>' .
		           '<th style="text-align:left;">Real</th>' .
		           '<th style="text-align:left;">Saved options</th>' .
		           '</tr></thead>';

		foreach ( $this->plugins as $plugin ) {
			$output .= $this->get_plugin_option( $plugin );
		}
		$output .= '</table>';

		$output .= '<button class="button button-primary">Save</button>';
		$output .= '</form>';

		return $output;
	}

	/**
	 *
	 */
	public function handle_submit() {
		if ( ! $this->load_history() ) {
			foreach ( $this->plugins as $plugin ) {
				$this->update_plugin_version( $plugin, $_POST[ $plugin->get_identifier() ] );
			}
		}

		wp_safe_redirect( self_admin_url( '?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}

	/**
	 * @param WordPress_Plugin $plugin
	 * @param                  $version
	 */
	protected function update_plugin_version( WordPress_Plugin $plugin, $version ) {
		if ( $this->plugin_version->update_version( $plugin, $version ) ) {
			do_action(
				'yoast_version_controller_notification',
				new Notification( $plugin->get_name() . ' version was set to ' . $version, 'success' )
			);
		}

		if ( $this->plugin_options->save_options( $plugin ) ) {
			do_action(
				'yoast_version_controller_notification',
				new Notification( $plugin->get_name() . ' options were saved.', 'success' )
			);
		}
	}

	/**
	 * @param WordPress_Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_plugin_option( WordPress_Plugin $plugin ) {
		return sprintf(
			'<tr><td>%s:</td><td><input type="text" name="%s" value="%s" maxlength="7" size="8"></td><td>(%s)</td><td>%s</td></tr>',
			esc_html( $plugin->get_name() ),
			esc_attr( $plugin->get_identifier() ),
			esc_attr( $this->plugin_version->get_version( $plugin ) ),
			esc_html( $plugin->get_version_constant() ),
			$this->get_option_history_select( $plugin )
		);
	}

	/**
	 * @param WordPress_Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_option_history_select( WordPress_Plugin $plugin ) {
		$history = $this->plugin_options->get_saved_options( $plugin );
		$history = array_reverse( $history, true );

		return sprintf(
			'<select name="%s"><option value=""></option>%s</select>',
			esc_attr( $plugin->get_identifier() . '-history' ),
			implode(
				'', array_map(
					function ( $timestamp, $item ) use ( $plugin ) {
						$version = $item[ $plugin->get_version_option_name() ][ $plugin->get_version_key() ];

						return sprintf(
							'<option value="%s">(%s) %s</option>', esc_attr( $timestamp ),
							esc_html( $version ),
							esc_html( date( 'Y-m-d H:i:s', $timestamp ) )
						);
					}, array_keys( $history ), $history
				)
			)
		);
	}

	/**
	 * @return bool
	 */
	protected function load_history() {
		foreach ( $this->plugins as $plugin ) {
			// if -history is set, load the history item, otherwise save.
			$timestamp = $_POST[ $plugin->get_identifier() . '-history' ];
			if ( ! empty( $timestamp ) ) {
				$notification = new Notification(
					'Options from ' . date( 'Y-m-d H:i:s', $timestamp ) .
					' for ' . $plugin->get_name() . ' have <strong>not</strong> been restored.',
					'error'
				);

				if ( $this->plugin_options->restore_options( $plugin, $timestamp ) ) {
					$notification = new Notification(
						'Options from ' . date( 'Y-m-d H:i:s', $timestamp ) .
						' for ' . $plugin->get_name() . ' have been restored.',
						'success'
					);
				}

				do_action( 'yoast_version_controller_notification', $notification );

				return true;
			}
		}

		return false;
	}
}
