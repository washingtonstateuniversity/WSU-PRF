<li id="comment-<?php comment_ID(); ?>" class="<?php hybrid_comment_class(); ?>">

	<div class="spine2-avatar"><?php echo hybrid_avatar(); ?></div>
	<div class="spine2-comm">
		<?php echo apply_atomic_shortcode( 'comment_meta', '<div class="comment-meta">[comment-author] [comment-published] [comment-permalink before="| "] [comment-edit-link before="| "]</div>' ); ?>

		<div class="comment-content">
			<?php comment_text(); ?>
		</div>
		<!-- .comment-content -->

		<?php echo hybrid_comment_reply_link_shortcode( array() ); ?>
		<hr></div>
<?php /* No closing </li> is needed.  WordPress will know where to add it. */ ?>