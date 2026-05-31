<?php

if (class_exists('WC_Integration')) {
    class Biteship_Integration extends WC_Integration
    {
        protected $api;

        public function __construct()
        {
            $this->id = "biteship";
            $this->method_title = __("Biteship", "biteship-shipping");
            $this->method_description = __("An integration for utilizing Biteship for shipping.", "biteship-shipping");

            $this->init_form_fields();
            $this->init_settings();
            $this->load_dependencies();

            add_action("woocommerce_update_options_integration_" . $this->id, [$this, "process_admin_options"]);
        }

        private function load_dependencies()
        {
            $this->api = Biteship_API::get_instance();
        }

        public function init_form_fields()
        {
            $this->form_fields = [
                "api_key" => [
                    "title" => __("API Key", "biteship-shipping"),
                    "type" => "password",
                    "description" => __(
                        "The key that will be used when dealing with Biteship services. You can read how to generate one in <a href='https://biteship.com'>Biteship documentation</a>.",
                        "biteship-shipping"
                    ),
                    "desc_tip" => false,
                    "default" => $this->get_option("api_key"),
                ],
                "subscription_name" => [
                    "title" => __("Subscription", "biteship-shipping"),
                    "type" => "text",
                    "description" => $this->get_option("subscription_description"),
                    "desc_tip" => false,
                    "default" => $this->get_option("subscription_name"),
                    "custom_attributes" => [
                        "readonly" => "readonly",
                    ],
                ],
                "complete_status_on_delivered" => [
                    "title" => __("Enable auto complete status", "biteship-shipping"),
                    "type" => "checkbox",
                    "description" => __("Automatically update order status to <b>Completed</b> when order is shipped.", "biteship-shipping"),
                    "desc_tip" => false,
                    "default" => "",
                ],
            ];
        }

        public function process_admin_options()
        {
            parent::process_admin_options();

            $api_key = $this->get_option("api_key");

            $info = $this->api->get_subscription_info($api_key);

            if (empty($info)) {
                WC_Admin_Settings::add_error(__("API Key is not valid.", "biteship-shipping"));
                $this->update_option("api_key", "");
                $this->update_option("api_key_id", "");
                $this->update_option("subscription_name", "");
                $this->update_option("subscription_description", "");
                $this->update_option("subscription_type", "");
                $this->update_option("subscription_period", "");
                $this->update_option("active", false);
                $this->update_option("due_at", "");
                return;
            }

            $subscription_type = $info["subscription_type"];
            $info_details = $this->get_subscription_detail($subscription_type);

            $this->update_option("api_key_id", $info["id"]);
            $this->update_option("subscription_name", $info_details["name"]);
            $this->update_option("subscription_description", $info_details["description"]);
            $this->update_option("subscription_type", $info["subscription_type"]);
            $this->update_option("subscription_period", $info["subscription_period"]);
            $this->update_option("active", $info["active"]);
            $this->update_option("due_at", $info["due_at"]);
        }

        public function get_subscription_detail($subscription_type)
        {
            switch ($subscription_type) {
                case "woocommercePremium":
                    return [
                        "name" => __("Premium", "biteship-shipping"),
                        "description" => __("All services are available.", "biteship-shipping"),
                    ];
                case "woocommerceStandard":
                    return [
                        "name" => __("Standard", "biteship-shipping"),
                        "description" => __(
                            "Essentials services plus Lion Parcel, Pos Indonesia, RPX, Tiki and Wahana are available. <a href='https://docs.google.com/spreadsheets/d/1Hww3i0OYeM6Lj7I4G9a8MXiRDFL_rPwUbqE12n7Ppz8/edit?usp=sharing' target='_blank'>Need instant courier service?</a>",
                            "biteship-shipping"
                        ),
                    ];
                case "woocommerceEssentials":
                    return [
                        "name" => __("Essentials", "biteship-shipping"),
                        "description" => __(
                            "Anteraja, ID Express, J&T, JNE, Ninja, SAP and Sicepat services are available. <a href='https://docs.google.com/spreadsheets/d/1Hww3i0OYeM6Lj7I4G9a8MXiRDFL_rPwUbqE12n7Ppz8/edit?usp=sharing'>Need Lion Parcel, Pos Indonesia, RPX, Tiki and Wahana service?</a>",
                            "biteship-shipping"
                        ),
                    ];
                default:
                    return [];
            }
        }
    }
}
