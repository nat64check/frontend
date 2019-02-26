<?php
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'website', get_bloginfo( 'stylesheet_url' ), [], false, 'screen' );


	wp_enqueue_script( 'nat64check.main', get_stylesheet_directory_uri() . '/scripts/nat64check.main.js', [ 'jquery' ], false, false );
	$args = [
		'marker'  => get_template_directory_uri() . '/graphics/favicon.png',
		'cluster' => get_template_directory_uri() . '/graphics/cluster_nat.png',
	];
	wp_localize_script( 'nat.locations', 'nat_main', $args );

	wp_enqueue_script( 'google-maps', '//maps.googleapis.com/maps/api/js?key=' . GOOGLE_API_KEY . '&libraries=places' );

	wp_enqueue_script( 'circle.progress', get_stylesheet_directory_uri() . '/scripts/circle.progress.js', [ 'jquery' ], false, false );
	wp_enqueue_script( 'bar.progress', get_stylesheet_directory_uri() . '/scripts/bar.progress.js', [ 'jquery' ], false, false );
	if ( is_page_template( 'page-results.php' ) || is_page_template( 'page-map.php' ) ) {
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'nat.locations', get_stylesheet_directory_uri() . '/scripts/nat.locations.js', [ 'jquery' ], false, false );
		wp_enqueue_script( 'google-maps-rich-marker', get_template_directory_uri() . '/scripts/richmarker.js', [ 'google-maps' ], false, false );
		wp_enqueue_script( 'google-maps-markercluster', get_template_directory_uri() . '/scripts/markerclusterer.js', [ 'google-maps' ], false, false );
	}
} );

add_action( 'template_redirect', function () {
	global $wp_registered_widgets;

	$sidebars = wp_get_sidebars_widgets();

	if ( empty( $sidebars ) ) {
		return;
	}

	$doSidebars = [ 'footer-sidebar' ];

	foreach ( $sidebars as $sidebarId => $widgets ) {

		if ( empty( $widgets ) || ! in_array( $sidebarId, $doSidebars ) ) {
			continue;
		}

		$numberOfWidgets = count( $widgets );

		foreach ( $widgets as $i => $widgetId ) {
			if ( isset( $wp_registered_widgets[ $widgetId ] ) ) {
				if ( $i == 0 ) {
					$wp_registered_widgets[ $widgetId ]['classname'] .= ' first-widget';
				} else if ( $numberOfWidgets == ( $i + 1 ) ) {
					$wp_registered_widgets[ $widgetId ]['classname'] .= ' last-widget';
				}

				if ( $numberOfWidgets == 5 ) {
					$wp_registered_widgets[ $widgetId ]['classname'] .= ' col-sm-2';
				} else if ( $numberOfWidgets == 4 ) {
					$wp_registered_widgets[ $widgetId ]['classname'] .= ' col-sm-3';
				} else if ( $numberOfWidgets == 3 ) {
					$wp_registered_widgets[ $widgetId ]['classname'] .= ' col-sm-4';
				} else if ( $numberOfWidgets == 2 ) {
					$wp_registered_widgets[ $widgetId ]['classname'] .= ' col-sm-6';
				} else if ( $numberOfWidgets == 1 ) {
					$wp_registered_widgets[ $widgetId ]['classname'] .= ' col-sm-12';
				}
			}
		}
	}
} );


add_action( 'login_head', function () {
	echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo( 'stylesheet_directory' ) . '/login-styles.css" />';
} );

add_action( 'after_setup_theme', function () {
	if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
		show_admin_bar( false );
	}
} );

add_action( 'init', function () {
	if ( is_admin() && ! current_user_can( 'administrator' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		wp_redirect( home_url() );
		exit;
	}
} );
