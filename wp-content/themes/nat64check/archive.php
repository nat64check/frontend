<?php get_header(); ?>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-sm-8">
                    <div class="content">
                        <h1><?php is_category() ? single_cat_title() : post_type_archive_title(); ?></h1>
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								?>
                                <div <?php post_class() ?>>
                                    <h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
									<?php the_excerpt(); ?>
                                </div>
								<?php
							}

							get_template_part( 'partials/pager' );
						}
						?>
                    </div>
                </div>
				<?php get_sidebar(); ?>
            </div>
        </div>
    </section>

<?php get_footer(); ?>
