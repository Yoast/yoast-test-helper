<?php

namespace Yoast\WP\Test_Helper\Conditionals;

/**
 * An interface for classes that are aware of conditionals, which determine when they should be active.
 */
interface Conditional_Aware {

	/**
	 * Returns the conditionals based on which this class should be active.
	 *
	 * @return Conditional[]
	 */
	public static function get_conditionals(): array;
}
