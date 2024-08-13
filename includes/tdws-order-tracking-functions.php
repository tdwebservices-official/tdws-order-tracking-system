<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://tdwebservices.com
 * @since      1.0.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/includes
 */


/**
* Saving array data sanitize function	 	
*/
function tdws_array_data_sanitize($value) {
	if ( is_array( $value ) ) {
		// If the value is an array, recursively sanitize it
		$value = array_map( 'tdws_array_data_sanitize', $value );
	} else {
		// Sanitize the value using sanitize_text_field()
		// $value = sanitize_text_field( $value );
		$allowed_html = wp_kses_allowed_html( 'post' );
		// Allow script and style tags
		$allowed_html['script'] = array();
		$allowed_html['style'] = array();
		// Sanitize the value
		//$value = wp_kses( $value, $allowed_html );			
	}
	return $value;
}

function tdws_set_timezone(){
	$defualt_timezone = wp_timezone()->getName();
	$defualt_timezone = apply_filters( 'set_tdws_custom_timezone', $defualt_timezone );
	if( !empty( $defualt_timezone ) ){
		date_default_timezone_set( $defualt_timezone );
	}	
}

/*Update order meta*/
function twds_update_order_meta( $object_id = 0, $meta_key = '', $meta_value = '' ){
	global $wpdb;
	$table_name = $wpdb->base_prefix.'wc_orders_meta';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
	if ( $wpdb->get_var( $query ) == $table_name ) {
		$meta_result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE meta_key = %s AND order_id = %d", $meta_key, $object_id ), ARRAY_A );	

		if ( !isset($meta_result['id']) ) {
			$result = $wpdb->insert(
				$table_name,
				array(
					'order_id' => $object_id,
					'meta_key'   => $meta_key,
					'meta_value' => maybe_serialize( $meta_value ),
				)
			);
		}else{

			$meta_value  = maybe_serialize( $meta_value );
			$where = array(
				'id'    => $meta_result['id'],
			);

			$data = [ 'meta_value' => $meta_value,  'update_date' => date( 'Y-m-d H:i:s' ) ];

			$wpdb->update( $table_name, $data, $where );
		}
	}
}

function twds_tracking_update_item_meta( $object_id = 0, $meta_key = '', $meta_value = '', $always_add = false ){
	global $wpdb;	
	$table_name = $wpdb->base_prefix.'tdws_order_tracking_meta';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
	if ( $wpdb->get_var( $query ) == $table_name ) {
		$meta_result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE meta_key = %s AND order_tracking_id = %d", $meta_key, $object_id ), ARRAY_A );	

		if ( !isset($meta_result['meta_id']) || $always_add == true ) {
			$result = $wpdb->insert(
				$table_name,
				array(
					'order_tracking_id' => $object_id,
					'meta_key'   => $meta_key,
					'meta_value' => maybe_serialize( $meta_value ),
					'create_date' => date( 'Y-m-d H:i:s' ),
					'update_date' => date( 'Y-m-d H:i:s' ),
				)
			);
		}else{			
			$meta_value  = maybe_serialize( $meta_value );
			$where = array(
				'meta_id'    => $meta_result['meta_id'],
			);
			$data = [ 'meta_value' => $meta_value, 'update_date' => date( 'Y-m-d H:i:s' ) ];
			$wpdb->update( $table_name, $data, $where );
		}
	}
}

function twds_tracking_update_status( $order_id = 0, $tracking_id = 0, $tdws_tracking_status_arr = array() ){
	global $wpdb;	
	$table_name = $wpdb->base_prefix.'tdws_order_tracking_status';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
	if ( $wpdb->get_var( $query ) == $table_name ) {		
		$meta_result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE tracking_id = %d AND order_id = %d", $tracking_id, $order_id ), ARRAY_A );	
		if ( !isset($meta_result['id']) ) {
			$tdws_tracking_status_arr = array(					
				'tracking_id' => $tracking_id,
				'order_id' => $order_id,
				'not_found' => date( 'Y-m-d H:i:s' ),						
				'create_date' => date( 'Y-m-d H:i:s' ),
				'update_date' => date( 'Y-m-d H:i:s' ),
			);
			$tb_format = array( '%d', '%d', '%s',  '%s', '%s' );
			$wpdb->insert(
				$table_name, $tdws_tracking_status_arr, 
				$tb_format
			);			
		}else{			
			$meta_value  = maybe_serialize( $meta_value );
			$where = array(
				'id' => $meta_result['id'],
			);
			$data = [ 'update_date' => date( 'Y-m-d H:i:s' ) ];
			$wpdb->update( $table_name, $data, $where );
		}
	}
}

