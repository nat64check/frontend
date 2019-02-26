<?php

global $nat_api;

$token = '';
//$server_setting = 'z';
if ( is_user_logged_in() ) {
	$token = 'user';
//	$server_setting = get_post_meta( max_wp_user_prop( 'connect_user' ), 'user_settings' )[0]->server_setting;
}
$get_args = (object) [
	'test_search' => '',
	'test'        => '',
	'score'       => '',
	'paging'      => '1',
];
foreach ( $get_args as $k => $v ) {
	if ( ! empty( $_GET[ $k ] ) ) {
		$get_args->$k = esc_attr( $_GET[ $k ] );
	}
}
$actives = (object) [
	'nat64' => '',
	'ipv6'  => '',
	'poor'  => '',
	'okay'  => '',
	'good'  => '',
];
foreach ( $actives as $k => $v ) {

	if ( $get_args->score == $k ) {
		$actives->$k = 'active';
	}
	if ( $get_args->test == $k ) {
		$actives->$k = 'active';
	}
}

$search    = '';
$get_score = '';
if ( $get_args->test_search || $get_args->test || $get_args->score ) {
	if ( $get_args->test_search ) {
		$search = 'url__icontains=' . $get_args->test_search . '&';
	}
	if ( $get_args->test || $get_args->score ) {

		if ( $get_args->test == 'nat64' ) {
			if ( $get_args->score == 'poor' ) {
				$get_score = 'ordering=nat64_overall_score';
			} else if ( $get_args->score == 'okay' ) {
				$get_score = 'ordering=nat64_overall_score&nat64_overall_score__isnull=3&';
			} else if ( $get_args->score == 'good' ) {
				$get_score = 'ordering=-nat64_overall_score&nat64_overall_score__isnull=3&';
			}
		} else if ( $get_args->test == 'ipv6' ) {
			if ( $get_args->score == 'poor' ) {
				$get_score = 'ordering=v6only_overall_score';
			} else if ( $get_args->score == 'okay' ) {
				$get_score = 'ordering=v6only_overall_score&v6only_overall_score__isnull=3&';
			} else if ( $get_args->score == 'good' ) {
				$get_score = 'ordering=-v6only_overall_score&v6only_overall_score__isnull=3&';
			}
		}
	}
}

$tests = json_decode( $nat_api->request( 'testruns/?finished__isnull=3&ordering=-finished&' . $search . $get_score . 'is_public=true&only=id,owner_id,finished,url,trillians,is_public', $token )['body'] )->results;


$values = (object) [
	'tests'         => $tests,
	'servers'       => json_decode( $nat_api->request( 'trillians/?is_alive=2', $token )['body'] )->results,
	'country_names' => get_option( 'nat_country_list' ),
	'generate_page' => get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'generating-results.php' ] ),
	'redirect_to'   => site_url(),
	'bg_color'      => 'bg-low',
	'count'         => 0,
	'country'       => 'nl',
];

