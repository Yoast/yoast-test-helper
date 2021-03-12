<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\SEO\Conditionals\Feature_Flag_Conditional;

/**
 * Toggles the features on and off.
 */
class Feature_Toggler implements Integration {

	/**
	 * The JavaScript features to toggle.
	 *
	 * @var string[]
	 */
	private $features = [];

	/**
	 * The feature flags to toggle.
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

		\add_filter( 'wpseo_enable_feature', [ $this, 'enable_js_features' ] );

		\add_action( 'init', [ $this, 'get_feature_flags' ], 9 );
		\add_filter( 'wpseo_enable_feature_flags', [ $this, 'enable_feature_flags' ] );
	}

	/**
	 * Gets all feature flags of the Feature_Flag_Conditinonal class subclasses.
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
		if ( $this->features === [] && $this->feature_flags === [] ) {
			return '';
		}

		$all_features = array_merge( $this->features, $this->feature_flags );

		$fields = '';

		foreach ( $all_features as $feature => $label ) {
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
		$all_features = array_merge( $this->features, $this->feature_flags );

		if ( \check_admin_referer( 'yoast_seo_feature_toggler' ) !== false ) {
			foreach ( $all_features as $feature => $label ) {
				$key = 'feature_toggle_' . $feature;
				$this->option->set( $key, isset( $_POST[ $key ] ) );
			}
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Enables JavaScript features in the plugin.
	 *
	 * @param string[] $feature_array The array of enabled JavaScript features.
	 *
	 * @return string[] The modified array of enabled JavaScript features.
	 */
	public function enable_js_features( $feature_array ) {
		foreach ( $this->features as $feature => $label ) {
			if ( $this->option->get( 'feature_toggle_' . $feature ) ) {
				$feature_array[] = $feature;
			}
		}

		return $feature_array;
	}

	/**
	 * Enables feature flags in the plugin.
	 *
	 * @param string[] $enabled_feature_flags The array of enabled feature flags.
	 *
	 * @return string[] The modified array of enabled feature flags.
	 */
	public function enable_feature_flags( $enabled_feature_flags ) {
		foreach ( $this->feature_flags as $feature => $name ) {
			if ( $this->option->get( 'feature_toggle_' . $feature ) ) {
				$enabled_feature_flags[] = $name;
			}
		}

		return $enabled_feature_flags;
	}
}
