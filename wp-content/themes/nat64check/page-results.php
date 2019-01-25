<?php
/* Template Name: Results */
get_header();

global $nat_api;

$values = (object) [
	'grey'         => '#999999',
	'yellow'       => '#FCB725',
	'red'          => '#E20000',
	'faq_id'       => get_category_by_slug( 'faq' )->term_id,
	'blog_id'      => get_category_by_slug( 'blog' )->term_id,
	'test_id'      => base64_decode( esc_html( $_GET['test_id'] ) ),
	'token'        => '',
	'results'      => '',
	'dns_results'  => '',
	'v4_image'     => [],
	'v4_resource'  => [],
	'v4_duration'  => [],
	'v4_overall'   => [],
	'v4_png'       => '',
	'v6_image'     => [],
	'v6_resource'  => [],
	'v6_duration'  => [],
	'v6_overall'   => [],
	'v6_png'       => '',
	'nat_image'    => [],
	'nat_resource' => [],
	'nat_duration' => [],
	'nat_overall'  => [],
	'nat_png'      => '',
	'dual_png'     => '',
	'average'      => '',
	'text'         => '',
	'bg'           => '',
	'icon'         => '',
	'messages'     => [],
	'maps'         => [],
];

if ( is_user_logged_in() ) {
	$values->token = 'user';
}

$get_args = (object) [
	'server'  => '',
	'test_id' => $values->test_id,
];

foreach ( $get_args as $k => $v ) {
	if ( ! empty( $_GET[ $k ] ) ) {
		$get_args->$k = esc_attr( $_GET[ $k ] );
	}
}

$ecxlude     = '';
$map_overall = [];

if ( $get_args->server == '' ) {
	$ecxlude = '&exclude=instanceruns__results__web_response__image';
}

$main_request     = json_decode( $nat_api->request( 'testruns/' . $values->test_id . '/?expand=messages,instanceruns__results,trillians' . $ecxlude, $values->token )['body'] );
$values->messages = $main_request->messages;
$instanceruns     = $main_request->instanceruns;
$test_locations   = $main_request->trillians;


foreach ( $instanceruns as $instance ) {
	$map_values = (object) [
		'tid'     => $instance->trillian_id,
		'overall' => [],
	];

	foreach ( $instance->results as $overall ) {

		$map_values->overall[] = $overall->overall_score;
	}
	$map_values->overall = (int) round( nat_get_average( $map_values->overall ) * 100 );
	$map_overall[]       = $map_values;
}


if ( $get_args->server == '' ) {
	$server = '<i class="fa fa-globe" aria-hidden="true"></i> All locations';
} else {
	foreach ( $test_locations as $server ) {
		if ( $server->id == $get_args->server ) {
			$server_object = $server;
		}
	}

	$server = '<p class="inline-block"><img src="' . get_stylesheet_directory_uri() . '/graphics/' . strtolower( $server_object->country ) . '.svg" alt=""></p>' . get_option( 'nat_country_list' )[ $server_object->country ];
	foreach ( $instanceruns as $instancerun ) {
		if ( $instancerun->trillian_id == $get_args->server ) {
			$instanceruns = [ $instancerun ];
		}
	}
}

foreach ( $instanceruns as $instancerun ) {

	$values->results     = $instancerun->results;
	$values->dns_results = $instancerun->dns_results;
	foreach ( $values->results as $result ) {

		if ( $result->instance_type == 'v4only' ) {
			$values->v4_image[]    = (int) round( $result->image_score * 100 );
			$values->v4_resource[] = (int) round( $result->resource_score * 100 );
			$values->v4_duration[] = (int) round( $result->web_response->duration );
			$values->v4_overall[]  = $result->overall_score * 100;
			$values->v4_png        = $result->web_response->image;
		} else if ( $result->instance_type == 'v6only' ) {
			$values->v6_image[]    = (int) round( $result->image_score * 100 );
			$values->v6_resource[] = (int) round( $result->resource_score * 100 );
			$values->v6_duration[] = (int) round( $result->web_response->duration );
			$values->v6_overall[]  = $result->overall_score * 100;
			$values->v6_png        = $result->web_response->image;
		} else if ( $result->instance_type == 'nat64' ) {
			$values->nat_image[]    = (int) round( $result->image_score * 100 );
			$values->nat_resource[] = (int) round( $result->resource_score * 100 );
			$values->nat_duration[] = (int) round( $result->web_response->duration );
			$values->nat_overall[]  = $result->overall_score * 100;
			$values->nat_png        = $result->web_response->image;
		} else if ( $result->instance_type = 'dual-stack' ) {
			$values->dual_png = $result->web_response->image;
		}
	}
}


