<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

//
// Sanitize Helper

class Superpack_Sanitize {
	protected static $instance = null;

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
	}

	public static function css_js( $code ) {
		//
		// Code snippet taken from Jetpack_Custom_CSS module

		$code = preg_replace( '/\\\\([0-9a-fA-F]{4})/', '\\\\\\\\$1', $prev = $code );

		if ( $code != $prev ) {
			return ''; // 'preg_replace found stuff';
		}

		// Some people put weird stuff in their CSS, KSES tends to be greedy
		$code = str_replace( '<=', '&lt;=', $code );
		// Why KSES instead of strip_tags?  Who knows?
		$code = wp_kses_split( $prev = $code, array(), array() );
		$code = str_replace( '&gt;', '>', $code ); // kses replaces lone '>' with &gt;
		// Why both KSES and strip_tags?  Because we just added some '>'.
		$code = strip_tags( $code );

		if ( $code != $prev ) {
			return ''; // 'kses found stuff';
		}

		return $code;
	}

	public static function embed( $html ) {
		// WP's default allowed tags
		global $allowedtags;

		$white_list = array(
			// allow iframe only in this instance
			'iframe' => array(
				// attributes to allow
				'src'             => array(),
				'width'           => array(),
				'height'          => array(),
				'frameborder'     => array(),
				'allowFullScreen' => array(),
			)
		);

		$allowed_html = array_merge( $allowedtags, $white_list );

		return wp_kses( $html, $allowed_html );
	}

	public static function html( $input ) {
		return wp_kses_post( balanceTags( $input, true ) );
	}


	//
	// Wrapper for Checkbox (Boolean), Email, URL

	public static function checkbox( $value ) {
		$value = (int) $value;

		return ( 1 === $value || true === $value ) ? 1 : 0;
	}

	public static function checkbox_js( $value ) {
		$value = (int) $value;

		return ( 1 === $value || true === $value ) ? true : false;
	}

	public static function email( $email ) {
		$email = trim( $email );

		return ! empty( $email ) ? sanitize_email( $email ) : '';
	}

	public static function email_link( $email ) {
		$email = self::email( $email );

		return ! empty( $email ) ? antispambot( 'mailto:' . $email ) : '';
	}

	public static function url( $url ) {
		$url = trim( $url );

		return ! empty( $url ) ? esc_url( $url ) : '';
	}

	public static function url_raw( $url ) {
		$url = trim( $url );

		return ! empty( $url ) ? esc_url_raw( $url ) : '';
	}


	//
	// Color

	//Sanitizes a hex color.
	public static function hex_color( $color ) {
		if ( '' === $color ) {
			return '';
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}

		return null;
	}

	// Sanitizes a hex color without a hash. Use hex_color() when possible.
	public static function hex_color_no_hash( $color ) {
		$color = ltrim( $color, '#' );

		if ( '' === $color ) {
			return '';
		}

		return self::hex_color( '#' . $color ) ? $color : null;
	}

	// Ensures that any hex color is properly hashed.
	public static function maybe_hash_hex_color( $color ) {
		if ( $unhashed = self::hex_color_no_hash( $color ) ) {
			return '#' . $unhashed;
		}

		return $color;
	}
}
