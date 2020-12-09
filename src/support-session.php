<?php

namespace Yoast\WP\Test_Helper;

/**
 * Shows admin notifications on the proper page.
 */
class Support_Session implements Integration {

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
	 * Enabling this plugin means you are in development mode.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( $this->option->get( 'enable_support_session' ) ) {
			\add_action( 'wp_print_scripts', [ $this, 'print_scripts' ] );
		}

		\add_action( 'admin_post_yoast_seo_test_support_session', [ $this, 'handle_submit' ] );
	}

	/**
	 * Print the scripts we need.
	 */
	public function print_scripts() {
		$current_user = wp_get_current_user();
		if ( $this->option->get( 'support_session_user_id' ) !== $current_user->ID ) {
			return;
		}

		printf(
			"<script type='text/javascript'>!function(e,t,n){function a(){var e=t.getElementsByTagName('script')[0],n=t.createElement('script');n.type='text/javascript',n.async=!0,n.src='https://beacon-v2.helpscout.net',e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],'complete'===t.readyState)return a();e.attachEvent?e.attachEvent('onload',a):e.addEventListener('load',a,!1)}(window,document,window.Beacon||function(){});</script>
<script type='text/javascript'>window.Beacon('init', '6311a7aa-4397-49df-8acc-05cbc61730c4'); window.Beacon( 'identify', { name: '%s', email: '%s'})</script>",
			esc_attr( $current_user->display_name ),
			esc_attr( $current_user->user_email )
		);
		printf(
			"<script>
(function(w, u, d){var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};var l = function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://code.upscope.io/eDAJYABAum.js';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(typeof u!=='function'){w.Upscope=i;l();}})(window, window.Upscope, document);
Upscope('init');
</script>
<script>
Upscope('updateConnection', {
  uniqueId: undefined,
  identities: ['%s', '%s']
});
</script>",
			esc_attr( $current_user->display_name ),
			esc_attr( $current_user->user_email )
		);
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields = Form_Presenter::create_checkbox(
			'enable_support_session',
			\esc_html__( 'Enable support session.', 'yoast-test-helper' ),
			$this->option->get( 'enable_support_session' )
		);

		return Form_Presenter::get_html( \__( 'Enable support session', 'yoast-test-helper' ), 'yoast_seo_test_support_session', $fields );
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( \check_admin_referer( 'yoast_seo_test_support_session' ) !== false ) {
			$this->option->set( 'enable_support_session', isset( $_POST['enable_support_session'] ) );
			$this->option->set( 'support_session_user_id', isset( $_POST['enable_support_session'] ) ? \get_current_user_id() : false );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}
}
