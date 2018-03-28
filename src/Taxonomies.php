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
	 * Holds our option instance.
	 *
	 * @var Option
	 */
	private $option;

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
		'rewrite'      => array(
			'slug' => '',
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
			'name'          => 'Genres',
			'singular_name' => 'Genre',
			'search_items'  => 'Search Genres',
			'all_items'     => 'All Genres',
			'edit_item'     => 'Edit Genre',
			'update_item'   => 'Update Genre',
			'add_new_item'  => 'Add New Genre',
			'new_item_name' => 'New Genre Name',
			'menu_name'     => 'Genre',
		),
		'rewrite'      => array(
			'slug' => '',
		),
		'hierarchical' => false,
		'public'       => true,
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
	 */
	public function add_hooks() {
		if ( $this->option->get( 'enable_post_types' ) === true ) {
			add_action( 'init', array( $this, 'register_taxonomies' ) );
		}
	}

	/**
	 * Registers our post types.
	 */
	public function register_taxonomies() {
		// Taxonomies for books.
		register_taxonomy( 'book-category', array( 'book' ), $this->set_slug( $this->category_args, 'yoast-test-book-category' ) );
		register_taxonomy( 'book-genre', array( 'book' ), $this->set_slug( $this->genre_args, 'yoast-test-book-genre' ) );

		// Taxonomies for movies.
		register_taxonomy( 'movie-category', array( 'movie' ), $this->set_slug( $this->category_args, 'yoast-test-movie-category' ) );
		register_taxonomy( 'movie-genre', array( 'movie' ), $this->set_slug( $this->genre_args, 'yoast-test-movie-genre' ) );
	}

	/**
	 * Sets the slug for a taxonomy.
	 *
	 * @param array  $taxonomy The taxonomy arguments.
	 * @param string $slug     The slug to set.
	 *
	 * @return array Taxonomy parameters.
	 */
	private function set_slug( $taxonomy, $slug ) {
		$taxonomy['rewrite']['slug'] = $slug;

		return $taxonomy;
	}
}
