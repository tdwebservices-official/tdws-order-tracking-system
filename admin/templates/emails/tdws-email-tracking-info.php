<?php
/**
 * TDWS Single Tracking Information Data
 */

$tracking_item_list = twds_tracking_data_by_item_id( $item_id, 1 );		

if( $tracking_item_list ){
	$tdws_index = 0;
	foreach ( $tracking_item_list as $tracking_item_info ) {
		$tdws_tracking_no = isset($tracking_item_info['tracking_no']) ? trim( $tracking_item_info['tracking_no'] ) : '';
		$tdws_carrier_name = isset($tracking_item_info['carrier_name']) ? trim( $tracking_item_info['carrier_name'] ) : '';
		$tdws_pickup_date = isset($tracking_item_info['pickup_date']) ? trim( $tracking_item_info['pickup_date'] ) : '';
		$tdws_carrier_link = isset($tracking_item_info['carrier_link']) ? trim( $tracking_item_info['carrier_link'] ) : '';
		$tdws_tracking_status = isset($tracking_item_info['status']) ? trim( $tracking_item_info['status'] ) : '';
		$tdws_date_format = (get_option('date_format')) ? get_option('date_format') : 'd/m/Y';
		if( $tdws_tracking_no || $tdws_carrier_name || $tdws_tracking_status ){

			if( $tdws_index > 0 ){
				echo "<hr style='border:1px solid #e5e5e5;' />";
			}	
			?>
			<ul style="display: block;list-style: none;padding: 0;margin: 10px 0 0;">
				<?php 
				if( $tdws_tracking_no ){
					?>
					<li style="margin-left: 0;list-style: none;"><strong><?php _e( "Tracking No", 'tdws-order-tracking-system' ); ?></strong> : <span style="word-break: break-word;"><?php echo esc_html( $tdws_tracking_no ); ?></span></li>
					<?php
				}
				if( $tdws_carrier_name ){
					if( $tdws_carrier_link ){
						?>
						<li style="margin-left: 0;list-style: none;"><strong><?php _e( "Carrier Name", 'tdws-order-tracking-system' ); ?></strong> : <span><a href="<?php echo esc_url( $tdws_carrier_link ); ?>" target="_blank"><?php echo esc_html( $tdws_carrier_name ); ?></a></span></li>
						<?php	
					}else{
						?>
						<li style="margin-left: 0;list-style: none;"><strong><?php _e( "Carrier Name", 'tdws-order-tracking-system' ); ?></strong> : <span><?php echo esc_html( $tdws_carrier_name ); ?></span></li>
						<?php	
					}
				}
				if( !empty( trim($tdws_pickup_date) ) && $tdws_pickup_date != "0000-00-00 00:00:00" ){
					?>
					<li style="margin-left: 0;list-style: none;"><strong><?php _e( "Pickup Date", 'tdws-order-tracking-system' ); ?></strong> : <span><?php echo esc_html( date( $tdws_date_format, strtotime( $tdws_pickup_date ) ) ); ?></span></li>
					<?php
				}
				if( $tdws_tracking_status ){
					?>
					<li style="margin-left: 0;list-style: none;"><strong><?php _e( "Status", 'tdws-order-tracking-system' ); ?></strong> : <span><?php echo esc_html( $tdws_tracking_status ); ?></span></li>
					<?php
				}
				?>		
			</ul>
			<?php

			$tdws_index++;

		}
	}
}