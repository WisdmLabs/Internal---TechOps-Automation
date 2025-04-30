<?php
namespace TechOpsContentSync;

class Installer {
    /**
     * Install a plugin or theme from a zip file
     *
     * @param string $zip_path Path to the zip file
     * @param string $type 'plugin' or 'theme'
     * @return array|WP_Error Success response with detailed information or WP_Error on failure
     */
    public function install_from_zip($zip_path, $type = 'plugin') {
        try {
            if (!file_exists($zip_path)) {
                return new \WP_Error('file_not_found', 'Zip file not found');
            }

            if (!in_array($type, ['plugin', 'theme'])) {
                return new \WP_Error('invalid_type', 'Invalid installation type');
            }

            // Validate package structure
            $validation_result = $this->validate_package_structure($zip_path, $type);
            if (is_wp_error($validation_result)) {
                return $validation_result;
            }

            // Extract package information
            $package_info = $this->extract_package_info($zip_path, $type);
            if (is_wp_error($package_info)) {
                return $package_info;
            }

            // Include WordPress upgrade functions
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/misc.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

            // Create upgrader skin
            $skin = new \WP_Upgrader_Skin();
            
            // Create upgrader
            $upgrader = new \WP_Upgrader($skin);

            // Install the package
            $result = $upgrader->install_package(array(
                'source' => $zip_path,
                'destination' => $type === 'plugin' ? WP_PLUGIN_DIR : get_theme_root(),
                'clear_destination' => true,
                'clear_working' => true,
                'hook_extra' => array()
            ));

            if (is_wp_error($result)) {
                return $result;
            }

            // Clean up the zip file
            unlink($zip_path);

            // Handle dependencies if present
            if ($type === 'plugin') {
                $this->handle_plugin_dependencies($package_info['slug']);
            }

            return array(
                'success' => true,
                'message' => sprintf('%s installed successfully', ucfirst($type)),
                'slug' => $package_info['slug'],
                'path' => $type === 'plugin' ? WP_PLUGIN_DIR . '/' . $package_info['slug'] : get_theme_root() . '/' . $package_info['slug'],
                'info' => $package_info
            );

        } catch (\Exception $e) {
            return new \WP_Error('installation_error', $e->getMessage());
        }
    }

    /**
     * Validate package structure
     *
     * @param string $zip_path Path to the zip file
     * @param string $type 'plugin' or 'theme'
     * @return bool|WP_Error True if valid, WP_Error otherwise
     */
    private function validate_package_structure($zip_path, $type) {
        $zip = new \ZipArchive();
        if ($zip->open($zip_path) !== true) {
            return new \WP_Error('invalid_zip', 'Invalid zip file');
        }

        $has_main_file = false;
        $has_readme = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            if ($type === 'plugin') {
                if (preg_match('/^[^\/]+\/[^\/]+\.php$/', $filename)) {
                    $has_main_file = true;
                }
                if (preg_match('/^[^\/]+\/readme\.txt$/', $filename)) {
                    $has_readme = true;
                }
            } else {
                if (preg_match('/^[^\/]+\/style\.css$/', $filename)) {
                    $has_main_file = true;
                }
            }
        }

        $zip->close();

        if (!$has_main_file) {
            return new \WP_Error('invalid_structure', sprintf('Invalid %s structure: main file not found', $type));
        }

        if ($type === 'plugin' && !$has_readme) {
            return new \WP_Error('invalid_structure', 'Plugin must include a readme.txt file');
        }

        return true;
    }

    /**
     * Extract package information
     *
     * @param string $zip_path Path to the zip file
     * @param string $type 'plugin' or 'theme'
     * @return array|WP_Error Package information or WP_Error
     */
    private function extract_package_info($zip_path, $type) {
        $zip = new \ZipArchive();
        if ($zip->open($zip_path) !== true) {
            return new \WP_Error('invalid_zip', 'Invalid zip file');
        }

        $info = array();
        $main_file = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            if ($type === 'plugin') {
                if (preg_match('/^([^\/]+)\/([^\/]+\.php)$/', $filename, $matches)) {
                    $main_file = $filename;
                    $info['slug'] = $matches[1];
                    $content = $zip->getFromIndex($i);
                    
                    if (preg_match('/Plugin Name:\s*(.+)/', $content, $matches)) {
                        $info['name'] = trim($matches[1]);
                    }
                    if (preg_match('/Version:\s*(.+)/', $content, $matches)) {
                        $info['version'] = trim($matches[1]);
                    }
                    if (preg_match('/Requires at least:\s*(.+)/', $content, $matches)) {
                        $info['requires'] = trim($matches[1]);
                    }
                    if (preg_match('/Requires PHP:\s*(.+)/', $content, $matches)) {
                        $info['requires_php'] = trim($matches[1]);
                    }
                }
            } else {
                if (preg_match('/^([^\/]+)\/style\.css$/', $filename, $matches)) {
                    $main_file = $filename;
                    $info['slug'] = $matches[1];
                    $content = $zip->getFromIndex($i);
                    
                    if (preg_match('/Theme Name:\s*(.+)/', $content, $matches)) {
                        $info['name'] = trim($matches[1]);
                    }
                    if (preg_match('/Version:\s*(.+)/', $content, $matches)) {
                        $info['version'] = trim($matches[1]);
                    }
                }
            }
        }

        $zip->close();

        if (empty($main_file)) {
            return new \WP_Error('invalid_package', 'Could not find main file in package');
        }

        return $info;
    }

    /**
     * Handle plugin dependencies
     *
     * @param string $plugin_slug Plugin slug
     * @return void
     */
    private function handle_plugin_dependencies($plugin_slug) {
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_slug . '.php';
        
        if (file_exists($plugin_file)) {
            $content = file_get_contents($plugin_file);
            
            if (preg_match('/Requires Plugins:\s*(.+)/', $content, $matches)) {
                $required_plugins = array_map('trim', explode(',', $matches[1]));
                
                foreach ($required_plugins as $required_plugin) {
                    if (!is_plugin_active($required_plugin)) {
                        activate_plugin($required_plugin);
                    }
                }
            }
        }
    }

    /**
     * Determine if a zip file contains a plugin or theme
     *
     * @param string $zip_path Path to the zip file
     * @return string|WP_Error 'plugin', 'theme', or WP_Error
     */
    public function detect_type($zip_path) {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($zip_path) !== true) {
                return new \WP_Error('invalid_zip', 'Invalid zip file');
            }

            // Check for plugin header
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('/^[^\/]+\/[^\/]+\.php$/', $filename)) {
                    $content = $zip->getFromIndex($i);
                    if (strpos($content, 'Plugin Name:') !== false) {
                        $zip->close();
                        return 'plugin';
                    }
                }
            }

            // Check for theme
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('/^[^\/]+\/style\.css$/', $filename)) {
                    $content = $zip->getFromIndex($i);
                    if (strpos($content, 'Theme Name:') !== false) {
                        $zip->close();
                        return 'theme';
                    }
                }
            }

            $zip->close();
            return new \WP_Error('unknown_type', 'Could not determine if this is a plugin or theme');

        } catch (\Exception $e) {
            return new \WP_Error('detection_error', $e->getMessage());
        }
    }
} 