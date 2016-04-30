<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Shortcode_Column extends Superpack_Shortcode {

	public static function callback( $attr, $content = null, $shortcode_tag ) {

		$attr = (object) shortcode_atts( array(
			'type'  => '',
			'last'  => false,
			'id'    => '',
			'class' => '',
		), $attr, $shortcode_tag );

		$attr->class = preg_split( '#\s+#', $attr->class );
		$attr->last  = (bool) $attr->last;

		$classes = array( 'column' );

		$type = 'full';

		switch ( $attr->type ) {
			case '1/2':
			case 'one-half':
				$type = 'one-half';
				break;

			case '1/3':
			case 'one-third':
				$type = 'one-third';
				break;
			case '2/3':
			case 'two-thirds':
				$type = 'two-thirds';
				break;

			case '1/4':
			case 'one-fourth' :
				$type = 'one-fourth';
				break;
			case '2/4':
			case 'two-fourth' :
				$type = 'two-fourths';
				break;
			case '3/4':
			case 'three-fourths':
				$type = 'three-fourths';
				break;

			case '1/5'       :
			case 'one-fifth' :
				$type = 'one-fifth';
				break;
			case '2/5'        :
			case 'two-fifths' :
				$type = 'two-fifths';
				break;
			case '3/5':
			case 'three-fifths':
				$type = 'three-fifths';
				break;
			case '4/5':
			case 'four-fifths':
				$type = 'four-fifths';
				break;
		}

		$classes[] = $type;

		$classes = array_merge( $classes, $attr->class );

		if ( $attr->last ) {
			$classes[] = 'last';
		}

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
