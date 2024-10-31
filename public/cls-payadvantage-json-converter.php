<?php
 class Pay_Advantage_Data_Mapper {
	 /**
	  * This method grabs objects and data from $_POST and turns it into a data array.
	  */
	 public static function get_customer_data_from_post() {
		 $suburb    = null;
		 $state     = null;
		 $post_code = null;
		 $street    = null;
		 $country   = null;

		 if ( ! empty( $_POST['payadvantagesuburb'] ) ) {
			 $suburb = sanitize_text_field( $_POST['payadvantagesuburb'] );
		 }
		 if ( ! empty( $_POST['payadvantagestate'] ) ) {
			 $state = sanitize_text_field( $_POST['payadvantagestate'] );
		 }
		 if ( ! empty( $_POST['payadvantagepostcode'] ) ) {
			 $post_code = sanitize_text_field( $_POST['payadvantagepostcode'] );
		 }
		 if ( ! empty( $_POST['payadvantagestreet'] ) ) {
			 $street = sanitize_text_field( $_POST['payadvantagestreet'] );
		 }
		 if ( ! empty( $_POST['payadvantagecountry'] ) ) {
			 $country = sanitize_text_field( $_POST['payadvantagecountry'] );
		 }

		 return array(
			 'Name'             => sanitize_text_field( $_POST['payadvantagefirstname'] . ' ' . sanitize_text_field( $_POST['payadvantagelastname'] ) ),
			 'Email'            => sanitize_email( $_POST['payadvantageemail'] ),
			 'IsConsumer'       => true,
			 'FirstName'        => sanitize_text_field( $_POST['payadvantagefirstname'] ),
			 'LastName'         => sanitize_text_field( $_POST['payadvantagelastname'] ),
			 'Mobile'           => self::extract_phone_number( sanitize_text_field( $_POST['payadvantagemobile'] ) ),
			 'Street1'          => $street,
			 'Locality'         => $suburb,
			 'AdministrativeArea'  => $state,
			 'Postcode'         => $post_code,
			 'CountryISO3316'   => $country
		 );
	 }

	 /**
	  * creates the customer data for sending with the api
	  */
	 public static function get_customer_data_from_woocommerce_data( $data ) {
		 $country_iso = null;

		 if ( ! empty( $data['country'] ) ) {
			 $country_iso = sanitize_text_field( $data['country'] );
		 }

		 $query = array(
			 'Email'          => sanitize_email( $data['email'] ),
			 'IsConsumer'     => true,
			 'FirstName'      => sanitize_text_field( $data['first_name'] ),
			 'LastName'       => sanitize_text_field( $data['last_name'] ),
			 'CountryISO3316' => $country_iso
		 );

		 if ( ! empty( $data['address_1'] ) ) {
			 $query['Street1'] = sanitize_text_field( $data['address_1'] );
		 }

		 if ( ! empty( $data['city'] ) ) {
			 $query['Suburb'] = sanitize_text_field( $data['city'] );
		 }

		 if ( ! empty( $data['postcode'] ) ) {
			 $query['Postcode'] = sanitize_text_field( $data['postcode'] );
		 }

		 if ( ! empty( $data['state'] ) ) {
			 $query['State'] = sanitize_text_field( $data['state'] );
		 }

		 // Note that standard Woo form does not contain mobile (only phone)
		 if ( ! empty( $data['phone'] ) ) {
			 $query['Phone']                = self::extract_phone_number( sanitize_text_field( $data['phone'] ) );
			 $query['PhoneCountryISO3316']  = $country_iso;
			 $query['Mobile']               = self::extract_phone_number( sanitize_text_field( $data['phone'] ) );
			 $query['MobileCountryISO3316'] = $country_iso;
		 }

		 if ( ! empty( $data['mobile'] ) ) {
			 $query['Mobile']               = self::extract_phone_number( sanitize_text_field( $data['mobile'] ) );
			 $query['MobileCountryISO3316'] = $country_iso;
		 }

		 return $query;
	 }

	 private static function extract_phone_number( $text_value ) {
		 return preg_replace( '/[^0-9]/', '', $text_value );
	 }
 }
?>