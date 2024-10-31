(function($, window, document, payAdvantage) {
    var $startPaymentButton = null;
    var $makePaymentButton = null;
    var $firstName = null;
    var $lastName = null;
    var $email = null;
    var $phone = null;
    var $description = null;
    var $amount = null;
    var $customerCode = null;
    var $container = null;
    var $messages = null;
    var $form = null;
    var $receipt = null;

    /**
     * Creates a customer api object.
     */
    function createCustomerObject() {
        return {
            'payadvantagefirstname': $firstName.val(),
            'payadvantagelastname': $lastName.val(),
            'payadvantageemail': $email.val(),
            'payadvantagemobile': $phone.val()
        };
    }

    /**
     * Validates the first name.
     * @return {boolean}
     */
    function validateFirstName() {
        var value = $firstName.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'First name is required.';
        } else if (!payAdvantage.common.isLettersOnly(value)) {
            error = 'First name is invalid.';
        }

        payAdvantage.common.setErrorMessage($firstName, error);
        return error === '';
    }

    /**
     * Validates the last name.
     * @return {boolean}
     */
    function validateLastName() {
        var value = $lastName.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Last name is required.';
        } else if (!payAdvantage.common.isLettersOnly(value)) {
            error = 'Last name is invalid.';
        }

        payAdvantage.common.setErrorMessage($lastName, error);
        return error === '';
    }

    /**
     * Validates the email address.
     * @return {boolean}
     */
    function validateEmail() {
        var value = $email.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Email is required.';
        } else if (!payAdvantage.common.isValidEmail(value)) {
            error = 'Email is invalid.';
        }

        payAdvantage.common.setErrorMessage($email, error);
        return error === '';
    }

    /**
     * Validates the phone number.
     * @return {boolean}
     */
    function validatePhone() {
        var value = $phone.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Mobile is required.';
        } else if (!payAdvantage.common.isValidPhone(value)) {
            error = 'Mobile is invalid.';
        }

        payAdvantage.common.setErrorMessage($phone, error);
        return error === '';
    }

    /**
     * Validates the payment description.
     * @return {boolean}
     */
    function validateDescription() {
        var value = $description.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Description is required.';
        }

        payAdvantage.common.setErrorMessage($description, error);
        return error === '';
    }

    /**
     * Validates the payment amount.
     * @return {boolean}
     */
    function validateAmount() {
        var value = $amount.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Amount is required.';
        } else if (!payAdvantage.common.isCurrency(value)) {
            error = 'Amount is invalid.'
        } else {
            var amount = parseFloat(value);
            if (amount < 1 || amount > 99999) {
                error = 'Enter an amount between $1.00 and $99,999.00.'
            }
        }

        payAdvantage.common.setErrorMessage($amount, error);
        return error === '';
    }

    /**
     * Checks the forms validity.
     * @return {boolean}
     */
    function checkValidity() {
        return [
            validateFirstName(),
            validateLastName(),
            validatePhone(),
            validateEmail(),
            validateDescription(),
            validateAmount()
        ].every(function (result) {
            return result;
        });
    }

    /**
     * Registers a customer.
     * @return {Promise}
     */
    function registerCustomer() {
        return payAdvantage.customer.register(createCustomerObject())
            .then(function (result) {
                $customerCode.val(result.Code);
                return result.Code;
            });
    }

    /**
     * Makes a payment.
     */
    function onMakePaymentClicked() {
        if (!checkValidity()) {
            return;
        }

        payAdvantage.common.block($container);

        const paymentDetails = {
            customer: { 'code': $customerCode.val() },
            amount: $amount.val(),
            description: $description.val()
        };

        const cardHolderDetails = {
            firstName: $firstName.val(),
            lastName: $lastName.val(),
            email: $email.val()
        }

        Promise.resolve()
            .then(function () {
                // Register the customer, if not already done so.
                if ($customerCode.val() === '') {
                    return registerCustomer();
                }
            })
            .then(function (result) {
                paymentDetails.customer.code = $customerCode.val();

                return payAdvantage.common.postAjax('pay_advantage_credit_card',
                    {
                        'customercode': paymentDetails.customer.code,
                        'paymentamount': paymentDetails.amount,
                        'paymentdescription': paymentDetails.description
                    });
            })
            .then(function (result) {
                payAdvantage.initialiseCreditCardCapture();
                payAdvantage.creditCardCapture.addEventListener('closed', onCreditCardDialogClosedHandler);
                payAdvantage.creditCardCapture.addEventListener('paid', onPaymentReceivedHandler);
                paymentDetails.expectedOnchargedFee = result.ExpectedOnchargedFee;
                return payAdvantage.creditCardCapture.show(result.IFrameUrl, cardHolderDetails);
            })
            .then(function () {
                payAdvantage.common.unblock($container);
            })
            .catch(function (result) {
               payAdvantage.common.unblock($container);
               payAdvantage.common.setErrorMessage($makePaymentButton, result.message);
            });
    }

    function onPaymentReceivedHandler() {
        $form.hide();
        $receipt.show();
    }

    function onCreditCardDialogClosedHandler() {
        payAdvantage.common.setErrorMessage($makePaymentButton, '');

        payAdvantage.creditCardCapture.removeEventListener('closed', onCreditCardDialogClosedHandler);
        payAdvantage.creditCardCapture.removeEventListener('paid', onPaymentReceivedHandler);
    }

    /**
     * Starts the make a payment process.
     */
    function onStartPaymentClicked() {
        $startPaymentButton.hide();
        $container.show();
        $form.show();
    }

    /**
     * Responds to customer field changes.
     */
    function onCustomerChange() {
        $customerCode.val('');
    }

    /**
     * Initialise the form.
     */
    $(document).ready(function () {
        $container = $('#payAdvantageCreditCardTab');
        $form = $container.find('form');
        $startPaymentButton = $('#pay-advantage-make-a-payment');
        $makePaymentButton = $('#pay-advantage-make-payment');
        $firstName = $('#payAdvantageCustomerFirstNameCC');
        $lastName = $('#payAdvantageCustomerLastNameCC');
        $email = $('#payAdvantageCustomerEmailCC');
        $phone = $('#payAdvantageMobileNumberCC');
        $description = $('#payAdvantageDescriptionCC');
        $amount = $('#payAdvantageAmountCC');
        $customerCode = $('#pay-advantage-customer-code-regcc');
        $messages = $('#pay-advantage-make-a-payment-messages');
        $receipt = $('#pay-advantage-make-a-payment-receipt');

        $startPaymentButton.click(onStartPaymentClicked);
        $makePaymentButton.click(onMakePaymentClicked);
        $firstName.on('blur', validateFirstName);
        $lastName.on('blur', validateLastName);
        $email.on('blur', validateEmail);
        $phone.on('blur', validatePhone);
        $description.on('blur', validateDescription);
        $amount.on('blur', validateAmount);
    });
}(jQuery, window, document, payAdvantage));