function twds_tracking_get_item_meta( $object_id = 0, $meta_key = '', $type = 0 ){
	global $wpdb;	
	$table_name = $wpdb->base_prefix.'tdws_order_tracking_meta';
	$meta_result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE meta_key = %s AND order_tracking_id = %d", $meta_key, $object_id ), ARRAY_A );	
	$meta_value = '';
	if( is_array( $meta_result ) && !empty( $meta_result ) ){
		if( $type == 1 ){
			return $meta_result;
		}else{
			$meta_result = isset($meta_result[0]['meta_value']) ? $meta_result[0]['meta_value'] : '';
			$meta_value = $meta_result;
		}
	}	
	return $meta_value;
}

function twds_tracking_data_by_item_id( $item_id = 0, $type = 0 ){
	global $wpdb;	
	$meta_key =  'product_ids';
	$table_name_1 = $wpdb->base_prefix.'tdws_order_tracking';
	$table_name_2 = $wpdb->base_prefix.'tdws_order_tracking_meta';
	$meta_sql = "SELECT t2.tracking_no, t2.carrier_name, t2.pickup_date, t2.create_date as m_create_date, t2.carrier_link, t2.status, t1.meta_id, t1.order_tracking_id, t1.meta_key, t1.meta_value FROM $table_name_2 as t1
	LEFT JOIN $table_name_1 as t2 ON t1.order_tracking_id = t2.id
	WHERE t1.meta_key = %s AND FIND_IN_SET( %s, t1.meta_value )";
	$meta_result = $wpdb->get_results( $wpdb->prepare( $meta_sql, $meta_key, $item_id ), ARRAY_A );	
	$meta_value = '';
	if( is_array( $meta_result ) && !empty( $meta_result ) ){
		if( $type == 1 ){
			return $meta_result;
		}else if( $type == 2 ){
			return isset($meta_result[0]) ? $meta_result[0] : array();
		}else{
			$meta_result = isset($meta_result[0]['meta_value']) ? $meta_result[0]['meta_value'] : '';
			$meta_value = $meta_result;
		}
	}	
	return $meta_value;
}

function twds_tracking_all_item_meta( $object_id = 0 ){
	global $wpdb;	
	$table_name = $wpdb->base_prefix.'tdws_order_tracking_meta';
	$meta_result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE order_tracking_id = %d", $object_id ), ARRAY_A );	
	$meta_value_array = array();
	if( is_array( $meta_result ) && !empty( $meta_result ) ){
		foreach ( $meta_result as $key => $m_value ) {
			$meta_value_array[$m_value['meta_key']] = $m_value['meta_value'];
		}		
	}	
	return $meta_value_array;
}

function twds_delete_order_meta( $object_id = 0, $meta_key = '' ){
	global $wpdb;
	$table_name = $wpdb->base_prefix.'wc_orders_meta';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
	if ( $wpdb->get_var( $query ) == $table_name ) {
		$wpdb->delete( $table_name, array( 'order_id' => $object_id, 'meta_key' => $meta_key ) );				
	}
}

/**
 * Get all order tag by count on order list page.
 *
 * @since    1.0.0
 */
