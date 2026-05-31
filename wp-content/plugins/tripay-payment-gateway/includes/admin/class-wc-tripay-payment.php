<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TripayPayment
{
    public static $tab_name = 'tripay_settings';
    public static $option_prefix = 'tripay';
    public static $version = '3.3.3';
    public static $baseurl = 'https://tripay.co.id';

    public static function init()
    {
        $request = $_REQUEST;

        add_filter('woocommerce_settings_tabs_array', [__CLASS__, 'add_tripay_settings_tab'], 50);
        add_action('woocommerce_settings_tabs_tripay_settings', [__CLASS__, 'tripay_settings_page']);
        add_action('woocommerce_update_options_tripay_settings', [__CLASS__, 'update_tripay_settings']);
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'wp_add_checkout_fees']);
        add_action('woocommerce_review_order_before_payment', [__CLASS__, 'wp_refresh_checkout_on_payment_methods_change']);
        add_action('woocommerce_view_order', [__CLASS__, 'view_order_and_thankyou_page'], 1, 1);
        add_action('woocommerce_thankyou', [__CLASS__, 'view_order_and_thankyou_page'], 1, 1);

        // load fee API before checkout
        add_action('woocommerce_review_order_before_payment', [__CLASS__, 'fee'], 1, 1);
    }

    public static function wp_encrypt($text)
    {
        // ..
    }

    public static function wp_decrypt($text)
    {
        // ..
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

    public static function get_checkout_url($param)
    {
        if (!$param) {
            return '';
        }

        $reference = $param;

        if (is_numeric($param)) {
            $order = new WC_Order($param);
            $reference = $order->get_meta('_tripay_payment_reference');
        } elseif (is_object($param) && ($param instanceof WC_Order)) {
            $reference = $param->get_meta('_tripay_payment_reference');
        }

        return esc_url(self::$baseurl).'/checkout/'.$reference;
    }

    public static function get_qr_url($param)
    {
        if (!$param) {
            return '';
        }

        $reference = $param;

        if (is_numeric($param)) {
            $order = new WC_Order($param);
            $reference = $order->get_meta('_tripay_payment_reference');
        } elseif (is_object($param) && ($param instanceof WC_Order)) {
            $reference = $param->get_meta('_tripay_payment_reference');
        }

        return esc_url(self::$baseurl).'/qr/'.$reference;
    }

    public static function getServerIp()
    {
        $url = self::buildApiUrl('/ip');
        $headers = [
            'X-Plugin-Meta' => 'woocommerce|'.self::$version,
        ];

        $response = wp_remote_post($url, [
            'method' => 'GET',
            'timeout' => 90,
            'headers' => $headers,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        // Retrieve the body's resopnse if no errors found
        $response_body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code == 200) {
            // Parse the response into something we can read
            $resp = json_decode($response_body);

            if ($resp->success == true) {
                return $resp->data->ip;
            }
        }

        return null;
    }

    public static function get_instruction($data)
    {
        $url = self::buildApiUrl('/payment/instruction?code='.$data['code'].'&pay_code='.$data['pay_code'].'&amount='.$data['amount'].'&allow_html=1');

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
            return null;
        }

        // Retrieve the body's resopnse if no errors found
        $response_body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);

        if (intval($response_code) === 200) {
            // Parse the response into something we can read
            $resp = json_decode($response_body);

            if ($resp->success === true) {
                return $resp->data;
            }
        }

        return null;
    }

    public static function view_order_and_thankyou_page($order_id)
    {
        if (!$order_id) {
            return;
        }

        $order = new WC_Order($order_id);
        $reference = $order->get_meta('_tripay_payment_reference');

        if (!empty($reference)) {
            $gateway = self::gateways(str_replace('tripay_', '', $order->get_payment_method()));
            $method = $order->get_payment_method_title();
            $type = $order->get_meta('_tripay_payment_type');
            $checkout_url = self::get_checkout_url($reference);
            $pay_url = $order->get_meta('_tripay_payment_pay_url');
            $pay_url = empty($pay_url) ? $checkout_url : $pay_url;
            $expiredTime = $order->get_meta('_tripay_payment_expired_time');

            $html = '
				  <table class="woocommerce-table shop_table">
					  <tbody>
					  	<tr>
							<th scope="row" style="vertical-align:top">Metode Pembayaran :</th>
							<td style="vertical-align:top">'.esc_html($method).'</td>
						</tr>
						<tr>
							<th scope="row" style="vertical-align:top">No. Referensi :</th>
							<td style="vertical-align:top">'.esc_html($reference).'</td>
						</tr>';

            switch (wp_timezone_string()) {
                case 'Asia/Jakarta':
                    $tz = 'WIB';
                    break;

                case 'Asia/Makassar':
                case 'Asia/Pontianak':
                    $tz = 'WITA';
                    break;

                case 'Asia/Jayapura':
                    $tz = 'WIT';
                    break;

                default:
                    $tz = '';
                    break;
            }

            $datetime = new \DateTime();
            $datetime->setTimestamp($expiredTime);
            $datetime->setTimezone(new \DateTimeZone(wp_timezone_string()));

            if ($type === 'DIRECT') {
                $pay_code = $order->get_meta('_tripay_payment_pay_code');
                $hasQR = $order->get_meta('_tripay_payment_has_qr');

                if (!empty($pay_code) && $hasQR == '0') {
                    $html .= '<tr>
                        <th scope="row" style="vertical-align:top">Kode Bayar/Nomor VA :</th>
                        <td style="vertical-align:top">'.esc_html($pay_code).'</td>
                        </tr>';
                }

                $html .= '<tr>
						<th scope="row" style="vertical-align:top">Batas Pembayaran :</th>
						<td style="vertical-align:top">'.esc_html($datetime->format('d F Y H:i')).' '.esc_html($tz).'</td>
						</tr>';

                if ($hasQR === '1') {
                    $qrUrl = self::get_qr_url($reference);
                    $html .= '<tr>
						<th scope="row" style="vertical-align:top">Kode QR :</th>
						<td style="vertical-align:top"><a href="'.esc_url($qrUrl).'" target="_blank"><img src="'.esc_url($qrUrl).'" style="width:100%;max-width:120px" /></a></td>
						</tr>';
                }
            } else {
                $html .= '<tr>
						<th scope="row" style="vertical-align:top">Batas Pembayaran :</th>
						<td style="vertical-align:top">'.esc_html($datetime->format('d F Y H:i')).' '.esc_html($tz).'</td>
						</tr>';
            }

            if ($order->has_status('pending') || $order->has_status('on-hold')) {
                if ($type === 'REDIRECT') {
                    $html .= '<tr>
                        <th scope="row" colspan="2">
                            <button type="button" class="woocommerce-button button pay" onclick="javascript:window.open(\''.esc_url($pay_url).'\', \'_blank\')">Bayar</button>
                        </th>
                    </tr>';
                } else {
                    $instructions = self::get_instruction([
                        'code' => isset($gateway['code']) ? $gateway['code'] : '',
                        'pay_code' => $pay_code,
                        'amount' => $order->get_meta('_tripay_payment_amount'),
                    ]);

                    if (is_array($instructions)) {
                        $html .= '<tr>
                        <th scope="row" style="vertical-align:top">Cara Pembayaran :</th>
                        <td style="vertical-align:top">';

                        foreach ($instructions as $inst) {
                            $html .= '<div class="panel panel-default">';
                            $html .= '<div class="panel-heading">'.esc_html($inst->title).'</div>';
                            $html .= '<div class="panel-body"><ol>';

                            foreach ($inst->steps as $step) {
                                $html .= '<li>'.esc_html(strip_tags($step)).'</li>';
                            }

                            $html .= '</ol></div>';
                            $html .= '</div>';
                        }

                        $html .= '</td></tr>';
                    }
                }
            }

            $html .= '</tbody></table>';

            echo $html;
        }
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
		console.log('test');
			(function($){
				$('form.checkout').on('change', 'input[name^="payment_method"]', function() {
					$('body').trigger('update_checkout');

				});
			})(jQuery);
		</script>
		<?php
    }

    public static function add_tripay_settings_tab($woocommerce_tab)
    {
        $woocommerce_tab[self::$tab_name] = 'TriPay '.__('Global Configuration', 'wc-tripay');

        return $woocommerce_tab;
    }

    public static function tripay_settings_fields()
    {
        global $tripay_payments;

        $settings = apply_filters('woocommerce_'.self::$tab_name, [
            [
                'title' => 'TriPay '.__('Global Configuration', 'wc-tripay'),
                'id' => esc_html(self::$option_prefix).'_global_settings',
                'desc' => '',
                'type' => 'title',
                'default' => '',
            ],
            [
                'title' => __('Mode Integrasi', 'wc-tripay'),
                'type' => 'select',
                'desc' => __('Mode integrasi sistem.<br/><b>Sandbox</b> digunakan untuk masa pengembangan<br/><b>Production</b> digunakan untuk transaksi riil', 'wc-tripay'),
                'id' => esc_html(self::$option_prefix).'_mode',
                'default' => 'sandbox',
                'options' => [
                    'sandbox' => 'Sandbox',
                    'production' => 'Production',
                ],
                'css' => 'width:25em;',
            ],
            [
                'title' => __('Merchant Code', 'wc-tripay'),
                'desc' => 'Untuk mode <b>Sandbox</b> lihat <a href="'.esc_url(self::$baseurl).'/simulator/merchant" traget="_blank">di sini</a><br>Untuk mode <b>Production</b> lihat <a href="'.esc_url(self::$baseurl).'/member/merchant" traget="_blank">di sini</a>',
                'id' => esc_html(self::$option_prefix).'_merchant_code',
                'type' => 'text',
                'css' => 'width:25em;',
                'default' => '',
            ],
            [
                'title' => __('API Key', 'wc-tripay'),
                'desc' => 'Untuk mode <b>Sandbox</b> lihat <a href="'.esc_url(self::$baseurl).'/simulator/merchant" traget="_blank">di sini</a><br>Untuk mode <b>Production</b> lihat <a href="'.esc_url(self::$baseurl).'/member/merchant" traget="_blank">di sini</a> lalu klik tombol <b>Opsi > Edit</b>',
                'id' => esc_html(self::$option_prefix).'_api_key',
                'type' => 'text',
                'css' => 'width:25em;',
                'default' => '',
            ],
            [
                'title' => __('Private Key', 'wc-tripay'),
                'desc' => 'Untuk mode <b>Sandbox</b> lihat <a href="'.esc_url(self::$baseurl).'/simulator/merchant" traget="_blank">di sini</a><br>Untuk mode <b>Production</b> lihat <a href="'.esc_url(self::$baseurl).'/member/merchant" traget="_blank">di sini</a> lalu klik tombol <b>Opsi > Edit</b>',
                'id' => esc_html(self::$option_prefix).'_private_key',
                'type' => 'text',
                'css' => 'width:25em',
                'default' => '',
            ],
            [
                'title' => __('Aktifkan Debugging', 'wc-tripay'),
                'desc' => __('Aktifkan/Nonaktifkan log transaksi.<br/>Log dapat dilihat di menu WooCommerce > Status > Logs'),
                'id' => esc_html(self::$option_prefix).'_debug',
                'type' => 'checkbox',
                'default' => 'no',
                'css' => 'width:25em;',
            ],
            [
                'title' => __('Aktifkan Verifikasi Callback', 'wc-tripay'),
                'desc' => 'Untuk keamanan, sebaiknya diaktifkan untuk mode Production dan dinonaktifkan untuk mode Sandbox',
                'id' => esc_html(self::$option_prefix).'_verify_callback',
                'type' => 'checkbox',
                'default' => 'no',
                'css' => 'width:25em;',
            ],
            [
                'title' => __('Status Pesanan Awal', 'wc-tripay'),
                'type' => 'select',
                'desc' => __('Status pesanan awal sebelum pembayaran dilakukan', 'wc-tripay'),
                'id' => esc_html(self::$option_prefix).'_initial_status',
                'default' => 'pending',
                'options' => [
                    'pending' => 'Pending',
                    'on-hold' => 'On Hold',
                ],
                'css' => 'width:25em;',
            ],
            [
                'title' => __('Status Sukses', 'wc-tripay'),
                'type' => 'select',
                'desc' => __('Status pesanan setelah pembayaran berhasil', 'wc-tripay'),
                'id' => esc_html(self::$option_prefix).'_success_status',
                'default' => 'processing',
                'options' => [
                    'completed' => 'Completed',
                    'on-hold' => 'On Hold',
                    'processing' => 'Processing',
                ],
                'css' => 'width:25em;',
            ],
            [
                'title' => __('Aktifkan Email Invoice ke Pelanggan', 'wc-tripay'),
                'desc' => 'Aktifkan/nonaktifkan email invoice yang dikirim ke pelanggan',
                'id' => esc_html(self::$option_prefix).'_customer_invoice_email',
                'type' => 'checkbox',
                'default' => 'yes',
                'css' => 'width:25em;',
            ],
            [
                'title' => __('Setelah checkout redirect ke?', 'wc-tripay'),
                'label' => '',
                'type' => 'select',
                'description' => __('Setelah konsumen checkout, pilih ke halaman mana pelanggan akan dialihkan', 'wc-tripay'),
                'default' => 'thankyou',
                'options' => [
                    'thankyou' => 'Thank You Page',
                    'orderpay' => 'Order Pay',
                ],
                'id' => esc_html(self::$option_prefix).'_redirect_page',
                'css' => 'width:25em;',
            ],
        ]);

        return apply_filters('woocommerce_'.self::$tab_name, $settings);
    }

    public static function tripay_settings_page()
    {
        $form = self::tripay_settings_fields();

        woocommerce_admin_fields($form);

        $currencyExhanges = get_option('tripay_exchange_rate', '{"usd_idr": 0}');
        $currencyExhanges = json_decode($currencyExhanges, true);

        $i = 0;
        foreach ($currencyExhanges as $key => $value) {
            $fromCur = strtoupper(explode('_', $key)[0]);
            $echo = '<tr valign="top" class="currency_conversion_field">
				<th scope="row" class="titledesc">';

            if ($i == 0) {
                $echo .= '<label>Kurs Konversi ke IDR</label>';
            }

            $echo .= '</th>
				<td class="forminp forminp-text">
					<div style="width:7em;display:inline-block;margin-right:5px">
						<input name="tripay_exchange_rate_from[]" type="text" value="'.esc_html($fromCur).'" class="" placeholder="Mata uang" style="width:100%;text-transform:uppercase">
					</div>
					<div style="width:10em;display:inline-block;margin-right:5px">
						<input name="tripay_exchange_rate_value[]" type="number" value="'.esc_html($value).'" class="" placeholder="Nilai Tukar" style="width:100%">
					</div>
					<div style="width:4em;display:inline-block">';

            if ($i == 0) {
                $echo .= '<button type="button" style="vertical-align: top;font-size: 18px;" onclick="addCurrencyConversionField()">+</button>';
            } else {
                $echo .= '<button type="button" style="vertical-align: top;font-size: 18px;" onclick="removeCurrencyConversionField(this)">x</button>';
            }

            $echo .= '
					</div>
				</td>
			</tr>';

            echo $echo;

            ++$i;
        }

        $ip = self::getServerIp();

        echo '<tr valign="top"><th scope="row" class="titledesc"><label for="tripay_callback">Callback URL </label></th><td class="forminp forminp-text"><input id="tripay_callback" type="text" value="'.self::callback_url().'" class="" placeholder="" readonly="true" style="width:25em;"><p class="description">Masukan link diatas ke kolom URL Callback di <a href="'.esc_url(self::$baseurl).'/member/merchant">di sini</a> lalu klik tombol <b>edit</b> sesuai merchant anda<br></p></td></tr>';

        if (!empty($ip)) {
            echo '<tr valign="top"><th scope="row" class="titledesc"><label for="tripay_server_ip">Server IP </label></th><td class="forminp forminp-text"><input id="tripay_server_ip" type="text" style="width:25em;" value="'.esc_html($ip).'" class="" placeholder="" readonly="true"><p class="description">Untuk keamanan tambahan (tidak wajib), tambahkan IP diatas ke kolom Whitelist IP di <a href="'.esc_url(self::$baseurl).'/member/merchant">di sini</a> lalu klik tombol <b>edit</b> sesuai merchant anda<br></p></td></tr>';
        }

        echo '<script type="text/javascript">
			function addCurrencyConversionField() {
				var field = "<tr valign=\"top\" class=\"currency_conversion_field\"><th scope=\"row\" class=\"titledesc\"></th><td class=\"forminp forminp-text\"><div style=\"width:7em;display:inline-block;margin-right:9px\"><input name=\"tripay_exchange_rate_from[]\" type=\"text\" value=\"\" class=\"\" placeholder=\"Mata uang\" style=\"width:100%;text-transform:uppercase\"></div><div style=\"width:10em;display:inline-block;margin-right:9px\"><input name=\"tripay_exchange_rate_value[]\" type=\"number\" value=\"\" class=\"\" placeholder=\"Nilai Tukar\" style=\"width:100%\"></div><div style=\"width:4em;display:inline-block\"><button type=\"button\" style=\"vertical-align: top;font-size: 18px;padding: 0px 8px;\" onclick=\"removeCurrencyConversionField(this)\">x</button></div></td></tr>";

				jQuery(field).insertAfter(jQuery(".currency_conversion_field")[jQuery(".currency_conversion_field").length-1]);
			}

			function removeCurrencyConversionField(obj) {
				jQuery(obj).parent().parent().parent().remove();
			}

		</script>';
    }

    public static function update_tripay_settings()
    {
        if (!isset($_POST['tripay_exchange_rate_from']) || !isset($_POST['tripay_exchange_rate_value'])) {
            return;
        }

        $exchangeFrom = $_POST['tripay_exchange_rate_from'];
        $exchangeValue = $_POST['tripay_exchange_rate_value'];

        unset($_POST['tripay_exchange_rate_from'], $_POST['tripay_exchange_rate_value']);

        $i = 0;
        $values = [];

        foreach ($exchangeFrom as $exFrom) {
            if (!empty($exFrom)) {
                $values[strtolower($exFrom).'_idr'] = $exchangeValue[$i];
            }
            ++$i;
        }
        update_option(self::$option_prefix.'_exchange_rate', json_encode($values));

        woocommerce_update_options(self::tripay_settings_fields());
    }

    public static function callback_url()
    {
        $checkoutUrl = wc_get_checkout_url();
        $hasQuery = !empty(parse_url($checkoutUrl, PHP_URL_QUERY));

        if ($hasQuery) {
            $checkoutUrl = rtrim($checkoutUrl, '&').'&wc-api=wc_gateway_tripay';
        } else {
            $checkoutUrl .= '?wc-api=wc_gateway_tripay';
        }

        return $checkoutUrl;
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

TripayPayment::init();
