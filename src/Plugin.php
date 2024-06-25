<?php

namespace Skills\WcHalkbankPaymentGateway;

class Plugin {
    /**
     * @var Plugin|null
     */
    private static $instance = null;

    private function __construct() {
        $this->init();

        register_activation_hook( WC_HALKBANK_PAYMENT_GATEWAY_FILE, [ $this, 'activate' ] );
    }

    public function init() {
        new WooCommerce();
        new CallbackRoute();
        new ConfirmRoute();
        new FailRoute();
    }

    public static function initialize() {
        if( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function activate() {
        ConfirmRoute::register_rewrite_rules();
        flush_rewrite_rules();
    }
}