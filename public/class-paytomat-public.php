<?php

/**
 * The public-facing functionality of the plugin.
 *
 
 * @package    Paytomat
 * @subpackage Paytomat/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Paytomat
 * @subpackage Paytomat/public
 * @author     Paytomat <it.acc@dailyco.in>
 */
class Paytomat_Public {

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $paytomat   The ID of this plugin.
	 */
	private $paytomat;

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
	 * @param      string    $paytomat      The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $paytomat, $version ) {
		$this->paytomat= $paytomat;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->paytomat, plugin_dir_url( __FILE__ ) . 'css/paytomat.css', array(), $this->version, 'all' );
	}


	public function enqueue_scripts() {
		wp_enqueue_script( $this->paytomat, plugin_dir_url( __FILE__ ) . 'js/paytomat.js', array( 'jquery' ), $this->version, true );
	}

	/*
	* Gen templete for show qr of order
	*
	* @since    1.0.0
	*/

	public function get_page_payment_qr($template){
    	global $wp_query;
		if (strripos($_SERVER['REQUEST_URI'],'payqr') > 0) {
			return plugin_dir_path( __FILE__ ) . 'partials/payment-qr.php';
		}
		return $template;
    }
}
