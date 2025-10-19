<?php
/**
 * API: Import Employees from CSV (FULL VERSION - 29 Columns)
 * Admin only
 * รองรับการ import ข้อมูลครบถ้วน 29 คอลัมน์
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Employee.php';

AuthController::requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method'], JSON_UNESCAPED_UNICODE);
    exit();
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error'], JSON_UNESCAPED_UNICODE);
    exit();
}

$file = $_FILES['csv_file'];

// Validate file type
$allowed_extensions = ['csv', 'txt'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a CSV file'], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Convert encoding to UTF-8
 */
function convertToUTF8($content) {
    $bom = pack('H*','EFBBBF');
    $content = preg_replace("/^$bom/", '', $content);
    
    $encoding = mb_detect_encoding($content, ['UTF-8', 'TIS-620', 'Windows-874', 'ISO-8859-11'], true);
    
    if ($encoding && $encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    } elseif (!$encoding) {
        $content = mb_convert_encoding($content, 'UTF-8', 'TIS-620');
    }
    
    return $content;
}

// Read and parse CSV
$file_content = file_get_contents($file['tmp_name']);
$file_content = convertToUTF8($file_content);

$lines = explode("\n", $file_content);
$csv_data = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    $row = str_getcsv($line, ',', '"');
    $row = array_map('trim', $row);
    $csv_data[] = $row;
}

if (empty($csv_data)) {
    echo json_encode(['success' => false, 'message' => 'CSV file is empty'], JSON_UNESCAPED_UNICODE);
    exit();
}

$conn = getDbConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Get master data mappings
function getMasterIdByName($conn, $table, $name_field, $id_field, $value, $lang = 'th') {
    if (empty($value)) return null;
    
    $value = trim($value);
    $column = $name_field . '_' . $lang;
    
    $stmt = $conn->prepare("SELECT $id_field FROM $table WHERE $column = ? LIMIT 1");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row[$id_field];
    }
    
    $stmt->close();
    return null;
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

$header_skipped = false;

