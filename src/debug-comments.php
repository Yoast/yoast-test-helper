<?php

namespace Yoast\WP\Test_Helper;

Use \ReflectionClass;
Use \ReflectionFunction;

/**
 * Class to manage registering and rendering the admin page in WordPress.
 */
class Debug_Comments implements Integration {

	/**
	 * Holds our option instance.
	 *
	 * @var Option
	 */
	private $option;

	/**
	 * Class constructor.
	 *
	 * @param Option $option Our option array.
	 */
	public function __construct( Option $option ) {
		$this->option = $option;
	}

	/**
	 * Add the required hooks
	 *
	 * @return void
	 */
	public function add_hooks() {
		\add_action( 'wp_footer', [ $this, 'show_robots_trace' ], \PHP_INT_MAX);

		\add_action(
			'admin_post_yoast_seo_debug_settings',
			[ $this, 'handle_submit' ]
		);
	}

	public function show_robots_trace(){
		if ( $this->option->get( 'show_robots_hooks' ) === true && \defined( 'WPSEO_VERSION' ) ) {
			echo "<!-- This is a list of callback functions hooked into the 'wp_robots' filter:\n";
			print_r( $this->list_hooks( 'wp_robots' ) );
			echo "-->";
		}
	}

	public function list_hooks( $hook = '' ) {
		global $wp_filter;
	
		if ( isset( $wp_filter[$hook]->callbacks ) ) {      
			array_walk( $wp_filter[$hook]->callbacks, function( $callbacks, $priority ) use ( &$hooks ) {           
				foreach ( $callbacks as $id => $callback )
					$hooks[] = array_merge( [ 'id' => $id, 'priority' => $priority ], $callback );
			});         
		} else {
			return [];
		}
	
		foreach( $hooks as &$item ) {
			// skip if callback does not exist
			if ( !is_callable( $item['function'] ) ) continue;
	
			// function name as string or static class method eg. 'Foo::Bar'
			if ( is_string( $item['function'] ) ) {
				$ref = strpos( $item['function'], '::' ) ? new ReflectionClass( strstr( $item['function'], '::', true ) ) : new ReflectionFunction( $item['function'] );
				$item['file'] = $ref->getFileName();
				$item['line'] = get_class( $ref ) == 'ReflectionFunction' 
					? $ref->getStartLine() 
					: $ref->getMethod( substr( $item['function'], strpos( $item['function'], '::' ) + 2 ) )->getStartLine();
	
			// array( object, method ), array( string object, method ), array( string object, string 'parent::method' )
			} elseif ( is_array( $item['function'] ) ) {
	
				$ref = new ReflectionClass( $item['function'][0] );
	
				// $item['function'][0] is a reference to existing object
				$item['function'] = array(
					is_object( $item['function'][0] ) ? get_class( $item['function'][0] ) : $item['function'][0],
					$item['function'][1]
				);
				$item['file'] = $ref->getFileName();
				$item['line'] = strpos( $item['function'][1], '::' )
					? $ref->getParentClass()->getMethod( substr( $item['function'][1], strpos( $item['function'][1], '::' ) + 2 ) )->getStartLine()
					: $ref->getMethod( $item['function'][1] )->getStartLine();
	
			// closures
			} elseif ( is_callable( $item['function'] ) ) {     
				$ref = new ReflectionFunction( $item['function'] );         
				$item['function'] = get_class( $item['function'] );
				$item['file'] = $ref->getFileName();
				$item['line'] = $ref->getStartLine();
	
			}       
		}
	
		return $hooks;
	}
	

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'show_robots_hooks',
			/* translators: %1$s and %2$s expand to link to debug bar. */
			\sprintf( \esc_html__( 'Enable HTML comments to see wp_robots hooks usage.', 'yoast-test-helper' ) ),
			$this->option->get( 'show_robots_hooks' )
		);

		return Form_Presenter::get_html( \__( 'Debug Comments', 'yoast-test-helper' ), 'yoast_seo_debug_settings', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_debug_settings' ) !== false ) {
			$this->option->set( 'show_robots_hooks', isset( $_POST['show_robots_hooks'] ) );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}
}
