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
class Tdws_Order_Tracking_System_Order_MetaBox {

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

		// Add TDWS Order Tracking Meta Box
		add_action( "add_meta_boxes", array( $this, "tdws_add_order_tracking_meta_box" ), 99, 2 );
		// Save TDWS Order Tracking Meta Box
		add_action( "woocommerce_process_shop_order_meta", array( $this, "tdws_save_order_tracking_meta_box" ), 99, 2 );
		add_action( "woocommerce_admin_order_item_headers", array( $this, "tdws_add_custom_tracking_column_header" ), 99, 1 );
		add_action( "woocommerce_admin_order_item_values", array( $this, "tdws_add_custom_tracking_column_edit_order" ), 99, 3 );

	}

	/**
	 * Add meta box hook and callback function hook.
	 *
	 * @since    1.0.0
	 */

	public function tdws_add_order_tracking_meta_box() {
		add_meta_box( "tdws-order-tracking-box", __( "TDWS Order Tracking", 'tdws-order-tracking-system' ), array( $this, "tdws_order_tracking_box_html" ), array( "shop_order","
			woocommerce_page_wc-orders" ), "normal", "high", null ); 
	}

	/**
	 * Show meta box html
	 *
	 * @since    1.0.0
	 */
	public function tdws_order_tracking_box_html( $post ){

		$order_id = $post->ID;
		$order = new WC_Order( $order_id );
		$product_items = $order->get_items();		
		$tdws_tracking_status = tdws_get_tracking_statuses( 1 );
		$tdws_enable_tracking = get_post_meta( $order_id, 'tdws_enable_tracking', true );	

		?>
		<div class="tdws-order-tracking-wrap">
			<div class="tdws-enable-field">
				<label class="tdws-label"><?php _e( "Enable Tracking", 'tdws-order-tracking-system' ); ?></label>
				<div class="tdws-main-label">                    
					<label>
						<input type="checkbox" name="tdws_enable_tracking" <?php checked( $tdws_enable_tracking, 'Yes' ); ?> class="tdws_enable_tracking" value="Yes" />
						<span class="tdws-slider tdws-round"></span>
					</label>
				</div>
			</div>
			<div class="tdws-items-box <?php echo ($tdws_enable_tracking != 'Yes') ? "tdws-hide" : ""; ?>">
				<div class="tdws-add-items">
					<button type="button" class="tdws-add-items-btn button button-primary" data-cnt="0"><?php _e( 'Add Tracking', 'tdws-order-tracking-system' ); ?></button>
				</div>
				<div class="tdws-order-tracking-items">
					<?php wp_nonce_field( 'tdws_order_tracking_save', 'tdws_order_tracking_save_field' ); ?>
					<?php 					
					$default_tdws_fields = array(
						'product_items' => $product_items,
						'tdws_tracking_status' => $tdws_tracking_status
					);
					$this->tdws_tracking_box_repeater_html( $order, $default_tdws_fields );
					?>
				</div>
			</div>
		</div>
		<?php	
	}

	public function tdws_tracking_box_repeater_html( $order, $default_tdws_fields ){

		global $wpdb;

		$table_name = $wpdb->prefix.'tdws_order_tracking';
		$order_id = $order->get_id();
		$tdws_tracking_result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE order_id = %d", $order_id ), ARRAY_A );	
		if( is_array( $tdws_tracking_result ) && count($tdws_tracking_result) > 0 ){
			foreach ( $tdws_tracking_result as $tk_key => $tk_value ) {
				include 'tdws-order-tracking-system-metabox-html.php';
			}
		}
		$tk_key = 'tdws_row_no';
		$tk_value = array(
			'id' => 0,
			'order_id' => $order_id,
			'tracking_no' => '',
			'carrier_name' => '',
			'pickup_date' => '',
			'carrier_link' => '',
			'status' => '',
			'hidden_meta' => 'yes',
		);
		include 'tdws-order-tracking-system-metabox-html.php';

	}

	/**
	 * Save meta box hook and callback function hook.
	 *
	 * @since    1.0.0
	 */
	public function tdws_save_order_tracking_meta_box( $order_id, $order ){
		
		// Check the nonce.
		if ( empty( $_POST['tdws_order_tracking_save_field'] ) || ! wp_verify_nonce( wp_unslash( $_POST['tdws_order_tracking_save_field'] ), 'tdws_order_tracking_save' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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

		$tdws_meta = isset($_POST['tdws_meta']) ? $_POST['tdws_meta'] : array();
		$tdws_enable_tracking = isset($_POST['tdws_enable_tracking']) ? sanitize_text_field( $_POST['tdws_enable_tracking'] ) : '';

		update_post_meta( $order_id, 'tdws_enable_tracking', $tdws_enable_tracking );
		twds_update_order_meta( $order_id, 'tdws_enable_tracking', $tdws_enable_tracking );

		global $wpdb;
		$tdws_table1 = $wpdb->prefix.'tdws_order_tracking';
		$tdws_table2 = $wpdb->prefix.'tdws_order_tracking_meta';
		$tdws_up_mail_tk_ids = $tdws_update_tracking_ids = $tdws_send_tracking_ids = array();

		if( $tdws_meta ){
			foreach ( $tdws_meta as $key => $tdws_value ) {
				$tdws_product_ids = 	(isset($tdws_value['product_ids']) && !empty($tdws_value['product_ids'])) ? implode( ',', $tdws_value['product_ids'] ) : '';
				if( $tdws_product_ids ){
					$tdws_tracking_arr = array(					
						'order_id' => $order_id,
						'tracking_no' => $tdws_value['tracking_no'],
						'carrier_name' => $tdws_value['carrier_name'],
						'pickup_date' => $tdws_value['pickup_date'],
						'carrier_link' => $tdws_value['carrier_link'],
						'status' => $tdws_value['status'],
						'create_date' => date( 'Y-m-d H:i:s' ),
						'update_date' => date( 'Y-m-d H:i:s' ),
					);
					$tb1_format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
					if( isset($tdws_value['tdws_item_id']) && !empty($tdws_value['tdws_item_id']) ){					
						$twds_tracking_id = $tdws_value['tdws_item_id'];
						unset( $tdws_tracking_arr['create_date'] );
						$where = [ 'id' => $twds_tracking_id ];
						$wpdb->update( $tdws_table1, $tdws_tracking_arr, $where );
					}else{
						$wpdb->insert(
							$tdws_table1, $tdws_tracking_arr, 
							$tb1_format
						);
						$twds_tracking_id = $wpdb->insert_id;
					}		
					$tdws_meta_list = array(
						array(
							'order_tracking_id' => $twds_tracking_id,
							'meta_key' => 'product_ids',
							'meta_value' => $tdws_product_ids,
						)
					);				
					if( $tdws_meta_list ){
						foreach ( $tdws_meta_list as $key => $tdws_meta_value ) {												
							twds_tracking_update_item_meta( $tdws_meta_value['order_tracking_id'], $tdws_meta_value['meta_key'], $tdws_meta_value['meta_value'] );
						}
					}
					$tdws_update_tracking_ids[] = $twds_tracking_id;			
					if( isset($tdws_value['tdws_send_mail']) && !empty(trim($tdws_value['tdws_send_mail'])) ){
						$tdws_send_tracking_ids = array_merge( $tdws_send_tracking_ids, $tdws_value['product_ids'] );
						$tdws_up_mail_tk_ids[] = $twds_tracking_id;
					}
				}

			}

			
			if( is_array( $tdws_send_tracking_ids ) && count($tdws_send_tracking_ids) > 0 ){
				$this->tdws_send_tracking_mail_by_items( $order_id, $tdws_send_tracking_ids, $tdws_up_mail_tk_ids );
			}
			
			if( is_array( $tdws_update_tracking_ids ) && count($tdws_update_tracking_ids) > 0 ){
				$tk_ids_format = implode(',', array_fill(0, count( $tdws_update_tracking_ids ), '%d')); 	
				$delete_sql_1 = "DELETE FROM ".$tdws_table1." WHERE id NOT IN ( $tk_ids_format ) AND order_id = %d";
				$delete_sql_2 = "DELETE FROM ".$tdws_table2." as t1 LEFT JOIN ".$tdws_table1." as t2 ON t1.order_tracking_id = t2.id WHERE order_tracking_id NOT IN ( $tk_ids_format ) AND t2.order_id = %d";
				if( empty( $tdws_enable_tracking ) || $tdws_enable_tracking == '' ){
					$delete_sql_1 = "DELETE FROM ".$tdws_table1." WHERE id IN ( $tk_ids_format ) AND order_id = %d";
					$delete_sql_2 = "DELETE FROM ".$tdws_table2." as t1 LEFT JOIN ".$tdws_table1." as t2 ON t1.order_tracking_id = t2.id WHERE order_tracking_id IN ( $tk_ids_format ) AND t2.order_id = %d";
				}			
				/* DELETE TDWS Order Tracking */
				$wpdb->query( $wpdb->prepare( $delete_sql_1, array_merge( $tdws_update_tracking_ids, array( $order_id ) ) ) );
				/* DELETE TDWS Order Tracking Meta */
				$wpdb->query( $wpdb->prepare( $delete_sql_2, $tdws_update_tracking_ids ) );	
			}

		}
		if( is_array( $tdws_update_tracking_ids ) && count($tdws_update_tracking_ids) == 0 ){

			/* DELETE TDWS Order Tracking Meta */
			$delete_sql_2 = "DELETE FROM FROM $tdws_table2 as t1
			LEFT JOIN $tdws_table1 as t2 ON t1.order_tracking_id = t2.id
			WHERE t2.order_id = %d";

			$wpdb->query( $wpdb->prepare( $delete_sql_2,  $order_id ) );

			$delete_sql_1 = "DELETE FROM ".$tdws_table1." WHERE order_id = %d";				
			/* DELETE TDWS Order Tracking */
			$wpdb->query( $wpdb->prepare( $delete_sql_1,  $order_id ) );
		}
	}	

	public function tdws_send_tracking_mail_by_items( $order_id, $tdws_product_ids, $tdws_up_mail_tk_ids ){
		
		$order = wc_get_order( $order_id );

		$tdws_ord_track_mail = get_option( 'tdws_ord_track_mail' );		

		$default_track_subject = apply_filters( 'tdws_order_tracking_mail_subject', 'Order Tracking' );
		$default_track_heading = apply_filters( 'tdws_order_tracking_mail_email_heading', 'Order Tracking' );
		$default_track_email_top = apply_filters( 'tdws_order_tracking_mail_before_item_html', 'Hii [first_name]' );

		$tdws_track_subject = isset($tdws_ord_track_mail['subject']) ? $tdws_ord_track_mail['subject'] : $default_track_subject;
		$tdws_track_email_heading = isset($tdws_ord_track_mail['email_heading']) ? $tdws_ord_track_mail['email_heading'] : $default_track_heading;		
		$tdws_track_email_top = isset($tdws_ord_track_mail['email_top_html']) ? $tdws_ord_track_mail['email_top_html'] : $default_track_email_top;
		$tdws_track_email_bottom = isset($tdws_ord_track_mail['email_bottom_html']) ? $tdws_ord_track_mail['email_bottom_html'] : '';
		
		$email  = $order->get_billing_email();
		$items = $order->get_items( 'line_item' );

		$tdws_track_subject = str_replace( '[order_id]', $order_id, $tdws_track_subject );
		$tdws_track_email_heading = str_replace( '[order_id]', $order_id, $tdws_track_email_heading );
		$tdws_track_email_top = str_replace( '[first_name]', $order->get_billing_first_name(), $tdws_track_email_top );
		$tdws_track_email_top = str_replace( '[last_name]', $order->get_billing_last_name(), $tdws_track_email_top );
		$tdws_track_email_top = str_replace( '[email]', $order->get_billing_email(), $tdws_track_email_top );
		$tdws_track_email_top = str_replace( '[order_id]', $order_id, $tdws_track_email_top );

		$tdws_track_email_bottom = str_replace( '[first_name]', $order->get_billing_first_name(), $tdws_track_email_bottom );
		$tdws_track_email_bottom = str_replace( '[last_name]', $order->get_billing_last_name(), $tdws_track_email_bottom );
		$tdws_track_email_bottom = str_replace( '[email]', $order->get_billing_email(), $tdws_track_email_bottom );
		$tdws_track_email_bottom = str_replace( '[order_id]', $order_id, $tdws_track_email_bottom );

		$email_heading  = $tdws_track_email_heading;

		$tdws_tracking_items = $tdws_product_ids;
		
		$show_sku =  apply_filters( 'tdws_order_tracking_product_mail_show_sku', true );
		$show_image =  apply_filters( 'tdws_order_tracking_product_mail_show_img', true );
		$image_size =  apply_filters( 'tdws_order_tracking_product_mail_image_size', array( 32, 32 ) );
		$css = $html = '';

		ob_start();
		include plugin_dir_path(dirname( __DIR__ )).'admin/templates/emails/tdws-email-template.php';
		$html = ob_get_clean();

		ob_start();
		wc_get_template( 'emails/email-styles.php' );
		$css = ob_get_clean();

		$content = '<style type="text/css">' . $css . '</style>' . $html;
		$mailFlag = tdws_custom_send_mail( $email, $tdws_track_subject, $content, '', array() );

		if( $tdws_up_mail_tk_ids ){
			foreach ( $tdws_up_mail_tk_ids as $t_object_id ) {				
				$obj_meta_key = 'tdws_mail_fail';
				if( $mailFlag ){
					$obj_meta_key = 'tdws_mail_success';
				}
				twds_tracking_update_item_meta( $t_object_id, $obj_meta_key, date( 'Y-m-d H:i:s' ), true );
			}	
		}		
	}

	/**
	 * Show tracking header box hook and callback function hook.
	 *
	 * @since    1.0.0
	 */
	public function tdws_add_custom_tracking_column_header( $order ){
		?>
		<th class="tdws-tracking-header">
			<?php _e( 'TDWS Tracking', 'tdws-order-tracking-system' ); ?>
		</td>
		<?php
	}

	/**
	 * Show tracking info box hook and callback function hook.
	 *
	 * @since    1.0.0
	 */
	public function tdws_add_custom_tracking_column_edit_order( $product, $item, $item_id ){
		$tracking_item_list = twds_tracking_data_by_item_id( $item_id, 1 );		
		if( !$tracking_item_list ){
			return false;
		}		
		?>
		<td class="tdws-tracking-info">
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
					<div class="view">
						<ul>
							<?php 
							if( $tdws_tracking_no ){
								?>
								<li><strong><?php _e( "Tracking No", 'tdws-order-tracking-system' ); ?></strong> : <?php echo esc_html( $tdws_tracking_no ); ?></li>
								<?php
							}
							if( $tdws_carrier_name ){
								if( $tdws_carrier_link ){
									?>
									<li><strong><?php _e( "Carrier Name", 'tdws-order-tracking-system' ); ?></strong> : <a href="<?php echo esc_url( $tdws_carrier_link ); ?>" target="_blank"><?php echo esc_html( $tdws_carrier_name ); ?></a></li>
									<?php	
								}else{
									?>
									<li><strong><?php _e( "Carrier Name", 'tdws-order-tracking-system' ); ?></strong> : <?php echo esc_html( $tdws_carrier_name ); ?></li>
									<?php	
								}
							}
							if( !empty( trim($tdws_pickup_date) ) && $tdws_pickup_date != "0000-00-00 00:00:00" ){
								?>
								<li><strong><?php _e( "Pickup Date", 'tdws-order-tracking-system' ); ?></strong> : <?php echo esc_html( date( $tdws_date_format, strtotime( $tdws_pickup_date ) ) ); ?></li>
								<?php
							}
							if( $tdws_tracking_status ){
								?>
								<li><strong><?php _e( "Status", 'tdws-order-tracking-system' ); ?></strong> : <?php echo esc_html( $tdws_tracking_status ); ?></li>
								<?php
							}
							?>										
						</ul>
					</div>
					<?php
				}
			}
			?>			
		</td>
		<?php
	}
}