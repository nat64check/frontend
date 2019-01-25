<?php

require_once 'MaxWPOptions.class.php';

class max_wp_comments {
	function __construct() {
		add_filter( 'max_wp_option_pages', [ $this, 'options' ] );

		add_action( 'wp_loaded', [ $this, 'recaptcha_check' ] );

		add_filter( 'get_default_comment_status', [ $this, 'default_comment_status' ], 10, 2 );
	}

	static function recaptcha_show( $args = [] ) {
		$args = (object) array_merge( [
			'style' => [],
			'key'   => maxwp_get_option( 'recaptcha_key' ),
		], $args );

		$css = '';

		if ( $args->style && count( $args->style ) > 0 ) {
			foreach ( $args->style as $key => $val ) {
				$css .= $key . ': ' . $val . '; ';
			}

			$css = trim( strtolower( $css ) );
		}

		$args->key = apply_filters( 'max_wp_comments_recaptcha_key', $args->key );

		if ( $args->key && ! is_user_logged_in() ) {
			?>
            <div style="<?php echo $css; ?>" class="g-recaptcha" data-sitekey="<?php echo $args->key; ?>"></div>
            <script src='https://www.google.com/recaptcha/api.js'></script>
			<?php
		}
	}

	function options( $pages ) {
		$fields = [
			[
				'type'  => 'checkbox',
				'title' => 'Reacties standaard gesloten',
				'name'  => 'comments_default_off',
			],
			[
				'title' => 'Recaptcha Site key',
				'name'  => 'recaptcha_key',
			],
			[
				'title' => 'Recaptcha Secret key',
				'name'  => 'recaptcha_secret',
			],
		];

		$pages[] = [
			'title'    => 'Instellingen',
			'parent'   => 'edit-comments.php',
			'sections' => [
				'Instellingen' => [
					'desc'   => 'Recaptcha key en secret kan hier aangemaakt worden: <a href="https://www.google.com/recaptcha/" target="_blank">https://www.google.com/recaptcha/</a>',
					'fields' => $fields,
				],
			],
		];

		return $pages;
	}

	function default_comment_status( $status, $post_type ) {
		if ( maxwp_get_option( 'comments_default_off' ) ) {
			$status = 'closed';
		}

		return $status;
	}

	function recaptcha_check() {
		if ( $_SERVER['REQUEST_URI'] == '/wp-comments-post.php' && count( $_POST ) && ! self::recaptcha_valid() ) {
			wp_die( '<strong>FOUT</strong> Het lijk erop dat u een spamreactie probeert te versturen.' );
		}
	}

	function recaptcha_valid() {
		if ( is_user_logged_in() ) {
			return true;
		}

		$recaptcha_secret = apply_filters( 'max_wp_comments_recaptcha_secret', maxwp_get_option( 'recaptcha_secret' ) );

		$allow = true;

		if ( $recaptcha_secret && isset( $_POST['g-recaptcha-response'] ) && $_POST['g-recaptcha-response'] != '' ) {
			$result = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
				'body' => [
					'secret'   => $recaptcha_secret,
					'response' => $_POST['g-recaptcha-response'],
					'remoteip' => $_SERVER['REMOTE_ADDR'],
				],
			] );

			if ( ! is_wp_error( $result ) ) {
				$result = json_decode( $result['body'] );

				if ( isset( $result->success ) && ! $result->success ) {
					$allow = false;
				}
			}
		} else if ( $recaptcha_secret ) {
			$allow = false;
		}

		return $allow;
	}

	function get_comments( $post_id = false ) {
		if ( ! $post_id ) {
			$post_id = get_the_id();
		}

		$comments = get_comments( [
			'status'  => 'approve',
			'post_id' => $post_id,
			'order'   => 'ASC',
		] );

		$orderd_comments = [];

		foreach ( $comments as $comment ) {

			if ( $comment->comment_parent == 0 ) {
				$comment->comment_depth = 1;

				$orderd_comments[] = $comment;
			}

			foreach ( $comments as $comment_child ) {
				if ( $comment_child->comment_parent == $comment->comment_ID ) {
					$comment_child->comment_depth = $this->comment_depth( $comment_child->comment_ID );

					$orderd_comments[] = $comment_child;
				}
			}
		}

		return $orderd_comments;
	}

	function comment_depth( $comment_id = false ) {
		$depth = 0;

		while ( $comment_id > 0 ) {
			$comment = get_comment( $comment_id );

			$comment_id = $comment->comment_parent;

			$depth ++;
		}

		return $depth;
	}
}

$GLOBALS['max_wp_comments'] = new max_wp_comments;
