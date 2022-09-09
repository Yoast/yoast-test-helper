<?php

namespace Yoast\WP\Test_Helper;

use Yoast\WP\SEO\Helpers\Indexables_Page_Helper;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Indexables_Page implements Integration {

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
	 * Adds the required hooks for this class.
	 */
	public function add_hooks() {
		\add_filter( 'wpseo_posts_threshold', [ $this, 'indexables_posts_threshold' ], 10, 1 );
		\add_filter( 'wpseo_analyzed_posts_threshold', [ $this, 'indexables_analyzed_posts_threshold' ], 10, 1 );

		\add_action( 'admin_post_yoast_seo_test_indexables_page', [ $this, 'handle_submit' ] );
	}

	/**
	 * Filter minimum post threshold.
	 *
	 * @param int $number The current post threshold.
	 *
	 * @return int The current post threshold.
	 */
	public function indexables_posts_threshold( $number ) {
		if ( $this->option->get( 'indexables_posts_threshold' ) > 0 ) {
			return $this->option->get( 'indexables_posts_threshold' );
		}

		return $number;
	}

	/**
	 * Filter minimum analyzed post threshold.
	 *
	 * @param int $number The current analyzed post threshold.
	 *
	 * @return int The current analyzed post threshold.
	 */
	public function indexables_analyzed_posts_threshold( $number ) {
		if ( $this->option->get( 'indexables_analyzed_posts_threshold' ) > 0 ) {
			return $this->option->get( 'indexables_analyzed_posts_threshold' );
		}

		return $number;
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		if ( ! \defined( 'YOAST_SEO_INDEXABLES_PAGE' ) || YOAST_SEO_INDEXABLES_PAGE !== true ) {
			return '';
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using WPSEO hook.
		$placeholder_thresholds = \apply_filters( 'wpseo_posts_threshold', Indexables_Page_Helper::POSTS_THRESHOLD );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using WPSEO hook.
		$placeholder_analyzed_thresholds  = \apply_filters( 'wpseo_analyzed_posts_threshold', Indexables_Page_Helper::ANALYSED_POSTS_THRESHOLD );
		$placeholder_analyzed_thresholds *= 100;
		$value                            = '';
		if ( $this->option->get( 'indexables_posts_threshold' ) > 0 ) {
			$value = $this->option->get( 'indexables_posts_threshold' );
		}

		$output  = '<label for="indexables_posts_threshold">' . \esc_html__( 'Minimum threshold for the amount of posts needed:', 'yoast-test-helper' ) . '</label>';
		$output .= '<input type="number" size="5" value="' . $value . '" placeholder="' . $placeholder_thresholds . '" name="indexables_posts_threshold" id="indexables_posts_threshold"/><br/>';

		$value = '';
		if ( $this->option->get( 'indexables_analyzed_posts_threshold' ) > 0 ) {
			$value = ( $this->option->get( 'indexables_analyzed_posts_threshold' ) * 100 );
		}

		$output .= '<label for="indexables_analyzed_posts_threshold">' . \esc_html__( 'The minimum threshold for the amount of analyzed posts:', 'yoast-test-helper' ) . '</label>';
		$output .= '<input type="number" size="5" min=1 max=100 value="' . $value . '" placeholder="' . $placeholder_analyzed_thresholds . '" name="indexables_analyzed_posts_threshold" id="indexables_analyzed_posts_threshold"/><span>%</span><br/>';


		return Form_Presenter::get_html( \__( 'Indexables overview thresholds', 'yoast-test-helper' ), 'yoast_seo_test_indexables_page', $output );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_indexables_page' ) !== false ) {
			$indexables_posts_threshold = null;
			if ( isset( $_POST['indexables_posts_threshold'] ) ) {
				$indexables_posts_threshold = \sanitize_text_field( \wp_unslash( $_POST['indexables_posts_threshold'] ) );
			}
			$this->option->set( 'indexables_posts_threshold', $indexables_posts_threshold );

			$indexables_analyzed_posts_threshold = null;

			if ( isset( $_POST['indexables_analyzed_posts_threshold'] ) && $_POST['indexables_analyzed_posts_threshold'] > 0 && $_POST['indexables_analyzed_posts_threshold'] <= 100 ) {
				$indexables_analyzed_posts_threshold = \sanitize_text_field( \wp_unslash( $_POST['indexables_analyzed_posts_threshold'] ) );
			}
			$this->option->set( 'indexables_analyzed_posts_threshold', ( $indexables_analyzed_posts_threshold / 100 ) );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}
}
