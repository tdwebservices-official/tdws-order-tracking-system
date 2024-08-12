<?php
/**
 * TDWS Tracking Provide Statuses List
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

defined( 'ABSPATH' ) || exit;

if( $tracking_provider ){
	$tdws_delivered_days = $tdws_delivered = 0;
	$tdws_del_track_date = '';

	if( isset($track_latest_event['stage']) && $track_latest_event['stage'] == 'Delivered' ){
		$tdws_delivered = 1;
		$t_del_date = isset($track_latest_event['time_raw']['date']) ? $track_latest_event['time_raw']['date'] : '';
		$t_del_time = isset($track_latest_event['time_raw']['time']) ? $track_latest_event['time_raw']['time'] : '';
		$t_del_timezone = isset($track_latest_event['time_raw']['timezone']) ? $track_latest_event['time_raw']['timezone'] : '';
		
		if( $t_del_date ){
			$tdws_del_track_date = tdws_format_provide_date( $t_del_date, $t_del_time, $t_del_timezone );	
		}
	
		$tdws_date1 = date_create($order_date);
		$tdws_date2 = date_create($t_del_date);
		$tdws_diff = date_diff( $tdws_date1, $tdws_date2 );
		$tdws_delivered_days = $tdws_diff->format("%a Days");		
	}
	?>
	<div class="tdws-provider-list">
		<div class="tdws-main-header">
		
		<div class="tracklist-header">
			<div class="tdws-provider-box">
				<span class="tracklist-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M112 0C85.5 0 64 21.5 64 48l0 48L16 96c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 208 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 160l-16 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l16 0 176 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 224l-48 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 144 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 288l0 128c0 53 43 96 96 96s96-43 96-96l128 0c0 53 43 96 96 96s96-43 96-96l32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-64 0-32 0-18.7c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7L416 96l0-48c0-26.5-21.5-48-48-48L112 0zM544 237.3l0 18.7-128 0 0-96 50.7 0L544 237.3zM160 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm272 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0z"/></svg></span>
				<div data-name="">
					<p>
						<span class="text-uppercase" title="<?php echo esc_attr( $tracking_no ); ?>"><?php echo esc_html( $tracking_no ); ?></span>
					</p>
					<?php 
					if ( $tdws_delivered_days > 0 ){
						?>
						<p>
							<span title="<?php echo esc_html( sprintf( 'Delivered (%s)', $tdws_delivered_days ), 'tdws-order-tracking-system' ); ?>"><?php echo esc_html( sprintf( 'Delivered (%s)', $tdws_delivered_days ), 'tdws-order-tracking-system' ); ?></span>
						</p>
						<?php	
					}
					?> 
					
				</div>
			</div>
		</div>
		<?php
		if( $tdws_delivered == 1 ){
			?>
			<div class="status-progress">
				<div class="status-progress-title"><?php _e( 'Delivered - Time of delivery: ', 'tdws-order-tracking-system'); ?> <?php echo esc_html( $tdws_del_track_date ); ?></div>
			</div>
			<?php
		}
		if( isset($tracking_provider['name']) && !empty($tracking_provider['name']) ){
			?>
			<div class="tdws-provider-info">
				<h3><?php echo isset($tracking_provider['name']) ? $tracking_provider['name'] : ''; ?></h3>
				<h4>- <?php echo (isset($tracking_provider['country']) && $tracking_provider['country']) ? WC()->countries->countries[ $tracking_provider['country'] ] : ''; ?></h4>
			</div>
			<?php
		}
		?>
	</div>

		<?php
		if( is_array($tracking_events) && count($tracking_events) > 0 ){
			?>
			<div class="tdws-provider-events">
				<ul>
					<?php 
					foreach ( $tracking_events as $t_key => $tracking_event_info ) {

						$t_date = isset($tracking_event_info['time_raw']['date']) ? $tracking_event_info['time_raw']['date'] : '';
						$t_time = isset($tracking_event_info['time_raw']['time']) ? $tracking_event_info['time_raw']['time'] : '';
						$t_timezone = isset($tracking_event_info['time_raw']['timezone']) ? $tracking_event_info['time_raw']['timezone'] : '';
						$tdws_description = isset($tracking_event_info['description']) ? $tracking_event_info['description'] : '';
						$tdws_location = isset($tracking_event_info['location']) ? $tracking_event_info['location'] : '';
						$tdws_track_date = '';
						if( $t_date ){
							$tdws_track_date = tdws_format_provide_date( $t_date, $t_time, $t_timezone );	
						}					
						?>
						<li>
							<div class="tdws-box-icon">
								<div class="tdws-border">
									<span class="tdws-active-icon"></span>
								</div>
							</div>
							<div class="tdws-box-info">
								<?php 
								if( $tdws_track_date ){
									?>
									<div class="tdws-provider-desc">
										<?php echo esc_html( $tdws_track_date ); ?>
									</div>
									<?php
								}
								?>
								<div class="tdws-provider-location">
									<?php echo esc_html( $tdws_location.' '.$tdws_description ); ?>
								</div>
							</div>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
			<?php
		}
		?>
		
	</div>
	<?php
}