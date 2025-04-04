<?php
namespace TechOpsContentSync;

class Authentication {
    /**
     * Validate authentication for a request
     */
    public function validate_request() {
        // Check if user is logged in
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return true;
        }

        // Check for Application Password
        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            return false;
        }

        $user = wp_authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        if (is_wp_error($user)) {
            return false;
        }

        // Verify user has required capabilities
        return user_can($user, 'manage_options');
    }

    /**
     * Get current authenticated user
     */
    public function get_current_user() {
        if (is_user_logged_in()) {
            return wp_get_current_user();
        }

        if (!empty($_SERVER['PHP_AUTH_USER'])) {
            return get_user_by('login', $_SERVER['PHP_AUTH_USER']);
        }

        return null;
    }

    /**
     * Check if request is using Application Password
     */
    public function is_using_app_password() {
        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            return false;
        }

        return true;
    }

    /**
     * Log authentication attempt
     */
    public function log_auth_attempt($success, $username) {
        $security = new Security();
        $security->log_event(
            'authentication',
            $success ? 'Authentication successful' : 'Authentication failed',
            [
                'username' => $username,
                'using_app_password' => $this->is_using_app_password()
            ]
        );
    }
} 