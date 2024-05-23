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

	jQuery(document).on( "click", ".tdws-edit-tag", function () {
		jQuery(this).addClass("dokan-hide").closest("li").next("li").removeClass("dokan-hide");
		return false;
	});
	jQuery(document).on("click", ".tdws-cancel-tag", function () {
		jQuery(this).closest("li").addClass("dokan-hide").prev("li").find("a.tdws-edit-tag").removeClass("dokan-hide");
		return false;
	});

	jQuery(document).on( "submit", "form#tdws-order-tag-form",  function (t) {
		t.preventDefault();
		var f_this = jQuery(this);
		var next_ul = f_this.closest("li");
		f_this.closest("li").block({ message: null, overlayCSS: { background: "#fff url(" + dokan.ajax_loader + ") no-repeat center", opacity: 0.6 } });
		jQuery.post( dokan.ajaxurl, f_this.serialize(), function (e) {
			if ((next_ul.unblock(), e.success)) {
				var t = next_ul.prev();
				next_ul.addClass("dokan-hide"), t.find("label").replaceWith(e.data), t.find("a.tdws-edit-tag").removeClass("dokan-hide");
			} else {
				dokan_sweetalert(e.data, { icon: "success" })
			}
		});
	});

	setTimeout(function(){
		var order_tag = getUrlParameter('order_tag');
		if( order_tag == null || order_tag == undefined ){
			order_tag = '';
		}
		if( jQuery('.dokan-order-filter-serach .dokan-left .dokan-form-group').length > 0 ){
			jQuery('.dokan-order-filter-serach .dokan-left .dokan-form-group,.dokan-order-filter-serach .dokan-right .dokan-form-group').append( '<input type="hidden" name="order_tag" value="'+order_tag+'" />' );	
		}
	},1000);

})( jQuery );

function getUrlParameter(name) {
	name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
	var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
	var results = regex.exec(location.search);
	return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};