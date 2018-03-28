<?php
/**
 * The main plugin file.
 *
 * @package Yoast\Version_Controller
 */

namespace Yoast\Test_Helper;

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
	private $book_args = array(
		'label'        => 'Books',
		'labels'       => array(
			'name'          => 'Books',
			'singular_name' => 'Book',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add new book',
		),
		'description'  => 'Our books post type',
		'public'       => true,
		'menu_icon'    => 'dashicons-book-alt',
		'has_archive'  => true,
		'rewrite'      => array(
			'slug' => 'yoast-test-books',
		),
		'show_in_rest' => true,
	);

	/**
	 * Arguments to use when registering the movie post type.
	 *
	 * @var array
	 */
	private $movie_args = array(
		'label'        => 'Movies',
		'labels'       => array(
			'name'          => 'Movies',
			'singular_name' => 'Movie',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add new movie',
		),
		'description'  => 'Our movies post type',
		'public'       => true,
		'menu_icon'    => 'dashicons-format-video',
		'has_archive'  => true,
		'rewrite'      => array(
			'slug' => 'yoast-test-movies',
		),
		'show_in_rest' => true,
	);

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
			add_action( 'init', array( $this, 'register_post_types' ) );
		}

		add_action( 'admin_post_yoast_seo_test_post_types', array( $this, 'handle_submit' ) );
		add_filter( 'gutenberg_can_edit_post_type', array( $this, 'enable_gutenberg' ), 10, 2 );
	}

	/**
	 * Checks whether Gutenberg is enabled for a certain post type.
	 *
	 * @param bool   $can_edit  Whether or not Gutenberg can edit the post type.
	 * @param string $post_type The post type slug.
	 *
	 * @return bool Whether or not Gutenberg is enabled.
	 */
	public function enable_gutenberg( $can_edit, $post_type ) {
		if ( $post_type === $this->movie_args['rewrite']['slug'] && $this->option->get( 'enable_gutenberg_videos' ) === true ) {
			return true;
		}
		if ( $post_type === $this->book_args['rewrite']['slug'] && $this->option->get( 'enable_gutenberg_books' ) === true ) {
			return true;
		}

		return $can_edit;
	}

	/**
	 * Registers our post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		register_post_type( 'book', $this->book_args );
		register_post_type( 'movie', $this->movie_args );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'enable_post_types', 'Enable post types & taxonomies.',
			$this->option->get( 'enable_post_types' )
		);

		$fields .= Form_Presenter::create_checkbox(
			'enable_gutenberg_books', 'Enable Gutenberg for Books.',
			$this->option->get( 'enable_gutenberg_books' )
		);

		$fields .= Form_Presenter::create_checkbox(
			'enable_gutenberg_videos', 'Enable Gutenberg for Videos.',
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
		if ( check_admin_referer( 'yoast_seo_test_post_types' ) !== false ) {
			$this->set_bool_option( 'enable_post_types' );
			$this->set_bool_option( 'enable_gutenberg_books' );
			$this->set_bool_option( 'enable_gutenberg_videos' );
		}

		wp_safe_redirect(
			self_admin_url(
				'tools.php?page=' .
				apply_filters( 'yoast_version_control_admin_page', '' )
			)
		);
	}

	/**
	 * Sets a boolean option based on a POST parameter.
	 *
	 * @param string $option The option to check and set.
	 */
	private function set_bool_option( $option ) {
		// The nonce is checked in the handle_submit function.
		// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$this->option->set( $option, isset( $_POST[ $option ] ) );
	}
}
