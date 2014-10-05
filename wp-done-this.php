<?php
/**
 * @package WP_Done_This
 * @version 1.0
 */
/*
Plugin Name: WP Done This
Plugin URI: http://bryce.se/
Description: Adds a widget for users to submit their own 'dones' to iDoneThis and view today's dones
Author: Bryce Adams
Version: 1.0
Author URI: http://bryce.se/
Text Domain: wpdonethis
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * WP_Done_This Class
 *
 * @package  WP Done This
 * @author   Bryce <bryce@bryce.se>
 * @since    1.0.0
 */

if ( ! class_exists( 'WP_Done_This' ) ) {

  	class WP_Done_This {

  		/**
		 * Construct the plugin
		 **/

		public function __construct() {

			add_action( 'plugins_loaded', array( $this, 'init' ) );

		}


		/**
		 * Initialize the plugin
		 **/

		public function init() {

			// Brace Yourself
			require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wp-done-this.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wp-done-this-api.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wp-done-this-widget.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'lib/timeago.inc.php' );

			// Vroom.. Vroom..
			add_action( 'init', array( 'WP_Done_This_Init', 'get_instance' ) );
			add_action( 'init', array( 'WP_Done_This_API', 'get_instance' ) );
			add_action( 'plugins_loaded', array( 'WP_Done_This_Widget', 'get_instance' ) );

		}

	}

}

$WP_Done_This = new WP_Done_This( __FILE__ );


/**
 * Plugin Settings Links etc.
 *
 * @package  WP Done This
 * @author   Bryce <bryce@bryce.se>
 * @since    1.0.0
 */

$plugin = plugin_basename( __FILE__ ); 
add_filter( 'plugin_action_links_' . $plugin, 'wpdonethis_plugin_links' );

// Add settings link on plugin page
if ( ! function_exists( 'wpdonethis_plugin_links' ) ) {
	function wpdonethis_plugin_links( $links ) {

	  $settings_link = '<a href="' . admin_url( 'profile.php' ) . '">Your Profile</a>';
	  array_unshift( $links, $settings_link ); 
	  return $links;

	}
}