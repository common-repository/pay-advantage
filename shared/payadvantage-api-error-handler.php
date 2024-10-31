<?php
/**
 * Handles the response error and returns appropriate response.
 */

class Pay_Advantage_Api_Error_Handler {
	/**
	 * The main function that looks for errors and tries to spit out some generic ones.
	 */
	public static function check_for_errors( $api_result, $type, $console, $code ) {
		if ( $api_result == null || $code == 404 ) {
			return array(
				"type"     => $type,
				"Messages" => array( "Error communicating with PayAdvantage." ),
				"console"  => $console,
				"Status"   => $code
			);
		}

		if ( $code >= 500 ) {
			return array(
				"type"     => $type,
				"Messages" => array( "Internal Error has occurred." ),
				"console"  => $console,
				"Status"   => $code
			);
		}

		$json_api_response = json_decode( $api_result, true );

		if ( isset( $json_api_response['ErrorCode'] ) && pay_advantage_has_messages( $json_api_response ) ) {
			return array(
				"type"     => $type,
				"Messages" => array( "(" . sanitize_text_field( $json_api_response['ErrorCode'] ) . ") " . sanitize_text_field( $json_api_response['Messages'][0] ) ),
				"console"  => $console,
				"Status"   => $code
			);
		}
		if ( pay_advantage_has_messages( $json_api_response ) ) {
			return array(
				"type"     => $type,
				"Messages" => array( sanitize_text_field( $json_api_response['Messages'][0] ) ),
				"console"  => $console,
				"Status"   => $code
			);
		}

		return array(
			"type"     => $type,
			"Messages" => array( "Internal Error has occurred." ),
			"console"  => $console,
			"Status"   => $code
		);
	}

	/**
	 * Logs to the options page errors that come through
	 */
	public static function log_error( $response ) {
		$current_log = get_option( 'pay_advantage_error_logging' );
		if ( $current_log == 0 ) {
			$current_log = gmdate( 'Y-m-d\TH:i:s\Z' ) . ': ' . sanitize_text_field( $response ) . '&#13;&#10;';
		} else {
			$current_log = gmdate( 'Y-m-d\TH:i:s\Z' ) . ': ' . sanitize_text_field( $response ) . '&#13;&#10;' . sanitize_text_field( $current_log );
		}

		if ( strlen( $current_log ) > PAYADV_MAX_LOG_SIZE_CHARS ) {
			$current_log = substr( $current_log, 0, PAYADV_MAX_LOG_SIZE_CHARS );
		}

		update_option( 'pay_advantage_error_logging', $current_log );
	}
}