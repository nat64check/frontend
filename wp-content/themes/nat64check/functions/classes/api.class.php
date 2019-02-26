<?php

class nat_api {
	public $url = 'https://core.nat64check.org/api/v1/';

	function __construct() {
		add_action( 'wp_authenticate', [ $this, 'get_token' ] );
		add_filter( 'cron_schedules', [ $this, 'nat_cron_weekly' ] );
		add_action( 'init', [ $this, 'update_country_list' ] );
		add_action( 'get_country_init', [ $this, 'get_country_object' ] );
	}

	function get_token( $username ) {
		if ( $username ) {

			$args = [
				'body' => [
					'username' => $username,
					'password' => $_POST['pwd'],
				],
			];

			$user_token = json_decode( $this->request( 'users/get_token/', '', $args, 'post' )['body'] )->token;

			$user_id = get_user_by( 'login', $username )->data->ID;

			update_field( 'api_token', $user_token, 'user_' . $user_id );
		}
	}

	function request( $url, $token = '', $args = [], $method = 'get' ) {
		$loop = true;
		do {
			//url//
			if ( ! preg_match( '|^http|', strtolower( $url ) ) ) {
				$url = $this->url . $url;
			}

			//headers//
			$headers = [
				'headers' => [
					'Content-Type' => 'application/json',
				],
			];
			if ( $token == 'user' ) {
				$token = get_field( 'api_token', 'user_' . get_current_user_id() . '' );
			}
			if ( $token ) {
				$headers['headers']['Authorization'] = 'token ' . $token;
			}

			//args//
			if ( is_array( $args ) ) {
				if ( isset( $args['body'] ) ) {
					$args['body'] = json_encode( $args['body'] );
				}

				$args = array_merge_recursive( $headers, $args );
			}

			//method//
			if ( $method == 'post' ) {
				$request = wp_remote_post( $url, $args );
			} else if ( $method == 'delete' ) {
				$args = [
					'method' => 'DELETE',
				];
				$args = array_merge_recursive( $headers, $args );

				$request = wp_remote_request( $url, $args );
			} else if ( $method == 'patch' ) {
				$args['method'] = 'PATCH';

				$request = wp_remote_request( $url, $args );
			} else {
				if ( isset( $args['body'] ) ) {
					$args['body'] = json_decode( $args['body'] );
				}
				$request = wp_remote_get( $url, $args );
			}

			//infinite loop//
			if ( is_wp_error( $request ) ) {
				sleep( 1 );
			} else if ( $request ) {
				$loop = false;
			} else {
				sleep( 1 );
			}
		} while ( $loop );

//		if( $request['response']['code'] == 200 ){
		return $request;
//		}
//		else{
//			echo 'something went wrong, please try again !, if the problem insists try again at a later time!';
//			exit;
//		}
	}

	function nat_cron_weekly( $schedules ) {
		$schedules['weekly'] = [
			'interval' => 60 * 60 * 24 * 7,
			'display'  => __( 'Once Weekly' ),
		];

		return $schedules;
	}

	function update_country_list() {
		if ( ! wp_next_scheduled( 'get_country_init' ) ) {
			wp_schedule_event( time(), 'weekly', 'get_country_init' );
		}
	}

	function get_country_object() {
		$list = json_decode( file_get_contents( "http://country.io/names.json" ), true );
		if ( $list ) {
			add_option( 'nat_country_list', $list );
		} else {
			get_option( 'nat_country_list', $list );
		}
	}
}

$nat_api = new nat_api();
