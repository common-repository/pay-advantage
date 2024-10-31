/* eslint no-unused-vars: 0 */
/* eslint no-undef: 0 */

(function($, window, document, payAdvantage) {
  const gateway_id = 'pay_advantage_gateway';

  var $form = null;
  var $firstName = null;
  var $lastName = null;
  var $company = null;
  var $email = null;
  var $phone = null;
  var $address1 = null;
  var $address2 = null;
  var $city = null;
  var $state = null;
  var $postcode = null;
  var $country = null;
  var $customerCode = null;

  var $redirectOnCloseUrl = null;
  var $orderId = null;
  var $paidNonce = null;

  /**
   * Gets a fields value
   * @param {{id: string, label: string}} field
   * @returns {string}
   */
  function getFieldValue(field) {
    return $('#' + field.id).val();
  }

  /**
   * Determines if Pay Advantage is selected as a gateway.
   * @return {boolean}
   */
  function isPayAdvantageGatewaySelected() {
    return $('#payment_method_pay_advantage_gateway').prop('checked');
  }

  /**
   * Scrolls the user to the notices.
   * This must be kept compatible with woocommerce/assets/js/frontend/checkout.js/scroll_to_notices
   */
  function wc_scrollToNotices() {
    var scrollElement = $( '.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout' );

    if ( ! scrollElement.length ) {
      scrollElement = $( '.form.checkout' );
    }
    if ( scrollElement.length ) {
      $( 'html, body' ).animate( {
        scrollTop: ( scrollElement.offset().top - 100 )
      }, 1000 );
    }
  }

  /**
   * Adds errors to the checkout.
   * This must be kept compatible with woocommerce/assets/js/frontend/checkout.js/submit_error
   * @param {string} error_message
   */
  function wc_submitError(error_message) {
    $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
    $form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>'); // eslint-disable-line max-len
    $form.removeClass('processing').unblock();
    $form.find('.input-text, select, input:checkbox').trigger('validate').trigger('blur');
    wc_scrollToNotices();
    $(document.body).trigger('checkout_error', [ error_message ]);
  }

  /**
   * Clears the Woo Commerce errors.
   */
  function clearErrors() {
    $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
  }

  /**
   * Adds a error to Woo Commerce errors.
   * @param {String[]} messages
   */
  function addErrors(messages) {
    const errors = $('<ul class="woocommerce-error" role="alert"></ul>');

    for (let i = 0; i < messages.length; i++) {
      const li = $('<li></li>');
      li.text(messages[i]);
      errors.append(li);
    }

    wc_submitError(errors[0].outerHTML);
  }

  /**
   * Determines is there are any errors.
   * @return {boolean}
   */
  function hasErrors() {
    return $('.woocommerce-error').length > 0;
  }

  /**
   * Creates a customer api object.
   */
  function createCustomerObject() {
    return {
      'payadvantagefirstname': $firstName.val(),
      'payadvantagelastname': $lastName.val(),
      'payadvantagename': $company.val(),
      'payadvantageemail': $email.val(),
      'payadvantagemobile': $phone.val(),
      'payadvantagestreet': $address1.val() + ' ' + $address2.val(),
      'payadvantagesuburb': $city.val(),
      'payadvantagestate': $state.val(),
      'payadvantagepostcode': $postcode.val(),
      'payadvantagecountry': $country.val()
    };
  }

  /**
   * Determines if a customer is valid or not.
   * @return {boolean}
   */
  function validateCustomer() {
    const result = payAdvantage.customer.validate(createCustomerObject());

    if (result.length > 0) {
      addErrors(result);
    }

    return result.length === 0;
  }

  /**
   * Registers a customer in Pay Advantage.
   */
  function registerCustomer() {
    return payAdvantage.customer.register(createCustomerObject())
        .then(function (result) {
          $customerCode.val(result.Code);
        });
  }

  /**
   * Puts the form into a processing state using the same logic as Woo Commerce.
   */
  function wc_startProcessing() {
    $form.addClass('processing');

    payAdvantage.common.block($form);
  }

  /**
   * Takes the form into a processing state using the same logic as Woo Commerce.
   */
  function wc_stopProcessing() {
    $form.removeClass('processing');

    payAdvantage.common.unblock($form);
  }

  /**
   * Modern browsers have their own standard generic messages that they will display.
   * Confirm, alert, prompt or custom message are not allowed during the unload event
   * Browsers will display their own standard messages
   * @param (Event) e
   * @returns {boolean|undefined}
   */
  function handleUnloadEvent(e) {
    // Check if the browser is Internet Explorer
    if( ( navigator.userAgent.indexOf('MSIE') !== -1 ) || ( !!document.documentMode ) ) {
      // IE handles unload events differently than modern browsers
      e.preventDefault();
      return undefined;
    }

    return true;
  }

  /**
   * Add protection against navigating away.
   */
  function attachUnloadEventsOnSubmit() {
    $(window).on('beforeunload', handleUnloadEvent);
  }

  /**
   * Remove the protection against navigating away.
   */
  function detachUnloadEventsOnSubmit() {
    $(window).off('beforeunload', handleUnloadEvent);
  }

  /**
   * Performs the required tasks that need to occur before submitting.
   * These include registering the customer and tokenising the credit card.
   * @param {Event} e
   * @return {boolean}
   */
  function onSubmit(e) {
    if ($form.is('.processing')) {
      return false;
    }

    // If the PA gateway is not selected, then leave.
    if (!isPayAdvantageGatewaySelected()) {
      return true;
    }

    // Stop the event.
    // We take over flow from here.
    e.preventDefault();
    e.stopImmediatePropagation();
    e.stopPropagation();

    clearErrors();

    if (!validateCustomer()) {
      return false;
    }

    $redirectOnCloseUrl = null;
    $orderId = null;
    $paidNonce = null;

    wc_startProcessing();

    Promise.resolve()
        .then(function () {
          // If there is no customer code, create one.
          if ($customerCode.val() === '') {
            return registerCustomer();
          }
        })
        .then(function (result) {
          return updateWooCommerceOrder();
        })
        .then(function (result) {
          $orderId = result.orderId;
          $paidNonce = result.paidNonce;
          payAdvantage.initialiseCreditCardCapture();
          payAdvantage.creditCardCapture.addEventListener('closing', creditCardDialogClosedHandler);
          payAdvantage.creditCardCapture.addEventListener('cancel', creditCardCancelHandler);
          payAdvantage.creditCardCapture.addEventListener('failed', creditCardFailedHandler);
          payAdvantage.creditCardCapture.addEventListener('paid', creditCardPaidHandler);
          return payAdvantage.creditCardCapture.show(result.iframeUrl, result.cardHolder);
        })
        .then(function (result) {
          wc_stopProcessing();
        })
        .catch(function (error) {
          wc_stopProcessing();

          // Error will be null if the error has been handled.
          if (error) {
            addErrors([ error.message ]);
          }
        });

    return false;
  }

  /**
   * Responds to the 'closing' event from the credit card capture dialog.
   * @param {Event} event
   * @return {void}
   */
  function creditCardDialogClosedHandler( event ) {
    payAdvantage.creditCardCapture.removeEventListener('closing', creditCardDialogClosedHandler );
    payAdvantage.creditCardCapture.removeEventListener('cancel', creditCardCancelHandler );
    payAdvantage.creditCardCapture.removeEventListener('failed', creditCardFailedHandler );
    payAdvantage.creditCardCapture.removeEventListener('paid', creditCardPaidHandler );

    if ($redirectOnCloseUrl) {
      event.redirectUri = $redirectOnCloseUrl;
    }
  }

  /**
   * Updates the order in WooCommerce.
   * @returns {Promise}
   */
  function updateWooCommerceOrder() {
    return new Promise( function ( resolve, reject ) {
      // Trigger a handler to let gateways manipulate the checkout if needed
      // eslint-disable-next-line max-len
      if ( $form.triggerHandler( 'checkout_place_order' ) !== false && $form.triggerHandler( 'checkout_place_order_' + gateway_id ) !== false) {

        wc_startProcessing();

        // Attach event to block reloading the page when the form has been submitted
        attachUnloadEventsOnSubmit();

        $.ajax({
          type: 'POST',
          url: wc_checkout_params.checkout_url,
          data: $form.serialize(),
          dataType: 'json',
          success: function ( result ) {
            // Detach the unload handler that prevents a reload / redirect
            detachUnloadEventsOnSubmit();

            try {
              if ('success' === result.result && $form.triggerHandler('checkout_place_order_success', result) !== false) {
                if (result.redirect.indexOf('payadvantage://') !== -1) {
                  resolve(JSON.parse(decodeURIComponent(result.redirect.substring(15))));
                } else {
                  reject(new Error('Invalid response'));
                }
              } else if ('failure' === result.result && result.messages) {
                wc_submitError(result.messages);
                reject(null);
              } else if ('failure' === result.result) {
                reject(new Error('Result failure'));
              } else {
                reject(new Error('Invalid response'));
              }
            } catch (err) {
              // Reload page
              if (true === result.reload) {
                window.location.reload();
              }

              // Trigger update in case we need a fresh nonce
              if (true === result.refresh) {
                $(document.body).trigger('update_checkout');
              }

              // Add new errors
              if (result.messages) {
                wc_submitError(result.messages);
                reject(null);
              } else {
                wc_submitError('<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>'); // eslint-disable-line max-len
                reject(null);
              }
            }
          },
          error: function (jqXHR, textStatus, errorThrown) {
            // Detach the unload handler that prevents a reload / redirect
            detachUnloadEventsOnSubmit();

            reject(errorThrown);
          }
        });
      } else {
        reject(null);
      }
    });
  }

  /**
   * Handles the 'paid' event.
   * @param {Event} eventArgs
   * @return {void}
   */
  function creditCardPaidHandler(eventArgs) {
    eventArgs.promise = eventArgs.promise
      .then(function () {
        return payAdvantage.common.postAjax('pay_advantage_wc_mark_order_as_paid',
          {
            'orderid': $orderId,
            'paymentcode': eventArgs.data.payment.code,
            'security': $paidNonce
          })
      })
      .then(function (response) {
        $redirectOnCloseUrl = response.redirect;
      })
      .catch(function (error) {
        console.log(error);
        throw new Error('There was an unexpected error updating the order to paid. Please contact the administrator of this site to update your order. ' + error.message);
      });
  }

  /**
   * Handles the 'failed' event.
   * @param {Event} eventArgs
   * @return {void}
   */
  function creditCardFailedHandler(eventArgs) {
    eventArgs.promise = eventArgs.promise
      .then(function () {
        return payAdvantage.common.postAjax('pay_advantage_wc_mark_order_as_failed',
          {
            'orderid': $orderId,
            'security': $paidNonce
          });
      })
      .catch(function (error) {
        console.log(error);
        payAdvantage.creditCardCapture.setError('There was an unexpected error updating the order to failed. ' + error.message);
      });
  }

  /**
   * Handles the 'cancel' event.
   * @param {Event} eventArgs
   * @return {void}
   */
  function creditCardCancelHandler(eventArgs) {
    eventArgs.promise = eventArgs.promise
      .then(function () {
        return payAdvantage.common.postAjax('pay_advantage_wc_mark_order_as_cancelled',
          {
            'orderid': $orderId,
            'security': $paidNonce
          })
      })
      .catch(function (error) {
        console.log(error);
        throw new Error('There was an unexpected error cancelling the payment. ' + error.message);
      });
  }

  /**
   * Respond to credit card field changes.
   */
  function onCustomerChange() {
    $customerCode.val('');
  }

  /**
   * Rebind elements that can be replaced.
   */
  function rebindElements() {
    $customerCode = $('#pay-advantage-customer-code-wc');
  }

  /**
   * Initialise the form.
   */
  $(document).ready(function () {
    $form = $('form.checkout');
    $firstName = $('#billing_first_name');
    $lastName = $('#billing_last_name');
    $company = $('#billing_company');
    $email = $('#billing_email');
    $phone = $('#billing_phone');
    $address1 = $('#billing_address_1');
    $address2 = $('#billing_address_2');
    $city = $('#billing_city');
    $state = $('#billing_state');
    $postcode = $('#billing_postcode');
    $country = $('#billing_country');
    $customerCode = $('#pay-advantage-customer-code-wc');

    $form.on('checkout_place_order_' + gateway_id, validateCustomer);

    // Form submission
    $form.on('submit', onSubmit );

    $firstName.on('change', onCustomerChange);
    $lastName.on('change', onCustomerChange);
    $company.on('change', onCustomerChange);
    $email.on('change', onCustomerChange);
    $phone.on('change', onCustomerChange);
    $address1.on('change', onCustomerChange);
    $address2.on('change', onCustomerChange);
    $city.on('change', onCustomerChange);
    $state.on('change', onCustomerChange);
    $postcode.on('change', onCustomerChange);
    $country.on('change', onCustomerChange);

    $(document).ajaxComplete(function() {
      rebindElements();
    });
  });
}(jQuery, window, document, payAdvantage));