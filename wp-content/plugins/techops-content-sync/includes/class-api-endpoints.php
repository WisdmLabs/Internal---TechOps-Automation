<?php
namespace TechOpsContentSync;

class API_Endpoints {
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('techops/v1', '/plugins/list', [
            'methods' => 'GET',
            'callback' => [$this, 'get_plugins_list'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('techops/v1', '/themes/list', [
            'methods' => 'GET',
            'callback' => [$this, 'get_themes_list'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('techops/v1', '/plugins/download/(?P<slug>[a-z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'download_plugin'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'slug' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_string($param) && !empty($param);
                    }
                ]
            ]
        ]);

        register_rest_route('techops/v1', '/themes/download/(?P<slug>[a-z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'download_theme'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'slug' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_string($param) && !empty($param);
                    }
                ]
            ]
        ]);

        // Add plugin activation endpoint
        register_rest_route('techops/v1', '/plugins/activate/(?P<slug>[a-z0-9-\.]+)', [
            'methods' => 'POST',
            'callback' => [$this, 'activate_plugin'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'slug' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_string($param) && !empty($param);
                    }
                ]
            ]
        ]);
    }

    /**
     * Check if request has required permissions
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Get list of installed plugins
     */
    public function get_plugins_list() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        $active_plugins = get_option('active_plugins', []);
        
        $formatted_plugins = [];
        foreach ($plugins as $plugin_path => $plugin_data) {
            $slug = explode('/', $plugin_path)[0];
            $formatted_plugins[] = [
                'name' => $plugin_data['Name'],
                'slug' => $slug,
                'version' => $plugin_data['Version'],
                'active' => in_array($plugin_path, $active_plugins),
                'path' => $plugin_path
            ];
        }

        return rest_ensure_response($formatted_plugins);
    }

    /**
     * Get list of installed themes
     */
    public function get_themes_list() {
        $themes = wp_get_themes();
        $active_theme = wp_get_theme();
        
        $formatted_themes = [];
        foreach ($themes as $theme_slug => $theme) {
            $formatted_themes[] = [
                'name' => $theme->get('Name'),
                'slug' => $theme_slug,
                'version' => $theme->get('Version'),
                'active' => ($active_theme->get_stylesheet() === $theme_slug),
                'path' => $theme->get_stylesheet_directory()
            ];
        }

        return rest_ensure_response($formatted_themes);
    }

    /**
     * Download a plugin as ZIP file
     */
    public function download_plugin($request) {
        $slug = $request->get_param('slug');
        $file_handler = new File_Handler();
        
        try {
            return $file_handler->create_plugin_zip($slug);
        } catch (\Exception $e) {
            return new \WP_Error(
                'plugin_download_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Download a theme as ZIP file
     */
    public function download_theme($request) {
        $slug = $request->get_param('slug');
        $file_handler = new File_Handler();
        
        try {
            return $file_handler->create_theme_zip($slug);
        } catch (\Exception $e) {
            return new \WP_Error(
                'theme_download_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Activate a plugin
     */
    public function activate_plugin($request) {
        // Debug: Log the request
        error_log('TechOps Content Sync: Plugin activation request received');
        error_log('TechOps Content Sync: Request parameters: ' . print_r($request->get_params(), true));

        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $slug = $request->get_param('slug');
        error_log('TechOps Content Sync: Raw slug received: ' . $slug);

        // Remove any directory traversal attempts
        $slug = str_replace(['../', './'], '', $slug);
        error_log('TechOps Content Sync: Sanitized slug: ' . $slug);

        $plugins = get_plugins();
        error_log('TechOps Content Sync: Available plugins: ' . print_r(array_keys($plugins), true));

        // First try exact match
        if (isset($plugins[$slug])) {
            $plugin_path = $slug;
        } else {
            // Try to find the plugin path
            $plugin_path = '';
            foreach ($plugins as $path => $data) {
                error_log('TechOps Content Sync: Checking path: ' . $path . ' against slug: ' . $slug);
                if ($path === $slug || strpos($path, $slug . '/') === 0) {
                    $plugin_path = $path;
                    error_log('TechOps Content Sync: Found matching path: ' . $plugin_path);
                    break;
                }
            }
        }

        if (empty($plugin_path)) {
            error_log('TechOps Content Sync: Plugin not found for slug: ' . $slug);
            return new \WP_Error(
                'plugin_not_found',
                'Plugin not found. Available plugins: ' . implode(', ', array_keys($plugins)),
                ['status' => 404]
            );
        }

        error_log('TechOps Content Sync: Using plugin path: ' . $plugin_path);

        // Check if plugin is already active
        if (is_plugin_active($plugin_path)) {
            error_log('TechOps Content Sync: Plugin is already active: ' . $plugin_path);
            return rest_ensure_response([
                'success' => true,
                'message' => 'Plugin is already active',
                'plugin' => $plugin_path
            ]);
        }

        // Activate the plugin
        error_log('TechOps Content Sync: Attempting to activate plugin: ' . $plugin_path);
        $result = activate_plugin($plugin_path);
        
        if (is_wp_error($result)) {
            error_log('TechOps Content Sync: Plugin activation failed: ' . $result->get_error_message());
            return new \WP_Error(
                'plugin_activation_failed',
                $result->get_error_message(),
                ['status' => 500]
            );
        }

        error_log('TechOps Content Sync: Plugin activated successfully: ' . $plugin_path);
        return rest_ensure_response([
            'success' => true,
            'message' => 'Plugin activated successfully',
            'plugin' => $plugin_path
        ]);
    }
} 