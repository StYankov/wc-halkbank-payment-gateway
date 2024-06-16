<?php

namespace Skills\WcHalkbankPaymentGateway;

class ConfirmRoute {
    public function __construct() {
        add_action( 'init', [ self::class, 'register_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'query_vars' ] );
        add_action( 'template_redirect', [ $this, 'template_redirect' ] );
    }

    public static function register_rewrite_rules() {
        add_rewrite_rule(
            '^halkbank/payment/complete/?$',
            'index.php?halkbank_payment_complete=1',
            'top'
        );
    }

    public function query_vars( $vars ) {
        $vars[] = 'halkbank_payment_complete';

        return $vars;
    }

    public function template_redirect() {
        if ( get_query_var( 'halkbank_payment_complete' ) === '1' ) {
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

        if( PaymentService::confirm_payment( $order ) ) {
            wp_redirect( $order->get_checkout_order_received_url() );
            exit;
        }
        
        wp_redirect( home_url() );
        exit;
    }
}