<?php
/**
 * Validates the ajax call information to make sure the data is valid.
 */

//Validation Tests
class Pay_Advantage_Validator {

	public function validate_customer_register( $customer_data, $validate_for_bpay ) {
		$validation_messages = array();

		if ( empty( $customer_data['FirstName'] ) ) {
			array_push( $validation_messages, 'First name is required.' );
		}

		if ( empty( $customer_data['LastName'] ) ) {
			array_push( $validation_messages, 'Last name is required.' );
		}

		if ( empty( $customer_data['Email'] ) ) {
			array_push( $validation_messages, 'Email is required.' );
		}

		if ( $validate_for_bpay && get_option( 'pay_advantage_require_mobile' ) == 1 && empty( $customer_data['Mobile'] ) ) {
			array_push( $validation_messages, 'Mobile phone number is required.' );
		}

		if ( $validate_for_bpay && get_option( 'pay_advantage_require_address' ) == 1 ) {
			if ( empty( $customer_data['Street1'] ) ) {
				array_push( $validation_messages, 'Street is required.' );
			}
			if ( empty( $customer_data['Locality'] ) ) {
				array_push( $validation_messages, 'Suburb is required.' );
			}
			if ( empty( $customer_data['AdministrativeArea'] ) ) {
				array_push( $validation_messages, 'State is required.' );
			}
			if ( empty( $customer_data['Postcode'] ) ) {
				array_push( $validation_messages, 'Postcode is required.' );
			}
		}

		return $validation_messages;
	}
}

?>