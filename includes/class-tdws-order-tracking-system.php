<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tdws_Order_Tracking_System
 * @subpackage Tdws_Order_Tracking_System/includes
 * @author     TD Web Services <info@tdwebservices.com>
 */
class Tdws_Order_Tracking_System {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tdws_Order_Tracking_System_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TDWS_ORDER_TRACKING_SYSTEM_VERSION' ) ) {
			$this->version = TDWS_ORDER_TRACKING_SYSTEM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tdws-order-tracking-system';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tdws_Order_Tracking_System_Loader. Orchestrates the hooks of the plugin.
	 * - Tdws_Order_Tracking_System_i18n. Defines internationalization functionality.
	 * - Tdws_Order_Tracking_System_Admin. Defines all hooks for the admin area.
	 * - Tdws_Order_Tracking_System_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tdws-order-tracking-system-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tdws-order-tracking-system-i18n.php';

		/**
		 * This file contains common functions used across all plugins
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/tdws-order-tracking-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tdws-order-tracking-system-admin.php';

		/**
		 * The class responsible for configuring settings for the 17track API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-tdws-17tracking-config.php';

		/**
		 * The class responsible for configuring settings, hooks, and actions for the 17track API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-tdws-17tracking-api.php';

		/**
		 * The class responsible for defining all automation-related cron jobs for order tracking with the 17track API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-tdws-17tracking-automation.php';
		
		/**
		 * The class responsible for defining all actions that occur in the admin order meta box.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-tdws-order-tracking-system-order-metabox.php';		

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tdws-order-tracking-system-public.php';

		$this->loader = new Tdws_Order_Tracking_System_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tdws_Order_Tracking_System_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tdws_Order_Tracking_System_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tdws_Order_Tracking_System_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		new Tdws_Order_Tracking_System_Order_MetaBox( $this->get_plugin_name(), $this->get_version() );
		new Tdws_Order_Tracking_Automation( $this->get_plugin_name(), $this->get_version() );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tdws_Order_Tracking_System_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tdws_Order_Tracking_System_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
