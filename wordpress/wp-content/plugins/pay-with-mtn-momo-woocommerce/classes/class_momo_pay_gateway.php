<?php
class WC_MomoPay_Gateway extends WC_Payment_Gateway
{
	protected static $true_currencies = array(
		'EUR',
		'UGX',
		'GHS',
		'XAF',
		'RWF',
		'XOF',
		'ZMW',
		'CFA',
		'CDF',
		'SZL',
		'GNF',
		'ZAR',
		'LRD',
		'USD'
	);
	protected static $live_currencies = array(
		'UGX',
		'GHS',
		'XAF',
		'RWF',
		'XOF',
		'ZMW',
		'CFA',
		'CDF',
		'SZL',
		'GNF',
		'ZAR',
		'LRD',
		'USD'
	);

	protected $sanbox_api_key = '';
	protected $sanbox_api_user_key = '';
	protected $wc_field_pref = 'woocommerce_momopay_';
	protected $version = 'BOxGzqSmyAu4XHD3DAgi';

	public $paid_status_name = 'paid';
	public $failed_status_name = 'failed';
	public $pending_status_name = 'pending';

	public $domain = 'mtn-momo-pay'; //plugin text domain

	/**
	 * All fields needed
	 */
	public $user_email;
	public $user_email_registered;
	public $user_name;
	public $user_phone;
	public $api_sub_key;
	public $mode;
	public $api_key;
	public $api_user;

	public $currency;
	public $location;

	public $is_user_registered = false;

	public $license_id;
	protected $last_api_error = '';

	public function __construct()
	{
		// Basic settings
		$this->id = 'momopay';
		$this->icon = plugin_dir_url(__FILE__) . '../assets/img/mtn-momo-logo.png';
		$this->has_fields = false;
		$this->method_title = __('MTN MoMo for WooCommerce', $this->domain);
		$this->method_description = __('To accept payments please configure your MTN MoMo settings', $this->domain);

		// gateways can support subscriptions, refunds, saved payment methods,
		// but in this tutorial we begin with simple payments
		$this->supports = array(
			'products'
		);

		// load the settings
		$this->init_form_fields();
		$this->init_settings();

		$this->mode = $this->get_option('mode');

		// Define variables set by the user in the admin section
		$this->enabled = 'yes';
		$this->title = $this->get_option('title', __('MTN MoMo Pay', $this->domain));
		//later we will add this here this->getPaymentHintMessage
		//Please ensure to use number format as follows: 2567xxxxxxxx and to have enough funds on your MoMo wallet to make payment instantly and avoid order cancellation.
		$this->description = $this->get_option('description', __('Make sure to have enough funds in your MoMo wallet to make payment instantly and avoid order cancellation.', $this->domain));
		$this->instructions = $this->get_option(
			'instructions',
			__('Place order and pay using MTN MoMo', $this->domain)
		);

		$this->user_email = $this->get_option('user_email');
		$this->user_name = $this->get_option('user_name');
		$this->user_phone = $this->get_option('user_phone');
		$this->is_user_registered = $this->get_option('user_registered');
		$this->user_email_registered = $this->get_option('user_email_registered');

		if ($this->is_user_registered) {
			$this->method_description = __('You are almost done! Select operation mode and set the API keys.', $this->domain);
		}

		$this->api_sub_key = $this->get_option('api_sub_key');
		$this->mode = $this->get_option('mode');
		$this->api_key = $this->get_option('api_key');
		$this->api_user = $this->get_option('api_user');
		$this->license_id = $this->get_option('license_id');
		$this->secret_key = $this->get_option('secret_key');
		$this->site_id = $this->get_option('site_id');

		$this->user_request_counter = $this->get_option('user_request_counter');
		$this->max_request_limit = $this->get_option('max_request_limit');

		$this->currency = $this->getCurrency();
		$this->location = $this->getLocation();
	}

