<?php
/**
 * Certificate Request Test Script
 * Use this to debug and test the API independently
 * 
 * Place this in your project root: /test_certificate.php
 * Access via: http://localhost/your_project/test_certificate.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Certificate Request Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test { margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .pass { background: #d4edda; border-color: #c3e6cb; }
        .fail { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 20px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        .result { white-space: pre-wrap; font-family: monospace; background: #fafafa; padding: 10px; border-radius: 4px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Certificate Request System - Test Suite</h1>";

// Test 1: Database Connection
echo "<div class='test'>";
echo "<h2>Test 1: Database Connection</h2>";

require_once __DIR__ . '/config/db_config.php';

$conn = getDbConnection();

if ($conn) {
    echo "<div class='pass'>✓ Database connection successful</div>";
    
    // Get connection details
    echo "<div class='result'>";
    echo "Host: " . $conn->get_connection_string() . "\n";
    echo "Database: if0_39800794_db\n";
    echo "</div>";
} else {
    echo "<div class='fail'>✗ Database connection failed</div>";
    echo "Error: Check db_config.php settings";
    exit();
}

echo "</div>";

// Test 2: Check certificate_requests table structure
echo "<div class='test'>";
echo "<h2>Test 2: Certificate Requests Table Structure</h2>";

$result = $conn->query("DESCRIBE certificate_requests");

if ($result) {
    echo "<div class='pass'>✓ Table exists</div>";
    echo "<table border='1' style='width:100%; margin-top: 10px;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . ($row['Key'] ?? '-') . "</td>";
        echo "<td>" . ($row['Default'] ?? '-') . "</td>";
        echo "<td>" . ($row['Extra'] ?? '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for required columns
    $required_columns = ['certificate_no', 'employee_id', 'employee_name', 'position', 'division', 'date_of_hire', 'hiring_type', 'base_salary', 'purpose', 'status'];
    $missing = [];
    
    foreach ($required_columns as $col) {
        if (!in_array($col, $columns)) {
            $missing[] = $col;
        }
    }
    
    if (empty($missing)) {
        echo "<div class='pass' style='margin-top: 10px;'>✓ All required columns exist</div>";
    } else {
        echo "<div class='fail' style='margin-top: 10px;'>✗ Missing columns: " . implode(', ', $missing) . "</div>";
    }
    
    // Check if cert_type_id exists
    if (in_array('cert_type_id', $columns)) {
        echo "<div class='info' style='margin-top: 10px;'>ℹ️ Note: cert_type_id column exists (but not used in working version)</div>";
    }
} else {
    echo "<div class='fail'>✗ Table query failed: " . $conn->error . "</div>";
}

echo "</div>";

// Test 3: Check employee data
echo "<div class='test'>";
echo "<h2>Test 3: Employee Data Availability</h2>";

$emp_count = $conn->query("SELECT COUNT(*) as count FROM employees");

if ($emp_count) {
    $row = $emp_count->fetch_assoc();
    if ($row['count'] > 0) {
        echo "<div class='pass'>✓ Employee records exist: " . $row['count'] . " total</div>";
        
        // Show sample employee
        $sample = $conn->query("SELECT employee_id, full_name_th, position_id, division_id, date_of_hire, base_salary FROM employees LIMIT 1");
        if ($sample && $sample->num_rows > 0) {
            $emp = $sample->fetch_assoc();
            echo "<div class='info' style='margin-top: 10px;'>Sample Employee:</div>";
            echo "<div class='result'>";
            echo "ID: " . $emp['employee_id'] . "\n";
            echo "Name: " . $emp['full_name_th'] . "\n";
            echo "Position ID: " . $emp['position_id'] . "\n";
            echo "Division ID: " . $emp['division_id'] . "\n";
            echo "Hire Date: " . $emp['date_of_hire'] . "\n";
            echo "Salary: " . $emp['base_salary'] . "\n";
            echo "</div>";
        }
    } else {
        echo "<div class='fail'>✗ No employee records found</div>";
    }
} else {
    echo "<div class='fail'>✗ Query failed: " . $conn->error . "</div>";
}

echo "</div>";

// Test 4: Check Master Data Tables
echo "<div class='test'>";
echo "<h2>Test 4: Master Data Tables</h2>";

$master_tables = [
    'prefix_master' => 'Prefixes',
    'position_master' => 'Positions',
    'division_master' => 'Divisions',
    'hiring_type_master' => 'Hiring Types'
];

foreach ($master_tables as $table => $name) {
    $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($count_result) {
        $row = $count_result->fetch_assoc();
        if ($row['count'] > 0) {
            echo "<div class='pass'>✓ $name: " . $row['count'] . " records</div>";
        } else {
            echo "<div class='fail'>✗ $name: No records found</div>";
        }
    } else {
        echo "<div class='fail'>✗ $name: Query failed</div>";
    }
}

echo "</div>";

// Test 5: Test JOIN Query (simulating form page)
echo "<div class='test'>";
echo "<h2>Test 5: Employee Data JOIN Query (Form Page Simulation)</h2>";

// Get first employee for testing
$first_emp = $conn->query("SELECT employee_id FROM employees LIMIT 1");
if ($first_emp && $first_emp->num_rows > 0) {
    $emp_row = $first_emp->fetch_assoc();
    $test_emp_id = $emp_row['employee_id'];
    
    $join_sql = "SELECT 
                    e.employee_id,
                    CONCAT(COALESCE(pm.prefix_name_th, 'Mr.'), ' ', e.full_name_th) as full_name,
                    pos.position_name_th as position,
                    div.division_name_th as division,
                    e.date_of_hire,
                    ht.type_name_th as hiring_type,
                    e.base_salary
                FROM employees e
                LEFT JOIN prefix_master pm ON e.prefix_id = pm.prefix_id
                LEFT JOIN position_master pos ON e.position_id = pos.position_id
                LEFT JOIN division_master div ON e.division_id = div.division_id
                LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
                WHERE e.employee_id = ?";
    
    $stmt = $conn->prepare($join_sql);
    $stmt->bind_param("s", $test_emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='pass'>✓ JOIN query successful</div>";
        $data = $result->fetch_assoc();
        echo "<div class='result'>";
        foreach ($data as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
        echo "</div>";
    } else {
        echo "<div class='fail'>✗ JOIN query returned no results</div>";
    }
    
    $stmt->close();
} else {
    echo "<div class='info'>ℹ️ Cannot test JOIN: No employees in database</div>";
}

echo "</div>";

// Test 6: Test INSERT Statement
echo "<div class='test'>";
echo "<h2>Test 6: Test INSERT Statement (Dry Run)</h2>";

$test_cert_no = 'TEST-' . date('Ymd') . '-' . rand(1000, 9999);
$test_employee_id = 'TEST01';
$test_employee_name = 'Test Employee Name';
$test_position = 'Test Position';
$test_division = 'Test Division';
$test_date = '2024-01-01';
$test_hiring_type = 'Test Type';
$test_salary = 15000.00;
$test_purpose = 'This is a test purpose for testing';

$insert_sql = "INSERT INTO certificate_requests (
                certificate_no,
                employee_id,
                employee_name,
                position,
                division,
                date_of_hire,
                hiring_type,
                base_salary,
                purpose,
                status,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'New', NOW(), NOW())";

$insert_stmt = $conn->prepare($insert_sql);

if (!$insert_stmt) {
    echo "<div class='fail'>✗ Prepare failed: " . $conn->error . "</div>";
} else {
    echo "<div class='pass'>✓ Prepare successful</div>";
    
    // Show bind_param string
    echo "<div class='info' style='margin-top: 10px;'>Bind Parameters: 'sssssssds' (9 parameters)</div>";
    echo "<div class='result'>";
    echo "1. (s) cert_no: $test_cert_no\n";
    echo "2. (s) employee_id: $test_employee_id\n";
    echo "3. (s) employee_name: $test_employee_name\n";
    echo "4. (s) position: $test_position\n";
    echo "5. (s) division: $test_division\n";
    echo "6. (s) date_of_hire: $test_date\n";
    echo "7. (s) hiring_type: $test_hiring_type\n";
    echo "8. (d) base_salary: $test_salary\n";
    echo "9. (s) purpose: $test_purpose\n";
    echo "</div>";
    
    // Try to bind
    if ($insert_stmt->bind_param(
        "sssssssds",
        $test_cert_no,
        $test_employee_id,
        $test_employee_name,
        $test_position,
        $test_division,
        $test_date,
        $test_hiring_type,
        $test_salary,
        $test_purpose
    )) {
        echo "<div class='pass' style='margin-top: 10px;'>✓ Bind parameters successful</div>";
        
        echo "<div class='info' style='margin-top: 10px;'>Note: Execute will NOT run (dry test only)</div>";
    } else {
        echo "<div class='fail'>✗ Bind failed: " . $insert_stmt->error . "</div>";
    }
    
    $insert_stmt->close();
}

echo "</div>";

// Test 7: Check File Paths
echo "<div class='test'>";
echo "<h2>Test 7: File Paths</h2>";

$files_to_check = [
    'api/save_certificate_request.php' => 'API Endpoint',
    'views/employee/request_certificate.php' => 'Form Page',
    'config/db_config.php' => 'Database Config',
    'logs' => 'Logs Directory'
];

foreach ($files_to_check as $path => $name) {
    $full_path = __DIR__ . '/' . $path;
    if (file_exists($full_path)) {
        if (is_dir($full_path)) {
            $writable = is_writable($full_path) ? '(writable)' : '(read-only)';
            echo "<div class='pass'>✓ $name exists $writable</div>";
        } else {
            $readable = is_readable($full_path) ? '(readable)' : '(not readable)';
            echo "<div class='pass'>✓ $name exists $readable</div>";
        }
    } else {
        if ($name === 'Logs Directory') {
            echo "<div class='info'>ℹ️ $name does not exist (will be created on first use)</div>";
        } else {
            echo "<div class='fail'>✗ $name not found at: $full_path</div>";
        }
    }
}

echo "</div>";

// Summary
echo "<div class='test' style='background: #e7f3ff; border-color: #2196F3;'>";
echo "<h2>📊 Test Summary</h2>";
echo "<p>If all tests above show ✓ (pass), your system is ready to use!</p>";
echo "<p>If any tests show ✗ (fail), check the error message and fix that issue first.</p>";
echo "</div>";

$conn->close();

echo "
    </div>
</body>
</html>";
?>