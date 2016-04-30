<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Widget_Tags extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'superpack-widget-tags',
			esc_html_x( 'Tags (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'superpack__widget superpack__widget-tags',
				'description' => esc_html_x( 'Display your most used tags.', 'admin', 'superpack' ),
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

		$current_taxonomy = $this->get_current_taxonomy( $instance );

		if ( ! empty( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			if ( 'post_tag' == $current_taxonomy ) {
				$title = esc_html__( 'Tags', 'superpack' );
			} else {
				$tax   = get_taxonomy( $current_taxonomy );
				$title = $tax->labels->name;
			}
		}

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$content = $args['before_widget'];

		if ( $title ) {
			$content .= $args['before_title'] . $title . $args['after_title'];
		} else {
			$content .= '<h3 class="screen-reader-text">' . esc_html__( 'Tags', 'superpack' ) . '</h3>';
		}

		$content .= '<div class="superpack__tag-buttons">';

		$content .= self::tag_button( array(
			'taxonomy' => $current_taxonomy,
			'number'   => $this->get_number( $instance ),
			'echo'     => false,
		) );

		$content .= "</div>";

		$content .= $args['after_widget'];

		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->id_base, $cache, 'superpack__widget' );

		echo $content;
	}

	public function flush_cache() {
		wp_cache_delete( $this->id_base, 'superpack__widget' );
	}

	public function form( $instance ) {
		$title            = $this->get_title( $instance );
		$current_taxonomy = $this->get_current_taxonomy( $instance );
		$number           = $this->get_number( $instance );

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
				for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php echo esc_html_x( 'Taxonomy:', 'admin', 'superpack' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>"
			        name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
				<?php foreach ( get_taxonomies() as $taxonomy ) :
					$tax = get_taxonomy( $taxonomy );
					if ( ! $tax->show_tagcloud || empty( $tax->labels->name ) ) {
						continue;
					}
					?>
					<option
						value="<?php echo esc_attr( $taxonomy ) ?>" <?php selected( $taxonomy, $current_taxonomy ) ?>><?php echo $tax->labels->name; ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php echo esc_html_x( 'Number of items to show:', 'admin', 'superpack' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>">
				<?php
				for ( $i = 1; $i <= 50; ++ $i ) {
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
		$instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
		$instance['number']   = absint( $new_instance['number'] );

		$this->flush_cache();

		return $instance;
	}

	public function get_title( $instance ) {
		$title = ( ! empty( $instance['title'] ) ) ? strip_tags( $instance['title'] ) : '';

		return apply_filters( 'widget_title', $title, $instance, $this->id_base );
	}

	public function get_current_taxonomy( $instance ) {
		if ( ! empty( $instance['taxonomy'] ) && taxonomy_exists( $instance['taxonomy'] ) ) {
			return $instance['taxonomy'];
		}

		return 'post_tag';
	}

	public function get_number( $instance ) {
		return ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 20;
	}

	public static function tag_button( $args ) {
		$defaults = array(
			'class'     => 'tag-button',
			'number'    => 20,
			'format'    => 'flat',
			'separator' => "\n",
			'orderby'   => 'name',
			'order'     => 'ASC',
			'exclude'   => '',
			'include'   => '',
			'link'      => 'view',
			'taxonomy'  => 'post_tag',
			'post_type' => '',
			'echo'      => true
		);
		$args     = wp_parse_args( $args, $defaults );

		$tags = get_terms(
			$args['taxonomy'],
			array_merge( $args, array( 'orderby' => 'count', 'order' => 'DESC' ) )
		); // Always query top tags

		if ( empty( $tags ) || is_wp_error( $tags ) ) {
			return false;
		}

		foreach ( $tags as $key => $tag ) {
			$link = get_term_link( intval( $tag->term_id ), $tag->taxonomy );

			if ( is_wp_error( $link ) ) {
				return false;
			}

			$tags[ $key ]->link = $link;
			$tags[ $key ]->id   = $tag->term_id;
		}

		$return = self::generate_tag_button( $tags, $args ); // Here's where those top tags get sorted according to $args

		$return = apply_filters( 'superpack_tag_cloud', $return, $args );

		if ( 'array' == $args['format'] || empty( $args['echo'] ) ) {
			return $return;
		}

		echo $return;
	}

	public static function generate_tag_button( $tags, $args = '' ) {
		$defaults = array(
			'class'                      => 'tag-button',
			'number'                     => 0,
			'format'                     => 'flat',
			'separator'                  => "\n",
			'orderby'                    => 'name',
			'order'                      => 'ASC',
			'smallest'                   => 8,
			'largest'                    => 22,
			'unit'                       => 'pt',
			'topic_count_text'           => null,
			'topic_count_text_callback'  => null,
			'topic_count_scale_callback' => 'default_topic_count_scale',
			'filter'                     => 1,
		);

		$args = wp_parse_args( $args, $defaults );

		$return = ( 'array' === $args['format'] ) ? array() : '';

		if ( empty( $tags ) ) {
			return $return;
		}

		// Juggle topic count tooltips:
		if ( isset( $args['topic_count_text'] ) ) {
			// First look for nooped plural support via topic_count_text.
			$translate_nooped_plural = $args['topic_count_text'];
		} elseif ( ! empty( $args['topic_count_text_callback'] ) ) {
			// Look for the alternative callback style. Ignore the previous default.
			if ( $args['topic_count_text_callback'] === 'default_topic_count_text' ) {
				$translate_nooped_plural = _n_noop( '%s topic', '%s topics' );
			} else {
				$translate_nooped_plural = false;
			}
		} else {
			// This is the default for when no callback, plural, or argument is passed in.
			$translate_nooped_plural = _n_noop( '%s topic', '%s topics' );
		}

		$tags_sorted = apply_filters( 'superpack_tag_cloud_sort', $tags, $args );
		if ( empty( $tags_sorted ) ) {
			return $return;
		}

		if ( $tags_sorted !== $tags ) {
			$tags = $tags_sorted;
			unset( $tags_sorted );
		} else {
			if ( 'RAND' === $args['order'] ) {
				shuffle( $tags );
			} else {
				// SQL cannot save you; this is a second (potentially different) sort on a subset of data.
				if ( 'name' === $args['orderby'] ) {
					uasort( $tags, '_wp_object_name_sort_cb' );
				} else {
					uasort( $tags, '_wp_object_count_sort_cb' );
				}

				if ( 'DESC' === $args['order'] ) {
					$tags = array_reverse( $tags, true );
				}
			}
		}

		if ( $args['number'] > 0 ) {
			$tags = array_slice( $tags, 0, $args['number'] );
		}

		$counts      = array();
		$real_counts = array(); // For the alt tag
		foreach ( (array) $tags as $key => $tag ) {
			$real_counts[ $key ] = $tag->count;
			$counts[ $key ]      = call_user_func( $args['topic_count_scale_callback'], $tag->count );
		}

		$min_count = min( $counts );
		$spread    = max( $counts ) - $min_count;
		if ( $spread <= 0 ) {
			$spread = 1;
		}
		$font_spread = $args['largest'] - $args['smallest'];
		if ( $font_spread < 0 ) {
			$font_spread = 1;
		}
		$font_step = $font_spread / $spread;

		$a = array();

		foreach ( $tags as $key => $tag ) {
			$count      = $counts[ $key ];
			$real_count = $real_counts[ $key ];
			$tag_link   = '#' != $tag->link ? esc_url( $tag->link ) : '#';
			$tag_id     = isset( $tags[ $key ]->id ) ? $tags[ $key ]->id : $key;
			$tag_name   = $tags[ $key ]->name;

			if ( $translate_nooped_plural ) {
				$title_attribute = sprintf( translate_nooped_plural( $translate_nooped_plural, $real_count ), number_format_i18n( $real_count ) );
			} else {
				$title_attribute = call_user_func( $args['topic_count_text_callback'], $real_count, $tag, $args );
			}

			$classes = array(
				$args['class'],
				"tag-button-$tag_id",
			);

			$a[] = "<a
						href='$tag_link'
						class='" . esc_attr( join( ' ', $classes ) ) . "' title='" . esc_attr( $title_attribute ) . "'
			        >$tag_name</a>";
		}

		switch ( $args['format'] ) {
			case 'array' :
				$return =& $a;
				break;
			case 'list' :
				$return = "<ul class='wp-tag-cloud superpack__tags'>\n\t<li>";
				$return .= join( "</li>\n\t<li>", $a );
				$return .= "</li>\n</ul>\n";
				break;
			default :
				$return = join( $args['separator'], $a );
				break;
		}

		if ( $args['filter'] ) {
			return apply_filters( 'superpack_generate_tag_button', $return, $tags, $args );
		} else {
			return $return;
		}
	}
}
