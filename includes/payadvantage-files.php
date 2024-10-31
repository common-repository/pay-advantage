<?php
/**
 * Includes files for running the plugin.
 */
include_once( plugin_dir_path( __FILE__ ) . '../shared/payadvantage-utils.php' );
include_once( plugin_dir_path( __FILE__ ) . '../admin/options-page.php' );
include_once( plugin_dir_path( __FILE__ ) . '../admin/options-ajax.php' );
include_once( plugin_dir_path( __FILE__ ) . '../shared/payadvantage-api.php' );
include_once( plugin_dir_path( __FILE__ ) . '../shared/payadvantage-api-error-handler.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/ajax-payadvantage-customer.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/ajax-payadvantage-creditcard.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/ajax-payadvantage-woocommerce.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/cls-payadvantage-json-converter.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/cls-payadvantage-validator.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/widget-payadvantage-register-bpay.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/widget-payadvantage-make-payment.php' );
include_once( plugin_dir_path( __FILE__ ) . '../public/cls-payadvantage-woocommerce.php' );
include_once( plugin_dir_path( __FILE__ ) . '../migration/migration.php' );
?>