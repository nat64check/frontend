<?php
get_header();

$get_args = (object) [
	'faq_search' => '',
];

foreach ( $get_args as $k => $v ) {
	if ( ! empty( $_GET[ $k ] ) ) {
		$get_args->$k = esc_attr( $_GET[ $k ] );
	}
}
$faq_id = get_queried_object()->term_id;


$args = [
	'faq_search'     => $get_args->faq_search,
	'cat'            => $faq_id,
	'posts_per_page' => 6,
];

query_posts( $args );
?>

    <section class="cat-faq section">
        <form id="faq-form" action="<?php echo get_term_link( $faq_id ); ?>" method="get">
            <div class="knowledgebase bg-high">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="title">
                                <h2>Knowledgebase</h2>
                            </div>
                            <p>Do you have a question about implementing </p>
                            <p>NAT64 or IPv6? Our comprehensive database of </p>
                            <p>commonly encountered issues can help.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="search-button button text-center">
                                <input type="search" placeholder="Search for a question" name="faq_search"
                                       value="<?php echo $get_args->faq_search; ?>">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="content">
                    <h1>Top questions</h1>
					<?php
					if ( have_posts() ) {
						$i = 1;
						echo '<div class="row">';

						while ( have_posts() ) {
							the_post();
							$post_color = get_field( 'post_color' );
							?>
                            <div class="col-lg-4 flex-container">
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
							if ( $i % 3 == 0 ) {
								echo '</div><div class="row">';
							}
							$i ++;
						}
						echo '</div>';
						get_template_part( 'partials/pager' );
					}
					?>
                </div>
            </div>
        </form>
    </section>

<?php
wp_reset_query();
get_footer(); ?>
