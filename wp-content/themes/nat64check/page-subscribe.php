<?php
/* Template Name: Subscribe form */
get_header();
?>

    <section class="subscribe">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="content">
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								?>
                                <div <?php post_class() ?>>
                                    <h1><?php the_title(); ?></h1>
									<?php the_content(); ?>
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

<?php get_footer(); ?>
