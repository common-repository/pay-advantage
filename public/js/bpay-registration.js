(function($, window, document, payAdvantage) {
    var $container = null;
    var $addressContainer = null;
    var $phoneContainer = null;
    var $firstName = null;
    var $lastName = null;
    var $phone = null;
    var $email = null;
    var $address = null;
    var $state = null;
    var $city = null;
    var $postcode = null;
    var $successContainer = null;
    var $billerCode = null;
    var $paymentRef = null;
    var $form = null;
    var $registerBpayButton = null;

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
     * Validates the street address.
     * @return {boolean}
     */
    function validateAddress() {
        var value = $address.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Street is required.';
        }

        payAdvantage.common.setErrorMessage($address, error);
        return error === '';
    }

    /**
     * Validates the street address.
     * @return {boolean}
     */
    function validateCity() {
        var value = $city.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Suburb is required.';
        } else if (!payAdvantage.common.isLettersOnly(value)) {
            error = 'Suburb is invalid.'
        }

        payAdvantage.common.setErrorMessage($city, error);
        return error === '';
    }

    /**
     * Validates the state.
     * @return {boolean}
     */
    function validateState() {
        var value = $state.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'State is required.';
        } else if (!payAdvantage.common.isLettersOnly(value)) {
            error = 'State is invalid.'
        }

        payAdvantage.common.setErrorMessage($state, error);
        return error === '';
    }

    /**
     * Validates the postcode.
     * @return {boolean}
     */
    function validatePostcode() {
        var value = $postcode.val();
        var error = '';
        if (payAdvantage.common.isEmptyString(value)) {
            error = 'Postcode is required.';
        } else if (!payAdvantage.common.isNumbersOnly(value)) {
            error = 'Postcode is invalid.'
        }

        payAdvantage.common.setErrorMessage($postcode, error);
        return error === '';
    }

    /**
     * Checks the forms validity.
     * @return {boolean}
     */
    function checkValidity() {
        var results = [
            validateFirstName(),
            validateLastName(),
            validateEmail()
            ];

        if (pay_advantage_ajax_object.pay_advantage_require_mobile !== '0') {
            results.push(validatePhone());
        }

        if (pay_advantage_ajax_object.pay_advantage_require_address !== '0') {
            results.push(validateAddress());
            results.push(validateCity());
            results.push(validatePostcode());
            results.push(validateState());
        }

        return results.every(function (result) {
            return result;
        });
    }

    /**
     * Creates a customer api object.
     */
    function createCustomerObject() {
        var result = {
            'payadvantagefirstname': $firstName.val(),
            'payadvantagelastname': $lastName.val(),
            'payadvantageemail': $email.val(),
            'payadvantagecreatebpay': 'true'
        };

        if (pay_advantage_ajax_object.pay_advantage_require_mobile !== '0') {
            result['payadvantagemobile'] = $phone.val();
        }

        if (pay_advantage_ajax_object.pay_advantage_require_address !== '0') {
            result['payadvantagestreet'] = $address.val();
            result['payadvantagesuburb'] = $city.val();
            result['payadvantagestate'] = $state.val();
            result['payadvantagepostcode'] = $postcode.val();
        }

        return result;
    }

    /**
     * Registers a customer.
     * @return {Promise}
     */
    function onRegisterCustomerClick() {
        if (!checkValidity()) {
            return;
        }

        payAdvantage.common.block($container);

        return payAdvantage.customer.register(createCustomerObject())
            .then(function (result) {
                payAdvantage.common.unblock($container);

                payAdvantage.common.setErrorMessage($registerBpayButton, '');
                $form.hide();
                $successContainer.show();
                $billerCode.text(result.BillerCode);
                $paymentRef.text(result.BPAYRef);
            })
            .catch(function (error) {
                payAdvantage.common.unblock($container);

                payAdvantage.common.setErrorMessage($registerBpayButton, error.message);
            });
    }

    /**
     * Initialise the widget.
     */
    $(document).ready(function () {
        $container = $('#payAdvantageBPayTab');
        $firstName = $('#payAdvantageCustomerFirstNamePABPAY');
        $lastName = $('#payAdvantageCustomerLastNamePABPAY');
        $email = $('#payAdvantageCustomerEmailPABPAY');
        $phone = $('#payAdvantageMobileNumberPABPAY');
        $address = $('#payAdvantageStreetPABPAY');
        $city = $('#payAdvantageSuburbPABPAY');
        $state = $('#payAdvantageStatePABPAY');
        $postcode = $('#payAdvantagePostcodePABPAY');
        $addressContainer = $('#payAdvantageRequiredAddress');
        $phoneContainer = $('#payAdvantageRequiredMobile');
        $successContainer = $('#payAdvantageBillerSuccess');
        $billerCode = $('#payAdvantageBillerCode');
        $paymentRef = $('#payAdvantageBPayRef');
        $form = $container.find('form');
        $registerBpayButton = $('#pay-advantage-register-bpay');

        $registerBpayButton.click(onRegisterCustomerClick);

        $firstName.on('change', validateFirstName);
        $lastName.on('change', validateLastName);
        $email.on('change', validateEmail);
        $phone.on('change', validatePhone);
        $address.on('change', validateAddress);
        $city.on('change', validateCity);
        $state.on('change', validateState);
        $postcode.on('change', validatePostcode);

        if (pay_advantage_ajax_object.pay_advantage_require_mobile === '0') {
            $phoneContainer.hide();
        }

        if (pay_advantage_ajax_object.pay_advantage_require_address === '0') {
            $addressContainer.hide();
        }
    });
}(jQuery, window, document, payAdvantage));