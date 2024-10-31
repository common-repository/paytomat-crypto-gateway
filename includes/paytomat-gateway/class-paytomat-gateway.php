<?php

/**
 * Fired during plugin activation
 *
 * @link       https://paytomat.com
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
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The main class for manage payments of crypto-gateway
*/
class WC_Gateway_Paytomat  extends WC_Payment_Gateway  {
	function __construct() {
		$this->id = "paytomat";
		$this->title = 'Paytomat Crypto Gateway';
		$this->description = 'Discover the crypto-world for your business. With our free plug-in, you can sell anything for the most popular cryptocurrency!';
		$this->icon = plugins_url() . '/paytomat-crypto-gateway/img/logo.png';
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->form_fields = array(
		    'enabled' => array(
		        'title' => __( 'Enable/Disable', 'woocommerce' ),
		        'type' => 'checkbox',
		        'label' => __( 'Enable Cheque Payment', 'woocommerce' ),
		        'default' => 'yes'
		    ),
		    'token' => array(
		        'title' => __( 'Token', 'woocommerce' ),
		        'type' => 'text',
		        'description' => __( 'JWT Token from merchant panel of Paytomat Crypto Gateway (<a href="https://app.paytomat.com/" target="_blank">app.paytomat.com</a>)', 'woocommerce' )
		    )
		);
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
 	* Init criptocurrecy for show to user
	*/

	public function payment_fields (){
		global $woocommerce; 
		require_once plugin_dir_path( __FILE__ ) . 'class-paytomat-api.php';
		require_once plugin_dir_path( __FILE__ ) . '../../constans.php';

		$token = $this->get_option('token');
        $total = $woocommerce->cart->total;
        $currency_order = get_woocommerce_currency(); 
        $ptmt_api = new Paytomat_Api($this->get_option('token'), PAYTOMAT_ENDPOINT_V1);
        $exchange   = $ptmt_api -> ptmt_get_exchange_rates( $currency_order);
        $currencies = $ptmt_api -> ptmt_get_currencies();

        if( !$exchange or !$currencies ){
        	echo "Currency <b> $currency_order </b> of order not supported of Paytomat.";
        	return;
        }
        if( !$this::is_currency_of_paytomat_available($currencies, $currency_order) ) {
        	echo "Currency <b> $currency_order </b> of order not supported of Paytomat merchant.";
        	return;
        }
        $this::show_currency_out($currencies, $exchange, $total, $currency_order);
	}

	/**
 	* Saving selected cryptocurrency and create trasaction in Paytomat-core
	*/

	public function process_payment( $order_id ) {
		require_once plugin_dir_path( __FILE__ ) . 'class-paytomat-api.php';
		require_once plugin_dir_path( __FILE__ ) . '../../constans.php';
	    global $woocommerce;

	    $this->init_form_fields();
	    $ptmt_api            = new Paytomat_Api($this->get_option('token'), PAYTOMAT_ENDPOINT_V1);
	    $currency_in         = get_woocommerce_currency(); 
	    $order               = new WC_Order( $order_id );
	    $token               = $this->get_option('token');
	    $thank_page_url      = $this->get_return_url( $order );
	    $total               = $woocommerce->cart->total;
	    $currency_out        = sanitize_text_field(wp_strip_all_tags($_POST['currency_out']));
	    //get data for payment with QR
	    $crypted_order_id    = $this::ptmt_encrypt_order_id($order_id);
	    $callback_url        = add_query_arg(array('order' => $crypted_order_id), home_url(PAYTOMAT_MERCHANT_ENDPOINT_CLOSE_ORDER));
	    $pt_transaction = $ptmt_api -> ptmt_create_transaction($currency_out, $currency_in, $total, $order_id, $callback_url);

	   	wc_setcookie('thank_page' . $order_id, $thank_page_url); 		
 		if (!$pt_transaction) {
 			wc_add_notice( __('Payment error:', 'woothemes') . 'error', 'error' );
 			return;
 		}

		$page_qr_pay = add_query_arg(array(
			'order_id'    => $order_id,
			'address'     => $pt_transaction->{'address'},
			'qr'	      => $pt_transaction->{'qr_code'},
			'amount_out'  => $pt_transaction->{'amount_out'},
			'track_url'   => $pt_transaction->{'track_url'},
			'qurency'     => $currency_out
		), home_url('/payqr'));
	    return array(
	        'result'   => 'success',
	        'redirect' => $page_qr_pay // page of plugin paytomat with qr-payment for showing
	    );
	}

    private function is_currency_of_paytomat_available($currencies, $currency_order) {
    	$res = false;
    	foreach ( $currencies as $currency) {
    		if ($currency->{'code'} === $currency_order and $currency->{'in'}) { 
    			$res = true;
    		}
    	}
    	return $res;
    }

    private function show_currency_out($currencies, $exchange_rates, $total, $currency_order) {
    	echo "<div id='pt-currency-selector'><select name='currency_out' class='pt' id='pt-selector'>";
    		foreach ( $currencies as $currency) {
    			if($currency->{'out'}){
    				$code  = esc_attr(wp_strip_all_tags($currency->{'code'}));
    				$title = wp_strip_all_tags($currency->{'title'});
    				$t = number_format(($exchange_rates-> {$code} * $total), 9);

    				// If currency equal 0, this currency was set as $currency_order in Woocommerce
    				if ($t == 0) {
    					$t = number_format($total, 9);
    				}

					echo <<<EOT
					<div>
					 <option class='pt-option-currency-title' value='$code' selected> 
    				   <label> $title </label> <b> $t </b>
    				 </option>
    				 </div>
EOT;
    			}
    		}
    	echo "</select></div>";
    }

    // Encrypt order_id of edpoint for Woocommerce for close order 
	public function ptmt_encrypt_order_id( $q ) {
		global $woocommerce;
	    $this->init_form_fields();
	    $cryptKey      = $this->get_option('token');
	    $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q , MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
	    return( $qEncoded );
	}

	public function ptmt_decrypt_order_id( $q ) {
		global $woocommerce;
	    $this->init_form_fields();
	    $cryptKey      = $this->get_option('token');
	    $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
	    return( +$qDecoded );
	}
}



