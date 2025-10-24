<?php
/**
 * Change Theme API
 * Handles theme switching between light and dark modes
 */

session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['theme']) && !isset($data['mode'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Theme parameter is required'
    ]);
    exit;
}

// Support both 'theme' and 'mode' parameter names
$theme = $data['theme'] ?? $data['mode'] ?? 'light';

// Validate theme value
if (!in_array($theme, ['light', 'dark'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid theme. Use "light" or "dark"'
    ]);
    exit;
}

// Update session
$_SESSION['theme_mode'] = $theme;

// Return success response
echo json_encode([
    'success' => true,
    'theme' => $theme,
    'message' => 'Theme changed successfully'
]);