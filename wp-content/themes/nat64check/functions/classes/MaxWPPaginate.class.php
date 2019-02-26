<?php

class MaxWPPaginate {
	static function show( $params = [] ) {
		$params = array_merge( [
			'firstText'  => 'first',
			'lastText'   => 'last',
			'nextText'   => 'next',
			'prevText'   => 'previous',
			'pagesLimit' => 10,
		], $params );

		$params = apply_filters( 'max_wp_paginate_args', $params );

		if ( get_query_var( 'posts_per_page' ) == - 1 ) {
			return;
		}

		global $wp_query;

		$postsPerPage = get_option( 'posts_per_page' );

		if ( isset( $wp_query->query_vars['posts_per_page'] ) && $wp_query->query_vars['posts_per_page'] > 0 ) {
			$postsPerPage = $wp_query->query_vars['posts_per_page'];
		}

		$page = get_query_var( 'paged' );

		$limit = apply_filters( 'max_wp_paginate_display_pages_limit', $params['pagesLimit'] );
		$start = 1;

		if ( is_search() || is_category() || is_tax() || is_tag() || is_post_type_archive() || is_date() || is_author() ) {
			$postCount = $wp_query->found_posts;
		} else if ( isset( $wp_query->found_posts ) && $wp_query->found_posts > 0 ) {
			$postCount = $wp_query->found_posts;
		} else {
			$postCount = wp_count_posts();
			$postCount = (int) $postCount->publish;
		}

		$pages = ceil( $postCount / $postsPerPage );

		if ( $pages == 0 ) {
			$pages = 1;
		}

		if ( $page == 0 ) {
			$page = 1;
		}

		$end = $pages;

		if ( $pages >= $limit ) {
			$start = $page - floor( $limit / 2 );

			if ( $start < 1 ) {
				$start = 1;
			}

			$end = ( $start + $limit - 1 );

			if ( $end > $pages ) {
				$end = $pages;

				$start = $end - $limit + 1;
			}
		}

		if ( $start < 1 ) {
			$start = 1;
		}

		if ( $pages > 1 ) {
			?>
            <ul class="pagination">
				<?php if ( $params['firstText'] && $page != 1 ) { ?>
                    <li class="first <?php echo $page == 1 ? 'disabled' : ''; ?>">
                        <a href="<?php echo $page == 1 ? '#' : get_pagenum_link( 0 ); ?>">
							<?php echo $params['firstText']; ?>
                        </a>
                    </li>
				<?php } ?>
				<?php if ( $params['prevText'] && $page != 1 ) { ?>
                    <li class="prev <?php echo $page == 1 ? 'disabled' : ''; ?>">
                        <a href="<?php echo $page == 1 ? '#' : get_pagenum_link( $page - 1 ); ?>">
							<?php echo $params['prevText']; ?>
                        </a>
                    </li>
				<?php } ?>

				<?php
				for ( $i = $start; $i <= $end; $i ++ ) {
					?>
                    <li class="<?php echo $page == $i ? 'active' : ''; ?>">
                        <a title="<?php echo $i; ?>" href="<?php echo get_pagenum_link( $i ); ?>">
							<?php echo $i; ?>
                        </a>
                    </li>
					<?php
				}
				?>

				<?php if ( $params['nextText'] && $page != $pages ) { ?>
                    <li class="next <?php echo $page == $pages ? 'disabled' : ''; ?>">
                        <a href="<?php echo $page == $pages ? '#' : get_pagenum_link( $page + 1 ); ?>">
							<?php echo $params['nextText']; ?>
                        </a>
                    </li>
				<?php } ?>
				<?php if ( $params['lastText'] && $page != $pages ) { ?>
                    <li class="last <?php echo $page == $pages ? 'disabled' : ''; ?>">
                        <a href="<?php echo $page == $pages ? '#' : get_pagenum_link( $pages ); ?>">
							<?php echo $params['lastText']; ?>
                        </a>
                    </li>
				<?php } ?>
            </ul>
			<?php
		}
	}
}
