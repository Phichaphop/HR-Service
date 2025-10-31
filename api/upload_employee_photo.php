<?php
/**
 * API: Upload Employee Photo
 * Path: /api/upload_employee_photo.php
 * 
 * Handles employee profile photo upload
 * Pattern: Same as update_request_status.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// ====== AUTHENTICATION & AUTHORIZATION ======
if (!AuthController::isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Only admin can upload photos
if (!AuthController::hasRole(['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin only']);
    exit();
}

// ====== VALIDATE INPUT ======
$employee_id = $_POST['employee_id'] ?? '';

if (empty($employee_id)) {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit();
}

if (!isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['photo'];

// ====== FILE VALIDATION ======

// Check upload error
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds max upload size',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form max size',
        UPLOAD_ERR_PARTIAL    => 'File was partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
        UPLOAD_ERR_EXTENSION  => 'Upload extension blocked'
    ];
    
    echo json_encode([
        'success' => false,
        'message' => $error_messages[$file['error']] ?? 'Upload error'
    ]);
    exit();
}

// Check file size (5MB)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode([
        'success' => false,
        'message' => 'File size exceeds 5MB limit'
    ]);
    exit();
}

// Check MIME type
$allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
} else {
    $mime_type = mime_content_type($file['tmp_name']) ?? 'unknown';
}

if (!in_array($mime_type, $allowed_mimes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type: ' . $mime_type
    ]);
    exit();
}

// Check file extension
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_extensions)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file extension: .' . $file_ext
    ]);
    exit();
}

// Verify it's a real image
$image_info = @getimagesize($file['tmp_name']);
if (!$image_info) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or corrupted image file'
    ]);
    exit();
}

// ====== DATABASE CONNECTION ======
$conn = getDbConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// ====== VERIFY EMPLOYEE EXISTS ======
$check_sql = "SELECT employee_id, profile_pic_path FROM employees WHERE employee_id = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$check_stmt->bind_param("s", $employee_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Employee not found: ' . $employee_id]);
    exit();
}

$employee = $check_result->fetch_assoc();
$check_stmt->close();

// ====== CREATE UPLOAD DIRECTORY ======
$upload_base_dir = __DIR__ . '/../uploads/employees';
$employee_photo_dir = $upload_base_dir . '/' . $employee_id;

// Create base directory
if (!is_dir($upload_base_dir)) {
    if (!@mkdir($upload_base_dir, 0755, true)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create upload directory'
        ]);
        exit();
    }
}

// Create employee directory
if (!is_dir($employee_photo_dir)) {
    if (!@mkdir($employee_photo_dir, 0755, true)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create employee photo directory'
        ]);
        exit();
    }
}

// Check write permissions
if (!is_writable($employee_photo_dir)) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Upload directory is not writable. Set permissions to 755'
    ]);
    exit();
}

// ====== DELETE OLD PHOTO ======
$old_photo_path = $employee['profile_pic_path'];
if (!empty($old_photo_path)) {
    $old_file = __DIR__ . '/../' . $old_photo_path;
    if (file_exists($old_file)) {
        @unlink($old_file);
    }
}

// ====== GENERATE UNIQUE FILENAME ======
$timestamp = time();
$filename = 'profile_' . $timestamp . '.' . $file_ext;
$upload_path = $employee_photo_dir . '/' . $filename;
$db_photo_path = 'uploads/employees/' . $employee_id . '/' . $filename;

// ====== MOVE UPLOADED FILE ======
if (!@move_uploaded_file($file['tmp_name'], $upload_path)) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save uploaded file'
    ]);
    exit();
}

// Set permissions
@chmod($upload_path, 0644);

// ====== CREATE THUMBNAIL ======
create_thumbnail($upload_path, $employee_photo_dir, $file_ext);

// ====== UPDATE DATABASE ======
$sql = "UPDATE employees 
        SET profile_pic_path = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE employee_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    @unlink($upload_path);
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ss", $db_photo_path, $employee_id);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    // Get BASE_PATH if defined
    $base_path = defined('BASE_PATH') ? BASE_PATH : '';
    $photo_url = $base_path . '/' . $db_photo_path;
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Photo uploaded successfully',
        'data' => [
            'employee_id' => $employee_id,
            'photo_url' => $photo_url,
            'photo_path' => $db_photo_path,
            'file_name' => $filename,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'file_size' => $file['size'],
            'mime_type' => $mime_type,
            'affected_rows' => $affected
        ]
    ]);
} else {
    $error = $stmt->error;
    $stmt->close();
    @unlink($upload_path);
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update employee record: ' . $error
    ]);
}

// ====== HELPER FUNCTION: CREATE THUMBNAIL ======
function create_thumbnail($image_path, $directory, $extension) {
    try {
        if (!extension_loaded('gd')) {
            return false;
        }
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $img = @imagecreatefromjpeg($image_path);
                break;
            case 'png':
                $img = @imagecreatefrompng($image_path);
                break;
            case 'gif':
                $img = @imagecreatefromgif($image_path);
                break;
            case 'webp':
                if (function_exists('imagecreatefromwebp')) {
                    $img = @imagecreatefromwebp($image_path);
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }
        
        if (!$img) {
            return false;
        }
        
        $width = imagesx($img);
        $height = imagesy($img);
        
        $thumb_width = 200;
        $thumb_height = 200;
        $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
        
        if ($extension === 'png' || $extension === 'gif') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        
        imagecopyresampled(
            $thumb, $img,
            0, 0, 0, 0,
            $thumb_width, $thumb_height,
            $width, $height
        );
        
        $thumb_name = 'profile_thumb_' . pathinfo(basename($image_path), PATHINFO_FILENAME) . '.webp';
        $thumb_path = $directory . '/' . $thumb_name;
        
        if (function_exists('imagewebp')) {
            imagewebp($thumb, $thumb_path, 80);
        } else {
            imagejpeg($thumb, $thumb_path, 80);
        }
        
        imagedestroy($img);
        imagedestroy($thumb);
        
        return true;
        
    } catch (Exception $e) {
        error_log('Thumbnail creation error: ' . $e->getMessage());
        return false;
    }
}

?>