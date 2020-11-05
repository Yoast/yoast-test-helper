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
		\register_taxonomy( 'book-category', [ 'book' ], $this->get_category_args( 'yoast-test-book-category' ) );
		\register_taxonomy( 'book-genre', [ 'book' ], $this->get_genre_args( 'yoast-test-book-genre' ) );

		// Taxonomies for movies.
		\register_taxonomy( 'movie-category', [ 'movie' ], $this->get_category_args( 'yoast-test-movie-category' ) );
		\register_taxonomy( 'movie-genre', [ 'movie' ], $this->get_genre_args( 'yoast-test-movie-genre' ) );
	}

	/**
	 * Get arguments to use when registering the category taxonomy.
	 *
	 * @param string $slug The slug to set for the taxonomy.
	 *
	 * @return array Arguments to use when registering the category taxonomy.
	 */
	private function get_category_args( $slug ) {
		return [
			'label'        => \__( 'Categories', 'yoast-test-helper' ),
			'labels'       => [
				'name'          => \__( 'Categories', 'yoast-test-helper' ),
				'singular_name' => \__( 'Category', 'yoast-test-helper' ),
			],
			'rewrite'      => [
				'slug' => $slug,
			],
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
		];
	}

	/**
	 * Get arguments to use when registering the genre taxonomy.
	 *
	 * @param string $slug The slug to set for the taxonomy.
	 *
	 * @return array Arguments to use when registering the genre taxonomy.
	 */
	private function get_genre_args( $slug ) {
		return [
			'label'        => \__( 'Genres', 'yoast-test-helper' ),
			'labels'       => [
				'name'          => \__( 'Genres', 'yoast-test-helper' ),
				'singular_name' => \__( 'Genre', 'yoast-test-helper' ),
				'search_items'  => \__( 'Search Genres', 'yoast-test-helper' ),
				'all_items'     => \__( 'All Genres', 'yoast-test-helper' ),
				'edit_item'     => \__( 'Edit Genre', 'yoast-test-helper' ),
				'update_item'   => \__( 'Update Genre', 'yoast-test-helper' ),
				'add_new_item'  => \__( 'Add New Genre', 'yoast-test-helper' ),
				'new_item_name' => \__( 'New Genre Name', 'yoast-test-helper' ),
				'menu_name'     => \__( 'Genre', 'yoast-test-helper' ),
			],
			'rewrite'      => [
				'slug' => $slug,
			],
			'hierarchical' => false,
			'public'       => true,
			'show_in_rest' => true,
		];
	}
}
