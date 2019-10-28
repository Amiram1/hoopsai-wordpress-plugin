<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hoopsai.com
 * @since      1.0.0
 *
 * @package    Hoopsai_For_Wordpress
 * @subpackage Hoopsai_For_Wordpress/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Hoopsai_For_Wordpress
 * @subpackage Hoopsai_For_Wordpress/public
 * @author     Amir Erell <amir@hoopsai.com>
 */
class Hoopsai_For_Wordpress_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $hoopsai_for_wordpress    The ID of this plugin.
	 */
	private $hoopsai_for_wordpress;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $hoopsai_for_wordpress       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $hoopsai_for_wordpress, $version ) {

		$this->hoopsai_for_wordpress = $hoopsai_for_wordpress;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hoopsai_For_Wordpress_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hoopsai_For_Wordpress_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->hoopsai_for_wordpress, plugin_dir_url( __FILE__ ) . 'css/hoopsai-for-wordpress-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hoopsai_For_Wordpress_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hoopsai_For_Wordpress_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->hoopsai_for_wordpress, plugin_dir_url( __FILE__ ) . 'js/hoopsai-for-wordpress-public.js', array( 'jquery' ), $this->version, false );

	}

}
