/* eslint no-unused-vars: 0 */
/* eslint no-undef: 0 */

(function($, window, document, payAdvantage) {
    window.payAdvantage = window.payAdvantage || {};

    window.payAdvantage.creditCardCapture = null;

    /**
     * Initialises the credit card dialog.
     */
    window.payAdvantage.initialiseCreditCardCapture = function () {
        if (window.payAdvantage.creditCardCapture !== null) {
            window.payAdvantage.creditCardCapture.dispose();
        }

        window.payAdvantage.creditCardCapture = new PayAdvantageCreditCardCaptureDialog();
    };
}(jQuery, window, document, payAdvantage));