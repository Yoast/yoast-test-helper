<?php
/**
 * Toggles between free and premium plugins.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Toggles between plugins.
 */
class Plugin_Toggler implements Integration {

	/**
	 * The plugins per group.
	 *
	 * @var array
	 */
	private $plugins = array();

	/**
	 * Regex with groups to filter the available plugins by name.
	 *
	 * @var string
	 */
	private $grouped_name_filter = '/^(Yoast SEO)$|^(Yoast SEO)[^:]{1}/';

	/**
	 * The active plugin of each group.
	 *
	 * @var array
	 */
	private $active_plugins = array();

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
		add_action( 'plugins_loaded', array( $this, 'init' ) );

		add_action(
			'admin_post_yoast_seo_plugin_toggler',
			array( $this, 'handle_submit' )
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
			! function_exists( 'is_plugin_active' ) ||
			! function_exists( 'get_plugins' )
		) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Apply filters to adapt the $this->grouped_name_filter property.
		$this->grouped_name_filter = apply_filters( 'yoast_plugin_toggler_filter', $this->grouped_name_filter );

		// Find the plugins.
		$this->plugins = $this->get_filtered_plugin_groups( $this->grouped_name_filter );

		// Apply filters to extend the $this->plugins property.
		$this->plugins = (array) apply_filters( 'yoast_plugin_toggler_extend', $this->plugins );

		// First check if the plugins are installed.
		$this->plugins = $this->get_installed_plugins( $this->plugins );

		// Get the currently active plugins.
		$this->active_plugins = $this->get_active_plugins();

