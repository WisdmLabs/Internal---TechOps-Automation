<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wisdmalbs.com
 * @since             1.0.0
 * @package           Ga4_Ecommerce_Funnel_Tracking
 *
 * @wordpress-plugin
 * Plugin Name:       GA4 Ecommerce Funnel Tracking
 * Plugin URI:        https://wisdmalbs.com
 * Description:       This plugin will track the user data on customer journey through the site.
 * Version:           1.0.1
 * Author:            WisdmLabs
 * Author URI:        https://wisdmalbs.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ga4-ecommerce-funnel-tracking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('GA4_ECOMMERCE_FUNNEL_TRACKING_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ga4-ecommerce-funnel-tracking-activator.php
 */
function activate_ga4_ecommerce_funnel_tracking()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-ga4-ecommerce-funnel-tracking-activator.php';
	Ga4_Ecommerce_Funnel_Tracking_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ga4-ecommerce-funnel-tracking-deactivator.php
 */
function deactivate_ga4_ecommerce_funnel_tracking()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-ga4-ecommerce-funnel-tracking-deactivator.php';
	Ga4_Ecommerce_Funnel_Tracking_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ga4_ecommerce_funnel_tracking');
register_deactivation_hook(__FILE__, 'deactivate_ga4_ecommerce_funnel_tracking');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ga4-ecommerce-funnel-tracking.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ga4_ecommerce_funnel_tracking()
{

	$plugin = new Ga4_Ecommerce_Funnel_Tracking();
	$plugin->run();
}
run_ga4_ecommerce_funnel_tracking();
