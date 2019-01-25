<?php

class nat_user {
	public $type = 'user';
	public $single = 'user';
	public $multiple = 'users';

	function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );

		add_action( 'acf/pre_save_post', [ $this, 'user_title' ], - 1 );
		add_action( 'acf/save_post', [ $this, 'create_new_user' ], 10, 2 );
		add_action( 'acf/save_post', [ $this, 'user_schedules' ], 20 );

		add_action( 'rest_api_init', [ $this, 'activation_endpoint' ] );
		add_action( 'rest_api_init', [ $this, 'send_mail_endpoint' ] );


		add_filter( 'acf/prepare_field/key=field_5bab4848f7824', [ $this, 'load_servers' ] );
		add_filter( 'acf/prepare_field/key=field_5bab482df7823', [ $this, 'load_private' ] );
		if ( ! empty( $_GET['user_id'] ) && ! empty( $_GET['user_auth'] ) ) {
			add_action( 'wp_loaded', [ __CLASS__, 'authenticate_user' ] );
		}
		if ( ! empty( $_GET['delete_user'] ) ) {
			add_action( 'wp_loaded', [ __CLASS__, 'delete_user' ] );
		}
		if ( ! empty( $_GET['change_pass'] ) ) {
			add_action( 'wp_loaded', [ __CLASS__, 'change_pass' ] );
		}
	}

	static function user_title( $post_id ) {

		if ( empty( $_POST['acf'] ) ) {
			return;
		}

		if ( ! empty( $_POST['acf']['field_5ba2349db6d2f'] ) && ! empty( $_POST['acf']['field_5ba234adb6d31'] ) ) {
			$_POST['acf']['_post_title']   = $_POST['acf']['field_5ba2349db6d2f'];
			$_POST['acf']['_post_content'] = $_POST['acf']['field_5ba234adb6d31'];
		}

		return $post_id;
	}

	static function authenticate_user() {
		$api_user  = (int) esc_html( base64_decode( $_GET['user_id'] ) );
		$user_code = esc_html( base64_decode( $_GET['user_auth'] ) );


		global $nat_api;
		$args = [
			'body' => [
				'code' => $user_code,
			],
		];
		$nat_api->request( 'users/' . $api_user . '/authenticate/', '', $args, 'post' );
//		wp_redirect( site_url().'/wp-login.php' );
		wp_redirect( site_url() . '/welcome/' );

		exit;
	}

	static function delete_user() {
		global $nat_api;
		$user_id      = (int) esc_html( base64_decode( $_GET['delete_user'] ) );
		$api_user     = get_field( 'api_user', 'user_' . $user_id );
		$related_user = max_wp_user_prop( 'connect_user' );

		$user_args = [
			'ID'          => $related_user,
			'post_status' => 'private',
		];

		$nat_api->request( 'testruns/users/' . $api_user, 'user', [], 'delete' );
		wp_delete_post( $related_user );
		wp_delete_user( $user_id );

		wp_redirect( site_url() );
	}

	static function user_schedules() {
		if ( ! is_admin() && is_singular( $this->type ) ) {
			global $nat_api;
			$schedule_list = get_post_meta( get_the_id(), 'schedule_list_ids', true );
			if ( ! is_array( $schedule_list ) ) {
				$schedule_list = [];
			}
			$schedule_now_list = [];

			if ( have_rows( 'new_checks' ) ) {

				while ( have_rows( 'new_checks' ) ) {
					the_row();

					$public = true;

					if ( get_sub_field( 'schedule_private' ) || ( get_post_meta( get_the_ID(), 'private_setting' )[0] == 'on' ) ) {
						$public = false;
					}

					$args = [
						'body' => [
							'name'      => get_sub_field( 'schedule_name' ),
							'url'       => get_sub_field( 'schedule_url' ),
							'time'      => get_sub_field( 'schedule_time' ),
							'start'     => get_sub_field( 'schedule_start' ),
							'end'       => get_sub_field( 'schedule_end' ),
							'frequency' => get_sub_field( 'schedule_freq' ),
							'is_public' => $public,
							'trillians' => get_sub_field( 'schedule_servers' ),
						],
					];

					if ( ! get_sub_field( 'schedule_id' ) ) {
						$schedule = $nat_api->request( 'schedules/', 'user', $args, 'post' );
						update_sub_field( 'schedule_id', json_decode( $schedule['body'] )->id );
						$schedule_list[]     = json_decode( $schedule['body'] )->id;
						$schedule_now_list[] = json_decode( $schedule['body'] )->id;
					} else {
						$nat_api->request( 'schedules/' . get_sub_field( 'schedule_id' ) . '/', 'user', $args, 'patch' );
						$schedule_now_list[] = get_sub_field( 'schedule_id' );
					}
				}
			}

			$schedule_list     = array_unique( $schedule_list );
			$schedule_now_list = array_unique( $schedule_now_list );
			update_post_meta( get_the_id(), 'schedule_list_ids', $schedule_list );

			foreach ( $schedule_list as $schedule_id ) {
				if ( $schedule_id && ! in_array( $schedule_id, $schedule_now_list ) ) {

					$nat_api->request( 'schedules/' . $schedule_id . '/', 'user', '', 'delete' );
				}
			}
		}
	}

	static function load_servers( $field ) {
		global $nat_api;
		$servers          = json_decode( $nat_api->request( 'trillians/?only=country,_url', $token )['body'] )->results;
		$field['choices'] = [];

		foreach ( $servers as $server ) {
			$field['choices'][ $server->_url ] = get_option( 'nat_country_list' )[ $server->country ];
		}

		return $field;
	}

	static function load_private( $field ) {
		if ( strpos( $field['name'], 'acfcloneindex' ) !== false ) {
			$check = 0;
			if ( get_post_meta( get_the_ID(), 'private_setting' )[0] ) {
				$check = 1;
			}
			$field['value'] = $check;
		}

		return $field;
	}

	function register_post_type() {
		$args = [
			'label'             => ucfirst( $this->multiple ),
			'labels'            => [
				'all_items'          => 'Alle ' . $this->multiple,
				'singular_name'      => ucfirst( $this->single ),
				'menu_name'          => ucfirst( $this->multiple ),
				'add_new'            => 'Nieuw ' . $this->single,
				'add_new_item'       => 'Nieuw ' . $this->single . ' toevoegen',
				'edit_item'          => ucfirst( $this->single ) . ' bewerken',
				'new_item'           => ucfirst( $this->single ) . ' toevoegen',
				'view_item'          => ucfirst( $this->single ) . ' bekijken',
				'search_items'       => ucfirst( $this->single ) . ' zoeken',
				'not_found'          => 'Geen ' . $this->multiple . ' gevonden',
				'not_found_in_trash' => 'Geen ' . $this->multiple . ' gevonden in de prullenbak',
			],
			'public'            => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'has_archive'       => false,
			'hierarchical'      => false,
			'menu_icon'         => 'dashicons-admin-users',
			'supports'          => [
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'revisions',
				'author',
			],
		];

		register_post_type( $this->type, $args );


	}

	function add_user_role() {
		$args = [
			'manage_categories'      => true,
			'manage_links'           => true,
			'edit_others_posts'      => true,
			'edit_pages'             => true,
			'edit_others_pages'      => true,
			'edit_published_pages'   => true,
			'publish_pages'          => true,
			'delete_pages'           => true,
			'delete_others_pages'    => true,
			'delete_published_pages' => true,
			'delete_others_posts'    => true,
			'delete_private_posts'   => true,
			'edit_private_posts'     => true,
			'read_private_posts'     => true,
			'delete_private_pages'   => true,
			'edit_private_pages'     => true,
			'read_private_pages'     => true,
			'unfiltered_html'        => true,
			'edit_published_posts'   => true,
			'upload_files'           => true,
			'publish_posts'          => false,
			'delete_published_posts' => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'read'                   => true,
		];
		add_role( 'nat_user', 'NAT64Check user', $args );
//		$role_edit = get_role( 'nat_user' );
//
//		$role_edit->remove_cap( 'publish_posts' );
	}

	function create_new_user( $post_id ) {
		if ( ! is_admin() && ! empty( $_POST['acf']['field_5ba2349db6d2f'] ) && ! empty( $_POST['acf']['field_5ba234adb6d31'] ) ) {

			global $nat_api;

			$userdata = (object) [
				'user_login' => $_POST['acf']['field_5ba2349db6d2f'],
				'user_pass'  => $_POST['acf']['field_5ba246de07363'],
				'user_email' => $_POST['acf']['field_5ba234adb6d31'],
				'first_name' => $_POST['acf']['field_5ba234f5dc2bc'],
				'last_name'  => $_POST['acf']['field_5ba234fbdc2bd'],
			];

			$args = [
				'body' => [
					'username'   => $userdata->user_login,
					'first_name' => $userdata->first_name,
					'last_name'  => $userdata->last_name,
					'email'      => $userdata->user_email,
					'password'   => $userdata->user_pass,
				],
			];

			$api_user = json_decode( $nat_api->request( 'users/register/', '', $args, 'post' )['body'] );

			if ( $api_user->id ) {
				$user_id = wp_insert_user( $userdata );

				update_user_meta( $user_id, 'connect_user', $post_id );
				$u = new WP_User( $user_id );

				$u->remove_role( 'subscriber' );
				$u->add_role( 'nat_user' );

				update_field( 'api_user', $api_user->id, 'user_' . $user_id );
			} else {
				get_header();
				?>
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="content-padding content-margin color-secondary">
								<?php
								foreach ( $api_user as $errors ) {
									foreach ( $errors as $error ) {
										echo '<i class="fa fa-times" aria-hidden="true"></i> ' . $error . '</br>';
									}
								}
								wp_delete_post( $post_id, true ); ?>
                                <a id="try-again" class="button bg-primary" href="<?php site_url() . '/register' ?>">Try
                                    again!</a>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				get_footer();
				exit;
			}
		}
	}

	function send_mail_endpoint() {

		register_rest_route( 'gui/v1', '/user/send_mail/', [
			'methods'  => 'POST',
			'callback' => [ $this, 'get_mail' ],
		] );
	}

	function get_mail( $request ) {
		$body = (object) [
			'email'   => $request->get_param( 'email' ),
			'subject' => $request->get_param( 'subject' ),
			'message' => $request->get_param( 'message' ),
		];

		foreach ( $body as $k => $v ) {
			if ( ! isset( $v ) ) {
				return new WP_REST_Response( $k . ' variable is required!', 400 );
				exit;
			}
		}

		$post_args = (object) [
			'email'   => $body->email,
			'subject' => $body->subject,
			'message' => $body->message,
		];

		$headers   = [];
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: NAT64Check <' . get_bloginfo( 'admin_email' ) . '>';


		ob_start();
		include( locate_template( 'partials/mail/mail-update.php' ) );
		$message = ob_get_contents();
		ob_end_clean();

		wp_mail( $body->email, $body->subject, $message, $headers );

		return new WP_REST_Response( $body, 200 );

	}

	function activation_endpoint() {

		register_rest_route( 'gui/v1', '/user/activation/', [
			'methods'  => 'POST',
			'callback' => [ $this, 'get_code' ],
		] );
	}

	function get_code( $request ) {
		$body = (object) [
			'user_id'    => $request->get_param( 'user_id' ),
			'username'   => $request->get_param( 'username' ),
			'first_name' => $request->get_param( 'first_name' ),
			'last_name'  => $request->get_param( 'last_name' ),
			'email'      => $request->get_param( 'email' ),
			'code'       => $request->get_param( 'code' ),
		];

		foreach ( $body as $k => $v ) {
			if ( ! isset( $v ) ) {
				return new WP_REST_Response( $k . ' variable is required!', 400 );
				exit;
			}
		}
		$post_args = (object) [
			'user_id'          => $body->user_id,
			'username'         => $body->username,
			'first_name'       => $body->first_name,
			'last_name'        => $body->last_name,
			'email'            => $body->email,
			'code'             => $body->code,
			'authenticate_url' => get_site_url() . '/?user_id=' . base64_encode( $body->user_id ) . '&user_auth=' . base64_encode( $body->code ),
		];

		$headers   = [];
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: NAT64Check <' . get_bloginfo( 'admin_email' ) . '>';

		$subject = 'Account activation';


		ob_start();
		include( locate_template( 'partials/mail/mail-register.php' ) );
		$message = ob_get_contents();
		ob_end_clean();

		wp_mail( $body->email, $subject, $message, $headers );

		return new WP_REST_Response( $body, 200 );

	}
}

$GLOBALS['nat_user'] = new nat_user();
