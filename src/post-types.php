<?php

namespace Yoast\WP\Test_Helper;

/**
 * Bootstrap for the entire plugin.
 */
class Post_Types implements Integration {

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
	 * Register the needed hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( $this->option->get( 'enable_post_types' ) === true ) {
			\add_action( 'init', [ $this, 'register_post_types' ] );
		}

		\add_action( 'admin_post_yoast_seo_test_post_types', [ $this, 'handle_submit' ] );
		\add_filter( 'gutenberg_can_edit_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
		\add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
	}

	/**
	 * Checks whether Gutenberg is enabled for a certain post type.
	 *
	 * @param bool   $can_edit  Whether or not Gutenberg can edit the post type.
	 * @param string $post_type The post type slug.
	 *
	 * @return bool Whether or not Gutenberg is enabled.
	 */
	public function disable_gutenberg( $can_edit, $post_type ) {
		if ( $post_type === 'movie' && $this->option->get( 'enable_gutenberg_videos' ) === false ) {
			return false;
		}
		if ( $post_type === 'book' && $this->option->get( 'enable_gutenberg_books' ) === false ) {
			return false;
		}

		return $can_edit;
	}

	/**
	 * Registers our post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		\register_post_type( 'book', $this->get_book_args() );
		\register_post_type( 'movie', $this->get_movie_args() );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'enable_post_types',
			\esc_html__( 'Enable post types & taxonomies.', 'yoast-test-helper' ),
			$this->option->get( 'enable_post_types' )
		);

		$fields .= Form_Presenter::create_checkbox(
			'enable_gutenberg_books',
			\esc_html__( 'Enable block editor for Books.', 'yoast-test-helper' ),
			$this->option->get( 'enable_gutenberg_books' )
		);

		$fields .= Form_Presenter::create_checkbox(
			'enable_gutenberg_videos',
			\esc_html__( 'Enable block editor for Videos.', 'yoast-test-helper' ),
			$this->option->get( 'enable_gutenberg_videos' )
		);

		return Form_Presenter::get_html( \__( 'Post types & Taxonomies', 'yoast-test-helper' ), 'yoast_seo_test_post_types', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_post_types' ) !== false ) {
			$this->set_bool_option( 'enable_post_types' );
			$this->set_bool_option( 'enable_gutenberg_books' );
			$this->set_bool_option( 'enable_gutenberg_videos' );
		}

		// If we've now enabled the post types, make sure they work.
		if ( $this->option->get( 'enable_post_types' ) && ! \post_type_exists( 'book' ) ) {
			$this->register_post_types();

			// Hook this to shutdown so we're certain all the required post types have been registered.
			\add_action( 'shutdown', [ $this, 'flush_rewrite_rules' ] );
		}

		\wp_safe_redirect(
			\self_admin_url(
				'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' )
			)
		);
	}

	/**
	 * Flushes the rewrite rules on the required action.
	 */
	public function flush_rewrite_rules() {
		\flush_rewrite_rules();
	}

	/**
	 * Sets a boolean option based on a POST parameter.
	 *
	 * @param string $option The option to check and set.
	 */
	private function set_bool_option( $option ) {
		// The nonce is checked in the handle_submit function.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$this->option->set( $option, isset( $_POST[ $option ] ) );
	}

	/**
	 * Return arguments to use when registering the book post type.
	 *
	 * @return array Arguments to use when registering the book post type.
	 */
	private function get_book_args() {
		return [
			'label'        => \__( 'Books', 'yoast-test-helper' ),
			'labels'       => [
				'name'          => \__( 'Books', 'yoast-test-helper' ),
				'singular_name' => \__( 'Book', 'yoast-test-helper' ),
				'add_new'       => \__( 'Add New', 'yoast-test-helper' ),
				'add_new_item'  => \__( 'Add new book', 'yoast-test-helper' ),
			],
			'description'  => \__( 'Our books post type', 'yoast-test-helper' ),
			'public'       => true,
			'menu_icon'    => 'dashicons-book-alt',
			'has_archive'  => 'my-books',
			'rewrite'      => [
				'slug' => 'yoast-test-books',
			],
			'show_in_rest' => true,
		];
	}

	/**
	 * Get arguments to use when registering the movie post type.
	 *
	 * @return array Arguments to use when registering the movie post type.
	 */
	private function get_movie_args() {
		return [
			'label'        => \__( 'Movies', 'yoast-test-helper' ),
			'labels'       => [
				'name'          => \__( 'Movies', 'yoast-test-helper' ),
				'singular_name' => \__( 'Movie', 'yoast-test-helper' ),
				'add_new'       => \__( 'Add New', 'yoast-test-helper' ),
				'add_new_item'  => \__( 'Add new movie', 'yoast-test-helper' ),
			],
			'description'  => \__( 'Our movies post type', 'yoast-test-helper' ),
			'public'       => true,
			'menu_icon'    => 'dashicons-format-video',
			'has_archive'  => true,
			'rewrite'      => [
				'slug' => 'yoast-test-movies',
			],
			'show_in_rest' => true,
		];
	}
}
