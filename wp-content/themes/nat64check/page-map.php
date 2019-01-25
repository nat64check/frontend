<?php
/* Template Name: Map */
get_header();

global $nat_api;
$token = '';
if ( is_user_logged_in() ) {
	$token = 'user';
}
$get_args = (object) [
	'server_search' => '',
];

foreach ( $get_args as $k => $v ) {
	if ( ! empty( $_GET[ $k ] ) ) {
		$get_args->$k = esc_attr( $_GET[ $k ] );
	}
}
$server_query = '';
if ( $get_args->server_search ) {
	$server_query = 'hostname__icontains=' . $get_args->server_search . '&';
}

$test_locations = json_decode( $nat_api->request( 'trillians/?' . $server_query . 'expand=admins&is_alive=2', $token )['body'] )->results;
?>

    <section class="section maps block-allsearches">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="locations-map">
						<?php
						foreach ( $test_locations as $test_location ) {
							$sub   = substr( $test_location->location, strpos( $test_location->location, '(' ) + strlen( '(' ), strlen( $test_location->location ) );
							$coord = explode( ' ', substr( $sub, 0, strpos( $sub, ')' ) ) );
							$lat   = $coord[1];
							$lng   = $coord[0];
							?>
                            <div class="marker" data-bg="world" data-lat="<?php echo $lat; ?>"
                                 data-lng="<?php echo $lng; ?>"></div>
							<?php
						}
						?>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-highest flexible-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <form id="server-filters" class="content-padding" action="<?php the_permalink(); ?>"
                              method="get">
                            <div class="search-input">
                                <input type="search" placeholder="Server name" name="server_search"
                                       value="<?php echo $get_args->server_search; ?>">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="info-row bg-white">
            <div class="container">
                <div class="row">
                    <div class="col-sm-5">
                        <h3 clas>Website</h3>
                    </div>
                    <div class="col-sm-4 text-center date">
                        <h3>Location</h3>
                    </div>
                    <div class="col-sm-3">
                        <div class="col-sm-6 text-center inline-block">
                            <h3>Owner</h3>
                        </div>
                        <div class="col-sm-5 text-center inline-block">
                            <h3>Contact</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
		$cnt = 0;
		foreach ( $test_locations as $test_location ) {
			if ( $cnt ++ % 2 == 1 ) {
				$bg_color = 'bg-high';
			} else {
				$bg_color = 'bg-low';
			}
			?>
            <div class="result-row <?php echo $bg_color; ?> content-padding-sm">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="website-flag inline-block"><p class="inline-block"><img
                                            src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/<?php echo strtolower( $test_location->country ); ?>.svg"
                                            alt=""></p></div>
                            <a href="<?php echo 'https://' . $test_location->hostname; ?>"
                               target="_blank"><?php echo $test_location->hostname; ?><i class="fa fa-link"
                                                                                         aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-4 text-center date">
							<?php echo get_option( 'nat_country_list' )[ $test_location->country ]; ?>
                        </div>
                        <div class="col-sm-3">
                            <div class="col-sm-6 text-center inline-block">
								<?php echo $test_location->name; ?>
                            </div>
                            <div class="col-sm-5 text-center inline-block">
                                <a href="mailto:<?php echo $test_location->admins[0]->email; ?>"><i
                                            class="fa fa-envelope-o" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
		?>
    </section>

<?php get_footer(); ?>
