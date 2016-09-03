<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


/**
 * Contact Fields, allows Themes to extend.
 */
function superpack_contact_fields() {

	$fields = array(
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

	return $fields;
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
	 * Bail early if Contact Feilds are not enabled.
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
	 * Bail early if Contact Feilds are not enabled.
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
function superpack_contact_fields_markup( $args = array() ) {

	/**
	 * Bail early if Contact Feilds are not enabled.
	 */
	if ( ! Superpack()->settings()->contact_fields['enable'] ) {
		return;
	}

	if ( ! isset( $args['user_id'] ) || isset( $args['user_id'] ) && $args['user_id'] < 1 ) {
		$args['user_id'] = get_the_author_meta( 'ID' );
	}

	$html    = '';
	$user_id = apply_filters( 'superpack_contact_fields_user_id', $args['user_id'] );
	$fields  = superpack_contact_fields();

	if ( is_array( $fields ) ) {

		foreach ( $fields as $key => $field ) {
			/**
			 * Process field link.
			 */
			if ( 'url' == $key ) {
				$field['link'] = esc_url( get_the_author_meta( 'url', $user_id ) );
			} elseif ( 'email' == $key ) {
				$field['link'] = superpack_sanitize_email_output( get_the_author_meta( 'email', $user_id ) );
			} elseif ( isset( $field['type'] ) && 'email' == $field['type'] ) {
				$field['link'] = superpack_sanitize_email_output( get_user_meta( $user_id, superpack_contact_fields_key( $key ), true ) );
			} else {
				$field['link'] = esc_url( get_user_meta( $user_id, superpack_contact_fields_key( $key ), true ) );
			}

			/**
			 * Pass $field through the filters.
			 */
			$field = apply_filters( 'superpack_contact_field', $field, $key );
			$field = apply_filters( 'superpack_contact_field_{$key}', $field );

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
			), $key );
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
			 * Prepare the markup for the field.
			 */
			if ( ! empty( $field['link'] ) ) {
				$html .= '<li>';
				$html .= '<a href="' . esc_url( $field['link'] ) . '" ';
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