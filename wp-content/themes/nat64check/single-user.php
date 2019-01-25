<?php
acf_form_head();
get_header();
$user_id = max_wp_user_prop( 'connect_user' );


if ( is_user_logged_in() && $user_id ) {

	$get_args = (object) [
		'history' => '',
	];
	foreach ( $get_args as $k => $v ) {
		if ( ! empty( $_GET[ $k ] ) ) {
			$get_args->$k = esc_attr( $_GET[ $k ] );
		}
	}

	$owner_id = get_field( 'api_user', 'user_' . get_current_user_id() );
	if ( $get_args->history ) {
		$tests = json_decode( $nat_api->request( 'testruns/?&ordering=-finished&owner=' . $owner_id . '&url__icontains=' . $get_args->history . '&only=id,owner_id,finished,url,trillians', 'user' )['body'] )->results;
	} else {
		$tests = json_decode( $nat_api->request( 'testruns/?&ordering=-finished&owner=' . $owner_id . '&only=id,owner_id,finished,url,trillians', 'user' )['body'] )->results;
	}

	$country_names = json_decode( file_get_contents( "http://country.io/names.json" ), true );

	$user_args = [
		'post_id'      => $user_id,
		'field_groups' => [ 'group_5b9ba666edeee' ],
	];

	$pages = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'page-results.php',
	] );

	$redirect_to = site_url();

	if ( isset( $pages[0] ) ) {
		$redirect_to = get_permalink( $pages[0]->ID );
	}
	?>
    <div class="user-checks bg-high">
        <div class="container">
            <div class="content">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="title">
                            <h2>Your schedules</h2>
                            <p>Here you can add/remove schedules to test your websites</p>
                            <p> </p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
						<?php acf_form( $user_args ); ?>
                        <!--						<a href="#" id="add-schedule"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>-->
                    </div>

                </div>
            </div>
        </div>
    </div>
	<?php
	if ( $tests ) { ?>
        <form id="test-form" action="<?php echo $values->redirect_to; ?>" method="get">
            <div class="history bg-mid">
                <div class="container">
                    <div class="content">
                        <div class="row">
                            <div class="col-sm-12">
                                <h2>History</h2>
                            </div>
                        </div>
                        <div class="row flex-container">
                            <div class="col-sm-6 flex-container">
                                <div class="search-button button inline-block">
                                    <input type="search" placeholder="search" name="history"
                                           value="<?php echo $get_args->history; ?>">
                                    <button type="submit"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="history-result-titles bg-white">
            <div class="container">
                <div class="row">
                    <div class="col-sm-5">
                        <h4>Website</h4>
                    </div>
                    <div class="col-sm-1 text-center">
                        <h4>NAT64</h4>
                    </div>
                    <div class="col-sm-1 text-center">
                        <h4>IPv6</h4>
                    </div>
                    <div class="col-sm-2 text-center">
                        <h4>View report</h4>
                    </div>
                    <div class="col-sm-3">
                        <h4>Latest activity</h4>
                    </div>
                </div>
            </div>
        </div>
		<?php
		$row = ( object ) [
			'bg_color' => 'bg-low',
			'country'  => 'nl',
			'nat_icon' => '<i class="fa fa-spinner fa-pulse fa-1x fa-fw inline-block"></i>',
			'v6_icon'  => '<i class="fa fa-spinner fa-pulse fa-1x fa-fw inline-block"></i>',
			'activity' => '<i class="fa fa-spinner fa-pulse fa-1x fa-fw inline-block"></i> Checking...',
			'cnt'      => 0,
			'get_var'  => 1,
		];
		if ( ! empty( $_GET['paging'] ) ) {
			$row->get_var = esc_html( $_GET['paging'] );
		}
		$paging = ( object ) [
			'pp'   => 6,
			'page' => isset( $row->get_var ) ? intval( $row->get_var - 1 ) : 0,
		];

		$number_of_pages = ( count( $tests ) / $paging->pp ) + 1;

		foreach ( array_slice( $tests, $paging->page * $paging->pp, $paging->pp ) as $test ) {

			if ( $test->trillians && isset( $test->trillians[0] ) ) {
				$row->country = json_decode( $nat_api->request( $test->trillians[0], 'user' )['body'] )->country;
			}

			if ( $row->cnt ++ % 2 == 1 ) {
				$row->bg_color = 'bg-high';
			} else {
				$row->bg_color = 'bg-low';
			}

			if ( $test->finished ) {

				if ( $test->v6only_overall_score * 100 >= 85 ) {
					$row->v6_icon = '<i class="fa fa-check" aria-hidden="true"></i>';
				} else if ( $test->v6only_overall_score * 100 < 60 ) {
					$row->v6_icon = '<i class="fa fa-times" aria-hidden="true"></i>';
				} else if ( $test->v6only_overall_score * 100 > 60 && $test->v6only_overall_score * 100 < 85 ) {
					$row->v6_icon = '<i class="fa fa-minus" aria-hidden="true"></i>';
				}

				if ( $test->nat64_overall_score * 100 >= 85 ) {
					$row->nat_icon = '<i class="fa fa-check" aria-hidden="true"></i>';
				} else if ( $test->nat64_overall_score * 100 < 60 ) {
					$row->nat_icon = '<i class="fa fa-times" aria-hidden="true"></i>';
				} else if ( $test->nat64_overall_score * 100 > 60 && $test->nat64_overall_score * 100 < 85 ) {
					$row->nat_icon = '<i class="fa fa-minus" aria-hidden="true"></i>';
				}

			}

			?>
            <div class="history-result-row <?php echo $row->bg_color; ?>">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="website-flag inline-block"><p class="inline-block"><img
                                            src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/<?php echo strtolower( $row->country ); ?>.svg"
                                            alt=""></p></div>
                            <a href="<?php echo $test->url; ?>" target="_blank"><?php echo $test->url; ?><i
                                        class="fa fa-link" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-1 text-center">
							<?php echo $row->nat_icon; ?>
                        </div>
                        <div class="col-sm-1 text-center">
							<?php echo $row->v6_icon; ?>
                        </div>
                        <div class="col-sm-2 text-center">
                            <a href="<?php echo $redirect_to . '?test_id=' . base64_encode( $test->id ) . '&url_test=' . base64_encode( $test->url ) . '' ?>">
                                <i class="fa fa-file-text" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div class="col-sm-3 ">
							<?php
							if ( $test->finished ) {
								$row->activity = date_i18n( 'd F Y', strtotime( $test->finished ) );
							}
							echo $row->activity;
							?>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
		if ( $number_of_pages > 2 ) {
			?>
            <div class="bg-mid">
                <div class="container">
                    <ul id="paginator" class="content-padding-sm flex-container justify-content-end">
						<?php
						for ( $i = 1; $i < $number_of_pages; $i ++ ) {
							$active = '';
							if ( isset( $row->get_var ) && intval( $row->get_var ) == $i ) {
								$active = 'active';
							} else if ( ! isset( $row->get_var ) && $i == 1 ) {
								$active = 'active';
							}

							?>
                            <li>
                                <a class="<?php echo $active; ?>" href='./?paging=<?= $i ?>'><?php echo $i; ?></a>
                            </li>
							<?php
						} ?>
                    </ul>
                </div>
            </div>
			<?php
		}
	}
} else { ?>
    <div class="user-checks bg-high">
        <div class="container">
            <div class="content">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="title">
                            <h2>You are not loged in, or do not have access to this dashboard!</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
}
get_footer(); ?>