	/**
	 * Processes submitted admin options
	 */
	function process_admin_options()
	{
		parent::process_admin_options();

		$this->updateUserData();

		$data = $this->getAdvData();
		$post_data = $this->get_post_data();
		if ($this->site_id) {
			if ($post_data[$this->wc_field_pref . 'reset_user_data']) {
				return $this->resetUserData();
			} else {
				return;
			}
		}

		if ($this->is_user_registered) {
			$mode = $post_data[$this->wc_field_pref . 'mode'];
			$api_sub_key = $post_data[$this->wc_field_pref . 'api_sub_key'];
			$api_key = $post_data[$this->wc_field_pref . 'api_key'];
			$api_user = $post_data[$this->wc_field_pref . 'api_user'];
			$key_not_valid = false;

			if (strlen($api_sub_key) !== 32) {
				$data['api_user'] = '';
				$key_not_valid = true;
			}
			if (strlen($api_key) !== 32 && $mode == 2) {
				$data['api_key'] = '';
				$key_not_valid = true;
			}
			if (strlen($api_user) !== 36 && $mode == 2) {
				$data['api_user'] = '';
				$key_not_valid = true;
			}

			if ($key_not_valid) {
				$settings = new WC_Admin_Settings();
				$keysError = sprintf(__("Your keys are invalid, remove any spaces, keys can only be 32 or 36 chars long.", $this->domain), $this->currency['code']);
				$settings->add_error($keysError);
				return;
			}
			$siteId = $this->validation($api_sub_key, $api_user, $api_key, $mode);
			if ($siteId) {
				$data['site_id'] = $siteId;
			}
		} else {
			$registrationResult = $this->registration(
				$post_data[$this->wc_field_pref . 'user_name'],
				$post_data[$this->wc_field_pref . 'user_email'],
				$post_data[$this->wc_field_pref . 'user_phone']
			);
			if ($registrationResult) {
				if ($this->license_id && $this->secret_key) {
					$data['user_registered'] = true;
					$data['user_email_registered'] = $post_data[$this->wc_field_pref . 'user_email'];
					$data['license_id'] = $this->license_id;
					$data['secret_key'] = $this->secret_key;
				}
			}
		}

		update_option('woocommerce_momopay_settings', $data, 'no');
	}

