<?php
/*
 * Plugin Name: HalkBank Payment Gateway
 * Description: A custom payment gateway for WooCommerce to process payments through HalkBank.
 * Version: 1.0.0
 * Author: Stoil Yankov
 * Author URI: https://stoilyankov.com
 * Text Domain: wc-halkbank-payment-gateway
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 5.0
 * WC tested up to: 8.9
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WC_HALKBANK_PAYMENT_GATEWAY_FILE', __FILE__ );

require_once __DIR__ . '/vendor/autoload.php';

Skills\WcHalkbankPaymentGateway\Plugin::initialize();
