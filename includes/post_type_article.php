<?php

/**
 * Registers post types articles.
 */
function articles_post_type() {

	$labels = array(
		'name'                  => _x( 'Articles', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Article', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Articles', 'text_domain' ),
		'name_admin_bar'        => __( 'Article', 'text_domain' ),
		'archives'              => __( 'Item Archives', 'text_domain' ),
		'attributes'            => __( 'Item Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Items', 'text_domain' ),
		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Item', 'text_domain' ),
		'edit_item'             => __( 'Edit Item', 'text_domain' ),
		'update_item'           => __( 'Update Item', 'text_domain' ),
		'view_item'             => __( 'View Item', 'text_domain' ),
		'view_items'            => __( 'View Items', 'text_domain' ),
		'search_items'          => __( 'Search Item', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Article', 'text_domain' ),
		'description'           => __( 'Site articles.', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor','content', 'thumbnail','author', 'page-attributes', 'custom-fields', 'comments', 'revisions' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( ZLICK_POST_TYPE, $args );

}
//add_action( 'init', 'articles_post_type', 0 );

/**
 * Adds Zlick Payments custom fields.
 */

function zp_add_custom_fields_meta_box() {
	add_meta_box(
		'zlick_payments_fields', // $id
            'Zlick Payment Fields', // $title
		'zp_show_custom_fields_meta_box', // $callback
		\zlick_payments\zp_get_post_type(), // $screen
		'normal', // $context
		'high' // $priority
	);
}

/**
 * Show Zlick Custom Fields.
 */
function zp_show_custom_fields_meta_box() {
	global $post;
	$custom = get_post_custom( $post->ID );
	?>
	<input type="hidden" name="zp_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
	<p>
		Placeholder code: <strong>zp_placeholder</strong>
	</p>
	<p>
		<label for="zp_is_paid">Is Paid</label>
		<input name="zp_is_paid" type="checkbox" id="zp_is_paid" value="paid" <?= ( @$custom["zp_is_paid"][0] =="paid") ? "checked" : ""?>   >
	</p>
    <p>
	    <label for="zp_article_price">Article Price</label>
        <input name="zp_article_price" type="text" id="zp_article_price" value="<?= @$custom["zp_article_price"][0] ?>">
    </p>
<?php }

add_action( 'add_meta_boxes', 'zp_add_custom_fields_meta_box' );

/**
 * @param $post_id
 *
 * @return mixed
 */
function zp_save_zlick_fields_meta( $post_id ) {
	// verify nonce
	if ( !wp_verify_nonce( $_POST['zp_meta_box_nonce'], basename(__FILE__) ) ) {
		return $post_id;
	}
	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// check permissions
	if ( get_post_type( $post_id ) === \zlick_payments\zp_get_post_type() ) {
		update_post_meta( $post_id, 'zp_is_paid', $_POST['zp_is_paid'] );
		update_post_meta( $post_id, 'zp_article_price', $_POST['zp_article_price'] );
	}
}
add_action( 'save_post', 'zp_save_zlick_fields_meta' );




