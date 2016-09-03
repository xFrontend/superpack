<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


/**
 * Includes SuperPack core
 */
require SUPERPACK__PLUGIN_DIR . '/class-superpack.php';
require SUPERPACK__PLUGIN_DIR . '/inc/helpers.php';
require SUPERPACK__PLUGIN_DIR . '/inc/contact-fields.php';


/**
 * Registers SuperPack Shortcodes
 */
function superpack_register_shortcodes() {
	require SUPERPACK__PLUGIN_DIR . '/inc/class-superpack-shortcodes.php';

	Superpack_Shortcodes::instance();
}

add_action( 'after_setup_theme', 'superpack_register_shortcodes', 999 );


/**
 * Registers SuperPack Widgets
 */
function superpack_register_widgets() {

	require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-about.php';
	require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-comments.php';
	require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-instagram.php';
	require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-posts.php';
	require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-social-icons.php';
	require SUPERPACK__PLUGIN_DIR . '/inc/widgets/widget-tags.php';

	register_widget( 'Superpack_Widget_About' );
	register_widget( 'Superpack_Widget_Comments' );
	register_widget( 'Superpack_Widget_Instagram' );
	register_widget( 'Superpack_Widget_Posts' );
	register_widget( 'Superpack_Widget_Tags' );
	register_widget( 'Superpack_Widget_Social_Icons' );
}

add_action( 'widgets_init', 'superpack_register_widgets' );


/**
 * Enqueues SuperPack Styles
 */
function superpack_enqueue() {
	$ext_css = SUPERPACK__CSSJS_SUFFIX . '.css';

	/**
	 * Load Shortcodes CSS if the active theme tell us nothing about it.
	 * We've only added CSS for row/column (grid) for sanity.
	 */
	if ( Superpack()->settings()->enqueue_shortcodes_css ) {

		wp_enqueue_style(
			Superpack()->codename( 'shortcodes-style' ),
			Superpack()->protocol( SUPERPACK__ASSETS_URI . '/css/shortcodes' . $ext_css ),
			array(),
			Superpack()->version()
		);

	}

	/**
	 * Load Widgets CSS if the active theme isn't smart enough to tell us not to- ;)
	 */
	if ( Superpack()->settings()->enqueue_widgets_css ) {

		wp_enqueue_style(
			Superpack()->codename( 'widgets-style' ),
			Superpack()->protocol( SUPERPACK__ASSETS_URI . '/css/widgets' . $ext_css ),
			array(),
			Superpack()->version()
		);

	}

}

add_action( 'wp_enqueue_scripts', 'superpack_enqueue' );
