<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="techops-git-form">
        <form id="techops-git-form">
            <div class="form-row">
                <label for="repo-url">Repository URL</label>
                <input type="text" id="repo-url" name="repo_url" required>
                <p class="description">
                    Enter the Git repository URL (e.g., https://github.com/username/repository)
                </p>
            </div>

            <div class="form-row">
                <label for="folder-path">Folder Path</label>
                <input type="text" id="folder-path" name="folder_path" required>
                <p class="description">
                    Enter the path to the plugin/theme folder within the repository (e.g., plugin-name)
                </p>
            </div>

            <div class="form-row">
                <label for="branch-tag">Branch or Tag</label>
                <select id="branch-tag" name="branch_or_tag">
                    <option value="">Select branch or tag</option>
                </select>
                <p class="description">
                    Select a specific branch or tag to install (optional)
                </p>
            </div>

            <div class="form-row">
                <label for="install-type">Installation Type</label>
                <select id="install-type" name="type">
                    <option value="plugin">Plugin</option>
                    <option value="theme">Theme</option>
                </select>
                <p class="description">
                    Select whether to install as a plugin or theme
                </p>
            </div>

            <div class="form-row">
                <button type="submit" id="git-submit" class="button button-primary">
                    Install from Git
                </button>
            </div>

            <div id="git-progress" class="progress-container">
                <div class="progress-bar">
                    <div class="progress-bar-inner"></div>
                </div>
                <div class="progress-text"></div>
            </div>

            <div id="git-status" class="status-message"></div>
        </form>

        <div class="history-section">
            <h3>Installation History</h3>
            <ul id="git-history" class="history-list"></ul>
        </div>

        <div class="logs-section">
            <div class="logs-header">
                <h3>Logs</h3>
                <div class="logs-actions">
                    <button id="refresh-logs" class="button">Refresh</button>
                    <button id="clear-logs" class="button">Clear</button>
                </div>
            </div>
            <div id="logs-container" class="logs-container">
                <?php
                $log_manager = new TechOpsContentSync\Log_Manager();
                $logs = $log_manager->get_logs();
                if (empty($logs)): ?>
                    <p class="no-logs">No logs available</p>
                <?php else:
                    foreach ($logs as $log): ?>
                        <div class="log-entry"><?php echo esc_html($log); ?></div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var techopsGit = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        rest_url: '<?php echo rest_url('techops/v1/'); ?>',
        nonce: '<?php echo wp_create_nonce('techops_git_nonce'); ?>',
        rest_nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };
</script> 