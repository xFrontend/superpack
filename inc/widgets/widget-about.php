<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Widget_About extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'superpack-widget-about',
			esc_html_x( 'About (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'superpack__widget superpack__widget-about',
				'description' => esc_html_x( 'Display your bio/site info on your site.', 'admin', 'superpack' ),
			),
			array(
				'height' => 200,
				'width'  => 400,
			)
		);

		add_filter( 'superpack_about_text', 'do_shortcode' );
		add_action( 'sidebar_admin_setup', array( &$this, 'enqueue' ) );
		add_action( 'switch_theme', array( &$this, 'flush_cache' ) );
	}

	public function widget( $args, $instance ) {
		$cache = wp_cache_get( $this->id_base, 'superpack__widget' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) && ! is_customize_preview() ) {
			echo $cache[ $args['widget_id'] ];

			return;
		}

		$title         = $this->get_title( $instance );
		$description   = $this->get_description( $instance );
		$attachment_id = $this->get_attachment_id( $instance );

		if ( 0 < $attachment_id ) {
			$thumbs = wp_get_attachment_image(
				$attachment_id,
				apply_filters( 'superpack_widget_about_image_size', Superpack()->settings()->image_size_about, $attachment_id )
			);
		}

		$content = $args['before_widget'];

		if ( $title ) {
			$content .= $args['before_title'] . $title . $args['after_title'];
		} else {
			$content .= '<h3 class="screen-reader-text">' . esc_html__( 'About', 'superpack' ) . '</h3>';
		}

		if ( ! empty( $thumbs ) || ! empty( $description ) ) {
			$content .= '<div class="superpack__about">';

			if ( ! empty( $thumbs ) ) {
				$content .= '<div class="superpack__thumbnail">';
				$content .= $thumbs;
				$content .= '</div>';
			};

			if ( ! empty( $description ) ) {
				$description = apply_filters( 'superpack_about_text', $description, $instance );

				$content .= '<div class="content description">';
				$content .= ! empty( $instance['filter'] ) ? wpautop( $description ) : '<p>' . $description . '</p>';
				$content .= '</div>';
			}

			$content .= '</div>';
		} else {
			$content .= '<div class="content description">';
			$content .= '<p>' . esc_html__( 'Add an image or description to hide this message.', 'superpack' ) . '</p>';
			$content .= '</div>';
		}

		$content .= $args['after_widget'];

		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->id_base, $cache, 'superpack__widget' );

		echo $content;
	}

	public function flush_cache() {
		wp_cache_delete( $this->id_base, 'superpack__widget' );
	}

	public function form( $instance ) {
		$title         = $this->get_title( $instance );
		$attachment_id = $this->get_attachment_id( $instance );
		$description   = $this->get_description( $instance );

		$thumbs = null;

		if ( 0 < $attachment_id ) {
			$thumbs = wp_get_attachment_image_src(
				$attachment_id,
				apply_filters( 'superpack_widget_about_image_size', 'medium', $attachment_id )
			);
		}
		?>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html_x( 'Title:', 'admin', 'superpack' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>

		<div class="superpack__uploader superpack__uploader-about">
			<p>
				<button type="button" class="button superpack__media-select"
				        data-button-title="<?php echo esc_attr_x( 'Select an Image', 'admin', 'superpack' ) ?>"
				        data-button-text="<?php echo esc_attr_x( 'Insert to the Widget', 'admin', 'superpack' ) ?>"
				        data-filter="image"
				><?php echo esc_html_x( 'Select Image', 'admin', 'superpack' ); ?></button>

				<button type="button"
				        class="button superpack__media-clear<?php if ( ! isset( $thumbs[0] ) ): ?> hide-if-js<?php endif; ?>"><?php echo esc_html_x( 'Remove Image', 'admin', 'superpack' ); ?></button>
			</p>

			<div class="image-preview">
				<?php if ( isset( $thumbs[0] ) ) {
					echo '<img src="' . esc_url( $thumbs[0] ) . '" alt="">';
				}; ?>
			</div>

			<input type="hidden" id="<?php echo esc_attr( $this->get_field_id( 'attachment_id' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'attachment_id' ) ); ?>"
			       value="<?php echo esc_attr( $attachment_id ); ?>">
		</div>

		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php echo esc_html_x( 'Content:', 'admin', 'superpack' ); ?></label>
			<textarea class="widefat" rows="8" cols="20"
			          id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"
			          name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'filter' ) ); ?>" <?php checked( isset( $instance['filter'] ) ? $instance['filter'] : 0 ); ?> >
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"><?php echo esc_html_x( 'Automatically add paragraphs', 'admin', 'superpack' ); ?></label>
		</p>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']         = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['attachment_id'] = absint( $new_instance['attachment_id'] );

		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['description'] = $new_instance['description'];
		} else {
			$instance['description'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['description'] ) ) ); // wp_filter_post_kses() expects slashed
		}

		$instance['filter'] = isset( $new_instance['filter'] );

		$this->flush_cache();

		return $instance;
	}

	public function enqueue() {
		/**
		 * CSS
		 */
		wp_add_inline_style(
			'wp-admin', '

			/* Superpack Widget-About */
			.superpack__uploader .image-preview img {
			  max-width: 100%; }

			.superpack__uploader-about .image-preview img {
			  width: 100%; }

			.superpack__uploader-gallery .image-preview img {
			  margin: 0 8px 10px 0; }
        ' );

		/**
		 * JavaScript
		 */
		$ext_js = SUPERPACK__CSSJS_SUFFIX . '.js';

		wp_enqueue_media();

		wp_enqueue_script(
			Superpack()->codename( 'functions-js' ),
			Superpack()->protocol( SUPERPACK__ASSETS_URI . '/js/functions' . $ext_js ),
			array( 'jquery', 'media-upload', 'media-views' ),
			Superpack()->version(),
			true
		);

	}

	public function get_attachment_id( $instance ) {
		return ( ! empty( $instance['attachment_id'] ) ) ? absint( $instance['attachment_id'] ) : 0;
	}

	public function get_description( $instance ) {
		return ( ! empty( $instance['description'] ) ) ? $instance['description'] : '';
	}

	public function get_title( $instance ) {
		$title = ( ! empty( $instance['title'] ) ) ? strip_tags( $instance['title'] ) : '';

		return apply_filters( 'widget_title', $title, $instance, $this->id_base );
	}
}
