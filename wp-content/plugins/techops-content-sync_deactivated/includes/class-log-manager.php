<?php
namespace TechOpsContentSync;

class Log_Manager {
    private $log_file;
    private $max_log_entries = 100;

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/techops-content-sync/techops-content-sync.log';
        
        // Register AJAX handler for refreshing logs
        add_action('wp_ajax_techops_refresh_logs', [$this, 'ajax_refresh_logs']);
    }

    /**
     * Write a log entry
     *
     * @param string $message Log message
     * @param string $level Log level (info, warning, error)
     */
    public function write_log($message, $level = 'info') {
        if (!file_exists($this->log_file)) {
            wp_mkdir_p(dirname($this->log_file));
        }

        $timestamp = current_time('mysql');
        $log_entry = sprintf("[%s] [%s] %s\n", $timestamp, strtoupper($level), $message);
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
        
        // Trim log file if it gets too large
        $this->trim_log_file();
    }

    /**
     * Get log entries
     *
     * @param int $limit Number of entries to return
     * @return array Log entries
     */
    public function get_logs($limit = 50) {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $logs = array_filter(
            array_map('trim', file($this->log_file)),
            function($line) {
                return !empty($line);
            }
        );

        // Reverse array to show newest first
        $logs = array_reverse($logs);

        // Limit the number of entries
        return array_slice($logs, 0, $limit);
    }

    /**
     * Clear all logs
     */
    public function clear_logs() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }

    /**
     * Trim log file to prevent it from getting too large
     */
    private function trim_log_file() {
        if (!file_exists($this->log_file)) {
            return;
        }

        $logs = file($this->log_file);
        if (count($logs) > $this->max_log_entries) {
            $logs = array_slice($logs, -$this->max_log_entries);
            file_put_contents($this->log_file, implode('', $logs));
        }
    }

    /**
     * AJAX handler for refreshing logs
     */
    public function ajax_refresh_logs() {
        check_ajax_referer('techops_git_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $logs = $this->get_logs();
        wp_send_json_success(['logs' => $logs]);
    }
} 