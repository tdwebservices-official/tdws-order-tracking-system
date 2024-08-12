(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

	// TDWS Tag Facility & Tracking Item Repeater
	setTimeout(function(){
		if( jQuery('.tdws-tag-list').length > 0 ){
			jQuery('#wc-orders-filter, #posts-filter').prepend(jQuery('.tdws-tag-list').clone(true));
			jQuery('.tdws-tag-list').last().remove();
			jQuery('.tdws-tag-list').addClass('active');
		}
		if( jQuery('.tdws-show-item').length > 0 ){
			jQuery('.tdws-show-item').each(function () {
				jQuery(this).find( '.tdws-product-select' ).select2({ placeholder : jQuery(this).find( '.tdws-product-select' ).attr('data-placeholder'),  allowClear: true });
				jQuery(this).find( '.tdws-carries-name' ).select2({placeholder : jQuery(this).find( '.tdws-carries-name' ).attr('data-placeholder')});				
			});
		}
		if( jQuery('.tdws-add-items-btn').length > 0 ){
			jQuery('.tdws-add-items-btn').attr( 'data-cnt', jQuery('.tdws-show-item').length );
		}
	},1500);

	// TDWS Set Mail Body

	jQuery(document).on( 'click', '.tdws-set-mail-body', function(e) {
		var data_key = jQuery(this).attr( 'data-key' );
		jQuery('.tdws-tracking-status-form input[name="option_type"]').val( jQuery(this).attr( 'data-key' ) );
		var t_this = jQuery(this);
		t_this.addClass('active');
		jQuery.ajax({
			type : "POST",
			url : tdwsAjax.ajax_url,
			dataType : "json",
			data : { 'action': 'tdws_tracking_get_mail_body_data' , 'option_key': data_key, ajax_nonce: tdwsAjax.nonce  },
			success: function(res) {
				if( res.data ){
					jQuery('.tdws-tracking-status-form .tdws-subject-tr input').val(res.data.subject);
					jQuery('.tdws-tracking-status-form .tdws-email-heading-tr input').val(res.data.email_heading);
					jQuery('.tdws-tracking-status-form .tdws-email-item-body textarea').val(res.data.email_body);
					if (typeof tinyMCE !== 'undefined') {
						tinymce.get('tdws_ord_track_mail_email_body').setContent(res.data.email_body);
					}	
				}	
				t_this.removeClass('active');	
				jQuery('#tdws-tracking-status-popup,body').addClass('tdws-model-open');

			},
			error: function(res) {				
				t_this.removeClass('active');	
			}
		});
		
	});

	// TDWS Send Mail Again Facility
	jQuery(document).on( 'click', '.tdws-send-mail-again', function(e) {
		var tdws_item_id = jQuery(this).attr( 'data-id' );		
		jQuery('#tdws-send-mail-again-popup input[name="tdws_tracking_id"]').val(tdws_item_id);		
		jQuery('#tdws-send-mail-again-popup,body').addClass('tdws-model-open');		
	});

	// TDWS Stop Tracking Button Facility
	jQuery(document).on( 'click', '.tdws-stop-tracking-btn', function(e) {
		var tdws_item_id = jQuery(this).attr( 'data-id' );		
		jQuery('#tdws-stop-tracking-popup input[name="tdws_tracking_id"]').val(tdws_item_id);		
		jQuery('#tdws-stop-tracking-popup,body').addClass('tdws-model-open');		
	});

	// TDWS Re-Tracking Button Facility
	jQuery(document).on( 'click', '.tdws-retracking-btn', function(e) {
		var tdws_item_id = jQuery(this).attr( 'data-id' );		
		jQuery('#tdws-re-tracking-popup input[name="tdws_tracking_id"]').val(tdws_item_id);		
		jQuery('#tdws-re-tracking-popup,body').addClass('tdws-model-open');		
	});

	// TDWS Tracking Status Submit Hook
	jQuery(document).on( 'submit', '.tdws-tracking-status-form', function(e) {

		e.preventDefault();
		var t_this = jQuery(this);		
		var FormData = jQuery(this).serialize();
		var action   = jQuery(this).find('input[name="action"]').val();
		if( t_this.find('.tdws-loader-button').hasClass('active') ){
			return false;
		}
		t_this.find('.tdws-loader-button').addClass('active');	
		jQuery.ajax({
			type : "POST",
			url : tdwsAjax.ajax_url,
			dataType : "json",
			data : { 'action': action , 'formdata': FormData,ajax_nonce: tdwsAjax.nonce },			
			success: function(data) {				
				t_this.find('.tdws-loader-button').removeClass('active');				
				if( data.type == 'success' ){
					t_this.find('.tdws-success-msg').fadeIn();
				}				
				setTimeout(function() {
					t_this.find('.tdws-success-msg').fadeOut();
				}, 3000)
			},
			error: function(data) {
				t_this.find('.tdws-loader-button').removeClass('active');
				t_this.find('.tdws-error-msg').fadeIn();
				setTimeout(function() {
					t_this.find('.tdws-error-msg').fadeOut();
				}, 3000);
			}
		});

		return false;

	});

	// TDWS Tracking Send Mail Again Form Submit
	jQuery(document).on( 'submit', '.tdws-send-mail-again-form', function(e) {

		e.preventDefault();
		var t_this = jQuery(this);		
		var FormData = jQuery(this).serialize();
		var action   = jQuery(this).find('input[name="action"]').val();
		if( t_this.find('.tdws-loader-button').hasClass('active') ){
			return false;
		}
		t_this.find('.tdws-loader-button').addClass('active');	
		jQuery.ajax({
			type : "POST",
			url : tdwsAjax.ajax_url,
			dataType : "json",
			data : { 'action': action , 'formdata': FormData,ajax_nonce: tdwsAjax.nonce },			
			success: function(data) {				
				t_this.find('.tdws-loader-button').removeClass('active');				
				if( data.type == 'success' ){
					t_this.find('.tdws-success-msg').fadeIn();
					jQuery('.tdws-send-mail-again-form .tdws-email-item-notes textarea').val('');
					if (typeof tinyMCE !== 'undefined') {
						tinymce.get('tdws_add_some_notes').setContent('');
					}
				}
				if( data.type == 'fail' ){
					t_this.find('.tdws-error-msg').fadeIn();
				}				
				setTimeout(function() {
					t_this.find('.tdws-success-msg').fadeOut();
					t_this.find('.tdws-error-msg').fadeOut();
				}, 3000)
			},
			error: function(data) {
				t_this.find('.tdws-loader-button').removeClass('active');
				t_this.find('.tdws-error-msg').fadeIn();
				setTimeout(function() {
					t_this.find('.tdws-error-msg').fadeOut();
				}, 3000);
			}
		});

		return false;

	});
	
	// TDWS Tracking Stop Tracking Form Submit
	jQuery(document).on( 'submit', '.tdws-stop-tracking-form', function(e) {

		e.preventDefault();
		var t_this = jQuery(this);		
		var FormData = jQuery(this).serialize();
		var action   = jQuery(this).find('input[name="action"]').val();
		if( t_this.find('.tdws-loader-button').hasClass('active') ){
			return false;
		}
		t_this.find('.tdws-loader-button').addClass('active');	
		jQuery.ajax({
			type : "POST",
			url : tdwsAjax.ajax_url,
			dataType : "json",
			data : { 'action': action , 'formdata': FormData,ajax_nonce: tdwsAjax.nonce },			
			success: function(data) {				
				t_this.find('.tdws-loader-button').removeClass('active');				
				if( data.type == 'success' ){
					t_this.find('.tdws-success-msg').fadeIn();
					jQuery('.tdws-stop-tracking-form .tdws-email-item-notes textarea').val('');
					if (typeof tinyMCE !== 'undefined') {
						tinymce.get('tdws_stop_track_notes').setContent('');
					}
				}
				if( data.type == 'fail' ){
					t_this.find('.tdws-error-msg').fadeIn();
				}				
				setTimeout(function() {
					t_this.find('.tdws-success-msg').fadeOut();
					t_this.find('.tdws-error-msg').fadeOut();
				}, 3000)
			},
			error: function(data) {
				t_this.find('.tdws-loader-button').removeClass('active');
				t_this.find('.tdws-error-msg').fadeIn();
				setTimeout(function() {
					t_this.find('.tdws-error-msg').fadeOut();
				}, 3000);
			}
		});
		return false;

	});

	// TDWS Tracking Re-Tracking Form Submit
	jQuery(document).on( 'submit', '.tdws-re-tracking-form', function(e) {

		e.preventDefault();
		var t_this = jQuery(this);		
		var FormData = jQuery(this).serialize();
		var action   = jQuery(this).find('input[name="action"]').val();
		if( t_this.find('.tdws-loader-button').hasClass('active') ){
			return false;
		}
		t_this.find('.tdws-loader-button').addClass('active');	
		jQuery.ajax({
			type : "POST",
			url : tdwsAjax.ajax_url,
			dataType : "json",
			data : { 'action': action , 'formdata': FormData,ajax_nonce: tdwsAjax.nonce },			
			success: function(data) {				
				t_this.find('.tdws-loader-button').removeClass('active');				
				if( data.type == 'success' ){
					t_this.find('.tdws-success-msg').fadeIn();
					jQuery('.tdws-re-tracking-form .tdws-email-item-notes textarea').val('');
					if (typeof tinyMCE !== 'undefined') {
						tinymce.get('tdws_re_track_notes').setContent('');
					}
				}
				if( data.type == 'fail' ){
					t_this.find('.tdws-error-msg').fadeIn();
				}				
				setTimeout(function() {
					t_this.find('.tdws-success-msg').fadeOut();
					t_this.find('.tdws-error-msg').fadeOut();
				}, 3000)
			},
			error: function(data) {
				t_this.find('.tdws-loader-button').removeClass('active');
				t_this.find('.tdws-error-msg').fadeIn();
				setTimeout(function() {
					t_this.find('.tdws-error-msg').fadeOut();
				}, 3000);
			}
		});
		return false;

	});
	
	// TDWS Close Popup Event	
	jQuery(document).on( 'click', '.tdws-close', function(e) {
		jQuery('.tdws-popup,body').removeClass('tdws-model-open');
	});

	// TDWS Mail Active or not facility
	jQuery(document).on( 'change', '.tdws-checkbox-box input', function(e) {
		if( jQuery(this).prop( 'checked' ) == true ){
			jQuery(this).parent().siblings('.tdws-mail-button').addClass('tdws-active');
		}else{
			jQuery(this).parent().siblings('.tdws-mail-button').removeClass('tdws-active');
		}
	});

	// TDWS Outside Popup hide facility
	jQuery(document).mouseup(function(e){
		var container = jQuery(".tdws-popup-wrapper");    
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			jQuery('.tdws-popup,body').removeClass('tdws-model-open');
		}
	});

	// TDWS Tracking Meta Box

	jQuery(document).on( 'click', '.tdws-add-items-btn', function(){
		var row_count = jQuery(this).attr('data-cnt');
		if( row_count == undefined ){
			row_count = 0;
		}		
		var row_html = jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-order-tracking-items .tdws-hide-item' ).html();
		row_html = row_html.replace(/hide_tdws_meta/g, 'tdws_meta');
		row_html = row_html.replace(/tdws_row_no/g, row_count );				
		jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-order-tracking-items' ).append( '<div class="tdws-show-item tdws-order-tracking-item">'+row_html+'</div>' );
		jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-order-tracking-items .tdws-show-item' ).last().find( '.tdws-product-select' ).select2({ placeholder : jQuery(this).find( '.tdws-product-select' ).attr('data-placeholder'), allowClear: true });
		jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-order-tracking-items .tdws-show-item' ).last().find( '.tdws-carries-name' ).select2({placeholder : jQuery(this).find( '.tdws-carries-name' ).attr('data-placeholder')});
		row_count = parseFloat(row_count) + 1;
		jQuery(this).attr( 'data-cnt', row_count );
	} );

	// TDWS Remove Tracking Item
	jQuery(document).on( 'click', '.tdws-remove-item-btn', function(){
		jQuery(this).closest('.tdws-show-item').remove();
	} );

	// TDWS Label Click Accordian ON or Off
	jQuery(document).on( 'click', '.tdws-item-label', function(){
		jQuery(this).closest('.tdws-show-item').find('.tdws-field-box-inner').slideToggle();
		jQuery(this).closest('.tdws-show-item').toggleClass('active');
	} );

	// TDWS Input/Select Change
	jQuery(document).on( 'change', '.tdws-show-item .tdws-field-box input,.tdws-show-item .tdws-field-box select', function(){		
		var update_flag = tdws_tracking_item_change( jQuery(this).closest('.tdws-show-item') );	
		jQuery(this).closest('.tdws-show-item').find('.tdws-send-mail').val(update_flag);							
	} );

	// TDWS Carries Change Event
	jQuery(document).on( 'change', '.tdws-show-item .tdws-field-col .tdws-carries-name', function(){		
		var carrier_code = jQuery(this).parent().find("option:selected").attr('data-code');
		if( carrier_code === undefined ){
			carrier_code = '';
		}
		jQuery(this).closest('.tdws-show-item').find('.tdws-carrier-code').val( carrier_code );							
	} );

	// TDWS Enable/Disable Tracking
	jQuery(document).on( 'change', '.tdws_enable_tracking', function(){
		if( jQuery(this).prop('checked') == true ){
			jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-items-box' ).removeClass( 'tdws-hide' );
		}else{
			jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-items-box' ).addClass( 'tdws-hide' );
		}
	} );

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


})( jQuery );

