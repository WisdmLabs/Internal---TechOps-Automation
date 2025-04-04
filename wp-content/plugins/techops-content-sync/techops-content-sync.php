<?php
/**
 * Plugin Name: TechOps Content Sync
 * Plugin URI: https://github.com/your-username/techops-content-sync
 * Description: Secure REST API endpoints for syncing WordPress plugins and themes
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: techops-content-sync
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TECHOPS_CONTENT_SYNC_VERSION', '1.0.0');
define('TECHOPS_CONTENT_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TECHOPS_CONTENT_SYNC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    $prefix = 'TechOpsContentSync\\';
    $base_dir = TECHOPS_CONTENT_SYNC_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function techops_content_sync_init() {
    // Load plugin components
    require_once TECHOPS_CONTENT_SYNC_PLUGIN_DIR . 'includes/class-api-endpoints.php';
    require_once TECHOPS_CONTENT_SYNC_PLUGIN_DIR . 'includes/class-authentication.php';
    require_once TECHOPS_CONTENT_SYNC_PLUGIN_DIR . 'includes/class-file-handler.php';
    require_once TECHOPS_CONTENT_SYNC_PLUGIN_DIR . 'includes/class-security.php';

    // Initialize REST API endpoints
    add_action('rest_api_init', function () {
        $api_endpoints = new TechOpsContentSync\API_Endpoints();
        $api_endpoints->register_routes();
    });
}
add_action('plugins_loaded', 'techops_content_sync_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Verify WordPress version
    if (version_compare(get_bloginfo('version'), '5.6', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('This plugin requires WordPress version 5.6 or higher.');
    }

    // Verify PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('This plugin requires PHP version 7.4 or higher.');
    }

    // Flush rewrite rules for new endpoints
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
}); 