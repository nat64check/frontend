<?php
get_header();
$counters = (object) [
	'comments' => 0,
];
if ( $comments_count = get_comments_number() ) {
	$counters->comments = $comments_count;
}
?>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="content">
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post(); ?>
                                <div <?php post_class() ?>>
                                    <div class="single">
                                        <h1><?php the_title(); ?></h1>
                                        Updated <?php the_modified_time( 'd/m/Y' ); ?> |
                                        by <?php echo get_the_author() ?> | Comments (<?php echo $counters->comments; ?>
                                        )
										<?php
										get_the_tags();
										the_content();

										if ( comments_open() ) { ?>
                                            <section id="section-reacties"
                                                     class="section-reacties section-padding bg-mid">
                                                <div class="container">
                                                    <div class="section-title text-center margin-bottom">
                                                        <h2>Comments</h2>
                                                    </div>
													<?php comments_template(); ?>
                                                </div>
                                            </section>
											<?php
										}
										?>
                                    </div>
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
