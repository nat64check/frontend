<?php get_header(); ?>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="content text-center">
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
				<?php get_sidebar(); ?>
            </div>
        </div>
    </section>

<?php get_footer(); ?>
