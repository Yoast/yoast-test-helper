<?php

namespace Yoast\Test_Helper;

class Form_Presenter {
	public static function get_html( $title, $nonce_field, $fields, $submit = true ) {
		$output = '<h2>' . esc_html( $title ) . '</h2>';
		$output .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= wp_nonce_field( $nonce_field, '_wpnonce', true, false );
		$output .= '<input type="hidden" name="action" value="' . esc_attr( $nonce_field ) . '">';

		$output .= $fields;

		if ( $submit ) {
			$output .= '<br/><br/>';
			$output .= '<button class="button button-primary">Save</button>';
		}

		$output .= '</form>';

		return $output;
	}
}
