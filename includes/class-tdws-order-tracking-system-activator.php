<?php

/**
 * Fired during plugin activation
 *
 * @link       https://tdwebservices.com
 * @since      1.0.0
 *
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/includes
 * @author     TD Web Services <info@tdwebservices.com>
 */
class Tdws_Order_Tracking_System_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

		$tdws_table1 = $wpdb->prefix . 'tdws_order_tracking';
		$tdws_table2 = $wpdb->prefix . 'tdws_order_tracking_meta';
		$tdws_table3 = $wpdb->prefix . 'tdws_order_tracking_status';

		$charset_collate = $wpdb->get_charset_collate();

		$tdws_sql_1 = $tdws_sql_2 = $tdws_sql_3 = '';

		// Prepare the SQL query with placeholders
		$table1_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tdws_table1 ) );   // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder	

		#Check to see if the table exists already, if not, then create it
		if ( $table1_exists != $tdws_table1 ) {

			$tdws_sql_1 = "CREATE TABLE $tdws_table1 (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`order_id` int(11) DEFAULT 0,
				`tracking_no` longtext DEFAULT NULL,
				`carrier_name` text DEFAULT NULL,
				`pickup_date` text DEFAULT NULL,
				`carrier_link` longtext DEFAULT NULL,
				`status` text DEFAULT NULL,
				`sub_status` text DEFAULT NULL,
				`tracking_status` int(11) DEFAULT 0,
				`create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL,
				`update_date` datetime DEFAULT CURRENT_TIMESTAMP NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $tdws_sql_1 );
		}		

		// Prepare the SQL query with placeholders
		$table2_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tdws_table2 ) );   // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder	

		#Check to see if the table exists already, if not, then create it
		if ( $table2_exists != $tdws_table2 ) {

			$tdws_sql_2 = "CREATE TABLE $tdws_table2 (
				`meta_id` int(11) NOT NULL AUTO_INCREMENT,
				`order_tracking_id` int(11) DEFAULT 0,
				`meta_key` longtext DEFAULT NULL,
				`meta_value` longtext DEFAULT NULL,
				`create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL,
				`update_date` datetime DEFAULT CURRENT_TIMESTAMP NULL,
				PRIMARY KEY  (meta_id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $tdws_sql_2 );
		}

		// Prepare the SQL query with placeholders
		$table3_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tdws_table3 ) );   // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder	

		#Check to see if the table exists already, if not, then create it
		if ( $table3_exists != $tdws_table3 ) {

			$tdws_sql_3 = "CREATE TABLE $tdws_table3 (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`order_id` int(11) DEFAULT 0,
				`tracking_id` int(11) DEFAULT 0,
				`not_found` text DEFAULT NULL,				
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


			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $tdws_sql_3 );
		}

	}

}
