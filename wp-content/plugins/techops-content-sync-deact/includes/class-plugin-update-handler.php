<?php
/**
 * Plugin Update Handler
 *
 * Handles reading downloaded JSON files and managing plugin updates.
 */

class Plugin_Update_Handler {
    private $github_handler;
    private $wp_org_handler;
    private $log_file;
    
    public function __construct() {
        $this->github_handler = new GitHub_Handler();
        $this->wp_org_handler = new WordPress_Org_Handler();
        $this->log_file = WP_CONTENT_DIR . '/techops-update-log.txt';
    }
    
    /**
     * Process downloaded JSON file and update plugins
     *
     * @param string $file_path Path to the downloaded JSON file
     * @return array Results of the update process
     */
    public function process_downloaded_file($file_path) {
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'message' => 'File not found: ' . $file_path
            );
        }

        $json_content = file_get_contents($file_path);
        $data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'message' => 'Invalid JSON format: ' . json_last_error_msg()
            );
        }

        if (!isset($data['processed']) || !is_array($data['processed'])) {
            return array(
                'success' => false,
                'message' => 'Invalid plugin data format'
            );
        }

        $results = array(
            'processed' => array(),
            'failed' => array(),
            'skipped' => array()
        );

        // Process plugins that need updates
        foreach ($data['processed'] as $item) {
            if ($item['type'] !== 'plugin') {
                continue; // Skip non-plugin items
            }

            $result = $this->install_plugin(
                $item['slug'],
                $item['to'],
                true, // Force update since it's in processed list
                true  // Auto-activate
            );

            if ($result['success']) {
                $results['processed'][] = array(
                    'type' => 'plugin',
                    'slug' => $item['slug'],
                    'from' => $item['from'],
                    'to' => $item['to']
                );
            } else {
                $results['failed'][] = array(
                    'type' => 'plugin',
                    'slug' => $item['slug'],
                    'reason' => $result['message']
                );
            }
        }

        // Add skipped items from the original file
        if (isset($data['skipped']) && is_array($data['skipped'])) {
            $results['skipped'] = $data['skipped'];
        }

        // Add failed items from the original file
        if (isset($data['failed']) && is_array($data['failed'])) {
            $results['failed'] = array_merge($results['failed'], $data['failed']);
        }

        return array(
            'success' => true,
            'results' => $results
        );
    }

    /**
     * Install or update a plugin
     *
     * @param string $plugin_slug Plugin slug
     * @param string $version Version to install
     * @param bool $force_update Whether to force update
     * @param bool $auto_activate Whether to auto-activate
     * @return array Installation result
     */
    public function install_plugin($plugin_slug, $version, $force_update = false, $auto_activate = true) {
        $this->log("Starting installation of {$plugin_slug} version {$version}");

        // Check if version is available
        $version_check = $this->wp_org_handler->is_version_available($plugin_slug, $version);
        if (is_wp_error($version_check)) {
            $this->log("Error checking version: " . $version_check->get_error_message());
            return array(
                'slug' => $plugin_slug,
                'success' => false,
                'message' => 'Version check failed: ' . $version_check->get_error_message()
            );
        }

        if (!$version_check && !$force_update) {
            $this->log("Version {$version} not available for {$plugin_slug}");
            return array(
                'slug' => $plugin_slug,
                'success' => false,
                'message' => "Version {$version} not available"
            );
        }

        // Download plugin
        $temp_file = $this->wp_org_handler->download_plugin($plugin_slug, $version);
        if (is_wp_error($temp_file)) {
            $this->log("Download failed: " . $temp_file->get_error_message());
            return array(
                'slug' => $plugin_slug,
                'success' => false,
                'message' => 'Download failed: ' . $temp_file->get_error_message()
            );
        }

        // Install plugin
        $upgrader = new Plugin_Upgrader();
        $result = $upgrader->install($temp_file);

        // Clean up temp file
        @unlink($temp_file);

        if (is_wp_error($result)) {
            $this->log("Installation failed: " . $result->get_error_message());
            return array(
                'slug' => $plugin_slug,
                'success' => false,
                'message' => 'Installation failed: ' . $result->get_error_message()
            );
        }

        // Activate if needed
        if ($auto_activate) {
            $activate_result = activate_plugin($plugin_slug);
            if (is_wp_error($activate_result)) {
                $this->log("Activation failed: " . $activate_result->get_error_message());
                return array(
                    'slug' => $plugin_slug,
                    'success' => false,
                    'message' => 'Activation failed: ' . $activate_result->get_error_message()
                );
            }
        }

        $this->log("Successfully installed {$plugin_slug} version {$version}");
        return array(
            'slug' => $plugin_slug,
            'success' => true,
            'message' => "Successfully installed version {$version}"
        );
    }

    /**
     * Log update activity
     *
     * @param string $message Log message
     */
    private function log($message) {
        $timestamp = current_time('mysql');
        $log_entry = "[{$timestamp}] {$message}\n";
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
} 