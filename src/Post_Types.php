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
			'slug' => 'books',
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
			'slug' => 'movies',
		),
		'show_in_rest' => true,
	);

	/**
	 * Post_Types constructor.
	 */
	public function __construct() {
		$this->option = new Option();
	}

	/**
	 * Register the needed hooks.
	 */
	public function add_hooks() {
		if ( $this->option->get( 'enable_post_types' ) === true ) {
			add_action( 'init', array( $this, 'register_post_types' ) );
		}

		add_action( 'admin_post_yoast_seo_test_post_types', array( $this, 'handle_submit' ) );
	}

	/**
	 * Registers our post types.
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
		$output  = '<h2>Post types &amp; Taxonomies</h2>';
		$output .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= wp_nonce_field( 'yoast_seo_test_post_types', '_wpnonce', true, false );
		$output .= '<input type="hidden" name="action" value="yoast_seo_test_post_types">';

		$output .= '<p>This adds two post types, Books and Movies, each with two taxonomies of their own, to your test site. Disabling this setting will not remove existing data from your database.</p>';
		$output .= '<input type="checkbox" ' . checked( $this->option->get( 'enable_post_types' ), true, false ) . ' name="enable_post_types" id="enable_post_types"/> <label for="enable_post_types">Enable post types & taxonomies.</label>';
		$output .= '<br/><br/>';
		$output .= '<button class="button button-primary">Save</button>';
		$output .= '</form>';

		return $output;
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( check_admin_referer( 'yoast_seo_test_post_types' ) !== false ) {
			$this->option->set( 'enable_post_types', isset( $_POST['enable_post_types'] ) );
		}

		wp_safe_redirect( self_admin_url( 'tools.php?page=' . apply_filters( 'yoast_version_control_admin_page', '' ) ) );
	}
}
