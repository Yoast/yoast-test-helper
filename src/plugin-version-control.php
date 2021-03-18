<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\Test_Helper\WordPress_Plugins\WordPress_Plugin;

/**
 * Adds the plugin version controls to the admin page.
 */
class Plugin_Version_Control implements Integration {

	/**
	 * The plugin version instance to use.
	 *
	 * @var WordPress_Plugin_Version
	 */
	protected $plugin_version;

	/**
	 * The plugin options to use.
	 *
	 * @var WordPress_Plugin_Options
	 */
	protected $plugin_options;

	/**
	 * The list of plugins to use.
	 *
	 * @var WordPress_Plugin[]
	 */
	protected $plugins;

	/**
	 * WordPress_Plugin_Version_Control constructor.
	 *
	 * @param array                    $plugins        Plugins to use.
	 * @param WordPress_Plugin_Version $plugin_version Plugin version to use.
	 * @param WordPress_Plugin_Options $plugin_options Plugin options to use.
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
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action( 'admin_post_yoast_version_control', [ $this, 'handle_submit' ] );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {

		$output  = '<table>';
		$output .= '<thead><tr>';
		$output .= '<th style="text-align:left;">' . \__( 'Plugin', 'yoast-test-helper' ) . '</th>';
		$output .= '<th style="text-align:left;">' . \__( 'DB Version', 'yoast-test-helper' ) . '</th>';
		$output .= '<th style="text-align:left;">' . \__( 'Real', 'yoast-test-helper' ) . '</th>';
		$output .= '<th style="text-align:left;">' . \__( 'Saved options', 'yoast-test-helper' ) . '</th>';
		$output .= '</tr></thead>';

		foreach ( $this->plugins as $plugin ) {
			$output .= $this->get_plugin_option( $plugin );
		}
		$output .= '</table>';

		return Form_Presenter::get_html( \__( 'Plugin options & database versions', 'yoast-test-helper' ), 'yoast_version_control', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( ! $this->load_history() && \check_admin_referer( 'yoast_version_control' ) !== false ) {
			foreach ( $this->plugins as $plugin ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$this->update_plugin_version( $plugin, $_POST[ $plugin->get_identifier() ] );
			}
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Updates the plugin version.
	 *
	 * @param WordPress_Plugin $plugin  Plugin to update a version of.
	 * @param string           $version Version to update.
	 */
	protected function update_plugin_version( WordPress_Plugin $plugin, $version ) {
		if ( $this->plugin_version->update_version( $plugin, $version ) ) {
			\do_action(
				'Yoast\WP\Test_Helper\notification',
				new Notification(
					\sprintf(
						/* translators: %1$s expands to the plugin name, %2$s to the version. */
						\__( '%1$s version was set to %2$s.', 'yoast-test-helper' ),
						$plugin->get_name(),
						$version
					),
					'success'
				)
			);
		}

		if ( $this->plugin_options->save_options( $plugin ) ) {
			\do_action(
				'Yoast\WP\Test_Helper\notification',
				new Notification(
					\sprintf(
						/* translators: %1$s expands to the plugin name. */
						\__( '%1$s options were saved.', 'yoast-test-helper' ),
						$plugin->get_name()
					),
					'success'
				)
			);
		}
	}

	/**
	 * Retrieves a plugin option.
	 *
	 * @param WordPress_Plugin $plugin Plugin to retrieve the option of.
	 *
	 * @return string The plugin option.
	 */
	protected function get_plugin_option( WordPress_Plugin $plugin ) {
		return \sprintf(
			'<tr><td>%s:</td><td><input type="text" name="%s" value="%s" maxlength="9" size="10"></td><td>(%s)</td><td>%s</td></tr>',
			\esc_html( $plugin->get_name() ),
			\esc_attr( $plugin->get_identifier() ),
			\esc_attr( $this->plugin_version->get_version( $plugin ) ),
			\esc_html( $plugin->get_version_constant() ),
			$this->get_option_history_select( $plugin )
		);
	}

	/**
	 * Retrieves the plugin stored options history.
	 *
	 * @param WordPress_Plugin $plugin Plugin to retrieve the history of.
	 *
	 * @return string The plugin option history.
	 */
	protected function get_option_history_select( WordPress_Plugin $plugin ) {
		$history = $this->plugin_options->get_saved_options( $plugin );
		$history = \array_reverse( $history, true );

		return \sprintf(
			'<select name="%s"><option value=""></option>%s</select>',
			\esc_attr( $plugin->get_identifier() . '-history' ),
			\implode(
				'',
				\array_map(
					static function ( $timestamp, $item ) use ( $plugin ) {
						$version_option = $plugin->get_version_option_name();
						$version_key    = $plugin->get_version_key();
						if ( ! empty( $version_key ) ) {
							$version = '';
							if ( isset( $item[ $version_option ][ $version_key ] ) ) {
								$version = $item[ $version_option ][ $version_key ];
							}
						}
						else {
							$version = $item[ $version_option ];
						}

						return \sprintf(
							'<option value="%s">(%s) %s</option>',
							\esc_attr( $timestamp ),
							\esc_html( $version ),
							\esc_html( \gmdate( 'Y-m-d H:i:s', $timestamp ) )
						);
					},
					\array_keys( $history ),
					$history
				)
			)
		);
	}

	/**
	 * Loads the history.
	 *
	 * @return bool
	 */
	protected function load_history() {
		if ( \check_admin_referer( 'yoast_version_control' ) === false ) {
			return false;
		}

		foreach ( $this->plugins as $plugin ) {
			// If history is set, load the history item, otherwise save.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$timestamp = $_POST[ $plugin->get_identifier() . '-history' ];
			if ( ! empty( $timestamp ) ) {
				$notification = new Notification(
					\sprintf(
						/* translators: %1$s expands to date, %2$s to plugin name, %3$s and %4$s to HTML strong tags. */
						\esc_html__( 'Options from %1$s for %2$s have %3$snot%4$s been restored.', 'yoast-test-helper' ),
						\gmdate( 'Y-m-d H:i:s', $timestamp ),
						$plugin->get_name(),
						'<strong>',
						'</strong>'
					),
					'error'
				);

				if ( $this->plugin_options->restore_options( $plugin, $timestamp ) ) {
					$notification = new Notification(
						\sprintf(
							/* translators: %1$s expands to date, %2$s to plugin name. */
							\esc_html__( 'Options from %1$s for %2$s have been restored.', 'yoast-test-helper' ),
							\gmdate( 'Y-m-d H:i:s', $timestamp ),
							$plugin->get_name()
						),
						'success'
					);
				}

				\do_action( 'Yoast\WP\Test_Helper\notification', $notification );

				return true;
			}
		}

		return false;
	}
}
