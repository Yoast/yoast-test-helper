<?php

namespace Yoast\WP\Test_Helper;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Admin_Page implements Integration {

	/**
	 * List of admin page blocks.
	 *
	 * @var callable[]
	 */
	protected $admin_page_blocks = [];

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );

		\add_filter( 'Yoast\WP\Test_Helper\admin_page', [ $this, 'get_admin_page' ] );
	}

	/**
	 * Retrieves the admin page identifier.
	 *
	 * @return string The admin page identifier.
	 */
	public function get_admin_page() {
		return 'yoast-test-helper';
	}

	/**
	 * Adding the assets to the page.
	 *
	 * @return void
	 */
	public function add_assets() {
		// CSS file.
		\wp_enqueue_style(
			'yoast-test-admin-style',
			\plugin_dir_url( \YOAST_TEST_HELPER_FILE ) . 'assets/css/admin.css',
			null,
			\YOAST_TEST_HELPER_VERSION
		);
		\wp_enqueue_script( 'masonry' );
	}

	/**
	 * Registers the admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		$menu_item = \add_management_page(
			'Yoast Test',
			'Yoast Test',
			'manage_options',
			\sanitize_key( $this->get_admin_page() ),
			[ $this, 'show_admin_page' ]
		);
		\add_action( 'admin_print_styles-' . $menu_item, [ $this, 'add_assets' ] );
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

		\do_action( 'Yoast\WP\Test_Helper\notifications', $this );

		echo '<div id="yoast_masonry">';
		$this->masonry_script();

		\array_map(
			static function( $block ) {
				$block_output = $block();
				if ( $block_output === '' ) {
					return;
				}
				echo '<div class="wpseo_test_block">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $block_output;
				echo '</div>';
			},
			$this->admin_page_blocks
		);
		echo '</div>';
	}

	/**
	 * Prints our masonry script.
	 *
	 * @return void
	 */
	private function masonry_script() {
		?>
		<script type="text/javascript">
			jQuery( window ).load( function() {
				var container = document.querySelector( "#yoast_masonry" );
				new Masonry( container, {
					itemSelector: ".wpseo_test_block",
					columnWidth: ".wpseo_test_block"
				} );
			} );
		</script>
		<?php
	}
}
