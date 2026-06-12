<?php

namespace Yoast\WP\Test_Helper\Conditionals;

/**
 * Trait No_Conditionals.
 * This trait can be used for classes that do not have any conditionals.
 * {@see Conditional_Aware}
 */
trait No_Conditionals {

	/**
	 * An empty array of conditionals, signifying that there are no conditionals for this class.
	 *
	 * @return Conditional[] An empty array of conditionals.
	 */
	public static function get_conditionals(): array {
		return [];
	}
}