if ( $values->results ) {
	$res_object = [];

	$values->average = nat_get_average( $values->nat_overall ) + nat_get_average( $values->v6_overall ) + nat_get_average( $values->v4_overall );
	$values->average = (int) round( $values->average / 3 );

	foreach ( $values->results as $req_url ) {
		$res_object[] = (object) [
			'instance_type' => $req_url->instance_type,
			'success'       => $req_url->web_response->success,
			'resources'     => $req_url->web_response->resources,
		];
	}

	foreach ( $res_object as $res ) {
		if ( $res->instance_type == 'dual-stack' ) {

			$dual_stack_urls = [];
			foreach ( $res->resources as $resource ) {
				$dual_stack_urls[] = $resource->request->url;
			}
		} else if ( $res->instance_type == 'nat64' ) {
			$nat64_urls = [];
			foreach ( $res->resources as $resource ) {
				$nat64_urls[] = $resource->request->url;
			}
		} else if ( $res->instance_type == 'v4only' ) {
			$v4only_urls = [];
			foreach ( $res->resources as $resource ) {
				$v4only_urls[] = $resource->request->url;
			}
		} else if ( $res->instance_type == 'v6only' ) {
			$v6only_urls = [];
			foreach ( $res->resources as $resource ) {
				$v6only_urls[] = $resource->request->url;
			}
		}
	}

	if ( $values->average >= 85 ) {
		$values->text = 'GOOD';
		$values->bg   = 'bg-primary';
		$values->icon = 'check';
	} else if ( $values->average <= 60 ) {
		$values->text = 'BAD';
		$values->bg   = 'bg-secondary';
		$values->icon = 'times';
	} else {
		$values->text = 'MODERATE';
		$values->bg   = 'bg-accent';
		$values->icon = 'minus';
	}

	?>
    <div class="block-results bg-high">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="title">
                        <h2>Results</h2>
                    </div>
                    <div class="content">
						<?php
						if ( $main_request->overall_feedback ) {
							echo '<p>' . $main_request->overall_feedback . '</p>';
						}
						?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="button <?php echo $values->bg; ?>">
                        <div class="rating-icon inline-block">
                            <i class="fa fa-<?php echo $values->icon; ?>-circle" aria-hidden="true"></i>
                        </div>
                        <div class="rating-txt inline-block">
                            <p>overall rating</p>
                            <h3><?php echo $values->text; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="server-form" class="block-imagematching bg-mid">
        <form action="<?php the_permalink(); ?>" method="get" class="bg-high">
            <div class="container">
                <div class="row matching-tab">
                    <div class="col-sm-12">
                        <div class="summary-button button bg-dark inline-block">
                            <a href="#">Summary</a>
                        </div>
                        <div class="servers-button inline-block">
                            <div id="server-select" class="multiselect">
                                <input type="hidden" name="server" value="<?php echo $get_args->server; ?>"/>
                                <div class="selectBox">
									<?php echo $server; ?>
                                    <div class="overSelect"></div>
                                </div>
                                <div class="checkboxes">
									<?php
									if ( $get_args->server != '' ) {
										?>
                                        <a href="#"><i class="fa fa-globe" aria-hidden="true"></i> All locations</a>
										<?php
										foreach ( $test_locations as $server_location ) {

											$country_name = get_option( 'nat_country_list' )[ $server_location->country ];
											$country_flag = '<img src="' . get_stylesheet_directory_uri() . '/graphics/' . strtolower( $server_location->country ) . '.svg" alt="">';
											if ( $server_location->id != $get_args->server ) { ?>
                                                <a href="#<?php echo $server_location->id; ?>"><p
                                                            class="inline-block"><?php echo $country_flag; ?></p> <?php echo get_option( 'nat_country_list' )[ $server_location->country ]; ?>
                                                </a>
												<?php
											}
										}
									} else {
										foreach ( $test_locations as $server_location ) {
											$country_name = get_option( 'nat_country_list' )[ $server_location->country ];
											$country_flag = '<img src="' . get_stylesheet_directory_uri() . '/graphics/' . strtolower( $server_location->country ) . '.svg" alt="">';
											?>
                                            <a href="#<?php echo $server_location->id; ?>"><p
                                                        class="inline-block"><?php echo $country_flag; ?></p> <?php echo get_option( 'nat_country_list' )[ $server_location->country ]; ?>
                                            </a>
											<?php
										}
									}
									?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="test_id" value="<?php echo $get_args->test_id; ?>"/>
            </div>
        </form>
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="title">
                        <h2>Image matching</h2>
                    </div>
                    <div class="content">
						<?php
						if ( $main_request->image_feedback ) {
							echo '<p>' . $main_request->image_feedback . '</p>';
						}
						//						else{
						//							echo '<p>Please wait for admin\'s comment on this</p>';
						//						}
						?>
                    </div>
                </div>
				<?php
				if ( $get_args->server == '' ) { ?>
                    <div class="col-lg-6 rating">
                        <div class="rating-nat inline-block">
                            <div class="title">NAT64</div>
                            <div class="big-circle-nat">
                                <div class="ko-progress-circle" data-progress="0">
                                    <div class="ko-circle">
                                        <div class="full ko-progress-circle__slice">
                                            <div class="ko-progress-circle__fill"></div>
                                        </div>
                                        <div class="ko-progress-circle__slice">
                                            <div class="ko-progress-circle__fill"></div>
                                            <div class="ko-progress-circle__fill ko-progress-circle__bar"></div>
                                        </div>
                                    </div>
                                    <div class="ko-progress-circle__overlay">
										<?php
										if ( ! nat_get_average( $values->nat_image ) ) {
											echo '<i class="fa fa-bolt" aria-hidden="true"></i>';
										} else {

											?>
                                            <p class="count"><?php echo nat_get_average( $values->nat_image ); ?>%</p>
											<?php
										}
										?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rating-ipv inline-block">
                            <div class="title">IPv6</div>
                            <div class="big-circle-ipv">
                                <div class="ko-progress-circle" data-progress="0">
                                    <div class="ko-circle">
                                        <div class="full ko-progress-circle__slice">
                                            <div class="ko-progress-circle__fill"></div>
                                        </div>
                                        <div class="ko-progress-circle__slice">
                                            <div class="ko-progress-circle__fill"></div>
                                            <div class="ko-progress-circle__fill ko-progress-circle__bar"></div>
                                        </div>
                                    </div>
                                    <div class="ko-progress-circle__overlay">
										<?php
										if ( ! nat_get_average( $values->v6_image ) ) {
											echo '<i class="fa fa-bolt" aria-hidden="true"></i>';
										} else { ?>
                                            <p class="count"><?php echo nat_get_average( $values->v6_image ); ?>%</p>
											<?php
										}
										?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
				}
				?>
            </div>
        </div>
    </div>
	<?php
	if ( $get_args->server != '' ) {
		$img_values = (object) [
			'v4'      => '',
			'v4_txt'  => nat_get_average( $values->v4_image ) . '%',
			'v6'      => '',
			'v6_txt'  => nat_get_average( $values->v6_image ) . '%',
			'nat'     => '',
			'nat_txt' => nat_get_average( $values->nat_image ) . '%',
		];

		if ( nat_get_average( $values->v4_image ) >= 85 ) {
			$img_values->v4 = 'bg-primary';
		} else if ( nat_get_average( $values->v4_image ) <= 60 ) {
			$img_values->v4 = 'bg-secondary';
			if ( nat_get_average( $values->v4_image ) == 0 ) {
				$img_values->v4_txt = '<i class="fa fa-bolt" aria-hidden="true"></i>';
			}
		} else {
			$img_values->v4 = 'bg-accent';
		}

		if ( nat_get_average( $values->v6_image ) >= 85 ) {
			$img_values->v6 = 'bg-primary';
		} else if ( nat_get_average( $values->v6_image ) <= 60 ) {
			$img_values->v6 = 'bg-secondary';
			if ( nat_get_average( $values->v6_image ) == 0 ) {
				$img_values->v6_txt = '<i class="fa fa-bolt" aria-hidden="true"></i>';
			}
		} else {
			$img_values->v6 = 'bg-accent';
		}

		if ( nat_get_average( $values->nat_image ) >= 85 ) {
			$img_values->nat = 'bg-primary';
		} else if ( nat_get_average( $values->nat_image ) <= 60 ) {
			$img_values->nat = 'bg-secondary';
			if ( nat_get_average( $values->nat_image ) == 0 ) {
				$img_values->nat_txt = '<i class="fa fa-bolt" aria-hidden="true"></i>';
			}
		} else {
			$img_values->nat = 'bg-accent';
		}
		$place_holder_img = get_stylesheet_directory_uri() . "/graphics/Placeholder.png";
		?>
        <div class="block-imagematch content-padding bg-mid">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="img-box">
                            <div class="box-title">
                                <h2 class="flex-container justify-content-center">IPv4</h2>
                                <div class="box-average flex-container justify-content-center align-items-center <?php echo $img_values->v4; ?>"><?php echo $img_values->v4_txt; ?></div>
                            </div>
                            <div class="box-img position-relative hover-blend">
								<?php
								if ( ! $values->v4_png ) { ?>
                                    <img class="width-fill" src="<?php echo $place_holder_img; ?>" alt="ipv4">
									<?php
								} else { ?>
                                    <img class="width-fill" src="data:image/png;base64,<?php echo $values->v4_png; ?>"
                                         alt="ipv4">
									<?php
								}
								?>
                                <img class="width-fill overlay-blend"
                                     src="data:image/png;base64,<?php echo $values->dual_png; ?>" alt="dual-stack">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="img-box">
                            <div class="box-title">
                                <h2 class="flex-container justify-content-center">NAT64</h2>
                                <div class="box-average flex-container justify-content-center align-items-center <?php echo $img_values->nat; ?>"><?php echo $img_values->nat_txt; ?></div>
                            </div>
                            <div class="box-img position-relative hover-blend">
								<?php
								if ( ! $values->nat_png ) { ?>
                                    <img class="width-fill" src="<?php echo $place_holder_img; ?>" alt="nat64">
									<?php
								} else { ?>
                                    <img class="width-fill" src="data:image/png;base64,<?php echo $values->nat_png; ?>"
                                         alt="nat64">
									<?php
								}
								?>
                                <img class="width-fill overlay-blend"
                                     src="data:image/png;base64,<?php echo $values->dual_png; ?>" alt="dual-stack">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="img-box">
                            <div class="box-title">
                                <h2 class="flex-container justify-content-center">IPv6</h2>
                                <div class="box-average flex-container justify-content-center align-items-center <?php echo $img_values->v6; ?>"><?php echo $img_values->v6_txt; ?></div>
                            </div>
                            <div class="box-img position-relative hover-blend">
								<?php
								if ( ! $values->v6_png ) { ?>
                                    <img class="width-fill" src="<?php echo $place_holder_img; ?>" alt="ipv6">
									<?php
								} else { ?>
                                    <img class="width-fill" src="data:image/png;base64,<?php echo $values->v6_png; ?>"
                                         alt="ipv6">
									<?php
								}
								?>
                                <img class="width-fill overlay-blend"
                                     src="data:image/png;base64,<?php echo $values->dual_png; ?>" alt="dual-stack">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
	?>
    <div class="block-resourcematching bg-low">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 col-lg-4">
                    <div class="title">
                        <h2>Resource matching</h2>
                    </div>
                    <div class="content">
						<?php
						if ( $get_args->server != '' ) {
							echo '<p>Overall match</p>';
						} else if ( $main_request->resource_feedback ) {
							echo '<p>' . $main_request->resource_feedback . '</p>';
						}
						//						else{
						//							echo '<p>Please wait for admin\'s comment on this</p>';
						//						}
						if ( $get_args->server != '' ) { ?>
                            <a id="all-res-button" class="button bg-high color-white" href="#">View all resources <i
                                        class="fa fa-caret-down" aria-hidden="true"></i></a>
							<?php
						}
						?>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="matching-nat inline-block">
                        <div class="title">NAT64</div>
						<?php
						if ( $values->nat_resource ) {
							$av_nat_res = nat_get_average( $values->nat_resource );
						} else {
							$av_nat_res = 0;
						}
						?>
                        <div id="progressbar-nat">
                            <div class="progress" value="<?php echo $av_nat_res; ?>"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="matching-ipv inline-block">
                        <div class="title">IPv6</div>
						<?php
						if ( $values->v6_resource ) {
							$av_v6_res = nat_get_average( $values->v6_resource );
						} else {
							$av_v6_res = 0;
						}
						?>
                        <div id="progressbar-ipv">
                            <div class="progress" value="<?php echo $av_v6_res; ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			if ( $get_args->server != '' ) {

				?>
                <div class="container all-res-dropdown">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-row">
                                All resources
                            </div>
                        </div>
                    </div>
                    <div class="row bg-white">
                        <div class="col-sm-6">
                            <h3>URL</h3>
                        </div>
                        <div class="col-sm-2 text-center date">
                            <h3>IPv4</h3>
                        </div>
                        <div class="col-sm-2 text-center">
                            <h3>NAT64</h3>
                        </div>
                        <div class="col-sm-2 text-center inline-block">
                            <h3>IPv6</h3>
                        </div>
                    </div>
					<?php
					$cnt      = 0;
					$bg_color = '';

					foreach ( $dual_stack_urls as $url ) {
						$cnt ++;
						if ( $cnt % 2 == 1 ) {
							$bg_color = 'bg-high';
						} else {
							$bg_color = 'bg-low';
						}
						?>
                        <div class="row result-row <?php echo $bg_color; ?>">
                            <div class="col-sm-6">
                                <a class="color-grey elipsis" href="<?php echo $url; ?>"
                                   target="_blank"><?php echo $url; ?></a>
                            </div>
                            <div class="col-sm-2 ipv4-compatible text-center">
								<?php
								if ( in_array( $url, $v4only_urls ) ) {
									echo '<i class="fa fa-check" aria-hidden="true"></i>';
								} else {
									echo '<i class="fa fa-times" aria-hidden="true"></i>';
								}
								?>
                            </div>
                            <div class="col-sm-2 nat-compatible text-center inline-block">
								<?php
								if ( is_array( $nat64_urls ) ) {
									if ( in_array( $url, $nat64_urls ) ) {
										echo '<i class="fa fa-check" aria-hidden="true"></i>';
									} else {
										echo '<i class="fa fa-times" aria-hidden="true"></i>';
									}
								} else {
									echo '<i class="fa fa-times" aria-hidden="true"></i>';
								}
								?>
                            </div>
                            <div class="col-sm-2 ipv6-compatible text-center inline-block">
								<?php
								if ( is_array( $v6only_urls ) ) {
									if ( in_array( $url, $v6only_urls ) ) {
										echo '<i class="fa fa-check" aria-hidden="true"></i>';
									} else {
										echo '<i class="fa fa-times" aria-hidden="true"></i>';
									}
								} else {
									echo '<i class="fa fa-times" aria-hidden="true"></i>';
								}
								?>
                            </div>

                        </div>
						<?php
					}
					?>
                </div>
				<?php
			}
			?>
        </div>
    </div>

    <div class="block-loadingtimes bg-mid">
        <div class="container">
            <div class="row">
                <div class="col-sm-3">
                    <div class="title">
                        <h2>Loading times</h2>
                    </div>
                    <div class="content">
                        <!--						<p>a comment about loading times</p>-->
                    </div>
                </div>
				<?php
				$average_color = (object) [
					'v4'      => nat_get_average( $values->v4_duration ),
					'v4_txt'  => nat_get_average( $values->v4_duration ) . 's',
					'v6'      => nat_get_average( $values->v6_duration ),
					'v6_txt'  => nat_get_average( $values->v6_duration ) . 's',
					'nat'     => nat_get_average( $values->nat_duration ),
					'nat_txt' => nat_get_average( $values->nat_duration ) . 's',
				];
				$v4_color      = '#FCB725';
				$v6_color      = '#FCB725';
				$nat_color     = '#FCB725';
				if ( $average_color->v4 > 5 ) {
					$v4_color = '#E20000';
				} else if ( $average_color->v4 <= 1 ) {
					$v4_color = '#3DA637';
					if ( $average_color->v4 == 0 ) {
						$v4_color              = '#E20000';
						$average_color->v4_txt = '<i class="fa fa-bolt" aria-hidden="true"></i>';
					}
				}

				if ( $average_color->v6 > 5 ) {
					$v6_color = '#E20000';
				} else if ( $average_color->v6 <= 1 ) {
					$v6_color = '#3DA637';
					if ( $average_color->v6 == 0 ) {
						$v6_color              = '#E20000';
						$average_color->v6_txt = '<i class="fa fa-bolt" aria-hidden="true"></i>';
					}
				}

				if ( $average_color->nat > 5 ) {
					$nat_color = '#E20000';
				} else if ( $average_color->nat <= 1 ) {
					$nat_color = '#3DA637';
					if ( $average_color->nat == 0 ) {
						$nat_color              = '#E20000';
						$average_color->nat_txt = '<i class="fa fa-bolt" aria-hidden="true"></i>';
					}
				}
				?>
                <div class="col col-sm-3">
                    <div class="loading-ipv4 inline-block">
                        <div class="title">IPv4</div>
                        <svg style="font-size: 7em" class="progress-circle" viewBox="0 0 44 44">
                            <circle class="bg rating-nat-bg" r="12" cx="22" cy="22" stroke-width="1"
                                    fill="<?php echo $v4_color; ?>"></circle>
                            <circle id="rating-nat-progress" class="progress" r="10" cx="22" cy="22"
                                    transform="rotate(-90, 22, 22)" stroke-width="1" fill="none" stroke-dasharray="101"
                                    stroke-dashoffset="0"></circle>
                            <text id="rating-nat-timer" x="22" y="22" font-size="12" text-anchor="middle"
                                  alignment-baseline="central" fill="green">
								<?php
								if ( is_float( $average_color->v4 ) ) {
									echo number_format_i18n( $average_color->v4, 1 );
								} else {
									echo $average_color->v4_txt;
								}
								?>
                            </text>
                        </svg>
                    </div>
                </div>
                <div class="col col-sm-3">
                    <div class="loading-nat inline-block">
                        <div class="title">NAT64</div>
                        <svg style="font-size: 7em" class="progress-circle" viewBox="0 0 44 44">
                            <circle class="bg rating-nat-bg" r="12" cx="22" cy="22" stroke-width="1"
                                    fill="<?php echo $nat_color; ?>"></circle>
                            <circle id="rating-nat-progress" class="progress" r="10" cx="22" cy="22"
                                    transform="rotate(-90, 22, 22)" stroke-width="1" fill="none" stroke-dasharray="101"
                                    stroke-dashoffset="0"></circle>
                            <text id="rating-nat-timer" x="22" y="22" font-size="12" text-anchor="middle"
                                  alignment-baseline="central" fill="green">
								<?php
								if ( is_float( $average_color->nat ) ) {
									echo number_format_i18n( $average_color->nat, 1 );
								} else {
									echo $average_color->nat_txt;
								}
								?>
                            </text>
                        </svg>
                    </div>
                </div>
                <div class="col col-sm-3">
                    <div class="loading-ipv6 inline-block">
                        <div class="title">IPv6</div>
                        <svg style="font-size: 7em" class="progress-circle" viewBox="0 0 44 44">
                            <circle class="bg rating-nat-bg" r="12" cx="22" cy="22" stroke-width="1"
                                    fill="<?php echo $v6_color; ?>"></circle>
                            <circle id="rating-nat-progress" class="progress" r="10" cx="22" cy="22"
                                    transform="rotate(-90, 22, 22)" stroke-width="1" fill="none" stroke-dasharray="101"
                                    stroke-dashoffset="0"></circle>
                            <text id="rating-nat-timer" x="22" y="22" font-size="12" text-anchor="middle"
                                  alignment-baseline="central" fill="green">
								<?php
								if ( is_float( $average_color->v6 ) ) {
									echo number_format_i18n( $average_color->v6, 1 );
								} else {
									echo $average_color->v6_txt;
								}
								?>
                            </text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
	if ( $get_args->server != '' ) { ?>
        <div class="block-loadingtimes bg-high">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="title">
                            <h2>Ping</h2>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <svg version="1.2" xmlns="http://www.w3.org/2000/svg" class="graph" aria-labelledby="title"
                             role="img">
                            <title id="title">IPv4 pings</title>
                            <g class="grid y-grid" id="yGrid">
                                <line x1="100" x2="500" y1="200" y2="200" stroke="#666666"></line>
                            </g>
                            <g class="labels x-labels">
                                <!--
																<text x="200" y="220">50</text>
																<text x="300" y="220">150</text>
																<text x="400" y="220">250</text>
								-->
                                <text x="300" y="220" class="label-title">IPv4 Ping</text>
                            </g>
                            <g class="data" data-setname="Our first data set">
								<?php
								$v4_ping_object = '';
								$v6_ping_object = '';

								foreach ( $values->results as $ping ) {

									if ( $ping->instance_type == 'v4only' ) {
										$v4_ping_object = $ping->instance_type;
										$dns            = '';
										$v4_dns         = '';
										$v6_dns         = '';
										foreach ( $instanceruns as $instancerun ) {
											foreach ( $instancerun->dns_results as $dns ) {
												if ( strpos( $dns, ':' ) !== false ) {
													$v6_dns = $dns;
												} else {
													$v4_dns = $dns;
												}
											}
										}
										if ( $ipv4_ping_results = $ping->ping_response->$v4_dns->results ) {
										} else {
											$ipv4_ping_results = $ping->ping_response->results;
										}

										$ipv4_latency = [];
										if ( $ipv4_ping_results ) {
											foreach ( $ipv4_ping_results as $v4_result ) {
												$ipv4_latency[] = $v4_result->latency;
												if ( $v4_result->latency < 50 ) {
													$circle_color = '#3DA637';
												} else if ( $v4_result->latency > 250 ) {
													$circle_color = '#E20000';
												} else {
													$circle_color = '#FCB725';
												}

												?>
                                                <circle fill="<?php echo $circle_color; ?>" fill-opacity="0.5" cx="300"
                                                        cy="<?php echo 200 - $v4_result->latency; ?>"
                                                        data-value="<?php echo $v4_result->latency; ?>" r="15"></circle>
												<?php
											}
											$ipv4_avg = array_sum( $ipv4_latency ) / count( $ipv4_ping_results );
											if ( $ipv4_avg < 50 ) {
												$circle_color = '#3DA637';
											} else if ( $ipv4_avg > 250 ) {
												$circle_color = '#E20000';
											} else {
												$circle_color = '#FCB725';
											}
											?>
                                            <circle fill="<?php echo $circle_color; ?>" cx="300"
                                                    cy="<?php echo 200 - $ipv4_avg; ?>"
                                                    data-value="<?php echo $ipv4_avg; ?>" r="15"></circle>
											<?php
										}
									}
								}
								if ( $v4_ping_object == '' ) { ?>
                                    <line x1="300" y1="110" x2="310" y2="100" stroke="#E20000" stroke-width="4"></line>
                                    <line x1="310" y1="110" x2="300" y2="100" stroke="#E20000" stroke-width="4"></line>
									<?php
								}
								?>
                            </g>
                        </svg>
                    </div>
                    <div class="col-sm-6">
                        <svg version="1.2" xmlns="http://www.w3.org/2000/svg" class="graph" aria-labelledby="title"
                             role="img">
                            <title id="title">IPv6 pings</title>
                            <g class="grid y-grid" id="yGrid">
                                <line x1="100" x2="500" y1="200" y2="200" stroke="#666666"></line>
                            </g>
                            <g class="labels x-labels">
                                <!--
																<text x="200" y="220">50</text>
																<text x="300" y="220">150</text>
																<text x="400" y="220">250</text>
								-->
                                <text x="300" y="220" class="label-title">IPv6 Ping</text>
                            </g>
                            <g class="data" data-setname="Our first data set">
								<?php
								foreach ( $values->results as $ping ) {

									if ( $ping->instance_type == 'v6only' ) {
										$v6_ping_object = $ping->instance_type;
										$dns            = '';
										$v4_dns         = '';
										$v6_dns         = '';
										foreach ( $instanceruns as $instancerun ) {
											foreach ( $instancerun->dns_results as $dns ) {
												if ( strpos( $dns, ':' ) !== false ) {
													$v6_dns = $dns;
												} else {
													$v4_dns = $dns;
												}
											}
										}
										if ( $ipv6_ping_results = $ping->ping_response->$v6_dns->results ) {
										} else {
											$ipv6_ping_results = $ping->ping_response->results;
										}

										$ipv6_latency = [];
										foreach ( $ipv6_ping_results as $v6_result ) {
											$ipv6_latency[] = $v6_result->latency;
											if ( $v6_result->latency < 50 ) {
												$circle_color = '#3DA637';
											} else if ( $v6_result->latency > 250 ) {
												$circle_color = '#E20000';
											} else {
												$circle_color = '#FCB725';
											}
											?>
                                            <circle fill="<?php echo $circle_color; ?>" fill-opacity="0.5" cx="300"
                                                    cy="<?php echo 200 - $v6_result->latency; ?>"
                                                    data-value="<?php echo $v6_result->latency; ?>" r="15"></circle>
											<?php
										}
										$ipv6_avg = array_sum( $ipv6_latency ) / count( $ipv6_ping_results );
										if ( $ipv6_avg < 50 ) {
											$circle_color = '#3DA637';
										} else if ( $ipv6_avg > 250 ) {
											$circle_color = '#E20000';
										} else {
											$circle_color = '#FCB725';
										}
										?>
                                        <circle fill="<?php echo $circle_color; ?>" cx="300"
                                                cy="<?php echo 200 - $ipv6_avg; ?>"
                                                data-value="<?php echo $ipv6_avg; ?>" r="15"></circle>
										<?php
									}
								}
								if ( $v6_ping_object == '' ) { ?>
                                    <line x1="300" y1="110" x2="310" y2="100" stroke="#E20000" stroke-width="4"></line>
                                    <line x1="310" y1="110" x2="300" y2="100" stroke="#E20000" stroke-width="4"></line>
									<?php
								}
								?>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
	?>
    <div class="block-dnsrecords bg-low">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="title">
                        <h2>DNS Records</h2>
                    </div>
                </div>
            </div>
            <div class="row border-bottom">
                <div class="col-sm-6">
                    <div class="dns-ipv4 text-center">
                        <div class="title">IPv4</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="dns-ipv6 text-center">
                        <div class="title">IPv6</div>
                    </div>
                </div>
            </div>
			<?php
			foreach ( $instanceruns as $instancerun ) {
				foreach ( $instancerun->dns_results as $dns ) {
					if ( strpos( $dns, ':' ) !== false ) {
						$v6_dns = $dns;
					} else {
						$v4_dns = $dns;
					}
				}
				?>
                <div class="row border-bottom">
                    <div class="col-sm-6">
                        <div class="dns-ipv4 text-center">
							<?php
							if ( $v4_dns ) { ?>
                                <div class="ip-ipv4"><?php echo $v4_dns; ?> <i class="fa fa-check"
                                                                               aria-hidden="true"></i></div>
								<?php
							} else { ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
								<?php
							}
							?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="dns-ipv6 text-center">
							<?php
							if ( $v6_dns ) { ?>
                                <div class="ip-ipv6"><?php echo $v6_dns; ?> <i class="fa fa-check"
                                                                               aria-hidden="true"></i></div>
								<?php
							} else { ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
								<?php
							}
							?>
                        </div>
                    </div>
                </div>
				<?php
			}
			?>
        </div>
    </div>
	<?php
	if ( $get_args->server == '' ) { ?>
        <div class="block-testlocations bg-mid">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="title">
                            <h2>Test locations</h2>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="locations-map">
							<?php
							if ( $get_args->server == '' ) {
								$test_locations = $test_locations;
							} else {
								foreach ( $test_locations as $test_location ) {
									if ( $test_location->id == $get_args->server ) {
										$test_locations = [ $test_location ];
									}
								}
							}
							foreach ( $test_locations as $test_location ) {
								if ( $get_args->server == '' ) {

									foreach ( $map_overall as $overall ) {
										if ( $test_location->id == $overall->tid ) {
											if ( $overall->overall >= 85 ) {
												$values->bg = 'bg-primary';
											} else if ( $overall->overall < 60 ) {
												$values->bg = 'bg-secondary';
											} else {
												$values->bg = 'bg-accent';
											}
										}
									}
								}

								$sub   = substr( $test_location->location, strpos( $test_location->location, '(' ) + strlen( '(' ), strlen( $test_location->location ) );
								$coord = explode( ' ', substr( $sub, 0, strpos( $sub, ')' ) ) );
								$lat   = $coord[1];
								$lng   = $coord[0];
								?>
                                <div class="marker"
                                     data-redirect="<?php echo site_url() . '/results/?server=' . $test_location->id . '&test_id=' . $get_args->test_id; ?>"
                                     data-bg="<?php echo $values->bg; ?>" data-lat="<?php echo $lat; ?>"
                                     data-lng="<?php echo $lng; ?>"></div>
								<?php
							}
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
	?>
    <div class="block-summary bg-high">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="title">
                        <h2>Summary</h2>
                    </div>
                    <div class="content">
                        <p>
                            This website, <?php echo '<strong>' . $main_request->url . '</strong>' ?>,
                            <!--						has some problems with <?php //echo '<strong>NAT64</strong>'
							?> and <?php //echo '<strong>IPv6</strong>'
							?> connectivity. Here is a summary of the issues you'r experiencing and the potential soloutions:-->
                        </p>
                    </div>
                    <div>
						<?php
						$count = 1;
						foreach ( $values->messages as $message ) {
							if ( $message->severity == 50 ) {
								$class = 'bg-secondary';
							} else if ( $message->severity == 40 ) {
								$class = 'bg-secondary';
							} else if ( $message->severity == 30 ) {
								$class = 'bg-accent';
							} else if ( $message->severity == 20 ) {
								$class = 'bg-highest';
							} else if ( $message->severity == 10 ) {
								$class = 'bg-highest';
							}
							?>
                            <div class="summary faq-item">
                                <h3 class=" bg-low">
                                    <div class="problem <?php echo $class; ?> inline-block"><?php echo $count ++; ?></div>
                                    <p class="inline-block"><?php echo $message->message; ?> </p></h3>
                            </div>
							<?php
						}
						?>
                    </div>
                    <div class="summary-print inline-block">
                        <a href="#" onClick="window.print()" class="button bg-highest"><i class="fa fa-print"
                                                                                          aria-hidden="true"></i>Print</a>
                    </div>
                    <div class="summary-download inline-block">
                        <a href="#" class="button bg-dark"><i class="fa fa-download" aria-hidden="true"></i>Download
                            report data</a>
                    </div>
					<?php
					if ( ! is_user_logged_in() ) { ?>
                        <div class="summary-signup inline-block">
                            <a href="<?php echo get_site_url() . '/register/'; ?>" class="button bg-accent"><i
                                        class="fa fa-pencil-square-o" aria-hidden="true"></i>Signup for an account</a>
                        </div>
                        <div class="signup-benefits">
                            <p><i class="fa fa-check" aria-hidden="true"></i>Regular emails on how to improve your
                                websites</p>
                            <p><i class="fa fa-check" aria-hidden="true"></i>Schedule regular checks</p>
                            <p><i class="fa fa-check" aria-hidden="true"></i>Added privacy options</p>
                        </div>
						<?php
					}
					?>
                </div>
                <div class="col-lg-4">
                    <div class="widgets-right">
                        <div class="first-widget bg-mid">
                            <h2>Find out more</h2>
                            <ul>
                                <li>
                                    <p>What does it take to be NAT64 and IPv6 compatible ? Read our FAQs to ﬁnd out</p>
                                    <p class="text-right"><a href="<?php echo get_term_link( $values->faq_id ); ?>">Frequently
                                            Asked Questions <i class="fa fa-caret-right" aria-hidden="true"></i></a></p>
                                </li>
                                <li>
                                    <p>How does your country fare? Read in-depth analysis of the latest trends in our
                                        blog.</p>
                                    <p class="text-right"><a href="<?php echo get_term_link( $values->blog_id ); ?>">Read
                                            the blog <i class="fa fa-caret-right" aria-hidden="true"></i></a></p>
                                </li>
                            </ul>
                        </div>
                        <div class="second-widget text-center bg-highest">
                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo.png"
                                 alt="<?php bloginfo( 'name' ); ?>"/>
                            <p>NAT64Check is an open source project. You can run your own version, test locally, or add
                                to the global pool.</p>
                            <div class="get-intouch text-center">
                                <h3 class="inline-block">Interested?</h3> <a
                                        href="<?php echo site_url() . '/contact/' ?>" class="inline-block">Get in
                                    touch. </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
} else { ?>
    <div class="block-summary bg-high">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="container">
                        The test isn't finished yet
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
}
get_footer(); ?>
