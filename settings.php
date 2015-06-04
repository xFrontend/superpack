<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


require SUPERPACK__PLUGIN_DIR . '/class-superpack.php';
require SUPERPACK__PLUGIN_DIR . '/class-superpack-sanitize.php';


//
//-| Register Widgets |---------------|

require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-about.php';
require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-comments.php';
require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-instagram.php';
require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-posts.php';
require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-tags.php';


//
//-| Stylesheets |--------------------|

add_action( 'wp_enqueue_scripts', 'superpack_enqueue' );

function superpack_enqueue() {
	$ext_css = SUPERPACK__CSSJS_SUFFIX . '.css';

	wp_register_style(
		Superpack()->codename(),
		Superpack()->protocol( SUPERPACK__ASSETS_URI . '/css/' . Superpack()->codename() . $ext_css ),
		array(),
		Superpack()->version()
	);

	wp_enqueue_style( Superpack()->codename() );
}