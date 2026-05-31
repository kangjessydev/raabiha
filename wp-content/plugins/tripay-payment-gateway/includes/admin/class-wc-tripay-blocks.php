<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Tripay_Blocks extends AbstractPaymentMethodType
{
    public static $option_prefix = 'tripay';
    public static $version = '3.3.3';
    public static $baseurl = 'https://tripay.co.id';
    public const SCRIPT_HANDLER = 'wc-tripay-payments-blocks';

    /**
     * Initializes the payment method type.
     */
    public function initialize()
    {
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'wp_add_checkout_fees']);
        add_action('wp_footer', [__CLASS__, 'wp_refresh_checkout_on_payment_methods_change'], 1, 1);
        add_action('woocommerce_review_order_before_payment', [__CLASS__, 'fee'], 1, 1);
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     */
    public function get_payment_method_script_handles(): array
    {
        wp_register_script(
            self::SCRIPT_HANDLER,
            plugins_url('../assets/js/tripay-blocks.min.js', __FILE__),
            [
             'react', 'wc-blocks-registry', 'wp-element', 'wp-html-entities', 'wp-i18n',
            ],
            null,
            true
        );

        $this->localize_wc_blocks_data();

        // if (function_exists('wp_set_script_translations')) {
        //     wp_set_script_translations(self::SCRIPT_HANDLER);
        // }
        return [self::SCRIPT_HANDLER];
    }

    /**
     * @return void
     */
    public function localize_wc_blocks_data()
    {
        wp_localize_script(
            self::SCRIPT_HANDLER,
            'tripayBlockData',
            [
                'gatewayData' => $this->get_payment_method_data(),
            ]
        );
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     */
    public function get_payment_method_data(): array
    {
        $availablePaymentMethods = [];
        $availableGateways = WC()->payment_gateways()->get_available_payment_gateways();
        foreach ($availableGateways as $key => $gateway) {
            if (strpos($key, 'tripay_') === false) {
                unset($availableGateways[$key]);
            }
        }

        foreach ($availableGateways as $gateway) {
            if ($gateway->get_option('enabled') === 'no') {
                continue;
            }
            $titleMarkup = "<span style='margin-right: 1em'>{$gateway->title}</span>{$gateway->get_icon()}";
            $availablePaymentMethods[] = [
                'id' => $gateway->id,
                'title' => $titleMarkup,
                'description' => $gateway->description,
                'supports' => array_filter($gateway->supports, [$gateway, 'supports']),
            ];
        }

        return [
            'availableGateways' => $availablePaymentMethods,
        ];
    }

    public static function gateways($id = null)
    {
        $lists = [
            'alfamart' => [
                'name' => 'Alfamart',
                'code' => 'ALFAMART',
                'class' => 'WC_Gateway_Tripay_ALFAMART',
                'type' => 'DIRECT',
            ],
            'alfamidi' => [
                'name' => 'Alfamidi',
                'code' => 'ALFAMIDI',
                'class' => 'WC_Gateway_Tripay_ALFAMIDI',
                'type' => 'DIRECT',
            ],
            'indomaret' => [
                'name' => 'Indomaret',
                'code' => 'INDOMARET',
                'class' => 'WC_Gateway_Tripay_INDOMARET',
                'type' => 'DIRECT',
            ],
            'bniva' => [
                'name' => 'BNI Virtual Account',
                'code' => 'BNIVA',
                'class' => 'WC_Gateway_Tripay_BNI_VA',
                'type' => 'DIRECT',
            ],
            'briva' => [
                'name' => 'BRI Virtual Account',
                'code' => 'BRIVA',
                'class' => 'WC_Gateway_Tripay_BRI_VA',
                'type' => 'DIRECT',
            ],
            'mandiriva' => [
                'name' => 'Mandiri Virtual Account',
                'code' => 'MANDIRIVA',
                'class' => 'WC_Gateway_Tripay_MANDIRI_VA',
                'type' => 'DIRECT',
            ],
            'bcava' => [
                'name' => 'BCA Virtual Account',
                'code' => 'BCAVA',
                'class' => 'WC_Gateway_Tripay_BCA_VA',
                'type' => 'DIRECT',
            ],
            'maybankva' => [
                'name' => 'Maybank Virtual Account',
                'code' => 'MYBVA',
                'class' => 'WC_Gateway_Tripay_MAYBANK_VA',
                'type' => 'DIRECT',
            ],
            'permatava' => [
                'name' => 'Permata Virtual Account',
                'code' => 'PERMATAVA',
                'class' => 'WC_Gateway_Tripay_PERMATA_VA',
                'type' => 'DIRECT',
            ],
            'sampoernava' => [
                'name' => 'Sahabat Sampoerna Virtual Account',
                'code' => 'SAMPOERNAVA',
                'class' => 'WC_Gateway_Tripay_SAMPOERNA_VA',
                'type' => 'DIRECT',
            ],
            'muamalatva' => [
                'name' => 'Muamalat Virtual Account',
                'code' => 'MUAMALATVA',
                'class' => 'WC_Gateway_Tripay_MUAMALAT_VA',
                'type' => 'DIRECT',
            ],
            'smsva' => [
                'name' => 'Sinarmas Virtual Account',
                'code' => 'SMSVA',
                'class' => 'WC_Gateway_Tripay_SMS_VA',
                'type' => 'DIRECT',
            ],
            'cimbva' => [
                'name' => 'CIMB Niaga Virtual Account',
                'code' => 'CIMBVA',
                'class' => 'WC_Gateway_Tripay_CIMB_VA',
                'type' => 'DIRECT',
            ],
            'bsiva' => [
                'name' => 'BSI Virtual Account',
                'code' => 'BSIVA',
                'class' => 'WC_Gateway_Tripay_BSI_VA',
                'type' => 'DIRECT',
            ],
            'ocbcva' => [
                'name' => 'OCBC NISP Virtual Account',
                'code' => 'OCBCVA',
                'class' => 'WC_Gateway_Tripay_OCBC_VA',
                'type' => 'DIRECT',
            ],
            'danamonva' => [
                'name' => 'Danamon Virtual Account',
                'code' => 'DANAMONVA',
                'class' => 'WC_Gateway_Tripay_DANAMON_VA',
                'type' => 'DIRECT',
            ],
            'otherva' => [
                'name' => 'Other Bank Virtual Account',
                'code' => 'OTHERBANKVA',
                'class' => 'WC_Gateway_Tripay_OTHER_VA',
                'type' => 'DIRECT',
            ],
            'qris' => [
                'name' => 'QRIS by ShopeePay',
                'code' => 'QRIS',
                'class' => 'WC_Gateway_Tripay_QRIS',
                'type' => 'DIRECT',
            ],
            'qrisc' => [
                'name' => 'QRIS Customizable',
                'code' => 'QRISC',
                'class' => 'WC_Gateway_Tripay_QRISC',
                'type' => 'DIRECT',
            ],
            'qris2' => [
                'name' => 'QRIS',
                'code' => 'QRIS2',
                'class' => 'WC_Gateway_Tripay_QRIS2',
                'type' => 'DIRECT',
            ],
            'ovo' => [
                'name' => 'OVO',
                'code' => 'OVO',
                'class' => 'WC_Gateway_Tripay_OVO',
                'type' => 'REDIRECT',
            ],
            'dana' => [
                'name' => 'DANA',
                'code' => 'DANA',
                'class' => 'WC_Gateway_Tripay_DANA',
                'type' => 'REDIRECT',
            ],
            'shopeepay' => [
                'name' => 'ShopeePay',
                'code' => 'SHOPEEPAY',
                'class' => 'WC_Gateway_Tripay_SHOPEEPAY',
                'type' => 'REDIRECT',
            ],
        ];

        return empty($id) ? $lists : (isset($lists[$id]) ? $lists[$id] : null);
    }

    public static function buildApiUrl($path = '')
    {
        $endpoint = !empty(get_option('tripay_mode'))
            ? (get_option('tripay_mode') == 'production' ? esc_url(self::$baseurl).'/api' : esc_url(self::$baseurl).'/api-sandbox')
            : rtrim(get_option('tripay_endpoint'), '/');

        $endpoint = $endpoint ? $endpoint : esc_url(self::$baseurl).'/api-sandbox';

        return rtrim($endpoint, '/').(!empty($path) ? '/'.ltrim($path, '/') : '');
    }

    public static function wp_add_checkout_fees($order_id)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $chosen_gateway = WC()->session->get('chosen_payment_method');

        $gateways = self::gateways();
        foreach ($gateways as $id => $prop) {
            if ($chosen_gateway == self::$option_prefix.'_'.$id) {
                $feeAmount = 0;
                $exchangeValue = get_option(self::$option_prefix.'_exchange_rate', null);

                if (sizeof(WC()->cart->get_fees()) > 0) {
                    $fees = WC()->cart->get_fees();
                    $i = 0;

                    foreach ($fees as $item) {
                        if ($item->name == 'Surcharge') {
                            continue;
                        }

                        $feeAmount = $item->amount;
                    }
                }

                $amount = WC()->cart->cart_contents_total + WC()->cart->shipping_total - WC()->cart->tax_total + $feeAmount;

                self::fee(self::convertUsdToIdr($amount, $exchangeValue));

                $fee = self::get_fee($prop['code']);

                if ($fee > 0) {
                    WC()->cart->add_fee(__('Surcharge', 'wc-tripay'), self::convertFromIdr($fee, get_woocommerce_currency(), $exchangeValue));
                }

                break;
            }
        }
    }

    public static function fee($amount)
    {
        WC()->session->set('tripay_payment_fee', []);

        $url = self::buildApiUrl('/merchant/fee-calculator?amount='.$amount);

        $headers = [
            'Authorization' => 'Bearer '.get_option('tripay_api_key'),
            'X-Plugin-Meta' => 'woocommerce|'.self::$version,
        ];

        $response = wp_remote_post($url, [
            'method' => 'GET',
            'timeout' => 90,
            'headers' => $headers,
        ]);

        if (is_wp_error($response)) {
            (new \WC_Logger())->add('tripay', 'WP Error: '.implode(', ', $response->get_error_messages()));

            return false;
        }

        // Retrieve the body's resopnse if no errors found
        $response_body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code == 200) {
            // Parse the response into something we can read
            $resp = json_decode($response_body);

            if ($resp->success == true) {
                WC()->session->set('tripay_payment_fee', $resp->data);
            }
        }

        return false;
    }

    private static function get_fee($paymentMethod)
    {
        $fee = 0;

        $channels = WC()->session->get('tripay_payment_fee');

        if (empty($channels)) {
            return;
        }

        $exchangeValue = get_option(self::$option_prefix.'_exchange_rate', null);

        foreach ($channels as $channel) {
            if (strtoupper($channel->code) == strtoupper($paymentMethod)) {
                $fee = $channel->total_fee->customer;
                break;
            }
        }

        return $fee;
    }

    public static function wp_refresh_checkout_on_payment_methods_change()
    {
        ?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
    $(document).on('change', 'input[name="radio-control-wc-payment-method-options"]', function() {
        const { extensionCartUpdate } = wc.blocksCheckout;

extensionCartUpdate( {
	namespace: 'checkout-blocks-tripay',
	data: {
		payment_method: document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked').value
	},
} );
    });
});

		</script>
		<?php
    }

    public static function convertUsdToIdr($value, $optionValue = null)
    {
        $currency = get_woocommerce_currency();
        $currentCurrency = strtolower($currency);

        if ($currentCurrency == 'idr') {
            return ceil($value);
        }

        $optionValue = $optionValue ? $optionValue : get_option(self::$option_prefix.'_exchange_rate', null);

        if (empty($optionValue)) {
            (new \WC_Logger())->add('tripay', 'TriPay exchange rate has not been set');

            return 0;
        }

        $optionValue = json_decode($optionValue, true);
        $key = $currentCurrency.'_idr';

        if (!isset($optionValue[$key]) || empty($optionValue[$key])) {
            (new \WC_Logger())->add('tripay', $currency.' to IDR conversion has not been set');

            return 0;
        }

        return ceil($value * $optionValue[$key]);
    }

    public static function convertFromIdr($value, $currency, $optionValue = null)
    {
        $currentCurrency = strtolower($currency);

        if ($currentCurrency == 'idr') {
            return ceil($value);
        }

        $optionValue = $optionValue ? $optionValue : get_option(self::$option_prefix.'_exchange_rate', null);

        if (empty($optionValue)) {
            (new \WC_Logger())->add('tripay', 'TriPay exchange rate has not been set');

            return 0;
        }

        $optionValue = json_decode($optionValue, true);
        $key = $currentCurrency.'_idr';

        if (!isset($optionValue[$key]) || empty($optionValue[$key])) {
            (new \WC_Logger())->add('tripay', $currency.' to IDR conversion has not been set');

            return 0;
        }

        return $value / $optionValue[$key];
    }
}