function tdws_get_all_order_tag_meta_array( $dokan_order = 0 ){

	global $wpdb;	
	$all_tag_cnt = 0;			
	$meta_key = 'tdws_order_tracking_tag';	
	if( $dokan_order == 1 ){
		$dokan_user_id = dokan_get_current_user_id();
		$status_query = "SELECT count( pm.meta_value ) as order_cnt, pm.meta_value as tag_name  FROM {$wpdb->prefix}postmeta as pm
		LEFT JOIN {$wpdb->prefix}dokan_orders as dk ON pm.post_id = dk.order_id WHERE pm.meta_key = %s AND dk.seller_id = %d  GROUP BY pm.meta_value;";	
		$tag_wise_list =  $wpdb->get_results( $wpdb->prepare( $status_query, $meta_key, $dokan_user_id ), ARRAY_A );
	} else{
		$status_query = "SELECT count( meta_value ) as order_cnt, meta_value as tag_name  FROM {$wpdb->prefix}postmeta WHERE `meta_key` = %s GROUP BY meta_value;";
		$tag_wise_list =  $wpdb->get_results( $wpdb->prepare( $status_query, $meta_key ), ARRAY_A );
	}
	
	$order_tag_wise_cnt = array();
	if( $tag_wise_list ){
		foreach ( $tag_wise_list as $p_key => $p_value ) {
			if( isset( $p_value['tag_name'] ) ){
				$order_tag_wise_cnt[$p_value['tag_name']] = $p_value['order_cnt'];	
				$all_tag_cnt = $all_tag_cnt + (float)$p_value['order_cnt'];
			}				
		}
	}
	$order_tag_wise_cnt['All'] = $all_tag_cnt;	
	return $order_tag_wise_cnt;
}

/**
 * List All Order Tags
 *
 * @since    1.0.0
 */
function tdws_get_order_tages( $type = 0 ){
	
	$default_tag_list = array(
		'New',
		'Processing',
		'Awaiting Stock', 
		'Payment Received'
	);

	$tdws_ord_track_opt = get_option( 'tdws_ord_track_opt' );
	$add_order_tags = isset($tdws_ord_track_opt['add_order_tags']) ? $tdws_ord_track_opt['add_order_tags'] : implode( ',', $default_tag_list );

	if( $type == 1 ){
		$add_order_tags = explode( ',', $add_order_tags );
		$add_order_tags = array_map( 'trim', $add_order_tags );
	}	
	$add_order_tags = apply_filters( 'custom_tdws_get_order_tages', $add_order_tags, $type );
	if( $type == 2 ){
		$add_order_tags = explode( ',', $add_order_tags );
		$add_order_tags = array_map( 'trim', $add_order_tags );
		return array( 'option' => $tdws_ord_track_opt, 'tags' => $add_order_tags );
	}
	return $add_order_tags;
}

function tdws_get_tracking_statuses( $type = 0 ){	
	$default_status_list = array(
		'Shipped',
		'On Hold',
		'Delivered', 
		'Cancelled'
	);
	$tdws_ord_track_opt = get_option( 'tdws_ord_track_opt' );
	$add_tracking_status = isset($tdws_ord_track_opt['add_tracking_status']) ? $tdws_ord_track_opt['add_tracking_status'] : implode( ',', $default_status_list );
	if( $type == 1 ){
		$add_tracking_status = explode( ',', $add_tracking_status );
		$add_tracking_status = array_map( 'trim', $add_tracking_status );
	}	
	$add_tracking_status = apply_filters( 'custom_tdws_get_tracking_statues', $add_tracking_status, $type );
	if( $type == 2 ){
		$add_tracking_status = explode( ',', $add_tracking_status );
		$add_tracking_status = array_map( 'trim', $add_tracking_status );
		return array( 'option' => $tdws_ord_track_opt, 'tags' => $add_tracking_status );
	}
	return $add_tracking_status;
}

function tdws_get_all_carrier_list(){
	$carrier_list = array();
	$cache_carrier_list = get_transient( 'tdws_api_carrier_list' );	
	$tdws_carrier_items = array();
	$carrier_list = file_get_contents( plugin_dir_url( __DIR__ ).'admin/json/apicarrier.json' );
	if( $carrier_list ){
		$carrier_list = json_decode( $carrier_list, true );
		if( $carrier_list ){				
			
			foreach ( $carrier_list as $key => $carrier_item ) {
				if( isset($carrier_item['key']) && !empty( $carrier_item['key'] ) && isset($carrier_item['_name'] ) && !empty( $carrier_item['_name'] )  ){
					$tdws_carrier_items[] = array(
						'code' => trim($carrier_item['key']),
						'name' => trim($carrier_item['_name']),
					);	
				}					
			}
			set_transient( 'tdws_api_carrier_list', $tdws_carrier_items );	
		}		
	}
	$tdws_carrier_items = apply_filters( 'tdws_api_carrier_list_values', $tdws_carrier_items );
	return $tdws_carrier_items;
}

