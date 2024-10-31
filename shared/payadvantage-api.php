<?php
class Pay_Advantage_Api {
	private $pay_advantage_url;

	function __construct() {
		$this->pay_advantage_url = get_option( 'pay_advantage_url' );
	}

	/**
	 * Queries the customer to see if they already exist.
	 */
	public function customer_query( $first_name, $last_name, $email, $mobile ) {

		$params = array();
		if ( ! empty( $first_name ) ) {
			$params[] = 'firstname=' . urlencode( $first_name );
		}
		if ( ! empty( $last_name ) ) {
			$params[] = 'lastname=' . urlencode( $last_name );
		}
		if ( ! empty( $email ) ) {
			$params[] = 'email=' . urlencode( $email );
		}
		if ( ! empty( $mobile ) ) {
			$params[] = 'mobile=' . urlencode( $mobile );
		}

		if ( count( $params ) == 0 ) {
			return array( 'Messages' => 'No query data set.' );
		}

		$query_url = "$this->pay_advantage_url/customers?" . implode( '&', $params );

		return $this->process_request( $query_url, null, 'customer', 'GET' );
	}

	/**
	 * Creates a customer with or without a BPAY reference.
	 */
	public function create_customer( $customer_data, $with_bpay = true ) {
		// Query the API to find a customer with the same details.
		$query_customer_result = $this->customer_query(
			$customer_data['FirstName'],
			$customer_data['LastName'],
			$customer_data['Email'],
			$customer_data['Mobile']
		);

		if ( pay_advantage_has_messages( $query_customer_result ) ) {
			return $query_customer_result;
		}

		// Query can return an array of matches
		$existing_customer = $query_customer_result['Records'];

		if ( count( $existing_customer ) > 0 ) {

			// Just picking the first one here if many. You can choose based on any strategy.
			$matched = $existing_customer[0];

			// If the selected customer doesn't have a BPAY ref and one is required, generate it.
			if ( $with_bpay && empty( $matched['BPAYRef'] ) ) {
				if ( ! $this->is_verified() ) {
					return array(
						"Messages" => array( "Your business has not been verified." ),
						"console"  => 'create_customer'
					);
				}

				$new_ref = $this->process_request(
					"$this->pay_advantage_url/customers/" . $matched['Code'] . '/createbpayref',
					null, // no content required
					'Add BPay',
					'POST' );

				if ( pay_advantage_has_messages( $new_ref ) ) {
					return $new_ref;
				}

				$matched['BillerCode'] = $new_ref['BillerCode'];
				$matched['BPAYRef']    = $new_ref['Reference'];
			}

			return $matched;
		}

		return $this->process_request(
			"$this->pay_advantage_url/customers" . ( $with_bpay ? '?with=bpayref' : '' ),
			$customer_data,
			$with_bpay ? 'BPay Registration' : 'Customer',
			'POST' );
	}

	public function get_refresh_token( $registration_code ) {
		$client_id     = get_option( 'pay_advantage_app_client_id' );
		$code_verifier = get_option( 'pay_advantage_app_code_verifier' );

		global $wp;
		$redirect_uri = get_site_url() . '/wp-admin/options-general.php?page=PayAdvantage';

		$data = array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => json_encode( array(
					'code'          => $registration_code,
					'client_id'     => $client_id,
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => $redirect_uri,
					'code_verifier' => $code_verifier
				)
			),
			'timeout' => 30
		);

		try {
			$response = wp_remote_post( $this->pay_advantage_url . '/token', $data );
			$result   = $this->process_result( $response, 'token', 'admin' );
		} catch ( Exception $e ) {
			return array( 'Messages' => $e->getMessage() );
		}

		if ( pay_advantage_has_messages( $result ) ) {
			return $result;
		}

