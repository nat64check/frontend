<?php
/* Template Name: Generating results */

get_header();

global $nat_api;
$token = '';
if ( is_user_logged_in() ) {
	$token = 'user';
}
$values = (object) [
	'url_test' => '',
	'hostname' => [],
	'servers'  => json_decode( $nat_api->request( 'trillians/?is_alive=2', $token )['body'] )->results,
];

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

if ( $values->url_test != '' && ! preg_match( "~^(?:f|ht)tps?://~i", $values->url_test ) ) {
	$values->url_test = "https://" . $values->url_test;
}
?>
    <input id="get_url_test" type="hidden" value="<?php echo $values->url_test; ?>">
    <input id="get_hostname" type="hidden" value="<?php echo implode( ', ', $values->hostname ); ?>">
    <div id="response">
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
        <div class="block-loading-results">
            <p class="saving">Your report is being generated <span>.</span><span>.</span><span>.</span></p>
            <p>Please wait!</p>
        </div>
    </div>
<?php get_footer(); ?>
