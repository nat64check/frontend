<?php get_header(); ?>

<?php global $wp_query; ?>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-sm-8">
                    <div class="content">
                        <h1><?php echo "{$wp_query->post_count} van {$wp_query->found_posts}";
	                        echo $wp_query->post_count == 1 ? ' result' : ' results'; ?> op
                            "<?php echo get_search_query(); ?>"</h1>
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								?>
                                <div <?php post_class( 'search-result' ) ?>>
                                    <h2><a href="<?php echo get_permalink(); ?>"
                                           title="More…"><?php the_title(); ?></a></h2>
									<?php the_excerpt(); ?>
                                </div>
								<?php
							}

							get_template_part( 'partials/pager' );
						} else {
							?>
                            <p>No results found.</p>
							<?php
						}
						?>
                    </div>
                </div>
				<?php get_sidebar(); ?>
            </div>
        </div>
    </section>

<?php get_footer(); ?>
