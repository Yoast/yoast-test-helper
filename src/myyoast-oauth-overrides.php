<?php

namespace Yoast\WP\Test_Helper;

use WP_Error;
use WPSEO_Utils;

/**
 * Configures the Yoast SEO MyYoast OAuth client for the active environment.
 *
 * The active environment is owned by {@see Domain_Dropdown}; this integration
 * just configures the OAuth credentials (PAT, software statement, initial
 * access token) that go with whichever environment that dropdown is set to.
 *
 * Instead of taking a raw software statement and initial access token by hand,
 * the developer enters a MyYoast Personal Access Token (PAT) for the chosen
 * issuer and the helper fetches a fresh SS+IAT pair from
 * `{issuer}/api/oauth/software-statements` (the same endpoint the
 * `update-myyoast-credentials` Grunt task uses at artifact-build time).
 *
 * PATs and credential pairs are persisted per issuer URL so a developer can
 * keep credentials for multiple environments side-by-side and switch by
 * changing the Domain Dropdown.
 *
 * The three `wpseo_myyoast_*` filters fire iff the active environment is
 * non-production AND credentials are stored for it.
 */
class MyYoast_OAuth_Overrides implements Integration {

	/**
	 * The action name used to wipe all local OAuth state in Yoast SEO.
	 *
	 * @var string
	 */
	private const CLEAR_STATE_ACTION = 'wpseo_myyoast_clear_client_state';

	/**
	 * The path under the chosen issuer URL that mints SS+IAT pairs.
	 *
	 * @var string
	 */
	private const CREDENTIALS_PATH = '/api/oauth/software-statements';

	/**
	 * Static claims sent in the SS request body. Mirrors `SOFTWARE_STATEMENT_CLAIMS`
	 * in `update-myyoast-credentials.js`.
	 *
	 * @var array<string, string|bool|array<int, string>>
	 */
	private const STATEMENT_CLAIMS = [
		'softwareId'              => 'yoast/wordpress-seo',
		'clientName'              => 'Yoast SEO',
		'logoUri'                 => 'https://yoast.com/app/uploads/2025/11/premium.svg',
		'clientUri'               => 'https://yoast.com/wordpress/plugins/seo/',
		'tosUri'                  => 'https://yoast.com/terms-of-service/',
		'policyUri'               => 'https://yoast.com/privacy-policy/',
		'contacts'                => [ 'support@yoast.com' ],
		'cleanupWhenInactive'     => true,
		'tokenEndpointAuthMethod' => 'private_key_jwt',
	];

	/**
	 * Holds our option instance.
	 *
	 * @var Option
	 */
	private $option;

	/**
	 * Source of truth for the active MyYoast environment.
	 *
	 * @var Domain_Dropdown
	 */
	private $domain_dropdown;

	/**
	 * Class constructor.
	 *
	 * @param Option          $option          Our option array.
	 * @param Domain_Dropdown $domain_dropdown The card that owns the active-environment selection.
	 */
	public function __construct( Option $option, Domain_Dropdown $domain_dropdown ) {
		$this->option          = $option;
		$this->domain_dropdown = $domain_dropdown;
	}

	/**
	 * Registers WordPress hooks and filters.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( $this->should_apply_overrides() ) {
			\add_filter( 'wpseo_myyoast_issuer_url', [ $this, 'filter_issuer_url' ] );
			\add_filter( 'wpseo_myyoast_software_statement', [ $this, 'filter_software_statement' ] );
			\add_filter( 'wpseo_myyoast_initial_access_token', [ $this, 'filter_initial_access_token' ] );
		}

		\add_action( 'admin_post_yoast_test_myyoast_oauth_save', [ $this, 'handle_save' ] );
		\add_action( 'admin_post_yoast_test_myyoast_oauth_fetch', [ $this, 'handle_fetch' ] );
		\add_action( 'admin_post_yoast_test_myyoast_oauth_clear_client', [ $this, 'handle_clear_client' ] );
	}

	/**
	 * Filters the MyYoast issuer URL.
	 *
	 * @return string The active environment URL.
	 */
	public function filter_issuer_url() {
		return $this->get_active_issuer();
	}

	/**
	 * Filters the MyYoast software statement.
	 *
	 * @param string $value The default software statement.
	 *
	 * @return string The stored software statement when one is configured, the original value otherwise.
	 */
	public function filter_software_statement( $value ) {
		$credentials = $this->get_active_credentials();
		if ( empty( $credentials['software_statement'] ) ) {
			return $value;
		}

		return $credentials['software_statement'];
	}

