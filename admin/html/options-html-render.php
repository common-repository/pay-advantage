<?php
function pay_advantage_options_page_render( $is_connected, $connection_errors, $pay_advantage_env, $is_verified ) {
	?>
    <div>
        <h1>Pay Advantage Settings</h1>
        <div id="payAdvantageNotice" class="updated notice" style="display: none;">
            <p></p>
        </div>

		<?php
		if ( isset( $connection_errors ) || ( $is_connected && ! $is_verified ) ) {
			?>
            <div id="payAdvantageErrorNotice" class="error notice"> <?php
				if ( isset( $connection_errors ) ) {
					foreach ( $connection_errors as $connection_error ) {
						echo "<p>" . htmlentities( $connection_error ) . "</p>";
					}
				}

				if ( $is_connected && ! $is_verified ) {
					echo "<p>Your business has not been verified. You will not be able to take payments or create BPAY references for customers. Please complete your pending verification(s).</p>";
				}
				?> </div> <?php
		}
		?>
        <?php 
            $phpiniPrecision = ini_get('serialize_precision');
            if ( $phpiniPrecision !== "-1" ) {
                ?>
                    <div id="payAdvantageErrorNotice" class="error notice">
                        <p>This plugin has detected issues with the current PHP settings. Please change the PHP <span style="font-weight:bold;">serialize_precision</span> setting to "-1” to ensure Pay Advantage accurately calculates fees.</p>
                        <p>The current setting value is: <?php echo($phpiniPrecision) ?></p>
                    </div>
                <?php
            }
        ?>

        <h2 class="title">Connect to your Pay Advantage Account</h2>
        <p>Connect to your Pay Advantage Sandbox account when testing your site and then connect to your Live Pay
            Advantage account when you're ready to publish your website.</p>

        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label>Environment</label>
                </th>
                <td>
					<?php
					if ( $is_connected ) {
						echo 'Connected to ' . ( esc_attr( $pay_advantage_env ) == "live" ? "Live" : "Sandbox" );
					} else { ?>
                        <input class="regular-text" type="radio" name="payAdvantageEnv"
                               value="sandbox" <?php echo esc_attr( $pay_advantage_env ) != "live" ? "checked" : ""; ?>/> Sandbox
                        <input class="regular-text" type="radio" name="payAdvantageEnv"
                               value="live" <?php echo esc_attr( $pay_advantage_env ) == "live" ? "checked" : ""; ?>/> Live
					<?php } ?>
                </td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td>
					<?php if ( $is_connected ) { ?>
                        <button class="button" type="button" onclick="payAdvantageDisconnect()">Disconnect</button>
					<?php } else { ?>
                        <button class="button" type="button" onclick="payAdvantageConnect()">Connect</button>
					<?php } ?>
                </td>
            </tr>
            </tbody>
        </table>
        <hr>

        <form method="post" class="wordpress-ajax-form">
			<?php settings_fields( 'payadvantage_options_group' ); ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>General</th>
                    <td>
                        <label for="payAdvantageAnonymousPermission">
                            <input id="payAdvantageAnonymousPermission" type="checkbox"/>
                            Show widget to users not logged in
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>BPAY Reference Widget</th>
                    <td>
                        <fieldset>
                            <label for="payAdvantageShowBPayTab">
                                <input id="payAdvantageShowBPayTab" type="checkbox"/>
                                Enable widget
                            </label>
                            <br>
                            <label for="payAdvantageRequireMobileNumber">
                                <input id="payAdvantageRequireMobileNumber" type="checkbox"/>
                                Require a mobile for registrations
                            </label>
                            <br>
                            <label for="payAdvantageRequireAddress">
                                <input id="payAdvantageRequireAddress" type="checkbox"/>
                                Require the customer's address for registrations
                            </label>
                        </fieldset>

                    </td>
                </tr>
                <tr>
                    <th>Credit Card Payment Widget</th>
                    <td>
                        <fieldset>
                            <label for="payAdvantageShowCreditCardTab">
                                <input id="payAdvantageShowCreditCardTab" type="checkbox"/>
                                Enable widget
                            </label>
                            <br>
                            <label for="payadvantageonchargecreditcardfees">
                                <input id="payadvantageonchargecreditcardfees" type="checkbox"/>
                                On charge fees
                            </label>
                            <br>
                            <label for="payAdvantageCreditCardDescription">
                                Credit Card description (required for Credit Card payments)
                                <br/>
                                <input maxlength="20" class="regular-text" type="text"
                                       id="payAdvantageCreditCardDescription" name="payAdvantageCreditCardDescription"
                                       value="<?php echo esc_attr( get_option( 'pay_advantage_credit_card_description' ) ); ?>"/>
                            </label>
                            <br/>
                            <label for="payAdvantageMakePaymentButton">Make Payment button text</label>
                            <br/>
                            <input maxlength="30" id="payAdvantageMakePaymentButton"
                                   name="payAdvantageMakePaymentButton" class="regular-text" type="text"
                                   value="<?php echo esc_attr( get_option( 'pay_advantage_make_payment_button' ) ); ?>"/>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th>Woo Commerce Payments</th>
                    <td>
                        <fieldset>
                            <label for="payadvantagewconchargecreditcardfees">
                                <input id="payadvantagewconchargecreditcardfees" type="checkbox"/>
                                On charge fees
                            </label>

                            <br/>
                            <label for="payAdvantagewcpaidstatus">Status to set order to after successful payment</label>
                            <br/>
                            <select id="payadvantagewcpaidstatus" name="payadvantagewcpaidstatus">
                                <option value="processing" <?php if ( get_option( 'pay_advantage_wc_paid_status' ) == 'processing' ) {
									echo 'selected';
								} ?>>Processing
                                </option>
                                <option value="completed" <?php if ( get_option( 'pay_advantage_wc_paid_status' ) == 'completed' ) {
									echo 'selected';
								} ?>>Completed
                                </option>
                            </select>

                            <br/>
                            <label for="payadvantagewccancelstatus">Status to set order to after user cancels payment</label>
                            <br/>
                            <select id="payadvantagewccancelstatus" name="payadvantagewccancelstatus">
                                <option value="pending" <?php if ( get_option( 'pay_advantage_wc_cancel_status' ) == 'pending' ) {
			                        echo 'selected';
		                        } ?>>Pending Payment
                                </option>
                                <option value="failed" <?php if ( get_option( 'pay_advantage_wc_cancel_status' ) == 'failed' ) {
			                        echo 'selected';
		                        } ?>>Failed
                                </option>
                                <option value="cancelled" <?php if ( get_option( 'pay_advantage_wc_cancel_status' ) == 'cancelled' ) {
		                            echo 'selected';
	                            } ?>>Cancelled
                                </option>
                            </select>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <td><?php submit_button(); ?></td>
                </tr>
                </tbody>
            </table>
        </form>
        <h3>Error Log</h3>
        <div>
            <textarea readonly id="payAdvantageErrorList"
                      style=" width:80% !important; height:500px !important"><?php echo get_option( 'pay_advantage_error_logging' ); ?></textarea>
        </div>
        <p class="pay-advantage-w-100 pay-advantage-text-right">Plugin
            version <?php echo PayAdvantagePluginVersion; ?></p>
    </div>
<?php } ?>