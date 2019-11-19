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
        'post_content'  => $json['postContent'],
        'post_status'   => $json['postStatus'],
        'post_category' => $json['postCategories'],
        'post_author'   => $json['postAuthor'],
        'tags_input'    => $json['postTags']
    );

    $post_result = wp_insert_post( $new_pva_post );

    if ($post_result != 0) {
        return new WP_REST_RESPONSE(array(
            'success' => true,
            'value'   => $page,
            'code'   => 200
        ), 200);
    }

    return new WP_Error('post_failure', esc_html__('Post Creation Failed.', 'hoopsai-wp'), array('status' => 401));
};

function hoopsai_wp_get_categories() {
    $args = array(
        'hide_empty'      => false,
    );
    $categories = get_categories($args);

    return new WP_REST_RESPONSE(array(
        'success' => true,
        'value'   => $categories,
        'code'   => 200
    ), 200);
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
        register_rest_route('hoopsai-wp/v1', '/get-categories', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'hoopsai_wp_get_categories',
            'permission_callback' => 'hoopsai_wp_settings_permissions_check'
        ));
});


add_action('admin_enqueue_scripts', function ($hook) {
    // only load scripts on dashboard and settings page
    global $hoopsai_wp_settings_page;
    global $hoopsai_wp_menu_page;
    if ($hook != $hoopsai_wp_settings_page && $hook != $hoopsai_wp_menu_page) {
        return;
    }

    if (in_array($_SERVER['REMOTE_ADDR'], array('192.168.1.110', '::1'))) {
        // DEV React dynamic loading
        $js_to_load = 'http://localhost:3000/static/js/bundle.js';
    } else {
        $js_to_load = plugin_dir_url( __FILE__ ) . 'hoopsai-wp.js';
        $css_to_load = plugin_dir_url( __FILE__ ) . 'hoopsai-wp.css';
    }

//     $js_to_load = 'http://localhost:3000/static/js/bundle.js';


    wp_enqueue_style('hoopsai_wp_styles', $css_to_load);
    wp_enqueue_script('hoopsai_wp_react', $js_to_load, '', mt_rand(10,1000), true);
    wp_localize_script('hoopsai_wp_react', 'hoopsai_wp_ajax', array(
        'urls'    => array(
            'proxy'    => rest_url('hoopsai-wp/v1/proxy'),
            'settings' => rest_url('hoopsai-wp/v1/settings'),
            'posts' => rest_url('hoopsai-wp/v1/posts'),
            'get-categories' => rest_url('hoopsai-wp/v1/get-categories')
            ),
        'nonce'   => wp_create_nonce('wp_rest'),
    ));
});


