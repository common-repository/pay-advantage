/* eslint no-unused-vars: 0 */
/* eslint no-undef: 0 */

function payAdvantageServerCall(actionName, payload) {
  return new Promise(function(resolve, reject) {
    if (!payload)
      payload = {};

    payload['action'] = actionName;
    payload['security'] = pay_advantage_ajax_object.pay_advantage_nonce;

    jQuery.ajax({
      type: 'POST',
      url: pay_advantage_ajax_object.pay_advantage_ajax_url,
      data: payload,
      success: function (response) {
        resolve(response);
      },
      error: function (response) {
        console.log(response);
        reject(response);
      }
    });
  });
}
