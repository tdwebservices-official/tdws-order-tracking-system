<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/admin
 * @author     TD Web Services <info@tdwebservices.com>
 */
class Tdws_Order_Tracking_System_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Add Order Tracking Option Page Menu
		add_action( 'admin_menu', array( $this, 'tdws_add_plugin_menu_hook' ) );

		// Save tracking mail body data hook
		add_action( "wp_ajax_".$this->plugin_name."_tracking_mail_body_save", array( $this, "tdws_plugin_tracking_mail_body_save" ) );
		add_action( "wp_ajax_nopriv_".$this->plugin_name."_tracking_mail_body_save", array( $this, "tdws_plugin_tracking_mail_body_save" ) );

		// Get tracking mail body data hook
		add_action( "wp_ajax_tdws_tracking_get_mail_body_data", array( $this, "tdws_tracking_get_mail_body_data" ) );
		add_action( "wp_ajax_nopriv_tdws_tracking_get_mail_body_data", array( $this, "tdws_tracking_get_mail_body_data" ) );

		// This hook use for report page script enable
		add_filter( 'woocommerce_reports_screen_ids', array( $this, 'tdws_custom_wc_reports_screen_ids' ), 11, 1 );

		 // Add Order Tag Field On Edit Order Page
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'tdws_add_order_tag_field_order_edit' ), 20, 1 );

		// Save Order Tag Field On Edit Order Page
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'tdws_save_order_tag_field_order_edit' ), 90, 2 );	

		// Update Order Tag Field On Change On Order Status
		add_action('woocommerce_order_status_failed', array( $this, 'tdws_update_order_tag_with_fail_status' ), 9999, 3 );	

		// Update Order Tag Field On Change On Order Status
		add_action('woocommerce_order_status_failed_to_processing', array( $this, 'tdws_update_order_tag_with_fail_to_process_status' ), 99, 2 );	
		add_action('woocommerce_order_status_failed_to_completed', array( $this, 'tdws_update_order_tag_with_fail_to_process_status' ), 99, 2 );	
		add_action('woocommerce_order_status_failed_to_on-hold', array( $this, 'tdws_update_order_tag_with_fail_to_process_status' ), 99, 2 );	
		add_action('woocommerce_order_status_to_failed', array( $this, 'tdws_update_order_tag_with_process_to_fail_status' ), 99, 2 );	
		add_action('woocommerce_order_status_on-hold_to_failed', array( $this, 'tdws_update_order_tag_with_process_to_fail_status' ), 99, 2 );	
		add_action('woocommerce_order_status_processing_to_failed', array( $this, 'tdws_update_order_tag_with_process_to_fail_status' ), 99, 2 );
		add_action('woocommerce_order_status_pending_to_failed', array( $this, 'tdws_update_order_tag_with_process_to_fail_status' ), 99, 2 );	

		// Add custom order tracking tag On List Order Page
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'tdws_add_order_tag_column_order_list' ), 99, 1 );
		add_filter( 'woocommerce_shop_order_list_table_columns', array( $this, 'tdws_add_order_tag_column_order_list' ), 99, 1 );
		add_action('manage_shop_order_posts_custom_column', array( $this, 'tdws_show_order_tag_column_order_list' ), 99, 2 );
		add_action( 'woocommerce_shop_order_list_table_custom_column', array( $this, 'tdws_show_order_tag_column_order_list' ), 99, 2 );

		// add Order Filter HTML On List Order Page
		add_action( 'manage_posts_extra_tablenav', array( $this, 'tdws_add_order_tag_filter_list' ), 99, 1 );
		add_action( 'woocommerce_order_list_table_extra_tablenav', array( $this, 'tdws_custom_filter_order_list_table_extra_tablenav' ), 99, 2 );
		add_action( 'woocommerce_order_list_table_extra_tablenav', array( $this, 'tdws_custom_action_order_list_table_extra_tablenav' ), 98, 2 );

		// add Order FilterHook to apply the filter
		add_action( 'pre_get_posts',  array( $this, 'tdws_apply_order_tag_filter_list' ), 99, 1 );
		add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'tdws_apply_order_tag_filter_list_by_table' ), 99, 1  );

		// Delete tdws tag data on init
		add_action( 'admin_init', array( $this, 'tdws_delete_hook_call' ) );

		// Hide Specific Order Item Meta
		add_action( 'woocommerce_hidden_order_itemmeta', array( $this, 'tdws_hidden_order_itemmeta' ), 99, 1 );

		// This hook fire when update a plugin
		add_action( 'upgrader_process_complete', array( $this, 'tdws_after_upgrade_function_load' ), 99, 2 );

		// This hook manually added missing column or table
		add_action( "wp_ajax_tdws_sync_tdws_tables", array( $this, "tdws_sync_tdws_tables" ) );

		// Update Permalink
		add_action( 'init', array( $this, 'tdws_update_flush_permalink' ), 999 );
		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tdws_Order_Tracking_System_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tdws_Order_Tracking_System_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$tdws_screen    = get_current_screen();
		$tdws_screen_id = isset($tdws_screen->id) ? $tdws_screen->id : '';

		if ( $tdws_screen_id == 'tdws_page_tdws-order-tracking-report' ) {
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'jquery-ui-style' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'woocommerce_admin_print_reports_styles' );
		}

		global $post;
		
		if( isset($post->post_type) && $post->post_type == 'tdws-coupon' ){			
			wp_enqueue_style( 'jquery-ui-style' );
		}
		
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tdws-order-tracking-system-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-tdws-popup', plugin_dir_url( __FILE__ ) . 'css/tdws-popup.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tdws_Order_Tracking_System_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tdws_Order_Tracking_System_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		global $post;
		
		if( isset($post->post_type) && $post->post_type == 'tdws-coupon' ){			
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tdws-order-tracking-system-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'tdwsAjax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce('tdws_form_save') ) );       

	}	

	/**
	 * This hook define a report page screen id add
	 *
	 * @since    1.1.0
	 */
	public function tdws_custom_wc_reports_screen_ids( $screen_id_list ){
		$screen_id_list[] = 'tdws_page_tdws-order-tracking-report';
		return $screen_id_list;
	}


	/**
	 * Call admin menu hook.
	 *
	 * @since    1.0.0
	 */
	public function tdws_add_plugin_menu_hook(){


		add_menu_page( __( 'TDWS', 'tdws-order-tracking-system' ), __( 'TDWS', 'tdws-order-tracking-system' ), 'tdws_plugins', 'tdws_order_tracking', array( $this, 'tdws_menu_option_page' ), 'dashicons-admin-generic', 25 );

		add_submenu_page( 'tdws_order_tracking', __( 'Order Tracking System', 'tdws-order-tracking-system' ), __( 'Order Tracking System', 'tdws-order-tracking-system' ), 'administrator', 'tdws-order-tracking-system', array( $this, 'tdws_menu_option_page' ) );

		add_submenu_page( 'tdws_order_tracking', __( '17TRACK API Setting', 'tdws-order-tracking-system' ), __( '17TRACK API Setting', 'tdws-order-tracking-system' ), 'administrator', '17track-api-setting', array( $this, 'tdws_17track_api_setting_page' ) );

		add_submenu_page( 'tdws_order_tracking', __( 'Order Tracking Report', 'tdws-order-tracking-system' ), __( 'Order Tracking Report', 'tdws-order-tracking-system' ), 'administrator', 'tdws-order-tracking-report', array( $this, 'tdws_17track_report_setting_page' ) );
		add_submenu_page( 'edit.php?post_type=tdws-coupon', __( 'Settings', 'tdws-order-tracking-system' ), __( 'Settings', 'tdws-order-tracking-system' ), 'administrator', 'tdws-coupon-setting', array( $this, 'tdws_coupon_setting_page' ) );

		
		add_action( 'admin_init', array( $this, 'tdws_register_menu_option_page_setting' ) );

	}

	/**
	 * Save and register option setting hook.
	 *
	 * @since    1.0.0
	 */

	public function tdws_register_menu_option_page_setting() {
		
		//register plugin settings
		if ( isset( $_POST['tdwsformType'] ) && wp_verify_nonce( $_POST['tdwsformType'], $this->plugin_name.'-generalData' ) ) {			
			register_setting( 'tdws-order-tracking-setting', 'tdws_ord_track_opt' );
			register_setting( 'tdws-order-tracking-setting', 'tdws_ord_track_mail' );	
			register_setting( 'tdws-order-tracking-setting', 'tdws_ord_reminder_mail' );								
		}

		if ( isset( $_POST['tdwsformType'] ) && wp_verify_nonce( $_POST['tdwsformType'], $this->plugin_name.'-apiData' ) ) {			
			register_setting( 'tdws-order-tracking-setting', 'tdws_17track_opt' );
		}

		if ( isset( $_POST['tdwsformType'] ) && wp_verify_nonce( $_POST['tdwsformType'], $this->plugin_name.'-tdws-coupon' ) ) {	
			register_setting( 'tdws-coupon-setting', 'tdws_coupon_settings_opt' );
		}

	}

	/**
	 * Flush Permalink
	 *
	 * @since    1.1.9
	 */

	public function tdws_update_flush_permalink(){
		if ( isset( $_POST['tdwsformType'] ) && wp_verify_nonce( $_POST['tdwsformType'], $this->plugin_name.'-tdws-coupon' ) ) {	
			flush_rewrite_rules(); 
		}
	}

	/**
	 * Make option setting page.
	 *
	 * @since    1.0.0
	 */
	/**
	 * Make option setting page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_menu_option_page() {

		$add_order_tags = tdws_get_order_tages();
		$tdws_ord_track_opt = get_option( 'tdws_ord_track_opt' );		
		$tdws_ord_track_mail = get_option( 'tdws_ord_track_mail' );		
		$tdws_ord_reminder_mail = get_option( 'tdws_ord_reminder_mail' );		
		$default_tag_name = apply_filters( 'set_default_tdws_tag_name', 'New' );
		$default_track_subject = apply_filters( 'tdws_order_tracking_mail_subject', 'Order Tracking' );
		$default_track_heading = apply_filters( 'tdws_order_tracking_mail_email_heading', 'Order Tracking' );
		$default_track_email_top = apply_filters( 'tdws_order_tracking_mail_before_item_html', 'Hii [first_name]' );

		$default_reminder_subject = apply_filters( 'tdws_order_reminder_mail_subject', 'Order Reminder' );
		$default_reminder_heading = apply_filters( 'tdws_order_reminder_mail_email_heading', 'Order Reminder' );
		$default_reminder_email_top = apply_filters( 'tdws_order_reminder_mail_before_item_html', 'Hii [first_name]' );

		$set_default_order_tag = isset($tdws_ord_track_opt['set_default_order_tag']) ? $tdws_ord_track_opt['set_default_order_tag'] : $default_tag_name;
		$order_tag_colour = isset($tdws_ord_track_opt['order_tag_colour']) ? $tdws_ord_track_opt['order_tag_colour'] : '';
		$tag_text_colour = isset($tdws_ord_track_opt['tag_text_colour']) ? $tdws_ord_track_opt['tag_text_colour'] : '';
		$tdws_track_subject = isset($tdws_ord_track_mail['subject']) ? $tdws_ord_track_mail['subject'] : $default_track_subject;
		$tdws_track_email_heading = isset($tdws_ord_track_mail['email_heading']) ? $tdws_ord_track_mail['email_heading'] : $default_track_heading;		
		$tdws_track_email_top = isset($tdws_ord_track_mail['email_top_html']) ? $tdws_ord_track_mail['email_top_html'] : $default_track_email_top;
		$tdws_track_email_bottom = isset($tdws_ord_track_mail['email_bottom_html']) ? $tdws_ord_track_mail['email_bottom_html'] : '';
		$auto_completed_reminder_day = isset($tdws_ord_reminder_mail['auto_completed_reminder_day']) ? $tdws_ord_reminder_mail['auto_completed_reminder_day'] : '';
		$auto_completed_finish_day = isset($tdws_ord_reminder_mail['auto_completed_finish_day']) ? $tdws_ord_reminder_mail['auto_completed_finish_day'] : '';
		$auto_completed_reminder_after_html = isset($tdws_ord_reminder_mail['auto_completed_reminder_after_html']) ? $tdws_ord_reminder_mail['auto_completed_reminder_after_html'] : '';
		$auto_completed_reminder_before_html = isset($tdws_ord_reminder_mail['auto_completed_reminder_before_html']) ? $tdws_ord_reminder_mail['auto_completed_reminder_before_html'] : $default_reminder_email_top;
		$auto_completed_reminder_heading = isset($tdws_ord_reminder_mail['auto_completed_reminder_heading']) ? $tdws_ord_reminder_mail['auto_completed_reminder_heading'] : $default_reminder_heading;
		$auto_completed_reminder_subject = isset($tdws_ord_reminder_mail['auto_completed_reminder_subject']) ? $tdws_ord_reminder_mail['auto_completed_reminder_subject'] : $default_reminder_subject;
		$tdws_auto_completed_status_enable = isset($tdws_ord_reminder_mail['auto_completed_status_enable']) ? $tdws_ord_reminder_mail['auto_completed_status_enable'] : '';
		$tdws_auto_completed_status = isset($tdws_ord_reminder_mail['auto_completed_status']) ? $tdws_ord_reminder_mail['auto_completed_status'] : array();
		if( empty($tdws_auto_completed_status) ){
			$tdws_auto_completed_status = array();
		}
		
		$tdws_date_format = (get_option('date_format')) ? get_option('date_format') : 'd/m/Y';			
		$tdws_time_format = (get_option('time_format')) ? get_option('time_format') : 'H:i:s';		

		$tdws_auto_completed_reminder_mail_set_cron = get_option( 'tdws_auto_completed_reminder_mail_set_cron' );
		$tdws_auto_completed_reminder_finish_cron = get_option( 'tdws_auto_completed_reminder_finish_cron' );

		?>
		<div class="wrap tdws-form-wrap">
			<h1><?php esc_html_e( 'TDWS Order Tracking System', 'tdws-order-tracking-system' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'tdws-order-tracking-setting' ); ?>
				<?php do_settings_sections( 'tdws-order-tracking-setting' ); ?>
				<div class="tdws-form-section">
					<table class="tdws-form-table" width="100%" border="1" cellpadding="10" cellspacing="10">
						<tbody>						
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Set Default Order Tag', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Set Default Order Tag', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[set_default_order_tag]" value="<?php echo esc_attr( $set_default_order_tag ); ?>" /></td>
							</tr>						
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Add Order Tags', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Add Order Tags', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[add_order_tags]" value="<?php echo esc_attr( $add_order_tags ); ?>" /></td>
							</tr>
							
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Order Tag Colours', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'Please add colour which you have enter tag also show default #000', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Order Tag Colours', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[order_tag_colour]" value="<?php echo esc_attr( $order_tag_colour ); ?>" /></td>
							</tr>	
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Tag Text Colour', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'Please add colour which you have enter tag text show default #fff', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Tag Text Colour', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_opt[tag_text_colour]" value="<?php echo esc_attr( $tag_text_colour ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Order Tracking Subject', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass order id in subject using [order_id]' ); ?> )</h6>
								</th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Order Tracking Subject', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_mail[subject]" value="<?php echo esc_attr( $tdws_track_subject ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Order Tracking Email Heading', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass order id in email heading using [order_id]' ); ?> )</h6>
								</th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Order Tracking Email Heading', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_mail[email_heading]" value="<?php echo esc_attr( $tdws_track_email_heading ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Order Tracking Email Before Items HTML', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass in your body to show [order_id], [first_name], [last_name], [email]', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<?php 																
									wp_editor( $tdws_track_email_top, 'tdws_ord_track_mail_email_top', array( 'media_buttons' => false, 'textarea_name' => 'tdws_ord_track_mail[email_top_html]', 'wpautop' => false ) );
									?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Order Tracking Email After Items HTML', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass in your body to show [order_id], [first_name], [last_name], [email]', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<?php 																
									wp_editor( $tdws_track_email_bottom, 'tdws_ord_track_mail_email_bottom', array( 'media_buttons' => false, 'textarea_name' => 'tdws_ord_track_mail[email_bottom_html]', 'wpautop' => false ) );
									?>
								</td>
							</tr>	
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Enable Auto Completed Order Reminder System', 'tdws-order-tracking-system' ); ?>
								</th>
								<td>
									<label class="tdws-main-label">
										<input type="checkbox" name="tdws_ord_reminder_mail[auto_completed_status_enable]" <?php checked( $tdws_auto_completed_status_enable, 'Yes' ); ?> class="tdws_auto_completed_status_enable" value="Yes" />
										<span class="tdws-slider tdws-round"></span>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Order Auto Completed Which Status', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'Here you can select order status which you want to auto completed via reminder', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<select name="tdws_ord_reminder_mail[auto_completed_status][]" class="tdws_auto_completed_status" multiple data-placeholder="<?php esc_html_e( 'Please Select Order Status which Auto Completed', 'tdws-order-tracking-system' ); ?>">
										<option value=""><?php esc_html_e( 'Select Status', 'tdws-order-tracking-system' ); ?></option>
										<?php 
										$order_statuses = wc_get_order_statuses();
										foreach ( $order_statuses as $status_key => $status_label ) {
											$selected_string = '';
											if( is_array($tdws_auto_completed_status) && count($tdws_auto_completed_status) > 0 && in_array( $status_key, $tdws_auto_completed_status ) ){
												$selected_string = 'selected="selected"';
											}
											?>
											<option value="<?php echo esc_attr( $status_key ); ?>" <?php echo esc_attr( $selected_string ); ?>><?php esc_html_e( $status_label  ); ?></option>
											<?php
										}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Auto Completed Order Reminder Day', 'tdws-order-tracking-system' ); ?>
								</th>
								<td><input type="number" placeholder="<?php esc_html_e( 'Please Enter Auto Completed Order Reminder Day', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_reminder_mail[auto_completed_reminder_day]" value="<?php echo esc_attr( $auto_completed_reminder_day ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Auto Completed Order Finish Day', 'tdws-order-tracking-system' ); ?>
								</th>
								<td><input type="number" placeholder="<?php esc_html_e( 'Please Enter Auto Completed Order Finish Day', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_reminder_mail[auto_completed_finish_day]" value="<?php echo esc_attr( $auto_completed_finish_day ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Auto Completed Order Reminder Subject', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass order id in email heading using [order_id]' ); ?> )</h6>
								</th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Auto Completed Order Reminder Subject', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_reminder_mail[auto_completed_reminder_subject]" value="<?php echo esc_attr( $auto_completed_reminder_subject ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Auto Completed Order Reminder Email Heading', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass order id in email heading using [order_id]' ); ?> )</h6>
								</th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Auto Completed Order Reminder Email Heading', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_reminder_mail[auto_completed_reminder_heading]" value="<?php echo esc_attr( $auto_completed_reminder_heading ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Auto Completed Order Reminder Before Items HTML', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass in your body to show [order_id], [first_name], [last_name], [email]', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<?php 																
									wp_editor( $auto_completed_reminder_before_html, 'auto_completed_reminder_before_html', array( 'media_buttons' => false, 'textarea_name' => 'tdws_ord_reminder_mail[auto_completed_reminder_before_html]', 'wpautop' => false ) );
									?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Auto Completed Order Reminder After Items HTML', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'You can pass in your body to show [order_id], [first_name], [last_name], [email]', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<?php 																
									wp_editor( $auto_completed_reminder_after_html, 'auto_completed_reminder_after_html', array( 'media_buttons' => false, 'textarea_name' => 'tdws_ord_reminder_mail[auto_completed_reminder_after_html]', 'wpautop' => false ) );
									?>
								</td>
							</tr>
							<tr valign="top" class="tdws-subject-tr" >
								<th scope="row">
									<?php esc_html_e( 'Use This Cron Auto Completed Reminder Mail For Order Set Daily Twice', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'Please Set This Cron Auto Completed Reminder Mail For Order Set Daily Twice', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<input type="text" class="tdws-full-width" readonly value="<?php echo site_url('wp-admin/admin-ajax.php?action=tdws_auto_completed_reminder_set_mail_cron'); ?>" />
									<?php 
									if( $tdws_auto_completed_reminder_mail_set_cron ){
										?>
										<h3 class="tdws-cron-info"><?php esc_html_e( 'Last Cron Run Date & Time :', 'tdws-order-tracking-system' ); ?> <?php echo esc_html( date( $tdws_date_format.' '.$tdws_time_format, strtotime( $tdws_auto_completed_reminder_mail_set_cron ) ) ); ?></h3>

										<?php	
									}
									?>
								</td>
							</tr>	
							<tr valign="top" class="tdws-subject-tr" >
								<th scope="row">
									<?php esc_html_e( 'Use This Cron Auto Completed Reminder Finish Order Set Daily Twice', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'Please Set This Cron Auto Completed Reminder Finish Order Set Daily Twice', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<input type="text" class="tdws-full-width" readonly value="<?php echo site_url('wp-admin/admin-ajax.php?action=tdws_auto_completed_reminder_finish_cron'); ?>" />
									<?php 
									if( $tdws_auto_completed_reminder_finish_cron ){
										?>
										<h3 class="tdws-cron-info"><?php esc_html_e( 'Last Cron Run Date & Time :', 'tdws-order-tracking-system' ); ?> <?php echo esc_html( date( $tdws_date_format.' '.$tdws_time_format, strtotime( $tdws_auto_completed_reminder_finish_cron ) ) ); ?></h3>
										
										<?php	
									}
									?>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="2">
									<?php wp_nonce_field( $this->plugin_name.'-generalData', 'tdwsformType' ); ?>
									<?php submit_button(); ?>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</form>
		</div>
		<?php 
	}	

	/**
	 * Make 17Track API setting page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_17track_api_setting_page() {
		$tdws_17track_opt = get_option( 'tdws_17track_opt' );
		$tdws_tracking_cron_batch_date = get_option( 'tdws_tracking_cron_batch_date' );
		$tdws_tracking_cron_batch_read_date = get_option( 'tdws_tracking_cron_batch_read_date' );
		$change_key = isset($tdws_17track_opt['change_key']) ? $tdws_17track_opt['change_key'] : '';
		$order_id_prefix = isset($tdws_17track_opt['order_id_prefix']) ? $tdws_17track_opt['order_id_prefix'] : '';
		$mail_tracking_status = isset($tdws_17track_opt['mail_tracking_status']) ? $tdws_17track_opt['mail_tracking_status'] : array();
		$tdws_17track_mail_status = tdws_17track_mail_tracking_status();	
		$tdws_date_format = (get_option('date_format')) ? get_option('date_format') : 'd/m/Y';			
		$tdws_time_format = (get_option('time_format')) ? get_option('time_format') : 'H:i:s';			
		?>

		<div class="wrap tdws-form-wrap">
			<h1><?php esc_html_e( '17TRACK API Setting', 'tdws-order-tracking-system' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'tdws-order-tracking-setting' ); ?>
				<?php do_settings_sections( 'tdws-order-tracking-setting' ); ?>
				<div class="tdws-form-section">
					<table class="tdws-form-table" width="100%" border="1" cellpadding="10" cellspacing="10">
						<tbody>						
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Change Key', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Change Key', 'tdws-order-tracking-system' ); ?>" name="tdws_17track_opt[change_key]" value="<?php echo esc_attr( $change_key ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Order ID Prefix', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Order ID Prefix', 'tdws-order-tracking-system' ); ?>" name="tdws_17track_opt[order_id_prefix]" value="<?php echo esc_attr( $order_id_prefix ); ?>" /></td>
							</tr>	
							<tr valign="top">
								<th scope="row"><?php esc_html_e( '17Track Tracking Status', 'tdws-order-tracking-system' ); ?></th>
								<td>
									<?php 
									if( $tdws_17track_mail_status ){
										foreach ( $tdws_17track_mail_status as $t_key => $t_value ) {
											?>
											<label class="tdws-checkbox-label">
												<span class="tdws-checkbox-box">
													<input type="checkbox" name="tdws_17track_opt[mail_tracking_status][]" <?php checked( in_array($t_key, $mail_tracking_status), 1 ); ?> value="<?php echo esc_attr( $t_key ); ?>" />
													<span class="tdws-checkbox-info"><?php echo $t_value; ?></span>
													<span class="tdws-checkbox-mark"></span>
												</span>
												<span class="tdws-mail-button <?php echo (in_array($t_key, $mail_tracking_status)) ? "tdws-active" : ""; ?>">
													<button type="button" class="tdws-set-mail-body tdws-loader-button button button" data-key="<?php echo esc_attr( $t_key ); ?>"><span class="tdws-button-label"><?php echo _e( 'Set Mail Body', 'tdws-order-tracking-system' ); ?></span><span class="tdws-loader"><img src="<?php echo esc_url( plugin_dir_url( __DIR__ ).'/admin/images/loader.gif' ); ?>"/></span></button>
												</span>
											</label>
											<?php
										}
									}
									?>
								</td>
							</tr>	
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Re-Tracking Mail Setting', 'tdws-order-tracking-system' ); ?></th>
								<td>
									<label class="tdws-checkbox-label">									
										<span class="tdws-mail-button tdws-active">
											<button type="button" class="tdws-set-mail-body tdws-loader-button button button" data-key="re_tracking"><span class="tdws-button-label"><?php echo _e( 'Set Mail Body', 'tdws-order-tracking-system' ); ?></span><span class="tdws-loader"><img src="<?php echo esc_url( plugin_dir_url( __DIR__ ).'/admin/images/loader.gif' ); ?>"/></span></button>
										</span>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Stop Tracking Mail Setting', 'tdws-order-tracking-system' ); ?></th>
								<td>
									<label class="tdws-checkbox-label">									
										<span class="tdws-mail-button tdws-active">
											<button type="button" class="tdws-set-mail-body tdws-loader-button button button" data-key="stop_tracking"><span class="tdws-button-label"><?php echo _e( 'Set Mail Body', 'tdws-order-tracking-system' ); ?></span><span class="tdws-loader"><img src="<?php echo esc_url( plugin_dir_url( __DIR__ ).'/admin/images/loader.gif' ); ?>"/></span></button>
										</span>
									</label>
								</td>
							</tr>	
							<tr valign="top" class="tdws-subject-tr" >
								<th scope="row">
									<?php esc_html_e( 'Use This Cron Tracking Notification Batch ( Every 30 Min )', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'Please Set This Cron To Your Server For Every 30 Minute Update Tracking Notification Batch', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<input type="text" class="tdws-full-width" readonly value="<?php echo site_url('wp-admin/admin-ajax.php?action=tdws_push_17track_api_cron'); ?>" />
									<?php 
									if( $tdws_tracking_cron_batch_date ){
										?>
										<h3 class="tdws-cron-info"><?php esc_html_e( 'Last Cron Run Date & Time :', 'tdws-order-tracking-system' ); ?> <?php echo esc_html( date( $tdws_date_format.' '.$tdws_time_format, strtotime( $tdws_tracking_cron_batch_date ) ) ); ?></h3>

										<?php	
									}
									?>
								</td>
							</tr>	
							<tr valign="top" class="tdws-subject-tr" >
								<th scope="row">
									<?php esc_html_e( 'Use This Cron Tracking Notification ( Every 3 Min )', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'Please Set This Cron To Your Server For Every 3 Minute Update Tracking Notification', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<input type="text" class="tdws-full-width" readonly value="<?php echo site_url('wp-admin/admin-ajax.php?action=tdws_push_17track_api_single_cron'); ?>" />
									<?php 
									if( $tdws_tracking_cron_batch_read_date ){
										?>
										<h3 class="tdws-cron-info"><?php esc_html_e( 'Last Cron Run Date & Time :', 'tdws-order-tracking-system' ); ?> <?php echo esc_html( date( $tdws_date_format.' '.$tdws_time_format, strtotime( $tdws_tracking_cron_batch_read_date ) ) ); ?></h3>
										
										<?php	
									}
									?>
								</td>
							</tr>
							<tr valign="top" class="tdws-subject-tr" >
								<th scope="row">
									<?php esc_html_e( 'Sync All TDWS Tables', 'tdws-order-tracking-system' ); ?>
									<h6>( <?php esc_html_e( 'When update a plugin please click here once', 'tdws-order-tracking-system' ); ?> )</h6>
								</th>
								<td>
									<a href="<?php echo site_url('wp-admin/admin-ajax.php?action=tdws_sync_tdws_tables'); ?>" target="_blank" class="tdws-sync-table-link"><button type="button" class="button"><span class="tdws-button-label"><?php echo _e( 'Sync TDWS Tables', 'tdws-order-tracking-system' ); ?></span></button></a>								
								</td>
							</tr>		
						</tbody>
						<tfoot>
							<tr>
								<td colspan="2">
									<?php wp_nonce_field( $this->plugin_name.'-apiData', 'tdwsformType' ); ?>
									<?php submit_button(); ?>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>			
			</form>

			<div id="tdws-tracking-status-popup" class="tracking-mail-status-popup tdws-popup">

				<div class="tdws-popup-wrapper">
					<button title="Close (Esc)" type="button" class="tdws-close">×</button>
					<form method="post" class="tdws-tracking-status-form">
						<div class="tdws-form-section">
							<table class="tdws-form-table" width="100%" border="1" cellpadding="10" cellspacing="10">
								<tbody>						
									<tr valign="top" class="tdws-subject-tr" >
										<th scope="row">
											<?php esc_html_e( 'Subject', 'tdws-order-tracking-system' ); ?>
											<h6>( <?php esc_html_e( 'You can pass order id in subject using [order_id]' ); ?> )</h6>
										</th>
										<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Subject', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_mail_body[subject]" value="" /></td>
									</tr>
									<tr valign="top" class="tdws-email-heading-tr">
										<th scope="row">
											<?php esc_html_e( 'Email Heading', 'tdws-order-tracking-system' ); ?>
											<h6>( <?php esc_html_e( 'You can pass order id in email heading using [order_id]' ); ?> )</h6>
										</th>
										<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Email Heading', 'tdws-order-tracking-system' ); ?>" name="tdws_ord_track_mail_body[email_heading]" value="" /></td>
									</tr>
									<tr valign="top" class="tdws-email-item-body">
										<th scope="row">
											<?php esc_html_e( 'Email Body Items HTML', 'tdws-order-tracking-system' ); ?>
											<h6>( <?php esc_html_e( 'You can pass in your body to show [order_id], [first_name], [last_name], [email] [tdws_tracking_table]' ); ?> )</h6>
										</th>
										<td>
											<?php 																
											wp_editor( "", 'tdws_ord_track_mail_email_body', array( 'media_buttons' => false, 'textarea_name' => 'tdws_ord_track_mail_body[email_body]', 'textarea_rows' => 20, 'wpautop' => false ) );
											?>
										</td>
									</tr>									
								</tbody>
								<tfoot>
									<tr>
										<td colspan="2">
											<?php wp_nonce_field( $this->plugin_name.'-MailBodySave', 'formType' ); ?>
											<input type="hidden" name="option_type" value="">
											<input type="hidden" name="action" value="<?php echo esc_attr( $this->plugin_name ); ?>_tracking_mail_body_save">
											<button type="submit" class="tdws-loader-button button button-primary"><span class="tdws-button-label"><?php echo _e( 'Save Changes', 'tdws-order-tracking-system' ); ?></span><span class="tdws-loader"><img src="<?php echo esc_url( plugin_dir_url( __DIR__ ).'/admin/images/loader.gif' ); ?>"/></span></button>
											<p class="tdws-success-msg"><?php echo _e( 'Mail body data saved successfully...', 'tdws-order-tracking-system' ); ?></p>
											<p class="tdws-error-msg"><?php echo _e( 'Something Went Wrong, Try Again...', 'tdws-order-tracking-system' ); ?></p>
										</td>
									</tr>
								</tfoot>
							</table>
						</div>	
					</form>		
				</div>
				
			</div>
		</div>
		<?php 
	}

	/**
	 * Make TDWS COUPON setting page.
	 *
	 * @since    1.4.0
	 */
	public function tdws_coupon_setting_page() {
		$tdws_coupon_settings_opt = get_option( 'tdws_coupon_settings_opt' );
		$default_thank_you_page_coupon_limit = apply_filters( 'tdws_thank_you_page_coupon_limit', 5 );
		$default_thank_you_page_coupon_list_order = apply_filters( 'tdws_thank_you_page_coupon_list_order', 'ASC' );
		$default_thank_you_page_coupon_list_order_by = apply_filters( 'tdws_thank_you_page_coupon_list_order_by', 'ID' );
		$default_my_account_page_coupon_per_page = apply_filters( 'tdws_my_account_page_coupon_per_page', 5 );
		$default_my_account_page_coupon_list_order = apply_filters( 'tdws_my_account_page_coupon_list_order', 'ASC' );
		$default_my_account_page_coupon_list_order_by = apply_filters( 'tdws_my_account_page_coupon_list_order_by', 'ID' );
		$default_my_account_page_no_coupon_data_message = apply_filters( 'tdws_my_account_page_no_coupon_data_message', 'No Deals Available' );
		$default_my_account_page_coupon_tab_title = apply_filters( 'tdws_my_account_page_coupon_tab_title', 'My Rewards' );
		$default_my_account_page_coupon_filter_label = apply_filters( 'tdws_my_account_page_coupon_filter_label', 'Your Interests:' );
		$default_my_account_page_coupon_filter_placeholder = apply_filters( 'tdws_my_account_page_coupon_filter_placeholder', 'Select Interests' );
		$default_my_account_page_coupon_tab_url = apply_filters( 'tdws_my_account_page_coupon_tab_title', 'tdws-my-rewards-list' );
		
		$thank_you_page_coupon_limit = (isset($tdws_coupon_settings_opt['thank_you_page_coupon_limit']) && !empty($tdws_coupon_settings_opt['thank_you_page_coupon_limit'])) ? $tdws_coupon_settings_opt['thank_you_page_coupon_limit'] : $default_thank_you_page_coupon_limit;	
		$thank_you_page_coupon_list_order = (isset($tdws_coupon_settings_opt['thank_you_page_coupon_list_order']) && !empty($tdws_coupon_settings_opt['thank_you_page_coupon_list_order'])) ? $tdws_coupon_settings_opt['thank_you_page_coupon_list_order'] : $default_thank_you_page_coupon_list_order;	
		$thank_you_page_coupon_list_order_by = (isset($tdws_coupon_settings_opt['thank_you_page_coupon_list_order_by']) && !empty($tdws_coupon_settings_opt['thank_you_page_coupon_list_order_by'])) ? $tdws_coupon_settings_opt['thank_you_page_coupon_list_order_by'] : $default_thank_you_page_coupon_list_order_by;
		$my_account_page_coupon_per_page = (isset($tdws_coupon_settings_opt['my_account_page_coupon_per_page']) && !empty($tdws_coupon_settings_opt['my_account_page_coupon_per_page'])) ? $tdws_coupon_settings_opt['my_account_page_coupon_per_page'] : $default_my_account_page_coupon_per_page;	
		$my_account_page_coupon_tab_title = (isset($tdws_coupon_settings_opt['my_account_page_coupon_tab_title']) && !empty($tdws_coupon_settings_opt['my_account_page_coupon_tab_title'])) ? $tdws_coupon_settings_opt['my_account_page_coupon_tab_title'] : $default_my_account_page_coupon_tab_title;	
		$my_account_page_coupon_tab_url = (isset($tdws_coupon_settings_opt['my_account_page_coupon_tab_url']) && !empty($tdws_coupon_settings_opt['my_account_page_coupon_tab_url'])) ? $tdws_coupon_settings_opt['my_account_page_coupon_tab_url'] : $default_my_account_page_coupon_tab_url;	

		$my_account_page_coupon_filter_label = (isset($tdws_coupon_settings_opt['my_account_page_coupon_filter_label']) && !empty($tdws_coupon_settings_opt['my_account_page_coupon_filter_label'])) ? $tdws_coupon_settings_opt['my_account_page_coupon_filter_label'] : $default_my_account_page_coupon_filter_label;	

		$my_account_page_coupon_filter_placeholder = (isset($tdws_coupon_settings_opt['my_account_page_coupon_filter_placeholder']) && !empty($tdws_coupon_settings_opt['my_account_page_coupon_filter_placeholder'])) ? $tdws_coupon_settings_opt['my_account_page_coupon_filter_placeholder'] : $default_my_account_page_coupon_filter_placeholder;	
		
		$my_account_page_coupon_list_order = (isset($tdws_coupon_settings_opt['my_account_page_coupon_list_order']) && !empty($tdws_coupon_settings_opt['my_account_page_coupon_list_order'])) ? $tdws_coupon_settings_opt['my_account_page_coupon_list_order'] : $default_my_account_page_coupon_list_order;	
		$my_account_page_coupon_list_order_by = (isset($tdws_coupon_settings_opt['my_account_page_coupon_list_order_by']) && !empty($tdws_coupon_settings_opt['my_account_page_coupon_list_order_by'])) ? $tdws_coupon_settings_opt['my_account_page_coupon_list_order_by'] : $default_my_account_page_coupon_list_order_by;	
		$my_account_page_no_coupon_data_message = (isset($tdws_coupon_settings_opt['my_account_page_no_coupon_data_message']) && !empty($tdws_coupon_settings_opt['my_account_page_no_coupon_data_message'])) ? $tdws_coupon_settings_opt['my_account_page_no_coupon_data_message'] : $default_my_account_page_no_coupon_data_message;		
		
		?>
		<div class="wrap tdws-form-wrap">
			<h1><?php esc_html_e( 'TDWS Coupon Setting', 'tdws-order-tracking-system' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'tdws-coupon-setting' ); ?>
				<?php do_settings_sections( 'tdws-coupon-setting' ); ?>
				<div class="tdws-form-section">
					<table class="tdws-form-table" width="100%" border="1" cellpadding="10" cellspacing="10">
						<tbody>						
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Thank You Page Coupon Limit', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="number" placeholder="<?php esc_html_e( 'Please Thank You Page Coupon Limit', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[thank_you_page_coupon_limit]" value="<?php echo esc_attr( $thank_you_page_coupon_limit ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Thank You Page Coupon List Order', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Page Coupon List Order', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[thank_you_page_coupon_list_order]" value="<?php echo esc_attr( $thank_you_page_coupon_list_order ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Thank You Page Coupon List Order By', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Page Coupon List Order', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[thank_you_page_coupon_list_order_by]" value="<?php echo esc_attr( $thank_you_page_coupon_list_order_by ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page Coupon Tab Title', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Page Coupon Tab Title', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_coupon_tab_title]" value="<?php echo esc_attr( $my_account_page_coupon_tab_title ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page Coupon Tab URL', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Page Coupon Tab URL', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_coupon_tab_url]" value="<?php echo esc_attr( $my_account_page_coupon_tab_url ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page Coupon Per Page', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="number" placeholder="<?php esc_html_e( 'Please My Account Page Coupon Per Page', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_coupon_per_page]" value="<?php echo esc_attr( $my_account_page_coupon_per_page ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page Coupon Filter Label', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please My Account Page Coupon Filter Label', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_coupon_filter_label]" value="<?php echo esc_attr( $my_account_page_coupon_filter_label ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page Coupon Filter Placeholder', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please My Account Page Coupon Filter Placeholder', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_coupon_filter_placeholder]" value="<?php echo esc_attr( $my_account_page_coupon_filter_placeholder ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page Coupon List Order', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Page Coupon List Order', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_coupon_list_order]" value="<?php echo esc_attr( $my_account_page_coupon_list_order ); ?>" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page Coupon List Order By', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Page Coupon List Order', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_coupon_list_order_by]" value="<?php echo esc_attr( $my_account_page_coupon_list_order_by ); ?>" /></td>
							</tr>	
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'My Account Page No Coupon Data Message', 'tdws-order-tracking-system' ); ?></th>
								<td><input type="text" placeholder="<?php esc_html_e( 'Please Enter Page No Coupon Data Message', 'tdws-order-tracking-system' ); ?>" name="tdws_coupon_settings_opt[my_account_page_no_coupon_data_message]" value="<?php echo esc_attr( $my_account_page_no_coupon_data_message ); ?>" /></td>
							</tr>	
						</tbody>
						<tfoot>
							<tr>
								<td colspan="2">
									<?php wp_nonce_field( $this->plugin_name.'-tdws-coupon', 'tdwsformType' ); ?>
									<?php submit_button(); ?>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>			
			</form>

		</div>
		<?php 
	}

	/**
	 * Show 17Track API Report page data with chart.
	 *
	 * @since    1.1.0
	 */
	public function tdws_17track_report_setting_page() {
		?>
		<div class="wrap tdws-form-wrap">
			<h1><?php esc_html_e( '17TRACK API Report', 'tdws-order-tracking-system' ); ?></h1>

			<?php

			include_once dirname( __FILE__ ) . '/includes/class-tdws-tracking-reports.php';

			$sales_by_date                 = new Tdws_Order_Tracking_Report();
			$sales_by_date->start_date     = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );
			$sales_by_date->end_date       = strtotime( gmdate( 'Y-m-d', current_time( 'timestamp' ) ) );
			$sales_by_date->chart_groupby  = 'day';
			$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

			$sales_by_date->output_report();
			?>
		</div>
		<?php 
	}	

	/**
	 * Save tracking mail status body data.
	 *
	 * @since    1.1.0
	 */
	public function tdws_plugin_tracking_mail_body_save(){
		$data_arr = $value_arr = array();					
		check_ajax_referer( 'tdws_form_save', 'ajax_nonce' );					
		/*
		* In $_POST['formdata'] We have store data as array format
		* We also sanitize data using tdws_array_data_sanitize function
		*/
		$formdata = isset($_POST['formdata']) ? $_POST['formdata'] : '';		
		parse_str( $formdata, $data_arr );	

		$data_arr = array_map( 'tdws_array_data_sanitize', $data_arr );
		
		if ( isset( $data_arr['formType'] ) && wp_verify_nonce( $data_arr['formType'], $this->plugin_name.'-MailBodySave' ) ) {			
			$option_name = 'tdws_'.$data_arr['option_type'].'_mail_obj';
			update_option( $option_name, $data_arr['tdws_ord_track_mail_body'] );			
		}		
		wp_send_json( array( 'type' => 'success' ) );
	}

	/**
	 * Get tracking mail status body data.
	 *
	 * @since    1.1.0
	 */
	public function tdws_tracking_get_mail_body_data(){
		$data_arr = $value_arr = array();		
		$success = false;		
		check_ajax_referer( 'tdws_form_save', 'ajax_nonce' );					
		$option_key = isset($_POST['option_key']) ? sanitize_text_field( $_POST['option_key'] ) : "";
		$default_mailObj = array(
			'subject' => '',
			'email_heading' => '',
			'email_body' => ''
		);
		$tdws_17track_mail_info = tdws_17track_mail_tracking_status_mail_info();
		if( isset($tdws_17track_mail_info[$option_key]) ){
			$default_mailObj = $tdws_17track_mail_info[$option_key];
		}
		if( $option_key ){
			$option_name = 'tdws_'.$_POST['option_key'].'_mail_obj';
			$optionData = get_option( $option_name );	
			if( $optionData ){
				$default_mailObj = $optionData;
			}
		}		
		wp_send_json( array( 'type' => 'success', 'data' => $default_mailObj ) );
	}


	/**
	 * Add custom field on order edit page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_add_order_tag_field_order_edit( $order ){		
		?>
		<p class="form-field form-field-wide tdws-order-tag">
			<label for="order_status">
				<?php
				esc_html_e( 'Order Tag:', 'tdws-order-tracking-system' );				
				?>
			</label>
			<select id="tdws_order_tracking_tag" name="tdws_order_tracking_tag" class="wc-enhanced-select">
				<option value=""><?php esc_html_e( 'Select Order Tag:', 'tdws-order-tracking-system' ); ?></option>
				<?php
				$tages = tdws_get_order_tages( 1 );
				$get_tag = get_post_meta( $order->get_id(), 'tdws_order_tracking_tag', true );
				foreach ( $tages as $tag_name ) {
					echo '<option value="' . esc_attr( $tag_name ) . '" ' . selected( $tag_name, $get_tag, false ) . '>' . esc_html( $tag_name ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * Update Order Tag When Order Status Changed.
	 *
	 * @since    1.0.0
	 */
	public function tdws_update_order_tag_with_fail_status( $order_id, $that, $status_transition ){
		
		update_post_meta( $order_id, 'tdws_order_tracking_tag', '' );
		twds_update_order_meta( $order_id, 'tdws_order_tracking_tag', '' );

	}

	/**
	 * Update Order Tag When Order Status  Changed when fail to processing or on hold.
	 *
	 * @since    1.0.0
	 */
	public function tdws_update_order_tag_with_fail_to_process_status( $order_id, $that ){
		
		$tdws_ord_track_opt = get_option( 'tdws_ord_track_opt' );				
		$set_default_order_tag = (isset($tdws_ord_track_opt['set_default_order_tag']) && !empty($tdws_ord_track_opt['set_default_order_tag'])) ? $tdws_ord_track_opt['set_default_order_tag'] : 'New';
		update_post_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $set_default_order_tag ) );
		twds_update_order_meta( $order_id, 'tdws_order_tracking_tag', sanitize_text_field( $set_default_order_tag ) );

	}

	/**
	 * Update Order Tag When Order Status Changed processing or on hold to fail.
	 *
	 * @since    1.0.0
	 */
	public function tdws_update_order_tag_with_process_to_fail_status( $order_id, $that ){
		
		update_post_meta( $order_id, 'tdws_order_tracking_tag', '' );
		twds_update_order_meta( $order_id, 'tdws_order_tracking_tag', '' );

	}

	/**
	 * Save custom field on order edit page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_save_order_tag_field_order_edit( $order_id, $order ){
		
		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $order_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $order_id ) ) {
			return;
		}

		if( isset($_POST['tdws_order_tracking_tag']) ){
			$tdws_order_tracking_tag = sanitize_text_field( $_POST['tdws_order_tracking_tag'] );
			update_post_meta( $order_id, 'tdws_order_tracking_tag', $tdws_order_tracking_tag );
			twds_update_order_meta( $order_id, 'tdws_order_tracking_tag', $tdws_order_tracking_tag );
		}

		if( isset($_POST['tdws_tracking_shipped_id']) ){
			$tdws_tracking_shipped = isset($_POST['tdws_tracking_shipped']) ? $_POST['tdws_tracking_shipped'] : array();		
			$tdws_tracking_shipped = array_map( 'tdws_array_data_sanitize', $tdws_tracking_shipped );

			$tdws_tracking_shipped_ids = isset($_POST['tdws_tracking_shipped_id']) ? $_POST['tdws_tracking_shipped_id'] : array();		
			$tdws_tracking_shipped_ids = array_map( 'tdws_array_data_sanitize', $tdws_tracking_shipped_ids );

			if( $tdws_tracking_shipped_ids ){
				foreach( $tdws_tracking_shipped_ids as $tdws_track_shipped_id ){
					$tdws_ship_check = 'no';
					if( isset($tdws_tracking_shipped[$tdws_track_shipped_id]) && !empty($tdws_tracking_shipped[$tdws_track_shipped_id]) ){
						$tdws_ship_check = 'yes';
					}
					wc_update_order_item_meta( $tdws_track_shipped_id, '_tdws_tracking_shipped', $tdws_ship_check );
				}
			}			

		}

	}

	/**
	 * Delete hook all functions
	 *
	 * @since    1.0.0
	 */
	public function tdws_delete_hook_call(){	
		
		// Callback delete order tag post meta
		add_action( 'woocommerce_delete_order', array( $this, 'tdws_delete_order_tag_meta' ), 99, 1 );		    

	}

	/**
	 * Delete order tag meta
	 *
	 * @since    1.0.0
	 */
	public function tdws_delete_order_tag_meta( $post_id ){
		delete_post_meta( $post_id, 'tdws_order_tracking_tag' );
		twds_delete_order_meta( $post_id, 'tdws_order_tracking_tag' );
	}

	/**
	 * Add custom column on order list page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_add_order_tag_column_order_list( $columns ){	
		
		$status_position = array_search( 'order_status', array_keys( $columns ) );
		$columns = array_slice( $columns, 0, $status_position + 1, true ) +
		array( 'order_tag' => __( 'Order Tag', 'tdws-order-tracking-system' ) ) +
		array_slice( $columns, $status_position + 1, null, true );
		
		return $columns;
	}

	/**
	 * Show custom column value on order list page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_show_order_tag_column_order_list( $column_name, $post ){	
		if( $column_name == 'order_tag' ){			
			$post_id = $post;
			if( is_object( $post ) ){				
				$post_id = $post->ID;
			}
			$this->render_order_tag_column( $post_id );
		}		
	}

	/**
	 * Rendor custom column value on order list page.
	 *
	 * @since    1.0.0
	 */
	public function render_order_tag_column( $post_id ){		
		$order_tracking_tag = get_post_meta( $post_id , 'tdws_order_tracking_tag' , true );
		if( $order_tracking_tag ){

			$tag_setting = tdws_get_order_tage_color_settings();
			$background_arr = $tag_setting['background'];
			$bg_color = tdws_get_colo_by_tag_name( $background_arr, $order_tracking_tag );
			$text_color = $tag_setting['text_color'];
			$style_str = 'background:'.$bg_color.';color:'.$text_color.';';
			echo '<span class="tdws_tag_badge" style='.esc_attr( $style_str ).'>'.esc_html( $order_tracking_tag ).'</span>';
		} 
	}

	/**
	 * Show all tag filter list on order list page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_add_order_tag_filter_list( $which ) {
		global $typenow;
		$this->tdws_custom_action_order_list_table_extra_tablenav( $typenow, $which );
		$this->tdws_custom_filter_order_list_table_extra_tablenav( $typenow, $which );
	}

	/**
	 * Add Custom Action For all tags on order list page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_custom_action_order_list_table_extra_tablenav( $typenow, $which ){	
		if ( 'shop_order' === $typenow && 'top' === $which ) {			
			$tage_view_list = tdws_get_order_tages( 1 );
			if( empty( $tage_view_list ) ){
				$tage_view_list = array();
			}			
			
			echo '<div class="tdws-tag-list-action">';

			echo '<label for="bulk-tag-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' .
			/* translators: Hidden accessibility text. */
			__( 'Select bulk tag action' ) .
			'</label>';
			echo '<select name="tdws_tag_action" id="bulk-tag-action-selector-' . esc_attr( $which ) . "\">\n";
			echo '<option value="-1">' . __( 'Bulk tag actions' ) . "</option>\n";
			if( $tage_view_list ){
				foreach ( $tage_view_list as $key => $value ) {
					if ( is_array( $value ) ) {
						echo "\t" . '<optgroup label="' . esc_attr( $value ) . '">' . "\n";

						foreach ( $value as $name => $title ) {
							$class = ( 'edit' === $name ) ? ' class="hide-if-no-js"' : '';

							echo "\t\t" . '<option value="' . esc_attr( $name ) . '"' . $class . '>' . $title . "</option>\n";
						}
						echo "\t" . "</optgroup>\n";
					} else {
						$class = ( 'edit' === $key ) ? ' class="hide-if-no-js"' : '';

						echo "\t" . '<option value="' . esc_attr( $value ) . '"' . $class . '>' . $value . "</option>\n";
					}
				}
			}
			echo "</select>\n";

			wp_nonce_field( 'tdwsBulkTagUpdate', 'tdwsBulkTagAction' );

			submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction_tdws_tag" ) );
			echo "\n";

			echo "</div>";	
		}			
	}

	/**
	 * Show all tag filter list on order list page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_custom_filter_order_list_table_extra_tablenav( $typenow, $which ){	
		if ( 'shop_order' === $typenow && 'top' === $which ) {			
			$tage_view_list = tdws_get_order_tages( 1 );
			if( empty( $tage_view_list ) ){
				$tage_view_list = array();
			}
			$tag_wise_count = tdws_get_all_order_tag_meta_array();
			array_unshift( $tage_view_list, "All" );
			$current_tag = isset( $_GET['tag_view'] ) ? sanitize_text_field( $_GET['tag_view'] ) : 'All';
			echo '<div class="tdws-tag-list">';
			echo '<ul class="tdws-tag-ul">';
			if( $tage_view_list ){
				foreach ( $tage_view_list as $t_key => $t_value ) {
					$class = ( $current_tag == $t_value ) ? "current" : '';

					$tag_filter_url   = ( 'All' === $t_value ) ? remove_query_arg( 'tag_view' ) : add_query_arg( 'tag_view', $t_value );
					$tag_filter_url   = remove_query_arg( array( 'paged', 's' ), $tag_filter_url );

					$tag_cnt = isset($tag_wise_count[$t_value]) ? $tag_wise_count[$t_value] : 0;
					if( $tag_cnt > 0 ){
						echo "<li><a href='".esc_url( $tag_filter_url )."' class='".esc_attr( $class )."'>". esc_html( $t_value ) ." <span class='count'>(".esc_html( $tag_cnt ).")</span></a>";						
						echo "</li>";
					}
					
				}
			}			
			echo '</ul>';
			echo '<input type="hidden" name="tag_view" value="'.$current_tag.'">';
			echo "</div>";	
		}			
	}


	/**
	 * Apply tag filter list on order list page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_apply_order_tag_filter_list( $query ){				
		global $pagenow, $typenow;			
		if ( is_admin() && $pagenow == 'edit.php' && $typenow == 'shop_order' ) {			
			$tag_view = isset($_GET['tag_view']) ? sanitize_text_field( $_GET['tag_view'] ) : '';
			if( $tag_view && $tag_view != 'All' ){
				$query->set( 'meta_key', 'tdws_order_tracking_tag' );
				$query->set( 'meta_value', $tag_view );
			}
		}

	}

	/**
	 * Apply tag filter list on order list page.
	 *
	 * @since    1.0.0
	 */
	public function tdws_apply_order_tag_filter_list_by_table( $order_query_args ){		
		$tag_view = isset($_GET['tag_view']) ? sanitize_text_field( $_GET['tag_view'] ) : '';
		if( $tag_view && $tag_view != 'All' ){
			$order_query_args['meta_query'] = array(
				array(
					'key' => 'tdws_order_tracking_tag',
					'value' => $tag_view, 
					'compare' => 'LIKE',
				),
			);
		}	
		
		return $order_query_args;
	}

	/**
	 * Hide Order Item Meta
	 *
	 * @since    1.1.0
	 */
	public function tdws_hidden_order_itemmeta( $order_items  ){		
		$order_items[] = '_tdws_tracking_shipped';		
		return $order_items;
	}


	/**
	 * This hook fire when plugin updated and add a new column tracking_status
	 *
	 * @since    1.1.0
	 */
	public function tdws_after_upgrade_function_load( $upgrader_object, $options ){
		
		$current_plugin_path_name = plugin_basename( __FILE__ );
		if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach($options['plugins'] as $each_plugin) {
				if ( $each_plugin == $current_plugin_path_name ) {

					/* Version 1.1.0 */
					tdws_add_column_tracking_statusDB();

				}
			}
		}

	}

	/**
	 * This hook manually added missing column or table
	 *
	 * @since    1.1.0
	 */
	public function tdws_sync_tdws_tables(){
		
		global $wpdb;		
		$table2_name = $wpdb->base_prefix.'tdws_order_tracking';
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$tdws_table3 = $wpdb->prefix . 'tdws_order_tracking_status';		

		// Prepare the SQL query with placeholders
		$table3_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tdws_table3 ) );   // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder	

		#Check to see if the table exists already, if not, then create it
		if ( $table3_exists != $tdws_table3 ) {

			$tdws_sql_3 = "CREATE TABLE $tdws_table3 (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`order_id` int(11) DEFAULT 0,
				`tracking_id` int(11) DEFAULT 0,
				`not_found` text DEFAULT NULL,				
				`shipped` text DEFAULT NULL,				
				`info_received` text DEFAULT NULL,				
				`in_transit` text DEFAULT NULL,
				`expired` text DEFAULT NULL,
				`available_for_pickup` text DEFAULT NULL,
				`out_for_delivery` text DEFAULT NULL,				
				`delivery_failure` text DEFAULT NULL,
				`delivered` text DEFAULT NULL,
				`exception` text DEFAULT NULL,
				`status` int(11) DEFAULT 0,
				`create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL,
				`update_date` datetime DEFAULT CURRENT_TIMESTAMP NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			dbDelta( $tdws_sql_3 );
		}

		$add_extra_columns = array(
			array(
				'column_name' => 'sub_status',
				'table_name' => $table2_name,
				'after_add' => 'status',
				'extra' => 'text DEFAULT NULL',
			),
			array(
				'column_name' => 'tracking_status',
				'table_name' => $table2_name,
				'after_add' => 'sub_status',
				'extra' => 'text DEFAULT NULL',
			),
			array(
				'column_name' => 'carrier_code',
				'table_name' => $table2_name,
				'after_add' => 'carrier_name',
				'extra' => 'text DEFAULT NULL',
			),
			array(
				'column_name' => 'shipped',
				'table_name' => $tdws_table3,
				'after_add' => 'not_found',
				'extra' => 'text DEFAULT NULL',
			)
		);

		if( $add_extra_columns ){
			foreach ( $add_extra_columns as $key => $extra_col_item ) {
				$add_column_SQL = "ALTER TABLE ".$extra_col_item['table_name']." ADD ".$extra_col_item['column_name']." ".$extra_col_item['extra']." AFTER ".$extra_col_item['after_add'].";";			
				maybe_add_column( $extra_col_item['table_name'], $extra_col_item['column_name'], $add_column_SQL );
			}
		}

		wp_die( "sync tables" );

	}

	
}
