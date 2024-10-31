<?php
class Pay_Advantage_Activator {

	/**
	 * @since	1.0.0
	 */
	public static function activate() {
		add_option( 'pay_advantage_db_version', '3.1.1' );
		add_option( 'pay_advantage_env', 'sandbox' );
		add_option( 'pay_advantage_url', PAYADV_API_URL_SANDBOX );
		add_option( 'pay_advantage_instance_id', uniqid() );
		add_option( 'pay_advantage_refresh_token', '' );
		add_option( 'pay_advantage_access_token', '' );
		add_option( 'pay_advantage_access_token_expiry', '' );
		add_option( 'pay_advantage_require_mobile', 1 );
		add_option( 'pay_advantage_require_address', 0 );
		add_option( 'pay_advantage_require_country', 0 );
		add_option( 'pay_advantage_show_bpay', 1 );
		add_option( 'pay_advantage_show_credit_card', 1 );
		add_option( 'pay_advantage_credit_card_description', 'Pay Advantage' );
		add_option( 'pay_advantage_error_logging', '' );
		add_option( 'pay_advantage_show_widget_to_users_not_logged_in', 0 );
		add_option( 'pay_advantage_wc_oncharge_credit_card_fees', '1' );
		add_option( 'pay_advantage_wc_paid_status', 'processing' );
		add_option( 'pay_advantage_wc_cancel_status', 'pending' );
		add_option( 'pay_advantage_make_payment_button', 'Make a payment' );
		
		// ensure instance_id is not empty
		$instance_id = get_option( 'pay_advantage_instance_id' );
		if ( empty( $instance_id )) {
			update_option( 'pay_advantage_instance_id', uniqid() );
		}
	}
}
?>