<?php
/**
 * API: Import Employees from CSV
 * Admin only
 * Format: Employee ID, Full Name (Thai), Username, Password, Role
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Employee.php';

// Require admin role only
AuthController::requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['csv_file'];

// Validate file type
$allowed_types = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types) && !str_ends_with($file['name'], '.csv')) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a CSV file']);
    exit();
}

// Read CSV file
$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    echo json_encode(['success' => false, 'message' => 'Failed to open file']);
    exit();
}

$conn = getDbConnection();
if (!$conn) {
    fclose($handle);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get roles mapping
$roles_result = $conn->query("SELECT role_id, role_name FROM roles");
$roles_map = [];
while ($row = $roles_result->fetch_assoc()) {
    $roles_map[strtolower($row['role_name'])] = $row['role_id'];
}

$results = [
    'success' => true,
    'total' => 0,
    'imported' => 0,
    'failed' => 0,
    'errors' => []
];

$line_number = 0;
$header_skipped = false;

while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    $line_number++;
    
    // Skip header row
    if (!$header_skipped) {
        $header_skipped = true;
        continue;
    }
    
    $results['total']++;
    
    // Validate required columns
    if (count($data) < 5) {
        $results['failed']++;
        $results['errors'][] = "Line $line_number: Insufficient columns (expected 5, got " . count($data) . ")";
        continue;
    }
    
    // Parse data
    $employee_id = trim($data[0]);
    $full_name_th = trim($data[1]);
    $username = trim($data[2]);
    $password = trim($data[3]);
    $role_name = strtolower(trim($data[4]));
    
    // Validate required fields
    if (empty($employee_id) || empty($full_name_th) || empty($username) || empty($password)) {
        $results['failed']++;
        $results['errors'][] = "Line $line_number: Missing required fields";
        continue;
    }
    
    // Validate employee ID format (max 8 characters)
    if (strlen($employee_id) > 8) {
        $results['failed']++;
        $results['errors'][] = "Line $line_number: Employee ID too long (max 8 characters)";
        continue;
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        $results['failed']++;
        $results['errors'][] = "Line $line_number: Password too short (min 6 characters)";
        continue;
    }
    
    // Get role_id
    $role_id = $roles_map[$role_name] ?? $roles_map['employee'] ?? 3;
    
    // Check if employee_id already exists
    $check_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
    $check_stmt->bind_param("s", $employee_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $results['failed']++;
        $results['errors'][] = "Line $line_number: Employee ID '$employee_id' already exists";
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();
    
    // Check if username already exists
    $check_stmt = $conn->prepare("SELECT username FROM employees WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $results['failed']++;
        $results['errors'][] = "Line $line_number: Username '$username' already exists";
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert employee with minimal required data
    $sql = "INSERT INTO employees (
        employee_id, 
        full_name_th, 
        username, 
        password, 
        role_id,
        status_id,
        prefix_id,
        sex_id,
        nationality_id,
        education_level_id,
        function_id,
        division_id,
        department_id,
        section_id,
        operation_id,
        position_id,
        position_level_id,
        labour_cost_id,
        hiring_type_id,
        customer_zone_id,
        contribution_level_id
    ) VALUES (?, ?, ?, ?, ?, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $employee_id, $full_name_th, $username, $password_hash, $role_id);
    
    if ($stmt->execute()) {
        $results['imported']++;
    } else {
        $results['failed']++;
        $results['errors'][] = "Line $line_number: Database error - " . $stmt->error;
    }
    
    $stmt->close();
}

fclose($handle);
$conn->close();

echo json_encode($results);
?>