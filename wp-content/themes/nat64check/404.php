<?php get_header(); ?>

<?php query_posts( [ 'p' => get_the_id(), 'post_type' => 'page' ] ); ?>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="content">
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								?>
                                <div <?php post_class( 'clearfix' ) ?>>
                                    <h1><?php the_title(); ?></h1>
									<?php
									the_content();

									wp_nav_menu( [
										'menu'      => 'Hoofdmenu',
										'depth'     => 3,
										'container' => false,
									] );

									wp_nav_menu( [
										'menu'      => 'Footermenu',
										'depth'     => 1,
										'container' => false,
									] );
									?>
                                </div>
								<?php
							}
						}
						?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php wp_reset_query(); ?>

<?php get_footer(); ?>
