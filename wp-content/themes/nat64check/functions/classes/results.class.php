<?php

class nat_results {
	function __construct() {
		add_action( 'wp_ajax_generating_results', [ $this, 'generating_results' ] );
		add_action( 'wp_ajax_nopriv_generating_results', [ $this, 'generating_results' ] );
	}

	function generating_results() {

		global $nat_api;
		$token = '';
		if ( is_user_logged_in() ) {
			$token = 'user';
		}
		$values = (object) [
			'args'         => [],
			'servers'      => json_decode( $nat_api->request( 'trillians/?is_alive=2', $token )['body'] )->results,
			'url_test'     => '',
			'hostname'     => [],
			'results_page' => get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-results.php' ] ),
			'redirect_to'  => site_url(),
			'create_test'  => '',
			'test_name'    => '',
			'test_id'      => '',
			'owner_id'     => '',
			'result'       => '',
		];

		if ( isset( $values->results_page[0] ) ) {
			$values->redirect_to = get_permalink( $values->results_page[0]->ID );
		}

		foreach ( $values as $key => $val ) {
			if ( isset( $_GET[ $key ] ) ) {
				$values->$key = esc_sql( $_GET[ $key ] );
			}
		}

		if ( ! $values->hostname ) {
			foreach ( $values->servers as $server ) {
				$values->hostname[] = $server->_url;
			}
		}
		if ( is_string( $values->hostname ) ) {
			$values->hostname = explode( ', ', $values->hostname );
		}

		if ( $values->url_test != '' && ! preg_match( "~^(?:f|ht)tps?://~i", $values->url_test ) ) {
			$values->url_test = "https://" . $values->url_test;
		}


		$values->args        = [ 'body' => [ 'url' => $values->url_test, 'trillians' => $values->hostname ] ];
		$values->create_test = $nat_api->request( 'testruns/', $token, $values->args, 'post' );
		$values->test_name   = json_decode( $values->create_test['body'] )->url;
		$values->test_id     = json_decode( $values->create_test['body'] )->id;
		$values->owner_id    = json_decode( $nat_api->request( 'testruns/' . $values->test_id . '/', $token )['body'] )->owner_id;

		$loop = true;
		$i    = 0;
		while ( $loop ) {
			$i ++;
			$values->result = $nat_api->request( 'testruns/' . $values->test_id . '/', $token )['body'];

			if ( is_wp_error( json_decode( $values->result->finished ) ) ) {
				sleep( 1 );
			} else if ( json_decode( $values->result )->finished ) {
				$url_test = base64_encode( json_decode( $values->result )->url );
				$loop     = false;
			} else {
				sleep( 1 );
			}
			if ( $i == 300 ) {
				$loop = false;
			}
		}

		$values->test_id = base64_encode( $values->test_id );
		if ( $i == 300 ) { ?>
            <div class="block-loading bg-high">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="test-sort text-center">
                                <h2>There is something wrong, test could not be complete!</h2>
                                <h2>please try again later!</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		} else { ?>
            <div class="block-loading bg-high">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="test text-center">
                                <i class="fa fa-check inline-block"></i>
                                <h2 class="inline-block">Image matching...</h2>
                            </div>
                            <div class="test text-center">
                                <i class="fa fa-check inline-block"></i>
                                <h2 class="inline-block">Resource matching...</h2>
                            </div>
                            <div class="test text-center">
                                <i class="fa fa-check inline-block"></i>
                                <h2 class="inline-block">Checking DNS Records...</h2>
                            </div>
                            <div class="test text-center">
                                <i class="fa fa-check inline-block"></i>
                                <h2 class="inline-block">Checking ping times...</h2>
                            </div>
                            <div class="test-sort text-center">
                                <h2>Test complete!</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block-loading-results">
                <p>Your report is ready!</p>
                <p>You will be redirected in <span class="span">5 seconds</span>...</p>
                <a id="result_url" class="button bg-secondary"
                   href="<?php echo $values->redirect_to . '?test_id=' . $values->test_id . '&url_test=' . $values->url_test . '' ?>"><i
                            class="fa fa-file-text-o" aria-hidden="true"></i> View your report</a>
            </div>
			<?php
			exit;
		}
	}
}

$GLOBALS['nat_results'] = new nat_results();
