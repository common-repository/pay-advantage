<?php
/**
 * Receives incoming api calls for registering consumers for BPay
 */
include_once( plugin_dir_path( __FILE__ ) . '/cls-payadvantage-json-converter.php' );
include_once( plugin_dir_path( __FILE__ ) . 'cls-payadvantage-validator.php' );

//Added both actions for logged in and guest consumers
add_action( 'wp_ajax_pay_advantage_create_customer', 'pay_advantage_create_customer_handler' );
add_action( 'wp_ajax_nopriv_pay_advantage_create_customer', 'pay_advantage_create_customer_handler' );

/**
 * Extracts the data from the widget api call and sends it to pay advantage.
 */
function pay_advantage_create_customer_handler() {
	try {
		check_ajax_referer( 'pay_advantage_nonce', 'security' );

		if ( ! get_option( 'pay_advantage_refresh_token' ) ) {
			pay_advantage_write_error_to_response( 'Not connected to Pay Advantage.' );
			wp_die();
		}

		if ( ! get_option( 'pay_advantage_verified' ) ) {
			pay_advantage_write_error_to_response( 'Your business has not been verified. Please complete your pending verification(s).' );
			wp_die();
		}

		$validator         = new Pay_Advantage_Validator();
		$pay_advantage_api = new Pay_Advantage_Api();

		$create_bpay = isset( $_POST['payadvantagecreatebpay'] ) ? $_POST['payadvantagecreatebpay'] === 'true' : 0;

		$customer_data = Pay_Advantage_Data_Mapper::get_customer_data_from_post();

		$validation_messages = $validator->validate_customer_register( $customer_data, $create_bpay );

		if ( count( $validation_messages ) > 0 ) {
			pay_advantage_write_error_to_response( $validation_messages );
			wp_die();
		}

		pay_advantage_send_response( $pay_advantage_api->create_customer( $customer_data, $create_bpay ) );
		wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}
?>