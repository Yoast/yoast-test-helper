<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\SEO\Conditionals\Feature_Flag_Conditional;

/**
 * Toggles the features on and off.
 */
class Feature_Toggler implements Integration {

	/**
	 * The features to toggle.
	 *
	 * @var string[]
	 */
	private $feature_flags = [];

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
	 * Registers WordPress hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action(
			'admin_post_yoast_seo_feature_toggler',
			[ $this, 'handle_submit' ]
		);

		\add_action( 'plugins_loaded', [ $this, 'get_feature_flags' ] );
		\add_filter( 'wpseo_enable_feature_flags', [ $this, 'enable_features' ] );
	}

	/**
	 * Gets all feature flags names of the Feature_Flag_Conditinonal class subclasses.
	 *
	 * @return void
	 */
	public function get_feature_flags() {
		foreach ( \get_declared_classes() as $class ) {
			if ( is_subclass_of( $class, Feature_Flag_Conditional::class ) ) {
				$feature_name                         = $class::get_feature_flag();
				$feature_flag                         = \strtoupper( 'YOAST_SEO_' . $feature_name );
				$this->feature_flags[ $feature_flag ] = $feature_name;
			}
		}
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		if ( $this->feature_flags === [] ) {
			return '';
		}

		$fields = '';

		foreach ( $this->feature_flags as $feature => $label ) {
			$key     = 'feature_toggle_' . $feature;
			$fields .= Form_Presenter::create_checkbox(
				$key,
				/* translators: %s expands to the label. */
				\sprintf( \esc_html__( 'Enable %s', 'yoast-test-helper' ), $label ),
				$this->option->get( $key )
			);
		}

		return Form_Presenter::get_html( \__( 'Feature toggler', 'yoast-test-helper' ), 'yoast_seo_feature_toggler', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_feature_toggler' ) !== false ) {
			foreach ( $this->feature_flags as $feature => $label ) {
				$key = 'feature_toggle_' . $feature;
				$this->option->set( $key, isset( $_POST[ $key ] ) );
			}
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Enable a feature in the plugin.
	 *
	 * @param string[] $enabled_feature_flags The array of enabled feature flags.
	 *
	 * @return string[] The modified array of enabled feature flags.
	 */
	public function enable_features( $enabled_feature_flags ) {
		foreach ( $this->feature_flags as $feature => $label ) {
			if ( $this->option->get( 'feature_toggle_' . $feature ) ) {
				$enabled_feature_flags[] = $label;
			}
		}

		return $enabled_feature_flags;
	}
}
