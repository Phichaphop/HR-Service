<?php
/**
 * Change Language API
 * Handles language switching between Thai, English, and Myanmar
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
if (!isset($data['language']) && !isset($data['lang'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Language parameter is required'
    ]);
    exit;
}

// Support both 'language' and 'lang' parameter names
$language = $data['language'] ?? $data['lang'] ?? 'th';

// Validate language value
if (!in_array($language, ['th', 'en', 'my'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid language. Use "th", "en", or "my"'
    ]);
    exit;
}

// Update session
$_SESSION['language'] = $language;

// Return success response
echo json_encode([
    'success' => true,
    'language' => $language,
    'message' => 'Language changed successfully'
]);