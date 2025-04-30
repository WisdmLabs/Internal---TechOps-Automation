<?php
/**
 * Plugin Name: TechOps Content Sync
 * Plugin URI: https://example.com/techops-content-sync
 * Description: Syncs WordPress plugins and themes with a Git repository
 * Version: 1.0.0
 * Author: TechOps
 * Author URI: https://example.com
 * Text Domain: techops-content-sync
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('TECHOPS_CONTENT_SYNC_VERSION', '1.0.0');
define('TECHOPS_CONTENT_SYNC_DIR', plugin_dir_path(__FILE__));
define('TECHOPS_CONTENT_SYNC_URL', plugin_dir_url(__FILE__));
define('TECHOPS_CONTENT_SYNC_FILE', __FILE__);
define('TECHOPS_CONTENT_SYNC_DEBUG', true);

// Load Composer autoloader if it exists
if (file_exists(TECHOPS_CONTENT_SYNC_DIR . 'vendor/autoload.php')) {
    require_once TECHOPS_CONTENT_SYNC_DIR . 'vendor/autoload.php';
}

/**
 * Check plugin dependencies
 */
function techops_content_sync_check_dependencies() {
    $errors = [];
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = 'PHP 7.4 or higher is required.';
    }
    
    // Check ZipArchive extension
    if (!class_exists('ZipArchive')) {
        $errors[] = 'PHP ZipArchive extension is required.';
    }
    
    // Check if Composer autoload exists
    if (!file_exists(TECHOPS_CONTENT_SYNC_DIR . 'vendor/autoload.php')) {
        $errors[] = 'Composer dependencies are not installed. Please run composer install in the plugin directory.';
    }
    
    // Check if Git is installed
    exec('git --version', $output, $return_var);
    if ($return_var !== 0) {
        $errors[] = 'Git is not installed or not accessible.';
    }
    
    return $errors;
}

/**
 * Display admin notices for dependency issues
 */
function techops_content_sync_admin_notices() {
    $dependency_errors = techops_content_sync_check_dependencies();
    
    if (!empty($dependency_errors)) {
        ?>
        <div class="notice notice-error">
            <p><strong>TechOps Content Sync:</strong> The following requirements are not met:</p>
            <ul>
                <?php foreach ($dependency_errors as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}
add_action('admin_notices', 'techops_content_sync_admin_notices');

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    // Check if the class belongs to our namespace
    if (strpos($class, 'TechOpsContentSync\\') !== 0) {
        return;
    }

    // Remove namespace prefix
    $class = str_replace('TechOpsContentSync\\', '', $class);
    
    // Convert class name to file path
    $file = TECHOPS_CONTENT_SYNC_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $class)) . '.php';
    
    // Load the file if it exists
    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Initialize the plugin
 */
function techops_content_sync_init() {
    // Check dependencies before initializing
    $dependency_errors = techops_content_sync_check_dependencies();
    if (!empty($dependency_errors)) {
        return;
    }
    
    // Log initialization
    error_log('TechOps Content Sync: Initializing plugin');
    
    // Load required files
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-api-endpoints.php';
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-authentication.php';
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-security.php';
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-file-handler.php';
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-git-handler.php';
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-installer.php';
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-git-history.php';
    require_once TECHOPS_CONTENT_SYNC_DIR . 'includes/class-rate-limiter.php';
    
    // Initialize components
    $api_endpoints = new TechOpsContentSync\API_Endpoints();
    $git_history = new TechOpsContentSync\Git_History();
    
    // Register REST API routes
    add_action('rest_api_init', [$api_endpoints, 'register_routes']);
    
    // Register admin scripts and styles
    add_action('admin_enqueue_scripts', 'techops_content_sync_admin_assets');
    
    // Log API init hook
    error_log('TechOps Content Sync: Added REST API init hook');
}
add_action('init', 'techops_content_sync_init');

/**
 * Register admin assets
 */
function techops_content_sync_admin_assets($hook) {
    if ($hook !== 'toplevel_page_techops-content-sync') {
        return;
    }

    // Register and enqueue styles
    wp_register_style(
        'techops-git-form',
        TECHOPS_CONTENT_SYNC_URL . 'assets/css/git-form.css',
        [],
        TECHOPS_CONTENT_SYNC_VERSION
    );
    wp_enqueue_style('techops-git-form');

    // Register and enqueue scripts
    wp_register_script(
        'techops-git-form',
        TECHOPS_CONTENT_SYNC_URL . 'assets/js/git-form.js',
        ['jquery'],
        TECHOPS_CONTENT_SYNC_VERSION,
        true
    );
    wp_enqueue_script('techops-git-form');
}

/**
 * Activation hook
 */
function techops_content_sync_activate() {
    $dependency_errors = techops_content_sync_check_dependencies();
    
    if (!empty($dependency_errors)) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            'TechOps Content Sync cannot be activated. The following requirements are not met:<br>' .
            implode('<br>', $dependency_errors)
        );
    }
    
    error_log('TechOps Content Sync: Plugin activated');
    
    // Create necessary directories
    $upload_dir = wp_upload_dir();
    $techops_dir = $upload_dir['basedir'] . '/techops-content-sync';
    
    if (!file_exists($techops_dir)) {
        wp_mkdir_p($techops_dir);
    }
    
    // Create temp directory
    $temp_dir = $techops_dir . '/temp';
    if (!file_exists($temp_dir)) {
        wp_mkdir_p($temp_dir);
    }
    
    // Create log file
    $log_file = $techops_dir . '/techops-content-sync.log';
    if (!file_exists($log_file)) {
        file_put_contents($log_file, 'TechOps Content Sync Log - ' . date('Y-m-d H:i:s') . "\n");
    }
    
    // Initialize Git history table
    $git_history = new TechOpsContentSync\Git_History();
    $git_history->create_table();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'techops_content_sync_activate');

/**
 * Deactivation hook
 */
function techops_content_sync_deactivate() {
    error_log('TechOps Content Sync: Plugin deactivated');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'techops_content_sync_deactivate');

/**
 * Add admin menu
 */
function techops_content_sync_admin_menu() {
    add_menu_page(
        'TechOps Content Sync',
        'TechOps Sync',
        'manage_options',
        'techops-content-sync',
        'techops_content_sync_admin_page',
        'dashicons-update',
        30
    );
}
add_action('admin_menu', 'techops_content_sync_admin_menu');

/**
 * Admin page callback
 */
function techops_content_sync_admin_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Include the Git form template
    include TECHOPS_CONTENT_SYNC_DIR . 'templates/admin/git-form.php';
} 