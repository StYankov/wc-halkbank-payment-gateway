<?php

namespace Skills\WcHalkbankPaymentGateway;

class GatewaySettings {
    /**
     * @return string The id of the payment method
     */
    public static function get_method_id() {
        return 'halkbank';        
    }

    private function __construct() { }

    public static function is_method_enabled() {
        return 'yes' === self::get_setting( 'enabled' );
    }

    public static function is_test_mode_enabled() {
        return 'yes' === self::get_setting( 'test_mode' );
    }

    public static function get_client_id() {
        return self::get_setting( 'clientId' );
    }

    public static function get_store_key() {
        return self::get_setting( 'storeKey' );
    }

    public static function get_currency_code() {
        return self::get_setting( 'currencyCode' );
    }

    public static function get_failed_payment_message() {
        return self::get_setting( 'failed_payment_message' );
    }

    /**
     * @param string $key The key of the setting to retrieve
     * 
     * @return mixed The value of the setting
     */
    public static function get_setting( string $key ) {
        $options = get_option( 'woocommerce_' . self::get_method_id() . '_settings' );

        if( false === is_array( $options ) ) {
            return null;
        }

        return isset( $options[ $key ] ) ? $options[ $key ] : null;
    }
}