<?php get_header();

$faq_id = get_queried_object()->term_id;


$args = [
	'cat'            => $faq_id,
	'posts_per_page' => 8,
];

query_posts( $args );
?>

    <section class="cat-blog section">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="title">
                        <h2>Our blog</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="content">
                <h1>Latest posts</h1>
				<?php
				if ( have_posts() ) {
					$i = 1;
					echo '<div class="row">';

					while ( have_posts() ) {
						the_post();
						$post_color = get_field( 'post_color' );
						$col_size   = 4;
						if ( $i == 1 || $i == 2 ) {
							$col_size = 6;
						}
						?>
                        <div class="flex-container col-lg-<?php echo $col_size; ?>">
                            <div <?php post_class( $post_color . ' flex-container flex-column box box-hover' ) ?>>
                                <h2><?php the_title(); ?></h2>
								<?php the_excerpt(); ?>
                                <div class="read-more">
                                    <a href="#">Read more...</a>
                                </div>
                                <a class="clickable" href="<?php the_permalink(); ?>"></a>
                            </div>
                        </div>
						<?php
//                    if($i % 3 == 0) {
//                        echo '</div><div class="row">';
//                    }
						$i ++;
					}
					echo '</div>';
					get_template_part( 'partials/pager' );
				}
				?>
            </div>
        </div>
    </section>

<?php
wp_reset_query();
get_footer(); ?>