if ( isset( $values->generate_page[0] ) ) {
	$values->redirect_to = get_permalink( $values->generate_page[0]->ID );
}
//if( $server_setting ){
//	$values->servers = array( json_decode( $nat_api->request( 'trillians/'.$server_setting, $token )[ 'body' ] ) );
//}
?>
<div class="block-allsearches">
    <form id="test-form" action="<?php echo $values->redirect_to; ?>" method="get">
        <div class="container">
            <div id="server-select" class="multiselect" data-server_count="<?php echo count( $values->servers ); ?>">
                <div class="selectBox">
                    <div class="overSelect">All locations</div>
                </div>
                <div class="checkboxes">
                    <table>
                        <tbody>
						<?php
						foreach ( $values->servers as $server ) { ?>
                            <tr>
                                <td>
                                    <input class="css-checkbox" type="checkbox" name="hostname[]"
                                           id="test-server-<?php echo $server->hostname; ?>"
                                           value="<?php echo $server->_url; ?>"/>
                                    <label class="css-label"
                                           for="test-server-<?php echo $server->hostname; ?>"><?php echo '<p class="inline-block"><img src="' . get_stylesheet_directory_uri() . '/graphics/' . strtolower( $server->country ) . '.svg" alt=""></p>'; ?><?php echo $values->country_names[ $server->country ]; ?></label>
                                </td>
                            </tr>
							<?php
						}
						?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="main-search-logo text-center container">
            <a id="logo" href="<?php echo home_url( '/' ); ?>"
               title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><img
                        src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo.png"
                        alt="<?php bloginfo( 'name' ); ?>"/></a>
        </div>
        <div class="container">
            <div class="main-search flex-container justify-content-center">
                <div class="search-input">
                    <input type="search" placeholder="https://www.example.com" name="url_test"
                           value="<?php echo $get_args->url_test; ?>">
                    <button type="submit"><i class="fa fa-check"></i></button>
                </div>
            </div>
        </div>
    </form>
    <div class="title bg-dark">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <p>All searches</p>
                </div>
            </div>
        </div>
    </div>
    <form id="test-filters" action="<?php the_permalink(); ?>" method="get">
        <div class="buttons-row bg-high">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="search-button button inline-block">
                            <div class="search-input">
                                <input type="search" placeholder="search" name="test_search"
                                       value="<?php echo $get_args->test_search; ?>">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
						<?php
						$actives = (object) [
							'nat64' => '',
							'ipv6'  => '',
							'poor'  => '',
							'okay'  => '',
							'good'  => '',
						];
						foreach ( $actives as $k => $v ) {

							if ( $get_args->score == $k ) {
								$actives->$k = 'active';
							}
							if ( $get_args->test == $k ) {
								$actives->$k = 'active';
							}
						}
						?>
                        <div class="buttons inline-block">
                            <a href="#"
                               class="test button bg-dark <?php echo $actives->nat64 ?> inline-block color-black">NAT64</a>
                            <a href="#"
                               class="test button bg-highest <?php echo $actives->ipv6 ?> inline-block color-black">IPv6</a>
                            <a href="#"
                               class="score button bg-secondary <?php echo $actives->poor ?> inline-block">Poor</a>
                            <a href="#"
                               class="score button bg-accent <?php echo $actives->okay ?> inline-block">Okay</a>
                            <a href="#"
                               class="score button bg-primary <?php echo $actives->good ?> inline-block">Good</a>
                            <input type="hidden" class="test-value" name="test" value="<?php echo $get_args->test; ?>"/>
                            <input type="hidden" class="score-value" name="score"
                                   value="<?php echo $get_args->score; ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="info-row bg-white">
            <div class="container">
                <div class="row">
                    <div class="col-sm-5">
                        <h3>Website</h3>
                    </div>
                    <div class="col-sm-4 text-center date">
                        <h3>Date / Time</h3>
                    </div>
                    <div class="col-sm-3">
                        <div class="col-sm-6 text-center inline-block">
                            <h3>NAT64</h3>
                        </div>
                        <div class="col-sm-5 text-center inline-block">
                            <h3>IPv6</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
		$paging          = ( object ) [
			'pp'   => 6,
			'page' => isset( $get_args->paging ) ? intval( $get_args->paging - 1 ) : 0,
		];
		$number_of_pages = ( count( $values->tests ) / $paging->pp ) + 1;

		foreach ( array_slice( $values->tests, $paging->page * $paging->pp, $paging->pp ) as $test ) {
			if ( $values->count ++ % 2 == 1 ) {
				$values->bg_color = 'bg-high';
			} else {
				$values->bg_color = 'bg-low';
			}

			if ( $test->trillians && isset( $test->trillians[0] ) ) {
				$values->country = json_decode( $nat_api->request( $test->trillians[0], $token )['body'] )->country;
			}

//			$instanceruns = json_decode( $nat_api->request( 'testruns/'.$test->id.'/?expand=instanceruns__results&only=instanceruns__results__instance_type,instanceruns__results__overall_score', $values->token )['body'] )->instanceruns;
//			
//			$nat_average = array();
//			$ipv6_average = array();
//			foreach( $instanceruns as $instancerun ){
//				foreach( $instancerun->results as $result ){
//					if( $result->instance_type == 'nat64' ){
//						$nat_average[] = (int) round( $result->overall_score * 100 );
//					}
//					elseif( $result->instance_type == 'v6only' ){
//						$ipv6_average[] = (int) round( $result->overall_score * 100 );
//					}
//				}
//			}
			$nat_icon  = '<i class="fa fa-minus" aria-hidden="true"></i>';
			$ipv6_icon = '<i class="fa fa-minus" aria-hidden="true"></i>';

			if ( (int) round( $test->nat64_overall_score ) * 100 >= 85 ) {
				$nat_icon = '<i class="fa fa-check" aria-hidden="true"></i>';
			} else if ( (int) round( $test->nat64_overall_score ) * 100 < 60 ) {
				$nat_icon = '<i class="fa fa-times" aria-hidden="true"></i>';
			}
			if ( (int) round( $test->v6only_overall_score ) * 100 >= 85 ) {
				$ipv6_icon = '<i class="fa fa-check" aria-hidden="true"></i>';
			} else if ( (int) round( $test->v6only_overall_score ) * 100 < 60 ) {
				$ipv6_icon = '<i class="fa fa-times" aria-hidden="true"></i>';
			}

			?>
            <div class="result-row <?php echo $values->bg_color; ?>">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="website-flag inline-block"><p class="inline-block"><img
                                            src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/<?php echo strtolower( $values->country ); ?>.svg"
                                            alt=""></p></div>
                            <a href="<?php echo site_url() . '/results/?test_id=' . base64_encode( $test->id ) . ''; ?>"><?php echo $test->url; ?>
                                <i class="fa fa-link" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-4 text-center date">
							<?php
							if ( $test->finished ) {
								echo date_i18n( 'd F Y, H:i', strtotime( $test->finished ) );
							}
							?>
                        </div>
                        <div class="col-sm-3">
                            <div class="col-sm-6 nat-compatible text-center inline-block">
								<?php echo $nat_icon; ?>
                            </div>
                            <div class="col-sm-5 ipv6-compatible text-center inline-block">
								<?php echo $ipv6_icon; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
		if ( $number_of_pages > 2 ) { ?>
            <div class="bg-mid">
                <div class="container">
                    <ul id="paginator" class="content-padding-sm flex-container justify-content-end">
						<?php
						for ( $i = 1; $i < $number_of_pages; $i ++ ) {
							$active = '';
							if ( isset( $get_args->paging ) && intval( $get_args->paging ) == $i ) {
								$active = 'active';
							} else if ( ! isset( $get_args->paging ) && $i == 1 ) {
								$active = 'active';
							}
							//$get_args->paging = $i;

							?>
                            <li>
                                <a class="<?php echo $active; ?>" href='#'><?php echo $i; ?></a>
                                <input type="hidden" class="paging-value" name="paging"
                                       value="<?php echo $get_args->paging; ?>"/>
                            </li>

							<?php
						} ?>
                    </ul>
                </div>
            </div>
			<?php
		}
		?>
    </form>
</div>
