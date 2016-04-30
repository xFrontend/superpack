<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Shortcode_Code extends Superpack_Shortcode {

	public static function callback( $attr, $content = null, $shortcode_tag ) {

		$attr = (object) shortcode_atts( array(
			'id'    => '',
			'class' => '',
		), $attr, $shortcode_tag );

		$attr->class = preg_split( '#\s+#', $attr->class );

		$classes = array( 'code' );

		$classes = array_merge( $classes, $attr->class );

		$id    = ( $attr->id != '' ) ? 'id="' . $attr->id . '"' : '';
		$class = join( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) );
		$class = trim( $class );

		$array = array(
			'<p>['       => '[',
			']</p>'      => ']',
			'<br /></p>' => '</p>',
			']<br />'    => ']'
		);

		$content = strtr( $content, $array );

		return sprintf(
			'<pre %s class="%s"><code>%s</code></pre>',
			esc_attr( $id ),
			esc_attr( $class ),
			$content
		);
	}

}
