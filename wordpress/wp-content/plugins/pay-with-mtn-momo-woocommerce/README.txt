=== Pay with MTN MoMo in WooCommerce ===
Contributors: mstonys
Donate link: https://www.clickon.ch
Tags: MTN MoMo, MoMo, payment, MTN, payment gateway, mobile money, WooCommerce
Tested up to: 6.1
Stable tag: 1.0.6
Requires at least: 5.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept secure MTN Mobile Money aka MoMo payments on your WooCommerce web store or eShop.

== Description ==

*MTN MoMo* is a mobile payment system which is available in *Uganda, Ghana, Cameroon, Zambia, Swaziland, Rwanda, Ivory Coast, Benin, Guinea-Conakry, Congo Brazza and Nigeria*

💥 November 2022 💥 also available in Nigeria 🇳🇬
💥 January 2022 💥 also available in South Africa 🇿🇦 and Liberia 🇱🇷

This plugin allows you to collect payments on your WooCommerce eShop using *MTN MoMo* in 13 countries 🇬🇭🇨🇲🇺🇬🇿🇲🇸🇿🇷🇼🇨🇮🇧🇯🇬🇳🇨🇬🇿🇦🇱🇷🇳🇬

You must have **WooCommerce** because we developed this plugin as a *WooCommerce payment gateway* 🚀

This is a **fully functioning plugin** not a *demo version*. It works in both *Sandbox* (development mode) and in *Live* (production mode). 

Please keep in mind that MoMo Sandbox has several `limitations` imposed by MTN, see them here:

* Sandbox is not requesting *real money*, means no **USSD request** on your phone. no matter how hard you try 😂
* In the Sandbox you can only use *EUR* currency and certain test numbers from Sweden 🇸🇪 🥶
* Sandbox is not able to callback your application, to notify about payment status. This one is a sad limitation. 😞
* Test phones numbers to test for **error conditions** are limited to `46733123450`, `46733123451`, `46733123452`, `46733123453`
* Only this number will be able to "pay" after 30 seconds `46733123454` 😎 
* All other numbers will act like the **payment was successful immediately**!

==== How to use ===
1. Install and activate *WooCommerce* plugin before activating this plugin
2. Select **EUR** as your WooCommerce currency if you want to test in Sandbox
3. Select **your target currency** if you have done KYC process with MTN (see below for more)
4. Install and activate this plugin
5. Enter your Merchant name, MTN phone number and Email to receive notifications
6. Continue to the next step, see below

==== Plugin setup for MTN MoMo Sandbox - no real payments 🤮 ====

