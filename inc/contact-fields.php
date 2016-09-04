<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


/**
 * Contact Fields, allows Themes to extend.
 */
function superpack_contact_fields() {

	$defaults = array(
		'facebook'     => array(
			'title'      => esc_html__( 'Facebook', 'superpack' ),
			'label'      => esc_html_x( 'Facebook URL', 'admin', 'superpack' ),
			'icon_class' => 'fa fa-facebook-f',
		),
		'twitter'      => array(
			'title'      => esc_html__( 'Twitter', 'superpack' ),
			'label'      => esc_html_x( 'Twitter URL', 'admin', 'superpack' ),
			'icon_class' => 'fa fa-twitter',
		),
		'gplus'        => array(
			'title'      => esc_html__( 'Google+', 'superpack' ),
			'label'      => esc_html_x( 'Google+ URL', 'admin', 'superpack' ),
			'icon_class' => 'fa fa-google-plus',
		),
		'linkedin'     => array(
			'title'      => esc_html__( 'Linkedin', 'superpack' ),
			'label'      => esc_html_x( 'LinkedIn URL', 'admin', 'superpack' ),
			'icon_class' => 'fa fa-linkedin',
		),
		'url'          => array(
			'title'      => esc_html__( 'Website', 'superpack' ),
			'label'      => esc_html_x( 'Website', 'admin', 'superpack' ),
			'icon_class' => 'fa fa-external-link-square',
		),
		'email_public' => array(
			'type'       => 'email',
			'title'      => esc_html__( 'Email', 'superpack' ),
			'label'      => esc_html_x( 'Public Email', 'admin', 'superpack' ),
			'icon_class' => 'fa fa-envelope',
		),
	);

	$fields = Superpack()->parse_args( Superpack()->settings()->contact_fields['fields'], $defaults );

	return apply_filters( 'superpack_contact_fields', $fields );
}


/**
 * Add Contact Fields to Author's Profile.
 *
 * @param $fields
 * @param $user
 *
 * @return mixed
 */
function superpack_contact_fields_register( $methods ) {

	/**
	 * Bail early if Contact Fields are not enabled.
	 */
	if ( ! Superpack()->settings()->contact_fields['enable'] ) {
		return $methods;
	}

	/**
	 * Check whether current user Author capability.
	 * Use `superpack_contact_fields_capability` filter to check
	 * with custom capability/conditions.
	 */
	if ( ! apply_filters( 'superpack_contact_fields_capability', current_user_can( 'publish_posts' ) ) ) {
		return $methods;
	}

	$fields = superpack_contact_fields();

	if ( is_array( $fields ) ) {
		/**
		 * Excludes default fields.
		 */
		unset( $fields['email'] );
		unset( $fields['url'] );

		foreach ( $fields as $key => $field ) {
			/**
			 * Each field must have title.
			 */
			if ( empty( $field['title'] ) ) {
				continue;
			}

			$label              = isset( $field['label'] ) ? $field['label'] : ucwords( $field['title'] );
			$method             = superpack_contact_fields_key( $key );
			$methods[ $method ] = sprintf( esc_html_x( '%s (SuperPack)', 'admin', 'superpack' ), $label );
		}
	}

	return $methods;
}

add_filter( 'user_contactmethods', 'superpack_contact_fields_register', 1 );


/**
 * Register Actions to display Contact Fields.
 */
function superpack_contact_fields_callback() {

	/**
	 * Bail early if Contact Fields are not enabled.
	 */
	if ( ! Superpack()->settings()->contact_fields['enable'] ) {
		return;
	}

	$hooks = Superpack()->settings()->contact_fields['action_hooks'];
	$hooks = apply_filters( 'superpack_contact_fields_callback', $hooks );

	if ( is_array( $hooks ) ) {
		foreach ( $hooks as $action ) {
			add_action( $action, 'superpack_contact_fields_markup' );
		}
	}
}

add_action( 'after_setup_theme', 'superpack_contact_fields_callback', 999 );


/**
 * Render Contact Fields.
 *
 * @param array $args
 */
