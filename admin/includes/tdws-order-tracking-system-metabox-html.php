<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://tdwebservices.com
 * @since      1.0.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/admin/includes
 */

$tdws_item_class = isset($tk_value['hidden_meta']) ? 'tdws-hide-item' : "tdws-show-item";
$tdws_disable_class = isset($tk_value['hidden_meta']) ? 'disabled' : "";
$tdws_disable_attr = isset($tk_value['hidden_meta']) ? 'disabled="true"' : "";

$tdws_input_name = isset($tk_value['hidden_meta']) ? 'hide_tdws_meta' : "tdws_meta";
$tdws_tracking_no = isset($tk_value['tracking_no']) ? $tk_value['tracking_no'] : '';
$tdws_carrier_name = isset($tk_value['carrier_name']) ? $tk_value['carrier_name'] : '';
$tdws_pickup_date = isset($tk_value['pickup_date']) ? $tk_value['pickup_date'] : '';
if( !empty( trim($tdws_pickup_date) ) && $tdws_pickup_date != "0000-00-00 00:00:00" ){
	$tdws_pickup_date = date( 'Y-m-d', strtotime(trim($tdws_pickup_date)) );
}
$tdws_carrier_link = isset($tk_value['carrier_link']) ? $tk_value['carrier_link'] : '';
$tdws_item_id = isset($tk_value['id']) ? $tk_value['id'] : 0;
$tdws_status = isset($tk_value['status']) ? $tk_value['status'] : '';
$tdws_product_ids = '';
$tdws_product_arr = array();
if( $tdws_item_id ){
	$all_tdws_meta = twds_tracking_all_item_meta( $tdws_item_id );
	$tdws_product_ids = isset($all_tdws_meta['product_ids']) ? $all_tdws_meta['product_ids'] : '';
	if( $tdws_product_ids ){
		$tdws_product_arr = explode( ',' , $tdws_product_ids );
	}
}
$tdws_active_product_labels = '';
?>
<div class="<?php echo $tdws_item_class; ?> tdws-order-tracking-item">
	<div class="tdws-field-box-inner">
		<div class="tdws-field-box">
			<div class="tdws-field-col">
				<label><?php _e( "Product", 'tdws-order-tracking-system' ); ?></label>
				<select class="tdws-product-select tdws-input-control" multiple="multiple" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][product_ids][]" old-value="<?php echo esc_attr( $tdws_product_ids ); ?>" data-placeholder="<?php esc_attr_e( 'Select Products', 'woocommerce' ); ?>">
					<option value=""><?php _e( 'Select Products', 'tdws-order-tracking-system' ); ?></option>
					<?php 
					if( isset($default_tdws_fields['product_items']) && !empty($default_tdws_fields['product_items']) ){
						foreach ( $default_tdws_fields['product_items'] as $key => $item_value ) {
							if( is_object( $item_value ) ){
								$selected_string = '';
								if( $tdws_product_arr && in_array( $item_value->get_id(), $tdws_product_arr ) ){
									$selected_string = 'selected="selected"';
									if( $tdws_active_product_labels ){
										$tdws_active_product_labels .= ', ';
									}
									$tdws_active_product_labels .= $item_value->get_name();
								}
								?>
								<option value="<?php echo esc_attr( $item_value->get_id() ); ?>" <?php echo esc_attr( $selected_string ); ?>><?php echo esc_attr( $item_value->get_name() ); ?></option>
								<?php	
							}										
						}
					}
					?>
				</select>
			</div>
			<div class="tdws-field-col">
				<label><?php _e( "Tracking No", 'tdws-order-tracking-system' ); ?></label>
				<input type="text" class="tdws-input-control <?php echo esc_attr( $tdws_disable_class ); ?>" <?php echo esc_attr( $tdws_disable_attr ); ?> name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][tracking_no]"  old-value="<?php echo esc_attr( $tdws_tracking_no ); ?>" placeholder="<?php _e( 'Enter Tracking Number', 'tdws-order-tracking-system' ); ?>" value="<?php echo esc_attr( $tdws_tracking_no ); ?>" />
			</div>
			<div class="tdws-field-col">
				<label><?php _e( "Carrier Name", 'tdws-order-tracking-system' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][carrier_name]" old-value="<?php echo esc_attr( $tdws_carrier_name ); ?>" placeholder="<?php _e( 'Enter Carrier Name', 'tdws-order-tracking-system' ); ?>" class="tdws-input-control <?php echo esc_attr( $tdws_disable_class ); ?>" <?php echo esc_attr( $tdws_disable_attr ); ?> value="<?php echo esc_attr( $tdws_carrier_name ); ?>" />
			</div>
			<div class="tdws-field-col">
				<label><?php _e( "PickUp Date", 'tdws-order-tracking-system' ); ?></label>
				<input type="text" class="tdws-pickup-date tdws-input-control <?php echo esc_attr( $tdws_disable_class ); ?>" <?php echo esc_attr( $tdws_disable_attr ); ?> name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][pickup_date]" old-value="<?php echo esc_attr( $tdws_pickup_date ); ?>" placeholder="<?php _e( 'Enter PickUp Date', 'tdws-order-tracking-system' ); ?>" value="<?php echo esc_attr( $tdws_pickup_date ); ?>" />
			</div>
			<div class="tdws-field-col">
				<label><?php _e( "Carrier Website Url", 'tdws-order-tracking-system' ); ?></label>
				<input type="text" class="tdws-input-control <?php echo esc_attr( $tdws_disable_class ); ?>" <?php echo esc_attr( $tdws_disable_attr ); ?> name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][carrier_link]" old-value="<?php echo esc_attr( $tdws_carrier_link ); ?>"  placeholder="<?php _e( 'Enter Carrier Website Url', 'tdws-order-tracking-system' ); ?>" value="<?php echo esc_attr( $tdws_carrier_link ); ?>" />
			</div>
			<div class="tdws-field-col">
				<label><?php _e( "Status", 'tdws-order-tracking-system' ); ?></label>						
				<select class="tdws-tracking-status tdws-input-control <?php echo esc_attr( $tdws_disable_class ); ?>" <?php echo esc_attr( $tdws_disable_attr ); ?> name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][status]"  old-value="<?php echo esc_attr( $tdws_status ); ?>"  data-placeholder="<?php esc_attr_e( 'Select Status', 'woocommerce' ); ?>">
					<option value=""><?php _e( 'Select Status', 'tdws-order-tracking-system' ); ?></option>
					<?php 
					if( isset($default_tdws_fields['tdws_tracking_status']) && !empty( $default_tdws_fields['tdws_tracking_status'] ) ){
						foreach ( $default_tdws_fields['tdws_tracking_status'] as $status_name ) {
							?>
							<option value="<?php echo esc_attr( $status_name ); ?>" <?php selected( $status_name, $tdws_status ); ?>><?php echo esc_attr( $status_name ); ?></option>
							<?php										
						}
					}
					?>
				</select>
			</div>
			<?php 
			do_action( 'tdws_add_extra_tracking_field', $order );
			?>					
			<div class="tdws-field-col tdws-field-remove">
				<input type="hidden" class="tdws-item-id" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][tdws_item_id]" value="<?php echo esc_attr( $tdws_item_id ); ?>">
				<input type="hidden" class="tdws-send-mail" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][tdws_send_mail]" value="0">
				<button type="button" class="tdws-remove-item-btn"><?php _e( 'Remove', 'tdws-order-tracking-system' ); ?></button>
			</div>
		</div>
	</div>
	<div class="tdws-item-label">
		<ul class="tdws-item-label-ul">
			<li><strong><?php _e( 'Products', 'tdws-order-tracking-system' ); ?> : </strong><span><?php echo wp_kses_post( $tdws_active_product_labels ); ?></span></li>
			<li><strong><?php _e( 'Tracking No', 'tdws-order-tracking-system' ); ?> : </strong><span><?php echo wp_kses_post( $tdws_tracking_no ); ?></span></li>
			<li><strong><?php _e( 'Status', 'tdws-order-tracking-system' ); ?> : </strong><span><?php echo wp_kses_post( $tdws_status ); ?></span></li>
			<li><span class="dashicons dashicons-arrow-down-alt2"></span></li>
		</ul>
	</div>
</div>