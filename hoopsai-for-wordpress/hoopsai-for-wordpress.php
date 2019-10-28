<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://hoopsai.com
 * @since             1.0.0
 * @package           Hoopsai_For_Wordpress
 *
 * @wordpress-plugin
 * Plugin Name:       hoopsAI for Wordpress
 * Plugin URI:        https://hoopsai.com/hoopsai-for-wordpress-uri/
 * Description:       Generate AI Content using hoopsAI's API.
 * Version:           1.0.0
 * Author:            hoopsAI
 * Author URI:        http://hoopsAI.com/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       hoopsai-for-wordpress
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'HOOPSAI_FOR_WORDPRESS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-hoopsai-for-wordpress-activator.php
 */
function activate_hoopsai_for_wordpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hoopsai-for-wordpress-activator.php';
	Hoopsai_For_Wordpress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-hoopsai-for-wordpress-deactivator.php
 */
function deactivate_hoopsai_for_wordpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hoopsai-for-wordpress-deactivator.php';
	Hoopsai_For_Wordpress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_hoopsai_for_wordpress' );
register_deactivation_hook( __FILE__, 'deactivate_hoopsai_for_wordpress' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-hoopsai-for-wordpress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_hoopsai_for_wordpress() {

	$plugin = new Hoopsai_For_Wordpress();
	$plugin->run();

}
run_hoopsai_for_wordpress();
