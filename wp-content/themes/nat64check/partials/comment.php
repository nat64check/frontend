<?php
$class = '';
if ( $comment->comment_depth > 1 ) {
	$class = 'reply';
}
?>
<div id="comment-<?php echo $comment->comment_ID; ?>"
     class="comment <?php echo $class; ?> comment-depth-<?php echo $comment->comment_depth; ?>">
    <div class="comment-body">
        <div class="comment-author">
			<?php echo $comment->comment_author; ?>
        </div>
        <div class="comment-meta">
			<?php echo date_i18n( 'j F Y', strtotime( $comment->comment_date ) ); ?>
        </div>
        <p><?php echo $comment->comment_content; ?></p>
        <div class="text-right">
			<?php
			comment_reply_link(
				[
					'max_depth'     => get_option( 'thread_comments_depth' ),
					'depth'         => $comment->comment_depth,
					'reply_text'    => '<i class="fa fa-comment-o" aria-hidden="true"></i> Reply',
					'reply_to_text' => '<i class="fa fa-comment-o" aria-hidden="true"></i> Reply',
				],
				$comment->comment_ID,
				$comment->comment_post_ID
			);
			?>
        </div>
    </div>
</div>
