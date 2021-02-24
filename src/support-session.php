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
			\add_action( 'wp_head', [ $this, 'print_scripts' ] );
			\add_action( 'admin_head', [ $this, 'print_scripts' ] );
		}

		\add_action( 'admin_post_yoast_seo_test_support_session', [ $this, 'handle_submit' ] );
	}

	/**
	 * Print the scripts we need.
	 */
	public function print_scripts() {
		if ( $this->get_minutes_left() < 0 ) {
			$this->option->set( 'enable_support_session', false );
			$this->option->set( 'support_session_user_id', false );
			$this->option->set( 'support_session_start_time', false );
			return;
		}

		$current_user = \wp_get_current_user();
		if ( $this->option->get( 'support_session_user_id' ) !== $current_user->ID ) {
			return;
		}

		\printf(
			"<script type='text/javascript'>!function(e,t,n){function a(){var e=t.getElementsByTagName('script')[0],n=t.createElement('script');n.type='text/javascript',n.async=!0,n.src='https://beacon-v2.helpscout.net',e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],'complete'===t.readyState)return a();e.attachEvent?e.attachEvent('onload',a):e.addEventListener('load',a,!1)}(window,document,window.Beacon||function(){});</script>
<script type='text/javascript'>window.Beacon('init', '6311a7aa-4397-49df-8acc-05cbc61730c4'); window.Beacon( 'identify', { name: '%s', email: '%s'})</script>",
			\esc_attr( $current_user->display_name ),
			\esc_attr( $current_user->user_email )
		);
		\printf(
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
			\esc_attr( $current_user->display_name ),
			\esc_attr( $current_user->user_email )
		);
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$fields  = '<p><strong>' . \esc_html__( 'Please only check this box when asked to do so by one of the Yoast support agents.', 'yoast-test-helper' ) . '</strong></p>';
		$fields .= Form_Presenter::create_checkbox(
			'enable_support_session',
			\esc_html__( 'Enable support session.', 'yoast-test-helper' ),
			$this->option->get( 'enable_support_session' )
		);
		$fields .= '<p>' . \esc_html__( 'For safety & performance reasons, this feature will automatically disable itself after 4 hours.', 'yoast-test-helper' ) . '</p>';
		if ( $this->option->get( 'enable_support_session' ) ) {
			$diff_minutes = $this->get_minutes_left();
			$hours_left   = \floor( $diff_minutes / 60 );
			$minutes_left = \str_pad( ( $diff_minutes % 60 ), 2, '0' );
			$time_left    = $hours_left . ':' . $minutes_left;

			/* translators: %s is replaced by the number of hours and minuts left. */
			$fields .= '<p><strong>' . \sprintf( \esc_html__( 'Time left: %s hours', 'yoast-test-helper' ), '</strong>' . $time_left ) . '</p>';
		}

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
			$this->option->set( 'support_session_start_time', isset( $_POST['enable_support_session'] ) ? \time() : false );
		}

		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}

	/**
	 * Returns the number of minutes a user has left before the support session auto closes.
	 *
	 * @return float|int
	 */
	private function get_minutes_left() {
		$diff = ( ( 4 * \HOUR_IN_SECONDS ) - ( \time() - $this->option->get( 'support_session_start_time' ) ) );
		return ( $diff / 60 );
	}
}
