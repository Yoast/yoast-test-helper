<?php

namespace Yoast\WP\Test_Helper\Conditionals;

/**
 * Conditional interface, used to prevent integrations from loading.
 */
interface Conditional {

	/**
	 * Returns whether this conditional is met.
	 *
	 * @return bool Whether or not the conditional is met.
	 */
	public function is_met(): bool;
}
