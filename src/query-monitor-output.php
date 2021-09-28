<?php

namespace Yoast\WP\Test_Helper;

use QM_Output_Html;

/**
 * Class to output the Indexable info within Query Monitor.
 */
class Query_Monitor_Output extends QM_Output_Html {

	/**
	 * Yoast_QueryMonitor_Output constructor.
	 *
	 * Empty to overwrite the parent class constructor.
	 */
	public function __construct() {
		// Intentionally left blank.
	}

	/**
	 * Returns the name of the output.
	 *
	 * @return string
	 */
	public function name() {
		return 'Yoast SEO';
	}

	/**
	 * Renders the Query Monitor output integration.
	 */
	public function output() {
		$this->before_non_tabular_output( 'qm-yoast-seo', $this->name() );

		echo '<section>';
		echo '<h3>Indexable</h3>';

		$model = \YoastSEO()->meta->for_current_page()->model;

		echo '<table>';
		echo '<tbody>';

		$keys = [
			'id',
			'permalink',
			'permalink_hash',
			'object_id',
			'object_type',
			'object_sub_type',
			'author_id',
			'post_parent',
			'title',
			'description',
			'breadcrumb_title',
			'post_status',
			'is_public',
			'is_protected',
			'has_public_posts',
			'number_of_pages',
			'canonical',
			'primary_focus_keyword',
			'primary_focus_keyword_score',
			'readability_score',
			'is_cornerstone',
			'is_robots_noindex',
			'is_robots_nofollow',
			'is_robots_noarchive',
			'is_robots_noimageindex',
			'is_robots_nosnippet',
			'twitter_title',
			'twitter_image',
			'twitter_description',
			'twitter_image_id',
			'twitter_image_source',
			'open_graph_title',
			'open_graph_description',
			'open_graph_image',
			'open_graph_image_id',
			'open_graph_image_source',
			'open_graph_image_meta',
			'link_count',
			'incoming_link_count',
			'prominent_words_version',
			'created_at',
			'updated_at',
			'blog_id',
			'language',
			'region',
			'schema_page_type',
			'schema_article_type',
			'has_ancestors',
			'estimated_reading_time_minutes',
		];
		foreach ( $keys as $key ) {
			echo '<tr>';
			echo '<th scope="row">' . \esc_html( $key ) . '</th>';
			$val = $model->__get( $key );
			echo '<td><pre>';
			if ( \is_array( $val ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				\print_r( $val );
			}
			else {
				echo \esc_html( $val );
			}
			echo '</pre></td>';

			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';

		$this->after_non_tabular_output();
	}
}
