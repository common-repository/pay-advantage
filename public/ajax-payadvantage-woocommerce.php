<?php
/**
 * Receives calls for credit card payments.
 */

add_action('wp_ajax_pay_advantage_wc_mark_order_as_paid', 'pay_advantage_mark_order_as_paid_handler');
add_action('wp_ajax_nopriv_pay_advantage_wc_mark_order_as_paid', 'pay_advantage_mark_order_as_paid_handler');

add_action('wp_ajax_pay_advantage_wc_mark_order_as_cancelled', 'pay_advantage_mark_order_as_cancelled_handler');
add_action('wp_ajax_nopriv_pay_advantage_wc_mark_order_as_cancelled', 'pay_advantage_mark_order_as_cancelled_handler');

add_action('wp_ajax_pay_advantage_wc_mark_order_as_failed', 'pay_advantage_mark_order_as_failed_handler');
add_action('wp_ajax_nopriv_pay_advantage_wc_mark_order_as_failed', 'pay_advantage_mark_order_as_failed_handler');

function pay_advantage_mark_order_as_paid_handler() {
	try {
		check_ajax_referer( 'pay_advantage_paid_nonce', 'security' );

		$order_id = sanitize_text_field( $_POST['orderid'] );
		if ( empty( $order_id ) ) {
			pay_advantage_write_error_to_response( 'Invalid order id.' );
			wp_die();
		}

		$payment_code = sanitize_text_field( $_POST['paymentcode'] );
		if ( empty( $payment_code ) ) {
			pay_advantage_write_error_to_response( 'Invalid payment code.' );
			wp_die();
		}

		$order = new WC_Order( $order_id );

		// The order id being completed needs to be verified against the payment to ensure the correct order is completed. If this is not the case then the merchant must investigate as it is possibly due to tampering.
		$pay_advantage_api = new Pay_Advantage_Api();
		$receipt           = $pay_advantage_api->get_payment( $payment_code );
		if ( ! isset( $receipt['ExternalID'] ) || $receipt['ExternalID'] != 'wc-' . $order_id ) {
			pay_advantage_write_error_to_response( 'Payment was not for this order.' );
			wp_die();
		}

		$paid_status = get_option( 'pay_advantage_wc_paid_status' );

		if ( $order->get_status() != 'pending' && $order->get_status() != 'failed' ) {
			pay_advantage_write_error_to_response( 'Failed to record the order as ' . $paid_status . '. Order does not have a status of pending or failed.' );
			wp_die();
		}

		$order->update_status( apply_filters( 'woocommerce_pay_advantage_process_payment_order_status', $paid_status, $order ), __( 'Payment received.', 'woocommerce' ) );
		WC()->cart->empty_cart();

		pay_advantage_send_response( array(
			'result'   => 'success',
			'redirect' => apply_filters( 'woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order )
		) );
		wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}

function pay_advantage_mark_order_as_cancelled_handler() {
	try {
		check_ajax_referer( 'pay_advantage_paid_nonce', 'security' );

		$order_id = sanitize_text_field( $_POST['orderid'] );
		if ( empty( $order_id ) ) {
			pay_advantage_write_error_to_response( 'Invalid order id.' );
			wp_die();
		}

		$order = new WC_Order( $order_id );

		$cancel_status = get_option( 'pay_advantage_wc_cancel_status' );

		if ( $order->get_status() != 'pending' && $order->get_status() != 'failed' ) {
			pay_advantage_write_error_to_response( 'Failed to record the order as ' . $cancel_status . '. Order does not have a status of pending or failed.' );
			wp_die();
		}

		$order->update_status( apply_filters( 'woocommerce_pay_advantage_process_payment_order_status', $cancel_status, $order ), __( 'Payment cancelled.', 'woocommerce' ) );

		pay_advantage_send_response( array(
			'result' => 'success'
		) );
		wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}

function pay_advantage_mark_order_as_failed_handler() {
	try {
		check_ajax_referer( 'pay_advantage_paid_nonce', 'security' );

		$order_id = sanitize_text_field( $_POST['orderid'] );
		if ( empty( $order_id ) ) {
			pay_advantage_write_error_to_response( 'Invalid order id.' );
			wp_die();
		}

		$order = new WC_Order($order_id);

		if ( $order->get_status() != 'pending' && $order->get_status() != 'failed' ) {
			pay_advantage_write_error_to_response( 'Failed to record the order as failed. Order does not have a status of pending or failed.' );
			wp_die();
		}

		$order->update_status( apply_filters( 'woocommerce_pay_advantage_process_payment_order_status', 'failed', $order ), __( 'Payment failed.', 'woocommerce' ) );

		pay_advantage_send_response( array(
			'result'   => 'success'
		) );
		wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}

?>