	/**
	 * Filters the MyYoast initial access token.
	 *
	 * @param string $value The default initial access token.
	 *
	 * @return string The stored initial access token when one is configured, the original value otherwise.
	 */
	public function filter_initial_access_token( $value ) {
		$credentials = $this->get_active_credentials();
		if ( empty( $credentials['initial_access_token'] ) ) {
			return $value;
		}

		return $credentials['initial_access_token'];
	}

	/**
	 * Retrieves the controls.
	 *
	 * @return string The HTML to use to render the controls.
	 */
	public function get_controls() {
		$active_issuer = $this->get_active_issuer();
		$is_production = $this->is_production( $active_issuer );
		$has_pat       = $this->has_active_pat();
		$credentials   = $this->get_active_credentials();

		$fields = '<p>' . \sprintf(
			/* translators: %s expands to the active issuer URL. */
			\esc_html__( 'Configures the OAuth credentials for the environment selected in the Domain Dropdown card: %s.', 'yoast-test-helper' ),
			'<code>' . \esc_html( $active_issuer ) . '</code>',
		) . '</p>';

		if ( $is_production ) {
			$fields .= '<p><em>' . \esc_html__( 'No overrides apply on production — Yoast SEO ships with valid baked-in credentials. Switch the Domain Dropdown to a non-production environment to configure credentials here.', 'yoast-test-helper' ) . '</em></p>';
		}

		$fields         .= \sprintf(
			'<label for="myyoast_oauth_pat">%1$s</label> <input type="password" size="40" id="myyoast_oauth_pat" name="myyoast_oauth_pat" value="" autocomplete="off" placeholder="myp_••••••••"/><br/>',
			\esc_html__( 'MyYoast PAT for this environment:', 'yoast-test-helper' ),
		);

		$output = Form_Presenter::get_html(
			\__( 'MyYoast OAuth overrides', 'yoast-test-helper' ),
			'yoast_test_myyoast_oauth_save',
			$fields,
		);

		$output .= '<hr/>';
		$output .= $this->render_status( $active_issuer, $credentials );

		$output .= '<div class="wpseo_test_actions">';
		$output .= $this->render_action_form(
			'yoast_test_myyoast_oauth_fetch',
			\esc_html__( 'Fetch credentials', 'yoast-test-helper' ),
			( $is_production || ! $has_pat ),
			false,
		);
		$output .= '</div>';

		$clear_prompt = \__(
			'Clear all MyYoast OAuth state?

This wipes the local OAuth client state for the active issuer:

• The plugin will deregister with MyYoast (best-effort), then forget the registered client.
• All site-level and user-level access tokens will be deleted. Every WordPress user that connected to MyYoast must sign in again.
• Both key pairs (registration JWT signing + DPoP) will be rotated.
• OIDC discovery, JWKS and DPoP nonce caches will be cleared.
• PATs stored in the test helper itself are kept.',
			'yoast-test-helper',
		);

		$output .= '<hr/>';
		$output .= $this->render_action_form(
			'yoast_test_myyoast_oauth_clear_client',
			\esc_html__( 'Clear OAuth state', 'yoast-test-helper' ),
			false,
			true,
			$clear_prompt,
		);

		return $output;
	}

	/**
	 * Renders an extra single-button form (Fetch / Clear) without its own `<h2>` heading.
	 *
	 * @param string $action          The admin-post action name (also used as the nonce field).
	 * @param string $label           The button label.
	 * @param bool   $disabled        Whether the button should be disabled.
	 * @param bool   $is_link         Whether to render a destructive red link instead of a button.
	 * @param string $confirm_message Optional message for a window.confirm() guard. Empty string = no prompt.
	 *
	 * @return string The HTML.
	 */
	private function render_action_form( $action, $label, $disabled, $is_link, $confirm_message = '' ) {
		$attrs       = ( $disabled ) ? ' disabled="disabled"' : '';
		$button_attr = ( $is_link ) ? 'class="button-link button-link-delete"' : 'class="button"';
		$onsubmit    = ( $confirm_message === '' ) ? '' : ' onsubmit="return confirm(' . \esc_attr( WPSEO_Utils::format_json_encode( $confirm_message ) ) . ')"';

		$output  = '<form action="' . \esc_url( \admin_url( 'admin-post.php' ) ) . '" method="POST"' . $onsubmit . '>';
		$output .= \str_replace( 'id="_wpnonce"', '', \wp_nonce_field( $action, '_wpnonce', true, false ) );
		$output .= '<input type="hidden" name="action" value="' . \esc_attr( $action ) . '">';
		$output .= \sprintf(
			'<button id="%1$s_button" %2$s%3$s type="submit">%4$s</button>',
			\esc_attr( $action ),
			$button_attr,
			$attrs,
			$label,
		);
		$output .= '</form>';

		return $output;
	}

