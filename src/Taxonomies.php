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
class Taxonomies implements Integration {

	/**
	 * Arguments to use when registering category like taxonomies.
	 *
	 * @var array
	 */
	private $category_args = array(
		'label'        => 'Categories',
		'labels'       => array(
			'name'          => 'Categories',
			'singular_name' => 'Category',
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
	);

	/**
	 * Arguments to use when registering the genre taxonomy.
	 *
	 * @var array
	 */
	private $genre_args = array(
		'label'        => 'Genres',
		'labels'       => array(
			'name'              => 'Genres',
			'singular_name'     => 'Genre',
			'search_items'      => 'Search Genres',
			'all_items'         => 'All Genres',
			'edit_item'         => 'Edit Genre',
			'update_item'       => 'Update Genre',
			'add_new_item'      => 'Add New Genre',
			'new_item_name'     => 'New Genre Name',
			'menu_name'         => 'Genre',
		),
		'hierarchical' => false,
		'public'       => true,
		'show_in_rest' => true,
	);

	/**
	 * Register the needed hooks.
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Registers our post types.
	 */
	public function register_taxonomies() {
		// Taxonomies for books.
		register_taxonomy( 'book-category', array( 'book' ), $this->category_args );
		register_taxonomy( 'book-genre', array( 'book' ), $this->genre_args );

		// Taxonomies for movies.
		register_taxonomy( 'movie-category', array( 'movie' ), $this->category_args );
		register_taxonomy( 'movie-genre', array( 'movie' ), $this->genre_args );
	}
}
