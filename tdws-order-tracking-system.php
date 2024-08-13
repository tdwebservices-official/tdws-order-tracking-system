<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tdwebservices.com
 * @since             1.0.0
 * @package           Tdws_Order_Tracking_System
 *
 * @wordpress-plugin
 * Plugin Name:       TDWS Order Tracking System
 * Plugin URI:        https://tdwebservices.com
 * Description:       Provide your customers with order status updates and shipment tracking details.
 * Requires Plugins: woocommerce
 * Version:           1.1.2
 * Author:            TD Web Services
 * Author URI:        https://tdwebservices.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tdws-order-tracking-system
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TDWS_ORDER_TRACKING_SYSTEM_VERSION', '1.1.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tdws-order-tracking-system-activator.php
 */
function activate_tdws_order_tracking_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tdws-order-tracking-system-activator.php';
	Tdws_Order_Tracking_System_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tdws-order-tracking-system-deactivator.php
 */
function deactivate_tdws_order_tracking_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tdws-order-tracking-system-deactivator.php';
	Tdws_Order_Tracking_System_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tdws_order_tracking_system' );

register_deactivation_hook( __FILE__, 'deactivate_tdws_order_tracking_system' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tdws-order-tracking-system.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tdws_order_tracking_system() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$plugin = new Tdws_Order_Tracking_System();
		$plugin->run();
	} else {
		if( is_admin() ) {
			tdws_woocommerce_activation_notice();
		}
	}
}

/**
 * Show notice message on admin plugin page.
 *
 * Callback function for admin_notices(action).
 *
 * @since  1.0.0
 * @access public
 */
function tdws_woocommerce_activation_notice() {
	?>
	<div class="error">
		<p>
			<?php echo '<strong> TDWS Order Tracking System </strong> requires <a target="_blank" href="https://wordpress.org/plugins/woocommerce/">Woocommerce</a> to be installed & activated!' ; ?>
		</p>
	</div>
	<?php
}

run_tdws_order_tracking_system();