	/**
	 * Renders a compact status block summarising the active issuer and any stored credentials.
	 *
	 * @param string                               $active_issuer The currently active issuer URL.
	 * @param array<string, string|int|float|bool> $credentials   The stored credentials for the active issuer.
	 *
	 * @return string The HTML.
	 */
	private function render_status( $active_issuer, array $credentials ) {
		$has_credentials   = ( ! empty( $credentials['software_statement'] ) && ! empty( $credentials['initial_access_token'] ) );
		$credentials_label = ( $has_credentials ) ? \esc_html__( 'yes', 'yoast-test-helper' ) : \esc_html__( 'no', 'yoast-test-helper' );

		$output  = '<p><strong>' . \esc_html__( 'Active issuer:', 'yoast-test-helper' ) . '</strong> ' . \esc_html( $active_issuer ) . '<br/>';
		$output .= '<strong>' . \esc_html__( 'Stored credentials:', 'yoast-test-helper' ) . '</strong> ' . $credentials_label . '</p>';

		if ( ! $has_credentials ) {
			return $output;
		}

		$claims = $this->decode_software_statement_claims( (string) $credentials['software_statement'] );
		if ( $claims === [] ) {
			return $output;
		}

		$rows = '';
		foreach ( [ 'software_id', 'software_version', 'iss', 'aud', 'iat', 'exp', 'jti' ] as $claim ) {
			if ( ! isset( $claims[ $claim ] ) ) {
				continue;
			}
			$rows .= '<tr><td><strong>' . \esc_html( $claim ) . '</strong></td>';
			$rows .= '<td><code>' . \esc_html( $this->stringify_claim( $claims[ $claim ] ) ) . '</code></td></tr>';
		}

		$output .= '<p><button type="button" class="button-link" command="show-modal" commandfor="myyoast_oauth_claims_dialog">' . \esc_html__( 'View decoded software statement claims', 'yoast-test-helper' ) . '</button></p>';

		$output .= '<dialog id="myyoast_oauth_claims_dialog" class="wpseo_test_dialog">';
		$output .= '<header class="wpseo_test_dialog_header">';
		$output .= '<h3>' . \esc_html__( 'Decoded software statement claims', 'yoast-test-helper' ) . '</h3>';
		$output .= '<button type="button" class="wpseo_test_dialog_close" command="close" commandfor="myyoast_oauth_claims_dialog" aria-label="' . \esc_attr__( 'Close', 'yoast-test-helper' ) . '">&times;</button>';
		$output .= '</header>';
		$output .= '<table>' . $rows . '</table>';
		$output .= '</dialog>';

		return $output;
	}

