<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://hoopsai.com
 * @since      1.0.0
 *
 * @package    Hoopsai_For_Wordpress
 * @subpackage Hoopsai_For_Wordpress/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Hoopsai_For_Wordpress
 * @subpackage Hoopsai_For_Wordpress/includes
 * @author     Amir Erell <amir@hoopsai.com>
 */
class Hoopsai_For_Wordpress_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'hoopsai-for-wordpress',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
