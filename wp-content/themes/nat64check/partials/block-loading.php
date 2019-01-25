<?php
$pages       = get_pages( [
	'meta_key'   => '_wp_page_template',
	'meta_value' => 'page-results.php',
] );
$redirect_to = site_url();
if ( isset( $pages[0] ) ) {
	$redirect_to = get_permalink( $pages[0]->ID );
}

$get_args = (object) [
	'url_test' => '',
	'hostname' => '',
];

foreach ( $get_args as $key => $val ) {
	if ( isset( $_GET[ $key ] ) ) {
		$get_args->$key = esc_sql( $_GET[ $key ] );
	}
}
global $nat_api;


$args = [
	'body' => [
		'url'       => $get_args->url_test,
		'trillians' => $get_args->hostname,
	],
];

$create_test = $nat_api->request( 'testruns/', $nat_api->full_token, $args, 'post' );

$test_name = json_decode( $create_test['body'] )->url;
$test_id   = json_decode( $create_test['body'] )->id;

$owner_id = json_decode( $nat_api->request( 'testruns/' . $test_id . '/', $nat_api->full_token )['body'] )->owner_id;


?>
<div class="block-loading bg-high">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="test-sort text-center">
                    <h2>Running easy test...</h2>
                </div>
                <div class="test text-center">
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw inline-block"></i>
                    <h2 class="inline-block">Image matching...</h2>
                </div>
                <div class="test text-center">
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw inline-block"></i>
                    <h2 class="inline-block">Resource matching...</h2>
                </div>
                <div class="test text-center">
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw inline-block"></i>
                    <h2 class="inline-block">Checking DNS Records...</h2>
                </div>
                <div class="test text-center">
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw inline-block"></i>
                    <h2 class="inline-block">Checking ping times...</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$loop = true;

while ( $loop ) {
	if ( is_wp_error( $request ) ) {
		sleep( 1 );
	} else if ( json_decode( $url_test = $nat_api->request( 'testruns/' . $test_id . '/', $nat_api->full_token, $args )['body'] )->url ) {
		$loop = false;
	} else {
		sleep( 1 );
	}
}

//make the report and fill the meta
//$post_args = array(
//	'post_title'	=> $test_name,
//	'post_status'	=> 'publish',
//	'post_type'		=> 'report'
//);
//
//$post_id = wp_insert_post( $post_args );
//update_field( 'api_post_id', $test_id, $post_id );
//update_field( 'api_user_id', $owner_id, $post_id );
//update_field( 'api_test_server', $test_server, $post_id );
//$redirect_to = get_permalink( $post_id );
$test_id = base64_encode( $test_id );
?>
<a class="button bg-primary" href="<?php echo $redirect_to . '?test_id=' . $test_id . '&url_test=' . $url_test . '' ?>">see
    report!</a>

<!--<div id="response"></div>-->
