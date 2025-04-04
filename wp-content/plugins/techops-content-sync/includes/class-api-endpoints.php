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
} 