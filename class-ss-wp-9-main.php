<?php
/**
 * Plugin Name: SoftwareSeni WP Training 9
 * Description: Understand how to create custom REST API in WordPress
 * Version: 1.0
 * Author: Bismoko Widyatno
 *
 * @package ss-wp-9
 */

/**
 * --------------------------------------------------------------------------
 * Main class for this plugin. This class will handle most of the
 * plugin logic
 * --------------------------------------------------------------------------
 **/
class SS_WP_9_Main {
	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		* Execute this when plugin activated and have been loaded
		* 1. register shortcodes
		*/
		add_action( 'plugins_loaded', array( $this, 'ss_wp9_plugins_loaded_handlers' ) );
	}

	/**
	 * Function to display latest posts from WP API
	 *
	 * @param int $ss_shortcode_atts action to do ( select, insert, update, delete ).
	 */
	public function ss_wp9_create_shortcode( $ss_shortcode_atts = array() ) {
		ob_start();

		return ob_get_clean();
	}



	/**
	 * Function for executing some task when plugins loaded
	 */
	public function ss_wp9_plugins_loaded_handlers() {
		add_shortcode( 'wp9_custom_rest_api', array( $this, 'ss_wp9_create_shortcode' ) );
	}
}
