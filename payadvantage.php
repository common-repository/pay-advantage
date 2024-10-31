<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.payadvantage.com.au/
 * @since             1.0.0
 * @package           PayAdvantage
 *
 * @wordpress-plugin
 * Plugin Name:       Pay Advantage
 * Plugin URI:        https://www.payadvantage.com.au/
 * Description:       This plugin adds a payment gateway to Woo Commerce as well as a widget for credit card and BPay payments.
 * Version:           3.3.1
 * Author:            Pay Advantage
 * Author URI:        https://www.payadvantage.com.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       PayAdvantage
 * Domain Path:       /languages
 * WC tested up to: 8.3.0
 * WC requires at least: 3.7
 */

/**
 * Current plugin version.
 */
define( 'PayAdvantagePluginVersion', '3.3.1' );

include( plugin_dir_path( __FILE__ ) . '/includes/payadvantage-files.php' );

// Load override definitions for development/ testing etc
$local_environment_overrides_path = plugin_dir_path( __FILE__ ) . 'payadvantage-overrides.php';
if ( file_exists( $local_environment_overrides_path ) ) {
	include( $local_environment_overrides_path );
} else {
	define( 'PAYADV_APP_ID', 'FFARQA' );
	define( 'PAYADV_REGISTRATION_URL_SANDBOX', 'https://test.payadvantage.com.au/signin' );
	define( 'PAYADV_REGISTRATION_URL_LIVE', 'https://secure.payadvantage.com.au/signin' );
	define( 'PAYADV_API_URL_SANDBOX', 'https://api.test.payadvantage.com.au/v3' );
	define( 'PAYADV_API_URL_LIVE', 'https://api.payadvantage.com.au/v3' );
	define( 'PAYADV_MAX_LOG_SIZE_CHARS', 2000 );
}

function activate_payadvantage() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-payadvantage-activator.php';
	Pay_Advantage_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_payadvantage' );

/*
 * Show significant notices on the admin dashboard.
 */
function payadvantage_system_notices() {
	$screen = get_current_screen();
	// Only render this notice on the dashboard and the plugins page.
	if ( ! $screen || ( 'dashboard' !== $screen->base && 'plugins' !== $screen->base ) ) {
		return;
	}

	$is_legacy_connection = ! empty( get_option( 'pay_advantage_user_name' ) );

	$phpiniPrecision = ini_get('serialize_precision');

	if ( $is_legacy_connection ) {
		echo '<div class="notice notice-error is-dismissible">';
		echo '<p>Pay Advantage is no longer connected. Please reconnect the Pay Advantage plugin in the settings.</p>';
		echo '</div>';
	} else if ( $phpiniPrecision !== "-1") {
		//Shows up as a warning on the plug-in and dashboard screen
		echo '<div class="notice notice-warning is-dismissible">';
		echo '<p>Pay Advantage plugin has detected issues with the current PHP settings. Please change the PHP <span style="font-weight:bold;">serialize_precision</span> setting to "-1” to ensure Pay Advantage accurately calculates fees.';
		echo '<p>The current setting value is: ' . $phpiniPrecision . '</p>';
		echo '</div>';
	}
}

add_action( 'admin_notices', 'payadvantage_system_notices' );

/*
 * Add additional information to the user agent when talking to the Pay Advantage API.
 */
function payadvantage_useragent( $current_user_agent, $target_url ) {
	$payadvantage_url = get_option( 'pay_advantage_url' );
	if ( ! isset( $payadvantage_url ) || $payadvantage_url == '' || $payadvantage_url == null ) {
		return $current_user_agent;
	}

	if ( strpos( $target_url, $payadvantage_url ) === false ) {
		return $current_user_agent;
	}

	// Add the current plugin version
	$user_agent = $current_user_agent . '; PayAdvantagePlugin/' . PayAdvantagePluginVersion;

	// Ensure the PHP version is included.
	if ( strpos( $user_agent, 'PHP/' ) === false ) {
		$user_agent .= '; PHP/' . phpversion();
	}

	// Ensure the WordPress version is included.
	if ( strpos( $user_agent, 'WordPress/' ) === false ) {
		$user_agent .= '; WordPress/' . get_bloginfo( 'version' );
	}

	// Ensure the WooCommerce version is included, if installed.
	if ( class_exists( 'WooCommerce' ) && strpos( $user_agent, 'WooCommerce/' ) === false ) {
		global $woocommerce;
		$user_agent .= '; WooCommerce/' . $woocommerce->version;
	}

	return $user_agent;
}

add_filter( 'http_headers_useragent', 'payadvantage_useragent', 10, 2 );

/**
 * @param [string] $path
 */
function payadvantage_plugin_url( $path ) {
	return plugins_url( $path, __FILE__ );
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

?>