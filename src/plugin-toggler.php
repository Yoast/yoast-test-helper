<?php

namespace Yoast\WP\Test_Helper;

use WPSEO_Utils;

/**
 * Toggles between plugins.
 */
class Plugin_Toggler implements Integration {

	/**
	 * The plugins per group.
	 *
	 * @var array
	 */
	private $plugin_groups = [];

	/**
	 * Regex with groups to filter the available plugins by name.
	 *
	 * @var string
	 */
	private $grouped_name_filter = '/^(Yoast SEO)$|^(Yoast SEO)[^:]{1}/';

	/**
	 * Holds our option instance.
	 *
	 * @var Option
	 */
	private $option;

	/**
	 * Class constructor.
	 *
	 * @param Option $option Our option array.
	 */
	public function __construct( Option $option ) {
		$this->option = $option;
	}

	/**
	 * Constructs the object and set init hook.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action( 'plugins_loaded', [ $this, 'init' ] );

		\add_action(
			'admin_post_yoast_seo_plugin_toggler',
			[ $this, 'handle_submit' ]
		);
	}

	/**
	 * Initialize plugin.
	 *
	 * Check for rights and look which plugin is active.
	 * Also adding hooks
	 *
	 * @return void
	 */
	public function init() {
		if ( ! $this->has_rights() ) {
			return;
		}

		if ( $this->option->get( 'plugin_toggler' ) !== true ) {
			return;
		}

		// Load WordPress core plugin.php when needed.
		if (
			! \function_exists( 'is_plugin_active' )
			|| ! \function_exists( 'get_plugins' )
		) {
			include_once \ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Apply filters to adapt the $this->grouped_name_filter property.
		$this->grouped_name_filter = \apply_filters( 'Yoast\WP\Test_Helper\plugin_toggler_filter', $this->grouped_name_filter );

		$this->init_plugin_groups();

		// Adding the hooks.
		$this->add_additional_hooks();
	}

	/**
	 * Adds the toggle fields to the page.
	 *
	 * @return void
	 */
	public function add_toggle() {
		$nonce = \wp_create_nonce( 'yoast-plugin-toggle' );

		/** \WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		// Add a menu for each group.
		foreach ( $this->plugin_groups as $group => $plugins ) {
			$active_plugin = $this->get_active_plugin( $group );
			$menu_id       = 'wpseo-plugin-toggler-' . \sanitize_title( $group );
			$menu_title    = $active_plugin;

			// Menu title fallback: active plugin > group > first plugin.
			if ( $menu_title === '' ) {
				$menu_title = $group;
				if ( $menu_title === '' ) {
					\reset( $plugins );
					$menu_title = \key( $plugins );
				}
			}

			$wp_admin_bar->add_menu(
				[
					'parent' => false,
					'id'     => $menu_id,
					'title'  => $menu_title,
					'href'   => '#',
				]
			);

			// Add a node for each plugin.
			foreach ( $plugins as $plugin => $plugin_path ) {
				if ( $plugin === $active_plugin ) {
					continue;
				}

				$wp_admin_bar->add_node(
					[
						'parent' => $menu_id,
						'id'     => 'wpseo-plugin-toggle-' . \sanitize_title( $plugin ),
						'title'  => 'Switch to ' . $plugin,
						'href'   => '#',
						'meta'   => [
							'onclick' => \sprintf(
								'Yoast_Plugin_Toggler.toggle_plugin( "%1$s", "%2$s", "%3$s" )',
								$group,
								$plugin,
								$nonce
							),
						],
					]
				);
			}
		}
	}

	/**
	 * Adding the assets to the page.
	 *
	 * @return void
	 */
	public function add_assets() {
		// JS file.
		\wp_enqueue_script(
			'yoast-toggle-script',
			\plugin_dir_url( \YOAST_TEST_HELPER_FILE ) . 'assets/js/yoast-toggle.js',
			[],
			\YOAST_TEST_HELPER_VERSION,
			true
		);
	}

	/**
	 * Toggle between the plugins.
	 *
	 * The active plugin will be deactivated. The inactive plugin will be printed as JSON and will be used to active
	 * this plugin in another AJAX request.
	 *
	 * @return void
	 */
	public function ajax_toggle_plugin() {

		$response = [];

		// If nonce is valid.
		if ( $this->verify_nonce() ) {
			$group  = \filter_input( \INPUT_GET, 'group' );
			$plugin = \filter_input( \INPUT_GET, 'plugin' );

			// First deactivate the current plugin.
			$this->deactivate_plugin_group( $group );
			$this->activate_plugin( $group, $plugin );

			$response = [
				'activated_plugin' => [
					'group'  => $group,
					'plugin' => $plugin,
				],
			];
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The util takes care of escaping.
		echo WPSEO_Utils::format_json_encode( $response );
		die();
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'plugin_toggler',
			\esc_html__( 'Show plugin toggler.', 'yoast-test-helper' ),
			$this->option->get( 'plugin_toggler' )
		);

		return Form_Presenter::get_html( \__( 'Plugin toggler', 'yoast-test-helper' ), 'yoast_seo_plugin_toggler', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_plugin_toggler' ) !== false ) {
			$this->option->set( 'plugin_toggler', isset( $_POST['plugin_toggler'] ) );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Check if there are enough rights to display the toggle
	 *
	 * If current page is adminpage and current user can activatie plugins return true
	 *
	 * @return bool True if the rights are present.
	 */
	private function has_rights() {
		return ( \is_admin() && \current_user_can( 'activate_plugins' ) );
	}

	/**
	 * Retrieves a grouped and filtered list of installed plugins.
	 *
	 * Uses WordPress's 'get_plugins' for the list of installed plugins.
	 * Uses $this->grouped_name_filter regex to get the group.
	 *
	 * Example:
	 * <code>
	 * $this->grouped_name_filter = '/^(Yoast SEO)$|^(Yoast SEO)[^:]{1}/'
	 * $plugin_groups = array(
	 *   'Yoast SEO' => array(
	 *     'Yoast SEO'             => 'wordpress-seo/wp-seo.php',
	 *     'Yoast SEO 8.4'         => 'wordpress-seo 8.4/wp-seo.php',
	 *     'Yoast SEO Premium'     => 'wordpress-seo-premium/wp-seo-premium.php',
	 *     'Yoast SEO Premium 8.4' => 'wordpress-seo-premium 8.4/wp-seo-premium.php',
	 *   ),
	 * );
	 * </code>
	 *
	 * @return array The plugins grouped by the regex matches.
	 */
	private function get_plugin_groups() {
		// Use WordPress to get all the plugins with their data.
		$plugins       = \get_plugins();
		$plugin_groups = [];

		foreach ( $plugins as $file => $data ) {
			$plugin = $data['Name'];
			$group  = $this->get_group_from_plugin_name( $plugin );
			if ( $group === '' ) {
				continue;
			}

			// Save the plugin under a group.
			if ( ! isset( $plugin_groups[ $group ] ) ) {
				$plugin_groups[ $group ] = [];
			}
			$plugin_groups[ $group ][ $plugin ] = $file;
		}

		return $plugin_groups;
	}

	/**
	 * Retrieves the group of the plugin via a regular expression.
	 *
	 * Example filter:
	 * $grouped_name_filter = '/^(Yoast SEO)$|^(Yoast SEO)[^:]{1}/'
	 *
	 * @param string $plugin_name The plugin name.
	 *
	 * @return string The group.
	 */
	private function get_group_from_plugin_name( $plugin_name ) {
		$matches = [];

		if ( \preg_match( $this->grouped_name_filter, $plugin_name, $matches ) ) {
			foreach ( $matches as $match ) {
				if ( $match !== '' ) {
					return \trim( $match );
				}
			}
		}

		return '';
	}

	/**
	 * Retrieves a list of installed plugins, pruned by group.
	 *
	 * @param array $plugin_groups Plugins to filter for installed plugins.
	 * @param bool  $prune         Whether to prune the groups if they contain less than 2 plugins. Defaults to true.
	 *
	 * @return array Plugins that are actually installed.
	 */
	private function check_plugins( array $plugin_groups, $prune = true ) {
		$installed = [];

		foreach ( $plugin_groups as $group => $plugins ) {
			foreach ( $plugins as $plugin => $plugin_path ) {
				$full_plugin_path = \ABSPATH . 'wp-content/plugins/' . \plugin_basename( $plugin_path );

				// Add the plugin to the group if it exists.
				if ( \file_exists( $full_plugin_path ) ) {
					$installed[ $group ][ $plugin ] = $plugin_path;
				}
			}

			if ( $prune ) {
				// Remove the group entirely if there are less than 2 plugins in it.
				if ( \count( $installed[ $group ] ) < 2 ) {
					unset( $installed[ $group ] );
				}
			}
		}

		return $installed;
	}

	/**
	 * Retrieves the active plugin of a group. First hit if there are multiple.
	 *
	 * @param string $group The group of to check.
	 *
	 * @return string The plugin name or an empty string.
	 */
	private function get_active_plugin( $group ) {
		if ( ! \array_key_exists( $group, $this->plugin_groups ) ) {
			return '';
		}

		$plugins = $this->plugin_groups[ $group ];
		foreach ( $plugins as $plugin => $plugin_path ) {
			if ( \is_plugin_active( $plugin_path ) ) {
				return $plugin;
			}
		}

		return '';
	}

	/**
	 * Adding the hooks.
	 *
	 * @return void
	 */
	private function add_additional_hooks() {
		// Setting AJAX-request to toggle the plugin.
		\add_action( 'wp_ajax_toggle_plugin', [ $this, 'ajax_toggle_plugin' ] );

		// Adding assets.
		\add_action( 'admin_init', [ $this, 'add_assets' ] );

		\add_action( 'admin_bar_menu', [ $this, 'add_toggle' ], 100 );
	}

	/**
	 * Activates a plugin of a specific group.
	 *
	 * @param string $group  Group to activate a plugin of.
	 * @param string $plugin Plugin to activate.
	 *
	 * @return void
	 */
	private function activate_plugin( $group, $plugin ) {
		if ( ! \array_key_exists( $group, $this->plugin_groups ) ) {
			return;
		}
		if ( ! \array_key_exists( $plugin, $this->plugin_groups[ $group ] ) ) {
			return;
		}

		$plugin_path = $this->plugin_groups[ $group ][ $plugin ];
		\activate_plugin( \plugin_basename( $plugin_path ), null, false, true );
	}

	/**
	 * Deactivates the plugins in a specific group.
	 *
	 * This will be performed in silent mode.
	 *
	 * @param string $group Group to deactivate the plugins of.
	 *
	 * @return void
	 */
	private function deactivate_plugin_group( $group ) {
		if ( ! \array_key_exists( $group, $this->plugin_groups ) ) {
			return;
		}

		$plugins = $this->plugin_groups[ $group ];
		foreach ( $plugins as $plugin_path ) {
			if ( \is_plugin_active( $plugin_path ) ) {
				\deactivate_plugins( \plugin_basename( $plugin_path ), true );
			}
		}
	}

	/**
	 * Verify the set nonce with the posted one
	 *
	 * @return bool True if verified.
	 */
	private function verify_nonce() {
		// Get the nonce value.
		$ajax_nonce = \filter_input( \INPUT_GET, 'ajax_nonce' );

		// If nonce is valid return true.
		if ( \wp_verify_nonce( $ajax_nonce, 'yoast-plugin-toggle' ) ) {
			return true;
		}
	}

	/**
	 * Initializes the plugin groups.
	 *
	 * @return void
	 */
	private function init_plugin_groups() {
		// Find the plugin groups.
		$plugin_groups = $this->get_plugin_groups();

		// Apply filters to extend the $this->plugin_groups property.
		$plugin_groups = (array) \apply_filters( 'Yoast\WP\Test_Helper\plugin_toggle_extend', $plugin_groups );

		// Check the plugins after the filter.
		$this->plugin_groups = $this->check_plugins( $plugin_groups );
	}
}
