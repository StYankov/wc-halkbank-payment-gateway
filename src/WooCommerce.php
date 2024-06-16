<?php

namespace Skills\WcHalkbankPaymentGateway;

class WooCommerce {
    public function __construct() {
        add_filter( 'woocommerce_payment_gateways', [ $this, 'payment_gateways' ] );
    }

    public function payment_gateways( $gateways ) {
        $gateways[] = WCHalkBankPaymentGateway::class;

        return $gateways;
    }
}