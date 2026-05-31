<?php

add_action('rest_api_init', function () {
    register_rest_route('raabiha/v1', '/ui-settings', array(
        'methods' => 'GET',
        'callback' => 'raabiha_get_ui_settings',
        'permission_callback' => 'raabiha_ui_settings_permissions_check',
    ));
    register_rest_route('raabiha/v1', '/ui-settings', array(
        'methods' => 'POST',
        'callback' => 'raabiha_update_ui_settings',
        'permission_callback' => 'raabiha_ui_settings_permissions_check',
    ));
});

function raabiha_ui_settings_permissions_check() {
    return current_user_can('edit_theme_options') || current_user_can('manage_options');
}

function raabiha_get_ui_settings() {
    // 1. Get Logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $logo_url = '';
    if ( $custom_logo_id ) {
        $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
    }

    // 2. Get Primary Menu Items
    $menu_items = array();
    $locations = get_nav_menu_locations();
    if ( isset( $locations['primary'] ) ) {
        $menu = get_term( $locations['primary'], 'nav_menu' );
        if ( ! is_wp_error( $menu ) && $menu ) {
            $items = wp_get_nav_menu_items( $menu->term_id );
            if ( $items ) {
                foreach ( $items as $item ) {
                    $menu_items[] = array(
                        'id' => $item->ID,
                        'title' => $item->title,
                        'url' => $item->url,
                        'target' => $item->target,
                    );
                }
            }
        }
    }

    return rest_ensure_response(array(
        'logo' => array(
            'id' => $custom_logo_id,
            'url' => $logo_url
        ),
        'header_menu' => $menu_items
    ));
}

function raabiha_update_ui_settings(WP_REST_Request $request) {
    $params = $request->get_json_params();

    // 1. Update Logo
    if (isset($params['logo_id'])) {
        $logo_id = intval($params['logo_id']);
        if ($logo_id > 0) {
            set_theme_mod('custom_logo', $logo_id);
        } else {
            remove_theme_mod('custom_logo');
        }
    }

    // 2. Update Primary Menu
    if (isset($params['header_menu']) && is_array($params['header_menu'])) {
        $menu_name = 'Primary Menu';
        $locations = get_nav_menu_locations();
        $menu_id = isset($locations['primary']) ? $locations['primary'] : 0;

        // If menu doesn't exist, create it
        if ($menu_id === 0 || !is_nav_menu($menu_id)) {
            $menu_id = wp_create_nav_menu($menu_name);
            $locations['primary'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }

        if (!is_wp_error($menu_id)) {
            // Delete all existing items first to keep it simple, or update them.
            // For simplicity, we delete all existing items and recreate them.
            $existing_items = wp_get_nav_menu_items($menu_id);
            if ($existing_items) {
                foreach ($existing_items as $item) {
                    wp_delete_post($item->ID, true);
                }
            }

            // Create new items
            $menu_items = $params['header_menu'];
            foreach ($menu_items as $index => $item) {
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => sanitize_text_field($item['title']),
                    'menu-item-url' => esc_url_raw($item['url']),
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $index + 1
                ));
            }
        }
    }

    return raabiha_get_ui_settings();
}
