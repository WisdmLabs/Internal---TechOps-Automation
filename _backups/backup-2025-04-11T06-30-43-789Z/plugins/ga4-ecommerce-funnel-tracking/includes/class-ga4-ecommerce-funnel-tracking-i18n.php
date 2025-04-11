<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wisdmalbs.com
 * @since      1.0.0
 *
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ga4_Ecommerce_Funnel_Tracking
 * @subpackage Ga4_Ecommerce_Funnel_Tracking/includes
 * @author     WisdmLabs <software@wisdmlabs.com>
 */
class Ga4_Ecommerce_Funnel_Tracking_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ga4-ecommerce-funnel-tracking',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
