<?php
/**
 * Admin page hander.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Admin_Page implements Integration {
	/**
	 * List of admin page blocks.
	 *
	 * @var array
	 */
	protected $admin_page_blocks = array();

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		add_filter( 'yoast_version_control_admin_page', array( $this, 'get_admin_page' ) );
	}

	/**
	 * Retrieves the admin page identifier.
	 *
	 * @return string The admin page identifier.
	 */
	public function get_admin_page() {
		return 'yoast-version-controller';
	}

	/**
	 * Adding the assets to the page.
	 *
	 * @return void
	 */
	public function add_assets() {
		// CSS file.
		wp_enqueue_style(
			'yoast-test-admin-style',
			plugin_dir_url( YOAST_TEST_HELPER_FILE ) . 'assets/css/admin.css'
		);
	}

	/**
	 * Registers the admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		$menu_item = add_management_page(
			'Yoast Test',
			'Yoast Test',
			'manage_options',
			sanitize_key( $this->get_admin_page() ),
			array( $this, 'show_admin_page' )
		);
		add_action( 'admin_print_styles-' . $menu_item, array( $this, 'add_assets' ) );
	}

	/**
	 * Adds an admin block.
	 *
	 * @param callable $block Block to add.
	 *
	 * @return void
	 */
	public function add_admin_page_block( $block ) {
		$this->admin_page_blocks[] = $block;
	}

	/**
	 * Shows the admin page.
	 *
	 * @return void
	 */
	public function show_admin_page() {
		echo '<h1>Yoast Test Helper</h1>';

		do_action( 'yoast_version_controller_notifications' );

		array_map(
			function ( $block ) {
				echo '<div class="wpseo_test_block">';
				// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				echo $block();
				echo '</div>';
			}, $this->admin_page_blocks
		);
	}
}
