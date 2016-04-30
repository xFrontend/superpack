<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Shortcode_Row extends Superpack_Shortcode {

	public static function callback( $attr, $content = null, $shortcode_tag ) {

		$attr = (object) shortcode_atts( array(
			'id'    => '',
			'class' => '',
		), $attr, $shortcode_tag );

		$attr->class = preg_split( '#\s+#', $attr->class );

		$classes = array( 'superpack__row', 'row' );
		$classes = array_merge( $classes, $attr->class );

		$id    = ( $attr->id != '' ) ? 'id="' . $attr->id . '"' : '';
		$class = join( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) );
		$class = trim( $class );

		return sprintf(
			'<div %s class="%s">%s</div>',
			esc_attr( $id ),
			esc_attr( $class ),
			parent::callback_content( $content )
		);
	}

}
