(function($, window) {
    window.payAdvantage = window.payAdvantage || {};
    window.payAdvantage.common = {};

    var _escapeHtmlDiv = null;
    /**
     * Escapes text as HTML;
     * @param {String} text
     * @returns {String} HTML.
     */
    window.payAdvantage.common.escapeHtml = function (text) {
        if (_escapeHtmlDiv === null)
            _escapeHtmlDiv = $('<div></div>');

        _escapeHtmlDiv.text(text);
        return _escapeHtmlDiv.html();
    }

    /**
     * Determines if the string is empty
     * @param {String} value
     * @returns {boolean}
     */
    window.payAdvantage.common.isEmptyString = function (value) {
        return value === null || value === undefined || value.trim() === '';
    }

    /**
     * Determines if the value is a valid phone number.
     * @param {string} value
     */
    window.payAdvantage.common.isValidPhone = function (value) {
        var phoneRegex = /((?:\+|00)[17](?: |)?|(?:\+|00)[1-9]\d{0,2}(?: |)?|(?:\+|00)1\d{3}(?: |)?)?(0\d|\([0-9]{3}\)|[1-9]{0,3})(?:((?: |)[0-9]{2}) {4}|((?:[0-9]{2}) {4})|((?: |)[0-9]{3}(?: |)[0-9]{4})|([0-9]{7}))/g;
        return value.match(phoneRegex) !== null;
    }

    /**
     * Determines if the value is a valid email address..
     * @param {string} value
     * @returns {boolean}
     */
    window.payAdvantage.common.isValidEmail = function (value) {
        var emailRegex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        return value.match(emailRegex) !== null;
    }

    /**
     * Determines if the text is just letters.
     * @param {string} value
     */
    window.payAdvantage.common.isLettersOnly = function(value) {
        return value.match(/^[_A-z]*((-|\s)*[_A-z])*$/) !== null;
    }

    /**
     * Determines if the text is just numbers.
     * @param {string} value
     */
    window.payAdvantage.common.isNumbersOnly = function(value) {
        return value.match(/^[0-9]*$/) !== null;
    }

    /**
     * Determines if the text is currency.
     * @param {string} value
     */
    window.payAdvantage.common.isCurrency = function(value) {
        return value.match(/^\d+(\.\d{1,2})?$/) !== null;
    }

    /**
     * Calls an action on server.
     * @param actionName
     * @param payload
     * @return {Promise<any>}
     */
    window.payAdvantage.common.postAjax = function (actionName, payload) {
        return new Promise(function(resolve, reject) {
            if (!payload)
                payload = {};

            payload['action'] = actionName;
            payload['security'] = payload['security'] || pay_advantage_ajax_object.pay_advantage_nonce;

            jQuery.ajax({
                type: 'POST',
                url: pay_advantage_ajax_object.pay_advantage_ajax_url,
                data: payload,
                success: function (response) {
                    if (response.Messages) {
                        console.log(response);
                        reject(new Error(response.Messages.join(' ')));
                    } else {
                        resolve(response);
                    }
                },
                error: function (response) {
                    console.log(response);
                    if (response.message) {
                        reject(response);
                    } else if (response.responseText) {
                        reject(new Error(response.responseText));
                    } else {
                        reject(new Error('An unexpected error occurred. Check the browsers console log for more information.'));
                    }
                }
            });
        });
    }

    /**
     * Blocks an element.
     * @param {jQuery} $element
     */
    window.payAdvantage.common.block = function ($element) {
        var element_data = $element.data();

        if ( 1 !== element_data['blockUI.isBlocked'] ) {
            $element.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
    }

    /**
     * Unblocks a previously blocked element.
     * @param {jQuery} $element
     */
    window.payAdvantage.common.unblock = function ($element) {
        var element_data = $element.data();

        if ( 1 === element_data['blockUI.isBlocked'] ) {
            $element.unblock();
        }
    }

    /**
     * Sets the error message for a field.
     * @param {jQuery} $element
     * @param {string|null} message
     */
    window.payAdvantage.common.setErrorMessage = function($element, message) {
        var errorElement = $element.siblings('.PayAdvantageError');
        if (!message) {
            errorElement.text('').hide();
        } else {
            errorElement.text(message).show();
        }
    }
}(jQuery, window));