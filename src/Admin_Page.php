<?php

namespace Yoast\Test_Helper;

class Admin_Page implements Integration {
	protected $admin_page_blocks = [];

	/**
	 *
	 */
	public function add_hooks() {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );

		add_filter( 'yoast_version_control_admin_page', [ $this, 'get_admin_page' ] );
	}

	/**
	 * @return string
	 */
	public function get_admin_page() {
		return 'yoast-version-controller';
	}

	/**
	 *
	 */
	public function register_admin_menu() {
		add_menu_page(
			'Yoast Test',
			'Yoast Test',
			'manage_options',
			sanitize_key( $this->get_admin_page() ),
			[ $this, 'show_admin_page' ],
			$this->get_icon(),
			999
		);
	}

	/**
	 * @param callable $block
	 */
	public function add_admin_page_block( callable $block ) {
		$this->admin_page_blocks[] = $block;
	}

	/**
	 *
	 */
	public function show_admin_page() {
		echo '<h1>Yoast Test Helper</h1>';

		do_action( 'yoast_version_controller_notifications' );

		array_map( function ( $block ) {
			echo $block();
		}, $this->admin_page_blocks );
	}

	/**
	 * @return string
	 */
	protected function get_icon() {
		if ( class_exists( '\WPSEO_Utils' ) ) {
			return \WPSEO_Utils::get_icon_svg( true );
		}

		$svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="100%" height="100%" style="fill:#82878c" viewBox="0 0 512 512"><g><g><g><g><path d="M203.6,395c6.8-17.4,6.8-36.6,0-54l-79.4-204h70.9l47.7,149.4l74.8-207.6H116.4c-41.8,0-76,34.2-76,76V357c0,41.8,34.2,76,76,76H173C189,424.1,197.6,410.3,203.6,395z"/></g><g><path d="M471.6,154.8c0-41.8-34.2-76-76-76h-3L285.7,365c-9.6,26.7-19.4,49.3-30.3,68h216.2V154.8z"/></g></g><path stroke-width="2.974" stroke-miterlimit="10" d="M338,1.3l-93.3,259.1l-42.1-131.9h-89.1l83.8,215.2c6,15.5,6,32.5,0,48c-7.4,19-19,37.3-53,41.9l-7.2,1v76h8.3c81.7,0,118.9-57.2,149.6-142.9L431.6,1.3H338z M279.4,362c-32.9,92-67.6,128.7-125.7,131.8v-45c37.5-7.5,51.3-31,59.1-51.1c7.5-19.3,7.5-40.7,0-60l-75-192.7h52.8l53.3,166.8l105.9-294h58.1L279.4,362z"/></g></g></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}
}
