<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://paytomat.com
 * @since      1.0.0
 *
 * @package    Paytomat
 * @subpackage Paytomat/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Paytomat
 * @subpackage Paytomat/includes
 * @author     Paytomat <it.acc@dailyco.in>
 */
class Paytomat_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $paytomat    The ID of this plugin.
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
	 * @param      string    $paytomat       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public function __construct( $paytomat, $version ) {

		$this->paytomat = $paytomat;
		$this->version = $version;
		
	}

	/**
	 * Init class of getway.
	**/ 
	public function init_paytomat_getway(){
		require_once plugin_dir_path( __FILE__ ) . '../includes/paytomat-gateway/class-paytomat-gateway.php';

	}

	/**
	 * Add getway of PayTomat to method of woocommerce.
	**/ 
	public function add_paytomat_getway_to_wc($methods){
		$methods[] = 'WC_Gateway_Paytomat'; 
    	return $methods;
	}
	
	/**
	 * Register the stylesheets for the admin area.
	**/
	public function enqueue_styles() {
		wp_enqueue_style( $this->paytomat, plugin_dir_url( __FILE__ ) . 'css/paytomat.css', array(), $this->version, "all" );
	}

	/**
	 * Register the JavaScript for the admin area.
	 **/
	public function enqueue_scripts() {
		wp_enqueue_script( $this->paytomat, plugin_dir_url( __FILE__ ) . 'js/paytomat.js', array( 'jquery' ), $this->version, false );
	}

	/*
	 Register action links of plugin.
	 function wpcf_plugin_action_links($links){	
		$mylinks = array(
		 '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytomat' ) . '">Settings</a>',
		);
		return array_merge( $links, $mylinks );
	}*/


	/*
	* close order after callback from Paytomat
	*/
    public function close_order_callback($data){
    	register_rest_route('paytomat/v1', '/close_order', array(
    		'methods' => 'POST',
    		'callback' => function(WP_REST_Request $request) {
    			global $woocommerce;
    			$this::init_paytomat_getway();
	            $crypt_order = str_replace(" ", "+", $request['order']);
	    		$pt_getway = new WC_Gateway_Paytomat();
	    		$order_id = $pt_getway -> ptmt_decrypt_order_id($crypt_order);
	    		if ($order_id) {
	    			$order = new WC_Order( $order_id );
					$order->payment_complete();
				    return true;
	    		} else {
	    			wp_die("incorrect data");
	    			return false;
	    		}
    		}
    	));
	}

}


