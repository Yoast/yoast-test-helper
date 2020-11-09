<?php

namespace Yoast\WP\Test_Helper;

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
		$field   = \esc_attr( $nonce_field );
		$output  = '<h2>' . \esc_html( $title ) . '</h2>';
		$output .= '<form action="' . \esc_url( \admin_url( 'admin-post.php' ) ) . '" method="POST">';
		$output .= \str_replace( 'id="_wpnonce"', '', \wp_nonce_field( $nonce_field, '_wpnonce', true, false ) );
		$output .= '<input type="hidden" name="action" value="' . $field . '">';

		$output .= $fields;

		if ( $submit ) {
			$output .= '<button id="' . $field . '_save" class="button" type="submit">' . \esc_html__( 'Save', 'yoast-test-helper' ) . '</button>';
		}

		$output .= '</form>';

		return $output;
	}

	/**
	 * Builds a checkbox element.
	 *
	 * @param string $option  The option to make a checkbox for.
	 * @param string $label   The label for the checkbox.
	 * @param bool   $checked If the checkbox should be checked or not.
	 *
	 * @return string The checkbox & label HTML.
	 */
	public static function create_checkbox( $option, $label, $checked = false ) {
		$checked_html = \checked( $checked, true, false );
		$output       = \sprintf( '<input type="checkbox" ' . $checked_html . ' name="%1$s" id="%1$s"/>', $option );
		$output      .= \sprintf( '<label for="%1$s">%2$s</label><br/>', $option, $label );

		return $output;
	}

	/**
	 * Builds a select element.
	 *
	 * @param string   $option   The option to make a checkbox for.
	 * @param string   $label    The label for the checkbox.
	 * @param string[] $options  The options of the select.
	 * @param bool     $selected The selected option.
	 *
	 * @return string The select & label HTML.
	 */
	public static function create_select( $option, $label, $options, $selected = false ) {
		$output  = \sprintf( '<label for="%1$s">%2$s</label>', $option, $label );
		$output .= \sprintf( '<select name="%1$s" id="%1$s">', $option );
		foreach ( $options as $value => $option_label ) {
			$selected_html = \selected( $selected === $value, true, false );
			$output       .= \sprintf( '<option ' . $selected_html . ' value="%1$s">%2$s</option>', $value, $option_label );
		}
		$output .= '</select><br/>';

		return $output;
	}
}