/**
 * List All Order Colour Wise Tags
 *
 * @since    1.0.0
 */
function tdws_get_order_tage_color_settings(){
	$all_tag_setting = tdws_get_order_tages( 2 );	
	$tdws_tags_list = $all_tag_setting['tags'];	
	$tdws_ord_track_opt = $all_tag_setting['option'];	
	$order_tag_colour = isset($tdws_ord_track_opt['order_tag_colour']) ? trim($tdws_ord_track_opt['order_tag_colour']) : '';
	$tag_text_colour = isset($tdws_ord_track_opt['tag_text_colour']) ? trim($tdws_ord_track_opt['tag_text_colour']) : '';
	$tag_color = $tdws_color_setting = $tag_colour_position  = array();
	if( $order_tag_colour ){
		$tag_colour_position = explode( ',' , $order_tag_colour );
	}
	if( $tdws_tags_list ){
		foreach( $tdws_tags_list as $tag_key => $tag_name ){
			$default_color = '#000000';
			if( isset($tag_colour_position[$tag_key]) && trim( $tag_colour_position[$tag_key] ) ){
				$default_color = $tag_colour_position[$tag_key];
			}
			$tag_color[$tag_name] = $default_color;
		}
	}

	$tdws_color_setting = array(
		'background' => $tag_color,
		'text_color' => ($tag_text_colour) ? $tag_text_colour : '#ffffff'
	);
	return $tdws_color_setting;
}

function tdws_17track_tracking_status(){
	$status_arr = array(
		"not_found" => array( "NotFound_Other","NotFound_InvalidCode" ),
		"info_received" => array( "InfoReceived" ), 
		"in_transit" => array( "InTransit_PickedUp", "InTransit_Other", "InTransit_Departure", "InTransit_Arrival", "InTransit_CustomsProcessing", "InTransit_CustomsReleased", "InTransit_CustomsRequiringInformation" ),
		"expired" => array( "Expired_Other" ),
		"available_for_pickup" => array( "AvailableForPickup_Other" ),
		"out_for_delivery" => array( "OutForDelivery_Other" ),
		"delivery_failure" => array( "DeliveryFailure_Other","DeliveryFailure_NoBody","DeliveryFailure_Security","DeliveryFailure_Rejected","DeliveryFailure_InvalidAddress" ),
		"delivered" => array( "Delivered_Other" ),
		"exception" => array( "Exception_Other","Exception_Returning","Exception_Returned","Exception_NoBody","Exception_Security","Exception_Damage","Exception_Rejected","Exception_Delayed","Exception_Lost","Exception_Destroyed","Exception_Cancel" )
	);	
	$status_arr = apply_filters( 'custom_tdws_get_17track_api_statues', $status_arr );
	return $status_arr;
}

function tdws_17track_mail_tracking_status(){
	$status_arr = array(
		"not_found" => 'No Found',
		"info_received" => "Info Received", 
		"in_transit" => "In Transit",		
		"out_for_delivery" => "Out For Delivery",
		"delivery_failure" => "Delivery Failure",
		"delivered" => "Delivered",
		"exception" => "Exception"
	);	
	$status_arr = apply_filters( 'custom_tdws_get_17track_mail_api_statues', $status_arr );
	return $status_arr;
}

function tdws_17track_mail_tracking_report_color_status(){
	$status_color_arr = array(
		"not_found" => '#FF6347',
		"info_received" => '#FFD700', 
		"in_transit" =>  '#1E90FF',		
		"out_for_delivery" => '#32CD32',
		"delivery_failure" => '#FF4500',
		"delivered" => '#7CFC00',
		"exception" => '#FF69B4'
	);	
	$status_color_arr = apply_filters( 'custom_tdws_get_17track_report_color_statues', $status_color_arr );
	return $status_color_arr;
}

