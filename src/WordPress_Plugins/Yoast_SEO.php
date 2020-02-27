<?php
/**
 * Yoast SEO plugin
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper\WordPress_Plugins;

use WPSEO_Options;

/**
 * Class to represent Yoast SEO.
 */
class Yoast_SEO implements WordPress_Plugin {
	/**
	 * Retrieves the plugin identifier.
	 *
	 * @return string The plugin identifier.
	 */
	public function get_identifier() {
		return 'wordpress-seo';
	}

	/**
	 * Retrieves the plugin name.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_name() {
		return 'Yoast SEO';
	}

	/**
	 * Retrieves the version option name.
	 *
	 * @return string The name that holds the version.
	 */
	public function get_version_option_name() {
		return 'wpseo';
	}

	/**
	 * Retrieves the version key.
	 *
	 * @return string The version key.
	 */
	public function get_version_key() {
		return 'version';
	}

	/**
	 * Retrieves the options.
	 *
	 * @return array The options.
	 */
	public function get_options() {
		return array(
			'wpseo',
			'wpseo_xml',
			'wpseo_rss',
			'wpseo_ms',
			'wpseo_internallinks',
			'wpseo_permalinks',
			'wpseo_titles',
		);
	}

	/**
	 * Retrieves the list of features.
	 *
	 * @return array List of features.
	 */
	public function get_features() {
		return array(
			'internal_link_count'         => 'Internal link counter',
			'prominent_words_calculation' => 'Prominent words calculation',
			'reset_configuration_wizard'  => 'Configuration wizard',
			'reset_notifications'         => 'Notifications',
			'reset_site_information'      => 'Site information',
			'reset_tracking'              => 'Tracking',
		);
	}

	/**
	 * Resets a feature.
	 *
	 * @param string $feature Feature to reset.
	 *
	 * @return bool True on succes.
	 */
	public function reset_feature( $feature ) {
		switch ( $feature ) {
			case 'internal_link_count':
				$this->reset_internal_link_count();
				return true;
			case 'prominent_words_calculation':
				$this->reset_prominent_words_calculation();
				return true;
			case 'reset_configuration_wizard':
				return $this->reset_configuration_wizard();
			case 'reset_notifications':
				$this->reset_notifications();
				return true;
			case 'reset_site_information':
				return $this->reset_site_information();
			case 'reset_tracking':
				return $this->reset_tracking();
		}

		return false;
	}

	/**
	 * Retrieves the active version of the plugin.
	 *
	 * @return string The current version of the plugin.
	 */
	public function get_version_constant() {
		return defined( 'WPSEO_VERSION' ) ? WPSEO_VERSION : 'not active';
	}

	/**
	 * Resets the internal link count.
	 *
	 * @return void
	 */
	private function reset_internal_link_count() {
		global $wpdb;

		$wpdb->query( 'UPDATE ' . $wpdb->prefix . 'yoast_seo_meta SET internal_link_count = NULL' );
	}

	/**
	 * Resets the prominent words calculation.
	 *
	 * @return void
	 */
	private function reset_prominent_words_calculation() {
		global $wpdb;

		$wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => '_yst_prominent_words_version' ) );
	}

	/**
	 * Resets all notifications.
	 *
	 * @return void
	 */
	private function reset_notifications() {
		global $wpdb;

		// Remove all notifications from the saved stack.
		$wpdb->delete( $wpdb->prefix . 'usermeta',
			array(
				'meta_key' => 'wp_yoast_notifications',
				'user_id'  => get_current_user_id(),
			)
		);

		// Delete all muted notification settings.
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key LIKE %s AND meta_value="seen"', 'wpseo-%' ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key LIKE %s AND meta_value="seen"', 'wp_wpseo-%' ) );

		// Restore dismissed notifications.
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key = "wp_yoast_promo_hide_premium_upsell_admin_block"' );
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key = "wpseo-remove-upsell-notice"' );
	}

	/**
	 * Resets the site information.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function reset_site_information() {
		return delete_transient( 'wpseo_site_information' );
	}

	/**
	 * Resets the tracking to fire again.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function reset_tracking() {
		return delete_option( 'wpseo_tracking_last_request' );
	}

	/**
	 * Resets the configuration wizard to its initial state.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function reset_configuration_wizard() {
		update_user_meta( get_current_user_id(), 'wpseo-dismiss-configuration-notice', 'no' );
		return WPSEO_Options::set( 'show_onboarding_notice', true );
	}
}
