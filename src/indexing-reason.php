<?php

namespace Yoast\WP\Test_Helper;


/**
 * Adds a filter to change the alert based on the saved set indexing reason.
 */
class Indexing_Reason implements Integration {

	/**
	 * Registers the hook to set the indexing reason.
	 */
	public function add_hooks() {
		\add_filter( 'wpseo_indexables_indexation_alert', [ $this, 'set_indexing_alert' ], 10, 2 );
	}

	/**
	 * Sets the indexing alert to something more specific when the reason is an indexables reset.
	 *
	 * @param string $alert  The current content of alert.
	 * @param string $reason The reason to show the alert for.
	 *
	 * @return string The reason to show.
	 */
	public function set_indexing_alert( $alert, $reason  ) {
		if ( $reason !== 'indexables-reset-by-test-helper' ) {
			return $alert;
		}

		return sprintf(
			\esc_html( 'Because of hard reset in the %1$s, some of your SEO data needs to be reprocessed.' ),
			'Yoast Test Helper'
		);
	}
}