foreach ($csv_data as $line_number => $data) {
    $actual_line = $line_number + 1;
    
    // Skip header
    if (!$header_skipped) {
        $header_skipped = true;
        continue;
    }
    
    $results['total']++;
    
    // Detect CSV format (5 columns or 29 columns)
    $is_full_format = (count($data) >= 29);
    $is_simple_format = (count($data) >= 5 && count($data) < 29);
    
    if (!$is_full_format && !$is_simple_format) {
        $results['failed']++;
        $results['errors'][] = "Line $actual_line: Invalid format (got " . count($data) . " columns, expected 5 or 29)";
        continue;
    }
    
    // Parse based on format
    if ($is_simple_format) {
        // Simple format (5 columns): ID, Name, Username, Password, Role
        $employee_id = trim($data[0]);
        $full_name_th = trim($data[1]);
        $username = trim($data[2]);
        $password = trim($data[3]);
        $role_name = strtolower(trim($data[4]));
        
        // Set defaults for missing fields
        $prefix_id = 1;
        $sex_id = 1;
        $nationality_id = 1;
        $education_level_id = 1;
        $function_id = 1;
        $division_id = 1;
        $department_id = 1;
        $section_id = 1;
        $operation_id = 1;
        $position_id = 1;
        $position_level_id = 1;
        $labour_cost_id = 1;
        $hiring_type_id = 1;
        $customer_zone_id = 1;
        $contribution_level_id = 1;
        $status_id = 1;
        
        $full_name_en = '';
        $birthday = null;
        $phone_no = '';
        $address_village = '';
        $address_subdistrict = '';
        $address_district = '';
        $address_province = '';
        $date_of_hire = date('Y-m-d');
        
    } else {
        // Full format (29 columns)
        $employee_id = trim($data[0]);
        $prefix_name = trim($data[1]);
        $full_name_th = trim($data[2]);
        $full_name_en = trim($data[3]);
        $sex_name = trim($data[4]);
        $birthday = !empty($data[5]) ? $data[5] : null;
        $nationality_name = trim($data[6]);
        $education_name = trim($data[7]);
        $phone_no = trim($data[8]);
        $address_village = trim($data[9]);
        $address_subdistrict = trim($data[10]);
        $address_district = trim($data[11]);
        $address_province = trim($data[12]);
        $function_name = trim($data[13]);
        $division_name = trim($data[14]);
        $department_name = trim($data[15]);
        $section_name = trim($data[16]);
        $operation_name = trim($data[17]);
        $position_name = trim($data[18]);
        $position_level_name = trim($data[19]);
        $labour_cost_name = trim($data[20]);
        $hiring_type_name = trim($data[21]);
        $zone_name = trim($data[22]);
        $contribution_name = trim($data[23]);
        $date_of_hire = !empty($data[24]) ? $data[24] : date('Y-m-d');
        $status_name = trim($data[25]);
        $username = trim($data[26]);
        $password = trim($data[27]);
        $role_name = strtolower(trim($data[28]));
        
        // Get master IDs
        $prefix_id = getMasterIdByName($conn, 'prefix_master', 'prefix', 'prefix_id', $prefix_name) ?: 1;
        $sex_id = getMasterIdByName($conn, 'sex_master', 'sex_name', 'sex_id', $sex_name) ?: 1;
        $nationality_id = getMasterIdByName($conn, 'nationality_master', 'nationality', 'nationality_id', $nationality_name) ?: 1;
        $education_level_id = getMasterIdByName($conn, 'education_level_master', 'level_name', 'education_id', $education_name) ?: 1;
        $function_id = getMasterIdByName($conn, 'function_master', 'function_name', 'function_id', $function_name) ?: 1;
        $division_id = getMasterIdByName($conn, 'division_master', 'division_name', 'division_id', $division_name) ?: 1;
        $department_id = getMasterIdByName($conn, 'department_master', 'department_name', 'department_id', $department_name) ?: 1;
        $section_id = getMasterIdByName($conn, 'section_master', 'section_name', 'section_id', $section_name) ?: 1;
        $operation_id = getMasterIdByName($conn, 'operation_master', 'operation_name', 'operation_id', $operation_name) ?: 1;
        $position_id = getMasterIdByName($conn, 'position_master', 'position_name', 'position_id', $position_name) ?: 1;
        $position_level_id = getMasterIdByName($conn, 'position_level_master', 'level_name', 'level_id', $position_level_name) ?: 1;
        $labour_cost_id = getMasterIdByName($conn, 'labour_cost_master', 'cost_name', 'labour_cost_id', $labour_cost_name) ?: 1;
        $hiring_type_id = getMasterIdByName($conn, 'hiring_type_master', 'type_name', 'hiring_type_id', $hiring_type_name) ?: 1;
        $customer_zone_id = getMasterIdByName($conn, 'customer_zone_master', 'zone_name', 'zone_id', $zone_name) ?: 1;
        $contribution_level_id = getMasterIdByName($conn, 'contribution_level_master', 'level_name', 'contribution_id', $contribution_name) ?: 1;
        $status_id = getMasterIdByName($conn, 'status_master', 'status_name', 'status_id', $status_name) ?: 1;
    }
    
    // Validate required fields
    if (empty($employee_id) || empty($full_name_th) || empty($username) || empty($password)) {
        $results['failed']++;
        $results['errors'][] = "Line $actual_line: Missing required fields (ID: '$employee_id', Name: '$full_name_th', Username: '$username')";
        continue;
    }
    
    // Validate employee ID length
    if (strlen($employee_id) > 8) {
        $results['failed']++;
        $results['errors'][] = "Line $actual_line: Employee ID too long (max 8 chars): '$employee_id'";
        continue;
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        $results['failed']++;
        $results['errors'][] = "Line $actual_line: Password too short (min 6 chars) for '$employee_id'";
        continue;
    }
    
    // Get role_id
    $role_id = $roles_map[$role_name] ?? $roles_map['employee'] ?? 3;
    
    // Check if employee_id exists
    $check_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
    $check_stmt->bind_param("s", $employee_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $results['failed']++;
        $results['errors'][] = "Line $actual_line: Employee ID '$employee_id' already exists";
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();
    
    // Check if username exists
    $check_stmt = $conn->prepare("SELECT username FROM employees WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $results['failed']++;
        $results['errors'][] = "Line $actual_line: Username '$username' already exists";
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert employee
    $sql = "INSERT INTO employees (
        employee_id, prefix_id, full_name_th, full_name_en, sex_id, birthday,
        nationality_id, education_level_id, phone_no, 
        address_village, address_subdistrict, address_district, address_province,
        function_id, division_id, department_id, section_id, operation_id,
        position_id, position_level_id, labour_cost_id, hiring_type_id,
        customer_zone_id, contribution_level_id, date_of_hire, status_id,
        username, password, role_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "siississsssssiiiiiiiiiiisisssi",
        $employee_id, $prefix_id, $full_name_th, $full_name_en, $sex_id, $birthday,
        $nationality_id, $education_level_id, $phone_no,
        $address_village, $address_subdistrict, $address_district, $address_province,
        $function_id, $division_id, $department_id, $section_id, $operation_id,
        $position_id, $position_level_id, $labour_cost_id, $hiring_type_id,
        $customer_zone_id, $contribution_level_id, $date_of_hire, $status_id,
        $username, $password_hash, $role_id
    );
    
    if ($stmt->execute()) {
        $results['imported']++;
    } else {
        $results['failed']++;
        $results['errors'][] = "Line $actual_line: Database error - " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>