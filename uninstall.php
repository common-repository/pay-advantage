<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

delete_option( 'pay_advantage_env' );
delete_option( 'pay_advantage_url' );
delete_option( 'pay_advantage_instance_id' );
delete_option( 'pay_advantage_refresh_token' );
delete_option( 'pay_advantage_access_token' );
delete_option( 'pay_advantage_access_token_expiry' );
delete_option( 'pay_advantage_require_mobile' );
delete_option( 'pay_advantage_require_address' );
delete_option( 'pay_advantage_require_country' );
delete_option( 'pay_advantage_site_url' );
delete_option( 'pay_advantage_show_bpay' );
delete_option( 'pay_advantage_show_credit_card' );
delete_option( 'pay_advantage_credit_card_description' );
delete_option( 'pay_advantage_error_logging' );
delete_option( 'pay_advantage_show_widget_to_users_not_logged_in' );
delete_option( 'pay_advantage_db_version' );

unregister_widget( 'pay_advantage_bpay_widget' );

?>