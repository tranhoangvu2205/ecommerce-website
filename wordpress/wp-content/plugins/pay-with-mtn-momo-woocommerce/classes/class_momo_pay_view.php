<?php
class WC_MomoPay_View
{

	protected static $pro_plugin_link = 'https://www.clickon.ch/mtn-momo-pay';

	protected static function createRegistrationBox($cfg)
	{
		return '<table class="form-table" data-momopay-step="1">
				<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_momopay_user_email">' . __('Merchant Email address', $cfg->domain) . '</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Merchant Email address</span></legend>
							<input class="input-text regular-input " type="text" name="woocommerce_momopay_user_email" id="woocommerce_momopay_user_email" value="' . $cfg->user_email . '" placeholder="">
							<p class="description">The Email address to receive payment notifications</p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_momopay_user_name">MTN Merchant name</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>MTN Merchant name</span></legend>
							<input class="input-text regular-input " type="text" name="woocommerce_momopay_user_name" id="woocommerce_momopay_user_name" value="' . $cfg->user_name . '" placeholder="">
							<p class="description">Company name you want your customers to see</p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_momopay_user_phone">Merchant phone number</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Merchant phone number Legend</span></legend>
							<input class="input-text regular-input " type="text" name="woocommerce_momopay_user_phone" id="woocommerce_momopay_user_phone" value="' . $cfg->user_phone . '" placeholder="">
							<p class="description">MTN MoMo phone number registered with MTN</p>
						</fieldset>
					</td>
				</tr>
				</tbody>
			</table>
			<input type="hidden" name="woocommerce_momopay_register_user_data" value="1" >
			<input type="hidden" name="woocommerce_momopay_save_user_data" value="0" >
			<input type="hidden" name="woocommerce_momopay_reset_user_data" value="0" >';
	}


	protected static function getCurrencyInfo($cfg)
	{
		return array(
			'code' => $cfg->currency['code'],
			'desc' => (($cfg->isSandboxMode() && $cfg->currency['code'] != 'EUR')) ? 'Change to EUR if you want to test in Sandbox' : $cfg->currency['name']
		);
	}

	protected static function createSelectBox($active_item)
	{
		$items = array('Sandbox', 'Live');

		$html = '<select class="select" name="woocommerce_momopay_mode" id="woocommerce_momopay_mode">';

		foreach ($items as $k => $item_name) {
			$i = $k + 1;
			$selected = ($active_item == $i) ? 'selected="selected"' : '';
			$html .= '<option value="' . $i . '" ' . $selected . '>' . $item_name . '</option>';
		}
		;

		$html .= '</select>';

		$hide_class = ($active_item == 2) ? 'hide' : '';

		$html .= '<p class="description ' . $hide_class . '" data-momoadv-box="sandbox">No real payments, only EUR currency, no USSD message</p>';

		return $html;
	}


	protected static function createLiveModeBox($cfg)
	{
		$html = '<table class="form-table ' . self::hideIfUsedSandboxMode($cfg->isSandboxMode()) . '" data-momoadv-box="live">
				<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">Provider Callback Host</th>
					<td class="forminp">
						<p class="description">mtn.momopay.ch</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Payment Server URL</th>
					<td class="forminp">
						<p class="description">https://mtn.momopay.ch/doPaymentCallback/' . $cfg->license_id . '</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_momopay_api_key">API Key</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>API Key</span></legend>
							<input class="input-text regular-input " type="text" name="woocommerce_momopay_api_key" id="woocommerce_momopay_api_key" value="' . $cfg->api_key . '" placeholder="">
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_momopay_api_user_key">API user </label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>API user</span></legend>
							<input class="input-text regular-input " type="text" name="woocommerce_momopay_api_user_key" id="woocommerce_momopay_api_user_key" value="' . $cfg->api_user_key . '" placeholder="">
						</fieldset>
						<p class="description">You can get the Live keys on MTN Partner Portal</p>
					</td>
				</tr>
				</tbody>
			</table>
			<input type="hidden" name="woocommerce_momopay_register_user_data" value="0" >
			<input type="hidden" name="woocommerce_momopay_save_user_data" value="1" >
			<input type="hidden" name="woocommerce_momopay_reset_user_data" value="0" >';

		return $html;
	}


	protected static function hideIfUsedSandboxMode($used_sandbox_mode)
	{
		return ($used_sandbox_mode) ? 'hide' : '';
	}


	protected static function createValidateBox($cfg)
	{
		$currency = self::getCurrencyInfo($cfg);

		$html = '<table class="form-table" data-momopay-step="2">
				<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">MoMo Merchant Email</th>
					<td class="forminp">
						<p class="description">' . $cfg->user_email . '</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">MoMo Merchant ID</th>
					<td class="forminp">
						<p class="description">' . $cfg->license_id . '</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Currency</th>
					<td class="forminp">
						<p class="description"><strong>' . $currency['code'] . '</strong> ' . $currency['desc'] . '</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_momopay_api_sub_key">Collection Primary key </label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Collection Primary key</span></legend>
							<input class="input-text regular-input " type="text" name="woocommerce_momopay_api_sub_key" id="woocommerce_momopay_api_sub_key" style="" value="' . $cfg->api_sub_key . '" placeholder="">
							<p class="description ' . self::hideIfUsedSandboxMode($cfg->isSandboxMode()) . '" data-momoadv-box="live">
								Get Live Collection Primary key here <a target="_blank" href="https://momoapi.mtn.com">https://momoapi.mtn.com</a>
							</p>
							<p class="description ' . self::hideIfUsedSandboxMode(!$cfg->isSandboxMode()) . '" data-momoadv-box="sandbox">
								Get Sandbox Collection Primary key from <a target="_blank" href="https://momodeveloper.mtn.com/developer">https://momodeveloper.mtn.com</a>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_momopay_mode">Mode of operation</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Mode of operation</span></legend>'
			. self::createSelectBox($cfg->mode)
			. '</fieldset>
					</td>
				</tr>
				</tbody>
			</table>';

		$html .= self::createLiveModeBox($cfg);

		return $html;
	}


