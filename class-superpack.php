<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


final class Superpack {
	const VERSION = '0.3.1';

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

	public function codename( $key = null ) {
		$codename = sanitize_title_with_dashes( get_called_class() );

		return ! is_null( $key ) ? $codename . '-' . sanitize_title_with_dashes( $key ) : $codename;
	}

	public function version() {
		return self::VERSION;
	}

	/**
	 * Returns the Settings.
	 *
	 * @return object
	 */
	public function settings() {
		if ( is_null( self::$settings ) ) {
			$settings = $defaults = array(
				'comment_avatar_size'    => '64',
				'enqueue_shortcodes_css' => true,
				'enqueue_widgets_css'    => true,
				'image_size_about'       => 'medium',
				'image_size_posts'       => 'thumbnail',
				'social_icons'           => array(
					'container_class' => 'superpack__menu-container',
					'menu_class'      => 'superpack__social',
					'icons'           => false,
				),
				'contact_fields'         => array(
					'container_class' => 'suerpack__user-links',
					'action_hooks'    => 'superpack_user_links',
					'enable'          => true,
				),
			);

			$supports = get_theme_support( 'superpack' );

			if ( is_array( $supports ) ) {
				if ( isset( $supports[0] ) && is_array( $supports[0] ) ) {

					foreach ( $supports[0] as $key => $value ) {
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

								if ( false === $value ) {
									/**
									 * Bail early.
									 */
									$settings[ $key ] = array(
										'icons' => false,
									);

									break;
								}

								/**
								 * Parse the settings.
								 */
								$menu_class      = isset( $value['menu_class'] ) ? $value['menu_class'] : '';
								$container_class = isset( $value['container_class'] ) ? $value['container_class'] : '';

								/* Back Compatibility - Check settings for < v0.3 */
								if ( isset( $supports[0]['social_icons_class'] ) ) {
									$menu_class = $supports[0]['social_icons_class'];
								}
								if ( isset( $supports[0]['social_icons_container_class'] ) ) {
									$container_class = $supports[0]['social_icons_container_class'];
								}

								/**
								 * Finalize Settings
								 */
								$settings[ $key ] = array(
									'container_class' => self::get_html_classes( $container_class, $defaults['social_icons']['container_class'] ),
									'menu_class'      => self::get_html_classes( $menu_class, $defaults['social_icons']['menu_class'] ),
									'icons'           => isset( $value['icons'] ) ? $value['icons'] : true,
								);

								break;

							case 'contact_fields' :

								if ( false === $value ) {
									/**
									 * Bail early.
									 */
									$settings[ $key ] = array(
										'enable' => false,
									);

									break;
								}

								$container_class = isset( $value['container_class'] ) ? $value['container_class'] : array();
								$action_hooks    = isset( $value['action_hooks'] ) ? $value['action_hooks'] : $defaults['contact_fields']['action_hooks'];

								$settings[ $key ] = array(
									'container_class' => self::get_html_classes( $container_class, $defaults['contact_fields']['container_class'] ),
									'action_hooks'    => is_array( $action_hooks ) ? array_unique( $action_hooks ) : (array) $action_hooks,
									'fields'          => isset( $value['fields'] ) ? $value['fields'] : array(),
									'enable'          => isset( $value['enable'] ) ? $value['enable'] : true,
								);

								break;
						}
					}

				}
			}

			$settings = self::parse_args( $settings, $defaults );

			/**
			 * Store settings in class static to avoid multiple parsing
			 */
			self::$settings = (object) apply_filters( 'supperpack_settings', $settings );
		}

		return self::$settings;
	}

	/**
	 * Merge multidimensional arrays.
	 *
	 * @param $settings
	 * @param $defaults
	 *
	 * @return array
	 *
	 * @see wp_parse_args()
	 */
	public function parse_args( &$settings, $defaults = '' ) {
		$settings = (array) $settings;
		$defaults = (array) $defaults;
		$return   = $defaults;

		foreach ( $settings as $key => &$value ) {
			if ( is_array( $value ) && isset( $return[ $key ] ) ) {
				$return[ $key ] = self::parse_args( $value, $return[ $key ] );
			} else {
				$return[ $key ] = $value;
			}
		}

		return $return;
	}

	/**
	 * Checks and returns escaped HTML classes.
	 *
	 * @param $value
	 * @param array $default
	 *
	 * @return string
	 */
	public function get_html_classes( $value, $default = '' ) {

		if ( is_string( $value ) ) {
			$value = preg_split( '#\s+#', $value );
		} elseif ( ! is_array( $value ) ) {
			$value = array();
		}

		$value = array_merge( (array) $default, $value );

		return trim( join( ' ', array_map( 'sanitize_html_class', array_unique( $value ) ) ) );
	}

	/**
	 * Helper to prepare name for generating cache.
	 *
	 * @param $group
	 * @param $key
	 *
	 * @return string
	 */
	public function cache_key( $group, $key ) {
		return substr( self::codename(), 0, 6 ) . '-' . substr( $group, 0, 4 ) . '-' . md5( self::version() . $key );
	}

	/**
	 * Helper to prepare group name for cache.
	 *
	 * @return string
	 */
	public function cache_group() {
		return self::codename() . '_' . self::version();
	}

	/**
	 * Wrapper function for Schema less URL.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function protocol( $url ) {
		$url = str_replace( array( 'http://', 'https://' ), '//', $url );

		return esc_url( $url );
	}

	/**
	 * Detect Browser/OS
	 *
	 * @param $query
	 *
	 * @return bool
	 */
	public function current_agent( $query ) {
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
