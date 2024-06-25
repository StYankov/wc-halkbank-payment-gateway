<?php

namespace Skills\WcHalkbankPaymentGateway;

class PaymentService {
    private function __construct() {}

    public static function get_3d_post_url() {
        if( GatewaySettings::is_test_mode_enabled() ) {
            return 'https://torus-stage-halkbankmacedonia.asseco-see.com.tr/fim/est3Dgate';
        }

        return '';
    }

    /**
     * @param \WC_Order $order
     */
    public static function render_payment_form( \WC_Order $order ) {
        $data = self::get_payload_data( $order );
        ?>

            <form name="pay_form" method="POST" action="<?php echo PaymentService::get_3d_post_url(); ?>">
                <?php foreach( $data as $key => $value ) : ?>
                    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                <?php endforeach; ?>
            </form>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', () => {
                    document.pay_form.submit();
                });
            </script>

        <?php
    }

    public static function get_payload_data( \WC_Order $order ) {
        $data = [
            'clientid'      => GatewaySettings::get_client_id(),
            'amount'        => $order->get_total( 'edit' ),
            'okurl'         => self::get_confirm_url(),
            'failUrl'       => self::get_fail_url(),
            'TranType'      => 'Auth',
            'callbackUrl'   => rest_url( 'wc-halkbank-payment-gateway/v1/callback' ),
            'currency'      => GatewaySettings::get_currency_code(),
            'rnd'           => microtime(),
            'storetype'     => '3D_PAY_HOSTING',
            'hashAlgorithm' => 'ver3',
            'lang'          => 'mk',
            'BillToName'    => $order->get_formatted_billing_full_name(),
            'refreshtime'   => 5,
            'orderId'       => $order->get_id()
        ];

        $data['HASH'] = self::generate_hash( $data );

        return $data;
    }

    /**
     * @param array $data
     * 
     * @return string Hashed value of the data
     */
    public static function generate_hash( $data ) {
        $keys = array_keys( $data );

        natcasesort( $keys );

        $hashval = "";					

        foreach( $keys as $key ) {				
            $escaped_value = str_replace( "|", "\\|", str_replace( "\\", "\\\\", $data[ $key ] ) );	
            $lower_key     = strtolower( $key );
                
            if( $lower_key !== "hash" && $lower_key !== "encoding" ) {
                $hashval = $hashval . $escaped_value . "|";
            }
        }
        
        $escaped_store_key = str_replace( "|", "\\|", str_replace( "\\", "\\\\", GatewaySettings::get_store_key() ) );	
        $hashval           = $hashval . $escaped_store_key;
        
        $calculatedHashValue = hash( 'sha512', $hashval );  
        
        return base64_encode( pack( 'H*', $calculatedHashValue ) );
    }

    public static function maybe_confirm_payment( $data ) {
        if( empty( $data ) ) {
            return false;;
        }

        if( empty( $data['clientId'] ) || $data['clientId'] !== GatewaySettings::get_client_id() ) {
            return false;
        }
    }

    public static function get_confirm_url() {
        return home_url( 'halkbank/payment/complete' );
    }

    public static function get_fail_url() {
        return home_url( 'halkbank/payment/failed' );
    }

    /**
     * @param \WC_Order $order
     * 
     * @return bool True if the payment is confirmed, false otherwise
     */
    public static function confirm_payment( \WC_Order $order ) {
        if( $order->is_paid() ) {
            return true;
        }

        if( $_POST['clientid'] !== GatewaySettings::get_client_id() ) {
            return false;
        }

        $order->payment_complete();

        return true;
    }
}