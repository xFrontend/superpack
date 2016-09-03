<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Widget_Posts extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'superpack-widget-posts',
			esc_html_x( 'Posts (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'superpack__widget superpack__widget-posts',
				'description' => esc_html_x( 'Display the blog posts on your site.', 'admin', 'superpack' ),
			)
		);

		add_action( 'deleted_post', array( &$this, 'flush_cache' ) );
		add_action( 'save_post', array( &$this, 'flush_cache' ) );
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

		$filter = $this->get_filter( $instance );

		if ( 'related' === $filter && ! is_single() ) {
			return;
		}

		$title = $this->get_title( $instance );

		$content = $args['before_widget'];

		if ( $title ) {
			$html_widget_title = $args['before_title'] . $title . $args['after_title'];
		} else {
			$filters = $this->post_filters();
			$title   = isset( $filters[ $filter ] ) ? $filters[ $filter ] : ucwords( $filter );

			$html_widget_title = $args['before_title'] . $title . $args['after_title'];
		}

		$data = $this->get_queried_object( $instance );

		if ( ! is_wp_error( $data ) && $data->have_posts() ) {

			$index = 0;

			$content .= $html_widget_title;
			$content .= '<ul class="posts-' . esc_attr( $filter ) . '">';

			while ( $data->have_posts() ) {
				$data->the_post();

				$index ++;

				$title = get_the_title() ? get_the_title() : get_the_ID();

				$content .= '<li>';

				$thumb_id   = get_post_thumbnail_id() ? get_post_thumbnail_id() : 0;
				$thumb_size = apply_filters( 'superpack_widget_posts_image_size', Superpack()->settings()->image_size_posts, $thumb_id );

				if ( 0 < $thumb_id && ! post_password_required() ) {

					$content .= '<div class="superpack__thumbnail">';
					$content .= '<div class="format-' . esc_attr( get_post_format() ? get_post_format() : 'standard' ) . '">';
					$content .= '<a href="' . get_the_permalink() . '" title="">' . wp_get_attachment_image( $thumb_id, $thumb_size ) . '</a>';
					$content .= '</div>';
					$content .= '</div>';

				}

				$content .= '<a class="content" href="' . get_permalink() . '" title="' . esc_attr( $title ) . '">';
				$content .= '<h4 class="post-title">' . esc_html( $title ) . '</h4>';
				$content .= '<span class="post-date">' . get_the_date() . '</span>';
				$content .= '</a>';

				$content .= '</li>';
			}

			$content .= '</ul>';

		} elseif ( is_wp_error( $data ) ) {

			$content .= $html_widget_title;

			$content .= '<ul class="posts-' . esc_attr( $filter ) . '">';
			$content .= '<li class="error">' . $data->get_error_message() . '</li>';
			$content .= '</ul>';

		}

		$content .= $args['after_widget'];

		$cache[ $args['widget_id'] ] = $content;

		echo $content;

		wp_reset_postdata(); // Reset the global $the_post as this query will have stomped on it

		wp_cache_set( $this->id_base, $cache, 'superpack__widget' );
	}

	public function flush_cache() {
		wp_cache_delete( $this->id_base, 'superpack__widget' );
	}

	public function form( $instance ) {
		$title   = $this->get_title( $instance );
		$number  = $this->get_number( $instance );
		$filters = $this->post_filters();

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
				for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ) ?>"><?php echo esc_html_x( 'Filter', 'admin', 'superpack' ) ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter' ) ) ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'filter' ) ) ?>">
				<?php
				foreach ( $filters as $key => $label ) {
					echo '<option value="' . esc_attr( $key ) . '"' . selected( $this->get_filter( $instance ), $key, false ) . '>' . $label . '</option>';
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

		$instance['title']  = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['filter'] = array_key_exists( $new_instance['filter'], $this->post_filters() ) ? $new_instance['filter'] : 'recent';
		$instance['number'] = absint( $new_instance['number'] );

		$this->flush_cache();

		return $instance;
	}

	public function get_filter( $instance ) {
		return ( ! empty( $instance['filter'] ) ) ? strip_tags( $instance['filter'] ) : 'recent';
	}

	public function get_number( $instance ) {
		return ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
	}

	public function get_queried_object( $instance ) {
		$filter = $this->get_filter( $instance );
		$number = $this->get_number( $instance );

		switch ( $filter ) {
			case 'sticky':
				$data = $this->posts_sticky( $number );
				break;

			case 'random':
				$data = $this->posts_random( $number );
				break;

			case 'related':
				$data = $this->posts_related( $number );
				break;

			case 'recent':
			default :
				$data = $this->posts_recent( $number );
				break;
		}

		return $data;
	}

	public function get_title( $instance ) {
		$title = ( ! empty( $instance['title'] ) ) ? strip_tags( $instance['title'] ) : '';

		return apply_filters( 'widget_title', $title, $instance, $this->id_base );
	}

	public function post_filters() {
		return array(
			'recent'  => esc_html__( 'Recent Posts', 'superpack' ),
			'random'  => esc_html__( 'Random Posts', 'superpack' ),
			'sticky'  => esc_html__( 'Sticky Posts', 'superpack' ),
			'related' => esc_html__( 'Related Posts', 'superpack' ),
		);
	}

	public function posts_recent( $count = 5, $cache = true ) {
		$count = 0 < intval( $count ) ? $count : 5;

		$cache_key  = Superpack()->cache_key( 'd', 'recent_posts' . $count );
		$cache_time = apply_filters( 'superpack_recent_posts_cache_time', MINUTE_IN_SECONDS );

		if ( false === ( $data = get_transient( $cache_key ) ) || false === $cache ) {
			$args = array(
				'orderby'             => 'date',    // date | rand
				'order'               => 'DESC',
				'post_status'         => 'publish',
				'post_type'           => 'post',
				'posts_per_page'      => $count,
				'paged'               => 1,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			);

			$data = new WP_Query( $args );

			set_transient( $cache_key, $data, $cache_time );
		}

		return $data;
	}

	public function posts_random( $count = 5, $cache = true ) {
		$count = 0 < intval( $count ) ? $count : 5;

		$cache_key  = Superpack()->cache_key( 'd', 'random_posts' . $count );
		$cache_time = apply_filters( 'superpack_random_posts_cache_time', MINUTE_IN_SECONDS );

		if ( false === ( $data = get_transient( $cache_key ) ) || false === $cache ) {
			$args = array(
				// date | rand
				'orderby'             => 'rand',
				'order'               => 'DESC',
				'post_status'         => 'publish',
				'post_type'           => 'post',
				'posts_per_page'      => $count,
				'paged'               => 1,
				// has_password true means posts with passwords, false means posts without.
				'has_password'        => false,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			);

			$data = new WP_Query( $args );

			set_transient( $cache_key, $data, $cache_time );
		}

		return $data;
	}

	public function posts_related( $count = 5, $current_post_id = 0, $cache = true ) {
		$count           = 0 < intval( $count ) ? $count : 5;
		$current_post_id = 0 < $current_post_id ? $current_post_id : get_the_ID();

		if ( ! is_single() || 1 > $current_post_id ) {
			return new WP_Error( 'skipped', esc_html__( 'No posts to show.', 'superpack' ) );
		}

		$cache_key  = Superpack()->cache_key( 'd', 'related_posts' . $count . $current_post_id );
		$cache_time = apply_filters( 'superpack_related_posts_cache_time', MINUTE_IN_SECONDS );

		if ( false === ( $data = get_transient( $cache_key ) ) || false === $cache ) {
			$post_cats = wp_get_object_terms( $current_post_id, 'category', array( 'fields' => 'ids' ) );
			$post_tags = wp_get_object_terms( $current_post_id, 'post_tag', array( 'fields' => 'ids' ) );

			$args = array(
				'orderby'        => 'date',    // date | rand
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'post_type'      => 'post',
				'posts_per_page' => $count,
				'paged'          => 1,
				'post__not_in'   => array( $current_post_id ), // Exclude current post
				'has_password'   => false, // has_password true means posts with passwords, false means posts without.
				'no_found_rows'  => true,
				'tax_query'      => array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'category',
						'field'    => 'id',
						'terms'    => $post_cats,
						'operator' => 'IN',
					),
					array(
						'taxonomy' => 'post_tag',
						'field'    => 'id',
						'terms'    => $post_tags,
						'operator' => 'IN',
					),
				),
			);

			$data = new WP_Query( $args );

			set_transient( $cache_key, $data, $cache_time );
		}

		return $data;
	}

	public function posts_sticky( $count = 5, $cache = true ) {
		$count = 0 < intval( $count ) ? $count : 5;

		$sticky = get_option( 'sticky_posts' );

		if ( empty( $sticky ) ) {
			return new WP_Error( 'no_sticky', esc_html__( 'No sticky posts found.', 'superpack' ) );
		}

		$cache_key  = Superpack()->cache_key( 'd', 'sticky_posts' . $count . count( $sticky ) );
		$cache_time = apply_filters( 'superpack_sticky_posts_cache_time', MINUTE_IN_SECONDS );

		if ( false === ( $data = get_transient( $cache_key ) ) || false === $cache ) {
			$args = array(
				'posts_per_page'      => $count,
				'paged'               => 1,
				'post__in'            => $sticky,
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			);

			$data = new WP_Query( $args );

			set_transient( $cache_key, $data, $cache_time );
		}

		return $data;
	}
}
