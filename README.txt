=== Pay Advantage ===
Contributors: @payadvantage
Tags: credit cards, payment gateway, online payments, e-commerce
Requires at least: 5.2
Tested up to: 6.4.1
Stable tag: 3.3.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Instantly accept Visa, Mastercard and American Express from your site with fast settlement to any Australian bank account.

== Description ==

[Pay Advantage](https://www.payadvantage.com.au) is Australia's #1 solution for accepting Visa, Mastercard, American Express, BPAY and Direct Debit.

This plugin allows you to add widgets to your website so you can:

1. Instantly accept all [major credit cards](https://www.payadvantage.com.au/accept-credit-card-payments/) including Visa, Mastercard and American Express from your site
1. Automatically on-charge credit card processing fee's
1. Reduce card acceptance fraud with the latest [3D Secure v2/Payer Authentication](https://help.payadvantage.com.au/hc/en-us/articles/4411360257167) system which verifies the cardholder authenticity
1. Generate [BPAY CRNs](https://www.payadvantage.com.au/bpay-biller/) allowing you to accept BPAY payments
1. Integrates with [WooCommerce](https://woocommerce.com)

= Awards =

* CIO Top 10 APAC Payment Solution Companies (2019)

= Features =

1. Easy online application with fast account approvals and next business day settlement of most payment types
1. Australia based telephone and email support
1. Instant online credit card payments through Visa, Mastercard and American Express
1. Generate BPAY compatible CRN's allowing you to accept payments through BPAY
1. WooCommerce payments

== Frequently Asked Questions ==

= Where can I find Pay Advantage documentation and user guides? =

For help setting up and configuring Pay Advantage Wordpress Plug-in, please refer to [Getting Started](https://help.payadvantage.com.au/hc/en-us/articles/360000478635-WordPress-WooCommerce).

= What type of payments can I make from the Pay Advantage Plug-in? =

- BPAY Payments. Displays a widget to capture customer details and provide them with a unique BPAY Reference. The customer can then make payment using thier online banking and unique Reference.
- Credit Card Payments. Displays a widget to capture credit card details and then processes the payment for the amount provided (including any on-charged fees).

= Is there a testing environment available? =

The environments available are SANDBOX and LIVE.
You can use the SANDBOX environment to test your configuration and plugin. 
Our sandbox environment is a replica of our live system and uses a separate portal. (https://test.payadvantage.com.au/) To set up - Select Sanbox from the Pay Advantage admin screen and click the Connect button and login to Pay Advantage with your Sandbox account username and password.
Using a sandbox account lets you test taking payments without having to use real card data.
For further information about connecting the [SANDBOX environment](https://help.payadvantage.com.au/hc/en-us/articles/360000478635-WordPress-WooCommerce-Plugin).
Learn more about [testing the SANDBOX environment](https://help.payadvantage.com.au/hc/en-us/articles/360000408995-Sandbox-Testing).
Once you are finished testing and ready to process live credit card data, you can then select live and click the Connect button once again and login with your live credentials.

= Where can I report bugs? =

Please submit any bugs using our [request form](https://help.payadvantage.com.au/hc/en-us/requests/new).
These requests will be delivered to our technical team who will be able to priorise fixing any issues discovered.

= Where can I request new features and extensions? =

We are always excited to hear from customers and what they would like to see in our suite of products.
Click to raise a new [feature request](https://help.payadvantage.com.au/hc/en-us/community/topics/200131566-Feature-Requests)

= Will Pay Advantage work with my theme? =

The Pay Advantage plug-in will popup with a credit card input for customers. This popup is styled with a standard theme to prevent any integration issues with your WordPress instllation. If you have installed WooCommerce, it will use the WordPress theme styling for it's presentation.

= Where can I find API documentation? =

For all Pay Advantage [API documentation](https://help.payadvantage.com.au/hc/en-us/categories/360000110195-API-Reference)

== Installation ==

This plugin requires a Pay Advantage account, register online [here](https://www.payadvantage.com.au).

Once your account is activated, please follow the instructions [in this help article](https://help.payadvantage.com.au/hc/en-us/articles/360000478635-WordPress-WooCommerce).

For troubleshooting this plugin, use this [guide](https://help.payadvantage.com.au/hc/en-us/articles/360000478635-WordPress-WooCommerce-Plugin#troubleshooting).

== Changelog ==

= 3.3.1 =
* Added support for new calculation guidelines
* Increased supported Wordpress version to 6.4.1
* Increased supported WooCommerce version to 8.3.0

= 3.3.0 =
* Added support for new calculation guidelines
* Increased supported Wordpress version to 6.2.2
* Increased supported WooCommerce version to 7.7.0

= 3.2.3 =
* Added php.ini configuration validation
* Increased supported Wordpress version to 6.1.1
* Increased supported WooCommerce version to 7.0.1

= 3.2.2 =
* Improved error handling.
* Increased supported Wordpress version to 6.0.2
* Increased supported WooCommerce version to 6.8.2

= 3.2.1 =
* Increased supported Wordpress version to 6.0.0
* Increased supported WooCommerce version to 6.5.1

= 3.2.0 =
* If you have 3.1, you must upgrade to this version to continue processing payments.
* Bug fixes
* Security improvements
* Increased supported Wordpress version to 5.9.1
* Increased supported WooCommerce version to 6.3.1

= 3.1.3 =
* Added option to select status to set Woo Commerce order to when the customer closes the payment popup without paying.
* Added recording of payment failures against Woo Commerce orders.
* Bug fixes.

= 3.1.2 =
* Bug fix: Use cancel event from credit card popup to detect payment cancellation.

= 3.1.1 =
* Fix images not displaying correctly.
* Fail Woo Commerce orders when the users cancels the payment.
* Log errors that can occur while updating the status of Woo Commerce orders.
* Add option to set the Woo Commerce order status after a successful payment.

= 3.1.0 =
* Added support for payer authentication (3D Secure).
* Added option to on-charge fees in Woo Commerce. This is enabled by default.
* Updated support for OAuth authentication.
* Bug fixes.
* Security improvements.
* Added support for Wordpress 5.8
* Added support for WooCommerce 5.6

= 3.0.1 =
* Set WooCommerce order status to 'processing' on payment.

= 3.0.0 =
* Replaced existing API credentials with OAuth 2 "Connect" to simplify install activation.
* Improved error reporting and stability.
* Fixed issue causing same customer details to be registered multiple times.

= 2.1.0 =
* Fix validation of international phone numbers in Woo Commerce plug-in.
* Minor bug fixes.
* Please ensure you update to this version.

= 2.0.0 =
* Multiple updates, settings page improvements.

= 1.1.0 =
* Multiple updates, bug fixes and general improvements.
 
= 1.0.0 =
* Initial release