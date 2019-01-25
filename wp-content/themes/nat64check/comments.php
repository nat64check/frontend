<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && 'comments.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	wp_die( 'Please do not load this page directly. Thanks!' );
}

if ( post_password_required() ) {
	return;
}

global $max_wp_comments;

wp_enqueue_script( 'comment-reply' );

if ( comments_open() ) {
	?>
    <div id="respond" class="box box-padding bg-white">
		<?php
		if ( isset( $_GET['invalid'] ) && $_GET['invalid'] == 'comment' ) {
			?>
            <p style="color: red;">
                It looks like you are sending spam comments.
            </p>
			<?php
		}
		?>
        <div id="cancel-comment-reply">
            <small>
				<?php cancel_comment_reply_link() ?>
            </small>
			<?php
			if ( isset( $_GET['replytocom'] ) && $_GET['replytocom'] != '' ) {
				$commentId = (int) esc_html( $_GET['replytocom'] );
				$comment   = get_comment( $commentId );

				if ( isset( $comment->comment_content ) ) {
					echo '<p>' . $comment->comment_content . '</p>';
				}
			}
			?>
        </div>
		<?php
		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			?>
            <p>
                You should <a href="<?php echo wp_login_url( get_permalink() ); ?>">Login</a> to give a comment.
            </p>
			<?php
		} else {
			$comment_author       = '';
			$comment_author_email = '';

			if ( isset( $_POST['author'] ) ) {
				$comment_author = esc_html( $_POST['author'] );
			}
			if ( isset( $_POST['email'] ) ) {
				$comment_author_email = esc_html( $_POST['email'] );
			}

			?>
            <form action="<?php echo site_url(); ?>/wp-comments-post.php" method="post" id="commentform">
				<?php
				if ( is_user_logged_in() ) {
					?>
                    <div class="field">
                        Loged in as <a
                                href="<?php echo get_permalink( max_wp_user_prop( 'connect_user' ) ); ?>"><?php echo $user_identity; ?></a>,
                        <a href="<?php echo wp_logout_url( get_permalink() ); ?>" title="Log out">
                            Log out &raquo;
                        </a>
                    </div>
					<?php
				} else {
					?>
                    <div class="row">
                        <div class="col-md-6 field name">
                            <label for="comment_author">Name</label><br>
                            <input type="text" class="form-control" name="author" id="comment_author"
                                   value="<?php echo esc_attr( $comment_author ); ?>" size="22" tabindex="0"
                                   aria-required="true" required="required"/>
                        </div>
                        <div class="col-md-6 field email">
                            <label for="comment_email">E-mailadres</label><br>
                            <input type="text" class="form-control" name="email" id="comment_email"
                                   value="<?php echo esc_attr( $comment_author_email ); ?>" size="22" tabindex="0"
                                   aria-required="true" required="required"/>
                        </div>
                    </div>
					<?php
				}
				?>
                <div class="field">
                    <label for="comment">Write a comment</label>
                    <textarea class="form-control" name="comment" id="comment" cols="58" rows="10" tabindex="0"
                              aria-required="true" required="required"></textarea>
                </div>

				<?php do_action( 'comment_form', get_the_id() ); ?>

				<?php if ( ! is_user_logged_in() ) { ?>
                    <div class="field">
                        <p><i>comment will be submitted after admin approval</i></p>
						<?php $max_wp_comments->recaptcha_show(); ?>
                    </div>
				<?php } ?>
                <div class="field">
                    <input class="button small" name="submit" type="submit" id="submit" tabindex="0" value="Reply"/>
					<?php comment_id_fields(); ?>
                </div>
            </form>
			<?php
		}
		?>
    </div>
	<?php
}

if ( $comments = $max_wp_comments->get_comments() ) {
	?>
    <div id="comments" class="clearfix">
		<?php
		foreach ( $comments as $comment ) {
			include( __DIR__ . '/partials/comment.php' );
		}
		?>
    </div>
	<?php
}

//comment_form();
