<?php

namespace Yoast\Test_Helper;

class Plugin_Toggler implements Integration {

	/** @var array[] The plugins to compare */
	private $plugins = array(
		'Yoast SEO' => array(
			'Free'    => 'wordpress-seo/wp-seo.php',
			'Premium' => 'wordpress-seo-premium/wp-seo-premium.php'
		)
	);

	/**
	 * Constructing the object and set init hook
	 */
	public function add_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize plugin
	 *
	 * Check for rights and look which plugin is active.
	 * Also adding hooks
	 *
	 */
	public function init() {
		if ( ! $this->has_rights() ) {
			return;
		}

		// Load core plugin.php if not exists
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Apply filters to extends the $this->plugins property
		$this->plugins = (array) apply_filters( 'yoast_plugin_toggler_extend', $this->plugins );

		// First check if both versions of plugin do exist
		$this->plugins = $this->get_installed_plugins( $this->plugins );

		// Adding the hooks
		$this->add_additional_hooks();
	}

	/**
	 * Adding the toggle fields to the page
	 */
	public function add_toggle() {
		$nonce = wp_create_nonce( 'yoast-plugin-toggle' );

		/** \WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		foreach ( $this->get_active_plugins() as $label => $version ) {
			$menu_id = 'wpseo-plugin-toggler-' . sanitize_title( $label );
			$wp_admin_bar->add_menu( array(
				'id'    => $menu_id,
				'title' => $label . ': ' . $version,
				'href'  => '#',
			) );

			foreach ( $this->plugins[ $label ] as $switch_version => $data ) {
				if ( $switch_version !== $version ) {
					$wp_admin_bar->add_menu( array(
						'parent' => $menu_id,
						'id'     => 'wpseo-plugin-toggle-' . sanitize_title( $label ),
						'title'  => 'Switch to ' . $switch_version,
						'href'   => '#',
						'meta'   => array( 'onclick' => 'Yoast_Plugin_Toggler.toggle_plugin( "' . $label . '", "' . $nonce . '")' )
					) );
				}
			}
		}
	}

	/**
	 * Adding the assets to the page
	 *
	 */
	public function add_assets() {
		// JS file
		wp_enqueue_script(
			'yoast-toggle-script',
			plugin_dir_url( YOAST_TEST_HELPER_FILE ) . 'assets/js/yoast-toggle.js'
		);
	}

	/**
	 * Toggle between the versions
	 *
	 * The active version will be deactivated. The inactive version will be printed as JSON and will be used to active
	 * this version in another AJAX request
	 *
	 */
	public function ajax_toggle_plugin_version() {

		$response = array();

		// If nonce is valid
		if ( $this->verify_nonce() ) {
			$current_plugin        = filter_input( INPUT_GET, 'plugin' );
			$version_to_activate   = $this->get_inactive_version( $current_plugin );
			$version_to_deactivate = $this->get_active_version( $current_plugin );

			// First deactivate current version
			$this->deactivate_plugin_version( $current_plugin, $version_to_deactivate );
			$this->activate_plugin_version( $current_plugin, $version_to_activate );

			$response = array(
				'activated_version' => $version_to_activate
			);
		}

		echo json_encode( $response );
		die();
	}

	/**
	 * Check if there are enough rights to display the toggle
	 *
	 * If current page is adminpage and current user can activatie plugins return true
	 *
	 * @return bool
	 */
	private function has_rights() {
		return ( is_admin() && current_user_can( 'activate_plugins' ) );
	}

	/**
	 * @param array $plugins
	 *
	 * @return array
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
	 * @return array
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
	 * Adding the hooks
	 *
	 */
	private function add_additional_hooks() {
		// Setting AJAX-request for toggle between version
		add_action( 'wp_ajax_toggle_version', array( $this, 'ajax_toggle_plugin_version' ) );

		// Adding assets
		add_action( 'admin_init', array( $this, 'add_assets' ) );

		add_action( 'admin_bar_menu', array( $this, 'add_toggle' ), 100 );
	}

	/**
	 * Activate the $version for given $plugin
	 *
	 * @param string $plugin
	 * @param string $version
	 */
	private function activate_plugin_version( $plugin, $version ) {
		$plugin_to_enable = $this->plugins[ $plugin ][ $version ];

		// Activate plugin
		activate_plugin( plugin_basename( $plugin_to_enable ), null, false, true );
	}

	/**
	 * Deactivate the $version for given $plugin
	 *
	 * This will be performed in silent mode
	 *
	 * @param string $plugin
	 * @param string $version [free or premium]
	 */
	private function deactivate_plugin_version( $plugin, $version ) {
		$plugin_to_disable = $this->plugins[ $plugin ][ $version ];

		// Disable plugin
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
	 * @return bool
	 */
	private function verify_nonce() {
		// Get the nonce value
		$ajax_nonce = filter_input( INPUT_GET, 'ajax_nonce' );

		// If nonce is valid return true
		if ( wp_verify_nonce( $ajax_nonce, 'yoast-plugin-toggle' ) ) {
			return true;
		}
	}
}
