<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

final class Superpack {
	const VERSION = '0.1.1';

	protected static $instance = null;
	protected static $settings = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
	}

	public static function codename( $key = null ) {
		$codename = sanitize_title_with_dashes( get_called_class() );

		return ! is_null( $key ) ? $codename . '-' . sanitize_title_with_dashes( $key ) : $codename;
	}

	public static function version() {
		return self::VERSION;
	}


	/**
	 * Settings
	 *
	 * @return object
	 */
	public static function get_settings() {
		if ( is_null( self::$settings ) ) {
			$settings = $defaults = array(
				'comment_avatar_size'          => '64',
				'enqueue_shortcodes_css'       => true,
				'enqueue_widgets_css'          => true,
				'image_size_about'             => 'medium',
				'image_size_posts'             => 'thumbnail',
				'social_icons'                 => false,
				'social_icons_container_class' => 'superpack__menu',
				'social_icons_class'           => 'superpack__social',
			);

			$_settings = get_theme_support( 'superpack' );

			if ( is_array( $_settings ) ) {
				if ( isset( $_settings[0] ) && is_array( $_settings[0] ) ) {

					foreach ( $_settings[0] as $key => $value ) {
						switch ( $key ) {
							case 'comment_avatar_size' :
								if ( is_numeric( $value ) ) {
									$settings[ $key ] = (int) $value;
								}

								break;

							case 'enqueue_shortcodes_css' :
								if ( is_bool( $value ) ) {
									$settings[ $key ] = $value;
								}

								break;

							case 'enqueue_widgets_css' :
								if ( is_bool( $value ) ) {
									$settings[ $key ] = $value;
								}

								break;

							case 'image_size_about' :
								if ( has_image_size( $value ) ) {
									$settings[ $key ] = $value;
								}

								break;

							case 'image_size_posts' :
								if ( has_image_size( $value ) ) {
									$settings[ $key ] = $value;
								}

								break;

							case 'social_icons' :
								if ( is_bool( $value ) ) {
									$settings[ $key ] = $value;
								}

								break;

							case 'social_icons_class' :
								if ( is_string( $value ) ) {
									$value = preg_split( '#\s+#', $value );
								} elseif ( ! is_array( $value ) ) {
									$value = array();
								}

								$value = array_merge( array( 'superpack__social' ), $value );

								$settings[ $key ] = trim( join( ' ', array_map( 'sanitize_html_class', array_unique( $value ) ) ) );

								break;

							case 'social_icons_container_class' :
								if ( is_string( $value ) ) {
									$value = preg_split( '#\s+#', $value );
								} elseif ( ! is_array( $value ) ) {
									$value = array();
								}

								$value = array_merge( array( 'superpack__icons-menu' ), $value );

								$settings[ $key ] = trim( join( ' ', array_map( 'sanitize_html_class', array_unique( $value ) ) ) );

								break;

							default:
								continue;

								break;
						}
					}

				}
			}

			$settings = wp_parse_args( $settings, $defaults );

			// Store settings in class static to avoid reparsing
			self::$settings = apply_filters( 'supperpack_settings', $settings );
		}

		return (object) self::$settings;
	}

	//
	// Helpers
	//

	/**
	 * Helper to prepare name for generating cache.
	 *
	 * @param $group
	 * @param $key
	 *
	 * @return string
	 */
	public static function cache_key( $group, $key ) {
		return substr( self::codename(), 0, 6 ) . '-' . substr( $group, 0, 4 ) . '-' . md5( self::version() . $key );
	}

	/**
	 * Helper to prepare group name for cache.
	 *
	 * @return string
	 */
	public static function cache_group() {
		return self::codename() . '_' . self::version();
	}

	/**
	 * Wrapper function for Schema less URL.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public static function protocol( $url ) {
		$url = str_replace( array( 'http://', 'https://' ), '//', $url );

		return esc_url( $url );
	}

	//
	// Conditional Helpers
	//

	/**
	 * Detect Browser/OS
	 *
	 * @param $query
	 *
	 * @return bool
	 */
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


/**
 * Returns the main instance of SuperPack
 */
function Superpack() {
	return Superpack::instance();
}

Superpack();
