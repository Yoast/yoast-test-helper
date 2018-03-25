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
	 * Holds our options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->options = Option::get_option();
	}

	/**
	 * The plugins to compare.
	 *
	 * @var array[]
	 */
	private $plugins = array(
		'Yoast SEO' => array(
			'Free'    => 'wordpress-seo/wp-seo.php',
			'Premium' => 'wordpress-seo-premium/wp-seo-premium.php',
		),
	);

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

		if ( ! $this->options['plugin_toggler'] ) {
			return;
		}

		// Load core plugin.php if not exists.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Apply filters to extends the $this->plugins property.
		$this->plugins = (array) apply_filters( 'yoast_plugin_toggler_extend', $this->plugins );

		// First check if both versions of plugin do exist.
		$this->plugins = $this->get_installed_plugins( $this->plugins );

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

		foreach ( $this->get_active_plugins() as $label => $version ) {
			$menu_id = 'wpseo-plugin-toggler-' . sanitize_title( $label );
			$wp_admin_bar->add_menu(
				array(
					'id'    => $menu_id,
					'title' => $label . ': ' . $version,
					'href'  => '#',
				)
			);

			foreach ( $this->plugins[ $label ] as $switch_version => $data ) {
				if ( $switch_version !== $version ) {
					$wp_admin_bar->add_menu(
						array(
							'parent' => $menu_id,
							'id'     => 'wpseo-plugin-toggle-' . sanitize_title( $label ),
							'title'  => 'Switch to ' . $switch_version,
							'href'   => '#',
							'meta'   => array( 'onclick' => 'Yoast_Plugin_Toggler.toggle_plugin( "' . $label . '", "' . $nonce . '")' ),
						)
					);
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
	 * Toggle between the versions.
	 *
	 * The active version will be deactivated. The inactive version will be printed as JSON and will be used to active
	 * this version in another AJAX request.
	 *
	 * @return void
	 */
	public function ajax_toggle_plugin_version() {

		$response = array();

		// If nonce is valid.
		if ( $this->verify_nonce() ) {
			$current_plugin        = filter_input( INPUT_GET, 'plugin' );
			$version_to_activate   = $this->get_inactive_version( $current_plugin );
			$version_to_deactivate = $this->get_active_version( $current_plugin );

			// First deactivate current version.
			$this->deactivate_plugin_version( $current_plugin, $version_to_deactivate );
			$this->activate_plugin_version( $current_plugin, $version_to_activate );

			$response = array(
				'activated_version' => $version_to_activate,
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
		$output  = '<h2>Plugin toggler</h2>';
		$output .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= wp_nonce_field( 'plugin_toggler', '_wpnonce', true, false );
		$output .= '<input type="hidden" name="action" value="yoast_seo_plugin_toggler">';

		$output .= '<input type="checkbox" ' . checked( $this->options['plugin_toggler'], true, false ) . ' name="plugin_toggler" id="plugin_toggler"/> <label for="plugin_toggler">Show plugin toggler.</label>';
		$output .= '<br/><br/>';
		$output .= '<button class="button button-primary">Save</button>';
		$output .= '</form>';

		return $output;
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( check_admin_referer( 'plugin_toggler' ) !== false ) {
			$this->options['plugin_toggler'] = false;
			if ( isset( $_POST['plugin_toggler'] ) ) {
				$this->options['plugin_toggler'] = true;
			}
			Option::set_option( $this->options );
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
	 * Retrieves a list of installed plugins.
	 *
	 * @param array $plugins Plugins to filter for installed plugins.
	 *
	 * @return array Plugins that are actually installed.
	 */
	private function get_installed_plugins( array $plugins ) {
		$installed = array();

		foreach ( $plugins as $plugin => $versions ) {
			foreach ( $versions as $version => $plugin_path ) {
				$full_plugin_path = ABSPATH . 'wp-content/plugins/' . plugin_basename( $plugin_path );

				if ( file_exists( $full_plugin_path ) ) {
					$installed[ $plugin ][ $version ] = $plugin_path;
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

		foreach ( $this->plugins as $plugin => $versions ) {
			foreach ( $versions as $version => $plugin_path ) {
				if ( is_plugin_active( $plugin_path ) ) {
					$active[ $plugin ] = $version;
				}
			}
		}

		return $active;
	}

	/**
	 * Adding the hooks.
	 *
	 * @return void
	 */
	private function add_additional_hooks() {
		// Setting AJAX-request for toggle between version.
		add_action( 'wp_ajax_toggle_version', array( $this, 'ajax_toggle_plugin_version' ) );

		// Adding assets.
		add_action( 'admin_init', array( $this, 'add_assets' ) );

		add_action( 'admin_bar_menu', array( $this, 'add_toggle' ), 100 );
	}

	/**
	 * Activates a version of a specific plugin.
	 *
	 * @param string $plugin  Plugin to activate a version of.
	 * @param string $version Version to activate.
	 *
	 * @return void
	 */
	private function activate_plugin_version( $plugin, $version ) {
		$plugin_to_enable = $this->plugins[ $plugin ][ $version ];

		// Activate plugin.
		activate_plugin( plugin_basename( $plugin_to_enable ), null, false, true );
	}

	/**
	 * Deactivates a version for a specific plugin
	 *
	 * This will be performed in silent mode.
	 *
	 * @param string $plugin  Plugin to deactivate a version of.
	 * @param string $version Version to deactivate.
	 *
	 * @return void
	 */
	private function deactivate_plugin_version( $plugin, $version ) {
		$plugin_to_disable = $this->plugins[ $plugin ][ $version ];

		// Disable plugin.
		deactivate_plugins( plugin_basename( $plugin_to_disable ), true );
	}

	/**
	 * Retrieves the active version of the plugin.
	 *
	 * @param string $plugin The plugin to retrieve the version from.
	 *
	 * @return string The version that is active.
	 */
	private function get_active_version( $plugin ) {
		foreach ( $this->plugins[ $plugin ] as $version => $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return $version;
			}
		}
	}

	/**
	 * Getting the version of given $plugin which is inactive
	 *
	 * @param string $plugin The plugin to retrieve the version from.
	 *
	 * @return string The version that is inactive.
	 */
	private function get_inactive_version( $plugin ) {
		foreach ( $this->plugins[ $plugin ] as $version => $plugin_path ) {
			if ( $this->get_active_version( $plugin ) !== $version ) {
				return $version;
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
		$ajax_nonce = filter_input( INPUT_GET, 'ajax_nonce' );

		// If nonce is valid return true.
		if ( wp_verify_nonce( $ajax_nonce, 'yoast-plugin-toggle' ) ) {
			return true;
		}
	}
}
