<?php
/**
 * Order Progress Bar
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

defined( 'ABSPATH' ) || exit;

if( $tracking_item_list ){
	?>
	<div class="tdws-tracking-wrapper">
		<?php
		global $wpdb;
		$status_table_name = $wpdb->base_prefix.'tdws_order_tracking_status';
		foreach ( $tracking_item_list as $tracking_item_info ) {

			$tdws_tracking_no = isset($tracking_item_info['tracking_no']) ? trim( $tracking_item_info['tracking_no'] ) : '';
			$tdws_carrier_name = isset($tracking_item_info['carrier_name']) ? trim( $tracking_item_info['carrier_name'] ) : '';
			$tdws_pickup_date = isset($tracking_item_info['pickup_date']) ? trim( $tracking_item_info['pickup_date'] ) : '';
			$tdws_carrier_link = isset($tracking_item_info['carrier_link']) ? trim( $tracking_item_info['carrier_link'] ) : '';
			$tdws_tracking_status = isset($tracking_item_info['status']) ? trim( $tracking_item_info['status'] ) : '';
			$order_tracking_id = isset($tracking_item_info['order_tracking_id']) ? trim( $tracking_item_info['order_tracking_id'] ) : 0;
			$order_id = $order->get_id();
			$tdws_date_format = (get_option('date_format')) ? get_option('date_format') : 'd/m/Y';
			$tdws_time_format = (get_option('time_format')) ? get_option('time_format') : 'h:i:s';
			
			if( $tdws_tracking_no || $tdws_carrier_name || $tdws_tracking_status ){	

				$trackingStatusData = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $status_table_name WHERE tracking_id = %d and order_id = %d", $order_tracking_id, $order_id), ARRAY_A );				

				$info_receivedDate = isset($trackingStatusData['info_received']) ? $trackingStatusData['info_received'] : '';
				$in_transitDate = isset($trackingStatusData['in_transit']) ? $trackingStatusData['in_transit'] : '';
				$out_for_deliveryDate = isset($trackingStatusData['out_for_delivery']) ? $trackingStatusData['out_for_delivery'] : '';
				$deliveredDate = isset($trackingStatusData['delivered']) ? $trackingStatusData['delivered'] : '';
				$exceptionDate = isset($trackingStatusData['exception']) ? $trackingStatusData['exception'] : '';
				if( $in_transitDate && $info_receivedDate == '' ){
					$info_receivedDate = $in_transitDate;
				}
				if( $out_for_deliveryDate && ( $info_receivedDate == '' && $in_transitDate == '' ) ){
					$info_receivedDate = $out_for_deliveryDate;
					$in_transitDate = $out_for_deliveryDate;
				}
				if( $out_for_deliveryDate && $info_receivedDate == '' ){
					$info_receivedDate = $out_for_deliveryDate;
				}
				if( $out_for_deliveryDate && $in_transitDate == '' ){
					$in_transitDate = $out_for_deliveryDate;
				}

				if( $deliveredDate && ( $info_receivedDate == '' && $in_transitDate == '' && $out_for_deliveryDate == '' ) ){
					$info_receivedDate = $deliveredDate;
					$in_transitDate = $deliveredDate;
					$out_for_deliveryDate = $deliveredDate;
				}
				if( $deliveredDate && $info_receivedDate == '' ){
					$info_receivedDate = $deliveredDate;
				}
				if( $deliveredDate && $in_transitDate == '' ){
					$in_transitDate = $deliveredDate;
				}
				if( $deliveredDate && $out_for_deliveryDate == '' ){
					$out_for_deliveryDate = $deliveredDate;
				}

				$defaultTrackingProgress = array(
					'info_received' => array( 'label' => __( 'Ordered', 'tdws-order-tracking-system' ),'date' => $trackingStatusData['create_date'] ),
					'in_transit' => array( 'label' => __( 'Shipped', 'tdws-order-tracking-system' ),'date' => $in_transitDate ),
					'out_for_delivery' => array( 'label' => __( 'Out For Delivery', 'tdws-order-tracking-system' ),'date' => $out_for_deliveryDate ),
					'delivered' => array( 'label' => __( 'Delivered', 'tdws-order-tracking-system' ),'date' => $deliveredDate ),
				);


				if( !is_null($exceptionDate) && $exceptionDate != '' ){
					if( $out_for_deliveryDate == '' ){
						unset($defaultTrackingProgress['out_for_delivery']);						
					}
					if(  $deliveredDate == '' ){
						unset($defaultTrackingProgress['delivered']);
					}
					$defaultTrackingProgress['exception'] = array( 'label' => __( 'Exception', 'tdws-order-tracking-system' ), 'date' => $exceptionDate );
				}

				?>
				<div class="tdws-tracking-card-wrapper">
					<div class="tdws-tracking-card">
						<div class="tdws-tracking-row tdws-tracking-title-row">

							<div class="tdws-tracking-flex tdws-tracking-flex-column tdws-tracking-title-info">								
								<p>
									<?php 
									if( empty($tdws_carrier_name) ){
										$tdws_carrier_name = '-';
									}
									if( $tdws_carrier_link ){
										?>
										<span><a href="<?php echo esc_url( $tdws_carrier_link ); ?>" target="_blank"><?php echo esc_html( $tdws_carrier_name ); ?></a></span>
										<?php	
									}else{
										?>
										<span><?php echo esc_html( $tdws_carrier_name ); ?></span>
										<?php	
									}
									?> 
									<span><?php	echo esc_html( $tdws_tracking_no ); ?></span>
								</p>
							</div>
							<div class="tdws-tracking-updates">
								<a href="javascript:;"  class="tdws-tracking-updates-btn tdws-loader-button" data-order-date="<?php echo $trackingStatusData['create_date']; ?>" data-tracking-no="<?php echo esc_attr( $tdws_tracking_no ); ?>"><span class="tdws-button-label"><?php _e( 'Check Updates', 'tdws-order-tracking-system' ); ?></span><span class="tdws-loader"><img src="<?php echo esc_url( plugin_dir_url( dirname( __DIR__ ), 1 ).'admin/images/loader.gif' ); ?>"/></span></a>							
							</div>
						</div>
						<!-- Add class 'active' to progress -->
						<div class="tdws-tracking-row tdws-tracking-flex">
							<ul class="tdws-progressbar">								
								<?php 
								if( $defaultTrackingProgress ){
									foreach ( $defaultTrackingProgress as $key => $progressValue ) {
										?>
										<li class="tdws-step <?php echo ( $progressValue['date'] !='' && !is_null($progressValue['date']) ) ? "active" : ""; ?>">
											<div class="tdws-progress-box">
												<span class="tdws-progress-label"><?php echo esc_html( $progressValue['label'] ); ?></span>
												<?php 
												$tdws_progress_date = '';
												if( $progressValue['date'] !='' &&  !is_null($progressValue['date']) ){
													$tdws_progress_date = esc_html( date( $tdws_date_format.' '.$tdws_time_format, strtotime( $progressValue['date'] ) ) );								
												}
												?>
												<span class="tdws-progress-date"><?php echo esc_html( $tdws_progress_date ); ?></span>
											</div>
										</li>
										<?php
									}
								}
								?>
							</ul>
						</div>

					</div>
				</div>			
				<?php
			}
		}
		?>			
	</div>
	<?php	
}
