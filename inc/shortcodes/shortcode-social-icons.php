<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Shortcode_Social_Icons extends Superpack_Shortcode {

	public static function callback( $attr, $content = null, $shortcode_tag ) {

		$attr = (object) shortcode_atts( array(
			'menu'  => '',
			'id'    => '',
			'class' => '',
		), $attr, $shortcode_tag );

		$attr->class     = preg_split( '#\s+#', $attr->class );
		$container_class = preg_split( '#\s+#', Superpack()->settings()->social_icons['container_class'] );

		$classes = array_merge( $container_class, $attr->class );

		$attr->class = trim( join( ' ', array_map( 'sanitize_html_class', array_unique( $classes ) ) ) );

		// Get menu
		$menu = ! empty( $attr->menu ) ? wp_get_nav_menu_object( $attr->menu ) : false;

		if ( ! $menu ) {
			return '';
		}

		$menu_args = array(
			'fallback_cb'     => '',
			'container'       => 'nav',
			'container_id'    => $attr->id,
			'container_class' => $attr->class,
			'depth'           => 1,
			'menu'            => $menu,
			'menu_class'      => Superpack()->settings()->social_icons['menu_class'],
			'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
			'echo'            => false
		);

		/**
		 * Hide menu texts if the active theme gave us green light.
		 */
		if ( Superpack()->settings()->social_icons['icons'] ) {
			$menu_args['link_before'] = '<span class="screen-reader-text">';
			$menu_args['link_after']  = '</span>';
		}

		return wp_nav_menu( apply_filters( 'superpack_social_icons_menu_args', $menu_args, $menu, (array) $attr ) );
	}

}
