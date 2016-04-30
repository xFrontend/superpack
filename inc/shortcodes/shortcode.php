<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

/**
 * Base class for SuperPack shortcodes to extend
 * Ensures each shortcode implements a consistent pattern
 */
abstract class Superpack_Shortcode {

	/**
	 * Get the "tag" used for the shortcode. This will be stored in post_content
	 *
	 * @return string
	 */
	public static function get_shortcode_tag() {
		$shortcode_tag = str_replace( 'Superpack_Shortcode_', '', get_called_class() );
		$shortcode_tag = strtolower( str_replace( '_', '-', $shortcode_tag ) );

		return apply_filters( 'superpack_shortcode_tag', $shortcode_tag, get_called_class() );
	}

	/**
	 * Allow subclasses to register their own action
	 * Fires after the shortcode has been registered on init
	 *
	 * @return null
	 */
	public static function setup_actions() {
		// No base actions are necessary
	}

	/**
	 * Turn embed code into a proper shortcode
	 *
	 * @param string $content
	 *
	 * @return string $content
	 */
	public static function reversal( $content ) {
		return $content;
	}

	/**
	 * Render the shortcode. Remember to always return, not echo
	 *
	 * @param array $attr Shortcode attributes
	 * @param string $content Any inner content for the shortcode (optional)
	 * @param $shortcode_tag
	 *
	 * @return string
	 */
	public static function callback( $attr, $content = null, $shortcode_tag ) {
		return '';
	}

	/**
	 * Callback to cleanup and do_shortcode on content
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public static function callback_content( $content ) {
		$array = array(
			'<p>['       => '[',
			']</p>'      => ']',
			'<br /></p>' => '</p>',
			']<br />'    => ']'
		);

		$content = shortcode_unautop( balanceTags( trim( $content ), true ) );
		$content = strtr( $content, $array );

		return do_shortcode( $content );
	}

}
