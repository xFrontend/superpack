<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Shortcode_Blockquote extends Superpack_Shortcode {

	public static function callback( $attr, $content = null, $shortcode_tag ) {

		$attr = (object) shortcode_atts( array(
			'cite'  => '',
			'type'  => '',
			'id'    => '',
			'class' => '',
		), $attr, $shortcode_tag );

		$attr->class = preg_split( '#\s+#', $attr->class );

		$classes = array();

		if ( 'right' === $attr->type ) {
			$classes[] = 'superpack__pull-right';
		} elseif ( 'left' === $attr->type ) {
			$classes[] = 'superpack__pull-left';
		}

		$classes = array_merge( $classes, $attr->class );

		$id    = ( $attr->id != '' ) ? 'id="' . $attr->id . '"' : '';
		$class = join( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) );
		$class = trim( $class );

		$cite = ! empty( $attr->cite ) ? '<cite class="superpack__cite">' . esc_html( $attr->cite ) . '</cite>' : '';

		return sprintf(
			'<blockquote %s class="%s">%s%s</blockquote>',
			esc_attr( $id ),
			esc_attr( $class ),
			parent::callback_content( $content ),
			$cite
		);
	}

}
