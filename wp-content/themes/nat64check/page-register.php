<?php
/* Template Name: Registeration form */
acf_form_head();
get_header();

$register_args = [
	'post_id'         => 'new_post',
	'new_post'        => [
		'post_type'   => 'user',
		'post_status' => 'publish',
	],
	'field_groups'    => [ 'group_5ba234655aeaf' ],
	'updated_message' => 'Registered! Please check your E-mail to activate your account!',
	'return'          => add_query_arg( 'updated', 'true', site_url() . '/thankyou-for-registering/' ),
];
?>

    <section class="section user-register">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="content">
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								?>
                                <div <?php post_class() ?>>
                                    <h1><?php the_title(); ?></h1>
									<?php
									the_content();

									?>
                                </div>
								<?php
							}
						}
						?>
                    </div>
					<?php acf_form( $register_args ); ?>
                </div>
            </div>
        </div>
    </section>

<?php get_footer(); ?>
