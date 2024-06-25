<?php

namespace Skills\WcHalkbankPaymentGateway;

class FailRoute {
    public function __construct() {
        add_action( 'init', [ self::class, 'register_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'query_vars' ] );
        add_action( 'template_redirect', [ $this, 'template_redirect' ] );
    }

    public static function register_rewrite_rules() {
        add_rewrite_rule(
            '^halkbank/payment/failed/?$',
            'index.php?halkbank_payment_failed=1',
            'top'
        );
    }

    public function query_vars( $vars ) {
        $vars[] = 'halkbank_payment_failed';

        return $vars;
    }

    public function template_redirect() {
        if ( get_query_var( 'halkbank_payment_failed' ) === '1' ) {
            $this->handle();
            exit;
        }
    }

    public function handle() {
        if( empty( $_POST ) ) {
            wp_redirect( home_url() );
            exit;
        }

        $order_id = isset( $_POST['orderId'] ) ? $_POST['orderId'] : null;

        if( empty( $order_id ) ) {
            wp_redirect( home_url() );
            exit;
        }

        $order = wc_get_order( $order_id );

        if( ! $order ) {
            wp_redirect( home_url() );
            exit;
        }

        $response = isset( $_POST['Response'] ) ? $_POST['Response'] : null;

        if( $response ) {
            $order->add_order_note( sprintf( __( 'Payment failed: %s', 'wc-halkbank-payment-gateway' ), $response ) );
        }
        
        wp_redirect( add_query_arg( ['payment-failed' => 1], $order->get_checkout_payment_url() ) );
        exit;
    }
}