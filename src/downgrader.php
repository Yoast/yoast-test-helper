<?php

namespace Yoast\WP\Test_Helper;

use Automatic_Upgrader_Skin;
use Exception;
use Yoast\WP\Lib\Migrations\Adapter;
use Yoast\WP\SEO\Config\Migration_Status;
use Yoast\WP\SEO\Loader;
use WP_Upgrader;
use WPSEO_Options;
use ZipArchive;

/**
 * Downgrader class.
 */
class Downgrader implements Integration {

	/**
	 * Registers WordPress hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action( 'admin_post_yoast_rollback_control', [ $this, 'handle_submit' ] );
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$option = 'target_version';

		$output  = \sprintf( '<label for="%1$s">%2$s</label>', $option, __( 'Downgrade to version: ', 'yoast-test-helper' ) );
		$output .= \sprintf( '<input name="%1$s" id="%1$s" type="text"></input><br />', $option );

		$title = \sprintf(
			// translators: %1$s is Yoast SEO.
			__( 'Downgrade %1$s', 'yoast-test-helper' ),
			'Yoast SEO'
		);

		return Form_Presenter::get_html( $title, 'yoast_rollback_control', $output );
	}

	/**
	 * Handles the form submission.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( ! \check_admin_referer( 'yoast_rollback_control' ) ) {
			return;
		}
		if ( ! isset( $_POST['target_version'] ) ) {
			return;
		}

		$target_version = filter_var( \wp_unslash( $_POST['target_version'] ) );
		try {
			$this->downgrade( $target_version );
			\do_action(
				'Yoast\WP\Test_Helper\notification',
				new Notification(
					\sprintf(
						// translators: %1$s is Yoast SEO, %2$s is the version number it was downgraded to.
						__( '%1$s has been succesfully downgraded to version %2$s.', 'yoast-test-helper' ),
						'Yoast SEO',
						$target_version
					),
					'success'
				)
			);
		} catch ( Exception $e ) {
			\do_action(
				'Yoast\WP\Test_Helper\notification',
				new Notification( $e->getMessage(), 'error' )
			);
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Downgrades the Yoast SEO version.
	 *
	 * @param string $target_version The version to downgrade to.
	 *
	 * @throws Exception If the downgrade fails.
	 *
	 * @return void
	 */
	protected function downgrade( $target_version ) {
		if ( ! preg_match( '/^\d+\.\d+$/', $target_version ) ) {
			throw new Exception( __( 'An invalid version number was passed.', 'yoast-test-helper' ) );
		}

		if ( version_compare( $target_version, '16.0', '<' ) ) {
			throw new Exception( __( 'Downgrading to below 16.0 is not supported', 'yoast-test-helper' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		$upgrader = new WP_Upgrader( new Automatic_Upgrader_Skin() );
		$upgrader->fs_connect();

		$downloaded_archive = $upgrader->download_package( "https://downloads.wordpress.org/plugin/wordpress-seo.$target_version.zip" );

		if ( is_wp_error( $downloaded_archive ) ) {
			throw new Exception( __( 'The requested version could not be downloaded', 'yoast-test-helper' ) );
		}

		// Open the downloaded archive.
		$zip = new ZipArchive();
		$zip->open( $downloaded_archive );

		$all_migration_files = \glob( \WPSEO_PATH . 'src/config/migrations/*.php' );

		// Find all migrations that are not in the downgraded archive.
		$migrations_to_downgrade = [];
		foreach ( $all_migration_files as $migration_file ) {
			$migration_file = str_replace( \WPSEO_PATH, '', $migration_file );
			if ( ! $zip->getFromName( 'wordpress-seo/' . $migration_file ) ) {
				$basename                  = \basename( $migration_file, '.php' );
				$version                   = \explode( '_', $basename )[0];
				$migrations_to_downgrade[] = $version;
			}
		}
		// Migrations should be downgrades from last to first.
		\sort( $migrations_to_downgrade, \SORT_STRING );
		$migrations_to_downgrade = \array_reverse( $migrations_to_downgrade );

		$loader           = \YoastSEO()->classes->get( Loader::class );
		$adapter          = \YoastSEO()->classes->get( Adapter::class );
		$migration_status = \YoastSEO()->classes->get( Migration_Status::class );
		$migrations       = $loader->get_migrations( 'free' );

		if ( ! $migration_status->lock_migration( 'free' ) ) {
			throw new Exception( __( 'A migration is already in progress. Please try again later.', 'yoast-test-helper' ) );
		}

		// Downgrade all migrations.
		foreach ( $migrations_to_downgrade as $version ) {
			$class = $migrations[ $version ];
			try {
				$migration = new $class( $adapter );
				$adapter->start_transaction();
				$migration->down();
				$adapter->remove_version( $version );
				$adapter->commit_transaction();
			} catch ( Exception $e ) {
				$this->adapter->rollback_transaction();

				throw new Exception(
					\sprintf(
						// translators: %1$s is the class name of the migration that failed, %2$s is the message given by the failure.
						__( 'Migration %1$s failed with the message: %2$s', 'yoast-test-helper' ),
						$class,
						$e->getMessage()
					),
					0,
					$e
				);
			}
		}

		$working_dir = $upgrader->unpack_package( $downloaded_archive, true );
		if ( is_wp_error( $working_dir ) ) {
			throw new Exception( __( 'Could not unpack the requested version.', 'yoast-test-helper' ) );
		}

		$result = $upgrader->install_package(
			[
				'source'            => $working_dir,
				'destination'       => WP_PLUGIN_DIR,
				'clear_destination' => true,
				'clear_working'     => true,
				'hook_extra'        => [
					'type'   => 'plugin',
					'action' => 'install',
				],
			]
		);
		if ( is_wp_error( $result ) ) {
			throw new Exception( __( 'Could not install the requested version.', 'yoast-test-helper' ) );
		}
		WPSEO_Options::set( 'version', $target_version );
	}
}
