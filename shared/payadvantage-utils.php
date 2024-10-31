<?php

function pay_advantage_send_response( $data ) {
	if ( is_array( $data ) ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $data );
	} else {
		echo $data;
	}
}

function pay_advantage_write_error_to_response( $messages ) {
	if ( is_string( $messages ) ) {
		$messages_array[] = $messages;
		pay_advantage_send_response( array( "Messages" => $messages_array ) );
	} else {
		pay_advantage_send_response( array( "Messages" => $messages ) );
	}
}

function pay_advantage_has_messages( $result ) {
	return isset( $result['Messages'] ) && count( $result['Messages'] ) > 0;
}