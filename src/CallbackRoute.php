<?php

namespace Skills\WcHalkbankPaymentGateway;

use WP_REST_Response;

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

        if( $request->get_param( 'ProcReturnCode' ) !== '00' ) {
            return false;
        }

        if( ! $order->is_paid() ) {
            $order->payment_complete( $request->get_param( 'TransId' ) );
        }

        $response = new WP_REST_Response( 'response=approved' );

        $response->header( 'Content-Type', 'text/plain' );

        return $response;
    }
}