	/**
	 * Initialize form fields that will be displayed in the admin section.
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'user_email' => array(
				'title' => __('Merchant Email address', $this->domain),
				'type' => 'text',
				'label' => __('Merchant Email address', $this->domain),
				'default' => '',
				'description' => __('The Email address to receive payment notifications', $this->domain),
				'desc_tip' => true
			),
			'user_name' => array(
				'title' => __('MTN Merchant name', $this->domain),
				'type' => 'text',
				'label' => __('MTN Merchant name', $this->domain),
				'default' => '',
				'description' => __('Company name you want your customers to see', $this->domain),
				'desc_tip' => true
			),
			'user_phone' => array(
				'title' => __('Merchant phone number', $this->domain),
				'type' => 'text',
				'label' => __('Merchant phone number', $this->domain),
				'description' => __('MTN MoMo phone number registered with MTN. If you do not have one, use WhatsApp number.', $this->domain),
				'desc_tip' => true,
				'default' => ''
			),
			'api_sub_key' => array(
				'title' => __('Collection Primary key', $this->domain),
				'type' => 'text',
				'label' => __('Collection Primary key', $this->domain),
				'default' => '',
				'description' => ' ',
				'desc_tip' => false
			),
			'mode' => array(
				'title' => __('Mode of operation', $this->domain),
				'type' => 'select',
				'options' => array(
					1 => __('Sandbox', $this->domain),
					2 => __('Live', $this->domain)
				),
				'description' => __('Use Sandbox for testing, Live only after KYC approval by MTN!', $this->domain),
				'desc_tip' => true
			),
			'api_key' => array(
				'title' => __('API key', $this->domain),
				'type' => 'text',
				'label' => __('API key', $this->domain),
				'default' => '',
				'description' => __('Get it on MTN Partner Portal, the link is given during the Live onboarding process. 32 chars long.', $this->domain),
				'desc_tip' => true
			),
			'api_user' => array(
				'title' => __('API user', $this->domain),
				'type' => 'text',
				'label' => __('API user', $this->domain),
				'default' => '',
				'description' => __('Generated on MTN Partner Portal, the link is given during the Live onboarding process. 36 chars long.', $this->domain),
				'desc_tip' => true
			)
		);
	}

	/**
	 * 
	 * Generates the HTML for admin settings page
	 * 
	 */
	public function admin_options()
	{
		$currentAdminOptionsStep = $this->getCurrentStep();

		if ($currentAdminOptionsStep == 3) {
			$html = WC_MomoPay_View::createFinishBox($this);
			echo $html;
			return;
		} else if ($currentAdminOptionsStep == 2) {
			unset($this->form_fields['user_email']);
			unset($this->form_fields['user_name']);
			unset($this->form_fields['user_phone']);

			$sandbox_key_hint = __('Get Sandbox Collection Primary key from <a target="_blank" href="https://momodeveloper.mtn.com/developer">momodeveloper.mtn.com</a>', $this->domain);
			$live_key_hint = __('Get Live Collection Primary key here <a target="_blank" href="https://momoapi.mtn.com">momoapi.mtn.com</a>', $this->domain);
			$sandbox_mode_hint = __('No real payments, only EUR currency, no USSD message', $this->domain);

			echo '<p style="display:none" id="admin_momo_primary_key_hint_sandbox">' . $sandbox_key_hint . '</p>';
			echo '<p style="display:none" id="admin_momo_primary_key_hint_live">' . $live_key_hint . '</p>';
			echo '<p style="display:none" id="admin_momo_mode_hint_sandbox">' . $sandbox_mode_hint . '</p>';

		} else if ($currentAdminOptionsStep == 1) {
			unset($this->form_fields['api_sub_key']);
			unset($this->form_fields['mode']);
			unset($this->form_fields['api_key']);
			unset($this->form_fields['api_user']);
		}

		if ($currentAdminOptionsStep != 3) {
			parent::admin_options();
		}

		ob_start();
?>
jQuery(document).ready(function() {
var mode = jQuery('select#woocommerce_momopay_mode').val();
if(mode == 2){
var live_hint = jQuery('p#admin_momo_primary_key_hint_live').html();
if(live_hint !== 'undefined'){
var fieldset = jQuery('input#woocommerce_momopay_api_sub_key').closest( "fieldset" );
var p = fieldset.find('p.description');
if(p){
p.html(live_hint);
}
}
} else {
var sandbox_hint = jQuery('p#admin_momo_primary_key_hint_sandbox').html();
if(sandbox_hint !== 'undefined'){
var fieldset = jQuery('input#woocommerce_momopay_api_sub_key').closest( "fieldset" );
var p = fieldset.find('p.description');
if(p){
p.html(sandbox_hint);
}
}
}

var mode_hint = jQuery('p#admin_momo_mode_hint_sandbox').html();
if(mode_hint !== 'undefined'){
var fieldset = jQuery('select#woocommerce_momopay_mode').closest( "fieldset" );
var p = fieldset.find('p.description');
if(p){
p.html(mode_hint);
if(mode == 2){
p.hide();
}
}
}

if(mode == 1){
var fieldsetKey = jQuery('input#woocommerce_momopay_api_key').closest( "fieldset" );
if(fieldsetKey) {
fieldsetKey.closest( "td" ).closest( "tr" ).hide();
}
var fieldsetUser = jQuery('input#woocommerce_momopay_api_user').closest( "fieldset" );
if(fieldsetUser) {
fieldsetUser.closest( "td" ).closest( "tr" ).hide();
}
}
});
jQuery('select#woocommerce_momopay_mode').change(function() {
var fieldset = jQuery(this).closest( "fieldset" );
var p = fieldset.find('p.description');
if(p){
if(jQuery(this).val() == 2){
p.hide();
var fieldsetKey = jQuery('input#woocommerce_momopay_api_key').closest( "fieldset" );
if(fieldsetKey) {
fieldsetKey.closest( "td" ).closest( "tr" ).show();
}
var fieldsetUser = jQuery('input#woocommerce_momopay_api_user').closest( "fieldset" );
if(fieldsetUser) {
fieldsetUser.closest( "td" ).closest( "tr" ).show();
}
jQuery('table#mtn_partner_portal_data').show();

var live_hint = jQuery('p#admin_momo_primary_key_hint_live').html();
if(live_hint !== 'undefined'){
var fieldset = jQuery('input#woocommerce_momopay_api_sub_key').closest( "fieldset" );
var p = fieldset.find('p.description');
if(p){
p.html(live_hint);
}
}
} else {
p.show();
var fieldsetKey = jQuery('input#woocommerce_momopay_api_key').closest( "fieldset" );
if(fieldsetKey) {
fieldsetKey.closest( "td" ).closest( "tr" ).hide();
}
var fieldsetUser = jQuery('input#woocommerce_momopay_api_user').closest( "fieldset" );
if(fieldsetUser) {
fieldsetUser.closest( "td" ).closest( "tr" ).hide();
}
jQuery('table#mtn_partner_portal_data').hide();

var sandbox_hint = jQuery('p#admin_momo_primary_key_hint_sandbox').html();
if(sandbox_hint !== 'undefined'){
var fieldset = jQuery('input#woocommerce_momopay_api_sub_key').closest( "fieldset" );
var p = fieldset.find('p.description');
if(p){
p.html(sandbox_hint);
}
}
}
}
});
<?php
		$javascript = ob_get_clean();
		wc_enqueue_js(apply_filters('woocommerce_momopay_admin_options_js', $javascript));

		$display_class = "";
		if ($this->isSandboxMode()) {
			$display_class = "display:none";
		}
		echo '<table class="form-table" style="' . $display_class . '" id="mtn_partner_portal_data"><tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">' . __('Provider Callback Host', $this->domain) . '</th>
				<td class="forminp">
					<p class="description">mtn.momopay.ch</p>
					<p class="description"></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">' . __('Payment Server URL', $this->domain) . '</th>
				<td class="forminp">
					<p class="description">https://mtn.momopay.ch/doPaymentCallback/' . $this->license_id . '</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc"></th>
				<td class="forminp">
					<p class="description"><i>' . __('Use these URLs on MTN Partner Portal -> API access', $this->domain) . '</i></p>
				</td>
			</tr>
		</tbody></table>';
	}

