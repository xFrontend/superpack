<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Superpack_Widget_Social_Icons extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'superpack-widget-social-icons',
			esc_html_x( 'Social Icons (SuperPack)', 'admin', 'superpack' ),
			array(
				'classname'   => 'superpack__widget superpack__widget-social-icons',
				'description' => esc_html_x( 'Display social profile link icons.', 'admin', 'superpack' ),
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

		// Get menu
		$menu = ! empty( $instance['menu'] ) ? wp_get_nav_menu_object( $instance['menu'] ) : false;

		if ( ! $menu ) {
			return;
		}

		$content = $args['before_widget'];

		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		if ( ! empty( $instance['title'] ) ) {
			$content .= $args['before_title'] . $instance['title'] . $args['after_title'];
		} else {
			$content .= '<h3 class="screen-reader-text">' . esc_html__( 'About', 'superpack' ) . '</h3>';
		}

		$menu_args = array(
			'fallback_cb'     => '',
			'container'       => 'nav',
			'container_class' => Superpack()->settings()->social_icons['container_class'],
			'depth'           => 1,
			'menu'            => $menu,
			'menu_class'      => Superpack()->settings()->social_icons['menu_class'],
			'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
			'echo'            => false
		);

		/**
		 * Hide menu texts if the active theme gave us green light.
		 */
		if ( Superpack()->settings()->social_icons['icons'] ) {
			$menu_args['link_before'] = '<span class="screen-reader-text">';
			$menu_args['link_after']  = '</span>';
		}

		$content .= wp_nav_menu( apply_filters( 'superpack_widget_social_icons_menu_args', $menu_args, $menu, $args ) );

		$content .= $args['after_widget'];

		$cache[ $args['widget_id'] ] = $content;

		wp_cache_set( $this->id_base, $cache, 'superpack__widget' );

		echo $content;
	}

	public function flush_cache() {
		wp_cache_delete( $this->id_base, 'superpack__widget' );
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$menu  = isset( $instance['menu'] ) ? $instance['menu'] : '';

		// Get menus
		$menus = wp_get_nav_menus();

		// If no menus exists, direct the user to go and create some.
		?>
		<p class="nav-menu-widget-no-menus-message" <?php if ( ! empty( $menus ) ) {
			echo ' style="display:none" ';
		} ?>>
			<?php
			if ( isset( $GLOBALS['wp_customize'] ) && $GLOBALS['wp_customize'] instanceof WP_Customize_Manager ) {
				// @todo When expanding a panel, the JS should be smart enough to collapse any existing panels and sections.
				$url = 'javascript: wp.customize.section.each(function( section ){ section.collapse(); }); wp.customize.panel( "nav_menus" ).focus();';
			} else {
				$url = admin_url( 'nav-menus.php' );
			}
			?>
			<?php echo sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.', 'superpack' ), esc_attr( $url ) ); ?>
		</p>
		<div class="nav-menu-widget-form-controls" <?php if ( empty( $menus ) ) {
			echo ' style="display:none" ';
		} ?>>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ) ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				       name="<?php echo $this->get_field_name( 'title' ); ?>"
				       value="<?php echo esc_attr( $title ); ?>"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'menu' ); ?>"><?php _e( 'Select Menu:' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'menu' ); ?>"
				        name="<?php echo $this->get_field_name( 'menu' ); ?>">
					<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
					<?php foreach ( $menus as $item ) : ?>
						<option
							value="<?php echo esc_attr( $item->term_id ); ?>" <?php selected( $menu, $item->term_id ); ?>>
							<?php echo esc_html( $item->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		}

		if ( ! empty( $new_instance['menu'] ) ) {
			$instance['menu'] = (int) $new_instance['menu'];
		}

		return $instance;
	}
}
