<?php
/**
 * Plugin Name: HoopsAI WordPress
 * Plugin URI: http://wordpress.org/plugins/hoopsai-wp
 * Description: Generate the latest Sport Content instantly using hoopsAI . To get started: sign up for an account on <a href="http://hoopsai.com">hoopsai.com</a> and add your API key to your HoopsAI WordPress settings here in Wordpress.
 * Version: 1.0.2
 * Author: HoopsAI WordPress
 * Author URI: https://hoopsai.com
 * Text Domain: hoopsai-wp
 * License: GPLv3
 *
 * HoopsAI WordPress is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. HoopsAI WordPress is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with HoopsAI WordPress. If not, see https://www.gnu.org/licenses/gpl-3.0.txt.
*/


// proxy requests to the hoopsAI API so that the API key remains hidden
function hoopsai_wp_api_proxy($request) {
    $params = $request->get_query_params();
    $endpoint = $params['endpoint'];
    unset($params['endpoint']);
    $query = http_build_query($params);
    $headers = array('x-api-key' => get_option('hoopsai_wp_api_key'));
    $request = wp_remote_get("https://api.hoopsai.com/dev/$endpoint?$query", array('headers' => $headers));
    return json_decode(wp_remote_retrieve_body($request));
}


// get saved settings from WP DB
function hoopsai_wp_get_settings($request) {
    $api_key = get_option('hoopsai_wp_api_key');
    return new WP_REST_RESPONSE(array(
        'success' => true,
        'value'   => array(
        'apiKey'  => !$api_key ? '' : $api_key,
        )
    ), 200);
}


// save settings to WP DB
function hoopsai_wp_update_settings($request) {
    $json = $request->get_json_params();
    // store the values in wp_options table
    $updated_api_key = update_option('hoopsai_wp_api_key', $json['apiKey']);
    return new WP_REST_RESPONSE(array(
        'success' => $updated_api_key,
        'value'   => $json
    ), 200);
}


// check permissions
function hoopsai_wp_settings_permissions_check() {
    // Restrict endpoint to only users who have the capability to manage options.
    if (current_user_can('manage_options')) {
        return true;
    }

    return new WP_Error('rest_forbidden', esc_html__('You do not have permissions to view this data.', 'hoopsai-wp'), array('status' => 401));;
}

// create new post
function hoopsai_wp_create_post($request) {
    $json = $request->get_json_params();

    $new_pva_post = array(
        'post_type'     => 'post',
        'post_title'    => $json['postTitle'],
        'post_content' => $json['postContent'],
        'post_status'   => 'publish',
        'post_category' => array( 1, 3 ), // Add it two categories.
        'post_author'   => 1
    );

    $post_result = wp_insert_post( $new_pva_post );

    if ($post_result != 0) {
        return new WP_REST_RESPONSE(array(
            'success' => true,
            'value'   => 'post created successfully',
            'code'   => 200
        ), 200);
    }

    return new WP_Error('post_failure', esc_html__('Post Creation Failed.', 'hoopsai-wp'), array('status' => 401));
};


add_action('rest_api_init', function () {
    register_rest_route('hoopsai-wp/v1', '/proxy', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'hoopsai_wp_api_proxy',
    ));
    register_rest_route('hoopsai-wp/v1', '/settings', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'hoopsai_wp_get_settings',
        'permission_callback' => 'hoopsai_wp_settings_permissions_check'
    ));
    register_rest_route('hoopsai-wp/v1', '/settings', array(
        'methods'  => WP_REST_Server::CREATABLE,
        'callback' => 'hoopsai_wp_update_settings',
        'permission_callback' => 'hoopsai_wp_settings_permissions_check'
    ));
    register_rest_route('hoopsai-wp/v1', '/posts', array(
        'methods'  => WP_REST_Server::CREATABLE,
        'callback' => 'hoopsai_wp_create_post',
        'permission_callback' => 'hoopsai_wp_settings_permissions_check'
    ));
});


add_action('admin_enqueue_scripts', function ($hook) {
    // only load scripts on dashboard and settings page
    global $hoopsai_wp_settings_page;
    if ($hook != 'index.php' && $hook != $hoopsai_wp_settings_page) {
        return;
    }

//     if (in_array($_SERVER['REMOTE_ADDR'], array('192.168.1.110', '::1'))) {
//         // DEV React dynamic loading
//         $js_to_load = 'http://localhost:3000/static/js/bundle.js';
//     } else {
//         $js_to_load = plugin_dir_url( __FILE__ ) . 'hoopsai-wp.js';
//         $css_to_load = plugin_dir_url( __FILE__ ) . 'hoopsai-wp.css';
//     }

    $js_to_load = 'http://localhost:3000/static/js/bundle.js';


    wp_enqueue_style('hoopsai_wp_styles', $css_to_load);
    wp_enqueue_script('hoopsai_wp_react', $js_to_load, '', mt_rand(10,1000), true);
    wp_localize_script('hoopsai_wp_react', 'hoopsai_wp_ajax', array(
        'urls'    => array(
            'proxy'    => rest_url('hoopsai-wp/v1/proxy'),
            'settings' => rest_url('hoopsai-wp/v1/settings'),
            'posts' => rest_url('hoopsai-wp/v1/posts')
            ),
        'nonce'   => wp_create_nonce('wp_rest'),
    ));
});


// display dashboard widget
add_action('wp_dashboard_setup', function () {
    wp_add_dashboard_widget('hoopsai_wp_widget', 'HoopsAI WordPress', 'hoopsai_wp_display_widget');
    function hoopsai_wp_display_widget() {
        ?>
        <div id="hoopsai_wp_dashboard"></div>
        <?php
    }
});


// add to settings menu
add_action('admin_menu', function () {
    global $hoopsai_wp_settings_page;
    $hoopsai_wp_settings_page = add_options_page('HoopsAI WordPress Settings', 'HoopsAI WordPress', 'manage_options', 'hoopsai-wp-settings', 'hoopsai_wp_settings_do_page');

    // Draw the menu page itself
    function hoopsai_wp_settings_do_page() {
        ?>
        <div id="hoopsai_wp_settings"></div>
        <?php
    }

    // add link to settings on plugin page (next to "Deactivate")
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
        $settings_link = '<a href="options-general.php?page=hoopsai-wp-settings">' . __( 'Settings' ) . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    });
});


// cleanup data on uninstall
function hoopsai_wp_uninstall () {
    delete_option('hoopsai_wp_api_key');
}

register_uninstall_hook(__FILE__, 'hoopsai_wp_uninstall');
