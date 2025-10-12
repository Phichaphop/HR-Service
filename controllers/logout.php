<?php
/**
 * Logout Handler
 * Properly destroys session and redirects to login page
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/AuthController.php';

// Use AuthController logout method which handles session properly
AuthController::logout();
?>