<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tdwebservices.com
 * @since      1.0.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/admin/includes
 */

class Tdws_Coupon_Box {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $one7trackAPI;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Register TDWS Custom Coupon Post Type
		add_action( 'init', array( $this, 'tdws_register_custom_coupon_post_type' ));

		// Add TDWS Custom Coupon Meta Boxes
		add_action( 'add_meta_boxes', array( $this, 'tdws_add_custom_coupon_meta_box' ) );

		// Save TDWS Custom Coupon Meta Boxes
		add_action( 'save_post', array( $this, 'tdws_save_custom_coupon_meta_box' ) );

	}


	/**
	 * Register Custom Coupon
	 *
	 * @since    1.1.9
	 */

	public function tdws_register_custom_coupon_post_type() {


		$tdws_post_labels = array(
			'name'                  => _x( 'TDWS Coupons', 'Post type general name', 'tdws-order-tracking-system' ),
			'singular_name'         => _x( 'TDWS Coupons', 'Post type singular name', 'tdws-order-tracking-system' ),
			'menu_name'             => _x( 'TDWS Coupons', 'Admin Menu text', 'tdws-order-tracking-system' ),
			'name_admin_bar'        => _x( 'TDWS Coupons', 'Add New on Toolbar', 'tdws-order-tracking-system' ),
			'add_new'               => __( 'Add New', 'tdws-order-tracking-system' ),
			'add_new_item'          => __( 'Add New TDWS Coupons', 'tdws-order-tracking-system' ),
			'new_item'              => __( 'New TDWS Coupons', 'tdws-order-tracking-system' ),
			'edit_item'             => __( 'Edit TDWS Coupons', 'tdws-order-tracking-system' ),
			'view_item'             => __( 'View TDWS Coupons', 'tdws-order-tracking-system' ),
			'all_items'             => __( 'All TDWS Coupons', 'tdws-order-tracking-system' ),
			'search_items'          => __( 'Search TDWS Coupons', 'tdws-order-tracking-system' ),
			'parent_item_colon'     => __( 'Parent TDWS Coupons:', 'tdws-order-tracking-system' ),
			'not_found'             => __( 'No TDWS Coupons found.', 'tdws-order-tracking-system' ),
			'not_found_in_trash'    => __( 'No TDWS Coupons found in Trash.', 'tdws-order-tracking-system' ),
		);

		$tdws_post_args = array(
			'labels'             => $tdws_post_labels,
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'tdws-coupon' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
		);

		register_post_type( 'tdws-coupon', $tdws_post_args );

		$tdws_cat_labels = array(
			'name'              => _x( 'Interest', 'taxonomy general name', 'tdws-order-tracking-system' ),
			'singular_name'     => _x( 'Interest', 'taxonomy singular name', 'tdws-order-tracking-system' ),
			'search_items'      => __( 'Search Interest', 'tdws-order-tracking-system' ),
			'all_items'         => __( 'All Interest', 'tdws-order-tracking-system' ),
			'parent_item'       => __( 'Parent Interest', 'tdws-order-tracking-system' ),
			'parent_item_colon' => __( 'Parent Interest:', 'tdws-order-tracking-system' ),
			'edit_item'         => __( 'Edit Interest', 'tdws-order-tracking-system' ),
			'update_item'       => __( 'Update Interest', 'tdws-order-tracking-system' ),
			'add_new_item'      => __( 'Add New Interest', 'tdws-order-tracking-system' ),
			'new_item_name'     => __( 'New Interest Name', 'tdws-order-tracking-system' ),
			'menu_name'         => __( 'Interest', 'tdws-order-tracking-system' ),
		);

		$tdws_cat_args = array(
			'hierarchical'      => true,
			'labels'            => $tdws_cat_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'tdws-coupon-interest' ),
		);

		register_taxonomy( 'tdws-coupon-interest', array( 'tdws-coupon' ), $tdws_cat_args );

	}

	/**
	 * Add Custom Coupon Metabox
	 *
	 * @since    1.1.9
	 */
	public function tdws_add_custom_coupon_meta_box(){
		add_meta_box( 'tdws_coupon_box_field', __( 'Coupon Fields', 'tdws-order-tracking-system' ), array( $this, 'tdws_add_custom_coupon_html_box' ), 'tdws-coupon', 'normal', 'high' );
	}

	/**
	 * Custom Coupon Metabox HTML
	 *
	 * @since    1.1.9
	 */
	public function tdws_add_custom_coupon_html_box( $post ){

		wp_nonce_field( 'tdws_coupon_save', 'tdws_coupon_meta_box_nonce' );
		$tdws_coupon_code = get_post_meta( $post->ID, 'tdws_coupon_code', true );
		$tdws_coupon_link = get_post_meta( $post->ID, 'tdws_coupon_link', true );
		$tdws_coupon_expiry_date = get_post_meta( $post->ID, 'tdws_coupon_expiry_date', true );
		$tdws_coupon_offer_highlight = get_post_meta( $post->ID, 'tdws_coupon_offer_highlight', true );
		$tdws_brand_logo = get_post_meta( $post->ID, 'tdws_brand_logo', true );
		$tdws_brand_name = get_post_meta( $post->ID, 'tdws_brand_name', true );
		?>
		<div class="tdws-form-section">
			<table class="tdws-form-table" width="100%" border="1" cellpadding="10" cellspacing="10">
				<tbody>						
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Coupon Code', 'tdws-order-tracking-system' ); ?></th>
						<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Coupon Code', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_code" value="<?php echo esc_attr( $tdws_coupon_code ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Offer Highlights', 'tdws-order-tracking-system' ); ?></th>
						<td>
							<?php 
							wp_editor( $tdws_coupon_offer_highlight, 'tdws_coupon_offer_highlight', array(
								'media_buttons' => false, 
								'wpautop' => false, 
								'textarea_name' => 'tdws_coupon_offer_highlight', 
								'textarea_rows' => 10, 					
							) );
							?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Brand Name', 'tdws-order-tracking-system' ); ?></th>
						<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Brand Name', 'tdws-order-tracking-system' ); ?>" name="tdws_brand_name" value="<?php echo esc_attr( $tdws_brand_name ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Brand Logo', 'tdws-order-tracking-system' ); ?></th>
						<td><?php echo tdws_media_uploader_field( 'tdws_brand_logo', $tdws_brand_logo ); ?></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Link', 'tdws-order-tracking-system' ); ?></th>
						<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Link', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_link" value="<?php echo esc_attr( $tdws_coupon_link ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Expiry/Validity Date', 'tdws-order-tracking-system' ); ?></th>
						<td><input type="text" readonly class="tdws_coupon_expiry_date" placeholder="<?php esc_html_e( 'Please Select Expiry/Validity Date', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_expiry_date" value="<?php echo esc_attr( $tdws_coupon_expiry_date ); ?>" /></td>
					</tr>	
				</tbody>
			</table>
		</div> 
		<?php
	}

	/**
	 * Save Custom Coupon Metabox
	 *
	 * @since    1.1.9
	 */
	public function tdws_save_custom_coupon_meta_box( $post_id ){

	    // verify taxonomies meta box nonce
		if ( !isset( $_POST['tdws_coupon_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['tdws_coupon_meta_box_nonce'], 'tdws_coupon_save' ) ){
			return;
		}

	   // return if autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
			return;
		}

	    // Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ){
			return;
		}
		
		if ( isset( $_POST['tdws_coupon_code'] ) ) {
			update_post_meta( $post_id, 'tdws_coupon_code', sanitize_text_field( $_POST['tdws_coupon_code'] ) );
		}
	
		if ( isset( $_POST['tdws_brand_name'] ) ) {
			update_post_meta( $post_id, 'tdws_brand_name', sanitize_text_field( $_POST['tdws_brand_name'] ) );
		}

		if ( isset( $_POST['tdws_brand_logo'] ) ) {
			update_post_meta( $post_id, 'tdws_brand_logo', sanitize_text_field( $_POST['tdws_brand_logo'] ) );
		}

		if ( isset( $_POST['tdws_coupon_link'] ) ) {
			update_post_meta( $post_id, 'tdws_coupon_link', $_POST['tdws_coupon_link'] );
		}

		if ( isset( $_POST['tdws_coupon_expiry_date'] ) ) {			
			update_post_meta( $post_id, 'tdws_coupon_expiry_date', $_POST['tdws_coupon_expiry_date'] );
		}

		if ( isset( $_POST['tdws_coupon_offer_highlight'] ) ) {
			update_post_meta( $post_id, 'tdws_coupon_offer_highlight', $_POST['tdws_coupon_offer_highlight'] );
		}

	}
	
}