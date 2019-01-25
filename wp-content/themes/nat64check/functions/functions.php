<?php

function nat_get_average( $array = [] ) {
	$average = 0;

	if ( count( $array ) ) {
		$array   = array_filter( $array, function ( $x ) {
			return $x !== '';
		} );
		$average = array_sum( $array ) / count( $array );
	}

	return $average;

}

function max_wp_user_prop( $prop = false, $user_id = false ) {
	if ( ! $user_id && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	$user_prop = false;

	if ( $user = get_user_by( 'id', $user_id ) ) {

		if ( isset( $user->$prop ) ) {
			$user_prop = $user->$prop;
		} else if ( isset( $user->data->$prop ) ) {
			$user_prop = $user->data->$prop;
		} else if ( isset( $user->data->{'user_' . $prop} ) ) {
			$user_prop = $user->data->{'user_' . $prop};
		} else if ( $meta_prop = get_user_meta( $user_id, $prop, true ) ) {
			$user_prop = $meta_prop;
		}
	}

	return $user_prop;
}

function max_wp_request_var( $var = '' ) {
	if ( isset( $_REQUEST[ $var ] ) ) {
		return max_wp_esc_var( $_REQUEST[ $var ] );
	}

	return false;
}

function max_wp_esc_var( $obj = false ) {
	if ( is_array( $obj ) ) {
		foreach ( $obj as $k => $v ) {
			$obj[ $k ] = max_wp_esc_var( $v );
		}
	} else if ( is_object( $obj ) ) {
		/** @noinspection PhpWrongForeachArgumentTypeInspection */
		foreach ( $obj as $k => $v ) {
			$obj->$k = max_wp_esc_var( $v );
		}
	} else {
		$obj = trim( esc_html( $obj ) );
	}

	return $obj;
}

function max_wp_get_option( $name = false, $blog_id = false ) {
	return MaxWPOptions::option( $name, $blog_id );
}

function max_wp_paginate( $args = [] ) {
	MaxWPPaginate::show( $args );
}
