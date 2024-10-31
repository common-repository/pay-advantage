(function(window) {
    window.payAdvantage = window.payAdvantage || {};
    window.payAdvantage.customer = {
        /**
         * Validates a customer
         * @param {Object} customer
         * @return {string[]}
         */
        validate: function (customer) {
            if (!customer) {
                return [ 'Customer is required.' ];
            }

            var result = [];

            if (payAdvantage.common.isEmptyString(customer.payadvantagefirstname)) {
                result.push('First name is a required field.');
            }
            if (payAdvantage.common.isEmptyString(customer.payadvantagelastname)) {
                result.push('Last name is a required field.');
            }

            if (payAdvantage.common.isEmptyString(customer.payadvantageemail)) {
                result.push('Email is a required field.');
            } else if (!payAdvantage.common.isValidEmail(customer.payadvantageemail)) {
                result.push('Email is invalid.');
            }

            if (pay_advantage_ajax_object.pay_advantage_require_mobile === '1') {
                if (payAdvantage.common.isEmptyString(customer.payadvantagemobile)) {
                    result.push('Phone / Mobile is a required field.');
                } else if (!payAdvantage.common.isValidPhone(customer.payadvantagemobile)) {
                    result.push('Phone / Mobile is invalid.');
                }
            }

            if (pay_advantage_ajax_object.pay_advantage_require_address === '1') {
                if (payAdvantage.common.isEmptyString(customer.payadvantagestreet)) {
                    result.push('Street is a required field.')
                }
                if (payAdvantage.common.isEmptyString(customer.payadvantagesuburb)) {
                    result.push('Suburb is a required field.')
                }
                if (payAdvantage.common.isEmptyString(customer.payadvantagestate)) {
                    result.push('State is a required field.')
                }
                if (payAdvantage.common.isEmptyString(customer.payadvantagepostcode)) {
                    result.push('Postcode is a required field.')
                }
            }

            return result;
        },

        register: function (customer) {
            return payAdvantage.common.postAjax(
                'pay_advantage_create_customer',
                customer);
        }
    };
}(window));