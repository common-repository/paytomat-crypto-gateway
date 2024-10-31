<?php

/**
 * 
 *
 * This plug-in works in conjunction with two products – the popular WooCommerce plug-in and the Paytomat merchant’s web panel. The latter is responsible for all processes associated with crypto-wallets, transactions, and crypto-payment settings.
 *
 * @link              http://paytomat.com
 * @since             1.0.0
 * @package           Paytomat Crypto Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Paytomat Crypto Gateway 
 * Plugin URI:        https://wordpress.org/plugins/paytomat-crypto-gateway/
 * Description:       Discover the crypto-world for your business. With our free plug-in, you can sell anything for the most popular cryptocurrency. To get started, you need a WooCommerce plugin.
 * Version:           1.0.0
 * Author:            Paytomat
 * Author URI:        http://paytomat.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       paytomat-crypto-gateway
 * Domain Path:       /languages
 * WC tested up to:   3.3
 * WC requires at least: 2.6
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 
 */
define( 'PAYTOMAT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-paytomat-activator.php
 */
function activate_paytomat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-paytomat-activator.php';
	Paytomat_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-paytomat-deactivator.php
 */
function deactivate_paytomat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-paytomat-deactivator.php';
	Paytomat_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_paytomat' );
register_deactivation_hook( __FILE__, 'deactivate_paytomat' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-paytomat.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
function run_paytomat() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$plugin = new Paytomat();
		$plugin->run();

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

run_paytomat();
