<?php
/**
 *Registers the widget with Wordpress.
 */

include_once( plugin_dir_path( __FILE__ ) . '/html/creditcard-tab-html.php' );
include_once( plugin_dir_path( __FILE__ ) . '/ajax-payadvantage-creditcard.php' );
include_once( plugin_dir_path( __FILE__ ) . '/cls-payadvantage-validator.php' );

class Pay_Advantage_Credit_Card_Widget extends WP_Widget {
    // Main constructor
    public function __construct() {
        parent::__construct(
            'pay_advantage_credit_card_widget',
            __( 'Pay Advantage - Take Credit Card Payment', 'text_domain' ),
            array(
                'customize_selective_refresh' => true,
            )
        );
    }

    public $args = array(
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget'  => '</div></div>'
    );

    // The widget form (for the backend )
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( '', 'text_domain' );
?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    // Update widget settings
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }

    // Display the widget
    public function widget( $args, $instance ) {

        if ( current_user_can( 'read' ) == 0 && get_option( 'pay_advantage_show_widget_to_users_not_logged_in' ) == "0"
                || !sanitize_text_field( get_option( 'pay_advantage_show_credit_card' ) ) ) {
            return;
        }

        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        echo pay_advantage_credit_card_html();

        echo $args['after_widget'];
    }
}

//Register the widget.
add_action( 'widgets_init', 'pay_advantage_credit_card_widget_handler' );

function pay_advantage_credit_card_widget_handler() {
    if ( sanitize_text_field( get_option( 'pay_advantage_show_credit_card' ) ) ) {
        register_widget( 'pay_advantage_credit_card_widget' );
    }
}

//Loads script.
function register_pay_advantage_credit_card_scripts_load() {
	$api_url = get_option( 'pay_advantage_url' );

	wp_register_style( 'pay_advantage_css', payadvantage_plugin_url( 'public/css/payadvantage.css' ), array(), PayAdvantagePluginVersion );
	wp_enqueue_style( 'pay_advantage_css' );

	wp_register_style( 'pay_advantage_cc_iframe', $api_url . '/creditcardcapture.css', array(), PayAdvantagePluginVersion );
	wp_enqueue_style( 'pay_advantage_cc_iframe' );

	wp_register_script( 'jquery-blockui', payadvantage_plugin_url( 'public/js/jquery-blockui/jquery.blockUI.min.js' ), array( 'jquery' ), '2.70', true );
	wp_register_script(
		'pay_advantage_common',
        payadvantage_plugin_url( 'public/js/common.js' ),
		array(
			'jquery',
			'jquery-blockui'
		),
		PayAdvantagePluginVersion );
	wp_register_script( 'pay_advantage_credit_card', payadvantage_plugin_url( 'public/js/credit-card-payment.js' ), array( 'pay_advantage_common' ), PayAdvantagePluginVersion );
	wp_register_script( 'pay_advantage_credit_card_wc', payadvantage_plugin_url( 'public/js/credit-card-payment-wc.js' ), array( 'pay_advantage_common' ), PayAdvantagePluginVersion );
	wp_register_script( 'pay_advantage_customer', payadvantage_plugin_url( 'public/js/customer.js' ), array( 'pay_advantage_common' ), PayAdvantagePluginVersion );
	wp_register_script( 'pay_advantage_cc_iframe', $api_url . '/creditcardcapture.js', array(), PayAdvantagePluginVersion );
	wp_register_script(
		'pay_advantage_make_payment',
        payadvantage_plugin_url( 'public/js/make-payment.js' ),
		array(
			'pay_advantage_credit_card',
			'pay_advantage_credit_card_wc',
			'pay_advantage_common',
			'pay_advantage_customer',
			'jquery',
			'pay_advantage_cc_iframe'
		),
		PayAdvantagePluginVersion );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script(
		'pay_advantage_make_payment',
		'pay_advantage_ajax_object',
		array(
			'pay_advantage_ajax_url'        => admin_url( 'admin-ajax.php' ),
			'pay_advantage_require_mobile'  => sanitize_text_field( get_option( 'pay_advantage_require_mobile' ) ),
			'pay_advantage_require_address' => sanitize_text_field( get_option( 'pay_advantage_require_address' ) ),
			'pay_advantage_nonce'           => wp_create_nonce( 'pay_advantage_nonce' )
		) );

	//Activates the script
	wp_enqueue_script( 'pay_advantage_make_payment' );
}

// Use a higher priority to load the CSS and JS after the theme and WooCommerce.
add_action( 'wp_enqueue_scripts', 'register_pay_advantage_credit_card_scripts_load', 99 );

?>