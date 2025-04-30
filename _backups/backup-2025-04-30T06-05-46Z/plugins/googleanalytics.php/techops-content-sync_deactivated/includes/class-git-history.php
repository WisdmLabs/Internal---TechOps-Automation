<?php
namespace TechOpsContentSync;

class Git_History {
    private $table_name;
    private $db_version = '1.0';

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'techops_git_history';
        
        // Register activation hook
        register_activation_hook(TECHOPS_CONTENT_SYNC_FILE, [$this, 'create_table']);
        
        // Register AJAX handlers
        add_action('wp_ajax_techops_get_git_history', [$this, 'ajax_get_history']);
        add_action('wp_ajax_techops_delete_git_history', [$this, 'ajax_delete_history']);
    }

    /**
     * Create the database table
     */
    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            repo_url varchar(255) NOT NULL,
            repo_name varchar(255) NOT NULL,
            folder_path varchar(255) NOT NULL,
            branch_or_tag varchar(255) DEFAULT NULL,
            type varchar(20) NOT NULL,
            install_date datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('techops_git_history_db_version', $this->db_version);
    }

    /**
     * Add a new installation record
     *
     * @param array $data Installation data
     * @return int|false The ID of the inserted record or false on failure
     */
    public function add_record($data) {
        global $wpdb;

        $defaults = [
            'repo_url' => '',
            'repo_name' => '',
            'folder_path' => '',
            'branch_or_tag' => null,
            'type' => 'plugin',
            'install_date' => current_time('mysql')
        ];

        $data = wp_parse_args($data, $defaults);

        // Extract repository name from URL
        if (empty($data['repo_name'])) {
            $data['repo_name'] = $this->extract_repo_name($data['repo_url']);
        }

        $result = $wpdb->insert(
            $this->table_name,
            $data,
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            error_log('Failed to insert Git history record: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Get installation history
     *
     * @param array $args Query arguments
     * @return array Installation history records
     */
    public function get_history($args = []) {
        global $wpdb;

        $defaults = [
            'orderby' => 'install_date',
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0
        ];

        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM {$this->table_name}";
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        $query .= " LIMIT {$args['limit']} OFFSET {$args['offset']}";

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Delete an installation record
     *
     * @param int $id Record ID
     * @return bool Whether the record was deleted
     */
    public function delete_record($id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Extract repository name from URL
     *
     * @param string $url Repository URL
     * @return string Repository name
     */
    private function extract_repo_name($url) {
        $url = trim($url, '/');
        $parts = explode('/', $url);
        $name = end($parts);
        
        // Remove .git extension if present
        if (substr($name, -4) === '.git') {
            $name = substr($name, 0, -4);
        }
        
        return $name;
    }

    /**
     * AJAX handler for getting installation history
     */
    public function ajax_get_history() {
        check_ajax_referer('techops_git_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $history = $this->get_history();
        wp_send_json_success($history);
    }

    /**
     * AJAX handler for deleting an installation record
     */
    public function ajax_delete_history() {
        check_ajax_referer('techops_git_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error('Invalid record ID');
        }

        $result = $this->delete_record($id);
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete record');
        }
    }
} 