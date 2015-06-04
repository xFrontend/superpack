<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

add_action( 'widgets_init', 'superpack_register_widget_about' );

function superpack_register_widget_about() {
	register_widget( 'Superpack_Widget_About' );
}

class Superpack_Widget_About extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'sp-widget-about',
			_x( 'About (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'sp-widget sp-widget-about',
				'description' => _x( 'Display your bio/site info on your site.', 'admin', 'superpack' ),
			),
			array(
				'height' => 200,
				'width'  => 400,
			)
		);

		add_action( 'sidebar_admin_setup', array( &$this, 'enqueue' ) );
		add_action( 'switch_theme', array( &$this, 'flush_cache' ) );
	}

	public function widget( $args, $instance ) {
		$cache = wp_cache_get( $this->id_base, 'sp-widget' );

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
				apply_filters( 'superpack_widget_about_image_size', 'medium', $attachment_id )
			);
		}

		$content = $args['before_widget'];

		if ( $title ) {
			$content .= $args['before_title'] . $title . $args['after_title'];
		} else {
			$content .= '<h3 class="screen-reader-text">' . __( 'About', 'superpack' ) . '</h3>';
		}

		if ( ! empty( $thumbs ) || ! empty( $description ) ) {
			$content .= '<div class="sp-about">';

			if ( ! empty( $thumbs ) ) {
				$content .= '<div class="sp-media-container">';
					$content .= apply_filters( 'superpack_widget_about_image_html', $thumbs, $attachment_id );
				$content .= '</div>';
			};

			if ( ! empty( $description ) ) {
				$content .= '<div class="content description">';
				$content .= ! empty( $instance['filter'] ) ? wpautop( $description ) : '<p>' . $description . '</p>';
				$content .= '</div>';
			}

			$content .= '</div>';
		} else {
			$content .= '<div class="content description">';
			$content .= '<p>' . __( 'Add an image or description to hide this message.', 'superpack' ) . '</p>';
			$content .= '</div>';
		}

		$content .= $args['after_widget'];

		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->id_base, $cache, 'sp-widget' );

		echo $content;
	}

	public function flush_cache() {
		wp_cache_delete( $this->id_base, 'sp-widget' );
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
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _ex( 'Title:', 'admin', 'superpack' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>

		<div class="uploader uploader-about">
			<p>
				<button type="button" class="button sp-media__select"
				        data-button-title="<?php echo esc_attr_x( 'Select an Image', 'admin', 'superpack' ) ?>"
				        data-button-text="<?php echo esc_attr_x( 'Insert into Widget', 'admin', 'superpack' ) ?>"
				        data-filter="image"
					><?php _ex( 'Select Image', 'admin', 'superpack' ); ?></button>

				<button type="button"
				        class="sp-button sp-media__clear<?php if ( ! isset( $thumbs[0] ) ): ?> hide-if-js<?php endif; ?>"><?php _ex( 'Remove Image', 'admin', 'superpack' ); ?></button>
			</p>

			<div class="image-preview">
				<?php if ( isset( $thumbs[0] ) ) {
					echo '<img src="' . esc_url( $thumbs[0] ) . '">';
				}; ?>
			</div>

			<input type="hidden" id="<?php echo esc_attr( $this->get_field_id( 'attachment_id' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'attachment_id' ) ); ?>"
			       value="<?php echo esc_attr( $attachment_id ); ?>">
		</div>

		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php _ex( 'Content:', 'admin', 'superpack' ); ?></label>
			<textarea class="widefat" rows="8" cols="20"
			          id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"
			          name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'filter' ) ); ?>" <?php checked( isset( $instance['filter'] ) ? $instance['filter'] : 0 ); ?> >
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"><?php _ex( 'Automatically add paragraphs', 'admin', 'superpack' ); ?></label>
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
		$ext_css = SUPERPACK__CSSJS_SUFFIX . '.css';
		$ext_js  = SUPERPACK__CSSJS_SUFFIX . '.js';

		wp_register_style(
			Superpack()->codename( 'common-admin-ui-css' ),
			Superpack()->protocol( SUPERPACK__ASSETS_URI . '/css/common-admin' . $ext_css ),
			array(),
			Superpack()->version()
		);

		wp_enqueue_style( Superpack()->codename( 'common-admin-ui-css' ) );

		wp_register_script(
			Superpack()->codename( 'widgets-admin-js' ),
			Superpack()->protocol( SUPERPACK__ASSETS_URI . '/js/widgets-admin' . $ext_js ),
			array( 'jquery', 'media-upload', 'media-views' ),
			Superpack()->version(),
			true
		);

		wp_enqueue_media();
		wp_enqueue_script( Superpack()->codename( 'widgets-admin-js' ) );
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
