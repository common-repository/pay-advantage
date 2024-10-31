<?php

$transient = 'pay_advantage_migration';
$current_db_version = get_option('pay_advantage_db_version');

if ( empty( $current_db_version ) ) {
	$current_db_version = '2.1.0';
}

// Check the current DB version, and upgrade it if needed.
// Use a transient to protect against migration from being run multiple times.
if ( version_compare( $current_db_version, '3.1.0' ) < 0 && !get_transient( $transient ) ) {
	set_transient( $transient, 'locked', 60 ); // lock function for 1 Minute
	add_action( 'plugins_loaded', 'pay_advantage_migration' ); // Execute the migration next time the plugins are loaded.
}

/**
 * Migrate the Pay Advantage plugin data.
 */
function pay_advantage_migration() {
	// Version 3.0.0 added an instance id.
	if ( version_compare( get_option( 'pay_advantage_db_version' ), '3.0.0' ) < 0 ) {
		$instance_id = get_option( 'pay_advantage_instance_id' );
		if ( empty( $instance_id ) ) {
			update_option( 'pay_advantage_instance_id', uniqid() );
		}
	}

	// 3.1.0 switched to use the hosted iframe, upgraded oauth and 3d secure
	if ( version_compare( get_option( 'pay_advantage_db_version' ), '3.1.0' ) < 0 ) {
		update_option( 'pay_advantage_make_payment_button', 'Make a Payment' );
        update_option( 'pay_advantage_oncharge_credit_card_fees', 1 );
        update_option( 'pay_advantage_wc_oncharge_credit_card_fees', 1 );

		$is_connected = ! empty( get_option( 'pay_advantage_refresh_token' ) );
		if ( $is_connected ) {
			update_option( 'pay_advantage_app_client_id', 'FFARQA' );
			update_option( 'pay_advantage_app_code_verifier', null );
		}
	}

    // 3.1.1 Introduced setting the WC order status after successful payment
    if ( version_compare( get_option( 'pay_advantage_db_version' ), '3.1.1' ) < 0 ) {
        // Set it to completed to be compatible with the previous versions.
        update_option( 'pay_advantage_wc_paid_status', 'completed' );
    }

	// 3.1.3 Introduced setting the WC order status after cancelling a payment
	if ( version_compare( get_option( 'pay_advantage_db_version' ), '3.1.3' ) < 0 ) {
		// Set it to completed to be compatible with the previous versions.
		update_option( 'pay_advantage_wc_cancel_status', 'pending' );
	}

	update_option( 'pay_advantage_db_version', '3.1.3' );
}