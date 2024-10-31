<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://paytomat.com
 * @since      1.0.0
 *
 * @package    Paytomat
 * @subpackage Paytomat/includes
 */

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
 * @package    Paytomat
 * @subpackage Paytomat/includes
 * @author     Paytomat <it.acc@dailyco.in>
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Paytomat {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Paytomat_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $paytomat    The string used to uniquely identify this plugin.
	 */
	protected $paytomat;

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
		if ( defined( 'PAYTOMAT_VERSION' ) ) {
			$this->version = PAYTOMAT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->paytomat = 'paytomat';

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
	 * - Paytomat_Loader. Orchestrates the hooks of the plugin.
	 * - Paytomat_i18n. Defines internationalization functionality.
	 * - Paytomat_Admin. Defines all hooks for the admin area.
	 * - Paytomat_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-paytomat-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-paytomat-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-paytomat-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-paytomat-public.php';

		$this->loader = new Paytomat_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Paytomat_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Paytomat_i18n();
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
		$plugin_admin = new Paytomat_Admin( $this->get_paytomat(), $this->get_version() );
	/**
	 * Add actions 
	 */
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'init_paytomat_getway');
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'close_order_callback');


	/**
	 * Add filters
	 */
		$this->loader->add_filter( 'woocommerce_payment_gateways', $plugin_admin, 'add_paytomat_getway_to_wc' );
		//$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'wpcf_plugin_action_links' ); 
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Paytomat_Public( $this->get_paytomat(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'template_include', $plugin_public, 'get_page_payment_qr');
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
		if (isset($this->file)) {
			register_activation_hook( $this->file, array( $this, 'activate' ) );

			add_filter( 'plugin_action_links', 'wpcf_plugin_action_links', 10, 2 );
			
			function wpcf_plugin_action_links($actions, $plugin_file){
				if( false === strpos( $plugin_file, basename(__FILE__) ) ) {
					return $actions;
				}

				$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytomat' ) . '">Settings</a>';
				array_unshift( $actions, $settings_link ); 
				return $actions; 
			}
		}
	}

	

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    paytomat.
	 */
	public function get_paytomat() {
		return $this->paytomat;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Paytomat_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    paytomat    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
