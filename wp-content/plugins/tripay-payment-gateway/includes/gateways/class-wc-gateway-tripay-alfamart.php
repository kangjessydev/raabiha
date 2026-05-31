<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Gateway_Tripay_ALFAMART extends Tripay_Payment_Gateway
{
    public $sub_id = 'tripay_alfamart';

    public function __construct()
    {
        parent::__construct();

        $this->method_title = 'TriPay - Alfamart';
        $this->method_description = 'Pembayaran melalui Alfamart';
        $this->payment_method = 'ALFAMART';

        $this->init_form_fields();
        $this->init_settings();

        if ($this->settings['enable_icon'] == 'yes') {
            $this->icon = !empty($this->settings['custom_icon']) ? esc_url($this->settings['custom_icon']) : esc_url(plugins_url('/assets/alfamart.png', dirname(__FILE__)));
        }
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable/Disable', 'wc-tripay'),
                'label' => __('Aktifkan Alfamart', 'wc-tripay'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ],
            'title' => [
                'title' => __('Judul', 'wc-tripay'),
                'type' => 'text',
                'description' => __('Nama Metode Pembayaran', 'wc-tripay'),
                'default' => __('Alfamart', 'wc-tripay'),
            ],
            'enable_icon' => [
                'title' => __('Ikon Pembayaran', 'wc-tripay'),
                'label' => __('Aktifkan Ikon', 'wc-tripay'),
                'type' => 'checkbox',
                'description' => '<img src="'.esc_url(plugins_url('/assets/alfamart.png', dirname(__FILE__))).'" style="height:100%;max-height:40px !important" />',
                'default' => 'no',
            ],
            'custom_icon' => [
                'title' => __('URL Ikon Pembayaran Kustom', 'wc-tripay'),
                'label' => __('URL Ikon Pembayaran Kustom', 'wc-tripay'),
                'type' => 'text',
                'description' => 'URL kustom untuk menggunakan ikon pembayaran pribadi. Jika kosong akan menggunakan ikon default diatas',
                'default' => '',
            ],
            'description' => [
                'title' => __('Deskripsi', 'wc-tripay'),
                'type' => 'textarea',
                'description' => __('', 'wc-tripay'),
                'default' => 'Pembayaran melalui Alfamart',
            ],
            'expired' => [
                'title' => __('Masa Berlaku Kode Bayar', 'wc-tripay'),
                'type' => 'select',
                'description' => __('', 'wc-tripay'),
                'default' => '1',
                'options' => [
                    '1' => '1 Hari',
                    '2' => '2 Hari',
                    '3' => '3 Hari',
                    '4' => '4 Hari',
                    '5' => '5 Hari',
                    '6' => '6 Hari',
                    '7' => '7 Hari',
                    '8' => '8 Hari',
                    '9' => '9 Hari',
                    '10' => '10 Hari',
                    '11' => '11 Hari',
                    '12' => '12 Hari',
                    '13' => '13 Hari',
                    '14' => '14 Hari',
                ],
            ],
            'checkout_method' => [
                'title' => __('Metode Checkout', 'wc-tripay'),
                'type' => 'select',
                'description' => __('DIRECT = Pelanggan diarahkan ke halaman invoice default WooCommerce.<br/>REDIRECT = Pelanggan diarahkan ke halaman invoice TriPay', 'wc-tripay'),
                'default' => 'REDIRECT',
                'options' => [
                    'DIRECT' => 'DIRECT',
                    'REDIRECT' => 'REDIRECT',
                ],
            ],
        ];
    }
}
