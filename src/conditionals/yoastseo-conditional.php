<?php

namespace Yoast\WP\Test_Helper\Conditionals;

/**
 * Conditional to check if Yoast SEO is installed and active.
 */
class YoastSEO_Conditional implements Conditional {

	/**
	 * Checks if Yoast SEO is active by checking if the WPSEO_VERSION constant is defined.
	 *
	 * @return bool
	 */
	public function is_met(): bool {
		return \defined( 'WPSEO_VERSION' );
	}
}
