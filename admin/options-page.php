<?php
/**
 * Contains setup for the options page. Sets up javascript and calls html for viewing the options page.
 */
include( plugin_dir_path( __FILE__ ) . '/html/options-html.php' );

/**
 * Sets up menu items in settings tab of the admin screen.
 */
function pay_advantage_register_option_page() {
	add_options_page( 'Pay Advantage', 'Pay Advantage', 'manage_options', 'PayAdvantage', 'pay_advantage_options_page' );
}

add_action( 'admin_menu', 'pay_advantage_register_option_page' );
// Use a higher priority to load the CSS and JS after the theme and WooCommerce.
add_action( 'admin_enqueue_scripts', 'pay_advantage_options_page_load', 99 );

/**
 * Sets up all the scripts for the page.
 */
function pay_advantage_options_page_load( $handle ) {
	if ( 'settings_page_PayAdvantage' != $handle ) {
		return;
	}
	wp_register_style( 'pay_advantage_css', payadvantage_plugin_url( 'public/css/payadvantage.css' ), array(), PayAdvantagePluginVersion );
	wp_enqueue_style( 'pay_advantage_css' );

	wp_register_script( 'jquery-blockui', payadvantage_plugin_url( 'public/js/jquery-blockui/jquery.blockUI.min.js' ), array( 'jquery' ), '2.70', true );
	wp_register_script( 'pay_advantage_common', payadvantage_plugin_url( 'public/js/common.js' ), array(
		'jquery',
		'jquery-blockui'
	), PayAdvantagePluginVersion );
	wp_register_script( 'pay_advantage_options_page', payadvantage_plugin_url( 'admin/js/options-page.js' ), array( 'pay_advantage_common' ), PayAdvantagePluginVersion );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'pay_advantage_options_page', 'pay_advantage_ajax_object', array(
		'pay_advantage_ajax_url'                           => admin_url( 'admin-ajax.php' ),
		'pay_advantage_require_mobile'                     => get_option( 'pay_advantage_require_mobile' ),
		'pay_advantage_require_address'                    => get_option( 'pay_advantage_require_address' ),
		'pay_advantage_require_country'                    => get_option( 'pay_advantage_require_country' ),
		'pay_advantage_show_bpay'                          => get_option( 'pay_advantage_show_bpay' ),
		'pay_advantage_show_credit_card'                   => get_option( 'pay_advantage_show_credit_card' ),
		'pay_advantage_oncharge_credit_card_fees'          => get_option( 'pay_advantage_oncharge_credit_card_fees' ),
		'pay_advantage_wc_oncharge_credit_card_fees'       => get_option( 'pay_advantage_wc_oncharge_credit_card_fees' ),
		'pay_advantage_show_widget_to_users_not_logged_in' => get_option( 'pay_advantage_show_widget_to_users_not_logged_in' ),
		'pay_advantage_nonce'                              => wp_create_nonce( 'pay_advantage_nonce' )
	) );

	//Activates the script
	wp_enqueue_script( 'pay_advantage_options_page', '', array(), PayAdvantagePluginVersion );
}

?>