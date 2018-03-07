<?php

namespace Yoast\Version_Controller;

use Yoast\Version_Controller\Plugin\Plugin;

class Admin_Page {
	/** @var Plugin[] Plugins */
	protected $plugins;

	/** @var Option_Control */
	protected $option_control;

	/**
	 * Admin_Page constructor.
	 *
	 * @param                $plugins
	 * @param Option_Control $option_control
	 */
	public function __construct( $plugins, Option_Control $option_control ) {
		$this->plugins        = $plugins;
		$this->option_control = $option_control;
	}

	/**
	 *
	 */
	public function add_hooks() {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_post_yoast_version_control', [ $this, 'handle_submit' ] );

		foreach ( $this->plugins as $plugin ) {
			add_action( 'admin_post_' . $plugin->get_identifier() . '-feature-reset', [ $this, 'reset_feature' ] );
		}
	}

	/**
	 *
	 */
	public function register_admin_menu() {
		add_menu_page(
			'Yoast Dev',
			'Version Controller',
			'manage_options',
			sanitize_key( $this->get_admin_page() ),
			[ $this, 'show_admin_page' ],
			'dashicons-gear',
			999
		);
	}

	/**
	 *
	 */
	public function show_admin_page() {
		echo '<h1>Yoast Version Controller</h1>';

		// Plugins.

		echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		echo '<input type="hidden" name="action" value="yoast_version_control">';

		foreach ( $this->plugins as $plugin ) {
			echo $this->get_plugin_option( $plugin );
		}

		echo '<button class="button button-primary">Save</button>';
		echo '</form>';

		// Show feature resets.
		foreach ( $this->plugins as $plugin ) {
			echo $this->get_plugin_features( $plugin );
		}
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_plugin_option( Plugin $plugin ) {
		return sprintf(
			'%s: <input type="text" name="%s" value="%s" maxlength="7" size="8"> %s<br>',
			esc_html( $plugin->get_name() ),
			esc_attr( $plugin->get_identifier() ),
			esc_attr( $this->get_version( $plugin ) ),
			$this->get_option_history_select( $plugin )
		);
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_option_history_select( Plugin $plugin ) {
		$history    = $this->option_control->get_saved_options( $plugin );
		$timestamps = array_reverse( array_keys( $history ) );

		return sprintf(
			'<select name="%s"><option name=""></option>%s</select>',
			esc_attr( $plugin->get_identifier() . '-history' ),
			implode( '', array_map( function ( $item ) {
				return sprintf( '<option name="%s">%s</option>', esc_attr( $item ),
					esc_html( date( 'Y-m-d H:i:s', $item ) ) );
			}, $timestamps ) )
		);
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_version( Plugin $plugin ) {
		$data = get_option( $plugin->get_version_option_name() );
		if ( isset( $data[ $plugin->get_version_key() ] ) ) {
			return $data[ $plugin->get_version_key() ];
		}

		return '';
	}

	/**
	 *
	 */
	public function handle_submit() {
		if ( ! $this->load_history() ) {
			foreach ( $this->plugins as $plugin ) {
				$this->update_plugin_version( $plugin, $_POST[ $plugin->get_identifier() ] );
			}
		}

		wp_redirect( self_admin_url( '?page=' . $this->get_admin_page() ) );
	}

	/**
	 * @return bool
	 */
	protected function load_history() {
		foreach ( $this->plugins as $plugin ) {
			// if -history is set, load the history item, otherwise save.
			if ( ! empty( $_POST[ $plugin->get_identifier() . '-history' ] ) ) {
				$this->option_control->restore_options( $plugin, $_POST[ $plugin->get_identifier() . '-history' ] );

				return true;
			}
		}

		return false;
	}

	public function get_admin_page() {
		return 'yoast-version-controller';
	}

	/**
	 * @param Plugin $plugin
	 * @param        $version
	 */
	protected function update_plugin_version( Plugin $plugin, $version ) {
		$data                               = get_option( $plugin->get_version_option_name() );
		$data[ $plugin->get_version_key() ] = $version;

		update_option( $plugin->get_version_option_name(), $data );

		$this->option_control->save_options( $plugin );
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return string
	 */
	protected function get_plugin_features( Plugin $plugin ) {
		$features = $plugin->get_features();
		if ( [] === $features ) {
			return '';
		}

		$form = '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">' .
		        '<input type="hidden" name="action" value="' . $plugin->get_identifier() . '-feature-reset">';

		return
			sprintf( '<h2>%s</h2>%s%s</form>',
				esc_html( $plugin->get_name() ),
				$form,
				implode( '', array_map( function ( $feature ) {
					return sprintf(
						'<button name="%s" type="submit" class="button">Reset %s</button> ',
						$feature,
						$feature
					);
				}, $features ) )
			);
	}

	public function reset_feature() {
		foreach ( $this->plugins as $plugin ) {
			echo $plugin->get_identifier();
			if ( $_POST['action'] !== $plugin->get_identifier() . '-feature-reset' ) {
				continue;
			}

			foreach ( $plugin->get_features() as $feature ) {
				if ( isset( $_POST[ $feature ] ) ) {
					$plugin->reset_feature( $feature );
				}
			}

			break;
		}

		wp_redirect( self_admin_url( '?page=' . $this->get_admin_page() ) );
	}
}
