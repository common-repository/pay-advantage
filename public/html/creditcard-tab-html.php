<?php
/**
 * Contains html for the credit card tab.
 */
function pay_advantage_credit_card_html() {
	$is_connected = ! empty( get_option( 'pay_advantage_refresh_token' ) );

	if ( $is_connected ) {
   ?>
        <button id="pay-advantage-make-a-payment" type="button" ><?php echo esc_html( get_option( 'pay_advantage_make_payment_button' ) ); ?></button>
        <div id="payAdvantageCreditCardTab" class="payAdvantageOverlayTabContent" style="display: none">
        <div id="pay-advantage-make-a-payment-messages" style="display: none"></div>
        <div id="pay-advantage-make-a-payment-receipt" style="display: none" >
            <div class="pb-4">
                <div class="success-tick-image">
                    <img src="<?php echo payadvantage_plugin_url('public/images/success_tick.svg' ); ?>" border="0" />
                </div>
                <h4 class="text-center">Thank you for your payment using <a href="https://www.payadvantage.com.au" target="_blank">Pay Advantage</a>.</h4>
            </div>
        </div>
         <form class="wordpress-ajax-form pay-advantage-widget" method="post" style="position: relative; display: none">
            <div>
                <?php if (esc_attr(get_option('pay_advantage_env')) != "live"):?>
                    <h4 style="color:red">Sandbox environment</h4>
                <?php endif;?>
                <h4>
                  A bit about you
                </h4>

                <input type="hidden" id="pay-advantage-customer-code-regcc" >

                <p>
                    <label class="pa-label" for="payAdvantageCustomerFirstNameCC">First Name <span class="required">*</span></label>
                    <input id="payAdvantageCustomerFirstNameCC" name="customerFirstName" type="text" >
                    <span class="PayAdvantageError"></span>
                </p>

                <p>
                    <label class="pa-label" for="payAdvantageCustomerLastNameCC">Last Name <span class="required">*</span></label>
                    <input id="payAdvantageCustomerLastNameCC" name="customerLastName" type="text" >
                    <span class="PayAdvantageError"></span>
                </p>
                
                <p>
                    <label class="pa-label" for="payAdvantageCustomerEmailCC">Email <span class="required">*</span></label>
                    <input id="payAdvantageCustomerEmailCC" name="customerEmailCC" type="text" >
                    <span class="PayAdvantageError"></span>
                </p>

                <p>
                    <label class="pa-label" for="payAdvantageMobileNumberCC">Mobile <span class="required">*</span></label>
                    <input maxlength="15" id="payAdvantageMobileNumberCC" name="mobileNumber" type="text" >
                    <span class="PayAdvantageError"></span>
                </p>
                
                <h4>Payment details</h4>
                <p>
                    <label class="pa-label" for="payAdvantageDescriptionCC">Description <span class="required">*</span></label>
                    <input id="payAdvantageDescriptionCC" name="DescriptionCC" type="text" maxlength="50"
                        value="<?php echo get_option( 'pay_advantage_credit_card_description' ) ?>" >
                    <span class="PayAdvantageError"></span>
                </p>
                <p>
                    <label class="pa-label" for="payAdvantageAmountCC">Amount <span class="required">*</span></label>
                    <input id="payAdvantageAmountCC" name="AmountCC" type="text" >
                    <span class="PayAdvantageError"></span>
                </p>

                <div id="pay-advantage-register-credit-card-capture-wc-iframe-container" style="display: none">
                    <iframe class="pay-advantage-credit-card-capture" id="pay-advantage-register-credit-card-capture-wc-iframe" src="" ></iframe>
                </div>

            </div>

            <br>
            <button class="button" type="button" id="pay-advantage-make-payment" >Pay</button>
            <div class="PayAdvantageError" ></div>
        </form> 
    </div>
		<?php
	} else {
		?>
        <h4>Not connected to Pay Advantage.</h4>
        <div>The Pay Advantage plugin must be connected before it can be used.</div>
		<?php
	}
}