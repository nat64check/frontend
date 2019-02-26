<?php
/* Template Name: User settings */

get_header();

$user_post_id  = max_wp_user_prop( 'connect_user' );
$user          = wp_get_current_user();
$servers       = json_decode( $nat_api->request( 'trillians/?is_alive=2' )['body'] )->results;
$country_names = get_option( 'nat_country_list' );


if ( max_wp_request_var( 'current-pass' ) && max_wp_request_var( 'new-pass' ) ) {
	if ( $user && wp_check_password( max_wp_request_var( 'current-pass' ), $user->data->user_pass, $user->ID ) ) {

		$args = [
			'body' => [
				'password' => max_wp_request_var( 'new-pass' ),
			],
		];
		json_decode( $nat_api->request( 'users/' . get_field( 'api_user', 'user_' . $user->ID ) . '/set_password/', $token, $args, 'post' )['body'] );
		wp_set_password( max_wp_request_var( 'new-pass' ), $user->ID );
		wp_redirect( site_url() );
	} else {
		echo 'Current password does not match!';
	}
}


$get_args = (object) [
	'time_setting'    => '',
	'server_setting'  => [],
	'private_setting' => '',
	'mail_setting'    => '',

];
foreach ( $get_args as $k => $v ) {
	if ( ! empty( $_GET ) ) {
		if ( ! empty( $_GET[ $k ] ) ) {
			update_post_meta( $user_post_id, $k, esc_attr( $_GET[ $k ] ) );
		} else {
			delete_post_meta( $user_post_id, $k );
		}
	}
}

?>

<section class="section account-info content-padding bg-low">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="content">
                    <div <?php post_class() ?>>
                        <div class="account flex-container flex-column">
                            <h1>Your account</h1>
                            <div class="flex-container justify-content-between align-items-center">
                                <h3><?php echo $user->data->display_name; ?>(<?php echo $user->data->user_email; ?>
                                    )</h3>
                                <a class="button bg-secondary"
                                   href="<?php echo site_url() . '?delete_user=' . base64_encode( get_current_user_id() ); ?>"><i
                                            class="fa fa-trash-o" aria-hidden="true"></i> Delete your account</a>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <form id="change-pass-form" action="<?php echo get_site_url(); ?>" method="get">
                                        <div class="input">
                                            <label>
                                                <!-- TODO: fix invalid HTML -->
                                                <!--suppress HtmlUnknownTag -->
                                                <h4>Current password</h4>
                                                <input class="current-pass"
                                                       type="password"
                                                       name="current-pass"
                                                       value="">
                                            </label>
                                        </div>
                                        <div class="input">
                                            <label>
                                                <!-- TODO: fix invalid HTML -->
                                                <!--suppress HtmlUnknownTag -->
                                                <h4>New password</h4>
                                                <input class="new-pass"
                                                       type="password"
                                                       name="new-pass" value="">
                                            </label>
                                        </div>
                                        <div>
                                            <a class="button bg-accent" href="#">
                                                <i class="fa fa-key" aria-hidden="true"></i>
                                                Change your password
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<form action="<?php the_permalink(); ?>" method="get">
    <section class="section account-settings content-padding bg-mid">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Default settings</h2>
                    <!--
					<div class="time-zones content-margin flex-container align-items-center">
						<h4>Default timezone</h4>
						<select name="time_setting" id="time-select">
							<?php //echo wp_timezone_choice( 'UTC' ) ?>
						</select>
					</div>
-->
					<?php /*
					<div id="server-select" class="multiselect server-select">
						<h4 class="inline-block">Default testing locations</h4>
						<div class="selectBox">
							All locations
							<div class="overSelect"></div>
						</div>
						<div class="checkboxes">
							<table>
								<tbody>
									<?php
									$servers = json_decode( $nat_api->request( 'trillians/', $token )[ 'body' ] )->results;
									foreach( $servers as $server ){ ?>
										<tr>
											<td>
												<input class="css-checkbox" type="checkbox" name="server_setting[]" id="test-server-<?php echo $server->hostname; ?>" value="<?php echo $server->_url; ?>" />
												<label class="css-label" for="test-server-<?php echo $server->country; ?>"><?php echo '<p class="inline-block"><img src="'.get_stylesheet_directory_uri().'/graphics/'.strtolower( $server->country ).'.svg" alt=""></p>'; ?> <?php echo get_option( 'nat_country_list' )[ $server->country ]; ?></label>
											</td>
										</tr>
										<?php 
									}
									?>
								  </tbody>
							</table>
						</div>
					</div>
					<?php */ /*
					<div class="server-select content-margin flex-container align-items-center">
						<h4>Default testing locations</h4>
						<select name="server_setting" id="server-select">
						<?php 
						$txt = '';
						$tid = '';
						if( !$get_args->server_setting ){
							$txt = 'All locations';
						}
						else{
							foreach( $servers as $server ){
								if( $server->id == $get_args->server_setting ){
									$txt = $country_names[ $server->country ];
									$tid = $server->id;
								}
							}
						}
						?>
						<option value="<?php echo $tid; ?>"><?php echo $txt; ?></option>
						<?php
						foreach( $servers as $server ){  ?>
							<option value="<?php echo $server->id; ?>"><?php echo $country_names[ $server->country ]; ?></option>
							<?php 
						}
						?>
						</select>		
					</div>
					*/ ?>
                    <div class="private content-margin flex-container align-items-center">
                        <label>
                            <!-- TODO: fix invalid HTML -->
                            <!--suppress HtmlUnknownTag -->
                            <h4>Checks marked  <i class="fa fa-lock" aria-hidden="true"></i>  Private as default </h4>
                            <!-- TODO: fix invalid HTML -->
                            <!--suppress HtmlUnknownTag -->
                            <div class="toggle-group">
								<?php
								$private_checked = 'checked';
								if ( ! get_post_meta( $user_post_id, 'private_setting' )[0] ) {
									$private_checked = '';
								}
								?>
                                <input type="checkbox" name="private_setting" <?php echo $private_checked; ?>>
                            </div>
                        </label>
                    </div>
                    <div class="mail content-margin flex-container align-items-center">
                        <label>
                            <!-- TODO: fix invalid HTML -->
                            <!--suppress HtmlUnknownTag -->
                            <h4>Email when check results change as default</h4>
                            <!-- TODO: fix invalid HTML -->
                            <!--suppress HtmlUnknownTag -->
                            <div class="toggle-group">
								<?php
								$mail_checked = 'checked';
								if ( ! get_post_meta( $user_post_id, 'mail_setting' )[0] ) {
									$mail_checked = '';
								}
								?>
                                <input type="checkbox" name="mail_setting" <?php echo $mail_checked; ?>>
                            </div>
                        </label>
                    </div>
                    <input type="submit" value="Submit">
                </div>
            </div>
        </div>
    </section>
</form>
<?php get_footer(); ?>
