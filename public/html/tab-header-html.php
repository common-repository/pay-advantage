<?php

function pay_advantage_tab_header_html() {
	?>
    <div class="tab">
        <button class="tablinks" onclick="payAdvantagePaymentType(event, 'payAdvantageBPayTab')"
                id="payAdvantageBPayButton">BPAY Reference
        </button>
        <button class="tablinks" onclick="payAdvantagePaymentType(event, 'payAdvantageCreditCardTab')"
                id="payAdvantageCreditCardButton">Credit Card
        </button>
    </div>
	<?php
}

?>