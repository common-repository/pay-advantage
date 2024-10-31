<?php
/**
 * This file is for the WooCommerce payment gateway.
 */

include_once( plugin_dir_path( __FILE__ ) . '../shared/payadvantage-api-error-handler.php' );
include_once( plugin_dir_path( __FILE__ ) . 'cls-payadvantage-validator.php' );
include_once( plugin_dir_path( __FILE__ ) . 'html/woocommerce-payment-fields-html.php' );

add_action( 'plugins_loaded', 'pay_advantage_gateway_handler' );

function pay_advantage_gateway_handler() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	class Pay_Advantage_Gateway extends WC_Payment_Gateway {
		/**
		 * This function sets up variables for WooCommerce, it tells Woo the inner workings of the class
		 */
		public function __construct() {
			$this->id                 = 'pay_advantage_gateway';
			$this->icon               = payadvantage_plugin_url( 'assets/pa_logo-30.png' );
			$this->has_fields         = true;
			$this->method_title       = 'Pay Advantage';
			$this->title              = 'Credit Card';
			$this->method_description = 'Take credit card payments using Pay Advantage.';
			$this->init_form_fields();
			$this->init_settings();
			$this->pay_button_id = 'test';

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
            // Use a higher priority to load the CSS and JS after the theme and WooCommerce.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
		}

		/**
		 * This method is connected to the has_fields method. It contains html for collecting data in the checkout window
		 * like credit card info.
		 */
		function payment_fields() {
			echo pay_advantage_woocommerce_payment_fields();
		}

		/**
		 * This is validating fields in the payment gateway.
		 */
		public function validate_fields() {
			// If the plugin in not connected, return an error and leave.
			// The credit card fields are not presented to the user when the plugin is disconnected.
			if ( ! get_option( 'pay_advantage_refresh_token' ) ) {
				wc_add_notice( __( 'Not connected to Pay Advantage.', 'woocommerce' ), 'error' );

				return;
			}

			if ( ! get_option( 'pay_advantage_verified' ) ) {
				wc_add_notice( __( 'Your business has not been verified. Please complete your pending verification(s).', 'woocommerce' ), 'error' );
			}

			if ( empty( $_POST['pay-advantage-customer-code-wc'] ) ) {
				wc_add_notice( __( 'Invalid customer.', 'woocommerce' ), 'error' );
			}
		}

		/**
		 * This is the meat and bones of the WooCommerce integration. It picks up information from the place order button and processes it.
		 */
		function process_payment( $order_id ) {
			$pay_advantage_api = new Pay_Advantage_Api();

			if ( ! get_option( 'pay_advantage_verified' ) ) {
				return array( 'result' => 'failed' );
			}

			$order      = new WC_Order( $order_id );
			$order_data = $order->get_data();

			if ( $order_data == null ) {
				return array( 'result' => 'error' );
			}

			$billing = $order_data['billing'];

			if ( $billing == null ) {
				return array( 'result' => 'error' );
			}

			$phpiniPrecision = ini_get('serialize_precision');
			if ( $phpiniPrecision !== "-1" ) {
				Pay_Advantage_Api_Error_Handler::log_error( 'Pay Advantage plugin has detected issues with the current PHP settings. Please change the PHP serialize_precision setting to -1 to ensure Pay Advantage accurately calculates fees.' );
			}

			$customer_code = sanitize_text_field( $_POST['pay-advantage-customer-code-wc'] );

			$iframe_url_response = $pay_advantage_api->get_cc_iframe_url( $customer_code, $order_data['total'], "Order: " . $order_id, 'wc-' . $order_id, get_option( 'pay_advantage_wc_oncharge_credit_card_fees' ) );
			if ( ( isset( $iframe_url_response['Status'] ) && $iframe_url_response['Status'] != 200 ) ||
			     pay_advantage_has_messages( $iframe_url_response ) ) {
				Pay_Advantage_Api_Error_Handler::log_error( json_encode( $iframe_url_response ) );

				return array( 'result' => 'failed' );
			}

			$order->add_meta_data( 'pay_advantage_text', 'Payment requested' );

			return array(
				'result'   => 'success',
				'redirect' => 'payadvantage://' . rawurlencode(
						json_encode(
							array(
								'iframeUrl'  => $iframe_url_response['IFrameUrl'],
								'payment'    => array(
									'amount'               => $order_data['total'],
									'description'          => "Order: " . $order_id,
									'externalID'           => 'wc-' . $order_id,
									'customer'             => array(
										'code' => $customer_code
									),
									'expectedOnchargedFee' => $onchargedFees
								),
								'cardHolder' => array(
									'firstName' => $order_data['billing']['first_name'],
									'lastName'  => $order_data['billing']['last_name'],
									'email'     => $order_data['billing']['email'],
									'address'   => array(
										'addressLine1' => $order_data['billing']['address_1'] . ' ' . $order_data['billing']['address_2'],
										'city'         => $order_data['billing']['city'],
										'country'      => $order_data['billing']['country'],
										'state'        => $order_data['billing']['state'],
										'postCode'     => $order_data['billing']['postcode']
									)
								),
								'orderId'    => $order_id,
								'paidNonce'  => wp_create_nonce( 'pay_advantage_paid_nonce' )
							)
						) )
			);
		}

		/**
		 * Frontend scripts
		 */
		public function enqueue_scripts() {
			$api_url = get_option( 'pay_advantage_url' );

			wp_register_style( 'pay_advantage_css', payadvantage_plugin_url( 'public/css/payadvantage.css' ), array(), PayAdvantagePluginVersion );
			wp_enqueue_style( 'pay_advantage_css' );

			wp_register_style( 'pay_advantage_cc_iframe', $api_url . '/creditcardcapture.css', array(), PayAdvantagePluginVersion );
			wp_enqueue_style( 'pay_advantage_cc_iframe' );

			wp_register_script( 'jquery-blockui', payadvantage_plugin_url( 'public/js/jquery-blockui/jquery.blockUI.min.js' ), array( 'jquery' ), '2.70', true );
			wp_register_script(
				'pay_advantage_common',
				payadvantage_plugin_url( 'public/js/common.js' ),
				array(
					'jquery',
					'jquery-blockui'
				),
				PayAdvantagePluginVersion );
			wp_register_script(
				'pay_advantage_customer',
				payadvantage_plugin_url( 'public/js/customer.js' ),
				array(
					'jquery',
					'pay_advantage_customer'
				),
				PayAdvantagePluginVersion );
			wp_register_script(
				'pay_advantage_credit_card_payment',
				payadvantage_plugin_url( 'public/js/credit-card-payment.js' ),
				array(
					'jquery',
					'pay_advantage_common'
				),
				PayAdvantagePluginVersion );
			wp_register_script( 'pay_advantage_cc_iframe', $api_url . '/creditcardcapture.js', array(), PayAdvantagePluginVersion );
			wp_register_script(
				'pay_advantage_credit_card_payment-wc',
				payadvantage_plugin_url( 'public/js/credit-card-payment-wc.js' ),
				array(
					'pay_advantage_common',
					'pay_advantage_credit_card_payment',
					'pay_advantage_cc_iframe',
					'jquery'
				),
				PayAdvantagePluginVersion );


			// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
			wp_localize_script(
				'pay_advantage_credit_card_payment-wc',
				'pay_advantage_ajax_object',
				array(
					'pay_advantage_ajax_url'        => admin_url( 'admin-ajax.php' ),
					'pay_advantage_require_mobile'  => sanitize_text_field( get_option( 'pay_advantage_require_mobile' ) ),
					'pay_advantage_require_address' => sanitize_text_field( get_option( 'pay_advantage_require_address' ) ),
					'pay_advantage_nonce'           => wp_create_nonce( 'pay_advantage_nonce' )
				) );

			//Activates the script
			wp_enqueue_script( 'pay_advantage_credit_card_payment-wc' );
		}
	}
}

/**
 * This is required as per WooCommerce documentation.
 * It lists methods into an array for use by parent class.
 */
function Pay_Advantage_Gateway( $methods ) {
	$methods[] = 'Pay_Advantage_Gateway';

	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'Pay_Advantage_Gateway' );
?>