function tdws_17track_mail_tracking_status_mail_info(){
	$mail_body_arr = array(
		"not_found" => array(
			'subject' => 'No Found',
			'email_heading' => 'No Found',
			'email_body' => '[tdws_tracking_table]'
		),
		"info_received" => array(
			'subject' => "Info Received",
			'email_heading' => "Info Received",
			'email_body' => '[tdws_tracking_table]'
		),
		"in_transit" => array(
			'subject' => "In Transit",
			'email_heading' => "In Transit",
			'email_body' => '[tdws_tracking_table]'
		),	
		"out_for_delivery" => array(
			'subject' => "Out For Delivery",
			'email_heading' => "Out For Delivery",
			'email_body' => '[tdws_tracking_table]'
		),
		"delivery_failure" => array(
			'subject' => "Delivery Failure",
			'email_heading' => "Delivery Failure",
			'email_body' => '[tdws_tracking_table]'
		),
		"delivered" => array(
			'subject' => "Delivered",
			'email_heading' => "Delivered",
			'email_body' => '[tdws_tracking_table]'
		),
		"exception" => array(
			'subject' => "Exception",
			'email_heading' => "Exception",
			'email_body' => '[tdws_tracking_table]'
		),
		"re_tracking" => array(
			'subject' => "Re-Tracking",
			'email_heading' => "Re-Tracking",
			'email_body' => '[tdws_tracking_table]'
		),
		"stop_tracking" => array(
			'subject' => "Stop Tracking",
			'email_heading' => "Stop Tracking",
			'email_body' => '[tdws_tracking_table]'
		)
	);	
	$mail_body_arr = apply_filters( 'custom_tdws_17track_mail_tracking_status_mail_info', $mail_body_arr );
	return $mail_body_arr;
}

function tdws_get_mail_body_by_stage_key( $option_key ){
	$default_mailObj = array(
		'subject' => '',
		'email_heading' => '',
		'email_body' => ''
	);
	$tdws_17track_mail_info = tdws_17track_mail_tracking_status_mail_info();
	if( isset($tdws_17track_mail_info[$option_key]) ){
		$default_mailObj = $tdws_17track_mail_info[$option_key];
	}
	if( $option_key ){
		$option_name = 'tdws_'.$option_key.'_mail_obj';
		$optionData = get_option( $option_name );			
		if( $optionData ){
			$default_mailObj = $optionData;			
		}
	}
	return $default_mailObj;
}


function tdws_17track_find_tracking_status_sub_stage( $sub_stage = '' ){
	$main_status_key = '';
	$status_arr = tdws_17track_tracking_status();
	if( is_array($status_arr) && count($status_arr) > 0 ){
		foreach ( $status_arr as $stage_key => $sub_stage_arr ) {
			if( is_array($sub_stage_arr) && in_array( $sub_stage, $sub_stage_arr ) ){
				$main_status_key = $stage_key;
				break;
			}
		}
	}
	return $main_status_key;
}

function tdws_get_colo_by_tag_name( $color_arr = array(), $tag_name = '' ){
	$color = isset($color_arr[$tag_name]) ? $color_arr[$tag_name] : '#000000';
	return $color;
}

function tdws_add_column_tracking_statusDB(){

	global $wpdb;		
	$table2_name = $wpdb->base_prefix.'tdws_order_tracking';
	$tdws_add_column_tracking_status = get_option( 'tdws_add_column_tracking_status' );
	if( empty($tdws_add_column_tracking_status) || $tdws_add_column_tracking_status == false || is_null( $tdws_add_column_tracking_status ) ){
		$column_name = 'tracking_status';
		$add_column_SQL = "ALTER TABLE $table2_name ADD $column_name INT(11) NOT NULL DEFAULT 0  AFTER status;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$addColumnFlag = maybe_add_column( $table2_name, $column_name, $add_column_SQL );
		if( $addColumnFlag ){
			update_option( 'tdws_add_column_tracking_status', $addColumnFlag );
		}	
	}

}

