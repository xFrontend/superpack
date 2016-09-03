<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


/**
 * Sanitizes an email address.
 *
 * @param $email
 *
 * @return string
 */
function superpack_sanitize_email( $email ) {
	$email = trim( $email );

	return ! empty( $email ) ? sanitize_email( $email ) : '';
}


/**
 * Sanitizes and converts an email address to block spam bots.
 *
 * @param $email
 *
 * @return string
 */
function superpack_sanitize_email_output( $email ) {
	$email = superpack_sanitize_email( $email );

	return ! empty( $email ) ? antispambot( 'mailto:' . $email ) : '';
}