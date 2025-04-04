<?php
namespace TechOpsContentSync;

class File_Handler {
    /**
     * Create a ZIP file of a plugin
     */
    public function create_plugin_zip($slug) {
        \error_log("Starting plugin zip creation for slug: " . $slug);
        
        if (!\function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = \get_plugins();
        $plugin_path = '';
        
        // Find the plugin path by slug
        foreach ($plugins as $path => $data) {
            \error_log("Checking plugin path: " . $path);
            if (\explode('/', $path)[0] === $slug) {
                $plugin_path = WP_PLUGIN_DIR . '/' . \dirname($path);
                \error_log("Found plugin path: " . $plugin_path);
                break;
            }
        }

        if (empty($plugin_path) || !\is_dir($plugin_path)) {
            \error_log("Plugin path not found or not a directory: " . $plugin_path);
            return new \WP_Error(
                'plugin_not_found',
                'Plugin not found or invalid directory',
                ['status' => 404]
            );
        }

        // Check directory permissions
        \error_log("Checking directory permissions for: " . $plugin_path);
        \error_log("Directory readable: " . (\is_readable($plugin_path) ? 'yes' : 'no'));
        \error_log("Directory writable: " . (\is_writable($plugin_path) ? 'yes' : 'no'));

        return $this->create_zip($plugin_path, $slug);
    }

    /**
     * Create a ZIP file of a theme
     */
    public function create_theme_zip($slug) {
        \error_log("Starting theme zip creation for slug: " . $slug);
        
        $theme = \wp_get_theme($slug);
        
        if (!$theme->exists()) {
            \error_log("Theme not found: " . $slug);
            return new \WP_Error(
                'theme_not_found',
                'Theme not found',
                ['status' => 404]
            );
        }

        $theme_path = $theme->get_stylesheet_directory();
        \error_log("Theme path: " . $theme_path);
        return $this->create_zip($theme_path, $slug);
    }

    /**
     * Create a ZIP file from a directory
     */
    private function create_zip($source_path, $name) {
        \error_log("Starting ZIP creation process");
        \error_log("Source path: " . $source_path);
        \error_log("Name: " . $name);

        // Check if ZipArchive is available
        if (!\class_exists('ZipArchive')) {
            \error_log("ZipArchive class not available");
            return new \WP_Error(
                'zip_not_available',
                'ZIP functionality not available on server',
                ['status' => 500]
            );
        }

        $temp_dir = \get_temp_dir();
        $zip_file = $temp_dir . $name . '.zip';
        
        \error_log("Temp directory: " . $temp_dir);
        \error_log("ZIP file path: " . $zip_file);

        // Check temp directory permissions
        \error_log("Temp directory writable: " . (\is_writable($temp_dir) ? 'yes' : 'no'));

        // Clean up any existing file
        if (\file_exists($zip_file)) {
            \error_log("Removing existing ZIP file");
            if (!\unlink($zip_file)) {
                \error_log("Failed to remove existing ZIP file");
                return new \WP_Error(
                    'cleanup_failed',
                    'Failed to remove existing ZIP file',
                    ['status' => 500]
                );
            }
        }

        // Create ZIP file
        \error_log("Creating new ZIP archive");
        $zip = new \ZipArchive();
        $zip_result = $zip->open($zip_file, \ZipArchive::CREATE);
        
        if ($zip_result !== true) {
            \error_log("Failed to create ZIP file. Error code: " . $zip_result);
            return new \WP_Error(
                'zip_creation_failed',
                'Failed to create ZIP file. Error code: ' . $zip_result,
                ['status' => 500]
            );
        }

        try {
            // Add files to ZIP
            \error_log("Adding files to ZIP");
            $source_path = \str_replace('\\', '/', \realpath($source_path));
            
            if (!$source_path) {
                throw new \Exception("Could not resolve real path for: " . $source_path);
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source_path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            $file_count = 0;
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $file_path = \str_replace('\\', '/', $file->getRealPath());
                    $relative_path = \substr($file_path, \strlen($source_path) + 1);
                    
                    \error_log("Adding file: " . $relative_path);
                    if (!$zip->addFile($file_path, $relative_path)) {
                        \error_log("Failed to add file: " . $file_path);
                        throw new \Exception("Failed to add file: " . $relative_path);
                    }
                    $file_count++;
                }
            }

            \error_log("Added {$file_count} files to ZIP");
            $zip->close();

            // Verify the ZIP file was created
            if (!\file_exists($zip_file)) {
                throw new \Exception("ZIP file not found after creation");
            }

            \error_log("ZIP file created successfully. Size: " . \filesize($zip_file) . " bytes");

            // Send file to browser
            $fp = \fopen($zip_file, 'rb');
            if (!$fp) {
                throw new \Exception("Could not open ZIP file for reading");
            }

            // Set headers
            \error_log("Setting response headers");
            \header('Content-Type: application/zip');
            \header('Content-Disposition: attachment; filename="' . \basename($zip_file) . '"');
            \header('Content-Length: ' . \filesize($zip_file));
            \header('Pragma: public');
            
            // Clean output buffer
            if (\ob_get_level()) {
                \ob_end_clean();
            }
            
            // Stream file content
            \error_log("Streaming ZIP file to browser");
            if (!\readfile($zip_file)) {
                throw new \Exception("Failed to read ZIP file");
            }
            
            // Clean up
            \fclose($fp);
            \unlink($zip_file);
            \error_log("ZIP file cleanup completed");
            
            exit;
        } catch (\Exception $e) {
            \error_log("Error during ZIP creation: " . $e->getMessage());
            if (isset($zip) && $zip instanceof \ZipArchive) {
                $zip->close();
            }
            if (\file_exists($zip_file)) {
                \unlink($zip_file);
            }
            return new \WP_Error(
                'zip_creation_failed',
                'Error creating ZIP: ' . $e->getMessage(),
                ['status' => 500]
            );
        }
    }
} 