	protected function registration($user_name, $user_email, $user_phone)
	{
		$res = $this->registerMoMo($user_name, $user_email, $user_phone);
		if ($res === false) {
			$settings = new WC_Admin_Settings();
			if (!$this->isTrueCurrency($this->currency['code'])) {
				$currencyError = sprintf(__("eShop currency %s is not supported! Set to EUR or any other supported currency.", $this->domain), $this->currency['code']);
				$settings->add_error($currencyError);
			} else {
				$error = __('Failed to reach MTN MoMo, check your settings!', $this->domain);
				if ($this->last_api_error) {
					$error .= " " . $this->last_api_error;
				}
				$settings->add_error($error);
			}
			return false;
		}

		$this->license_id = $res->licenseId;
		$this->secret_key = $res->secretKey;

		return true;
	}

	protected function validation($api_sub_key, $api_user, $api_key, $mode)
	{
		$settings = new WC_Admin_Settings();
		if ($this->currency['code'] != 'EUR' && $mode == 1) {
			$currencyError = sprintf(__("eShop currency %s is not supported! Sandbox only supports EUR, set under WooCommerce->Currency options", $this->domain), $this->currency['code']);
			$settings->add_error($currencyError);
			return false;
		} else if (
			!$this->isLiveCurrency($this->currency['code'])
			&& $mode == 2
		) {
			//do not allow live mode if not live currency!
			$currencyError = sprintf(
				__("eShop currency %s is not supported for the Live usage!", $this->domain),
				$this->currency['code']
			);
			$settings->add_error($currencyError);
			return false;
		}

		$res = $this->validateUserMoMoKeys($api_sub_key, $api_user, $api_key, $mode);
		if ($res) {
			if (!property_exists($res, 'siteId')) {
				$error = __('Failed to validate MTN MoMo keys, make sure you supplied the right values below! Do not use Collection Widget Primary key!', $this->domain);
				if ($this->last_api_error) {
					$error .= " " . $this->last_api_error;
				}
				$settings->add_error($error);
				return false;
			}
		} else {
			$error = __('Failed to validate MTN MoMo keys, make sure you supplied the right values below! Do not use Collection Widget Primary key!', $this->domain);
			if ($this->last_api_error) {
				$error .= " " . $this->last_api_error;
			}
			$settings->add_error($error);
			return false;
		}

		return $res->siteId;
	}


