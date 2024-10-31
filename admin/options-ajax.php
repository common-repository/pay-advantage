<?php
add_action( 'wp_ajax_save_pay_advantage_settings_action', 'save_pay_advantage_settings_handler' );

/**
 * Action that saves the data to the options table, called by the options page.
 */
function save_pay_advantage_settings_handler() {
	try {
	    check_ajax_referer( 'pay_advantage_nonce', 'security' );

	    if ( ! is_admin() ) {
	        pay_advantage_write_error_to_response( 'Failed, user is not an admin.' );
	        wp_die();
	    }

	    $require_mobile               = filter_var( $_POST['payadvantagerequiremobile'], FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
	    $require_address              = filter_var( $_POST['payadvantagerequireaddress'], FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
	    $show_credit_card             = filter_var( $_POST['payadvantageshowbcreditcard'], FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
	    $oncharge_credit_card_fees    = filter_var( $_POST['payadvantageonchargecreditcardfees'], FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
	    $wc_oncharge_credit_card_fees = filter_var( $_POST['payadvantagewconchargecreditcardfees'], FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
	    $show_bpay                    = filter_var( $_POST['payadvantageshowbpay'], FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
	    $description                  = sanitize_text_field( $_POST['payadvantagecarddescription'] );
	    $anonymous_permission         = filter_var( $_POST['payadvantageanonymouspermission'], FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
	    $register_credit_card_button  = sanitize_text_field( $_POST['payadvantagemakepaymentbutton'] );
	    $wc_paid_status               = sanitize_text_field( $_POST['payadvantagewcpaidstatus'] );
		$wc_cancel_status             = sanitize_text_field( $_POST['payadvantagewccancelstatus'] );

	    update_option( 'pay_advantage_credit_card_description', $description );
	    update_option( 'pay_advantage_show_widget_to_users_not_logged_in', $anonymous_permission );
	    update_option( 'pay_advantage_require_mobile', $require_mobile );
	    update_option( 'pay_advantage_require_address', $require_address );
	    update_option( 'pay_advantage_show_credit_card', $show_credit_card );
	    update_option( 'pay_advantage_oncharge_credit_card_fees', $oncharge_credit_card_fees );
	    update_option( 'pay_advantage_wc_oncharge_credit_card_fees', $wc_oncharge_credit_card_fees );
	    update_option( 'pay_advantage_show_bpay', $show_bpay );
	    update_option( 'pay_advantage_make_payment_button', $register_credit_card_button );
	    update_option( 'pay_advantage_wc_paid_status', $wc_paid_status );
		update_option( 'pay_advantage_wc_cancel_status', $wc_cancel_status );

	    pay_advantage_send_response( array() );
	    wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}

function pay_advantage_create_code_code_verifier() {
    $dictionary = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~';
    $output     = '';

    for ( $i = 0; $i < 128; $i ++ ) {
        $output .= $dictionary[ random_int( 0, strlen( $dictionary ) ) ];
    }

    return $output;
}

add_action( 'wp_ajax_pay_advantage_connect_action', 'pay_advantage_connect_handler' );
function pay_advantage_connect_handler() {
	try {
	    if ( ! is_admin() ) {
	        pay_advantage_write_error_to_response( 'Not authorised.' );
	        wp_die();
	    }

	    $env = sanitize_text_field( $_POST['payadvantageenv'] );
	    update_option( 'pay_advantage_env', $env );
	    update_option( 'pay_advantage_url', $env == "live" ? PAYADV_API_URL_LIVE : PAYADV_API_URL_SANDBOX );

	    // Use a specific instance for the connection. This allows the user to manage various installations of the plugin.
	    $instance_id = get_option( 'pay_advantage_instance_id' );

	    // create a nonce state and append. Pick up nonce and compare on registration callback.
	    $nonce = wp_create_nonce( 'pay_advantage_connect_nonce' );

	    $client_id      = 'D69CB714EC1E4B9781C48D9B980FBD36';
	    $code_verifier  = pay_advantage_create_code_code_verifier();
	    $code_challenge = base64_encode( hash( 'sha256', $code_verifier, true ) );
	    update_option( 'pay_advantage_app_client_id', $client_id );
	    update_option( 'pay_advantage_app_code_verifier', $code_verifier );

	    global $wp;
	    $redirect_uri = get_site_url() . '/wp-admin/options-general.php?page=PayAdvantage';

	    // redirect.
	    pay_advantage_send_response( array(
	        'RedirectTo' => (
	            $env == "live" ? PAYADV_REGISTRATION_URL_LIVE : PAYADV_REGISTRATION_URL_SANDBOX ) .
	            "?client_id=" . urlencode( $client_id ) .
	            "&code_challenge=" . urlencode( $code_challenge ) .
	            "&code_challenge_method=S256" .
	            "&state=" . urlencode( $nonce ) .
	            "&redirect_uri=" . urlencode( $redirect_uri ) .
	            "&response_type=code" .
	            "&response_mode=query" .
	            "&instance_id=" . urlencode( $instance_id ) .
	            "&instance_name=" . rawurlencode( get_bloginfo( 'name' ) )
	    ) );
	    wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}

add_action( 'wp_ajax_pay_advantage_disconnect_action', 'pay_advantage_disconnect_handler' );
function pay_advantage_disconnect_handler() {
	try {
	    if ( ! is_admin() ) {
	        pay_advantage_write_error_to_response( 'Not authorised.' );
	        wp_die();
	    }

	    pay_advantage_send_response( ( new Pay_Advantage_Api() )->delete_refresh_token() );
	    wp_die();
	} catch (Exception $e) {
		Pay_Advantage_Api_Error_Handler::log_error( $e->getMessage() . ' ' . $e->getTraceAsString() );
		pay_advantage_write_error_to_response( $e->getMessage() );
		wp_die();
	}
}
?>