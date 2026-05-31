<?php

// Exit if accessed directly.
if (!defined("ABSPATH")) {
    exit();
}

class Biteship_Helper
{
    public static function meta_key($str)
    {
        return "_wc_bts_" . $str;
    }

    public static function option_key($str)
    {
        return "wc_bts_" . $str;
    }

    public static function notice_key(...$args)
    {
        return "wc-bts-" . implode("-", $args);
    }

    public static function get_option($key)
    {
        return get_option(self::option_key($key));
    }

    public static function get_setting($key)
    {
        $settings = get_option("woocommerce_biteship_settings");
        return isset($settings[$key]) ? $settings[$key] : null;
    }

    public static function is_premium()
    {
        $subscription_type = self::get_setting("subscription_type");
        return $subscription_type == "woocommercePremium";
    }

    public static function get_gmaps_api_key()
    {
        if (!self::is_premium()) {
            return;
        }

        return self::get_setting("gmaps_api_key");
    }

    public static function get_gmaps_js_url()
    {
        if (!self::is_premium()) {
            return;
        }

        return get_rest_url(null, "wc-biteship/v1/gmaps.js");
    }

    public static function get_coordinate_object($coordinate)
    {
        $points = explode(",", $coordinate);
        return [
            "latitude" => $points[0],
            "longitude" => $points[1],
        ];
    }

    public static function get_cod_fee($amount, $cod_percentage)
    {
        return $amount * ($cod_percentage / 100);
    }

    public static function get_insurance_fee($amount, $insurance_percentage)
    {
        return $amount * ($insurance_percentage / 100);
    }

    public static function is_billing_only()
    {
        return get_option("woocommerce_ship_to_destination") == "billing_only";
    }

    public static function get_items_from_order($order)
    {
        $items = [];
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $item_object = [
                "name" => $product->get_name(),
                "width" => $product->get_width(),
                "length" => $product->get_length(),
                "height" => $product->get_height(),
                "weight" => $product->get_weight(),
                "description" => $product->get_short_description(),
                "quantity" => $item->get_quantity(),
                "value" => $product->get_price(),
            ];

            if (!isset($item_object["description"])) {
                $item_object["description"] = "Goods";
            }

            $item_object["description"] = esc_html($item_object["description"]);

            $items[] = $item_object;
        }

        return $items;
    }

    public static function get_items_from_package($package)
    {
        $items = [];
        foreach ($package->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $item_object = [
                "name" => $product->get_name(),
                "width" => $product->get_width(),
                "length" => $product->get_length(),
                "height" => $product->get_height(),
                "weight" => $product->get_weight(),
                "description" => $product->get_short_description(),
                "quantity" => $item->get_quantity(),
                "value" => $product->get_price(),
            ];

            if (!isset($item_object["description"])) {
                $item_object["description"] = "Goods";
            }

            $item_object["description"] = esc_html($item_object["description"]);


            $items[] = $item_object;
        }

        return $items;
    }
}
