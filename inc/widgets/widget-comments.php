<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

add_action( 'widgets_init', 'superpack_register_widget_comments' );

function superpack_register_widget_comments() {
	register_widget( 'Superpack_Widget_Comments' );
}

class Superpack_Widget_Comments extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'superpack-widget-comments',
			_x( 'Comments (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'sp-widget sp-widget-comments',
				'description' => _x( 'Display the most recent comments.', 'admin', 'superpack' ),
			)
		);

		add_action( 'comment_post', array( &$this, 'flush_cache' ) );
		add_action( 'transition_comment_status', array( &$this, 'flush_cache' ) );
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

		$content = '';

		$title  = $this->get_title( $instance );
		$number = $this->get_number( $instance );
		$avatar = apply_filters( 'superpack_widget_comments_image_size', '64' );

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
				$content .= $args['before_title'] . __( 'Recent Comments', 'superpack' ) . $args['after_title'];
			}

			$content .= '<ul class="comments_list_widget">';

			foreach ( (array) $comments as $comment ) {
				$title = get_the_title( $comment->comment_post_ID );

				$content .= '<li>';
				$content .= '<a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) . '" title="' . esc_attr( $title ) . '" >';
				$content .= '<div class="avatar">' . get_avatar( $comment, $avatar ) . '</div>';
				$content .= '<div class="content">';
					$content .= '<span class="user-nick sp-meta">';
					/* Translators: 1: Comment author */
					$content .= sprintf( __( '%1$s on', 'superpack' ), get_comment_author( $comment->comment_ID ) );
					$content .= '</span>';
					$content .= '<div class="title"><strong class="h5">' . esc_html( $title ) . '</strong></div>';
				$content .= '</div>';
				$content .= '</a>';
				$content .= '</li>';
			}

			$content .= '</ul>';

			$content .= $args['after_widget'];
		}

		$cache[ $args['widget_id'] ] = $content;

		echo $content;

		wp_cache_set( $this->id_base, $cache, 'sp-widget' );
	}

	public function flush_cache() {
		wp_cache_delete( $this->id_base, 'sp-widget' );
	}

	public function form( $instance ) {
		$title  = $this->get_title( $instance );
		$number = $this->get_number( $instance );
		?>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _ex( 'Title:', 'admin', 'superpack' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _ex( 'Number of items to show:', 'admin', 'superpack' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>">
				<?php
				for ( $i = 1; $i <= 20; ++ $i ) {
					echo '<option value="' . $i . '" ' . selected( $number, $i, false ) . '>' . number_format_i18n( $i ) . '</option>';
				}
				?>
			</select>
		</p>

		<?php /*
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'filter_blog' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_blog' ) ); ?>" <?php checked( ! isset( $instance['filter_blog'] ) OR $instance['filter_blog'] ? $instance['filter_blog'] : 0 ); ?> >
            <label for="<?php echo esc_attr( $this->get_field_id( 'filter_blog' ) ); ?>"><?php _ex( 'Display only blog comments.', 'admin', 'superpack' ); ?></label>
        </p>
        */ ?>
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
