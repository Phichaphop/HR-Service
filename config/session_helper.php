<?php
/**
 * Session Helper (Improved)
 * จัดการ session และ theme variables แบบรวมศูนย์
 */

if (!function_exists('ensure_session_started')) {
    function ensure_session_started() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('get_session')) {
    function get_session($key, $default = null) {
        ensure_session_started();
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('set_session')) {
    function set_session($key, $value) {
        ensure_session_started();
        $_SESSION[$key] = $value;
    }
}

if (!function_exists('destroy_session')) {
    function destroy_session() {
        ensure_session_started();
        session_unset();
        session_destroy();
    }
}

/**
 * ฟังก์ชันใหม่: ดึงข้อมูล user และ theme variables พร้อมกัน
 * @return array
 */
if (!function_exists('get_theme_vars')) {
    function get_theme_vars() {
        ensure_session_started();
        
        $theme_mode = $_SESSION['theme_mode'] ?? 'light';
        $is_dark = ($theme_mode === 'dark');
        
        return [
            // User info
            'user_id' => $_SESSION['user_id'] ?? '',
            'user_name_th' => $_SESSION['full_name_th'] ?? '',
            'user_name_en' => $_SESSION['full_name_en'] ?? '',
            'user_role' => $_SESSION['role'] ?? '',
            'language' => $_SESSION['language'] ?? 'th',
            'profile_pic' => $_SESSION['profile_pic'] ?? '',
            
            // Theme settings
            'theme_mode' => $theme_mode,
            'is_dark' => $is_dark,
            
            // CSS classes
            'bg_class' => $is_dark ? 'bg-gray-900' : 'bg-gray-50',
            'text_class' => $is_dark ? 'text-gray-100' : 'text-gray-800',
            'card_bg' => $is_dark ? 'bg-gray-800' : 'bg-white',
            'border_class' => $is_dark ? 'border-gray-700' : 'border-gray-200',
            'header_bg' => $is_dark ? 'bg-gray-800' : 'bg-white'
        ];
    }
}

/**
 * ฟังก์ชันใหม่: ดึง display name ตาม language
 * @return string
 */
if (!function_exists('get_display_name')) {
    function get_display_name() {
        ensure_session_started();
        $language = $_SESSION['language'] ?? 'th';
        return ($language === 'en') ? 
            ($_SESSION['full_name_en'] ?? '') : 
            ($_SESSION['full_name_th'] ?? '');
    }
}
?>