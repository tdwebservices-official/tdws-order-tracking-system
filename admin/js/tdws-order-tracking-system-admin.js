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
	setTimeout(function(){
		if( jQuery('.tdws-tag-list').length > 0 ){
			jQuery('#wc-orders-filter, #posts-filter').prepend(jQuery('.tdws-tag-list').clone(true));
			jQuery('.tdws-tag-list').last().remove();
			jQuery('.tdws-tag-list').addClass('active');
		}
		if( jQuery('.tdws-show-item').length > 0 ){
			jQuery('.tdws-show-item').each(function () {
				jQuery(this).find( '.tdws-product-select' ).select2({  allowClear: true });
				jQuery(this).find( '.tdws-tracking-status' ).select2();
				jQuery(this).find( '.tdws-pickup-date' ).datepicker({dateFormat:'yy-mm-dd'});
			});
		}
		if( jQuery('.tdws-add-items-btn').length > 0 ){
			jQuery('.tdws-add-items-btn').attr( 'data-cnt', jQuery('.tdws-show-item').length );
		}
	},1000);

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
		jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-order-tracking-items .tdws-show-item' ).last().find( '.tdws-product-select' ).select2({  allowClear: true });
		jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-order-tracking-items .tdws-show-item' ).last().find( '.tdws-tracking-status' ).select2();
		jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-order-tracking-items .tdws-show-item' ).last().find( '.tdws-pickup-date' ).datepicker({dateFormat:'yy-mm-dd'});
		row_count = parseFloat(row_count) + 1;
		jQuery(this).attr( 'data-cnt', row_count );
	} );

	jQuery(document).on( 'click', '.tdws-remove-item-btn', function(){
		jQuery(this).closest('.tdws-show-item').remove();
	} );

	jQuery(document).on( 'click', '.tdws-item-label', function(){
		jQuery(this).closest('.tdws-show-item').find('.tdws-field-box-inner').slideToggle();
		jQuery(this).closest('.tdws-show-item').toggleClass('active');

	} );

	jQuery(document).on( 'change', '.tdws-show-item .tdws-field-box input,.tdws-show-item .tdws-field-box select', function(){		
		var update_flag = tdws_tracking_item_change( jQuery(this).closest('.tdws-show-item') );	
		jQuery(this).closest('.tdws-show-item').find('.tdws-send-mail').val(update_flag);							
	} );

	jQuery(document).on( 'change', '.tdws_enable_tracking', function(){
		if( jQuery(this).prop('checked') == true ){
			jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-items-box' ).removeClass( 'tdws-hide' );
		}else{
			jQuery(this).closest('.tdws-order-tracking-wrap').find( '.tdws-items-box' ).addClass( 'tdws-hide' );
		}
	} );

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