<?php
/**
 * WordPress.org API Handler
 *
 * Handles communication with WordPress.org API for plugin downloads and updates.
 */

class WordPress_Org_Handler {
    const WP_ORG_API_BASE = 'https://api.wordpress.org/plugins/info/1.0/';
    const DOWNLOAD_BASE = 'https://downloads.wordpress.org/plugin/';

    /**
     * Get plugin information from WordPress.org
     *
     * @param string $plugin_slug Plugin slug
     * @return array|WP_Error Plugin information or error
     */
    public function get_plugin_info($plugin_slug) {
        $url = add_query_arg(array(
            'action' => 'plugin_information',
            'request' => array(
                'slug' => $plugin_slug,
                'fields' => array(
                    'version' => true,
                    'download_link' => true,
                    'requires' => true,
                    'tested' => true,
                ),
            ),
        ), self::WP_ORG_API_BASE);

        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = maybe_unserialize($body);

        if (!is_object($data)) {
            return new WP_Error('invalid_response', 'Invalid response from WordPress.org');
        }

        return $data;
    }

    /**
     * Download plugin from WordPress.org
     *
     * @param string $plugin_slug Plugin slug
     * @param string $version Plugin version
     * @return string|WP_Error Path to downloaded file or error
     */
    public function download_plugin($plugin_slug, $version) {
        $download_url = self::DOWNLOAD_BASE . $plugin_slug . '.' . $version . '.zip';
        
        $temp_file = download_url($download_url);
        
        if (is_wp_error($temp_file)) {
            return $temp_file;
        }

        return $temp_file;
    }

    /**
     * Get latest version of a plugin
     *
     * @param string $plugin_slug Plugin slug
     * @return string|WP_Error Latest version or error
     */
    public function get_latest_version($plugin_slug) {
        $info = $this->get_plugin_info($plugin_slug);
        
        if (is_wp_error($info)) {
            return $info;
        }

        return $info->version;
    }

    /**
     * Check if plugin version is available on WordPress.org
     *
     * @param string $plugin_slug Plugin slug
     * @param string $version Version to check
     * @return bool|WP_Error True if available, false if not, or error
     */
    public function is_version_available($plugin_slug, $version) {
        $info = $this->get_plugin_info($plugin_slug);
        
        if (is_wp_error($info)) {
            return $info;
        }

        return version_compare($info->version, $version, '>=');
    }
} 