// display dashboard widget
add_action('admin_menu', function () {
    global $hoopsai_wp_menu_page;

    $hoopsai_wp_menu_page = add_menu_page(
        __( 'HoopsAI Menu', 'textdomain' ),
        'HoopsAI Menu',
        'manage_options',
        'hoopsaimenu',
        'hoopsai_wp_menu_page',
        'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTEiIGhlaWdodD0iOTEiIHZpZXdCb3g9IjAgMCA5MSA5MSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQ1LjQ1OTkgOTAuMjcyQzcwLjA0NzcgOTAuMjcyIDg5Ljk3OTkgNzAuMzM5NyA4OS45Nzk5IDQ1Ljc1MkM4OS45Nzk5IDIxLjE2NDMgNzAuMDQ3NyAxLjIzMTk5IDQ1LjQ1OTkgMS4yMzE5OUMyMC44NzIyIDEuMjMxOTkgMC45Mzk5NDEgMjEuMTY0MyAwLjkzOTk0MSA0NS43NTJDMC45Mzk5NDEgNzAuMzM5NyAyMC44NzIyIDkwLjI3MiA0NS40NTk5IDkwLjI3MloiIGZpbGw9IndoaXRlIi8+CjxtYXNrIGlkPSJtYXNrMCIgbWFzay10eXBlPSJhbHBoYSIgbWFza1VuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeD0iMCIgeT0iMSIgd2lkdGg9IjkwIiBoZWlnaHQ9IjkwIj4KPHBhdGggZD0iTTQ1LjQ1OTkgOTAuMjcyQzcwLjA0NzcgOTAuMjcyIDg5Ljk3OTkgNzAuMzM5NyA4OS45Nzk5IDQ1Ljc1MkM4OS45Nzk5IDIxLjE2NDMgNzAuMDQ3NyAxLjIzMTk5IDQ1LjQ1OTkgMS4yMzE5OUMyMC44NzIyIDEuMjMxOTkgMC45Mzk5NDEgMjEuMTY0MyAwLjkzOTk0MSA0NS43NTJDMC45Mzk5NDEgNzAuMzM5NyAyMC44NzIyIDkwLjI3MiA0NS40NTk5IDkwLjI3MloiIGZpbGw9IndoaXRlIi8+CjwvbWFzaz4KPGcgbWFzaz0idXJsKCNtYXNrMCkiPgo8cGF0aCBkPSJNNzAuMTEyMyAzNC4zNTAyQzY5LjA4MTYgMzguNjcwMSA2OC41MzU4IDQzLjE3ODMgNjguNTM1OCA0Ny44MTM5QzY4LjUzNTggNzkuNzMzNyA5NC40MTE5IDEwNS42MSAxMjYuMzMyIDEwNS42MVYxMDUuNjFDMTU4LjI1MSAxMDUuNjEgMTg0LjEyOCA3OS43MzM3IDE4NC4xMjggNDcuODEzOUMxODQuMTI4IDE1Ljg5NDEgMTU4LjI1MSAtOS45ODE5OSAxMjYuMzMyIC05Ljk4MTk5IiBzdHJva2U9IiMxMDJGNDUiIHN0cm9rZS13aWR0aD0iNC40OCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CjxwYXRoIGQ9Ik03Mi4xODMyIDMzLjQ4NzZDNzQuNzgxMiAzMy40ODc2IDc2Ljg4NzIgMzEuMzgxNSA3Ni44ODcyIDI4Ljc4MzZDNzYuODg3MiAyNi4xODU2IDc0Ljc4MTIgMjQuMDc5NiA3Mi4xODMyIDI0LjA3OTZDNjkuNTg1MyAyNC4wNzk2IDY3LjQ3OTIgMjYuMTg1NiA2Ny40NzkyIDI4Ljc4MzZDNjcuNDc5MiAzMS4zODE1IDY5LjU4NTMgMzMuNDg3NiA3Mi4xODMyIDMzLjQ4NzZaIiBzdHJva2U9IiMxMDJGNDUiIHN0cm9rZS13aWR0aD0iMy4xMzYiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8cGF0aCBkPSJNMjEuMDM5MyA1NS41NzA0QzIyLjA3MDEgNTEuMjUwNSAyMi42MTU4IDQ2Ljc0MjMgMjIuNjE1OCA0Mi4xMDY3QzIyLjYxNTggMTAuMTg2OSAtMy4yNjAyNSAtMTUuNjg5MiAtMzUuMTggLTE1LjY4OTJWLTE1LjY4OTJDLTY3LjA5OTggLTE1LjY4OTIgLTkyLjk3NTkgMTAuMTg2OSAtOTIuOTc1OSA0Mi4xMDY3Qy05Mi45NzU5IDc0LjAyNjUgLTY3LjA5OTggOTkuOTAyNiAtMzUuMTggOTkuOTAyNiIgc3Ryb2tlPSIjMTAyRjQ1IiBzdHJva2Utd2lkdGg9IjQuNDgiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTcuMjg4NCA1Ny41NTI5QzE0LjY5MDUgNTcuNTUyOSAxMi41ODQ0IDU5LjY1OSAxMi41ODQ0IDYyLjI1NjlDMTIuNTg0NCA2NC44NTQ5IDE0LjY5MDUgNjYuOTYwOSAxNy4yODg0IDY2Ljk2MDlDMTkuODg2NCA2Ni45NjA5IDIxLjk5MjQgNjQuODU0OSAyMS45OTI0IDYyLjI1NjlDMjEuOTkyNCA1OS42NTkgMTkuODg2NCA1Ny41NTI5IDE3LjI4ODQgNTcuNTUyOVoiIHN0cm9rZT0iIzEwMkY0NSIgc3Ryb2tlLXdpZHRoPSIzLjEzNiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CjxwYXRoIGQ9Ik00NS41OTk5IDY3LjkyMDNWMTgxLjYiIHN0cm9rZT0iIzEwMkY0NSIgc3Ryb2tlLXdpZHRoPSI0LjQ4IiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPHBhdGggZD0iTTQ1LjU5OTkgLTYuNTU5NzVWNzMuNTIwMiIgc3Ryb2tlPSIjMTAyRjQ1IiBzdHJva2Utd2lkdGg9IjQuNDgiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L2c+CjxwYXRoIG9wYWNpdHk9IjAuMSIgZD0iTTE0LjEzNzMgNDUuMjY2MUMxNC4xMzczIDM4LjkyMDcgMTkuNDAzNyAzMy44NDI2IDI1Ljc0NDggMzQuMDczNUw2NC44NzAxIDM1LjQ5OEM3MC44OTMxIDM1LjcxNzMgNzUuNjYyNiA0MC42NjM2IDc1LjY2MjYgNDYuNjkwNlY0OS4xMzQ0Qzc1LjY2MjYgNTUuNDc5OCA3MC4zOTYzIDYwLjU1NzkgNjQuMDU1MSA2MC4zMjdMMjQuOTI5OCA1OC45MDI1QzE4LjkwNjggNTguNjgzMyAxNC4xMzczIDUzLjczNyAxNC4xMzczIDQ3LjcxVjQ1LjI2NjFaIiBmaWxsPSIjN0QxRjE5Ii8+CjxyZWN0IHg9IjE0Ljg2MTUiIHk9IjMzLjMyMjYiIHdpZHRoPSI2MS41MjUzIiBoZWlnaHQ9IjI0Ljg1ODciIHJ4PSIxMS4yIiBmaWxsPSIjMTAyRjQ1Ii8+CjxwYXRoIGQ9Ik0zMC4xNDY1IDQ5Ljk1MjFDMzIuMzExNSA0OS45NTIxIDM0LjA2NjUgNDguMTk3IDM0LjA2NjUgNDYuMDMyMUMzNC4wNjY1IDQzLjg2NzEgMzIuMzExNSA0Mi4xMTIxIDMwLjE0NjUgNDIuMTEyMUMyNy45ODE1IDQyLjExMjEgMjYuMjI2NSA0My44NjcxIDI2LjIyNjUgNDYuMDMyMUMyNi4yMjY1IDQ4LjE5NyAyNy45ODE1IDQ5Ljk1MjEgMzAuMTQ2NSA0OS45NTIxWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTYwLjQ2MjUgNDkuOTUyMUM2Mi42Mjc0IDQ5Ljk1MjEgNjQuMzgyNSA0OC4xOTcgNjQuMzgyNSA0Ni4wMzIxQzY0LjM4MjUgNDMuODY3MSA2Mi42Mjc0IDQyLjExMjEgNjAuNDYyNSA0Mi4xMTIxQzU4LjI5NzUgNDIuMTEyMSA1Ni41NDI1IDQzLjg2NzEgNTYuNTQyNSA0Ni4wMzIxQzU2LjU0MjUgNDguMTk3IDU4LjI5NzUgNDkuOTUyMSA2MC40NjI1IDQ5Ljk1MjFaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K'
    );

    function hoopsai_wp_menu_page() {
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
