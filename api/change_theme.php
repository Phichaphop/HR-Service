<?php
/**
 * API: Change Theme Mode (Light/Dark)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
if (!AuthController::isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$mode = $input['mode'] ?? '';

// Update theme mode
$result = AuthController::updateThemeMode($mode);

echo json_encode($result);
?>