<?php
add_filter( 'max_wp_login_screen', function ( $args ) {
	$args['img'] = get_stylesheet_directory_uri() . '/graphics/logo.png';

	return $args;
} );

add_filter( 'maxwp_attachment_size_limit', function () {
	return 2000;
} );

add_filter( 'login_redirect', function () {
	return get_site_url();
} );

apply_filters( 'get_comment_date', 'Y', 10, 3 );

add_filter( 'gform_enable_password_field', '__return_true' );
add_filter( 'gettext', function ( $text ) {

	if ( $text == 'Lost your password?' ) {
		$text .= '<br /><a href="' . site_url() . '/register/' . '">Don\'t have an account yet? Register here!</a>';
	}

	return $text;
} );
