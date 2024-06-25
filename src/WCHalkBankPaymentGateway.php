<?php

namespace Skills\WcHalkbankPaymentGateway;

class WCHalkBankPaymentGateway extends \WC_Payment_Gateway {
    public function __construct() {
        $this->setup_properties();

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
        add_action( 'woocommerce_receipt_' . $this->id, [ $this, 'render_payment_form' ] );
        add_action( 'wp_head', [ $this, 'failed_payment_notice' ] );
    }

    protected function setup_properties() {
		$this->id                 = GatewaySettings::get_method_id();
		$this->icon               = apply_filters( 'woocommerce_halkbank_icon', '' );
		$this->method_title       = __( 'Halkbank POS', 'wc-halkbank-payment-gateway' );
		$this->method_description = __( 'Payments using the HalkBank POS system', 'wc-halkbank-payment-gateway' );
		$this->has_fields         = false;
	}

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'       => __( 'Enable/Disable', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable Custom Payment Gateway', 'woocommerce' ),
                'default'     => 'yes',
            ],
            'title' => [
                'title'       => __( 'Title', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'wc-halkbank-payment-gateway' ),
                'default'     => __( 'HalkBank Payment', 'woocommerce' ),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __( 'Description', 'woocommerce' ),
                'type'        => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
                'default'     => __( 'Pay using our custom payment gateway.', 'woocommerce' ),
            ],
            'failed_payment_message' => [
                'title'       => __( 'Failed Payment Message', 'wc-halkbank-payment-gateway' ),
                'type'        => 'textarea',
                'description' => __( 'This message will be displayed to the user when the payment fails.', 'wc-halkbank-payment-gateway' ),
                'default'     => __( 'Payment failed. Try again', 'wc-halkbank-payment-gateway' ),
            ],
            'test_mode'   => [
                'title'       => __( 'Test Mode', 'wc-halkbank-payment-gateway' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable Test Mode', 'wc-halkbank-payment-gateway' ),
                'default'     => 'yes',
                'description' => __( 'Place the payment gateway in test mode using test API keys.', 'wc-halkbank-payment-gateway' ),
            ],
            'clientId'    => [
                'title'       => __( 'Client ID', 'wc-halkbank-payment-gateway' ),
                'type'        => 'text',
                'description' => __( 'This is the client ID provided by HalkBank.', 'wc-halkbank-payment-gateway' ),
            ],
            'storeKey'    => [
                'title'       => __( 'Store Key', 'wc-halkbank-payment-gateway' ),
                'type'        => 'text',
                'description' => __( 'This is the store key provided by HalkBank.', 'wc-halkbank-payment-gateway' ),
            ],
            'currencyCode' => [
                'title'       => __( 'Currency Code', 'wc-halkbank-payment-gateway' ),
                'description' => __( 'Currency code in ISO 4217 standard.', 'wc-halkbank-payment-gateway' ),
                'type'        => 'text',
                'default'     => '807'
            ]
        ];
    }

    /**
     * @param string|int $order_id
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        $order->add_order_note(
            __( 'HalkBank payment process is started', 'wc-halkbank-payment-gateway' )
        );

        return [
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url( true )
        ];
    }

    /**
     * @param int $order_id
     */
    public function render_payment_form( $order_id ) {
        PaymentService::render_payment_form( wc_get_order( $order_id ) );
    }

    public function failed_payment_notice() {
        if( false === is_checkout() ) {
            return;
        }

        $order_id = get_query_var( 'order-pay' );

        if( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        
        if( $order && $order->get_payment_method() === GatewaySettings::get_method_id() && ! empty( $_GET['payment-failed'] ) ) {
            wc_add_notice( GatewaySettings::get_failed_payment_message(), 'error' );
        }
    }
}