<?php
/**
 * Database Debug Script
 * Check table structure and sample data
 */

require_once __DIR__ . '/config/db_config.php';

$conn = getDbConnection();

if (!$conn) {
    die("Database connection failed");
}

echo "=== DATABASE DEBUG SCRIPT ===\n\n";

// 1. Check employees table structure
echo "1. EMPLOYEES TABLE STRUCTURE:\n";
echo "================================\n";
$result = $conn->query("DESCRIBE employees");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n";

// 2. Check position_master table structure
echo "2. POSITION_MASTER TABLE STRUCTURE:\n";
echo "====================================\n";
$result = $conn->query("DESCRIBE position_master");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n";

// 3. Check function_master table structure
echo "3. FUNCTION_MASTER TABLE STRUCTURE:\n";
echo "====================================\n";
$result = $conn->query("DESCRIBE function_master");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n";

// 4. Check status_master table structure
echo "4. STATUS_MASTER TABLE STRUCTURE:\n";
echo "=================================\n";
$result = $conn->query("DESCRIBE status_master");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n";

// 5. Sample data from employees
echo "5. SAMPLE EMPLOYEE DATA (FIRST ROW):\n";
echo "====================================\n";
$result = $conn->query("SELECT * FROM employees LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    foreach ($row as $key => $value) {
        echo "- " . $key . " => " . (is_null($value) ? 'NULL' : $value) . "\n";
    }
} else {
    echo "No employees found\n";
}

echo "\n";

// 6. Test JOIN query
echo "6. TEST JOIN QUERY:\n";
echo "==================\n";
$sql = "SELECT 
    e.employee_id,
    e.full_name_th,
    e.full_name_en,
    e.position_id,
    e.function_id,
    e.status_id,
    e.year_of_service,
    e.phone_no,
    e.profile_pic_path,
    p.position_name_th,
    p.position_name_en,
    p.position_name_my,
    f.function_name_th,
    f.function_name_en,
    f.function_name_my,
    s.status_name_th,
    s.status_name_en,
    s.status_name_my
FROM employees e
LEFT JOIN position_master p ON e.position_id = p.position_id
LEFT JOIN function_master f ON e.function_id = f.function_id
LEFT JOIN status_master s ON e.status_id = s.status_id
LIMIT 1";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo "Query executed successfully!\n\n";
    $row = $result->fetch_assoc();
    foreach ($row as $key => $value) {
        echo "- " . $key . " => " . (is_null($value) ? 'NULL' : $value) . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n";

// 7. Check master data counts
echo "7. MASTER DATA COUNTS:\n";
echo "=====================\n";
$tables = ['position_master', 'function_master', 'status_master'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "- $table: " . $row['cnt'] . " records\n";
    }
}

echo "\n";

// 8. Sample master data
echo "8. SAMPLE POSITION_MASTER DATA:\n";
echo "==============================\n";
$result = $conn->query("SELECT * FROM position_master LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['position_id'] . " | TH: " . ($row['position_name_th'] ?? 'NULL') . " | EN: " . ($row['position_name_en'] ?? 'NULL') . " | MY: " . ($row['position_name_my'] ?? 'NULL') . "\n";
    }
} else {
    echo "No data or error: " . $conn->error . "\n";
}

echo "\n";

echo "9. SAMPLE FUNCTION_MASTER DATA:\n";
echo "==============================\n";
$result = $conn->query("SELECT * FROM function_master LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['function_id'] . " | TH: " . ($row['function_name_th'] ?? 'NULL') . " | EN: " . ($row['function_name_en'] ?? 'NULL') . " | MY: " . ($row['function_name_my'] ?? 'NULL') . "\n";
    }
} else {
    echo "No data or error: " . $conn->error . "\n";
}

echo "\n";

echo "10. SAMPLE STATUS_MASTER DATA:\n";
echo "============================\n";
$result = $conn->query("SELECT * FROM status_master LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['status_id'] . " | TH: " . ($row['status_name_th'] ?? 'NULL') . " | EN: " . ($row['status_name_en'] ?? 'NULL') . " | MY: " . ($row['status_name_my'] ?? 'NULL') . "\n";
    }
} else {
    echo "No data or error: " . $conn->error . "\n";
}

$conn->close();

echo "\n=== END DEBUG ===\n";
?>