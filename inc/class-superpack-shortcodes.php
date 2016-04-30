<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

/**
 * Manages Superpack shortcodes
 */
class Superpack_Shortcodes {

	private static $instance;

	private $shortcode_classes = array(
		'Superpack_Shortcode_Code',
		'Superpack_Shortcode_Dropcap',
		'Superpack_Shortcode_Blockquote',
		'Superpack_Shortcode_Row',
		'Superpack_Shortcode_Column',
		'Superpack_Shortcode_Social_Icons',
	);
	private $registered_shortcode_classes = array();
	private $registered_shortcodes = array();

	public static function instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;

			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}

		return self::$instance;
	}

	/**
	 * Autoload any of our shortcode classes
	 */
	public function autoload_shortcode_classes( $class ) {
		if ( 0 !== stripos( $class, 'Superpack_Shortcode' ) ) {
			return;
		}

		$file_name = str_replace( 'superpack_', '', strtolower( $class ) );
		$file_name = str_replace( '_', '-', strtolower( $file_name . '.php' ) );
		$file      = SUPERPACK__PLUGIN_DIR . '/inc/shortcodes/' . $file_name;

		if ( file_exists( $file ) ) {
			require $file;
		}
	}

	/**
	 * Set up shortcode actions
	 */
	private function setup_actions() {
		spl_autoload_register( array( $this, 'autoload_shortcode_classes' ) );

		add_action( 'init', array( $this, 'action_init_register_shortcodes' ) );
	}

	/**
	 * Set up shortcode filters
	 */
	private function setup_filters() {
		add_filter( 'pre_kses', array( $this, 'filter_pre_kses' ) );
	}

	/**
	 * Register all of the shortcodes
	 */
	public function action_init_register_shortcodes() {

		$this->registered_shortcode_classes = apply_filters( 'superpack_shortcode_classes', $this->shortcode_classes );

		foreach ( $this->registered_shortcode_classes as $class ) {
			$shortcode_tag = $class::get_shortcode_tag();

			$this->registered_shortcodes[ $shortcode_tag ] = $class;

			remove_shortcode( $shortcode_tag );

			add_shortcode( $shortcode_tag, array( $this, 'do_shortcode_callback' ) );

			$class::setup_actions();
		}
	}


	/**
	 * Modify post content before kses is applied
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filter_pre_kses( $content ) {

		foreach ( $this->registered_shortcode_classes as $shortcode_class ) {
			$content = $shortcode_class::reversal( $content );
		}

		return $content;
	}

	/**
	 * Do the shortcode callback
	 *
	 * @param $attr
	 * @param string $content
	 * @param $shortcode_tag
	 *
	 * @return string
	 */
	public function do_shortcode_callback( $attr, $content = '', $shortcode_tag ) {

		if ( empty( $this->registered_shortcodes[ $shortcode_tag ] ) ) {
			return '';
		}

		$class = $this->registered_shortcodes[ $shortcode_tag ];

		return $class::callback( $attr, $content, $shortcode_tag );
	}
}
