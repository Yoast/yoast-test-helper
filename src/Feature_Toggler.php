<?php
/**
 * Toggles features on and off based on feature flags.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Toggles the features on and off.
 */
class Feature_Toggler implements Integration {

	/**
	 * The features to toggle.
	 *
	 * @var array
	 */
	private $features = array(
		'improvedInternalLinking' => 'Improved internal linking',
	);

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
		add_action( 'wpseo_enable_feature', array( $this, 'enable_features' ) );

		add_action(
			'admin_post_yoast_seo_feature_toggler',
			array( $this, 'handle_submit' )
		);
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = '';

		foreach ( $this->features as $feature => $label ) {
			$key     = 'feature_toggle_' . $feature;
			$fields .= Form_Presenter::create_checkbox(
				$key, 'Enable ' . $label,
				$this->option->get( $key )
			);
		}

		return Form_Presenter::get_html( 'Feature toggler', 'yoast_seo_feature_toggler', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( check_admin_referer( 'yoast_seo_feature_toggler' ) !== false ) {
			foreach ( $this->features as $feature => $label ) {
				$key = 'feature_toggle_' . $feature;
				$this->option->set( $key, isset( $_POST[ $key ] ) );
			}
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}

	/**
	 * Enable a feature in the plugin.
	 *
	 * @param array $feature_array The array of enabled features.
	 *
	 * @return array The modified array of enabled features.
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
