<?php
/**
 * Contains html for the BPay tab.
 */
function pay_advantage_bpay_tab_html() {
	$is_connected = ! empty( get_option( 'pay_advantage_refresh_token' ) );

	if ( $is_connected ) {
		?>
        <div id="payAdvantageBPayTab" class="payAdvantageOverlayTabContent">
            <div id="payAdvantageBillerSuccess" style="display: none;">
                <div class="pb-4">
                    <p>Thank you <span id="payAdvantageCustomerName"></span> for registering with PayAdvantage. Please copy the following details:</p>
                    <div class="BPayBlock">
                        <div class="BPayLogoVert"></div>
                        <div class="BPayDetails">
                            <div class="BillerCode">
                                Biller Code: <span id="payAdvantageBillerCode"></span>
                            </div>
                            <div class="BPayRef">
                                Ref: <span id="payAdvantageBPayRef"></span>
                            </div>
                        </div>
                    </div>
                    <small>Unsure how to make a payment? <a href="https://www.bpay.com.au/Personal/Pay-bills.aspx" target="_blank">click here.</a></small>
                </div>
            </div>
            <form class="wordpress-ajax-form pay-advantage-widget" method="post" style="position: relative">
                <div>
                    <?php if (esc_attr(get_option('pay_advantage_env')) != "live"):?>
                        <h4 style="color:red">Sandbox environment</h4>
                    <?php endif;?>
                    
                    <p>
                        <label class="pa-label" for="payAdvantageCustomerFirstNamePABPAY">First Name <span class="required">*</span></label>
                        <input id="payAdvantageCustomerFirstNamePABPAY" name="customerFirstNamePABPAY" type="text" />
                        <span class="PayAdvantageError"></span>
                    </p>
                    
                    <p>
                        <label class="pa-label" for="payAdvantageCustomerLastNamePABPAY">Last Name <span class="required">*</span></label>
                        <input id="payAdvantageCustomerLastNamePABPAY" name="customerLastNamePABPAY" type="text" />
                        <span class="PayAdvantageError"></span>
                    </p>

                    <p>
                        <label class="pa-label" for="payAdvantageCustomerEmailPABPAY">Email <span class="required">*</span></label>
                        <input id="payAdvantageCustomerEmailPABPAY" name="customerEmailPABPAY"  type="text" />
                        <span class="PayAdvantageError"></span>
                    </p>

                    <div id="payAdvantageRequiredMobile">
                        <p>
                            <label class="pa-label" for="payAdvantageMobileNumberPABPAY">Mobile <span class="required">*</span></label>
                            <input maxlength="15" id="payAdvantageMobileNumberPABPAY" name="mobileNumberPABPAY" type="text" />
                            <span class="PayAdvantageError"></span>
                        
                        </p>
                    </div>
                    <div id="payAdvantageRequiredAddress">

                        <p>
                            <label class="pa-label" for="payAdvantageStreetPABPAY">Street <span class="required">*</span></label>
                            <input id="payAdvantageStreetPABPAY" name="streetPABPAY" type="text" />
                            <span class="PayAdvantageError"></span>
                        </p>
                    
                        <p>
                            <label class="pa-label" for="payAdvantageSuburbPABPAY">Suburb <span class="required">*</span></label>
                            <input id="payAdvantageSuburbPABPAY" name="suburbPABPAY" type="text" />
                            <span class="PayAdvantageError"></span>
                        </p>

                        <p>
                            <label class="pa-label" for="payAdvantageStatePABPAY">State <span class="required">*</span></label>
                            <input id="payAdvantageStatePABPAY" name="statePABPAY" type="text" />
                            <span class="PayAdvantageError"></span>
                        </p>
                    
                        <p>
                            <label class="pa-label" for="payAdvantagePostcodePABPAY">Postcode <span class="required">*</span></label>
                            <input id="payAdvantagePostcodePABPAY" name="postcodePABPAY" type="text" />
                            <span class="PayAdvantageError"></span>
                        </p>
                    </div>                                
                </div>
                <br>
                <button class="button" id="pay-advantage-register-bpay" type="button">Register</button>
                <div class="PayAdvantageError"></div>
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