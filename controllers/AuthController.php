<?php
/**
 * Authentication Controller
 * Handles login, logout, password reset, and session management
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Localization.php';

class AuthController {
    
    /**
     * Login user
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'message' => string]
     */
    public static function login($username, $password) {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $stmt = $conn->prepare("SELECT e.*, r.role_name 
                                FROM employees e 
                                JOIN roles r ON e.role_id = r.role_id 
                                WHERE e.username = ? AND e.status_id = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // Set session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $row['employee_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name_th'] = $row['full_name_th'];
                $_SESSION['full_name_en'] = $row['full_name_en'];
                $_SESSION['role'] = $row['role_name'];
                $_SESSION['role_id'] = $row['role_id'];
                $_SESSION['theme_mode'] = $row['theme_mode'] ?? 'light';
                $_SESSION['language'] = $row['language_preference'];
                $_SESSION['profile_pic'] = $row['profile_pic_path'];
                $_SESSION['last_activity'] = time();
                
                $stmt->close();
                $conn->close();
                
                return ['success' => true, 'message' => 'Login successful', 'redirect' => '/index.php'];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Invalid password'];
            }
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'User not found'];
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: ' . BASE_PATH . '/views/auth/login.php');
        exit();
    }
    
    /**
     * Check if user is authenticated
     * @return bool
     */
    public static function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }
        
        // Check session timeout
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            self::logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Check user role authorization
     * @param array $allowed_roles Array of allowed role names
     * @return bool
     */
    public static function hasRole($allowed_roles) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!self::isAuthenticated()) {
            return false;
        }
        
        return in_array($_SESSION['role'], $allowed_roles);
    }
    
    /**
     * Require authentication (redirect if not authenticated)
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: ' . BASE_PATH . '/views/auth/login.php');
            exit();
        }
    }
    
    /**
     * Require specific role (redirect if unauthorized)
     * @param array $allowed_roles
     */
    public static function requireRole($allowed_roles) {
        self::requireAuth();
        
        if (!self::hasRole($allowed_roles)) {
            header('Location: ' . BASE_PATH . '/views/errors/403.php');
            exit();
        }
    }
    
    /**
     * Request password reset (send OTP via email)
     * @param string $email
     * @return array
     */
    public static function requestPasswordReset($email) {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT employee_id, full_name_th FROM employees WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(1, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store OTP in session or database (for demo, using session)
            session_start();
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_expires'] = $expires;
            
            // Send email with OTP
            $sent = self::sendOTPEmail($email, $row['full_name_th'], $otp);
            
            $stmt->close();
            $conn->close();
            
            if ($sent) {
                return ['success' => true, 'message' => 'OTP sent to your email'];
            } else {
                return ['success' => false, 'message' => 'Failed to send email'];
            }
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Email not found'];
        }
    }
    
    /**
     * Verify OTP
     * @param string $otp
     * @return array
     */
    public static function verifyOTP($otp) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_expires'])) {
            return ['success' => false, 'message' => 'No OTP request found'];
        }
        
        if (time() > strtotime($_SESSION['reset_expires'])) {
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_expires']);
            return ['success' => false, 'message' => 'OTP expired'];
        }
        
        if ($_SESSION['reset_otp'] === $otp) {
            return ['success' => true, 'message' => 'OTP verified'];
        } else {
            return ['success' => false, 'message' => 'Invalid OTP'];
        }
    }
    
    /**
     * Reset password
     * @param string $otp
     * @param string $new_password
     * @return array
     */
    public static function resetPassword($otp, $new_password) {
        // Verify OTP first
        $verify = self::verifyOTP($otp);
        
        if (!$verify['success']) {
            return $verify;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $email = $_SESSION['reset_email'];
        
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE employees SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE username = ?");
        $stmt->bind_param("ss", $password_hash, $email);
        
        if ($stmt->execute()) {
            // Clear OTP session
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_expires']);
            
            $stmt->close();
            $conn->close();
            
            return ['success' => true, 'message' => 'Password reset successful'];
        } else {
            $stmt->close();
            $conn->close();
            
            return ['success' => false, 'message' => 'Failed to reset password'];
        }
    }
    
    /**
     * Send OTP email using SMTP
     * @param string $to
     * @param string $name
     * @param string $otp
     * @return bool
     */
    private static function sendOTPEmail($to, $name, $otp) {
        // For production, use PHPMailer or similar library
        // This is a simplified version
        
        $subject = "Password Reset OTP - HR Service";
        $message = "Hello $name,\n\n";
        $message .= "Your OTP for password reset is: $otp\n";
        $message .= "This OTP will expire in 15 minutes.\n\n";
        $message .= "If you did not request this, please ignore this email.\n\n";
        $message .= "Best regards,\nHR Service Team";
        
        $headers = "From: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // For development, log instead of sending
        error_log("OTP Email to $to: $otp");
        
        // In production, implement actual SMTP sending
        return true; // Return true for demo
    }
    
    /**
     * Change user theme mode (light/dark)
     * @param string $mode Theme mode (light or dark)
     * @return array
     */
    public static function updateThemeMode($mode) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!self::isAuthenticated()) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        // Validate theme mode
        if (!in_array($mode, ['light', 'dark'])) {
            return ['success' => false, 'message' => 'Invalid theme mode'];
        }
        
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("UPDATE employees SET theme_mode = ?, updated_at = CURRENT_TIMESTAMP WHERE employee_id = ?");
        $stmt->bind_param("ss", $mode, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['theme_mode'] = $mode;
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Theme mode updated'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update theme mode'];
        }
    }
    
    /**
     * Change user language preference
     * @param string $lang Language code (th, en, my)
     * @return array
     */
    public static function updateLanguage($lang) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!self::isAuthenticated()) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        // Validate language code
        if (!in_array($lang, ['th', 'en', 'my'])) {
            return ['success' => false, 'message' => 'Invalid language code'];
        }
        
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("UPDATE employees SET language_preference = ?, updated_at = CURRENT_TIMESTAMP WHERE employee_id = ?");
        $stmt->bind_param("ss", $lang, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['language'] = $lang;
            Localization::clearCache();
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Language updated'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update language'];
        }
    }
}
?>