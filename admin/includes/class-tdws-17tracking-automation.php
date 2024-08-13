<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tdwebservices.com
 * @since      1.1.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/admin/includes
 */

class Tdws_Order_Tracking_Automation {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $one7trackAPI;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->one7trackAPI = new Tdws_Order_Tracking_System_17TrackAPI( $this->plugin_name, $this->version );

		// Batch wise data push
		add_action( 'wp_ajax_nopriv_tdws_push_17track_api_cron', array( $this, 'tdws_push_17track_api_cron' ) );
		add_action( 'wp_ajax_tdws_push_17track_api_cron', array( $this, 'tdws_push_17track_api_cron' ) );

		// Batch wise data callback
		add_action( 'wp_ajax_nopriv_tdws_push_17track_api_single_cron', array( $this, 'tdws_push_17track_api_single_cron' ) );
		add_action( 'wp_ajax_tdws_push_17track_api_single_cron', array( $this, 'tdws_push_17track_api_single_cron' ) );

	}


	/**
	 * This function define to batch wise data store
	 *
	 * @since    1.1.0
	 */	
	public function tdws_push_17track_api_cron(){		
		tdws_set_timezone();	
		global $wpdb;	
		$table_name = $wpdb->base_prefix.'tdws_order_tracking_status';
		$table2_name = $wpdb->base_prefix.'tdws_order_tracking';
		tdws_add_column_tracking_statusDB();
		$meta_result = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $table2_name WHERE ( tracking_no != '' ) AND tracking_status = 0 order by id asc" ), ARRAY_A );
		$tdws_tracking_cron_ids = array();
		if( $meta_result ){
			foreach ( $meta_result as $key => $m_value ) {
				$tdws_tracking_cron_ids[] = $m_value['id'];
			}
		}
		update_option( 'tdws_tracking_cron_ids', $tdws_tracking_cron_ids );
		update_option( 'tdws_tracking_cron_batch_date', date('Y-m-d H:i:s') );
		update_option( 'tdws_tracking_cron_page', 1 );
		wp_die( 'Done' );

	}

	/**
	 * This function define to batch wise data read and callback to api
	 *
	 * @since    1.1.0
	 */	
	public function tdws_push_17track_api_single_cron(){		

		tdws_set_timezone();	
		$tdws_tracking_cron_ids = get_option( 'tdws_tracking_cron_ids' );
		if( empty($tdws_tracking_cron_ids) || is_null($tdws_tracking_cron_ids) ){
			$tdws_tracking_cron_ids = array();
		}

		$tdws_tracking_cron_page = get_option( 'tdws_tracking_cron_page' );
		
		if( empty($tdws_tracking_cron_page) || is_null( $tdws_tracking_cron_page ) ){
			$tdws_tracking_cron_page = 1;
		}

		$tdws_17track_opt = get_option( 'tdws_17track_opt' );
		$mail_tracking_status = isset($tdws_17track_opt['mail_tracking_status']) ? $tdws_17track_opt['mail_tracking_status'] : array();

		$page = $tdws_tracking_cron_page;
		$total = is_array($tdws_tracking_cron_ids) ? count( $tdws_tracking_cron_ids ) : 0; 
		$limit = 3;  
		$totalPages = ceil( $total / $limit );
		$page = max( $page, 1 ); 
		$page = min( $page, $totalPages );
		$offset = ( $page - 1) * $limit;
		if( $offset < 0 ) {
			$offset = 0;
		}
		$tdws_tracking_cron_ids = array_slice( $tdws_tracking_cron_ids, $offset, $limit );


		if( is_array($tdws_tracking_cron_ids) && count( $tdws_tracking_cron_ids ) > 0 &&  $tdws_tracking_cron_page <= $totalPages ){

			global $wpdb;	
			$table_name = $wpdb->base_prefix.'tdws_order_tracking_status';
			$table2_name = $wpdb->base_prefix.'tdws_order_tracking';

			tdws_add_column_tracking_statusDB();

			$tdws_id_indexer = implode(',', array_fill(0, count($tdws_tracking_cron_ids), '%d'));
			$meta_result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table2_name WHERE id IN ( $tdws_id_indexer )", ...$tdws_tracking_cron_ids ), ARRAY_A );

			$trackingIDs = array();		
			if( is_array( $meta_result ) && !empty( $meta_result ) ){
				foreach ( $meta_result as $key => $m_value ) {				
					if( $m_value['tracking_no'] ){
						$m_value = array_map( 'trim', $m_value );


						$tdws_tracking_id = $m_value['id'];
						$tdws_pickup_date = $m_value['pickup_date'];
						$tdws_current_date = date( 'Y-m-d H:i:s' );

						$trackData = $this->one7trackAPI->getPureTrackInfo( $m_value['tracking_no'] );
						$last_tracking_status = isset($trackData['track_info']['latest_status']['status']) ? $trackData['track_info']['latest_status']['status'] : '';
						$last_tracking_sub_status = isset($trackData['track_info']['latest_status']['sub_status']) ? $trackData['track_info']['latest_status']['sub_status'] : '';
						$trackEventList = $this->one7trackAPI->getAllTrackEvents( $trackData, 1 );	
						$tdws_pickup_update_date = '';
						
						if( $trackEventList ){
							$singleStatusData = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE tracking_id = %d AND status != 1", $m_value['id']), ARRAY_A );
							if( is_array($singleStatusData) && count($singleStatusData) > 0 ){
								$singleStatusData = array_map( 'trim', $singleStatusData );
								$status_auto_id = isset($singleStatusData['id']) ? $singleStatusData['id'] : 0;						
								if( $status_auto_id ){
									$mail_tracking_stages = $update_stage_data = array();				
									foreach ( $trackEventList as $state_key => $status_time ) {
										if( isset($status_time[0]) && !empty($status_time[0]) ){
											if( ( isset($singleStatusData[$state_key]) && $singleStatusData[$state_key] == '' ) || ( isset($singleStatusData[$state_key]) && is_null($singleStatusData[$state_key]) ) || ( isset($singleStatusData[$state_key]) && empty($singleStatusData[$state_key]) )  ){
												if( is_array($mail_tracking_status) && in_array( $state_key, $mail_tracking_status ) ){
													$mail_tracking_stages[] = $state_key;					
												}
												$update_stage_data[$state_key] = date( 'Y-m-d H:i:s', strtotime( $status_time[0] ) );								
											}
											if( $state_key == 'pickup' ){
												if( $tdws_pickup_date == '' || is_null( $tdws_pickup_date ) ){
													$tdws_pickup_update_date = date( 'Y-m-d', strtotime( $status_time[0] ) );																								
												}
											}
										}
									}	
									$update_stage_data['update_date'] = $tdws_current_date;									
									$update_flag = $wpdb->update( $table_name, $update_stage_data, [ 'id' => $status_auto_id ] );	

									if( $update_flag && is_array($mail_tracking_stages) && count($mail_tracking_stages) > 0 ){
										foreach ( $mail_tracking_stages as $m_key => $st_value ) {											
											tdws_send_tracking_mail_by_stage( $m_value['order_id'], $tdws_tracking_id, $st_value );
										}
									}								
								}	
								$trackingIDs[] = $status_auto_id;

							}						
						}
						if( $tdws_pickup_update_date ){
							$wpdb->query( $wpdb->prepare( "UPDATE $table2_name SET pickup_date = %s, update_date = %s, status = %s, sub_status = %s WHERE id = %d", $tdws_pickup_update_date, $tdws_current_date, $last_tracking_status, $last_tracking_sub_status, $tdws_tracking_id ) );
						}else{
							$wpdb->query( $wpdb->prepare( "UPDATE $table2_name SET  update_date = %s, status = %s, sub_status = %s WHERE id = %d", $tdws_current_date, $last_tracking_status, $last_tracking_sub_status, $tdws_tracking_id ) );
						}
					}
				}		
			}	

			if( is_array($trackingIDs) && count($trackingIDs) > 0 ){
				$placeholders = implode(',', array_fill(0, count($trackingIDs), '%d'));
				$deliveryTrackIDs = $wpdb->get_results( $wpdb->prepare( "SELECT id, order_id, tracking_id, delivered, exception FROM $table_name WHERE id IN ( $placeholders ) AND ( (  delivered != '' AND delivered IS NOT NULL  ) OR (  exception != '' AND exception IS NOT NULL  ) ) ", ...$trackingIDs ), ARRAY_A );

				if( is_array($deliveryTrackIDs) && count($deliveryTrackIDs) > 0 ){
					foreach ( $deliveryTrackIDs as $key => $deliveryTrackItem ) {
						if( $deliveryTrackItem['order_id'] && $deliveryTrackItem['tracking_id'] ){
							$wpdb->query( $wpdb->prepare( "UPDATE $table2_name SET tracking_status = 1, update_date = %s WHERE id = %d AND order_id = %d", $tdws_current_date, $deliveryTrackItem['tracking_id'], $deliveryTrackItem['order_id'] ));
						}
						if( $deliveryTrackItem['id'] ){
							$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET status = 1, update_date = %s WHERE id = %d", $tdws_current_date, $deliveryTrackItem['id'] ) );
						}
					}
				}
			}		

			$tdws_tracking_cron_page++;			
		}else{
			$tdws_tracking_cron_page = 1;
		}

		update_option( 'tdws_tracking_cron_page', $tdws_tracking_cron_page );
		update_option( 'tdws_tracking_cron_batch_read_date', date('Y-m-d H:i:s') );
		wp_die('done now next page =>'.$tdws_tracking_cron_page);
	}

}