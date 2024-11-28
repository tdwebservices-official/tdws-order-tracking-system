<?php
/**
 * TDWS Email Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$WC_Emails = new WC_Emails;
$WC_Emails->email_header( $email_heading, $email );

/* translators: %s: Customer first name */ 
if( $auto_completed_reminder_before_html ){
	echo wp_kses_post( $auto_completed_reminder_before_html );
}
?>
<div style="margin-bottom: 40px;margin-top: 15px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product Information', 'woocommerce' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>							
			</tr>
		</thead>
		<tbody>
			<?php
			$text_align  = is_rtl() ? 'right' : 'left';
			$margin_side = is_rtl() ? 'left' : 'right';
			foreach ( $items as $item_id => $item ) {

				$product       = $item->get_product();
				$sku           = '';
				$purchase_note = '';
				$image         = '';

				if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
					continue;
				}

				if ( is_object( $product ) ) {
					$sku           = $product->get_sku();
					$purchase_note = $product->get_purchase_note();
					$image         = $product->get_image( $image_size );
				}

				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
						<?php

						// Show title/image etc.
						if ( $show_image ) {
							echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) );
						}

						echo "<span style='display: inline-block;vertical-align: middle;width:80%;'>";

						// Product name.
						echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );

						// SKU.
						if ( $show_sku && $sku ) {
							echo wp_kses_post( ' (#' . $sku . ')' );
						}
						
						echo "</span>";

						echo "</span>";

						include 'tdws-email-tracking-info.php';
						?>
					</td>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
						<?php
						$qty          = $item->get_quantity();
						$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

						if ( $refunded_qty ) {
							$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
						} else {
							$qty_display = esc_html( $qty );
						}
						echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );
						?>
					</td>
					
				</tr>
				<?php 
			}
			?>
		</tbody>
	</table>
</div>
<?php 
if( $auto_completed_reminder_after_html ){
	echo wp_kses_post( $auto_completed_reminder_after_html );
}

$WC_Emails->email_footer();
