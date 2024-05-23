<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://tdwebservices.com
 * @since      1.4.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/public
 * @author     TD Web Services <info@tdwebservices.com>
 */
class Tdws_Order_Tracking_System_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		include 'partials/tdws-order-tracking-system-public-display.php';

		/* Default TDWS Tag Save Hook */
		add_action( 'woocommerce_new_order', array( $this, 'tdws_default_tag_save_setting' ), 99, 1 );

		/* Show TDWS Tracking Info On View Order Page */
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'tdws_show_tracking_info_view_order' ), 99, 4 );

		$tdws_active_plugin_list = apply_filters('active_plugins', get_option('active_plugins') );

		$this->tdws_active_plugin_list = $tdws_active_plugin_list;

		if( is_array( $this->tdws_active_plugin_list ) && in_array('dokan-lite/dokan.php', $this->tdws_active_plugin_list ) ){ 			
			add_action( 'dokan_order_inside_content', array( $this, 'tdws_dokan_order_inside_content_order_tags' ), 12 );
			add_action( 'dokan_order_listing_header_before_action_column', array( $this, 'tdws_add_extra_column_dokan_order_list' ), 20 );
			add_action( 'dokan_order_listing_row_before_action_field', array( $this, 'tdws_show_extra_column_dokan_order_list' ), 20, 1 );
			add_action( 'dokan_order_detail_after_order_general_details', array( $this, 'tdws_add_order_tag_to_edit_order_page' ), 20, 1 );
			add_action( 'wp_ajax_tdws_order_tag_status', array( $this, 'tdws_change_order_tag' ) );
			add_filter( 'dokan_get_vendor_orders_args',  array( $this, 'tdws_dokan_get_vendor_orders_args' ) , 99, 3 );
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		if( is_array( $this->tdws_active_plugin_list ) && in_array('dokan-lite/dokan.php', $this->tdws_active_plugin_list ) ){ 			
			if( dokan_is_seller_dashboard() ){
				wp_enqueue_style( $this->plugin_name.'-dokan-dashboard', plugin_dir_url( __FILE__ ) . 'css/tdws-dokan-dashboard.css', array(), $this->version, 'all' );
			}
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tdws-order-tracking-system-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		if( is_array( $this->tdws_active_plugin_list ) && in_array('dokan-lite/dokan.php', $this->tdws_active_plugin_list ) ){ 			
			if( dokan_is_seller_dashboard() ){
				wp_enqueue_script( $this->plugin_name.'-dokan-dashboard', plugin_dir_url( __FILE__ ) . 'js/tdws-dokan-dashboard.js', array( 'jquery' ), $this->version, false );
			}
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tdws-order-tracking-system-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * TDWS Default Tag Save Callback function
	 *
	 * @since    1.4.0
	 */
	public function tdws_default_tag_save_setting( $order_id ) {
		$tdws_ord_track_opt = get_option( 'tdws_ord_track_opt' );				
		$set_default_order_tag = (isset($tdws_ord_track_opt['set_default_order_tag']) && !empty($tdws_ord_track_opt['set_default_order_tag'])) ? $tdws_ord_track_opt['set_default_order_tag'] : 'New';
		update_post_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $set_default_order_tag ) );
		twds_update_order_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $set_default_order_tag ) );
	} 

	/**
	 * TDWS Show Column Filter Page
	 *
	 * @since    1.4.0
	 */
	public function tdws_dokan_order_inside_content_order_tags(){
		$tage_view_list = tdws_get_order_tages( 1 );
		if( empty( $tage_view_list ) ){
			$tage_view_list = array();
		}
		$tag_wise_count = tdws_get_all_order_tag_meta_array( 1 );		
		array_unshift( $tage_view_list, "All" );
		$current_tag = isset( $_GET['order_tag'] ) ? sanitize_text_field( $_GET['order_tag'] ) : 'All';

		$orders_url    = dokan_get_navigation_url( 'orders' );		
		$filter_nonce  = wp_create_nonce( 'seller-order-filter-nonce' );
		

		echo '<div class="tdws-tag-list">';
		echo '<ul class="tdws-tag-ul">';
		if( $tage_view_list ){
			foreach ( $tage_view_list as $t_key => $t_value ) {
				$class = ( $current_tag == $t_value ) ? "active" : '';				

            // Get filtered orders url based on order status.				
				$url_args = array(			
					'order_tag'              => $t_value,
					'seller_order_filter_nonce' => $filter_nonce,
				);
				$tag_filter_url = add_query_arg( $url_args, $orders_url );

				$tag_cnt = isset($tag_wise_count[$t_value]) ? $tag_wise_count[$t_value] : 0;
				if( $tag_cnt > 0 ){
					echo "<li><a href='".esc_url( $tag_filter_url )."' class='".esc_attr( $class )."'>". esc_html( $t_value ) ." <span class='count'>(".esc_html( $tag_cnt ).")</span></a>";					
					echo "</li>";
				}

			}
		}			
		echo '</ul>';
		echo "</div>";	
	}

	/**
	 * TDWS Add Column Order Page
	 *
	 * @since    1.4.0
	 */
	public function tdws_add_extra_column_dokan_order_list(){
		?>
		<th><?php esc_html_e( 'Order Tag', 'tdws-order-tracking-system' ); ?></th>
		<?php
	}

	/**
	 * TDWS Show Column Order Page
	 *
	 * @since    1.4.0
	 */
	public function tdws_show_extra_column_dokan_order_list( $order ){
		$order_tracking_tag = get_post_meta( $order->get_id() , 'tdws_order_tracking_tag' , true );
		?>
		<td class="dokan-order-tag-column">
			<?php 
			if( $order_tracking_tag ){
				$tag_setting = tdws_get_order_tage_color_settings();
				$background_arr = $tag_setting['background'];
				$bg_color = tdws_get_colo_by_tag_name( $background_arr, $order_tracking_tag );
				$text_color = $tag_setting['text_color'];
				$style_str = 'background:'.$bg_color.';color:'.$text_color.';';
				echo '<span class="tdws_tag_badge" style='.esc_attr( $style_str ).'>'.esc_html( $order_tracking_tag ).'</span>';
			} else {
				echo '-';
			}
			?>
		</td>
		<?php
	}

	/**
	 * TDWS Add Order Tag to Edit Order Page
	 *
	 * @since    1.4.0
	 */
	public function tdws_add_order_tag_to_edit_order_page( $order ){
		$get_tag = get_post_meta( $order->get_id() , 'tdws_order_tracking_tag' , true );
		?>
		<div class="" style="width:100%">
			<div class="dokan-panel dokan-panel-default">
				<div class="dokan-panel-heading"><strong><?php esc_html_e( 'TDWS Field Details', 'tdws-order-tracking-system' ); ?></strong></div>
				<div class="dokan-panel-body general-details">
					<ul class="list-unstyled tdws-order-tag">
						<li>
							<span><?php esc_html_e( 'Order Tag:', 'tdws-order-tracking-system' ); ?></span>
							<?php 
							echo wp_kses_post( $this->tdws_order_tag_label( $get_tag ) );
							?>
							<a href="#" class="tdws-edit-tag"><small><?php esc_html_e( '&nbsp; Edit', 'tdws-order-tracking-system' ); ?></small></a>
						</li>
						<li class="dokan-hide">
							<form id="tdws-order-tag-form" action="" method="post">
								<select id="order_tag" name="order_tag" class="form-control">
									<option value=""><?php esc_html_e( 'Select Order Tag:', 'tdws-order-tracking-system' ); ?></option>
									<?php
									$tages = tdws_get_order_tages( 1 );
									foreach ( $tages as $tag_name ) {
										echo '<option value="' . esc_attr( $tag_name ) . '" ' . selected( $tag_name, $get_tag, false ) . '>' . esc_html( $tag_name ) . '</option>';
									}
									?>
								</select>
								<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
								<input type="hidden" name="action" value="tdws_order_tag_status">
								<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'tdws_order_tag_status_nonce' ) ); ?>">
								<input type="submit" class="dokan-btn dokan-btn-success dokan-btn-sm" name="tdws_order_tag_status" value="<?php esc_attr_e( 'Update', 'tdws-order-tracking-system' ); ?>">

								<a href="#" class="dokan-btn dokan-btn-default dokan-btn-sm tdws-cancel-tag"><?php esc_html_e( 'Cancel', 'tdws-order-tracking-system' ); ?></a>
							</form>
						</li>

					</ul>

				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Update a order tag
	 *
	 * @return void
 	*/
	public function tdws_change_order_tag() {

		check_ajax_referer( 'tdws_order_tag_status_nonce' );

		if ( ! current_user_can( 'dokan_manage_order' ) ) {
			wp_send_json_error( __( 'You have no permission to manage this order', 'dokan-lite' ) );
			return;
		}

		$order_id     = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : '';
		$order_tag = isset( $_POST['order_tag'] ) ? sanitize_text_field( wp_unslash( $_POST['order_tag'] ) ) : '';

		update_post_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $order_tag ) );
		twds_update_order_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $order_tag ) );

		$html = $this->tdws_order_tag_label( $order_tag );

		wp_send_json_success( $html );
	}

	public function tdws_order_tag_label( $get_tag ){
		ob_start();		
		?>
		<label class="dokan-label tdws-tag-label">
			<?php 
			if( $get_tag ){
				$tag_setting = tdws_get_order_tage_color_settings();
				$background_arr = $tag_setting['background'];
				$bg_color = tdws_get_colo_by_tag_name( $background_arr, $get_tag );
				$text_color = $tag_setting['text_color'];
				$style_str = 'background:'.$bg_color.';color:'.$text_color.';';
				echo '<span class="tdws_tag_badge" style='.esc_attr( $style_str ).'>'.esc_html( $get_tag ).'</span>';
			} else {
				echo '-';
			}
			?>
		</label>
		<?php
		return ob_get_clean();
	}

	public function tdws_dokan_get_vendor_orders_args( $all_args, $args, $q_arr = array() ) {
		$order_tag = isset($_GET['order_tag']) ? sanitize_text_field( $_GET['order_tag'] ) : '';
		if( $order_tag && $order_tag != 'All' ){	
			$all_args['meta_query'][] = array(
				'key' => 'tdws_order_tracking_tag',
				'value' => $order_tag,
				'compare' =>'=',
			);	
		}
		return $all_args;
	}

	/* Show TDWS Tracking Information View Order */
	public function tdws_show_tracking_info_view_order( $item_id, $item, $order, $plain_text ){
		$tracking_item_list = twds_tracking_data_by_item_id( $item_id, 1 );		

		global $post;
		if ( is_a( $post, 'WP_Post' ) && !has_shortcode( $post->post_content, 'woocommerce_order_tracking') && !is_wc_endpoint_url() && !$tracking_item_list  ) {
			return false;
		}
		?>
		<div class="tdws-tracking-item">
			<?php 
			if( $tracking_item_list ){
				foreach ( $tracking_item_list as $tracking_item_info ) {
					$tdws_tracking_no = isset($tracking_item_info['tracking_no']) ? trim( $tracking_item_info['tracking_no'] ) : '';
					$tdws_carrier_name = isset($tracking_item_info['carrier_name']) ? trim( $tracking_item_info['carrier_name'] ) : '';
					$tdws_pickup_date = isset($tracking_item_info['pickup_date']) ? trim( $tracking_item_info['pickup_date'] ) : '';
					$tdws_carrier_link = isset($tracking_item_info['carrier_link']) ? trim( $tracking_item_info['carrier_link'] ) : '';
					$tdws_tracking_status = isset($tracking_item_info['status']) ? trim( $tracking_item_info['status'] ) : '';
					$tdws_date_format = (get_option('date_format')) ? get_option('date_format') : 'd/m/Y';
					?>
					<div class="tdws-tracking-item-box">
						<ul>
							<?php 
							if( $tdws_tracking_no ){
								?>
								<li><strong><?php _e( "Tracking No", 'tdws-order-tracking-system' ); ?></strong> : <span><?php echo esc_html( $tdws_tracking_no ); ?></span></li>
								<?php
							}
							if( $tdws_carrier_name ){
								if( $tdws_carrier_link ){
									?>
									<li><strong><?php _e( "Carrier Name", 'tdws-order-tracking-system' ); ?></strong> : <span><a href="<?php echo esc_url( $tdws_carrier_link ); ?>" target="_blank"><?php echo esc_html( $tdws_carrier_name ); ?></a></span></li>
									<?php	
								}else{
									?>
									<li><strong><?php _e( "Carrier Name", 'tdws-order-tracking-system' ); ?></strong> : <span><?php echo esc_html( $tdws_carrier_name ); ?></span></li>
									<?php	
								}
							}
							if( !empty( trim($tdws_pickup_date) ) && $tdws_pickup_date != "0000-00-00 00:00:00" ){
								?>
								<li><strong><?php _e( "Pickup Date", 'tdws-order-tracking-system' ); ?></strong> : <span><?php echo esc_html( date( $tdws_date_format, strtotime( $tdws_pickup_date ) ) ); ?></span></li>
								<?php
							}
							if( $tdws_tracking_status ){
								?>
								<li><strong><?php _e( "Status", 'tdws-order-tracking-system' ); ?></strong> : <span><?php echo esc_html( $tdws_tracking_status ); ?></span></li>
								<?php
							}
							?>		
						</ul>
					</div>
					<?php
				}
			}
			?>			
		</div>
		<?php
	}

}


