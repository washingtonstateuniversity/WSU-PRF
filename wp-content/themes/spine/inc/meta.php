<?php

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'spine2_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'spine2_post_meta_boxes_setup' );

/* Meta box setup function. */
function spine2_post_meta_boxes_setup() {

	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'spine2_add_post_meta_boxes' );

	/* Save post meta on the 'save_post' hook. */
	add_action( 'save_post', 'spine2_save_hide_header_img_meta', 10, 2 );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function spine2_add_post_meta_boxes() {
	foreach ( array_keys( $GLOBALS['wp_post_types'] ) as $post_type )
	{
		// Skip:
		if ( in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item' ) ) )
			continue;
		add_meta_box(
			'spine2-hide-header-img',			// Unique ID
			esc_html__( 'Hide Header Image', 'example' ),		// Title
			'spine2_hide_header_img_meta_box',		// Callback function
			$post_type,					// Admin page (or post type)
			'side',					// Context
			'default'					// Priority
		);

	}

}

/* Display the post meta box. */
function spine2_hide_header_img_meta_box( $object, $box ) { ?>

	<?php wp_nonce_field( basename( __FILE__ ), 'spine2_hide_header_img_nonce' ); ?>

	<p>
		<label for="spine2-hide-header-img"><?php _e( "Tick this box to hide the header image on this page/post.", 'spine2' ); ?></label>
		<br />
		<input type="checkbox" name="spine2-hide-header-img" id="spine2-hide-header-img" value="1" <?php checked( intval( get_post_meta( $object->ID, 'spine2_hide_header_img', true )), 1 ); ?>/>
	</p>
<?php }

/* Save the meta box's post metadata. */
function spine2_save_hide_header_img_meta( $post_id, $post ) {

	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['spine2_hide_header_img_nonce'] ) || !wp_verify_nonce( $_POST['spine2_hide_header_img_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	/* Get the posted data and sanitize it for use as an HTML class. */
	$new_meta_value = ( isset( $_POST['spine2-hide-header-img'] ) ? sanitize_html_class( $_POST['spine2-hide-header-img'] ) : '' );

	/* Get the meta key. */
	$meta_key = 'spine2_hide_header_img';

	/* Get the meta value of the custom field key. */
	$meta_value = get_post_meta( $post_id, $meta_key, true );

	/* If a new meta value was added and there was no previous value, add it. */
	if ( $new_meta_value && '' == $meta_value )
		add_post_meta( $post_id, $meta_key, $new_meta_value, true );

	/* If the new meta value does not match the old value, update it. */
	elseif ( $new_meta_value && $new_meta_value != $meta_value )
		update_post_meta( $post_id, $meta_key, $new_meta_value );

	/* If there is no new meta value but an old value exists, delete it. */
	elseif ( '' == $new_meta_value && $meta_value )
		delete_post_meta( $post_id, $meta_key, $meta_value );
}