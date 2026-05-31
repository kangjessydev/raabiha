<?php
/**
 * Biteship API
 *
 * @package Biteship_For_WooCommerce
 */

class Biteship_API
{
    /**
     * Biteship API base URL
     */
    private $api_url;

    public function __construct()
    {
        $this->api_url = getenv("WORDPRESS_BITESHIP_API_URL") ?: "https://api.biteship.com";
    }

    /**
     * The single instance of the class
     */
    private static $instance = null;

    /**
     * Main Biteship_API Instance
     *
     * Ensures only one instance of Biteship_API is loaded or can be loaded.
     *
     * @return Biteship_API - Main instance.
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_gmaps_js_url()
    {
        return $this->api_url . "/v1/woocommerce/plugins/gmaps_js?key_id=" . urlencode(Biteship_Helper::get_setting("api_key_id"));
    }

    public function get_gmaps_js()
    {
        $args = [
            "method" => "GET",
            "headers" => [
                "Authorization" => "Bearer " . Biteship_Helper::get_setting("api_key"),
                "Accept" => "application/javascript",
            ],
        ];

        $response = wp_remote_request($this->get_gmaps_js_url(), $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $gmaps_js = wp_remote_retrieve_body($response);

        return $gmaps_js;
    }

    /**
     * Create an order via Biteship API
     *
     * @param array $order_data Order data to be sent to Biteship
     * @return array|WP_Error The API response or WP_Error on failure
     * @see https://biteship.com/id/docs/api/orders/create For API documentation
     */
    public function create_order($params)
    {
        $body = [
            "origin_contact_name" => $params["origin_contact_name"],
            "origin_contact_phone" => $params["origin_contact_phone"],
            "origin_postal_code" => isset($params["origin_postcode"]) ? $params["origin_postcode"] : null,
            "origin_coordinate" => isset($params["origin_coordinate"]) ? $params["origin_coordinate"] : null,
            "origin_address" => $params["origin_address"],
            "destination_contact_name" => $params["destination_contact_name"],
            "destination_contact_phone" => $params["destination_contact_phone"],
            "destination_postal_code" => isset($params["destination_postcode"]) ? $params["destination_postcode"] : null,
            "destination_coordinate" => isset($params["destination_coordinate"]) ? $params["destination_coordinate"] : null,
            "destination_address" => $params["destination_address"],
            "courier_company" => $params["courier_code"],
            "courier_type" => $params["service_code"],
            "delivery_type" => "now",
            "webhooks" => [
                [
                    "name" => "Woocommerce Tracking status",
                    "events" => ["order.status"],
                    "url" => $params["callback_url"],
                ],
            ],
            "items" => $params["items"],
        ];

        if (isset($params["origin_coordinate"]) && isset($params["destination_coordinate"])) {
            $body["origin_coordinate"] = $params["origin_coordinate"];
            $body["destination_coordinate"] = $params["destination_coordinate"];
        } else {
            $body["origin_postal_code"] = $params["origin_postcode"];
            $body["destination_postal_code"] = $params["destination_postcode"];
        }

        if (isset($params["cash_on_delivery"]["amount"])) {
            $body["destination_cash_on_delivery"] = $params["cash_on_delivery"]["amount"];
        }

        if (isset($params["insurance"]["amount"])) {
            $body["courier_insurance"] = $params["insurance"]["amount"];
        }

        $args = [
            "method" => "POST",
            "headers" => [
                "Authorization" => "Bearer " . Biteship_Helper::get_setting("api_key"),
                "Content-Type" => "application/json",
            ],
            "body" => wp_json_encode($body),
        ];

        return $this->make_request("/v1/woocommerce/orders", $args);
    }

    /**
     * Get an order via Biteship API
     *
     * @param string $order_id The order id
     * @return array|WP_Error The API response or WP_Error on failure
     * @see https://biteship.com/id/docs/api/orders/retrieve For API documentation
     */
    public function get_order($order_id)
    {
        $args = [
            "method" => "GET",
            "headers" => [
                "Authorization" => "Bearer " . Biteship_Helper::get_setting("api_key"),
                "Accept" => "application/json",
            ],
        ];

        return $this->make_request("/v1/woocommerce/orders/{$order_id}", $args);
    }

    /**
     * Cancel an order via Biteship API
     *
     * @param string $order_id The order id
     * @return array|WP_Error The API response or WP_Error on failure
     * @see https://biteship.com/id/docs/api/orders/cancel For API documentation
     */
    public function cancel_order($order_id)
    {
        $args = [
            "method" => "DELETE",
            "headers" => [
                "Authorization" => "Bearer " . Biteship_Helper::get_setting("api_key"),
                "Content-Type" => "application/json",
            ],
        ];

        return $this->make_request("/v1/woocommerce/orders/{$order_id}", $args);
    }

    /**
     * Get shipping rates from Biteship API
     *
     * @param array $rate_data Data required to get shipping rates.
     * @return array|WP_Error The API response or WP_Error on failure.
     * @see https://biteship.com/id/docs/couriers/retrieve For API documentation
     */
    public function get_rates($params)
    {
        $endpoint = "rates";

        $args = [
            "method" => "POST",
            "headers" => [
                "Authorization" => "Bearer " . Biteship_Helper::get_setting("api_key"),
                "Content-Type" => "application/json",
            ],
            "body" => wp_json_encode($params),
        ];

        $response = $this->make_request("/v1/woocommerce/rates/couriers", $args);

        if (!is_array($response) || !$response["success"]) {
            return;
        }

        return $response["pricing"];
    }

    public function get_subscription_info($api_key)
    {
        $args = [
            "method" => "POST",
            "headers" => [
                "Authorization" => "Bearer " . Biteship_Helper::get_setting("api_key"),
                "Content-Type" => "application/json",
            ],
            "body" => wp_json_encode([
                "licence" => $api_key,
            ]),
        ];

        $response = $this->make_request("/v1/woocommerce/plugins/validate_key", $args);

        if (!is_array($response) || !$response["success"]) {
            return;
        }

        return [
            "id" => $response["data"]["id"],
            "subscription_type" => $response["data"]["type"],
            "subscription_period" => $response["data"]["subscriptionPeriod"],
            "active" => $response["data"]["active"],
            "due_at" => $response["data"]["dueAt"],
        ];
    }

    public function get_gmaps_api_key()
    {
        $args = [
            "method" => "GET",
            "headers" => [
                "Authorization" => "Bearer " . Biteship_Helper::get_setting("api_key"),
            ],
        ];

        $response = $this->make_request("/v1/woocommerce/plugins/gmaps_api_key", $args);

        if (!is_array($response) || !$response["success"]) {
            return;
        }

        return $response["api_key"];
    }

    /**
     * Make an API request to Biteship
     *
     * @param string $endpoint The API endpoint
     * @param array $args The request arguments
     * @return array|WP_Error The API response or WP_Error on failure
     */
    private function make_request($endpoint, $args)
    {
        $url = $this->api_url . $endpoint;

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_json = wp_remote_retrieve_body($response);
        $body = json_decode($response_json, true);

        return $body;
    }
}
