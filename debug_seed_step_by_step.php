<?php
/**
 * Debug Seed Data - Step by Step
 * ทดสอบการ seed ข้อมูลทีละขั้นตอน พร้อม error messages
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/config/db_config.php';

echo "<!DOCTYPE html><html><head>";
echo "<meta charset='UTF-8'>";
echo "<title>Seed Debug</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #f5f5f5; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.section { margin: 20px 0; padding: 15px; background: white; border-left: 4px solid #007bff; }
.sql { background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #dee2e6; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔍 Seed Data Debug Tool</h1>";

$conn = getDbConnection();

if (!$conn) {
    echo "<div class='error'>❌ Database connection failed!</div>";
    echo "</body></html>";
    die();
}

echo "<div class='success'>✓ Database connected</div>";

// Set SQL mode
$conn->query("SET sql_mode = ''");
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Function to test insert
function testInsert($conn, $name, $sql, $description = '') {
    echo "<div class='section'>";
    echo "<h3>Testing: $name</h3>";
    if ($description) echo "<p>$description</p>";
    
    echo "<div class='sql'>$sql</div>";
    
    if ($conn->query($sql)) {
        // Get affected rows
        $affected = $conn->affected_rows;
        echo "<div class='success'>✓ SUCCESS - Inserted $affected row(s)</div>";
        
        // Try to count
        preg_match('/INSERT INTO (\w+)/', $sql, $matches);
        if (isset($matches[1])) {
            $table = $matches[1];
            $count_result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
            if ($count_result) {
                $count = $count_result->fetch_assoc();
                echo "<div>Total rows in $table: " . $count['cnt'] . "</div>";
            }
        }
        
        echo "</div>";
        return true;
    } else {
        echo "<div class='error'>✗ FAILED</div>";
        echo "<div class='error'>Error: " . $conn->error . "</div>";
        echo "<div class='error'>Error Code: " . $conn->errno . "</div>";
        echo "</div>";
        return false;
    }
}

// Function to show table contents
function showTableData($conn, $table, $limit = 3) {
    echo "<div style='margin: 10px 0;'>";
    echo "<strong>Sample data from $table:</strong><br>";
    
    $result = $conn->query("SELECT * FROM $table LIMIT $limit");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 5px;'>";
        
        // Header
        $first_row = $result->fetch_assoc();
        echo "<tr>";
        foreach (array_keys($first_row) as $col) {
            echo "<th>$col</th>";
        }
        echo "</tr>";
        
        // First row
        echo "<tr>";
        foreach ($first_row as $val) {
            echo "<td>" . htmlspecialchars(substr($val, 0, 50)) . "</td>";
        }
        echo "</tr>";
        
        // Other rows
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $val) {
                echo "<td>" . htmlspecialchars(substr($val, 0, 50)) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠ No data found</div>";
    }
    echo "</div>";
}

echo "<h2>📊 Starting Seed Process...</h2>";

$success_count = 0;
$fail_count = 0;

// ===== 1. ROLES =====
if (testInsert($conn, "Roles", 
    "INSERT INTO roles (role_name, role_name_th, role_name_en, role_name_my) VALUES 
    ('admin', 'ผู้ดูแลระบบ', 'Administrator', 'Admin'),
    ('officer', 'เจ้าหน้าที่', 'Officer', 'Officer'),
    ('employee', 'พนักงาน', 'Employee', 'Employee')",
    "Insert user roles (admin, officer, employee)"
)) {
    $success_count++;
    showTableData($conn, 'roles');
} else {
    $fail_count++;
}

// ===== 2. PREFIX =====
if (testInsert($conn, "Prefix Master", 
    "INSERT INTO prefix_master (prefix_th, prefix_en, prefix_my) VALUES 
    ('นาย', 'Mr.', 'Mr'),
    ('นาง', 'Mrs.', 'Mrs'),
    ('นางสาว', 'Miss', 'Miss')",
    "Insert name prefixes"
)) {
    $success_count++;
    showTableData($conn, 'prefix_master');
} else {
    $fail_count++;
}

// ===== 3. SEX =====
if (testInsert($conn, "Sex Master", 
    "INSERT INTO sex_master (sex_name_th, sex_name_en, sex_name_my) VALUES 
    ('ชาย', 'Male', 'Male'),
    ('หญิง', 'Female', 'Female')",
    "Insert gender options"
)) {
    $success_count++;
    showTableData($conn, 'sex_master');
} else {
    $fail_count++;
}

// ===== 4. NATIONALITY =====
if (testInsert($conn, "Nationality Master", 
    "INSERT INTO nationality_master (nationality_th, nationality_en, nationality_my) VALUES 
    ('ไทย', 'Thai', 'Thai'),
    ('พม่า', 'Myanmar', 'Myanmar')",
    "Insert nationality options"
)) {
    $success_count++;
    showTableData($conn, 'nationality_master');
} else {
    $fail_count++;
}

// ===== 5. EDUCATION =====
if (testInsert($conn, "Education Level Master", 
    "INSERT INTO education_level_master (level_name_th, level_name_en, level_name_my) VALUES 
    ('ประถมศึกษา', 'Primary', 'Primary'),
    ('มัธยมศึกษา', 'Secondary', 'Secondary'),
    ('ปริญญาตรี', 'Bachelor', 'Bachelor')",
    "Insert education levels"
)) {
    $success_count++;
    showTableData($conn, 'education_level_master');
} else {
    $fail_count++;
}

// ===== 6. STATUS =====
if (testInsert($conn, "Status Master", 
    "INSERT INTO status_master (status_name_th, status_name_en, status_name_my) VALUES 
    ('ทำงานอยู่', 'Active', 'Active'),
    ('ลาออก', 'Resigned', 'Resigned')",
    "Insert employee status"
)) {
    $success_count++;
    showTableData($conn, 'status_master');
} else {
    $fail_count++;
}

// ===== 7. FUNCTION =====
if (testInsert($conn, "Function Master", 
    "INSERT INTO function_master (function_name_th, function_name_en, function_name_my) VALUES 
    ('HR', 'HR', 'HR'),
    ('IT', 'IT', 'IT')",
    "Insert department functions"
)) {
    $success_count++;
    showTableData($conn, 'function_master');
} else {
    $fail_count++;
}

// ===== 8. DIVISION =====
if (testInsert($conn, "Division Master", 
    "INSERT INTO division_master (division_name_th, division_name_en, division_name_my) VALUES 
    ('Human Resource', 'Human Resource', 'Human Resource'),
    ('Information Technology', 'Information Technology', 'Information Technology')",
    "Insert divisions"
)) {
    $success_count++;
    showTableData($conn, 'division_master');
} else {
    $fail_count++;
}

// ===== 9. DEPARTMENT =====
if (testInsert($conn, "Department Master", 
    "INSERT INTO department_master (department_name_th, department_name_en, department_name_my) VALUES 
    ('HR Department', 'HR Department', 'HR Department'),
    ('IT Department', 'IT Department', 'IT Department')",
    "Insert departments"
)) {
    $success_count++;
    showTableData($conn, 'department_master');
} else {
    $fail_count++;
}

// ===== 10. SECTION =====
if (testInsert($conn, "Section Master", 
    "INSERT INTO section_master (section_name_th, section_name_en, section_name_my) VALUES 
    ('HR Section', 'HR Section', 'HR Section'),
    ('IT Section', 'IT Section', 'IT Section')",
    "Insert sections"
)) {
    $success_count++;
    showTableData($conn, 'section_master');
} else {
    $fail_count++;
}

// ===== 11. OPERATION =====
if (testInsert($conn, "Operation Master", 
    "INSERT INTO operation_master (operation_name_th, operation_name_en, operation_name_my) VALUES 
    ('HR Operations', 'HR Operations', 'HR Operations'),
    ('IT Operations', 'IT Operations', 'IT Operations')",
    "Insert operations"
)) {
    $success_count++;
    showTableData($conn, 'operation_master');
} else {
    $fail_count++;
}

// ===== 12. POSITION =====
if (testInsert($conn, "Position Master", 
    "INSERT INTO position_master (position_name_th, position_name_en, position_name_my) VALUES 
    ('Manager', 'Manager', 'Manager'),
    ('Officer', 'Officer', 'Officer'),
    ('Staff', 'Staff', 'Staff')",
    "Insert positions"
)) {
    $success_count++;
    showTableData($conn, 'position_master');
} else {
    $fail_count++;
}

// ===== 13. POSITION LEVEL =====
if (testInsert($conn, "Position Level Master", 
    "INSERT INTO position_level_master (level_name_th, level_name_en, level_name_my) VALUES 
    ('Manager', 'Manager', 'Manager'),
    ('Officer', 'Officer', 'Officer'),
    ('Staff', 'Staff', 'Staff')",
    "Insert position levels"
)) {
    $success_count++;
    showTableData($conn, 'position_level_master');
} else {
    $fail_count++;
}

// ===== 14. LABOUR COST =====
if (testInsert($conn, "Labour Cost Master", 
    "INSERT INTO labour_cost_master (cost_name_th, cost_name_en, cost_name_my) VALUES 
    ('Direct', 'Direct', 'Direct'),
    ('Indirect', 'Indirect', 'Indirect')",
    "Insert labour cost types"
)) {
    $success_count++;
    showTableData($conn, 'labour_cost_master');
} else {
    $fail_count++;
}

// ===== 15. HIRING TYPE =====
if (testInsert($conn, "Hiring Type Master", 
    "INSERT INTO hiring_type_master (type_name_th, type_name_en, type_name_my) VALUES 
    ('Daily', 'Daily', 'Daily'),
    ('Monthly', 'Monthly', 'Monthly')",
    "Insert hiring types"
)) {
    $success_count++;
    showTableData($conn, 'hiring_type_master');
} else {
    $fail_count++;
}

// ===== 16. CUSTOMER ZONE =====
if (testInsert($conn, "Customer Zone Master", 
    "INSERT INTO customer_zone_master (zone_name_th, zone_name_en, zone_name_my) VALUES 
    ('Zone 1', 'Zone 1', 'Zone 1'),
    ('Zone 2', 'Zone 2', 'Zone 2')",
    "Insert customer zones"
)) {
    $success_count++;
    showTableData($conn, 'customer_zone_master');
} else {
    $fail_count++;
}

// ===== 17. CONTRIBUTION LEVEL =====
if (testInsert($conn, "Contribution Level Master", 
    "INSERT INTO contribution_level_master (level_name_th, level_name_en, level_name_my) VALUES 
    ('C1', 'C1', 'C1'),
    ('C2', 'C2', 'C2')",
    "Insert contribution levels"
)) {
    $success_count++;
    showTableData($conn, 'contribution_level_master');
} else {
    $fail_count++;
}

// ===== 18. TERMINATION REASON =====
if (testInsert($conn, "Termination Reason Master", 
    "INSERT INTO termination_reason_master (reason_th, reason_en, reason_my) VALUES 
    ('ลาออก', 'Resign', 'Resign'),
    ('ถูกไล่ออก', 'Terminate', 'Terminate')",
    "Insert termination reasons"
)) {
    $success_count++;
    showTableData($conn, 'termination_reason_master');
} else {
    $fail_count++;
}

// ===== 19. SERVICE CATEGORY =====
if (testInsert($conn, "Service Category Master", 
    "INSERT INTO service_category_master (category_name_th, category_name_en, category_name_my) VALUES 
    ('Leave', 'Leave', 'Leave'),
    ('Certificate', 'Certificate', 'Certificate')",
    "Insert service categories"
)) {
    $success_count++;
    showTableData($conn, 'service_category_master');
} else {
    $fail_count++;
}

// ===== 20. SERVICE TYPE =====
if (testInsert($conn, "Service Type Master", 
    "INSERT INTO service_type_master (type_name_th, type_name_en, type_name_my) VALUES 
    ('Individual', 'Individual', 'Individual'),
    ('Group', 'Group', 'Group')",
    "Insert service types"
)) {
    $success_count++;
    showTableData($conn, 'service_type_master');
} else {
    $fail_count++;
}

// ===== 21. DOC TYPE =====
if (testInsert($conn, "Document Type Master", 
    "INSERT INTO doc_type_master (type_name_th, type_name_en, type_name_my) VALUES 
    ('Handbook', 'Handbook', 'Handbook'),
    ('Form', 'Form', 'Form')",
    "Insert document types"
)) {
    $success_count++;
    showTableData($conn, 'doc_type_master');
} else {
    $fail_count++;
}

// ===== 22. CERTIFICATE TYPE =====
if (testInsert($conn, "Certificate Type Master", 
    "INSERT INTO certificate_types (type_name_th, type_name_en, type_name_my, template_content) VALUES 
    ('Work Certificate', 'Work Certificate', 'Work Certificate', 'Template 1'),
    ('Salary Certificate', 'Salary Certificate', 'Salary Certificate', 'Template 2')",
    "Insert certificate types"
)) {
    $success_count++;
    showTableData($conn, 'certificate_types');
} else {
    $fail_count++;
}

// ===== 23. COMPANY INFO =====
if (testInsert($conn, "Company Info", 
    "INSERT INTO company_info (company_name_th, company_name_en, phone, address) VALUES 
    ('Test Company TH', 'Test Company EN', '02-123-4567', 'Bangkok, Thailand')",
    "Insert company information"
)) {
    $success_count++;
    showTableData($conn, 'company_info');
} else {
    $fail_count++;
}

// ===== 24. LOCALIZATION =====
if (testInsert($conn, "Localization Master", 
    "INSERT INTO localization_master (key_id, th_text, en_text, my_text, category) VALUES 
    ('test', 'ทดสอบ', 'Test', 'Test', 'general')",
    "Insert localization strings"
)) {
    $success_count++;
    showTableData($conn, 'localization_master');
} else {
    $fail_count++;
}

// ===== 25. LOCKER =====
if (testInsert($conn, "Locker Master", 
    "INSERT INTO locker_master (locker_number, locker_location, status) VALUES 
    ('L001', 'Floor 1', 'Available'),
    ('L002', 'Floor 2', 'Available')",
    "Insert locker information"
)) {
    $success_count++;
    showTableData($conn, 'locker_master');
} else {
    $fail_count++;
}

// ===== 26. EMPLOYEE (CRITICAL) =====
echo "<div class='section'>";
echo "<h3>Testing: Employee (CRITICAL TEST)</h3>";
echo "<p>This is the most complex insert with many foreign keys</p>";

$password_hash = password_hash('password123', PASSWORD_DEFAULT);

$employee_sql = "INSERT INTO employees 
    (employee_id, prefix_id, full_name_th, full_name_en, function_id, division_id, department_id, 
    section_id, operation_id, position_id, position_level_id, labour_cost_id, hiring_type_id, 
    customer_zone_id, contribution_level_id, sex_id, nationality_id, birthday, education_level_id, 
    phone_no, address_province, date_of_hire, status_id, username, password, role_id) 
    VALUES 
    ('TEST001', 1, 'Test Admin', 'Test Admin', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1990-01-01', 1, '0812345678', 'Bangkok', '2020-01-01', 1, 'TEST001', '" . $password_hash . "', 1)";

echo "<div class='sql'>" . htmlspecialchars($employee_sql) . "</div>";

if ($conn->query($employee_sql)) {
    $success_count++;
    echo "<div class='success'>✓ SUCCESS - Employee inserted</div>";
    showTableData($conn, 'employees');
} else {
    $fail_count++;
    echo "<div class='error'>✗ FAILED</div>";
    echo "<div class='error'>Error: " . $conn->error . "</div>";
    echo "<div class='error'>Error Code: " . $conn->errno . "</div>";
    
    // Additional debugging for employee insert
    echo "<div style='margin-top: 10px; padding: 10px; background: #fff3cd;'>";
    echo "<strong>Debugging foreign keys:</strong><br>";
    
    $fk_checks = [
        'prefix_id' => 'prefix_master',
        'function_id' => 'function_master',
        'division_id' => 'division_master',
        'department_id' => 'department_master',
        'section_id' => 'section_master',
        'operation_id' => 'operation_master',
        'position_id' => 'position_master',
        'position_level_id' => 'position_level_master',
        'labour_cost_id' => 'labour_cost_master',
        'hiring_type_id' => 'hiring_type_master',
        'customer_zone_id' => 'customer_zone_master',
        'contribution_level_id' => 'contribution_level_master',
        'sex_id' => 'sex_master',
        'nationality_id' => 'nationality_master',
        'education_level_id' => 'education_level_master',
        'status_id' => 'status_master',
        'role_id' => 'roles'
    ];
    
    foreach ($fk_checks as $fk => $table) {
        $check = $conn->query("SELECT COUNT(*) as cnt FROM $table WHERE " . str_replace('_id', '_id', $fk) . " = 1 OR 1=1 LIMIT 1");
        if ($check) {
            $cnt = $check->fetch_assoc();
            echo "$table: " . ($cnt['cnt'] > 0 ? "✓ Has data" : "✗ Empty") . "<br>";
        } else {
            echo "$table: ✗ Table not found<br>";
        }
    }
    echo "</div>";
}
echo "</div>";

// Re-enable foreign keys
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Summary
echo "<div class='section' style='border-left-color: " . ($fail_count > 0 ? "red" : "green") . ";'>";
echo "<h2>📊 Summary</h2>";
echo "<div class='success'>✓ Successful: $success_count</div>";
echo "<div class='error'>✗ Failed: $fail_count</div>";

if ($fail_count == 0) {
    echo "<div class='success'><h3>🎉 ALL TESTS PASSED!</h3></div>";
    echo "<p>Data has been seeded successfully. You can now use the application.</p>";
} else {
    echo "<div class='error'><h3>⚠ SOME TESTS FAILED</h3></div>";
    echo "<p>Please review the errors above and fix them.</p>";
}

echo "</div>";

$conn->close();

echo "</body></html>";
?>