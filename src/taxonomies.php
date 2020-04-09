<?php

namespace Yoast\WP\Test_Helper;

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
	private $category_args = [
		'label'        => 'Categories',
		'labels'       => [
			'name'          => 'Categories',
			'singular_name' => 'Category',
		],
		'rewrite'      => [
			'slug' => '',
		],
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
	];

	/**
	 * Arguments to use when registering the genre taxonomy.
	 *
	 * @var array
	 */
	private $genre_args = [
		'label'        => 'Genres',
		'labels'       => [
			'name'          => 'Genres',
			'singular_name' => 'Genre',
			'search_items'  => 'Search Genres',
			'all_items'     => 'All Genres',
			'edit_item'     => 'Edit Genre',
			'update_item'   => 'Update Genre',
			'add_new_item'  => 'Add New Genre',
			'new_item_name' => 'New Genre Name',
			'menu_name'     => 'Genre',
		],
		'rewrite'      => [
			'slug' => '',
		],
		'hierarchical' => false,
		'public'       => true,
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
	 */
	public function add_hooks() {
		if ( $this->option->get( 'enable_post_types' ) === true ) {
			\add_action( 'init', [ $this, 'register_taxonomies' ] );
		}
	}

	/**
	 * Registers our post types.
	 */
	public function register_taxonomies() {
		// Taxonomies for books.
		\register_taxonomy( 'book-category', [ 'book' ], $this->set_slug( $this->category_args, 'yoast-test-book-category' ) );
		\register_taxonomy( 'book-genre', [ 'book' ], $this->set_slug( $this->genre_args, 'yoast-test-book-genre' ) );

		// Taxonomies for movies.
		\register_taxonomy( 'movie-category', [ 'movie' ], $this->set_slug( $this->category_args, 'yoast-test-movie-category' ) );
		\register_taxonomy( 'movie-genre', [ 'movie' ], $this->set_slug( $this->genre_args, 'yoast-test-movie-genre' ) );
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
