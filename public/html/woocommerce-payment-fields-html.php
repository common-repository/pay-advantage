<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function pay_advantage_woocommerce_payment_fields() {
	$is_connected = ! empty( get_option( 'pay_advantage_refresh_token' ) );

	if ( $is_connected ) {
    ?>
        <?php if ( esc_attr ( get_option( 'pay_advantage_env' ) ) != "live"): ?>
            <div>You are connected to the SANDBOX environment.</div>
            <div>To test payments, use the card number 42000000000000000 with an expiry date in the future and the CVN 000.</div>
            <div>For more information, <a href="https://help.payadvantage.com.au/hc/en-us/articles/360000408995">click here.</a></div>
        <?php else: ?>
            <script>
                jQuery( '.payment_box.payment_method_pay_advantage_gateway' ).addClass( 'pay-advantage-hidden' );
            </script>
        <?php endif; ?>
        <?php if ( ! get_option( 'pay_advantage_verified' ) ) { ?>
            <div>Your business has not been verified.</div>
        <?php } ?>
        <input type="hidden" id="pay-advantage-customer-code-wc" name="pay-advantage-customer-code-wc" />
    <?php
    } else {
	    ?>
        <h4>Not connected to Pay Advantage.</h4>
        <div>The Pay Advantage plugin must be connected before it can be used.</div>
        <?php
	}
}