	public static function createFinishBox($cfg)
	{
		$currency = self::getCurrencyInfo($cfg);
		$promo_option = '';

		$promo_box_class = 'momopay_limits-box';
		$promo_text = "MOMO FREE GATEWAY";

		$html = '<div class="mtn-momo-pay_main">';
		$html .= '<div id="momopay_leftSide" class="mtn-momo-pay_left">';
		$html .= '
		<p class="' . $promo_box_class . '">' . $promo_text . '</p>' . $promo_option .
			'<table class="form-table" data-momopay-step="3">
				<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">Merchant Email</th>
					<td class="forminp">
						<p class="description">' . $cfg->user_email_registered . '</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Merchant ID</th>
					<td class="forminp">
						<p class="description">' . $cfg->license_id . '</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Currency</th>
					<td class="forminp">
						<p class="description"><strong>' . (($cfg->isSandboxMode()) ? 'EUR' : $currency['code']) . '</strong></p>';

		if ($currency['code'] == 'EUR')
			$html .= '<p class="mtn-momo-pay_error-box">Sandbox mode! No real payments can be done, no USSD push to mobile phone.</p>';
		else
			$html .= '<p class="mtn-momo-pay_error-box">You can only request money in this currency!</p>';

		$html .= '	</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Collection Primary key</th>
					<td class="forminp">
						<p class="description">' . $cfg->api_sub_key . '</p>
					</td>
				</tr>
				</tbody>
			</table>
			
			<input type="hidden" name="woocommerce_momopay_reset_user_data" value="1" >';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}


	public static function createMainBox($cfg)
	{
		$step = $cfg->getCurrentStep();

		$html = '<h3>MTN MoMo Configuration</h3>
				<p>To accept payments please configure your MoMo settings, if you do not have MTN number, enter your WhatsApp phone number.</p>
				<div class="mtn-momo-pay_main">';

		if ($step == 1) {
			$html .= self::createRegistrationBox($cfg);
		} else {
			$html .= '<div id="momopay_leftSide" class="mtn-momo-pay_left">';
			$html .= (($step < 3) ? self::createValidateBox($cfg) : self::createFinishBox($cfg));
			$html .= '</div>';
		}

		$html .= '</div><div class="mtn-momo-pay_error-box" id="momopay_errorBox">' . $cfg->getLastError() . '</div>';
		return $html;
	}


	public static function createReceiptBox($order, $payment_id, $cfg)
	{
		$process_desc = (!$payment_id) ? 'Payment failed, MTN MoMo plugin is not configured' : 'Accept payment on ' . $order->billing_phone . ' with a PIN';

		$orderCancelUrl = $order->get_cancel_order_url('');

		// $siteUrl = get_site_url();
		// $endpointUrl = $siteUrl . '/wp-json/woocommerce-mtn-momo-pay/v1/backend';
		$endpointUrl = admin_url('admin-ajax.php');

		$cart_page_id = wc_get_page_id('cart');
		$cartUrl = $cart_page_id ? get_permalink($cart_page_id) : '';

		$orderReceivedUrl = $order->get_checkout_order_received_url();
		$cartPaymentWithIdUrl = $cartUrl . '?momo_payment_id=' . $payment_id;

		$sandboxLine = '';
		if ($cfg->isSandboxMode()) {
			$sandboxLine = '<h3 style="color:red; font-weight: bold;">' . __('Sandbox mode - payment will be accepted automatically', $cfg->domain) . '</h3>';
		}
		$html = '<div class="momopay-checkout-box">' . $sandboxLine . '
			<h3 id="momopay_processDescBox">' . $process_desc . '</h3>
			<img class="momopay-checkout-logo" src="' . plugin_dir_url(__FILE__) . '../assets/img/mtn-momo-logo.png" alt="MTN MoMoPay payments">
			<div class="mtn-momo-pay_error-box" id="momopay_errorBox"></div>
			<input type="hidden" id="currentOrderId" value="' . $order->get_id() . '" />
			<input type="hidden" id="paymentId" value="' . $payment_id . '" />
			<input type="hidden" id="orderCancelUrl" value="' . $orderCancelUrl . '" />
			<input type="hidden" id="endpointUrl" value="' . $endpointUrl . '" />
			<input type="hidden" id="momononce" value="' . wp_create_nonce('momopay') . '" />
			<input type="hidden" id="isSandbox" value="' . $cfg->isSandboxMode() . '" />
			<input type="hidden" id="cartUrl" value="' . $cartUrl . '" />
			<input type="hidden" id="orderReceivedUrl" value="' . $orderReceivedUrl . '" />
			<input type="hidden" id="cartPaymentWithIdUrl" value="' . $cartPaymentWithIdUrl . '" />
			<button id="cancelCurrentOrder">Cancel order</button>
		</div>';

		return $html;
	}
}
?>