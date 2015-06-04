<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

final class Superpack {
	const CODENAME = 'SuperPack';
	const VERSION  = '0.1.0';

	protected static $instance = null;

	public static $format = 'superpack_format_';

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {}

	public static function version() {
		return self::VERSION;
	}


	//
	// Helper

	public static function codename( $key = null ) {
		$codename = sanitize_title_with_dashes( self::CODENAME );

		return ! is_null( $key ) ? $codename . '-' . sanitize_title_with_dashes( $key ) : $codename;
	}

	public static function cache_key( $group, $key ) {
		return substr( self::codename(), 0, 6 ) . '-' . substr( $group, 0, 4 ) . '-' . md5( self::version() . $key );
	}

	public static function cache_group() {
		return self::codename() . '_' . Superpack::VERSION;
	}

	public static function protocol( $url ) {
		$url = str_replace( array( 'http://', 'https://' ), '//', $url );

		return esc_url( $url );
	}


	// Conditional Helper

	public static function is_current_agent( $query ) {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		preg_match( "/iPhone|iPad|iPod|Android|webOS|Safari|Chrome|Firefox|Opera|MSIE/", $_SERVER['HTTP_USER_AGENT'], $matches );

		$_agent = null;
		$agent  = current( $matches );

		switch ( $agent ) {
			case 'iPhone':
			case 'iPad':
			case 'iPod':
				$_agent = 'iOS';
				break;
			case 'MSIE':
				$_agent = 'IE';
				break;
		}

		switch ( $agent ) {
			case 'iPhone':
			case 'iPad':
			case 'iPod':
			case 'Android':
			case 'webOS':
			case 'Safari':
			case 'Chrome':
			case 'Firefox':
			case 'Opera':
				break;
		}

		return ( strtolower( $query ) == strtolower( $_agent ) || strtolower( $query ) == strtolower( $agent ) );
	}
}


//
// Returns the main instance of Superpack

function Superpack() {
	return Superpack::instance();
}

Superpack();
