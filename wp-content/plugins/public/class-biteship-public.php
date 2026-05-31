<?php

class Biteship_Public
{
    public function init()
    {
        $this->register_hooks();
    }

    public function register_hooks()
    {
        // Initialize Biteship as Shipping Method
        add_action("woocommerce_shipping_init", [$this, "init_shipping_method"]);
        // Register Biteship as Shipping Method
        add_filter("woocommerce_shipping_methods", [$this, "register_shipping_method"]);
        // Enrich package data
        add_filter("woocommerce_cart_shipping_packages", [$this, "enrich_shipping_packages"]);
        // Enrich shipping method
        add_action("woocommerce_checkout_create_order_shipping_item", [$this, "enrich_shipping_method"], 10, 3);
        // Add new checkout field
        add_action("woocommerce_checkout_fields", [$this, "coordinate_fields"]);
        // Inject Javascript
        add_action("wp_enqueue_scripts", [$this, "enqueue_assets"]);
        // Add tracking status to order history
        add_action("woocommerce_order_details_after_order_table", [$this, "add_tracking_status_to_order"], 10, 1);
        // Add extra fee when necessary
        add_action("woocommerce_cart_calculate_fees", [$this, "add_extra_fee"]);
        // Hide COD option
        add_filter("woocommerce_available_payment_gateways", [$this, "normalize_payment_gateways"]);
        // Add tracking status to order list in my account page
        add_filter("woocommerce_my_account_my_orders_columns", [$this, "add_tracking_detail_column"]);
        add_action("woocommerce_my_account_my_orders_column_tracking-status", [$this, "add_tracking_status_column_content"], 10, 1);
        add_action("woocommerce_my_account_my_orders_column_tracking-number", [$this, "add_tracking_number_column_content"], 10, 1);
        // Handle update order review on checkout page, happens when field is changed
        add_action("woocommerce_checkout_update_order_review", [$this, "handle_update_order_review"], 10, 1);
    }

    public function init_shipping_method()
    {
        require_once BITESHIP_SHIPPING_PATH . "includes/class-biteship-shipping-method.php";
    }

    public function register_shipping_method($methods)
    {
        $methods["biteship"] = "Biteship_Shipping_Method";
        return $methods;
    }

    public function handle_update_order_review($post_data)
    {
        parse_str($post_data, $data);

        if (isset($data["shipping_coordinate"])) {
            WC()->session->set("shipping_coordinate", sanitize_text_field($data["shipping_coordinate"]));
        }

        if (isset($data["billing_coordinate"])) {
            WC()->session->set("billing_coordinate", sanitize_text_field($data["billing_coordinate"]));
        }
    }

    public function enqueue_assets()
    {
        if (!is_checkout()) {
            return;
        }

        // Inject CSS
        wp_enqueue_style("biteship-public-style", BITESHIP_SHIPPING_URL . "public/css/biteship-public.css", [], BITESHIP_SHIPPING_VERSION, "all");

        $deps = ["jquery"];

        if (Biteship_Helper::is_premium()) {
            wp_enqueue_script("google-maps", Biteship_Helper::get_gmaps_js_url(), [], BITESHIP_SHIPPING_VERSION, true);
            $deps[] = "google-maps";
        }

        wp_enqueue_script("checkout-modifier", BITESHIP_SHIPPING_URL . "public/js/checkout-modifier.js", $deps, BITESHIP_SHIPPING_VERSION, true);
        wp_localize_script("checkout-modifier", "checkoutI18n", $this->get_localization());
    }

    public function coordinate_fields($fields = [])
    {
        if (Biteship_Helper::is_premium()) {
            $fields["billing"]["billing_coordinate"] = [
                "id" => "billing_coordinate",
                "type" => "text",
                "label" => "Coordinate",
                "required" => false,
                "priority" => 95,
                "class" => ["form-row-wide"],
            ];

            $fields["shipping"]["shipping_coordinate"] = [
                "id" => "shipping_coordinate",
                "type" => "text",
                "label" => "Coordinate",
                "required" => false,
                "priority" => 95,
                "class" => ["form-row-wide"],
            ];
        }

        return $fields;
    }

    public function get_localization()
    {
        return [
            "LOCATION_LABEL" => __("Location", "biteship-shipping"),
            "SEARCHBOX_PLACEHOLDER" => __("Search Google Maps", "biteship-shipping"),
        ];
    }

    public function enrich_shipping_packages($packages = [])
    {
        $coordinate = null;
        $billing_only = Biteship_Helper::is_billing_only();

        if ($billing_only) {
            $billing_coordinate = WC()->session->get("billing_coordinate");
            if (isset($billing_coordinate)) {
                $coordinate = sanitize_text_field($billing_coordinate);
            }
        } else {
            $shipping_coordinate = WC()->session->get("shipping_coordinate");
            if (isset($shipping_coordinate)) {
                $coordinate = sanitize_text_field($shipping_coordinate);
            }
        }

        if (!empty($coordinate)) {
            foreach ($packages as &$package) {
                $package["destination"]["coordinate"] = $coordinate;
            }
        }

        return $packages;
    }