		update_option( 'pay_advantage_refresh_token', $result["refresh_token"] );
		update_option( 'pay_advantage_app_code_verifier', null );
		// Remove the v2 access keys as they are no longer needed.
		delete_option( 'pay_advantage_user_name' );
		delete_option( 'pay_advantage_password' );
		$this->set_access_token_from_result( $result );
	}

	public function delete_refresh_token() {
		$result = $this->process_request( $this->pay_advantage_url . '/token', null, 'token', 'DELETE' );

		// If we get an error, and the error is consistent with the token not existing, or having been revoked,
		// clear the local tokens.
		if ( isset( $result['Status'] ) ) {
			if ( $result['Status'] == 404 || $result['Status'] == 403 ) {
				$this->clear_all_tokens();
			}
		}

		if ( pay_advantage_has_messages( $result ) ) {
			return $result;
		}

		$this->clear_all_tokens();
	}

	/**
	 * Gets a url for use in the credit card iframe.
	 * @return array|false|mixed|string[][]|void
	 */
	public function get_cc_iframe_url( $customer_code, $amount, $description, $externalID, $onchargeFees ) {
		$payload = array (
            'customer' => array ( 'code' => $customer_code ),
			'amount' => $amount,
			'description' => $description
		);

        if ( isset ( $onchargeFees ) )
            $payload['onchargeFees'] = $onchargeFees == 1;

		if ( ! empty( $externalID ) ) {
			$payload['externalID'] = $externalID;
		}

		return $this->process_request( $this->pay_advantage_url . '/credit_card_iframes', $payload, 'credit_card_iframes', 'POST' );
	}

	/**
	 * Gets a payment from Pay Advantage.
	 */
	public function get_payment( $payment_code ) {
		$url = $this->pay_advantage_url . '/payments/' . urlencode( $payment_code );

		return $this->process_request( $url, null, 'payment', 'GET' );
	}

	private function clear_all_tokens() {
		update_option( 'pay_advantage_refresh_token', null );
		update_option( 'pay_advantage_access_token', null );
		update_option( 'pay_advantage_verified', false );
		update_option( 'pay_advantage_access_token_expiry', null );
		update_option( 'pay_advantage_app_client_id', null );
		update_option( 'pay_advantage_app_code_verifier', null );
	}

	private function get_access_token() {
		// Read access token from storage.
		$access_token = get_option( 'pay_advantage_access_token' );

		// If there is an access token, check the expiry. If not expired return this token
		// as it is still ok to use.
		if ( ! empty( $access_token ) ) {
			$access_token_expiry = get_option( 'pay_advantage_access_token_expiry' );
			$now                 = new DateTime();
			if ( $access_token_expiry > $now ) {
				return $access_token;
			}
		}

		// Read the refresh token from storage. If one doesn't exist there is nothing more that can be done here.
		$refresh_token = get_option( 'pay_advantage_refresh_token' );
		if ( empty( $refresh_token ) ) {
			return array( 'Messages' => array( "This plugin has not been connected. Please contact vendor." ) );
		}

		// Build up the request for receiving an access token using refresh token.
		$data = array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => json_encode( array(
				'scope'         => PAYADV_APP_ID, // the registered app code
				'refresh_token' => $refresh_token,
				'grant_type'    => 'refresh_token',
				'client_id'     => '' // the ID recorded for this instance of the app
			) ),
			'timeout' => 30
		);

		// Perform the request.
		$response = wp_remote_post( get_option( 'pay_advantage_url' ) . '/token', $data );
		// Standardise the response.
		$result = $this->process_result( $response, 'token', 'admin' );

		// On error return errors and abort.
		if ( isset( $result['Status'] ) ) {
			if ( $result['Status'] == 403 ) {
				$this->clear_all_tokens();
			}

			return $result;
		}

		if ( pay_advantage_has_messages( $result ) ) {
			return $result;
		}

		// Store and return access token.
		$this->set_access_token_from_result( $result );

		return $result['access_token'];
	}

	/**
	 * Determines if the currently connected merchant is verified.
	 */
	public function is_verified() {
		return get_option( 'pay_advantage_verified' ) == true;
	}

	/**
	 * Stores the access token and calculated expiry.
	 */
	private function set_access_token_from_result( $result ) {
		update_option( 'pay_advantage_access_token', $result["access_token"] );
		update_option( 'pay_advantage_verified', false );

		// take 10 seconds off the expiry as a send and receive buffer
		$expires_in_seconds  = $result["expires_in"] - 10;
		$expire_in_interval  = new DateInterval( "PT{$expires_in_seconds}S" );
		$access_token_expiry = ( new DateTime() )->add( $expire_in_interval );
		update_option( 'pay_advantage_access_token_expiry', $access_token_expiry );

		// Read the JWT to determine if the merchant is verified or not.
		$jwt_parts    = explode( ".", $result["access_token"] );
		$payload_json = base64_decode( $jwt_parts[1] );
		$payload      = json_decode( $payload_json );
		if ( isset( $payload->prm ) &&
			count( $payload->prm ) == 1 &&
			isset ( $payload->prm[0]->v ) &&
			$payload->prm[0]->v == true ) {
			update_option( 'pay_advantage_verified', true );
		}
	}

	private function process_request( $end_point, $body, $console, $request_type ) {
		try {
			$access_token = $this->get_access_token();

			if ( pay_advantage_has_messages( $access_token ) ) {
				return $access_token;
			}

			$data = array(
				'method'  => $request_type,
				'headers' => array(
					'Authorization' => "Bearer $access_token"
				),
				'timeout' => 30
			);

			if ( $request_type == 'POST' ) {
				$data['headers']['Content-Type'] = 'application/json';
				if ( is_array( $data ) ) {
					$data['body'] = json_encode( $body );
				}
			}

			$response = wp_remote_request( $end_point, $data );

			return $this->process_result( $response, $console, $request_type );
		} catch ( Exception $e ) {
			return array( 'Messages' => array( $e->getMessage() ) );
		}
	}

	/**
	 * Attempt to standardise all Pay Advantage responses.
	 */
	private function process_result( $response, $console, $request_type ) {
		$generic_error_message = array(
			'Messages' => array( "There is an issue processing your request, please contact vendor." ),
			"console"  => $console
		);

		if ( is_wp_error( $response ) ) {
			Pay_Advantage_Api_Error_Handler::log_error( json_encode( $response ) );

			return $generic_error_message;
		}

		if ( ! isset( $response['response'] ) ) {
			Pay_Advantage_Api_Error_Handler::log_error( json_encode( $response ) );

			return $generic_error_message;
		}

		$body = wp_remote_retrieve_body( $response );

		try {
			if ( $response['response']['code'] > 300 ) {
				Pay_Advantage_Api_Error_Handler::log_error( json_encode( $response ) );

				return Pay_Advantage_Api_Error_Handler::check_for_errors( $body, $request_type, $console, $response['response']['code'] );
			}
		} catch ( Exception $e ) {
			Pay_Advantage_Api_Error_Handler::log_error( json_encode( $response ) );

			return $generic_error_message;
		}

		return json_decode( $body, true );
	}
}
?>