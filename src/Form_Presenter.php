<?php
/**
 * HTML Form renderer.
 *
 * @package Yoast\Test_Helper
 */

namespace Yoast\Test_Helper;

/**
 * Renders the generic form code.
 */
class Form_Presenter {
	/**
	 * Retrieves the HTML for a form.
	 *
	 * @param string $title       The title for the form.
	 * @param string $nonce_field The name of the nonce field.
	 * @param string $fields      Fields to render in the form.
	 * @param bool   $submit      Show the submit field or not.
	 *
	 * @return string The HTML to render the form.
	 */
	public static function get_html( $title, $nonce_field, $fields, $submit = true ) {
		$output  = '<h2>' . esc_html( $title ) . '</h2>';
		$output .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= wp_nonce_field( $nonce_field, '_wpnonce', true, false );
		$output .= '<input type="hidden" name="action" value="' . esc_attr( $nonce_field ) . '">';

		$output .= $fields;

		if ( $submit ) {
			$output .= '<button class="button" type="submit">Save</button>';
		}

		$output .= '</form>';

		return $output;
	}

	/**
	 * Build a checkbox element.
	 *
	 * @param string $option  The option to make a checkbox for.
	 * @param string $label   The label for the checkbox.
	 * @param bool   $checked If the checkbox should be checked or not.
	 *
	 * @return string The checkbox & label HTML.
	 */
	public static function create_checkbox( $option, $label, $checked = false ) {
		$checked_html = checked( $checked, true, false );
		$output       = sprintf( '<input type="checkbox" ' . $checked_html . ' name="%1$s" id="%1$s"/>', $option );
		$output      .= sprintf( '<label for="%1$s">%2$s</label><br/>', $option, $label );

		return $output;
	}
}
