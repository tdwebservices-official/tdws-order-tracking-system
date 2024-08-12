(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// TDWS Check Tracking Status

	jQuery(document).on( 'click', '.tdws-tracking-updates-btn', function(e) {
		var tracking_no = jQuery(this).attr( 'data-tracking-no' );		
		var order_date = jQuery(this).attr( 'data-order-date' );		
		var t_this = jQuery(this);
		t_this.addClass('active');
		jQuery.ajax({
			type : "POST",
			url : tdwsAjax.ajax_url,
			dataType : "json",
			data : { 'action': 'tdws_tracking_data_by_tracking_no', 'tracking_no': tracking_no, 'order_date': order_date, ajax_nonce: tdwsAjax.nonce  },
			success: function(res) {			
				jQuery('#tdws-tracking-update-popup .tdws-traking-event-updates').html( res.tracking_status_html );
				jQuery('#tdws-tracking-update-popup,body').addClass('tdws-model-open');	
				t_this.removeClass('active');					
			},
			error: function(res) {				
				t_this.removeClass('active');	
			}
		});
		
	});

	// TDWS Popup Hide Outside Div Click
	jQuery(document).mouseup(function(e){
		var container = jQuery(".tdws-popup-wrapper");    
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			jQuery('.tdws-popup,body').removeClass('tdws-model-open');
		}
	});

	// TDWS Close Popup Event
	jQuery(document).on( 'click', '.tdws-close', function(e) {
		jQuery('.tdws-popup,body').removeClass('tdws-model-open');
	});


})( jQuery );