function tdws_tracking_item_change( parent_this ){
	
	var change_flag = 0;
	var error_flag = 0;
	parent_this.find( '.tdws-field-box input.tdws-input-control' ).each(function () {
		if( !jQuery(this).hasClass('.tdws-item-id') && !jQuery(this).hasClass('.tdws-send-mail') ){
			var old_value = jQuery(this).attr('old-value');
			if( old_value == undefined ){
				old_value = '';
			}
			old_value = old_value.trim();
			var new_value = jQuery(this).val();
			if( new_value == undefined ){
				new_value = '';
			}
			new_value = new_value.trim();			
			if( old_value != new_value ){
				change_flag = 1;
			}			
		}		
	});

	parent_this.find( '.tdws-field-box select.tdws-input-control' ).each(function () {
		
		var old_select_value = jQuery(this).attr('old-value');
		if( old_select_value == undefined ){
			old_select_value = '';
		}	
		old_select_value = old_select_value.trim();

		var new_select_value = jQuery(this).val();
		if( new_select_value == undefined ){
			new_select_value = '';
		}
		
		if( new_select_value == undefined || new_select_value == '' ){
			if( jQuery(this).hasClass('tdws-product-select') ){
				error_flag = 1;
			}
		}

		var input_name = jQuery(this).attr('name');
		if( input_name == undefined ){
			input_name = '';
		}
		var array_flag = 0;
		input_name = input_name.trim();
		if( input_name ){
			if( input_name.indexOf('[]') !== -1 ){
				array_flag = 1;				
			}
		} 
		if( array_flag ){
			var old_select_value_arr = [];
			if( old_select_value ){
				old_select_value_arr = old_select_value.split(',');
			}
			var difference = jQuery.grep( new_select_value, function(element) {
				return jQuery.inArray( element, old_select_value_arr ) == -1;
			});			
			if( difference.length > 0 || ( old_select_value_arr.length > 0 && new_select_value.length == 0 ) ){
				change_flag = 1;
			}
		}else{
			new_select_value = new_select_value.trim();
			if( old_select_value != new_select_value ){
				change_flag = 1;
			}
		}
	});
	
	if( error_flag ){
		parent_this.addClass('tdws-error');
		parent_this.find( '.tdws-field-box select.tdws-input-control:not(.tdws-product-select)' ).addClass('disabled').attr( 'disabled', true );
		parent_this.find( '.tdws-field-box input.tdws-input-control' ).addClass('disabled').attr( 'disabled', true );
		parent_this.find( '.tdws-field-box textarea.tdws-input-control' ).addClass('disabled').attr( 'disabled', true );
	}else{
		parent_this.removeClass('tdws-error');
		parent_this.find( '.tdws-field-box select.tdws-input-control:not(.tdws-product-select)' ).removeClass('disabled').removeAttr( 'disabled' );
		parent_this.find( '.tdws-field-box input.tdws-input-control' ).removeClass('disabled').removeAttr( 'disabled' );
		parent_this.find( '.tdws-field-box textarea.tdws-input-control' ).removeClass('disabled').removeAttr( 'disabled' );
	}
	
	return change_flag;
}