	/**
	 * Handles the settings form submit. Stores the PAT for the active environment.
	 *
	 * @return void
	 */
	public function handle_save() {
		if ( \check_admin_referer( 'yoast_test_myyoast_oauth_save' ) === false ) {
			$this->redirect();

			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce is verified above.
		if ( isset( $_POST['myyoast_oauth_pat'] ) && \is_string( $_POST['myyoast_oauth_pat'] ) ) {
			$pat    = \trim( \sanitize_text_field( \wp_unslash( $_POST['myyoast_oauth_pat'] ) ) );
			$issuer = $this->get_active_issuer();
			if ( $pat !== '' && ! $this->is_production( $issuer ) ) {
				$this->store_credential_field( $issuer, 'pat', $pat );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$this->redirect();
	}

	/**
	 * Handles the fetch form submit.
	 *
	 * @return void
	 */
	public function handle_fetch() {
		if ( \check_admin_referer( 'yoast_test_myyoast_oauth_fetch' ) === false ) {
			$this->redirect();

			return;
		}

		$issuer = $this->get_active_issuer();
		if ( $this->is_production( $issuer ) ) {
			$this->add_notification( \__( 'The Domain Dropdown is on production. Switch to a staging or local environment before fetching credentials.', 'yoast-test-helper' ), 'error' );
			$this->redirect();

			return;
		}

		$credentials = $this->get_active_credentials();
		$pat         = isset( $credentials['pat'] ) ? (string) $credentials['pat'] : '';
		if ( $pat === '' ) {
			$this->add_notification( \__( 'No PAT stored for the active environment. Paste one in the settings form and save first.', 'yoast-test-helper' ), 'error' );
			$this->redirect();

			return;
		}

		$response = $this->request_credentials( $issuer, $pat );
		if ( \is_wp_error( $response ) ) {
			$this->add_notification(
				\sprintf(
					/* translators: %s expands to the WordPress error message. */
					\esc_html__( 'Could not reach the credentials endpoint: %s', 'yoast-test-helper' ),
					$response->get_error_message(),
				),
				'error',
			);
			$this->redirect();

			return;
		}

		$status = (int) \wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 ) {
			$this->add_notification(
				\sprintf(
					/* translators: 1: HTTP status code, 2: response body. */
					\esc_html__( 'Credentials endpoint returned HTTP %1$d. Body: %2$s', 'yoast-test-helper' ),
					$status,
					\esc_html( (string) \wp_remote_retrieve_body( $response ) ),
				),
				'error',
			);
			$this->redirect();

			return;
		}

		$body    = (string) \wp_remote_retrieve_body( $response );
		$decoded = \json_decode( $body, true );
		if ( ! \is_array( $decoded ) ) {
			$this->add_notification( \__( 'Credentials endpoint returned a response that is not valid JSON.', 'yoast-test-helper' ), 'error' );
			$this->redirect();

			return;
		}

		$software_statement   = '';
		$initial_access_token = '';
		if ( isset( $decoded['softwareStatement'] ) && \is_string( $decoded['softwareStatement'] ) ) {
			$software_statement = $decoded['softwareStatement'];
		}
		if ( isset( $decoded['initialAccessToken'] ) && \is_string( $decoded['initialAccessToken'] ) ) {
			$initial_access_token = $decoded['initialAccessToken'];
		}

		if ( $software_statement === '' || $initial_access_token === '' ) {
			$this->add_notification( \__( 'Credentials endpoint response is missing softwareStatement or initialAccessToken.', 'yoast-test-helper' ), 'error' );
			$this->redirect();

			return;
		}

		$this->store_credential_field( $issuer, 'software_statement', $software_statement );
		$this->store_credential_field( $issuer, 'initial_access_token', $initial_access_token );
		$this->store_credential_field( $issuer, 'fetched_at', \time() );

		$this->add_notification(
			\sprintf(
				/* translators: %s expands to the issuer URL. */
				\esc_html__( 'Stored a fresh software statement and initial access token for %s.', 'yoast-test-helper' ),
				\esc_html( $issuer ),
			),
			'success',
		);
		$this->redirect();
	}

	/**
	 * Handles the clear-state form submit.
	 *
	 * @return void
	 */
	public function handle_clear_client() {
		if ( \check_admin_referer( 'yoast_test_myyoast_oauth_clear_client' ) === false ) {
			$this->redirect();

			return;
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Foreign hook owned by the wordpress-seo plugin.
		\do_action( self::CLEAR_STATE_ACTION );

		$this->add_notification( \__( 'Fired wpseo_myyoast_clear_client_state. Local OAuth state in Yoast SEO has been wiped (if Yoast SEO is active and the MyYoast connection feature flag is on).', 'yoast-test-helper' ), 'success' );
		$this->redirect();
	}

	/**
	 * Sends the SS+IAT request to the configured issuer.
	 *
	 * @param string $issuer The issuer URL.
	 * @param string $pat    The bearer token to authenticate with.
	 *
	 * @return array<string, string|int|array<string, string>|object>|WP_Error The wp_remote_post response.
	 *                        The array shape is the standard WordPress HTTP response; we hand it straight
	 *                        to wp_remote_retrieve_*() helpers and never poke into it directly.
	 */
	private function request_credentials( $issuer, $pat ) {
		$body                    = self::STATEMENT_CLAIMS;
		$body['softwareVersion'] = ( \defined( 'WPSEO_VERSION' ) ) ? \WPSEO_VERSION : 'dev';

		return \wp_remote_post(
			\rtrim( $issuer, '/' ) . self::CREDENTIALS_PATH,
			[
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $pat,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				],
				'body'    => WPSEO_Utils::format_json_encode( $body ),
			],
		);
	}

	/**
	 * Decodes the payload segment of an unverified JWT into an associative array.
	 * The signature is not checked — this is for display only.
	 *
	 * @param string $jwt The JWT.
	 *
	 * @return array<string, string|int|float|bool|array<int|string, string|int|float|bool>> The decoded claims, or an empty array on failure.
	 */
	private function decode_software_statement_claims( $jwt ) {
		$segments = \explode( '.', $jwt );
		if ( \count( $segments ) !== 3 ) {
			return [];
		}

		$padded  = \strtr( $segments[1], '-_', '+/' );
		$padded .= \str_repeat( '=', ( ( 4 - ( \strlen( $padded ) % 4 ) ) % 4 ) );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Decoding a JWT segment, not obfuscation.
		$json = \base64_decode( $padded, true );
		if ( $json === false ) {
			return [];
		}

		$claims = \json_decode( $json, true );

		return \is_array( $claims ) ? $claims : [];
	}

	/**
	 * Coerces a JWT claim value into a short displayable string.
	 *
	 * @param string|int|float|bool|array<int|string, string|int|float|bool>|null $value The claim value.
	 *
	 * @return string The string representation.
	 */
	private function stringify_claim( $value ) {
		if ( \is_scalar( $value ) ) {
			return (string) $value;
		}

		return (string) WPSEO_Utils::format_json_encode( $value );
	}

	/**
	 * Returns the active issuer URL — i.e. the environment selected in Domain_Dropdown.
	 *
	 * @return string The active issuer URL.
	 */
	private function get_active_issuer() {
		return $this->domain_dropdown->get_active_domain();
	}

	/**
	 * Returns whether the given issuer is the production default. When true, the
	 * OAuth filters do not fire — production credentials baked into Yoast SEO are
	 * already correct.
	 *
	 * @param string $issuer The issuer URL.
	 *
	 * @return bool True when the issuer is the production default.
	 */
	private function is_production( $issuer ) {
		return $issuer === Domain_Dropdown::DEFAULT_DOMAIN;
	}

	/**
	 * Returns whether the OAuth filters should be registered: only when the active
	 * environment is non-production AND credentials are stored for it.
	 *
	 * @return bool True when overrides should apply.
	 */
	private function should_apply_overrides() {
		$issuer = $this->get_active_issuer();
		if ( $this->is_production( $issuer ) ) {
			return false;
		}

		$credentials = $this->get_active_credentials();

		return ( ! empty( $credentials['software_statement'] ) && ! empty( $credentials['initial_access_token'] ) );
	}

	/**
	 * Returns the credential record stored for the active issuer.
	 *
	 * @return array<string, string|int|float|bool> The credential record, or [] when none is stored.
	 */
	private function get_active_credentials() {
		$issuer = $this->get_active_issuer();

		$store = $this->option->get( 'myyoast_oauth_credentials' );
		if ( ! \is_array( $store ) || ! isset( $store[ $issuer ] ) || ! \is_array( $store[ $issuer ] ) ) {
			return [];
		}

		return $store[ $issuer ];
	}

	/**
	 * Returns whether a PAT is stored for the active issuer.
	 *
	 * @return bool True when a non-empty PAT is stored.
	 */
	private function has_active_pat() {
		$credentials = $this->get_active_credentials();

		return ! empty( $credentials['pat'] );
	}

	/**
	 * Persists a single credential field for the given issuer, leaving other fields untouched.
	 *
	 * @param string                $issuer The issuer URL.
	 * @param string                $field  The credential field name.
	 * @param string|int|float|bool $value  The credential value.
	 *
	 * @return void
	 */
	private function store_credential_field( $issuer, $field, $value ) {
		$store = $this->option->get( 'myyoast_oauth_credentials' );
		if ( ! \is_array( $store ) ) {
			$store = [];
		}

		$record           = ( isset( $store[ $issuer ] ) && \is_array( $store[ $issuer ] ) ) ? $store[ $issuer ] : [];
		$record[ $field ] = $value;
		$store[ $issuer ] = $record;

		$this->option->set( 'myyoast_oauth_credentials', $store );
	}

	/**
	 * Queues a notification on the test helper admin page.
	 *
	 * @param string $message The notification message.
	 * @param string $type    The notification type (info, success, error).
	 *
	 * @return void
	 */
	private function add_notification( $message, $type = 'info' ) {
		\do_action( 'Yoast\WP\Test_Helper\notification', new Notification( $message, $type ) );
	}

	/**
	 * Redirects back to the test helper admin page.
	 *
	 * @return void
	 */
	private function redirect() {
		\wp_safe_redirect( \self_admin_url( 'tools.php?page=' . \apply_filters( 'Yoast\WP\Test_Helper\admin_page', '' ) ) );
	}
}
