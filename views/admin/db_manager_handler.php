<?php
/**
 * Database Manager Handler - Fixed Version
 * Backend API for AJAX requests with better error handling
 */

// Set headers first
header('Content-Type: application/json');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('max_execution_time', 60);
ini_set('memory_limit', '256M');

// Start output buffering to catch any accidental output
ob_start();

try {
    session_start();
    require_once __DIR__ . '/../../config/db_config.php';
    
    // Check authentication
    if (!isset($_SESSION['db_manager_auth']) || $_SESSION['db_manager_auth'] !== true) {
        throw new Exception('Not authenticated');
    }
    
    // Get action
    $action = $_POST['action'] ?? '';
    
    // Route actions
    switch ($action) {
        case 'check_table':
            checkTable();
            break;
        case 'create_table':
            createTable();
            break;
        case 'seed_table':
            seedTable();
            break;
        case 'drop_table':
            dropTable();
            break;
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    // Clear any output buffer
    ob_end_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

/**
 * Check if table exists
 */
function checkTable() {
    $table = $_POST['table'] ?? '';
    $conn = getDbConnection();
    
    if (!$conn) {
        respondError('Database connection failed');
    }
    
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = ($result && $result->num_rows > 0);
    
    $conn->close();
    
    respondSuccess(['exists' => $exists]);
}

/**
 * Create single table
 */
function createTable() {
    $table = $_POST['table'] ?? '';
    
    if (empty($table)) {
        respondError('Table name required');
    }
    
    $conn = getDbConnection();
    if (!$conn) {
        respondError('Database connection failed');
    }
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("SET sql_mode = ''");
    
    // Get SQL for this table (inline)
    $sql = getTableSQL($table);
    
    if (empty($sql)) {
        $conn->close();
        respondError('Table definition not found for: ' . $table);
    }
    
    // Execute SQL
    if ($conn->query($sql)) {
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->close();
        respondSuccess(['message' => 'Table created successfully']);
    } else {
        $error = $conn->error;
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->close();
        respondError('SQL Error: ' . $error);
    }
}

/**
 * Seed data for single table
 */
function seedTable() {
    $table = $_POST['table'] ?? '';
    
    if (empty($table)) {
        respondError('Table name required');
    }
    
    $conn = getDbConnection();
    if (!$conn) {
        respondError('Database connection failed');
    }
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("SET sql_mode = ''");
    
    // Get seed data for this table
    $sql = getSeedSQL($table, $conn);
    
    if (empty($sql)) {
        $conn->close();
        respondSuccess(['message' => 'No seed data available', 'rows' => 0]);
    }
    
    // Execute SQL
    if ($conn->query($sql)) {
        $rows = $conn->affected_rows;
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->close();
        respondSuccess(['message' => 'Data seeded successfully', 'rows' => $rows]);
    } else {
        $error = $conn->error;
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->close();
        respondError('SQL Error: ' . $error);
    }
}

/**
 * Drop single table
 */
function dropTable() {
    $table = $_POST['table'] ?? '';
    
    if (empty($table)) {
        respondError('Table name required');
    }
    
    $conn = getDbConnection();
    if (!$conn) {
        respondError('Database connection failed');
    }
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    if ($conn->query("DROP TABLE IF EXISTS `$table`")) {
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->close();
        respondSuccess(['message' => 'Table dropped successfully']);
    } else {
        $error = $conn->error;
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->close();
        respondError('SQL Error: ' . $error);
    }
}

/**
 * Helper function to respond with success
 */
function respondSuccess($data = []) {
    ob_end_clean(); // Clear any output
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

/**
 * Helper function to respond with error
 */
function respondError($message) {
    ob_end_clean(); // Clear any output
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * Get CREATE TABLE SQL - Now using direct SQL from schema.sql file
 */
function getTableSQL($table) {
    // Read schema.sql and extract specific table
    $schema_file = __DIR__ . '/../../db/schema.sql';
    
    if (!file_exists($schema_file)) {
        // Schema file not found, return empty
        return '';
    }
    
    $content = file_get_contents($schema_file);
    
    // Find CREATE TABLE for this specific table
    $pattern = "/CREATE TABLE (?:IF NOT EXISTS )?`?" . preg_quote($table, '/') . "`?[^;]*;/is";
    
    if (preg_match($pattern, $content, $matches)) {
        return $matches[0];
    }
    
    return '';
}

/**
 * Get seed SQL for specific table
 */
function getSeedSQL($table, $conn) {
    switch ($table) {
        case 'roles':
            return "INSERT INTO roles (role_name, role_name_th, role_name_en, role_name_my) VALUES 
                ('admin', 'ผู้ดูแลระบบ', 'Administrator', 'Admin'),
                ('officer', 'เจ้าหน้าที่', 'Officer', 'Officer'),
                ('employee', 'พนักงาน', 'Employee', 'Employee')";
        
        case 'prefix_master':
            return "INSERT INTO prefix_master (prefix_th, prefix_en, prefix_my) VALUES 
                ('นาย', 'Mr.', 'Mr'),
                ('นาง', 'Mrs.', 'Mrs'),
                ('นางสาว', 'Miss', 'Miss')";
        
        case 'sex_master':
            return "INSERT INTO sex_master (sex_name_th, sex_name_en, sex_name_my) VALUES 
                ('ชาย', 'Male', 'Male'),
                ('หญิง', 'Female', 'Female')";
        
        case 'nationality_master':
            return "INSERT INTO nationality_master (nationality_th, nationality_en, nationality_my) VALUES 
                ('ไทย', 'Thai', 'Thai'),
                ('พม่า', 'Myanmar', 'Myanmar')";
        
        case 'education_level_master':
            return "INSERT INTO education_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('ประถมศึกษา', 'Primary', 'Primary'),
                ('มัธยมศึกษา', 'Secondary', 'Secondary'),
                ('ปริญญาตรี', 'Bachelor', 'Bachelor'),
                ('ปริญญาโท', 'Master', 'Master')";
        
        case 'status_master':
            return "INSERT INTO status_master (status_name_th, status_name_en, status_name_my) VALUES 
                ('ทำงานอยู่', 'Active', 'Active'),
                ('ลาออก', 'Resigned', 'Resigned')";
        
        case 'function_master':
            return "INSERT INTO function_master (function_name_th, function_name_en, function_name_my) VALUES 
                ('HR', 'HR', 'HR'),
                ('IT', 'IT', 'IT'),
                ('Finance', 'Finance', 'Finance'),
                ('Operation', 'Operation', 'Operation')";
        
        case 'division_master':
            return "INSERT INTO division_master (division_name_th, division_name_en, division_name_my) VALUES 
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('Information Technology', 'Information Technology', 'Information Technology'),
                ('Finance & Accounting', 'Finance & Accounting', 'Finance & Accounting'),
                ('Production', 'Production', 'Production')";
        
        case 'department_master':
            return "INSERT INTO department_master (department_name_th, department_name_en, department_name_my) VALUES 
                ('HR Department', 'HR Department', 'HR Department'),
                ('IT Department', 'IT Department', 'IT Department'),
                ('Accounting', 'Accounting', 'Accounting'),
                ('Production', 'Production', 'Production')";
        
        case 'section_master':
            return "INSERT INTO section_master (section_name_th, section_name_en, section_name_my) VALUES 
                ('HR Section', 'HR Section', 'HR Section'),
                ('IT Section', 'IT Section', 'IT Section'),
                ('Accounting Section', 'Accounting Section', 'Accounting Section'),
                ('Production Section', 'Production Section', 'Production Section')";
        
        case 'operation_master':
            return "INSERT INTO operation_master (operation_name_th, operation_name_en, operation_name_my) VALUES 
                ('HR Operations', 'HR Operations', 'HR Operations'),
                ('IT Operations', 'IT Operations', 'IT Operations'),
                ('Accounting Operations', 'Accounting Operations', 'Accounting Operations'),
                ('Production Operations', 'Production Operations', 'Production Operations')";
        
        case 'position_master':
            return "INSERT INTO position_master (position_name_th, position_name_en, position_name_my) VALUES 
                ('Manager', 'Manager', 'Manager'),
                ('Supervisor', 'Supervisor', 'Supervisor'),
                ('Officer', 'Officer', 'Officer'),
                ('Staff', 'Staff', 'Staff'),
                ('Worker', 'Worker', 'Worker')";
        
        case 'position_level_master':
            return "INSERT INTO position_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('Director', 'Director', 'Director'),
                ('Manager', 'Manager', 'Manager'),
                ('Supervisor', 'Supervisor', 'Supervisor'),
                ('Officer', 'Officer', 'Officer'),
                ('Staff', 'Staff', 'Staff')";
        
        case 'labour_cost_master':
            return "INSERT INTO labour_cost_master (cost_name_th, cost_name_en, cost_name_my) VALUES 
                ('Direct', 'Direct', 'Direct'),
                ('Indirect', 'Indirect', 'Indirect')";
        
        case 'hiring_type_master':
            return "INSERT INTO hiring_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('Daily', 'Daily', 'Daily'),
                ('Monthly', 'Monthly', 'Monthly')";
        
        case 'customer_zone_master':
            return "INSERT INTO customer_zone_master (zone_name_th, zone_name_en, zone_name_my) VALUES 
                ('Zone 1', 'Zone 1', 'Zone 1'),
                ('Zone 2', 'Zone 2', 'Zone 2'),
                ('Zone 3', 'Zone 3', 'Zone 3')";
        
        case 'contribution_level_master':
            return "INSERT INTO contribution_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('C1', 'C1', 'C1'),
                ('C2', 'C2', 'C2'),
                ('C3', 'C3', 'C3')";
        
        case 'termination_reason_master':
            return "INSERT INTO termination_reason_master (reason_th, reason_en, reason_my) VALUES 
                ('ลาออก', 'Resign', 'Resign'),
                ('ถูกไล่ออก', 'Terminate', 'Terminate'),
                ('เกษียณ', 'Retire', 'Retire')";
        
        case 'service_category_master':
            return "INSERT INTO service_category_master (category_name_th, category_name_en, category_name_my) VALUES 
                ('ใบลา', 'Leave', 'Leave'),
                ('หนังสือรับรอง', 'Certificate', 'Certificate'),
                ('เบิกอุปกรณ์', 'Supplies', 'Supplies')";
        
        case 'service_type_master':
            return "INSERT INTO service_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('รายบุคคล', 'Individual', 'Individual'),
                ('รายกลุ่ม', 'Group', 'Group')";
        
        case 'doc_type_master':
            return "INSERT INTO doc_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('คู่มือ', 'Handbook', 'Handbook'),
                ('แบบฟอร์ม', 'Form', 'Form'),
                ('ประกาศ', 'Announcement', 'Announcement')";
        
        case 'certificate_types':
            return "INSERT INTO certificate_types (type_name_th, type_name_en, type_name_my, template_content) VALUES 
                ('หนังสือรับรองการทำงาน', 'Work Certificate', 'Work Certificate', 'Work Certificate Template'),
                ('หนังสือรับรองเงินเดือน', 'Salary Certificate', 'Salary Certificate', 'Salary Certificate Template')";
        
        case 'company_info':
            return "INSERT INTO company_info (company_name_th, company_name_en, phone, address) VALUES 
                ('บริษัท แทร็กซ์ อินเตอร์เทรด จำกัด', 'Trax Intertrade Co., Ltd.', '043-507-089', '61 หมู่ 5 ถนนร้อยเอ็ด-กาฬสินธุ์ ต.จังหาร อ.จังหาร จ.ร้อยเอ็ด 45000')";
        
        case 'localization_master':
            return "INSERT INTO localization_master (key_id, th_text, en_text, my_text, category) VALUES 
                ('app_title', 'ระบบบริการทรัพยากรบุคคล', 'HR Service System', 'HR Service System', 'general'),
                ('login', 'เข้าสู่ระบบ', 'Login', 'Login', 'auth'),
                ('logout', 'ออกจากระบบ', 'Logout', 'Logout', 'auth')";
        
        case 'locker_master':
            return "INSERT INTO locker_master (locker_number, locker_location, status) VALUES 
                ('L001', 'Floor 1 - Zone A', 'Available'),
                ('L002', 'Floor 1 - Zone B', 'Available'),
                ('L003', 'Floor 2 - Zone A', 'Available'),
                ('L004', 'Floor 2 - Zone B', 'Available')";
        
        case 'employees':
            $password = password_hash('password123', PASSWORD_DEFAULT);
            return "INSERT INTO employees 
                (employee_id, prefix_id, full_name_th, full_name_en, function_id, division_id, department_id, 
                section_id, operation_id, position_id, position_level_id, labour_cost_id, hiring_type_id, 
                customer_zone_id, contribution_level_id, sex_id, nationality_id, birthday, education_level_id, 
                phone_no, address_province, date_of_hire, status_id, username, password, role_id) 
                VALUES 
                ('90681322', 1, 'Admin User', 'Admin User', 1, 1, 1, 1, 1, 1, 2, 1, 2, 1, 1, 1, 1, '1985-01-01', 3, '0812345678', 'Bangkok', '2020-01-01', 1, '90681322', '$password', 1),
                ('90681323', 2, 'Officer User', 'Officer User', 1, 1, 1, 1, 1, 2, 3, 1, 2, 1, 1, 2, 1, '1990-05-15', 3, '0823456789', 'Bangkok', '2021-01-01', 1, '90681323', '$password', 2),
                ('90681324', 1, 'Employee User', 'Employee User', 2, 2, 2, 2, 2, 3, 4, 1, 2, 1, 1, 1, 1, '1995-10-20', 3, '0834567890', 'Bangkok', '2022-01-01', 1, '90681324', '$password', 3)";
        
        default:
            return ''; // No seed data
    }
}
?>