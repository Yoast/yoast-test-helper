<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\SEO\Conditionals\Feature_Flag_Conditional;
use ReflectionClass;
use ReflectionMethod;

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
	 * Loops all declared classes and checks for Subclasses of the Feature_Flag_Conditinonal class.
	 *
	 * @return array An array of the registered feature flag conditionals.
	 */
	public function get_feature_flags() {
		foreach ( \get_declared_classes() as $class ) {
			if ( is_subclass_of( $class, Feature_Flag_Conditional::class ) ) {
				$feature_name                         = $class::get_feature_flag();
				$feature_flag                         = \strtoupper( 'YOAST_SEO_' . $feature_name );
				$this->feature_flags[ $feature_flag ] = $feature_name;
			}
		}
		return $this->feature_flags;
	}

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
		\add_action( 'wpseo_enable_feature', [ $this, 'enable_features' ] );

		\add_action(
			'admin_post_yoast_seo_feature_toggler',
			[ $this, 'handle_submit' ]
		);
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$this->$features = $this->get_feature_flags();
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
			foreach ( $this->features as $feature => $label ) {
				$key = 'feature_toggle_' . $feature;
				$this->option->set( $key, isset( $_POST[ $key ] ) );
			}
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Enable a feature in the plugin.
	 *
	 * @param string[] $feature_array The array of enabled features.
	 *
	 * @return string[] The modified array of enabled features.
	 */
	public function enable_features( $feature_array ) {
		foreach ( $this->features as $feature => $label ) {
			if ( $this->option->get( 'feature_toggle_' . $feature ) ) {
				$feature_array[] = $feature;
			}
		}

		return $feature_array;
	}
}
