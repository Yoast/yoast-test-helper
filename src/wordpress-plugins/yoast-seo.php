<?php

namespace Yoast\WP\Test_Helper\WordPress_Plugins;

use WPSEO_Capability_Manager_Factory;
use WPSEO_Options;
use WPSEO_Role_Manager_Factory;

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
		return [
			'wpseo',
			'wpseo_xml',
			'wpseo_rss',
			'wpseo_ms',
			'wpseo_internallinks',
			'wpseo_permalinks',
			'wpseo_titles',
		];
	}

	/**
	 * Retrieves the list of features.
	 *
	 * @return string[] List of features.
	 */
	public function get_features() {
		return [
			'internal_link_count'                => \esc_html__( 'Internal link counter', 'yoast-test-helper' ),
			'prominent_words_calculation'        => \esc_html__( 'Prominent words calculation', 'yoast-test-helper' ),
			'reset_configuration_wizard'         => \esc_html__( 'Configuration wizard', 'yoast-test-helper' ),
			'reset_notifications'                => \esc_html__( 'Notifications', 'yoast-test-helper' ),
			'reset_site_information'             => \esc_html__( 'Site information', 'yoast-test-helper' ),
			'reset_tracking'                     => \esc_html__( 'Tracking', 'yoast-test-helper' ),
			'reset_indexables'                   => \esc_html__( 'Indexables tables & migrations', 'yoast-test-helper' ),
			'reset_capabilities'                 => \esc_html__( 'SEO roles & capabilities', 'yoast-test-helper' ),
			'reset_free_installation_success'    => \esc_html__( 'Free installation success page', 'yoast-test-helper' ),
			'reset_premium_installation_success' => \esc_html__( 'Premium installation success page', 'yoast-test-helper' ),
			'reset_first_time_configuration'     => \esc_html__( 'First time configuration', 'yoast-test-helper' ),
			'reset_premium_workouts'             => \esc_html__( 'Premium workouts', 'yoast-test-helper' ),
			'reset_options'                      => \esc_html__( 'Options', 'yoast-test-helper' ),
			'reset_cornerstone_flags'            => \esc_html__( 'Cornerstone flags', 'yoast-test-helper' ),
		];
	}

	/**
	 * Resets a feature.
	 *
	 * @param string $feature Feature to reset.
	 *
	 * @return bool True on success.
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
			case 'reset_indexables':
				return $this->reset_indexables();
			case 'reset_notifications':
				$this->reset_notifications();
				return true;
			case 'reset_site_information':
				return $this->reset_site_information();
			case 'reset_tracking':
				return $this->reset_tracking();
			case 'reset_capabilities':
				$this->reset_capabilities();
				return true;
			case 'reset_free_installation_success':
				$this->reset_free_installation_success_page();
				return true;
			case 'reset_premium_installation_success':
				$this->reset_premium_installation_success_page();
				return true;
			case 'reset_first_time_configuration':
				$this->reset_first_time_configuration();
				return true;
			case 'reset_premium_workouts':
				$this->reset_premium_workouts();
				return true;
			case 'reset_options':
				$this->reset_options();
				return true;
			case 'reset_cornerstone_flags':
				$this->reset_cornerstone_flags();
				return true;
		}

		return false;
	}

	/**
	 * Retrieves the active version of the plugin.
	 *
	 * @return string The current version of the plugin.
	 */
	public function get_version_constant() {
		return \defined( 'WPSEO_VERSION' ) ? \WPSEO_VERSION : \__( 'not active', 'yoast-test-helper' );
	}

	/**
	 * Resets the internal link count.
	 *
	 * @return void
	 */
	private function reset_internal_link_count() {
		global $wpdb;

		$wpdb->query( 'UPDATE ' . $wpdb->prefix . 'yoast_indexable SET link_count = NULL, incoming_link_count = NULL' );

		\delete_transient( 'wpseo_unindexed_post_link_count' );
		\delete_transient( 'wpseo_unindexed_term_link_count' );

		$this->reset_indexing_notification( 'indexables-reset-by-test-helper' );
	}

	/**
	 * Resets the prominent words calculation.
	 *
	 * @return void
	 */
	private function reset_prominent_words_calculation() {
		global $wpdb;

		$wpdb->delete( $wpdb->prefix . 'postmeta', [ 'meta_key' => '_yst_prominent_words_version' ] );

		$wpdb->query( 'UPDATE ' . $wpdb->prefix . 'yoast_indexable SET prominent_words_version = NULL' );
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'yoast_prominent_words' );
		WPSEO_Options::set( 'prominent_words_indexing_completed', false );
		\delete_transient( 'total_unindexed_prominent_words' );

		$this->reset_indexing_notification( 'indexables-reset-by-test-helper' );
	}

	/**
	 * Resets all notifications.
	 *
	 * @return void
	 */
	private function reset_notifications() {
		global $wpdb;

		// Remove all notifications from the saved stack.
		$wpdb->delete(
			$wpdb->prefix . 'usermeta',
			[
				'meta_key' => 'wp_yoast_notifications',
				'user_id'  => \get_current_user_id(),
			]
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
		return \delete_transient( 'wpseo_site_information' );
	}

	/**
	 * Resets the tracking to fire again.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function reset_tracking() {
		return \delete_option( 'wpseo_tracking_last_request' );
	}

	/**
	 * Resets the configuration wizard to its initial state.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function reset_configuration_wizard() {

		return WPSEO_Options::set( 'show_onboarding_notice', true );
	}

	/**
	 * Reset all indexables related tables, options and transients, forcing Yoast SEO to rebuild the tables from scratch and reindex all indexables.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	private function reset_indexables() {
		global $wpdb;

		// Reset the prominent words calculation.
		$this->reset_prominent_words_calculation();

		// Reset the internal link count.
		$this->reset_internal_link_count();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- We know what we're doing. Really.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'yoast_indexable' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'yoast_indexable_hierarchy' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'yoast_migrations' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'yoast_primary_term' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'yoast_seo_links' );

		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange

		WPSEO_Options::set( 'indexing_started', null );
		WPSEO_Options::set( 'indexables_indexing_completed', false );
		WPSEO_Options::set( 'indexing_first_time', true );

		$this->reset_indexing_notification( 'indexables-reset-by-test-helper' );

		// Delete the transients that hold the (limited) total unindexed counts.
		\delete_transient( 'wpseo_total_unindexed_posts' );
		\delete_transient( 'wpseo_total_unindexed_posts_limited' );
		\delete_transient( 'wpseo_total_unindexed_post_type_archives' );
		\delete_transient( 'wpseo_total_unindexed_terms' );
		\delete_transient( 'wpseo_total_unindexed_terms_limited' );
		\delete_transient( 'wpseo_total_unindexed_general_items' );
		\delete_transient( 'wpseo_unindexed_post_link_count' );
		\delete_transient( 'wpseo_unindexed_post_link_count_limited' );
		\delete_transient( 'wpseo_unindexed_term_link_count' );
		\delete_transient( 'wpseo_unindexed_term_link_count_limited' );
		\delete_transient( 'total_unindexed_prominent_words' );

		\delete_option( 'yoast_migrations_premium' );
		return \delete_option( 'yoast_migrations_free' );
	}

	/**
	 * Resets the indexing notification such that it is shown again.
	 *
	 * @param string $reason The indexing reason why the site needs to be reindexed.
	 */
	protected function reset_indexing_notification( $reason ) {
		\YoastSEO()->helpers->indexing->set_reason( $reason );
	}

	/**
	 * Resets the SEO capabilities & roles.
	 *
	 * @return void
	 */
	protected function reset_capabilities() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- We intentionally call this action.
		\do_action( 'wpseo_register_roles' );
		$role_manager = WPSEO_Role_Manager_Factory::get();
		$role_manager->remove();
		$role_manager->add();

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- We intentionally call this action.
		\do_action( 'wpseo_register_capabilities' );
		$capability_manager = WPSEO_Capability_Manager_Factory::get();
		$capability_manager->remove();
		$capability_manager->add();

		if ( \defined( 'WPSEO_PREMIUM_VERSION' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- We intentionally call this action.
			\do_action( 'wpseo_register_capabilities_premium' );
			$premium_capability_manager = WPSEO_Capability_Manager_Factory::get( 'premium' );
			$premium_capability_manager->remove();
			$premium_capability_manager->add();
		}
	}

	/**
	 * Resets the Free installation success page timestamp such that on reactivation the user is redirected again.
	 *
	 * @return void
	 */
	protected function reset_free_installation_success_page() {
		WPSEO_Options::set( 'activation_redirect_timestamp_free', '0' );
	}

	/**
	 * Resets the Premium installation success page timestamp such that on reactivation the user is redirected again.
	 *
	 * @return void
	 */
	protected function reset_premium_installation_success_page() {
		WPSEO_Options::set( 'activation_redirect_timestamp', '0' );
	}

	/**
	 * Resets the First-time configuration.
	 *
	 * @return void
	 */
	protected function reset_first_time_configuration() {
		$workouts_data                  = WPSEO_Options::get( 'workouts_data' );
		$workouts_data['configuration'] = [ 'finishedSteps' => [] ];
		WPSEO_Options::set( 'workouts_data', $workouts_data );
	}

	/**
	 * Resets the Premium workouts.
	 *
	 * @return void
	 */
	protected function reset_premium_workouts() {
		WPSEO_Options::set( 'workouts', [ 'cornerstone' => [ 'finishedSteps' => [] ] ] );
	}

	/**
	 * Resets the option to the defaults as if the plugin were installed the first time.
	 *
	 * @return void
	 */
	protected function reset_options() {
		WPSEO_Options::reset();
	}

	/**
	 * Resets the cornerstone flags set for posts.
	 *
	 * @return void
	 */
	protected function reset_cornerstone_flags() {
		global $wpdb;
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = "_yoast_wpseo_is_cornerstone"' );
	}
}
