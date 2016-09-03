<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Widget_Comments extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'superpack-widget-comments',
			esc_html_x( 'Comments (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'superpack__widget superpack__widget-comments',
				'description' => esc_html_x( 'Display the most recent comments.', 'admin', 'superpack' ),
			)
		);

		add_action( 'comment_post', array( &$this, 'flush_cache' ) );
		add_action( 'transition_comment_status', array( &$this, 'flush_cache' ) );
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

		$content = '';

		$title  = $this->get_title( $instance );
		$number = $this->get_number( $instance );
		$avatar = apply_filters( 'superpack_widget_comment_avatar_size', Superpack()->settings()->comment_avatar_size );

		$comments = get_comments( array(
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish',
			'post_type'   => ( $this->is_filter_blog( $instance ) ? 'post' : '' ),
			'type'        => 'comment',
		) );

		if ( $comments ) {

			// Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
			$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );

			_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

			$content .= $args['before_widget'];

			if ( $title ) {
				$content .= $args['before_title'] . $title . $args['after_title'];
			} else {
				$content .= $args['before_title'] . esc_html__( 'Recent Comments', 'superpack' ) . $args['after_title'];
			}

			$content .= '<ul class="comments_list_widget">';

			foreach ( (array) $comments as $comment ) {
				$title = get_the_title( $comment->comment_post_ID );

				$content .= '<li>';
				$content .= '<a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) . '" title="' . esc_attr( $title ) . '" >';
				$content .= '<div class="avatar">' . get_avatar( $comment, $avatar ) . '</div>';
				$content .= '<div class="content">';
				$content .= '<span class="user-nick">';
				/* Translators: 1: Comment author */
				$content .= sprintf( esc_html__( '%1$s on', 'superpack' ), '<strong>' . get_comment_author( $comment->comment_ID ) . '</strong>' );
				$content .= '</span>';
				$content .= '<h4 class="post-title">' . esc_html( $title ) . '</h4>';
				$content .= '</div>';
				$content .= '</a>';
				$content .= '</li>';
			}

			$content .= '</ul>';

			$content .= $args['after_widget'];
		}

		$cache[ $args['widget_id'] ] = $content;

		echo $content;

		wp_cache_set( $this->id_base, $cache, 'superpack__widget' );
	}

	public function flush_cache() {
		wp_cache_delete( $this->id_base, 'superpack__widget' );
	}

	public function form( $instance ) {
		$title  = $this->get_title( $instance );
		$number = $this->get_number( $instance );
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
		$instance['number'] = absint( $new_instance['number'] );

		$instance['filter_blog'] = $new_instance['filter_blog'] ? 1 : 0;

		$this->flush_cache();

		return $instance;
	}

	public function get_number( $instance ) {
		return ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
	}

	public function get_title( $instance ) {
		$title = ( ! empty( $instance['title'] ) ) ? strip_tags( $instance['title'] ) : '';

		return apply_filters( 'widget_title', $title, $instance, $this->id_base );
	}

	public function is_filter_blog( $instance ) {
		return ( ! isset( $instance['filter_blog'] ) OR $instance['filter_blog'] ) ? true : false;
	}
}
