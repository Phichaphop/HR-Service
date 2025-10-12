<?php
/**
 * Session Helper
 * Ensures session is started only once
 */

if (!function_exists('ensure_session_started')) {
    /**
     * Start session if not already started
     */
    function ensure_session_started() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('get_session')) {
    /**
     * Get session value safely
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_session($key, $default = null) {
        ensure_session_started();
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('set_session')) {
    /**
     * Set session value safely
     * @param string $key
     * @param mixed $value
     */
    function set_session($key, $value) {
        ensure_session_started();
        $_SESSION[$key] = $value;
    }
}

if (!function_exists('destroy_session')) {
    /**
     * Destroy session safely
     */
    function destroy_session() {
        ensure_session_started();
        session_unset();
        session_destroy();
    }
}
?>