		// Adding the hooks.
		$this->add_additional_hooks();
	}

	/**
	 * Adds the toggle fields to the page.
	 *
	 * @return void
	 */
	public function add_toggle() {
		$nonce = wp_create_nonce( 'yoast-plugin-toggle' );

		/** \WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		// Add a menu for each group.
		foreach ( $this->plugins as $group => $plugins ) {
			$active_plugin = $this->get_active_plugin( $group );
			$menu_id       = 'wpseo-plugin-toggler-' . sanitize_title( $group );
			$menu_title    = $active_plugin;

			// Menu title fallback: active plugin > group > first plugin.
			if ( $menu_title === '' ) {
				$menu_title = $group;
				if ( $menu_title === '' ) {
					reset( $plugins );
					$menu_title = key( $plugins );
				}
			}

			$wp_admin_bar->add_menu( array(
				'parent' => false,
				'id'     => $menu_id,
				'title'  => $menu_title,
				'href'   => '#',
			) );

			// Add a node for each plugin.
			foreach ( $plugins as $plugin => $plugin_path ) {
				if ( $plugin !== $active_plugin ) {
					$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => 'wpseo-plugin-toggle-' . sanitize_title( $plugin ),
						'title'  => 'Switch to ' . $plugin,
						'href'   => '#',
						'meta'   => array(
							'onclick' => sprintf(
								'Yoast_Plugin_Toggler.toggle_plugin( "%1$s", "%2$s", "%3$s" )',
								$group,
								$plugin,
								$nonce
							)
						)
					) );
				}
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
		wp_enqueue_script(
			'yoast-toggle-script',
			plugin_dir_url( YOAST_TEST_HELPER_FILE ) . 'assets/js/yoast-toggle.js'
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

		$response = array();

		// If nonce is valid.
		if ( $this->verify_nonce() ) {
			$group  = filter_input( INPUT_GET, 'group' );
			$plugin = filter_input( INPUT_GET, 'plugin' );

			// First deactivate the current plugin.
			$this->deactivate_plugins( $group );
			$this->activate_plugin( $group, $plugin );

			$response = array(
				'activated_plugin' => array(
					'group'  => $group,
					'plugin' => $plugin
				)
			);
		}

		echo wp_json_encode( $response );
		die();
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'plugin_toggler', 'Show plugin toggler.',
			$this->option->get( 'plugin_toggler' )
		);

		return Form_Presenter::get_html( 'Plugin toggler', 'yoast_seo_plugin_toggler', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( check_admin_referer( 'yoast_seo_plugin_toggler' ) !== false ) {
			$this->option->set( 'plugin_toggler', isset( $_POST['plugin_toggler'] ) );
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}

	/**
	 * Check if there are enough rights to display the toggle
	 *
	 * If current page is adminpage and current user can activatie plugins return true
	 *
	 * @return bool True if the rights are present.
	 */
	private function has_rights() {
		return ( is_admin() && current_user_can( 'activate_plugins' ) );
	}

	/**
	 * Check the plugins directory and retrieve plugins that match the filter.
	 *
	 * Example:
	 * $grouped_name_filter = '/^((Yoast SEO)|(Yoast SEO) Premium)[ \d.]*$/'
	 * $plugins = array(
	 * 	'Yoast SEO' => array(
	 * 		'Yoast SEO'             => 'wordpress-seo/wp-seo.php',
	 * 		'Yoast SEO 8.4'         => 'wordpress-seo 8.4/wp-seo.php',
	 * 		'Yoast SEO Premium'     => 'wordpress-seo-premium/wp-seo-premium.php',
	 * 		'Yoast SEO Premium 8.4' => 'wordpress-seo-premium 8.4/wp-seo-premium.php',
	 * 	),
	 * );
	 *
	 * @param string $grouped_name_filter Regex to filter on the plugin data name.
	 *
	 * @return array The plugins grouped by the regex matches.
	 */
	private function get_filtered_plugin_groups( $grouped_name_filter ) {
		// Use WordPress to get all the plugins with their data.
		$all_plugins = get_plugins();
		$plugins     = array();

		foreach ( $all_plugins as $file => $data ) {
			$matches = array();
			$name    = $data[ 'Name' ];

			// Save the plugin under a group.
			if ( preg_match( $grouped_name_filter, $name, $matches ) ) {
				$matches = array_reverse( $matches );
				$group   = '';

				foreach ( $matches as $match ) {
					if ( $match !== '' ) {
						$group = $match;
						break;
					}
				}

				if ( ! isset( $plugins[ $group ] ) ) {
					$plugins[ $group ] = array();
				}
				$plugins[ $group ][ $name ] = $file;
			}
		}

		return $plugins;
	}

	/**
	 * Retrieves a list of installed plugins, pruned by group.
	 *
	 * @param array $filter_plugins Plugins to filter for installed plugins.
	 * @param bool  [$prune=true]   Whether to prune the groups if they contain
	 *                              less than 2 plugins.
	 *
	 * @return array Plugins that are actually installed.
	 */
	private function get_installed_plugins( array $filter_plugins, $prune = true ) {
		$installed = array();

		foreach ( $filter_plugins AS $group => $plugins ) {
			foreach ( $plugins AS $plugin => $plugin_path ) {
				$full_plugin_path = ABSPATH . 'wp-content/plugins/' . plugin_basename( $plugin_path );

				// Add the plugin to the group if it exists.
				if ( file_exists( $full_plugin_path ) ) {
					$installed[ $group ][ $plugin ] = $plugin_path;
				}
			}

			if ( $prune ) {
				// Remove the group entirely if there is less than 2 plugins in it.
				if ( count( $installed[ $group ] ) < 2 ) {
					unset( $installed[ $group ] );
				}
			}
		}

		return $installed;
	}

	/**
	 * Retrieves a list of active plugins.
	 *
	 * @return array List of active plugins.
	 */
	private function get_active_plugins() {
		$active = array();

		foreach ( $this->plugins as $group => $plugins ) {
			foreach ( $plugins as $plugin => $plugin_path ) {
				if ( is_plugin_active( $plugin_path ) ) {
					$active[ $group ] = $plugin;
				}
			}
		}

		return $active;
	}

	/**
	 * Retrieves the active plugin of a group.
	 *
	 * @param string $group The group of to check.
	 *
	 * @return string The plugin name or an empty string.
	 */
	private function get_active_plugin( $group ) {
		if ( array_key_exists( $group, $this->active_plugins ) ) {
			return $this->active_plugins[ $group ];
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
		add_action( 'wp_ajax_toggle_plugin', array( $this, 'ajax_toggle_plugin' ) );

		// Adding assets.
		add_action( 'admin_init', array( $this, 'add_assets' ) );

		add_action( 'admin_bar_menu', array( $this, 'add_toggle' ), 100 );
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
		$plugin_path = $this->plugins[ $group ][ $plugin ];

		// Activate plugin.
		activate_plugin( plugin_basename( $plugin_path ), null, false, true );
		$this->active_plugins[ $group ] = $plugin;
	}

	/**
	 * Deactivates the plugins for a specific group.
	 *
	 * This will be performed in silent mode.
	 *
	 * @param string $group  Group to deactivate the plugins of.
	 *
	 * @return void
	 */
	private function deactivate_plugins( $group ) {
		$active_plugin = array_key_exists( $group, $this->active_plugins ) ? $this->active_plugins[ $group ] : '';

		deactivate_plugins( plugin_basename( $this->plugins[ $group ][ $active_plugin ] ), true );
		unset( $this->active_plugins[ $group ] );
	}

	/**
	 * Verify the set nonce with the posted one
	 *
	 * @return bool True if verified.
	 */
	private function verify_nonce() {
		// Get the nonce value.
		$ajax_nonce = filter_input( INPUT_GET, 'ajax_nonce' );

		// If nonce is valid return true.
		if ( wp_verify_nonce( $ajax_nonce, 'yoast-plugin-toggle' ) ) {
			return true;
		}
	}
}
