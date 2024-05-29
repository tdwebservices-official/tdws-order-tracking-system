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

			$data = [ 'meta_value' => $meta_value ];

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
	$meta_sql = "SELECT t2.tracking_no, t2.carrier_name, t2.pickup_date, t2.carrier_link, t2.status, t1.meta_id, t1.order_tracking_id, t1.meta_key, t1.meta_value FROM $table_name_2 as t1
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

function tdws_get_colo_by_tag_name( $color_arr = array(), $tag_name ){
	$color = isset($color_arr[$tag_name]) ? $color_arr[$tag_name] : '#000000';
	return $color;
}

function tdws_custom_send_mail( $to, $subject, $body, $attachments = array() ){	

	$wc_email = new WC_Email();
	$mail_flag = $wc_email->send( $to, $subject, $body, "Content-Type: text/html\r\n", $attachments );

	return $mail_flag;
}