function superpack_contact_fields_markup( $user_id ) {

	/**
	 * Bail early if Contact Fields are not enabled.
	 */
	if ( ! Superpack()->settings()->contact_fields['enable'] ) {
		return;
	}

	if ( $user_id < 1 ) {
		$user_id = get_the_author_meta( 'ID' );
	}

	$html    = '';
	$user_id = apply_filters( 'superpack_contact_fields_user_id', $user_id );
	$fields  = superpack_contact_fields();

	if ( is_array( $fields ) ) {

		foreach ( $fields as $key => $field ) {
			/**
			 * Process field link.
			 */
			if ( 'url' == $key ) {
				$field['type'] = 'url';
				$field['data'] = get_the_author_meta( 'url', $user_id );
			} elseif ( 'email' == $key ) {
				$field['type'] = 'email';
				$field['data'] = get_the_author_meta( 'email', $user_id );
			} elseif ( isset( $field['type'] ) && 'email' == $field['type'] ) {
				$field['data'] = get_user_meta( $user_id, superpack_contact_fields_key( $key ), true );
			} else {
				$field['type'] = 'url';
				$field['data'] = get_user_meta( $user_id, superpack_contact_fields_key( $key ), true );
			}

			/**
			 * Pass $field through the filters.
			 */
			$field = apply_filters( 'superpack_contact_field', $field, $key, $user_id );
			$field = apply_filters( 'superpack_contact_field_{$key}', $field, $user_id );

			/**
			 * Each field must have title.
			 */
			if ( empty( $field['title'] ) ) {
				continue;
			}

			/**
			 * Attributes for the field.
			 */
			$attr = apply_filters( 'superpack_contact_fields_attributes', array(
				'class' => $key,
				'rel'   => 'me',
			), $key, $user_id );
			$attr = array_map( 'esc_attr', $attr );

			/**
			 * Process field icon, if any or just use title.
			 */
			if ( ! empty( $field['icon_class'] ) ) {
				$title = '<i class="' . esc_attr( $field['icon_class'] ) . '"></i>' . ' ' . '<span class="screen-reader-text">' . $field['title'] . '</span>';
			} else {
				$title = $field['title'];
			}

			/**
			 * Prepares the link.
			 */
			if ( isset( $field['type'] ) && 'email' == $field['type'] ) {
				$link = superpack_sanitize_email_output( $field['data'] );
			} else {
				$link = esc_url( $field['data'] );
			}

			/**
			 * Prepare the markup for the field.
			 */
			if ( ! empty( $link ) ) {
				$html .= '<li>';
				$html .= '<a href="' . $link . '" ';
				foreach ( $attr as $name => $value ) {
					$html .= " $name=" . '"' . $value . '"';
				}
				$html .= '>' . $title . '</a> ';
				$html .= '</li>';
			}
		}

	}

	if ( ! empty ( $html ) ) {
		$html = '<ul class="' . Superpack()->settings()->contact_fields['container_class'] . '">' . $html . '</ul>';

		/**
		 * Print the final markup.
		 */
		echo apply_filters( 'superpack_contact_fields_markup', $html, $user_id );
	}
}


/**
 * Returns prefix/key for Contact Fields.
 *
 * @param string $name
 *
 * @return string
 */
function superpack_contact_fields_key( $name = '' ) {
	return apply_filters( 'superpack_contact_fields_prefix', 'superpack_' ) . $name;
}


/**
 * Migrate Contact Fields data for supported Themes/Snowbird.
 *
 * @see https://github.com/xFrontend/superpack/issues/3
 *
 * TODO: Remove in a future release.
 */
function superpack_contact_fields_data_update() {

	/**
	 * Bail early if we're not in Admin Dashboard,
	 */
	if ( ! is_admin() ) {
		return;
	}

	/**
	 * Bail early if Contact Fields are not enabled, or no proper permission.
	 */
	if ( ! Superpack()->settings()->contact_fields['enable'] || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$the_key = Superpack()->codename( 'migrate_contact_fields' );

	/**
	 * Bail if we've done it before.
	 */
	if ( get_option( $the_key ) ) {
		return;
	}

	/**
	 * Gather data for the Authors
	 */
	$new = $author_ids = array();

	if ( function_exists( 'snowbird_get_contributor_ids' ) ) {
		$author_ids = snowbird_get_contributor_ids();
	}

	/**
	 * A theme author have to provide User IDs to proceed.
	 */
	$author_ids = apply_filters( 'superpack_contact_fields_author_ids', $author_ids );

	/**
	 * Bail when we don't have User IDs.
	 */
	if ( empty( $author_ids ) ) {
		return;
	}

	/**
	 * Contact Fields we need to update.
	 */
	$fields = array(
		'facebook',
		'twitter',
		'gplus',
		'linkedin',
		'email_public',
	);

	foreach ( $author_ids as $user_id ) {

		foreach ( $fields as $key ) {
			$old     = get_user_meta( $user_id, $key, true );
			$current = get_user_meta( $user_id, superpack_contact_fields_key( $key ), true );

			if ( 'email_public' == $key ) {
				$old     = superpack_sanitize_email( $old );
				$current = superpack_sanitize_email( $current );
			} else {
				$old     = esc_url( $old );
				$current = esc_url( $current );
			}

			if ( empty( $current ) && $old != $current ) {
				$new[ $user_id ][ $key ] = $old;
			}
		}

	}

	/**
	 * Update the data to SuperPack Contact Fields
	 */
	foreach ( $new as $user_id => $methods ) {

		foreach ( $methods as $key => $value ) {
			update_user_meta( $user_id, superpack_contact_fields_key( $key ), $new[ $user_id ][ $key ] );
		}

	}

	/**
	 * Mark we're done.
	 */
	$theme = wp_get_theme()->parent() ? wp_get_theme()->parent() : wp_get_theme();

	add_option( $the_key, $theme->get( 'Name' ) );
}

add_action( 'after_setup_theme', 'superpack_contact_fields_data_update', 99 );