<?php
$sidebar = 'sidebar';

if ( $sidebar && is_active_sidebar( $sidebar ) ) {
	?>
    <aside class="col-sm-4">
		<?php dynamic_sidebar( $sidebar ); ?>
    </aside>
	<?php
}
