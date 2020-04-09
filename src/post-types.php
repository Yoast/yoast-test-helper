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
	 * Arguments to use when registering the book post type.
	 *
	 * @var array
	 */
	private $book_args = [
		'label'        => 'Books',
		'labels'       => [
			'name'          => 'Books',
			'singular_name' => 'Book',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add new book',
		],
		'description'  => 'Our books post type',
		'public'       => true,
		'menu_icon'    => 'dashicons-book-alt',
		'has_archive'  => 'my-books',
		'rewrite'      => [
			'slug' => 'yoast-test-books',
		],
		'show_in_rest' => true,
	];

	/**
	 * Arguments to use when registering the movie post type.
	 *
	 * @var array
	 */
	private $movie_args = [
		'label'        => 'Movies',
		'labels'       => [
			'name'          => 'Movies',
			'singular_name' => 'Movie',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add new movie',
		],
		'description'  => 'Our movies post type',
		'public'       => true,
		'menu_icon'    => 'dashicons-format-video',
		'has_archive'  => true,
		'rewrite'      => [
			'slug' => 'yoast-test-movies',
		],
		'show_in_rest' => true,
	];

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
		\register_post_type( 'book', $this->book_args );
		\register_post_type( 'movie', $this->movie_args );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'enable_post_types',
			'Enable post types & taxonomies.',
			$this->option->get( 'enable_post_types' )
		);

		$fields .= Form_Presenter::create_checkbox(
			'enable_gutenberg_books',
			'Enable block editor for Books.',
			$this->option->get( 'enable_gutenberg_books' )
		);

		$fields .= Form_Presenter::create_checkbox(
			'enable_gutenberg_videos',
			'Enable block editor for Videos.',
			$this->option->get( 'enable_gutenberg_videos' )
		);

		return Form_Presenter::get_html( 'Post types & Taxonomies', 'yoast_seo_test_post_types', $fields );
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
}
