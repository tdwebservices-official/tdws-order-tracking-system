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
$tdws_item_id = isset($tk_value['id']) ? $tk_value['id'] : 0;
$tdws_status = isset($tk_value['status']) ? $tk_value['status'] : '';
$tdws_carrier_code = $tdws_product_ids = '';
$tdws_product_arr = array();
if( $tdws_item_id ){
	$all_tdws_meta = twds_tracking_all_item_meta( $tdws_item_id );
	$tdws_product_ids = isset($all_tdws_meta['product_ids']) ? $all_tdws_meta['product_ids'] : '';
	$tdws_carrier_code = isset($all_tdws_meta['carrier_code']) ? $all_tdws_meta['carrier_code'] : '';

	if( $tdws_product_ids ){
		$tdws_product_arr = explode( ',' , $tdws_product_ids );
	}
}
$tdws_active_product_labels = '';
?>
<div class="<?php echo $tdws_item_class; ?> tdws-order-tracking-item">
	<div class="tdws-field-box-inner">
		<div class="tdws-field-box">
			<div class="tdws-field-col tdws-full-width">
				<label><?php _e( "Product", 'tdws-order-tracking-system' ); ?></label>
				<select class="tdws-product-select tdws-input-control" multiple="multiple" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][product_ids][]" old-value="<?php echo esc_attr( $tdws_product_ids ); ?>" data-placeholder="<?php esc_attr_e( 'Select Products', 'tdws-order-tracking-system' ); ?>">
					<?php 
					if( isset($default_tdws_fields['product_items']) && !empty($default_tdws_fields['product_items']) ){
						foreach ( $default_tdws_fields['product_items'] as $key => $item_value ) {
							if( is_object( $item_value ) ){
								
								$full_p_name = '';
								$item_product_id = $item_value->get_product_id();
								if( $item_value->get_variation_id() ){
									$item_product_id = $item_value->get_variation_id();
								}
								$p_sku = get_post_meta( $item_product_id, '_sku', true );
								if( empty($p_sku) ){
									$p_sku = get_post_meta( $item_value->get_product_id(), '_sku', true );
								}
								if( $p_sku ){
									$full_p_name .= "[ ".$p_sku." ] ";
								}
								$full_p_name .= $item_value->get_name();

								$selected_string = '';
								if( $tdws_product_arr && in_array( $item_value->get_id(), $tdws_product_arr ) ){
									$selected_string = 'selected="selected"';
									if( $tdws_active_product_labels ){
										$tdws_active_product_labels .= ', ';
									}
									$tdws_active_product_labels .= $full_p_name;
								}
								?>
								<option value="<?php echo esc_attr( $item_value->get_id() ); ?>" <?php echo esc_attr( $selected_string ); ?>><?php echo esc_attr( $full_p_name ); ?></option>
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
				<select class="tdws-carries-name tdws-comman-select2 tdws-input-control <?php echo esc_attr( $tdws_disable_class ); ?>" <?php echo esc_attr( $tdws_disable_attr ); ?> name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][carrier_name]"  old-value="<?php echo esc_attr( $tdws_status ); ?>"  data-placeholder="<?php esc_attr_e( 'Select Carrier Name', 'woocommerce' ); ?>">
					<option value=""><?php _e( 'Select Status', 'tdws-order-tracking-system' ); ?></option>
					<?php 
					if( isset($default_tdws_fields['tdws_carrier_list']) && !empty( $default_tdws_fields['tdws_carrier_list'] ) ){
						foreach ( $default_tdws_fields['tdws_carrier_list'] as $carrier_item ) {
							?>
							<option value="<?php echo esc_attr( $carrier_item['name'] ); ?>" data-code="<?php echo esc_attr( $carrier_item['code'] ); ?>" <?php selected( $carrier_item['name'], $tdws_carrier_name ); ?>><?php echo esc_attr( $carrier_item['name'] ); ?></option>
							<?php										
						}
					}
					?>
				</select>
				<input type="hidden" class="tdws-carrier-code" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][carrier_code]" value="<?php echo esc_attr( $tdws_carrier_code ); ?>">				
			</div>
			<?php 
			do_action( 'tdws_add_extra_tracking_field', $order );
			?>					
			<div class="tdws-field-col tdws-field-remove">
				<input type="hidden" class="tdws-item-id" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][tdws_item_id]" value="<?php echo esc_attr( $tdws_item_id ); ?>">
				<input type="hidden" class="tdws-send-mail" name="<?php echo esc_attr( $tdws_input_name ); ?>[<?php echo esc_attr( $tk_key ); ?>][tdws_send_mail]" value="0">
				<ul class="tdws-field-actions <?php echo !isset($tk_value['hidden_meta']) ? "active" : ""; ?>">
					<li>
						<button type="button" class="tdws-icon-button tdws-send-mail-again" data-id="<?php echo esc_attr( $tdws_item_id ); ?>" title="<?php _e( 'Send Mail Again', 'tdws-order-tracking-system' ); ?>"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M215.4 96H144 107.8 96v8.8V144v40.4 89L.2 202.5c1.6-18.1 10.9-34.9 25.7-45.8L48 140.3V96c0-26.5 21.5-48 48-48h76.6l49.9-36.9C232.2 3.9 243.9 0 256 0s23.8 3.9 33.5 11L339.4 48H416c26.5 0 48 21.5 48 48v44.3l22.1 16.4c14.8 10.9 24.1 27.7 25.7 45.8L416 273.4v-89V144 104.8 96H404.2 368 296.6 215.4zM0 448V242.1L217.6 403.3c11.1 8.2 24.6 12.7 38.4 12.7s27.3-4.4 38.4-12.7L512 242.1V448v0c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64v0zM176 160H336c8.8 0 16 7.2 16 16s-7.2 16-16 16H176c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H336c8.8 0 16 7.2 16 16s-7.2 16-16 16H176c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg></span></button>
					</li>
					<li>
						<button type="button" class="tdws-icon-button tdws-retracking-btn" data-id="<?php echo esc_attr( $tdws_item_id ); ?>" title="<?php _e( 'Re-Tracking', 'tdws-order-tracking-system' ); ?>"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M0 48C0 21.5 21.5 0 48 0H368c26.5 0 48 21.5 48 48V96h50.7c17 0 33.3 6.7 45.3 18.7L589.3 192c12 12 18.7 28.3 18.7 45.3V256v32 64c17.7 0 32 14.3 32 32s-14.3 32-32 32H576c0 53-43 96-96 96s-96-43-96-96H256c0 53-43 96-96 96s-96-43-96-96H48c-26.5 0-48-21.5-48-48V48zM416 256H544V237.3L466.7 160H416v96zM160 464a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm368-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM257 95c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l39 39H96c-13.3 0-24 10.7-24 24s10.7 24 24 24H262.1l-39 39c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l80-80c9.4-9.4 9.4-24.6 0-33.9L257 95z"/></svg></span></button>
					</li>
					<li>
						<button type="button" class="tdws-icon-button tdws-stop-tracking-btn" data-id="<?php echo esc_attr( $tdws_item_id ); ?>" title="<?php _e( 'Stop Tracking', 'tdws-order-tracking-system' ); ?>"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"/></svg></span></button>
					</li>
				</ul>
				<button type="button" class="tdws-icon-button tdws-remove-item-btn" title="<?php _e( 'Remove', 'tdws-order-tracking-system' ); ?>"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg></span></button>
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