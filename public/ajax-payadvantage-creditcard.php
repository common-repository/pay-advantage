<?php
/**
 * Receives calls for credit card payments.
 */

include_once( plugin_dir_path( __FILE__ ) . 'cls-payadvantage-validator.php' );

//Added both actions for logged in and guest consumers
add_action('wp_ajax_pay_advantage_credit_card', 'pay_advantage_credit_card_handler');
add_action('wp_ajax_nopriv_pay_advantage_credit_card', 'pay_advantage_credit_card_handler');

//Extracts the data from the widget api call and sends it to pay advantage.
function pay_advantage_credit_card_handler() {
	try {
		$pay_advantage_api = new Pay_Advantage_Api();

		check_ajax_referer( 'pay_advantage_nonce', 'security' );

		if ( current_user_can( 'read' ) == 0 && get_option( 'pay_advantage_show_widget_to_users_not_logged_in' ) == "0" ) {
			pay_advantage_write_error_to_response( 'Please login to use this service.' );
			wp_die();
		}

		if ( ! get_option( 'pay_advantage_refresh_token' ) ) {
			pay_advantage_write_error_to_response( 'Not connected to Pay Advantage.' );
			wp_die();
		}

		if ( ! get_option( 'pay_advantage_verified' ) ) {
			pay_advantage_write_error_to_response( 'Your business has not been verified. Please complete your pending verification(s).' );
			wp_die();
		}

		$payment_amount            = sanitize_text_field( $_POST['paymentamount'] );
		$payment_description       = sanitize_text_field( $_POST['paymentdescription'] );
		$customer_code             = sanitize_text_field( $_POST['customercode'] );

		$validation_messages = array();

		if ( empty( $payment_amount ) ) {
			array_push( $validation_messages, 'Amount is required.' );
		} else if ( $payment_amount < 1 || $payment_amount > 99999.00 ) {
			array_push( $validation_messages, 'Enter an amount between $1.00 and $99,999.00.' );
		}

		if ( empty( $payment_description ) ) {
			array_push( $validation_messages, 'Description is required.' );
		}

		if ( empty( $customer_code ) ) {
			array_push( $validation_messages, 'Customer is required.' );
		}

		if ( count( $validation_messages ) > 0 ) {
			pay_advantage_write_error_to_response( $validation_messages );
			wp_die();
		}

		$iframe_url_response = $pay_advantage_api->get_cc_iframe_url( $customer_code, $payment_amount, $payment_description, null, get_option( 'pay_advantage_oncharge_credit_card_fees' ) );
		if ( isset( $iframe_url_response['Status'] ) && $iframe_url_response['Status'] != 200 ) {
			pay_advantage_write_error_to_response( 'Failed to prepare the payment.' );
			wp_die();
		}

		pay_advantage_send_response( array(
			"IFrameUrl"            => $iframe_url_response['IFrameUrl']
		) );
		wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}

?>