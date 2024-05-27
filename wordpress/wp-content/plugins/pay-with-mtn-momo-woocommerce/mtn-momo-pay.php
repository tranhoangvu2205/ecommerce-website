<?php
/**
 * Plugin Name: MTN MoMo for WooCommerce
 * Plugin URI: https://www.clickon.ch
 * Description: Accept payments with MTN MoMo in WooCommerce
 * Version: 1.0.6
 * Author: mstonys
 * Author URI: https://profiles.wordpress.org/mstonys/
 * Licence: GPL2 
 * Text Domain: mtn-momo-pay
 * Tested up to: 6.1
 * Stable tag: 5.0
 * WC requires at least: 4.2.0
 * WC tested up to: 7.1
 */

defined('ABSPATH') or die("No access please!");

require_once __DIR__ . '/classes/class_momo_pay_helper.php';
// require_once ___DIR__ . 'config.php';


/**
 * MTN MoMo Payment Gateway
 *
 * @class          WC_Gateway_MomoPay
 * @extends        WC_Payment_Gateway
 * @version        1.0.0
 */


/**
 * Check if WooCommerce is activated
 */
if (!function_exists('is_woocommerce_activated')) {
	function is_woocommerce_activated()
	{
		if (class_exists('woocommerce')) {
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('woo_momo_pay_admin_assets')) {
	function woo_momo_pay_admin_assets()
	{
		wp_enqueue_script('momo_pay_script', plugin_dir_url(__FILE__) . 'assets/js/wc-mtn-momo-pay.js', array('jquery'), time(), true);
		wp_enqueue_style('momo_pay_style', plugin_dir_url(__FILE__) . 'assets/css/wc-mtn-momo-pay.css', array(), time());
	}
}

if (!function_exists('woo_momo_pay_public_assets')) {
	function woo_momo_pay_public_assets()
	{
		wp_enqueue_script('momo_pay_script_public', plugin_dir_url(__FILE__) . 'assets/js/wc-mtn-momo-pay_public.js', array('jquery'), time(), true);
		wp_enqueue_style('momo_pay_style', plugin_dir_url(__FILE__) . 'assets/css/wc-mtn-momo-pay.css', array(), time());
	}
}


if (!function_exists('process_momo_payment')) {
	function process_momo_payment(WP_REST_Request $request)
	{
		$parameters = $request->get_json_params();
		echo WC_MomoPay_Controller::processData(@$parameters);
		exit();
	}
}
;

function woo_momo_pay_gateway_init()
{
	if (!class_exists('WC_Payment_Gateway'))
		return;

	require_once __DIR__ . '/classes/class_momo_pay_controller.php';
	require_once __DIR__ . '/classes/class_momo_pay_gateway.php';
	require_once __DIR__ . '/classes/class_momo_pay_view.php';

	$p = new WC_MomoPay_Gateway();

	add_action('woocommerce_receipt_' . $p->id, array($p, 'receipt_page'));
	if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
		add_action('woocommerce_update_options_payment_gateways_' . $p->id, array($p, 'process_admin_options'));
	} else {
		add_action('woocommerce_update_options_payment_gateways', array($p, 'process_admin_options'));
	}
}
;

function woo_momopay_add_gateway_class($methods)
{
	$methods[] = 'WC_MomoPay_Gateway';
	return $methods;
}

function wc_momo_pay_settings_link($links)
{
	$plugin_links = array(
		'<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=momopay') . '">' . __('Configure', 'mtn-momo-pay') . '</a>'
	);

	return array_merge($plugin_links, $links);
}


function woo_momo_pay_install()
{
	WC_MomoPay_Helper::pluginInit();
}


function woo_momo_pay_remove()
{
	WC_MomoPay_Helper::pluginRemove();
}

register_activation_hook(__FILE__, 'woo_momo_pay_install');
register_uninstall_hook(__FILE__, 'woo_momo_pay_remove');

add_action('admin_enqueue_scripts', 'woo_momo_pay_admin_assets');
add_action('wp_enqueue_scripts', 'woo_momo_pay_public_assets');

add_action('plugins_loaded', 'woo_momo_pay_gateway_init');

add_action('rest_api_init', function () {
	register_rest_route(
		'woocommerce-mtn-momo-pay/v1',
		'/backend',
		array(
			'methods' => 'POST',
			'callback' => 'process_momo_payment',
			'permission_callback' => '__return_true'
		)
	);
});

if (!add_filter('woocommerce_payment_gateways', 'woo_momopay_add_gateway_class'))
	die;

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_momo_pay_settings_link');

// On cart page only
add_action('woocommerce_check_cart_items', 'show_momo_errors_in_the_cart');
function show_momo_errors_in_the_cart()
{
	if (isset($_GET['momo_payment_id'])) {
		$momoPaymentId = sanitize_text_field($_GET['momo_payment_id']);
		wc_print_notice(sprintf(__("MTN MoMo payment %s failed. Please try again.", "mtn-momo-pay"), $momoPaymentId), 'error');
	}
}

//Handles Wordpress AJAX
function momopay_ajax()
{
	//check nonce, if it is invalid we will get 403 error
	check_ajax_referer("momopay");
	if (isset($_POST['data'])) {
		$data = $_POST['data'];
		echo WC_MomoPay_Controller::processData(@$data);
	}
	exit();
}

add_action('wp_ajax_momopay_ajax', 'momopay_ajax');
add_action('wp_ajax_nopriv_momopay_ajax', 'momopay_ajax');

?>