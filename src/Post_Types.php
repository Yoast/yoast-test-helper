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
	 * Register the needed hooks.
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Registers our post types.
	 */
	public function register_post_types() {
		register_post_type( 'book', $this->book_args );
		register_post_type( 'movie', $this->movie_args );
	}
}
