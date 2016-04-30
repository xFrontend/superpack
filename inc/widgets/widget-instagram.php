<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Widget_Instagram extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'superpack-widget-instagram',
			esc_html_x( 'Instagram (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'superpack__widget superpack__widget-instagram',
				'description' => esc_html_x( 'Display your latest Instagram photos on your site.', 'admin', 'superpack' ),
			)
		);

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

		$title    = $this->get_title( $instance );
		$username = $this->get_username( $instance );
		$columns  = $this->get_columns( $instance );
		$number   = $this->get_number( $instance );

		$content = $args['before_widget'];

		if ( $title ) {
			//--$title = '<a href="http://instagram.com/' . sanitize_title( $username ) . '" class="instagram-profile-link">' . $title . '</a>';
			$content .= $args['before_title'] . $title . $args['after_title'];
		} else {
			$content .= '<h3 class="screen-reader-text">' . esc_html__( 'Instagram Photos', 'superpack' ) . '</h3>';
		}

		$data = self::instagram_data( $username, $number );

		if ( ! is_wp_error( $data ) && ! empty( $data ) ) {
			$content .= '<div class="gallery gallery-columns-' . esc_attr( $columns ) . '" >';

			foreach ( $data as $item ) {
				if ( ! empty( $item['is_video'] ) ) {
					continue;
				}

				$image = $item['thumbnail'];

				$content .= '<figure class="gallery-item superpack__thumbnail">';
				$content .= '<a href="' . esc_url( $item['link'] ) . '" target="_blank"><img src="' . esc_url( $image ) . '" alt=""></a>';
				$content .= '</figure>';
			}

			$content .= '</div>';
		} elseif ( is_wp_error( $data ) ) {
			$content .= '<div class="superpack__instagram-container">';
			$content .= '<span class="error">' . $data->get_error_message() . '</span>';
			$content .= '</div>';
		} else {
			$content .= '<div class="superpack__instagram-container">';
			$content .= '<span class="error">' . esc_html__( 'Nothing to show.', 'superpack' ) . '</span>';
			$content .= '</div>';
		}

		$content .= $args['after_widget'];

		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->id_base, $cache, 'superpack__widget' );

		echo $content;
	}

	public function flush_cache( $instance ) {
		$username  = strtolower( $this->get_username( $instance ) );
		$cache_key = Superpack()->cache_key( 'h', 'instagram_data' . sanitize_title_with_dashes( $username ) );

		delete_transient( $cache_key );

		wp_cache_delete( $this->id_base, 'superpack__widget' );
	}

	public function form( $instance ) {
		$title    = $this->get_title( $instance );
		$username = $this->get_username( $instance );
		$columns  = $this->get_columns( $instance );
		$number   = $this->get_number( $instance );
		?>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html_x( 'Title:', 'admin', 'superpack' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>"><?php echo esc_html_x( 'Instagram Username:', 'admin', 'superpack' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'username' ) ); ?>"
			       value="<?php echo esc_attr( $username ); ?>">
		</p>

		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"><?php echo esc_html_x( 'Columns:', 'admin', 'superpack' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>">
				<?php
				for ( $i = 1; $i <= 9; ++ $i ) {
					echo '<option value="' . $i . '" ' . selected( $columns, $i, false ) . '>' . number_format_i18n( $i ) . '</option>';
				}
				?>
			</select>
		</p>

		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php echo esc_html_x( 'Number of items to show:', 'admin', 'superpack' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>">
				<?php
				for ( $i = 1; $i <= 20; ++ $i ) {
					echo '<option value="' . $i . '" ' . selected( $number, $i, false ) . '>' . number_format_i18n( $i ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']    = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['username'] = ( ! empty( $new_instance['username'] ) ) ? strip_tags( $new_instance['username'] ) : '';

		$instance['columns'] = absint( $new_instance['columns'] );
		$instance['number']  = absint( $new_instance['number'] );

		$this->flush_cache( $new_instance );

		return $instance;
	}

	public function get_columns( $instance ) {
		return ( ! empty( $instance['columns'] ) ) ? absint( $instance['columns'] ) : 3;
	}

	public function get_number( $instance ) {
		return ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 6;
	}

	public function get_title( $instance ) {
		$title = ( ! empty( $instance['title'] ) ) ? strip_tags( $instance['title'] ) : '';

		return apply_filters( 'widget_title', $title, $instance, $this->id_base );
	}

	public function get_username( $instance ) {
		return ( ! empty( $instance['username'] ) ) ? strip_tags( $instance['username'] ) : '';
	}

	public static function instagram_data( $username, $limit, $cache = true ) {
		$username   = strtolower( $username );
		$cache_key  = Superpack()->cache_key( 'h', 'instagram_data' . sanitize_title_with_dashes( $username ) );
		$cache_time = apply_filters( 'superpack_instagram_cache_time', HOUR_IN_SECONDS * 2 );

		if ( false === ( $instagram = get_transient( $cache_key ) ) || false === $cache ) {
			$data      = array();
			$instagram = array();
			$array     = self::instagram_fetch( $username );

			if ( ! is_wp_error( $array ) && isset( $array['entry_data']['ProfilePage'][0]['user']['media']['nodes'] ) ) {

				$data = $array['entry_data']['ProfilePage'][0]['user']['media']['nodes'];

				$user_id = $array['entry_data']['ProfilePage'][0]['user']['id'];

				foreach ( $data as $image ) {
					if ( $user_id == $image['owner']['id'] ) {
						$image['link'] = 'https://instagram.com/p/' . $image['code'] . '/';

						$instagram[] = array(
							'id'          => $image['id'],
							'link'        => $image['link'],
							'is_video'    => $image['is_video'],
							'description' => $image['caption'],
							'time'        => $image['date'],
							'likes'       => $image['likes']['count'],
							'comments'    => $image['comments']['count'],
							'thumbnail'   => $image['display_src'],
							'medium'      => $image['display_src'],
							'large'       => $image['display_src'],
						);
					}
				}

				$instagram = is_array( $instagram ) ? array_slice( $instagram, 0, 20 ) : array(); // save max 20 items
				$instagram = ! empty( $instagram ) ? json_encode( $instagram ) : ''; // to avoid messy serialization

			} elseif ( is_wp_error( $array ) ) {

				$cache_time = apply_filters( 'superpack_instagram_cache_time_retry', HOUR_IN_SECONDS );
				$instagram  = $array;

			} else {

				$instagram = new WP_Error( 'empty', __( 'Nothing to show.', 'superpack' ) );

			}

			unset( $data );
			unset( $array );

			set_transient( $cache_key, $instagram, $cache_time );
		}

		$instagram = ! is_wp_error( $instagram ) ? json_decode( $instagram, true ) : $instagram;

		return is_array( $instagram ) ? array_slice( $instagram, 0, $limit ) : $instagram;
	}

	public static function instagram_fetch( $username ) {
		//
		// Based on https://gist.github.com/cosmocatalano/4544576

		$username = trim( $username );

		if ( empty( $username ) || 2 >= strlen( $username ) ) {
			return new WP_Error( 'bad_username', esc_html__( 'Instagram username is too short.', 'superpack' ) );
		}

		$remote = wp_remote_get( 'http://instagram.com/' . trim( $username ) );

		if ( is_wp_error( $remote ) ) {
			return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', 'superpack' ) );
		}

		if ( 200 != wp_remote_retrieve_response_code( $remote ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Instagram did not return a 200.', 'superpack' ) );
		}

		$shards = explode( 'window._sharedData = ', $remote['body'] );

		if ( isset( $shards[1] ) ) {
			$json = explode( ';</script>', $shards[1] );
		}

		if ( isset( $json[0] ) ) {
			return json_decode( $json[0], true );
		} else {
			return new WP_Error( 'bad_json', esc_html__( 'Instagram has returned invalid data.', 'superpack' ) );
		}
	}
}