	public function initUserData()
	{
		$adv_data = $this->getAdvData();

		$this->user_email = @$adv_data['user_email'];
		$this->user_name = @$adv_data['user_name'];
		$this->user_phone = @$adv_data['user_phone'];

		$this->api_sub_key = @$adv_data['api_sub_key'];
		$this->mode = @$adv_data['mode'];
		$this->api_key = @$adv_data['api_key'];
		$this->api_user = @$adv_data['api_user'];

		$this->currency = $this->getCurrency();
		$this->location = $this->getLocation();
		$this->license_id = @$adv_data['license_id'];
		$this->is_user_registered = @$adv_data['user_registered'];
		// $this->is_user_saved         = @$adv_data['user_saved'];
		$this->user_request_counter = @$adv_data['user_request_counter'];
		$this->max_request_limit = @$adv_data['max_request_limit'];
	}

	public function setUserData($data)
	{
		if (@$data[$this->wc_field_pref . 'user_email'])
			$this->user_email = $data[$this->wc_field_pref . 'user_email'];
		if (@$data[$this->wc_field_pref . 'user_name'])
			$this->user_name = $data[$this->wc_field_pref . 'user_name'];
		if (@$data[$this->wc_field_pref . 'user_phone'])
			$this->user_phone = $this->cleanPhone($data[$this->wc_field_pref . 'user_phone']);

		if (@$data[$this->wc_field_pref . 'api_sub_key'])
			$this->api_sub_key = $data[$this->wc_field_pref . 'api_sub_key'];
		if (@$data[$this->wc_field_pref . 'mode'])
			$this->mode = $data[$this->wc_field_pref . 'mode'];
		if (@$data[$this->wc_field_pref . 'api_key'])
			$this->api_key = $data[$this->wc_field_pref . 'api_key'];
		if (@$data[$this->wc_field_pref . 'api_user'])
			$this->api_user = $data[$this->wc_field_pref . 'api_user'];
	}

	public function initUserAction()
	{
		$settings = new WC_Admin_Settings();
		$post_data = $this->get_post_data();

		if (!$post_data)
			return false;
		$this->setUserData($post_data);

		if ($post_data[$this->wc_field_pref . 'user_phone']) {
			$this->registration();
			return;
		}

		if ($post_data[$this->wc_field_pref . 'api_sub_key']) {
			$this->validation();
			return;
		} else if ($post_data[$this->wc_field_pref . 'reset_user_data']) {
			$this->resetUserData();
			return;
		} else {
			$settings->add_error('Empty MoMo Collection Primary key!', $this->domain);
		}
	}

	public function updateUserData()
	{
		if (($res = $this->getUserData()) === false)
			return;

		$this->user_request_counter = $res->requestCount;
		$this->max_request_limit = $res->requestLimit;

		$this->setAdvData('max_request_limit', $res->requestLimit);
		$this->setAdvData('user_request_counter', $res->requestCount);
	}


	public function getLastError()
	{
		return ($this->last_api_error) ? $this->last_api_error : '';
	}


	protected function registerMoMo($user_name, $user_email, $user_phone)
	{
		$url = 'https://momopay.clickon.ch/getLicenseForEmail';
		$data = array(
			'fullName' => $user_name,
			'emailAddress' => $user_email,
			'phoneNumber' => $this->cleanPhone($user_phone),
			'currencyCode' => $this->currency['code'],
			'type' => 'wooplugin',
			'siteAddress' => get_site_url()
		);

		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body' => json_encode($data)
		);

