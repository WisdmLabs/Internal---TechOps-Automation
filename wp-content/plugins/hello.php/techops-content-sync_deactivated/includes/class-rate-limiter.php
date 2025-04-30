<?php
namespace TechOpsContentSync;

class Rate_Limiter {
    private $max_requests;
    private $time_window;
    private $transient_prefix = 'techops_rate_limit_';

    /**
     * Constructor
     *
     * @param int $max_requests Maximum number of requests allowed
     * @param int $time_window Time window in seconds
     */
    public function __construct($max_requests = 10, $time_window = 60) {
        $this->max_requests = $max_requests;
        $this->time_window = $time_window;
    }

    /**
     * Check if the current request is allowed
     *
     * @return bool Whether the request is allowed
     */
    public function check() {
        $ip = $this->get_client_ip();
        $transient_key = $this->transient_prefix . md5($ip);
        
        $data = get_transient($transient_key);
        if ($data === false) {
            $data = [
                'count' => 1,
                'timestamp' => time()
            ];
            set_transient($transient_key, $data, $this->time_window);
            return true;
        }

        // Reset if time window has passed
        if (time() - $data['timestamp'] > $this->time_window) {
            $data = [
                'count' => 1,
                'timestamp' => time()
            ];
            set_transient($transient_key, $data, $this->time_window);
            return true;
        }

        // Check if limit exceeded
        if ($data['count'] >= $this->max_requests) {
            return false;
        }

        // Increment count
        $data['count']++;
        set_transient($transient_key, $data, $this->time_window);
        return true;
    }

    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
} 