    public function enrich_shipping_method($item, $package_key, $package)
    {
        $chosen_shipping_methods = WC()->session->get("chosen_shipping_methods");
        $chosen_rate_id = $chosen_shipping_methods[$package_key];

        if (isset($package["rates"][$chosen_rate_id])) {
            $selected_rate = $package["rates"][$chosen_rate_id];

            if ($selected_rate->get_method_id() === "biteship") {
                $meta_data = $selected_rate->get_meta_data();

                $item->add_meta_data(Biteship_Helper::meta_key("courier_code"), $meta_data[Biteship_Helper::meta_key("courier_code")], true);
                $item->add_meta_data(Biteship_Helper::meta_key("service_code"), $meta_data[Biteship_Helper::meta_key("service_code")], true);
                $item->add_meta_data(Biteship_Helper::meta_key("cod"), $meta_data[Biteship_Helper::meta_key("cod")], true);
                $item->add_meta_data(Biteship_Helper::meta_key("cod_percentage"), $meta_data[Biteship_Helper::meta_key("cod_percentage")], true);
                $item->add_meta_data(Biteship_Helper::meta_key("insurance"), $meta_data[Biteship_Helper::meta_key("insurance")], true);
                $item->add_meta_data(Biteship_Helper::meta_key("insurance_percentage"), $meta_data[Biteship_Helper::meta_key("insurance_percentage")], true);
            }
        }
    }

    public function add_tracking_status_to_order($order)
    {
        $tracking_status = esc_html($order->get_meta(Biteship_Helper::meta_key("tracking_status")));
        $tracking_url = esc_url($order->get_meta(Biteship_Helper::meta_key("tracking_url")));
        $tracking_number = esc_html($order->get_meta(Biteship_Helper::meta_key("tracking_number")));

        if ($tracking_status) {
            echo '<h2 class="woocommerce-column__title">' . esc_html__("Tracking Detail", "biteship-shipping") . "</h2>";
            echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
            echo "<tbody>";

            echo '<tr class="woocommerce-table__line-item order_item">';
            echo '<th scope="row">' . esc_html__("Tracking status", "biteship-shipping") . "</th>";
            echo "<td><span>" . esc_html($tracking_status) . "<span></td>";
            echo "</tr>";

            echo '<tr class="woocommerce-table__line-item order_item">';
            echo '<th scope="row">' . esc_html__("Tracking number", "biteship-shipping") . "</th>";
            echo "<td><span><a href='" . esc_url($tracking_url) . "'>" . esc_html($tracking_number) . "</a><span></td>";
            echo "</tr>";

            echo "</tbody>";
            echo "</table>";
        }
    }

    public function add_tracking_detail_column($columns)
    {
        $new_columns = [];
        foreach ($columns as $key => $name) {
            $new_columns[$key] = $name;
            if ($key === "order-status") {
                $new_columns["tracking-status"] = __("Tracking status", "biteship-shipping");
                $new_columns["tracking-number"] = __("Tracking number", "biteship-shipping");
            }
        }
        return $new_columns;
    }

    public function add_tracking_status_column_content($order)
    {
        $tracking_status = $order->get_meta(Biteship_Helper::meta_key("tracking_status"));

        if ($tracking_status) {
            echo "<span>" . esc_html($tracking_status) . "</span>";
        } else {
            echo '<span aria-hidden="true">—</span>';
        }
    }

    public function add_tracking_number_column_content($order)
    {
        $tracking_number = $order->get_meta(Biteship_Helper::meta_key("tracking_number"));
        $tracking_url = $order->get_meta(Biteship_Helper::meta_key("tracking_url"));
        echo "<a href='" . esc_url($tracking_url) . "' target='_blank'>" . esc_html($tracking_number) . "</a>";
    }

    public function add_extra_fee($cart)
    {
        $rate = Biteship_Public_Helper::get_rate();

        if (Biteship_Public_Helper::is_cod($rate)) {
            $cod_key = __("COD Fee", "biteship-shipping");
            $cod_value = Biteship_Public_Helper::get_cod_fee($cart, $rate);
            $cart->add_fee($cod_key, $cod_value);
        }

        if (Biteship_Public_Helper::is_insured($rate)) {
            $insurance_key = __("Insurance Fee", "biteship-shipping");
            $insurance_value = Biteship_Public_Helper::get_insurance_fee($cart, $rate);
            $cart->add_fee($insurance_key, $insurance_value);
        }
    }

    public function normalize_payment_gateways($available_gateways)
    {
        if (!is_checkout()) {
            return $available_gateways;
        }

        $chosen_shipping_methods = WC()->session->get("chosen_shipping_methods");
        if (!$chosen_shipping_methods) {
            return $available_gateways;
        }

        foreach ($chosen_shipping_methods as $package_key => $chosen_rate_id) {
            $packages = WC()->shipping()->get_packages();
            if (isset($packages[$package_key]["rates"][$chosen_rate_id])) {
                $selected_rate = $packages[$package_key]["rates"][$chosen_rate_id];
                if ($selected_rate->get_method_id() === "biteship") {
                    $meta_data = $selected_rate->get_meta_data();
                    $meta_key = Biteship_Helper::meta_key("cod");

                    if (!isset($meta_data[$meta_key]) || !$meta_data[$meta_key]) {
                        unset($available_gateways["cod"]);
                    }
                }
            }
        }
        return $available_gateways;
    }
}
