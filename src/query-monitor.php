<?php

namespace Yoast\WP\Test_Helper;

/**
 * Class to add a Yoast SEO tab to Query Monitor.
 */
class Query_Monitor implements Integration {

	/**
	 * Registers our menu item and output function.
	 */
	public function add_hooks() {
		\add_filter( 'qm/output/panel_menus', [ $this, 'add_menu_panel' ], 80 );
		\add_filter( 'qm/outputter/html', [ $this, 'output' ], 12, 1 );
	}

	/**
	 * Adds the panel item.
	 *
	 * @param array $menu Array of menu items.
	 *
	 * @return array Array of menu items.
	 */
	public function add_menu_panel( array $menu ) {
		$menu['yoast-seo'] = [
			'id'    => 'yoast-seo',
			'title' => 'Yoast SEO',
			'href'  => '#qm-yoast-seo',
		];

		return $menu;
	}

	/**
	 * Links the output to our output class.
	 *
	 * @param array $output Array with output for each tab.
	 *
	 * @return array Array with output for each tab.
	 */
	public function output( array $output ) {
		$output['yoast-seo'] = new Query_Monitor_Output();

		return $output;
	}
}
