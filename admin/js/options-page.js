/* eslint no-unused-vars: 0 */
/* eslint no-undef: 0 */

jQuery(document).ready(function ($) {
  $('.wordpress-ajax-form').on('submit', function (e) {
    e.preventDefault();
    const anonymousPermission = $('#payAdvantageAnonymousPermission').prop('checked');
    const showBPay = $('#payAdvantageShowBPayTab').prop('checked');
    const showCreditCard = $('#payAdvantageShowCreditCardTab').prop('checked');
    const requirePhone = $('#payAdvantageRequireMobileNumber').prop('checked');
    const requireAddress = $('#payAdvantageRequireAddress').prop('checked');
    const requireCountry = $('#payAdvantageRequireCountry').prop('checked');
    const onchargeCreditCardFees = $('#payadvantageonchargecreditcardfees').prop('checked');
    const creditCardDescription = $('#payAdvantageCreditCardDescription').val();
    const payAdvantageMakePaymentButton = $('#payAdvantageMakePaymentButton').val();
    const wcOnchargeCreditCardFees = $('#payadvantagewconchargecreditcardfees').prop('checked');
    const wcPaidStatus = $('#payadvantagewcpaidstatus').val();
    const wcCancelStatus = $('#payadvantagewccancelstatus').val();

    const payload = {
      'payadvantagerequiremobile': requirePhone,
      'payadvantagerequireaddress': requireAddress,
      'payadvantagerequirecountry': requireCountry,
      'payadvantageshowbpay': showBPay,
      'payadvantageshowbcreditcard': showCreditCard,
      'payadvantagecarddescription': creditCardDescription,
      'payadvantageanonymouspermission': anonymousPermission,
      'payadvantagemakepaymentbutton': payAdvantageMakePaymentButton,
      'payadvantageonchargecreditcardfees': onchargeCreditCardFees,
      'payadvantagewconchargecreditcardfees': wcOnchargeCreditCardFees,
      'payadvantagewcpaidstatus': wcPaidStatus,
      'payadvantagewccancelstatus': wcCancelStatus
    };

      window.payAdvantage.common.postAjax('save_pay_advantage_settings_action', payload)
        .then(function(response) {
          if (response.Messages) {
              payAdvantageShowToast('error', response.Messages.join(' '));
          } else {
              payAdvantageShowToast('updated', 'Settings updated.', 10000);
          }
        })
        .catch(function() {
            payAdvantageShowToast('error', 'An error occurred.');
        });
  })

  $('#payAdvantageAnonymousPermission').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_show_widget_to_users_not_logged_in));
  $('#payAdvantageRequireMobileNumber').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_require_mobile));
  $('#payAdvantageRequireAddress').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_require_address));
  $('#payAdvantageRequireCountry').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_require_country));
  $('#payAdvantageShowBPayTab').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_show_bpay));
  $('#payAdvantageShowCreditCardTab').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_show_credit_card));
  $('#payadvantageonchargecreditcardfees').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_oncharge_credit_card_fees));
  $('#payadvantagewconchargecreditcardfees').prop('checked', payAdvantageCheckIfOptionSelected(pay_advantage_ajax_object.pay_advantage_wc_oncharge_credit_card_fees));
})

/**
 * Used for api call. Required!!
 */
function payAdvantageShowToast (className, message, timeoutMs) {
  var element = jQuery('#payAdvantageNotice');

  element.attr('class', 'notice '+ className);
  element.find('p').text(message);

  element.show();
  setTimeout(function () {
    element.hide();
  }, timeoutMs || 4000);
  jQuery(window).scrollTop(0);
}

/**
 * Sends login data to test creds to api. For checking login details.
 */
function payAdvantageConnect() {
    window.payAdvantage.common.postAjax('pay_advantage_connect_action', { 'payadvantageenv': jQuery('input[name="payAdvantageEnv"]:checked').val() })
      .then(function(response) {
        if (response.Messages) {
          payAdvantageShowToast('error', response.Messages.join(' '));
        } else {
          window.location.href = response.RedirectTo;
        }
      })
      .catch(function() {
        payAdvantageShowToast('error', 'An error occurred.');
      });
}

/**
 * Disconnects the user from Pay Advantage.
 */
function payAdvantageDisconnect() {
    window.payAdvantage.common.postAjax('pay_advantage_disconnect_action')
      .then(function() {
          window.location.reload();
      })
      .catch((function() {
          payAdvantageShowToast('error', 'An error occurred.');
      }));
}

function payAdvantageCheckIfOptionSelected (value) {
  return value === '1';
}