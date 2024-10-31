<?php

/**
 * Сlass for querying REST API PayTomat
 *
 * @link       https://app.paytomat.com
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

class Paytomat_Api extends WC_Payment_Gateway  {
	public $paytomat_endpoint;
	public $paytomat_token;
	public $paytomat_headers;
	function __construct($paytomat_token, $paytomat_endpoint) {
		$this -> paytomat_token    = $paytomat_token;
		$this -> paytomat_endpoint = $paytomat_endpoint;
		$this -> paytomat_headers  = array(
			"Authorization" => "Bearer " . $paytomat_token,
			"Content-type"  => "application/json"
		);
	}

	public function ptmt_get_currencies() {
        $paytomat_endpoint = $this -> paytomat_endpoint;
        $paytomat_headers  = $this -> paytomat_headers;
		$res      = wp_remote_get($paytomat_endpoint . '/currencies', 
					array(
						'timeout'  => 10,
						'headers' =>  $paytomat_headers
					)
		);
		$answer = $res["body"];
		$answer = json_decode($answer);
		if (isset($answer -> {'currencies'})) {
			$pt_result = $answer -> {'currencies'};
		} else {
			$pt_result = false;
		}
		return $pt_result;
	}

	public function ptmt_get_exchange_rates($currency_out) {
		$paytomat_endpoint = $this -> paytomat_endpoint;
        $paytomat_headers  = $this -> paytomat_headers;
		$res = wp_remote_get($paytomat_endpoint . '/exchange_rates?currency=' . $currency_out, 
			array(
				'timeout'  => 10,
				'headers'  => $paytomat_headers
				)
		);

		$answer = $res["body"];
		$answer = json_decode($answer);
		if (isset($answer -> {'rates'})) {
			$pt_result = $answer -> {'rates'};
			if (isset($pt_result -> {'error'})) {
				$pt_result = false;
			}
		} else {
			$pt_result = false;
		}
		return $pt_result;
	}

	public function ptmt_create_transaction($currency_out, $currency_in, $total, $order_id, $callback_url, $ref = 'def' ) {
		$paytomat_endpoint = $this -> paytomat_endpoint;
        $paytomat_headers  = $this -> paytomat_headers;
		$ref = ($ref === 'def') ? time() : $ref;
		$res = wp_remote_post($paytomat_endpoint . '/create_transaction', 
			array(
				'timeout'  => 10,
				'headers'  => $paytomat_headers,
				'body'     => json_encode(array(
						'order_id'     => strval($order_id),
						'currency_in'  => $currency_in,
						'currency_out' => $currency_out,
						'amount_in'    => $total,
						'callback_url' => $callback_url
				))
			)
		);

		if ($res["response"]["code"] >= 200) {
			try {
			    $pt_result = json_decode($res["body"]);
			} catch (Exception $e) {
			    $pt_result = false;
			}
		} else {
			$pt_result = false;
		}
		return $pt_result;
	}
}