function tdws_send_tracking_mail_by_stage( $order_id,  $tdws_tracking_id, $stage_id, $tdws_extra_data = array() ){

	$tdws_status_mail_data = tdws_get_mail_body_by_stage_key( $stage_id );
	$tdws_product_ids = twds_tracking_get_item_meta( $tdws_tracking_id, 'product_ids' );		
	
	$order = wc_get_order( $order_id );

	$tdws_track_subject = $tdws_status_mail_data['subject'];
	$tdws_track_email_heading = $tdws_status_mail_data['email_heading'];		
	$tdws_track_email_body = $tdws_status_mail_data['email_body'];				
	$tdws_notes = isset($tdws_extra_data['tdws_notes']) ? $tdws_extra_data['tdws_notes'] : '';				

	$email  = $order->get_billing_email();
	$items = $order->get_items( 'line_item' );

	$tdws_track_subject = str_replace( '[order_id]', $order_id, $tdws_track_subject );
	$tdws_track_email_heading = str_replace( '[order_id]', $order_id, $tdws_track_email_heading );

	if( $tdws_track_email_body ){
		$tdws_track_email_body = str_replace( '[first_name]', $order->get_billing_first_name(), $tdws_track_email_body );
		$tdws_track_email_body = str_replace( '[last_name]', $order->get_billing_last_name(), $tdws_track_email_body );
		$tdws_track_email_body = str_replace( '[email]', $order->get_billing_email(), $tdws_track_email_body );
		$tdws_track_email_body = str_replace( '[order_id]', $order_id, $tdws_track_email_body );	
	}	

	$email_heading  = $tdws_track_email_heading;

	$tdws_tracking_items = $tdws_product_ids;		
	$content = $css = $html = '';
	$show_sku =  apply_filters( 'tdws_order_tracking_product_mail_show_sku', true );
	$show_image =  apply_filters( 'tdws_order_tracking_product_mail_show_img', true );
	$image_size =  apply_filters( 'tdws_order_tracking_product_mail_image_size', array( 64, 64 ) );
	
	ob_start();
	include plugin_dir_path(dirname( __FILE__ )).'admin/templates/emails/tdws-tracking-email-template.php';
	$html = ob_get_clean();
	ob_start();
	wc_get_template( 'emails/email-styles.php' );
	$css = ob_get_clean();
	$content = '<style type="text/css">ul li { list-style:none; } ' . $css . '</style>' . $html;				

	$mailFlag = tdws_custom_send_mail( $email, $tdws_track_subject, $content, array() );	
	$obj_meta_key = 'tdws_17track_'.$stage_id.'_mail_fail';
	$response_flag = false;
	if( $mailFlag ){
		$obj_meta_key = 'tdws_17track_'.$stage_id.'_mail_success';
		$response_flag = true;
	}	
	
	twds_tracking_update_item_meta( $tdws_tracking_id, $obj_meta_key, date( 'Y-m-d H:i:s' ), true );	
	return $response_flag;
}


function tdws_custom_send_mail( $to, $subject, $body, $attachments = array() ){	

	if ( ! class_exists( 'WC_Email' ) ) {
		return false;
	}

	$wc_email = new WC_Email();
	$mail_flag = $wc_email->send( $to, $subject, $body, "Content-Type: text/html\r\n", $attachments );

	return $mail_flag;
}

function tdws_format_provide_date( $date, $time, $timezone ){
	$tdws_date_format = (get_option('date_format')) ? get_option('date_format') : 'd/m/Y';
	$tdws_time_format = (get_option('time_format')) ? get_option('time_format') : 'd/m/Y';
	$defualt_timezone = wp_timezone()->getName();
	$dateTimeString = $date . ' ' . $time;
	if( empty($timezone) ){
		$timezone = '+00:00';
	}
	if( empty($time) ){
		$time = '00:00';
	}
	// Create DateTime object
	$dateTime = new DateTime($dateTimeString, new DateTimeZone($timezone));
	// Optionally, set a different timezone for display
	$dateTime->setTimezone(new DateTimeZone( $defualt_timezone ));
	// Format for display
	return $dateTime->format($tdws_date_format.' '.$tdws_time_format);
}