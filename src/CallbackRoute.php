<?php

namespace Skills\WcHalkbankPaymentGateway;

class CallbackRoute {
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_route' ] );
    }

    public function register_route() {
        register_rest_route( 'wc-halkbank-payment-gateway/v1', '/callback', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'callback' ],
            'permission_callback' => '__return_true'
        ] );
    }

    public function callback( \WP_REST_Request $request ) {
        $order = wc_get_order( $request->get_param( 'orderId' ) );

        if( empty( $order ) ) {
            return false;
        }

        $order->add_order_note( json_encode( $request->get_params() ) );
        
        return true;
    }
}