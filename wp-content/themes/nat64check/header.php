<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" class="<?php do_action( 'html_class' ); ?>" <?php language_attributes(); ?>>
<!-- 
    Realisatie:
    MAX
    Apeldoorn
    W: www.max.nl
    E: info@max.nl
    T: 055-5270270
-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><?php wp_title( ' | ' ); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
	<?php wp_head(); ?>
</head>

<?php
$label   = '<i class="fa fa-user color-white" aria-hidden="true"></i> Login';
$link_to = wp_login_url();
$token   = '';
if ( is_user_logged_in() ) {
	$token          = 'user';
	$label          = get_currentuserinfo()->user_nicename;
	$user_dashboard = get_permalink( max_wp_user_prop( 'connect_user' ) );
	$link_to        = $user_dashboard;
}
global $nat_api;
?>

<body <?php body_class(); ?>>
<div id="page-loader" class="flex-container justify-content-center align-items-center">
    <div class="loader"></div>
</div>
<div id="wrapper">
    <header id="header">
        <div id="header-top">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <a id="logo" href="<?php echo home_url( '/' ); ?>"
                           title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><img
                                    src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo.png"
                                    alt="<?php bloginfo( 'name' ); ?>"/></a>
                    </div>
                    <div class="col-sm-6 flex-container justify-content-end">
						<?php
						if ( ! is_user_logged_in() ) { ?>
                            <div class="register-btn flex-container">
                                <a href="<?php echo get_site_url() . '/register/'; ?>"><i
                                            class="fa fa-check color-white" aria-hidden="true"></i> Register</a>
                            </div>
							<?php
						}
						if ( is_user_logged_in() ) { ?>
                            <div class="user-setting flex-container">
                                <i class="fa fa-cog" aria-hidden="true"></i>
                                <div class="user-options">
                                    <ul class="flex-container flex-column">
                                        <li class="flex-container">
                                            <a href="<?php echo $user_dashboard; ?>">Dashboard</a>
                                        </li>
                                        <li class="flex-container">
                                            <a href="<?php echo get_site_url() . '/user-settings/'; ?>">Your
                                                settings</a>
                                        </li>
                                        <li class="flex-container">
                                            <a href="<?php echo wp_logout_url( home_url( '/' ) ); ?>">Log Out</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
							<?php
						}
						?>
                        <div class="user-name flex-container">
                            <a href="<?php echo $link_to; ?>"><?php echo $label ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
		if ( is_page_template( 'generating-results.php' ) || is_page_template( 'page-results.php' ) ) {

			if ( $url = json_decode( $nat_api->request( 'testruns/' . base64_decode( max_wp_request_var( 'test_id' ) ) . '/', $token )['body'] )->url ) {
				$url = base64_encode( $url );
			} else if ( $url = max_wp_request_var( 'url_test' ) ) {
				if ( is_page_template( 'generating-results.php' ) ) {
					if ( $url != '' && ! preg_match( "~^(?:f|ht)tps?://~i", $url ) ) {
						$url = base64_encode( "https://" . $url );
					} else {
						$url = base64_encode( $url );
					}
				}
			}

			?>
            <div id="header-bottom" class="bg-highest">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-sm-8">
                            <div class="checked-website inline-block">
                                <a href="<?php echo base64_decode( $url ); ?>"
                                   target="_blank"><?php echo base64_decode( $url ); ?></a> <i
                                        class="fa fa-external-link" aria-hidden="true"></i>
                            </div>
                            <div class="checked-website-button inline-block">
								<?php
								$values = (object) [
									'icon'  => '<i class="fa fa-refresh" aria-hidden="true"></i>',
									'text'  => 'Check again',
									'bg'    => 'bg-primary',
									'event' => '',
								];

								if ( is_page_template( 'generating-results.php' ) ) {
									$values->icon  = '<i class="fa fa-spinner fa-pulse fa-3x fa-fw font-25 inline-block"></i>';
									$values->text  = 'Checking';
									$values->bg    = 'bg-accent';
									$values->event = 'event';
								}
								?>
                                <a href="<?php echo site_url() . '/generating-results/?url_test=' . base64_decode( $url ); ?>"
                                   class="button <?php echo $values->bg; ?>"><?php echo $values->icon; ?><?php echo $values->text; ?></a>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="checked-website-info">
								<?php
								if ( $test_id = base64_decode( max_wp_request_var( 'test_id' ) ) ) {
									$time_finished = strtotime( json_decode( $nat_api->request( 'testruns/' . $test_id . '/', 'user' )['body'] )->finished ); ?>
                                    <p>last test completed:</p>
                                    <p><?php echo date_i18n( 'd F Y,H:i', $time_finished, true ); ?> GMT</p>
                                    <p>localtime <?php echo date_i18n( 'd F Y,H:i', $time_finished ); ?></p>
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
    </header>
    <div id="content-wrapper">
