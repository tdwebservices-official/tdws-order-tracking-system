<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tdwebservices.com
 * @since      1.4.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/admin
 * @author     TD Web Services <info@tdwebservices.com>
 */
class Tdws_Order_Tracking_System_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.4.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.4.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.4.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Add Order Tracking Option Page Menu
		add_action( 'admin_menu', array( $this, 'tdws_add_plugin_menu_hook' ) );

		 // Add Order Tag Field On Edit Order Page
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'tdws_add_order_tag_field_order_edit' ), 20, 1 );

		// Save Order Tag Field On Edit Order Page
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'tdws_save_order_tag_field_order_edit' ), 99, 2 );		

		// Add custom order tracking tag On List Order Page
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'tdws_add_order_tag_column_order_list' ), 99, 1 );
		add_filter( 'woocommerce_shop_order_list_table_columns', array( $this, 'tdws_add_order_tag_column_order_list' ), 99, 1 );

		// Add custom order tracking tag On List Order Page
		add_action('manage_shop_order_posts_custom_column', array( $this, 'tdws_show_order_tag_column_order_list' ), 99, 2 );
		add_action( 'woocommerce_shop_order_list_table_custom_column', array( $this, 'tdws_show_order_tag_column_order_list' ), 99, 2 );

		// add Order Filter HTML On List Order Page
		add_action( 'manage_posts_extra_tablenav', array( $this, 'tdws_add_order_tag_filter_list' ), 99, 1 );
		add_action( 'woocommerce_order_list_table_extra_tablenav', array( $this, 'tdws_custom_filter_order_list_table_extra_tablenav' ), 99, 2 );

		// add Order FilterHook to apply the filter
		add_action( 'pre_get_posts',  array( $this, 'tdws_apply_order_tag_filter_list' ), 99, 1 );
		add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'tdws_apply_order_tag_filter_list_by_table' ), 99, 1  );

		// Delete tdws tag data on init
		add_action( 'admin_init', array( $this, 'tdws_delete_hook_call' ) );
		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.4.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tdws_Order_Tracking_System_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tdws_Order_Tracking_System_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tdws-order-tracking-system-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.4.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tdws_Order_Tracking_System_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tdws_Order_Tracking_System_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tdws-order-tracking-system-admin.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Call admin menu hook.
	 *
	 * @since    1.4.0
	 */

	public function tdws_add_plugin_menu_hook(){


		//create new top-level menu
		add_menu_page( __( 'TDWS', 'tdws-order-tracking-system' ), __( 'TDWS', 'tdws-order-tracking-system' ), 'tdws_plugins', 'tdws_order_tracking', array( $this, 'tdws_menu_option_page' ), 'dashicons-admin-generic', 25 );

		//create new sub-level menu
		add_submenu_page( 'tdws_order_tracking', __( 'Order Tracking System', 'tdws-order-tracking-system' ), __( 'Order Tracking System', 'tdws-order-tracking-system' ), 'administrator', 'tdws-order-tracking-system', array( $this, 'tdws_menu_option_page' ) );

		//call register plugin settings 
		add_action( 'admin_init', array( $this, 'tdws_register_menu_option_page_setting' ) );

	}

	/**
	 * Save and register option setting hook.
	 *
	 * @since    1.4.0
	 */

	public function tdws_register_menu_option_page_setting() {
		
		//register plugin settings
		register_setting( 'tdws-order-tracking-setting', 'tdws_ord_track_opt' );
		register_setting( 'tdws-order-tracking-setting', 'tdws_ord_track_mail' );

	}

	/**
	 * Make option setting page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_menu_option_page() {

		$add_order_tags = tdws_get_order_tages();
		$add_tracking_statuses = tdws_get_tracking_statuses();
		$tdws_ord_track_opt = get_option( 'tdws_ord_track_opt' );		
		$tdws_ord_track_mail = get_option( 'tdws_ord_track_mail' );		
		$default_tag_name = apply_filters( 'set_default_tdws_tag_name', 'New' );
		$default_track_subject = apply_filters( 'tdws_order_tracking_mail_subject', 'Order Tracking' );
		$default_track_heading = apply_filters( 'tdws_order_tracking_mail_email_heading', 'Order Tracking' );
		$default_track_email_top = apply_filters( 'tdws_order_tracking_mail_before_item_html', 'Hii [first_name]' );

		$set_default_order_tag = isset($tdws_ord_track_opt['set_default_order_tag']) ? $tdws_ord_track_opt['set_default_order_tag'] : $default_tag_name;
		$order_tag_colour = isset($tdws_ord_track_opt['order_tag_colour']) ? $tdws_ord_track_opt['order_tag_colour'] : '';
		$tag_text_colour = isset($tdws_ord_track_opt['tag_text_colour']) ? $tdws_ord_track_opt['tag_text_colour'] : '';
		$tdws_track_subject = isset($tdws_ord_track_mail['subject']) ? $tdws_ord_track_mail['subject'] : $default_track_subject;
		$tdws_track_email_heading = isset($tdws_ord_track_mail['email_heading']) ? $tdws_ord_track_mail['email_heading'] : $default_track_heading;		
		$tdws_track_email_top = isset($tdws_ord_track_mail['email_top_html']) ? $tdws_ord_track_mail['email_top_html'] : $default_track_email_top;
		$tdws_track_email_bottom = isset($tdws_ord_track_mail['email_bottom_html']) ? $tdws_ord_track_mail['email_bottom_html'] : '';
		?>
		<div class="wrap tdws-form-wrap">
			<h1><?php esc_html_e( 'TDWS Order Tracking System', 'tdws-order-tracking-system' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'tdws-order-tracking-setting' ); ?>
				<?php do_settings_sections( 'tdws-order-tracking-setting' ); ?>
				<table class="tdws-form-table" width="100%" border="1" cellpadding="10" cellspacing="10">
					<tbody>						
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Set Default Order Tag', 'tdws-order-tracking-system' ); ?></th>
							<td><input type="text" placeholder="<?php esc_html_e( 'Please Set Default Order Tag', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[set_default_order_tag]" value="<?php echo esc_attr( $set_default_order_tag ); ?>" /></td>
						</tr>						
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Add Order Tags', 'tdws-order-tracking-system' ); ?></th>
							<td><input type="text" placeholder="<?php esc_html_e( 'Please Add Order Tags', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[add_order_tags]" value="<?php echo esc_attr( $add_order_tags ); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Add Tracking Status', 'tdws-order-tracking-system' ); ?></th>
							<td><input type="text" placeholder="<?php esc_html_e( 'Please Add Tracking Status', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[add_tracking_status]" value="<?php echo esc_attr( $add_tracking_statuses ); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Order Tag Colours', 'tdws-order-tracking-system' ); ?>
								<h6>( <?php esc_html_e( 'Please add colour which you have enter tag also show default #000', 'tdws-order-tracking-system' ); ?> )</h6>
							</th>
							<td><input type="text" placeholder="<?php esc_html_e( 'Please Order Tag Colours', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[order_tag_colour]" value="<?php echo esc_attr( $order_tag_colour ); ?>" /></td>
						</tr>	
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Tag Text Colour', 'tdws-order-tracking-system' ); ?>
								<h6>( <?php esc_html_e( 'Please add colour which you have enter tag text show default #fff', 'tdws-order-tracking-system' ); ?> )</h6>
							</th>
							<td><input type="text" placeholder="<?php esc_html_e( 'Please Tag Text Colour', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[tag_text_colour]" value="<?php echo esc_attr( $tag_text_colour ); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Order Tracking Subject', 'tdws-order-tracking-system' ); ?>
								<h6>( <?php esc_html_e( 'You can pass order id in subject using [order_id]' ); ?> )</h6>
							</th>
							<td><input type="text" placeholder="<?php esc_html_e( 'Please Order Tracking Subject', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_mail[subject]" value="<?php echo esc_attr( $tdws_track_subject ); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Order Tracking Email Heading', 'tdws-order-tracking-system' ); ?>
								<h6>( <?php esc_html_e( 'You can pass order id in email heading using [order_id]' ); ?> )</h6>
							</th>
							<td><input type="text" placeholder="<?php esc_html_e( 'Please Order Tracking Email Heading', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_mail[email_heading]" value="<?php echo esc_attr( $tdws_track_email_heading ); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Order Tracking Email Before Items HTML', 'tdws-order-tracking-system' ); ?>
								<h6>( <?php esc_html_e( 'You can pass in your body to show [order_id], [first_name], [last_name], [email]' ); ?> )</h6>
							</th>
							<td>
								<?php 																
								wp_editor( $tdws_track_email_top, 'tdws_ord_track_mail_email_top', array( 'media_buttons' => false, 'textarea_name' => 'tdws_ord_track_mail[email_top_html]', 'wpautop' => false ) );
								?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Order Tracking Email After Items HTML', 'tdws-order-tracking-system' ); ?>
								<h6>( <?php esc_html_e( 'You can pass in your body to show [order_id], [first_name], [last_name], [email]' ); ?> )</h6>
							</th>
							<td>
								<?php 																
								wp_editor( $tdws_track_email_bottom, 'tdws_ord_track_mail_email_bottom', array( 'media_buttons' => false, 'textarea_name' => 'tdws_ord_track_mail[email_bottom_html]', 'wpautop' => false ) );
								?>
							</td>
						</tr>	

					</tbody>
					<tfoot>
						<tr>
							<td colspan="2">
								<?php submit_button(); ?>
							</td>
						</tr>
					</tfoot>
				</table>				
			</form>
		</div>
		<?php 
	}	

	/**
	 * Add custom field on order edit page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_add_order_tag_field_order_edit( $order ){		
		?>
		<p class="form-field form-field-wide tdws-order-tag">
			<label for="order_status">
				<?php
				esc_html_e( 'Order Tag:', 'tdws-order-tracking-system' );				
				?>
			</label>
			<select id="tdws_order_tracking_tag" name="tdws_order_tracking_tag" class="wc-enhanced-select">
				<option value=""><?php esc_html_e( 'Select Order Tag:', 'tdws-order-tracking-system' ); ?></option>
				<?php
				$tages = tdws_get_order_tages( 1 );
				$get_tag = get_post_meta( $order->get_id(), 'tdws_order_tracking_tag', true );
				foreach ( $tages as $tag_name ) {
					echo '<option value="' . esc_attr( $tag_name ) . '" ' . selected( $tag_name, $get_tag, false ) . '>' . esc_html( $tag_name ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * Save custom field on order edit page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_save_order_tag_field_order_edit( $order_id, $order ){
		
		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $order_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $order_id ) ) {
			return;
		}

		if( isset($_POST['tdws_order_tracking_tag']) ){
			update_post_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $_POST['tdws_order_tracking_tag'] ) );
			twds_update_order_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $_POST['tdws_order_tracking_tag'] ) );
		}

	}

	/**
	 * Delete hook all functions
	 *
	 * @since    1.4.0
	 */
	public function tdws_delete_hook_call(){	
		
		// Callback delete order tag post meta
		add_action( 'woocommerce_delete_order', array( $this, 'tdws_delete_order_tag_meta' ), 99, 1 );		    

	}

	/**
	 * Delete order tag meta
	 *
	 * @since    1.4.0
	 */
	public function tdws_delete_order_tag_meta( $post_id ){
		delete_post_meta( $post_id, 'tdws_order_tracking_tag' );
		twds_delete_order_meta( $post_id, 'tdws_order_tracking_tag' );
	}

	/**
	 * Add custom column on order list page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_add_order_tag_column_order_list( $columns ){	
		
		$status_position = array_search( 'order_status', array_keys( $columns ) );
		$columns = array_slice( $columns, 0, $status_position + 1, true ) +
		array( 'order_tag' => __( 'Order Tag', 'tdws-order-tracking-system' ) ) +
		array_slice( $columns, $status_position + 1, null, true );
		
		return $columns;
	}

	/**
	 * Show custom column value on order list page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_show_order_tag_column_order_list( $column_name, $post ){	
		if( $column_name == 'order_tag' ){			
			$post_id = $post;
			if( is_object( $post ) ){				
				$post_id = $post->ID;
			}
			$this->render_order_tag_column( $post_id );
		}		
	}

	/**
	 * Rendor custom column value on order list page.
	 *
	 * @since    1.4.0
	 */
	public function render_order_tag_column( $post_id ){		
		$order_tracking_tag = get_post_meta( $post_id , 'tdws_order_tracking_tag' , true );
		if( $order_tracking_tag ){

			$tag_setting = tdws_get_order_tage_color_settings();
			$background_arr = $tag_setting['background'];
			$bg_color = tdws_get_colo_by_tag_name( $background_arr, $order_tracking_tag );
			$text_color = $tag_setting['text_color'];
			$style_str = 'background:'.$bg_color.';color:'.$text_color.';';
			echo '<span class="tdws_tag_badge" style='.esc_attr( $style_str ).'>'.esc_html( $order_tracking_tag ).'</span>';
		} 
	}

	

	/**
	 * Show all tag filter list on order list page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_add_order_tag_filter_list( $which ) {
		global $typenow;
		
		$this->tdws_custom_filter_order_list_table_extra_tablenav( $typenow, $which );
	}

	/**
	 * Show all tag filter list on order list page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_custom_filter_order_list_table_extra_tablenav( $typenow, $which ){	
		if ( 'shop_order' === $typenow && 'top' === $which ) {			
			$tage_view_list = tdws_get_order_tages( 1 );
			if( empty( $tage_view_list ) ){
				$tage_view_list = array();
			}
			$tag_wise_count = tdws_get_all_order_tag_meta_array();
			array_unshift( $tage_view_list, "All" );
			$current_tag = isset( $_GET['tag_view'] ) ? sanitize_text_field( $_GET['tag_view'] ) : 'All';
			echo '<div class="tdws-tag-list">';
			echo '<ul class="tdws-tag-ul">';
			if( $tage_view_list ){
				foreach ( $tage_view_list as $t_key => $t_value ) {
					$class = ( $current_tag == $t_value ) ? "current" : '';

					$tag_filter_url   = ( 'All' === $t_value ) ? remove_query_arg( 'tag_view' ) : add_query_arg( 'tag_view', $t_value );
					$tag_filter_url   = remove_query_arg( array( 'paged', 's' ), $tag_filter_url );

					$tag_cnt = isset($tag_wise_count[$t_value]) ? $tag_wise_count[$t_value] : 0;
					if( $tag_cnt > 0 ){
						echo "<li><a href='".esc_url( $tag_filter_url )."' class='".esc_attr( $class )."'>". esc_html( $t_value ) ." <span class='count'>(".esc_html( $tag_cnt ).")</span></a>";						
						echo "</li>";
					}
					
				}
			}			
			echo '</ul>';
			echo '<input type="hidden" name="tag_view" value="'.$current_tag.'">';
			echo "</div>";	
		}			
	}

	/**
	 * Apply tag filter list on order list page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_apply_order_tag_filter_list( $query ){				
		global $pagenow, $typenow;			
		if ( is_admin() && $pagenow == 'edit.php' && $typenow == 'shop_order' ) {			
			$tag_view = isset($_GET['tag_view']) ? sanitize_text_field( $_GET['tag_view'] ) : '';
			if( $tag_view && $tag_view != 'All' ){
				$query->set( 'meta_key', 'tdws_order_tracking_tag' );
				$query->set( 'meta_value', $tag_view );
			}
		}

	}

	/**
	 * Apply tag filter list on order list page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_apply_order_tag_filter_list_by_table( $order_query_args ){		
		$tag_view = isset($_GET['tag_view']) ? sanitize_text_field( $_GET['tag_view'] ) : '';
		if( $tag_view && $tag_view != 'All' ){
			$order_query_args['meta_query'] = array(
				array(
					'key' => 'tdws_order_tracking_tag',
					'value' => $tag_view, 
					'compare' => 'LIKE',
				),
			);
		}	
		
		return $order_query_args;
	}


}