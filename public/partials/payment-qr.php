<?php
/**
 * The public-facing tmplate for view QR of order
 *
 * @link       http://paytomat.com
 * @since      1.0.0
 *
 * @package    Paytomat
 * @subpackage Paytomat/public/partials
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}
global $woocommerce;
$amount_out = sanitize_text_field($_GET['amount_out']);
$currency 	= sanitize_text_field($_GET['qurency']);
$qr_code  	= sanitize_text_field($_GET['qr']);
$address  	= sanitize_text_field($_GET['address']);
$order_id 	= sanitize_text_field($_GET['order_id']);
$thank_page = sanitize_text_field($_COOKIE['thank_page' . $order_id]);
$track_url 	= sanitize_text_field($_GET['track_url']);
$logo_src 	= plugin_dir_url(__FILE__) . '../../img/paytomat.svg';
$js_dir_src = plugin_dir_url(__FILE__) . '../js';
$wc_checkout_url = get_permalink(wc_get_page_id( 'checkout' ));

if (!is_numeric($amount_out)) {
	wp_die( 'Invalid data' );
}

if (!is_string($currency)) {
	wp_die( 'Invalid data' );
}

if (!is_string($qr_code)) {
	wp_die( 'Invalid data' );
}

if (!is_string($address)) {
	wp_die( 'Invalid data' );
}

if (!is_numeric($order_id)) {
	wp_die( 'Invalid data' );
}

if (!is_string($thank_page)) {
	wp_die( 'Invalid data' );
}

if (!is_string($track_url)) {
	wp_die( 'Invalid data' );
}

if ($currency === "XEM") {
	$qr_code = wp_kses_stripslashes($qr_code);
}

function set_pt_title() {
	return "Paytomat crypto gateway";
}

add_filter( 'pre_get_document_title', 'set_pt_title');
wp_enqueue_script('ptmt-jquery-qrcode', plugin_dir_url(__FILE__)  . "../js/libs/jquery-qrcode.min.js", array('jquery'), false, false);
wp_enqueue_style('ptmt-qrframe', plugin_dir_url(__FILE__) . "../css/paytomat-qrframe.css");

get_header();
?>

<div align="center" id="paytomat-qr">
	<br>
	<h2 align="center">PAY IN CRYPTO</h2>
	<div id="pt-place-qr">
		<div class="pt-row">
			<div class="pt-col-2 pt-text-center pt-info" >
				<img src="<?=$logo_src?>" >
				<p>To pay for order <b>№<?=absint($order_id)?></b>, you need send <b><?=wp_strip_all_tags($amount_out);?> <?=wp_strip_all_tags($currency);?></b> <br>to this address<br><b><?=wp_strip_all_tags($address);?></b></p>
				<p>OR <a href="<?=esc_url($wc_checkout_url);?>" class="pt-back" >GO BACK</a></p>
				<a class="pt-back pt-track" target="a_blank" href= "<?=esc_url($track_url)?>">TRACK PAYMENT</a> <a class="pt-paid" href=<?=esc_url($thank_page);?>>I'VE PAID</a>
			</div>
			<div class="pt-col-2 pt-text-center pt-qr">
				<div id="qr-frame">
					<h2>SCAN TO PAY</h2>
					<div id="qr" data-qr="<?=esc_attr($qr_code);?>"></div>
				</div>
			</div>
		</div>
	</div>
	<br>
</div>

<?php
get_footer();
?>