1. Visit [MoMo API Developer portal](https://momodeveloper.mtn.com/signup) and sign-up 📧
2. Enable **Collection API** (NOT the Collection Widget API please)
3. Get Sandbox *Collection Primary key* and enter it in the **Plugin settings**
4. Test payments in Sandbox you can **only use EUR currency**

**IMPORTANT for Rwanda** use `https://momodeveloper.mtn.co.rw` to sign-up and get the keys!

==== Plugin setup for MTN MoMo Live - with real payments 💰😎 ====

1. Visit [MoMo API Developer portal](https://momodeveloper.mtn.com/go-live) and submit your KYC documents 📝📝📝
2. ⌛ Wait for the MTN to process your application and send you access to the Partner Portal and API management dashboard ⌛
3. Login to the [momoapi.mtn.com](https://momoapi.mtn.com) and get your Live *Collection Primary key* 🔑
4. Login to the **MTN Partner Portal** and generate *API user* and *API key* 🔑
5. Enter *Collection Primary key*, *API user* and *API key* in the **Plugin settings**
6. Accept payments! 💰💰💰

**IMPORTANT for Rwanda** use `https://momodeveloper.mtn.co.rw/go-live` to go Live! Get *Collection Primary key* here `https://momoapi.mtn.co.rw`
Recently we got many reports that https://momodeveloper.mtn.co.rw Portal is not stable, so we advice to use https://momodeveloper.mtn.com instead. 
Please check the Rwanda FAQ below for more information.

==== Supported currencies ====

* EUR (Sandbox only, no real payments)
* GHS Ghana / Ghanaian Cedi
* UGX Uganda / Ugandan Shilling
* XAF Cameroon / Central African CFA franc
* RWF Rwanda / Rwandan Franc
* XOF Benin / West African CFA franc
* XOF Ivory Coast / West African CFA franc
* XAF Congo Brazza / Central African CFA franc
* SZL Eswatini/Swaziland / Swazi Lilangeni
* GNF Guinea-Conakry / Guinean Franc
* ZMW Zambia / Zambian Kwacha
* ZAR South Africa / South African Rand
* LRD Liberia / Liberian dollar & USD
* NRN Nigeria / Naira

** IMPORTANT Do not forget to select your country and the currency in WooCommerce settings **

==== DEMO eShop - in Sandbox mode ====

No real payments can be done, eShop currency EUR [demo.momo.clickon.ch](https://demo.momo.clickon.ch)

==== How to get Support ====

You have several ways to request support for this plugin:

* You can send us an Email to momo-plugin@clickon.ch 🙋📧
* Submit your question to the plugin discussion board on wordpress.com 🙋🗣️
* Visit [MTN MoMo Skype forums](https://momodeveloper.mtn.com/contact-support) (ask for `Mindaugas`) 🙋😋

We will be more than happy to help you! 👍

== Installation ==

This section describes how to install the plugin and get it working.

1. Unzip `pay-with-mtn-momo-woocommerce.zip` to the `/wp-content/plugins/` directory, or install from the WordPress Plugins Directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure plugin under WooCommerce->Payments, look for *MTN MoMo*


== Disclaimer ==

ClickOn GmbH, the company behind this plugin and MTN MoMo Gateway, are not affiliated with MTN or WooCommerce. Please keep this in mind. 😎
This is a free plugin with **Free** gateway package, upgrade to **Pro** for more features, drop us an Email to *momo-plugin@clickon.ch*

== Privacy policy ==

Please check our [Privacy policy](https://www.clickon.ch/momo-gateway-privacy-policy) and see how we are handling your private data like name, phone numbers, Email address and any other data.

== Frequently Asked Questions ==

= Do you have a demo website? =

Yes, it is here [demo.momo.clickon.ch](https://demo.momo.clickon.ch), no real payments can be done, eShop currency *EUR*

= Do you have a Live demo? =

No yet. We are working on it. However, you can check https://kivea.net for inspiration 😎 

= Which phone number the MoMo request is sent? =

We are sending the MoMo Pay request to the phone number provided on the checkout form. The format is 256XXXXXXXXX, where 256 is country code.

= How can we do real payments? =

To accept real aka Live payments you must be approved by MTN, the process is called "Go Live" you can read about it here https://momodeveloper.mtn.com/go-live
Remember it will require you do submit the KYC documents, your passport copy etc. it is quite some paper work, so please seek legal advice!

= What is the phone number format to use during the checkout? =

We are sending MoMo requests to the phone number provided during the checkout, so it must follow a certain format, for example:

* 237698936XXX - Cameroon
* 233204701XXX - Ghana
* 256700908XXX - Uganda
* 268782XXXXX - Eswatini
* 24206422XXXX - Congo B

No "+" in front and always with the country code!

= For Rwanda is it possible to use momodeveloper.mtn.com to get Sandbox primary key? =

In case you are in Rwanda you should use a dedicated portal at https://momodeveloper.mtn.co.rw, however if you
set your WooCommerce Country / State to anything but Rwanda you can use Sandbox Primary key from https://momodeveloper.mtn.com

Recently there were many reports that https://momodeveloper.mtn.co.rw Portal is not very stable, so we do advice to use https://momodeveloper.mtn.com instead.

== Screenshots ==

1. WooCommerce Shop demo purchase with MTN MoMo
2. Plugin settings, Live setup
3. Plugin settings, Sandbox setup

== Changelog ==

= 1.0.6 =
* Wordpress 6.1 support added and WooCommerce 7.1
* Add support for Nigerian Naira NGN and MTN Nigeria
* Uses Wordpress AJAX call to get payment status, more secure
* Delay checkout in Sandbox
* Checkout warning message in Sandbox

= 1.0.5 =
* Wordpress 6.0 support added
* Fixes order with floating point amounts like 0.2 USD etc.
* Fixes for Liberia to support both USD and LRD currencies

= 1.0.4 =
* Removed request count
* Better handling of Wordpress multisite instalations

= 1.0.3 =
* Fixed setup issues for AWS Lightsail
* Fixed XAF currency support in Cameroon and Congo Brazza

= 1.0.2 =
* Documentation updated
* Fixed REST callback, `permission_callback` added
* Deprecation warnings removed

= 1.0.1 =
* Tested with Wordpress 5.7
* Fixed PHP notices
* Support for Rwanda portal momodeveloper.mtn.co.rw  

= 1.0.0 =
* Our first version with MTN MoMo Live and Sandbox, Collection API is fully supported

== Upgrade Notice ==

= 1.0.1 =
* Tested with Wordpress 5.7
* Fixed PHP notices
* Support for Rwanda portal momodeveloper.mtn.co.rw 

= 1.0.0 =
* Our first version with MTN MoMo Live and Sandbox, Collection API is fully supported