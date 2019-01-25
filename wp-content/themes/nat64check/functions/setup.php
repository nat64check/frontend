<?php
if ( ! defined( 'NAT_GOOGLE_API_KEY' ) ) {
	define( 'NAT_GOOGLE_API_KEY', 'AIzaSyAuxP7gjwSRUXaM6e209JVHWhVZFNzqnm4' );
}

if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'menus' );
	add_theme_support( 'widgets' );
	add_theme_support( 'post-thumbnails' );
	//add_theme_support( 'woocommerce' );
}

add_action( 'init', function () {
	$menus = [
		'Hoofdmenu',
		'Footermenu',
	];

	foreach ( $menus as $menu ) {
		if ( ! wp_get_nav_menu_object( $menu ) ) {
			wp_create_nav_menu( $menu );
		}
	}
} );

add_action( 'widgets_init', function () {
	register_sidebar( [
		'name'         => 'Standaard zijbalk',
		'id'           => 'sidebar',
		'before_title' => '<div class="title">',
		'after_title'  => '</div>',
	] );


	register_sidebar( [
		'name'         => 'Onderbalk',
		'id'           => 'footer-sidebar',
		'before_title' => '<div class="title">',
		'after_title'  => '</div>',
	] );

	$defaultWidgets = [
		'WP_Widget_Pages',
		'WP_Widget_Calendar',
		'WP_Widget_Archives',
		'WP_Widget_Links',
		'WP_Widget_Meta',
		'WP_Widget_Categories',
		'WP_Widget_Recent_Posts',
		'WP_Widget_Recent_Comments',
		'WP_Widget_RSS',
		'WP_Widget_Tag_Cloud',
		'SiteOrigin_Panels_Widgets_PostLoop',
	];

	foreach ( $defaultWidgets as $widget ) {
		unregister_widget( $widget );
	}
} );