		$res = wp_remote_post($url, $args);

		if (is_wp_error($res)) {
			$this->last_api_error = $res->get_error_message();
			return false;
		}
		;

		if (!@$res['body'] && $res['response']['code'] != 200) {
			$this->last_api_error = 'Registration error (Server code:' . $res['response']['code'] . ')!';
			return false;
		}
		;

		$res = json_decode($res['body']);

		if (!@$res->licenseId || !@$res->secretKey) {
			$this->last_api_error = (@$res->message) ? $res->message : __('MoMo settings are invalid!', $this->domain);
			return false;
		}
		;

		$out = new stdClass();
		$out->licenseId = (string) $res->licenseId;
		$out->secretKey = (string) $res->secretKey;

		return $out;
	}


	protected function validateUserMoMoKeys($api_sub_key, $api_user, $api_key, $mode)
	{
		$url = 'https://momopay.clickon.ch/setKeys/' . $this->license_id;
		$data = array(
			'subKey' => $api_sub_key,
			'currencyCode' => $this->currency['code'],
			'countryCode' => $this->location,
			'type' => 'collection'
		);

		if ($mode == 2) {
			$data['apiUser'] = $api_user;
			$data['apiKey'] = $api_key;
		}

		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-secret-key' => $this->secret_key,
				'version' => $this->version
			),
			'body' => json_encode($data)
		);

		$res = wp_remote_post($url, $args);
		// var_dump($res);

		if (is_wp_error($res)) {
			$this->last_api_error = $res->get_error_message();
			return false;
		}
		;

		if (isset($res['response']['code'])) {
			$result = json_decode($res['body']);
			if (!@$result) {
				$this->last_api_error = __('MoMo settings failed to save!', $this->domain);
				return false;
			}

			if ($res['response']['code'] != 200 && property_exists($result, 'message')) {
				$this->last_api_error = @$result->message;
				return false;
			} else {
				return $result;
			}
		} else {
			$this->last_api_error = __('MoMo settings failed to save!', $this->domain);
			return false;
		}
	}


	protected function getUserData()
	{
		if (!$this->license_id)
			return false;

		$url = 'https://momopay.clickon.ch/getLicenseUsage/' . $this->license_id;
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-secret-key' => $this->secret_key,
				'version' => $this->version
			),
			'body' => json_encode(array())
		);

		$res = wp_remote_post($url, $args);

		if (is_wp_error($res)) {
			$this->last_api_error = $res->get_error_message();
			return false;
		}
		;

		if (!@$res['body'] && $res['response']['code'] != 200) {
			$this->last_api_error = __('MoMo Gateway failed, please try again later!', $this->domain);
			return false;
		}
		;

		$res = json_decode($res['body']);

		if (!isset($res->requestCount) || !isset($res->requestLimit)) {
			$this->last_api_error = __('MoMo Gateway sent invalid reply, please try again later!', $this->domain);
			return false;
		}
		;

		$out = new stdClass();
		$out->requestCount = (int) $res->requestCount;
		$out->requestLimit = (int) $res->requestLimit;

		return $out;
	}

	protected function resetUserData($partial = false)
	{
		if ($partial) {
			$data = $this->getAdvData();

			$this->site_id = '';
			$this->api_sub_key = '';
			$this->mode = 1;
			$this->site_id = '';

			unset($data['site_id']);
			unset($data['mode']);
			unset($data['api_sub_key']);

			update_option('woocommerce_momopay_settings', $data, 'no');
		} else {
			$this->user_email = '';
			$this->user_name = '';
			$this->user_phone = '';
			$this->api_sub_key = '';
			$this->mode = 1;
			$this->api_key = '';
			$this->api_user = '';
			$this->license_id = '';
			$this->site_id = '';

			update_option('woocommerce_momopay_settings', array(), 'no'); #"enabled"=>"yes"
		}

	}

	protected function setAdvData($var_name, $new_value, $autoload = 'no')
	{
		$data = $this->getAdvData();

		$data[$var_name] = $new_value;

		// update_option( 'wc_momopay_adv_data', $data, $autoload);
		update_option('woocommerce_momopay_settings', $data, $autoload);
	}


	protected function getAdvData($var_name = false)
	{
		$data = get_option('woocommerce_momopay_settings');
		if ($data === false)
			return ($var_name) ? '' : array();
		if (!$var_name)
			return $data;
		return (@$data[$var_name]) ? $data[$var_name] : '';
	}

	/**
	 * 1 - Initial step, nothing is set, new plugin
	 * 2 - MoMo Merchant registration done
	 * 3 - MoMo Keys are set, all is ready to use the plugin
	 */
	public function getCurrentStep()
	{
		if ($this->site_id)
			return 3;
		if ($this->isRegisteredUser())
			return 2;
		return 1;
	}

	public function isRegisteredUser()
	{
		return ($this->secret_key && $this->license_id) ? true : false;
	}

	public static function getTrueCurrencies()
	{
		return self::$true_currencies;
	}

	public static function isTrueCurrency($currency_code)
	{
		return (in_array($currency_code, self::$true_currencies)) ? true : false;
	}

	public static function isLiveCurrency($currency_code)
	{
		return (in_array($currency_code, self::$live_currencies)) ? true : false;
	}

	public function isSandboxMode()
	{
		return ($this->mode == 2) ? false : true;
	}


	protected function getCurrency()
	{
		$currencies = get_woocommerce_currencies();
		$currency_code = get_woocommerce_currency();

		$currency_name = (@$currencies[$currency_code]) ? $currencies[$currency_code] : false;

		return ($currency_code && $currency_name) ? array('code' => $currency_code, 'name' => $currency_name) : false;
	}


	protected function getLocation()
	{
		$array = wc_get_base_location();

		return (isset($array['country'])) ? $array['country'] : '';
	}

	protected function preparePaymentData($order)
	{
		return array(
			'amount' => (float) $order->get_total(),
			'note' => 'Order ' . $order->get_order_number(),
			'payer' => $this->cleanPhone($order->billing_phone)
		);
	}


	protected function cleanPhone($phone_num)
	{
		$s = preg_replace('/\D*/', '', $phone_num);
		$s = preg_replace('/\s+/', '', $s);
		return $s;
	}


	protected function addCommentToOrder($order)
	{
		$order->add_order_note(__('MoMo payment request sent, accept with PIN on ', $this->domain) . $order->billing_phone);
	}


	protected function getMsgFromApiAnswer($answer)
	{
		if (@$answer->reason)
			return $answer->reason;
		if (@$answer->message)
			return $answer->message;
		return null;
	}


	protected function isActiveGateway()
	{
		return ($this->site_id) ? true : false;
	}

	/**
	 * Receipt Page
	 **/
	public function receipt_page($order_id)
	{
		global $woocommerce;
		$order = new WC_Order($order_id);
		$payment_id = null;
		$site_URL = get_site_url();

		if (
			$this->isActiveGateway() &&
			!$order->has_status('completed') &&
			!$order->has_status('processing')
		) {
			$this->addCommentToOrder($order);
			$prepare_order = $this->preparePaymentData($order);
			$payment_result = $this->sendPayment($prepare_order);
			if (!$payment_result) {
				return false;
			}
			$payment_result = json_decode($payment_result);
			$payment_id = (@$payment_result->paymentId) ? $payment_result->paymentId : null;
			if ($payment_id) {
				$total_amount_to_save = ((float) $order->get_total()) * 100;
				$trn = array(
					'order_id' => $order_id,
					'amount' => (int) $total_amount_to_save,
					'phone_number' => $prepare_order['payer'],
					'payment_id' => $payment_result->paymentId,
					'failed_reason' => $this->getMsgFromApiAnswer($payment_result),
					'status' => $this->pending_status_name
				);
				$this->saveTransaction($trn);
			} else {

			}

			if ($order->get_payment_method() == 'momopay') {
				echo WC_MomoPay_View::createReceiptBox($order, $payment_id, $this);
			}
		}
	}

	/**
	 * Process the payment field and redirect to checkout/pay page.
	 */
	public function sendPayment($order)
	{
		$url = 'https://momopay.clickon.ch/doPayment/' . $this->license_id;
		$data = array(
			"amount" => $order['amount'],
			"note" => $order['note'],
			"payer" => $order['payer']
		);

		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-secret-key' => $this->secret_key,
				'version' => $this->version
			),
			'body' => json_encode($data)
		);

		$res = wp_remote_post($url, $args);

		if (is_wp_error($res)) {
			$this->last_api_error = $res->get_error_message();

			return false;
		}
		;

		if (@$res['body'])
			return $res['body'];

		if ($res['response']['code'] != 200) {
			$this->last_api_error = 'Payment error!';
			return false;
		}
		;

		return false;
	}

	public function getPaymentStatus($payment_id)
	{
		try {
			$url = 'https://momopay.clickon.ch/getPaymentStatus/' . $this->license_id;
			$data = array("paymentId" => $payment_id);

			$args = array(
				'headers' => array('Content-Type' => 'application/json'),
				'body' => json_encode($data)
			);

			$res = wp_remote_post($url, $args);

			if (is_wp_error($res)) {
				$this->last_api_error = $res->get_error_message();
				return false;
			}
			;

			$out = new stdClass();

			if ($res['response']['code'] == 423 || ($res['body'] && ($res['response']['code'] == 200))) {
				$a = json_decode($res['body']);

				$out->code = $res['response']['code'];

				if (@$a->status) {
					$out->status = $a->status;
				}

				if ($out->code == 423) {
					$out->message = __('Payment method is not available, please contact site Administrator', $this->domain);
				} else if ($out->status == $this->paid_status_name) {
					$out->message = __('Thank you, the order has been paid!', $this->domain);
				}

				$out->message = $this->getMsgFromApiAnswer($a);
			} else {
				$out->status = false;
				$out->message = 'Failed to get payment status';
			}
			;

			return $out;
		} catch (Exception $e) {
			return false;
		}
		;
	}


	public function cancelOrder($order_id)
	{
		$order = new WC_Order($order_id);
		$res = $order->update_status('cancelled', __('Canceled by the user', $this->domain) . "<br/>");

		$out = new stdClass();
		$out->status = $res;
		$out->message = ($res) ? 'Order cancelled.' : __('Cancellation error! Please try again later.', $this->domain);

		return $out;
	}

	public function process_payment($order_id)
	{
		$order = new WC_Order($order_id);

		$checkout_url = $order->get_checkout_payment_url(true);
		$checkout_edited_url = $checkout_url . "&transactionType=momocheckout";

		return array(
			'result' => 'success',
			'redirect' => add_query_arg(
				'order',
				$order_id,
				add_query_arg(
					'key',
					$order->get_order_key(),
					$checkout_edited_url
				)
			)
		);
	}


	protected function saveTransaction($data)
	{
		global $wpdb;

		$wpdb->insert(
			WC_MomoPay_Helper::getPaymentsTableName(),
			array(
				'order_id' => $data['order_id'],
				'amount' => $data['amount'],
				'phone_number' => $data['phone_number'],
				'payment_id' => $data['payment_id'],
				'failed_reason' => $data['failed_reason'],
				'status' => $data['status']
			)
		);
	}


	public function updateTransaction($payment_id, $api_answer)
	{
		global $wpdb;

		$new_data = array(
			'status' => $api_answer->status
		);

		if ($api_answer->message && $api_answer->status != $this->paid_status_name) {
			$new_data['failed_reason'] = $api_answer->message;
		}

		$res = $wpdb->update(
			WC_MomoPay_Helper::getPaymentsTableName(),
			$new_data,
			array('payment_id' => $payment_id)
		);

		if ($api_answer->status == $this->paid_status_name) {
			$order_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT order_id FROM " . WC_MomoPay_Helper::getPaymentsTableName() . " WHERE payment_id = %s",
					$payment_id
				)
			);

			$order = new WC_Order($order_id);
			$order->add_order_note(__('MoMo payment received', $this->domain));
			$order->payment_complete();
		}

		if (!$res)
			return false;
	}
}
;
;