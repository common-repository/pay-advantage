<?php
require_once plugin_dir_path( __FILE__ ) . 'options-html-render.php';

function pay_advantage_options_page() {
	$is_connected      = ! empty( get_option( 'pay_advantage_refresh_token' ) );
	$connection_errors = null;

	if ( ! $is_connected ) {
		if ( isset( $_GET["code"] ) ) {
			$state = $_GET["state"];

			if ( ! wp_verify_nonce( $state, 'pay_advantage_connect_nonce' ) ) {
				$connection_errors = array( 'Already authorised.' );
			} else if ( ! is_admin() ) {
				$connection_errors = array( 'Not authorised.' );
			} else {
				// Check the nonce is the one recorded and destroy it.
				// Record the refresh token, access token and access token expiry.
				$result = ( new Pay_Advantage_Api() )->get_refresh_token( $_GET["code"] );
				if ( pay_advantage_has_messages( $result ) ) {
					$connection_errors = $result['Messages'];
				} else {
					// Redirect back to the page to remove the oauth values from the url. These will cause problems if the page does another postback.
					exit( wp_redirect( admin_url( 'options-general.php?page=PayAdvantage' ) ) );
				}
			}
		} else if ( isset ( $_POST["error"] ) ) {
			if ( ! is_admin() ) {
				$connection_errors = array( 'Not authorised.' );
			} else {
				$connection_errors = array( sanitize_text_field( $_GET["error_description"] ) );
			}
		}
	}

	pay_advantage_options_page_render( $is_connected, $connection_errors, get_option( 'pay_advantage_env' ), get_option( 'pay_advantage_verified' ) );
}
?>