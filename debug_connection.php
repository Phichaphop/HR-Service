<?php
/**
 * Debug Database Connection
 * ทดสอบการเชื่อมต่อฐานข้อมูล
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";
echo "<pre>";

// Test 1: Check config file
echo "=== Test 1: Config File ===\n";
$config_path = __DIR__ . '/config/db_config.php';
if (file_exists($config_path)) {
    echo "✓ Config file exists\n";
    require_once $config_path;
    echo "✓ Config file loaded\n";
    
    echo "\nConnection Parameters:\n";
    echo "Server: " . DB_SERVER . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "User: " . DB_USER . "\n";
    echo "Password: " . (DB_PASS ? "***" : "(empty)") . "\n";
} else {
    echo "✗ Config file NOT found at: $config_path\n";
    die();
}

// Test 2: MySQL Connection (without database)
echo "\n=== Test 2: MySQL Server Connection ===\n";
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS);
if ($conn->connect_error) {
    echo "✗ Connection failed: " . $conn->connect_error . "\n";
    die();
} else {
    echo "✓ MySQL server connected\n";
    echo "Server version: " . $conn->server_info . "\n";
}

// Test 3: Database exists
echo "\n=== Test 3: Database Exists ===\n";
$result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
if ($result && $result->num_rows > 0) {
    echo "✓ Database '" . DB_NAME . "' exists\n";
    $conn->select_db(DB_NAME);
} else {
    echo "✗ Database '" . DB_NAME . "' does NOT exist\n";
    echo "\nTrying to create database...\n";
    if ($conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "✓ Database created successfully\n";
        $conn->select_db(DB_NAME);
    } else {
        echo "✗ Failed to create database: " . $conn->error . "\n";
        die();
    }
}

// Test 4: List tables
echo "\n=== Test 4: Tables in Database ===\n";
$result = $conn->query("SHOW TABLES");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " tables:\n";
        while ($row = $result->fetch_array()) {
            echo "  - " . $row[0] . "\n";
        }
    } else {
        echo "⚠ Database exists but no tables found\n";
    }
} else {
    echo "✗ Error listing tables: " . $conn->error . "\n";
}

// Test 5: Check table structure (if employees table exists)
echo "\n=== Test 5: Check employees Table ===\n";
$result = $conn->query("SHOW TABLES LIKE 'employees'");
if ($result && $result->num_rows > 0) {
    echo "✓ employees table exists\n";
    
    // Count rows
    $count_result = $conn->query("SELECT COUNT(*) as cnt FROM employees");
    if ($count_result) {
        $count = $count_result->fetch_assoc();
        echo "  Rows: " . $count['cnt'] . "\n";
    }
    
    // Show structure
    echo "\n  Table structure:\n";
    $struct = $conn->query("DESCRIBE employees");
    if ($struct) {
        while ($field = $struct->fetch_assoc()) {
            echo "    - " . $field['Field'] . " (" . $field['Type'] . ")\n";
        }
    }
} else {
    echo "⚠ employees table does NOT exist\n";
}

// Test 6: Check roles table
echo "\n=== Test 6: Check roles Table ===\n";
$result = $conn->query("SHOW TABLES LIKE 'roles'");
if ($result && $result->num_rows > 0) {
    echo "✓ roles table exists\n";
    
    $count_result = $conn->query("SELECT COUNT(*) as cnt FROM roles");
    if ($count_result) {
        $count = $count_result->fetch_assoc();
        echo "  Rows: " . $count['cnt'] . "\n";
        
        if ($count['cnt'] > 0) {
            echo "\n  Sample data:\n";
            $data = $conn->query("SELECT * FROM roles LIMIT 3");
            while ($row = $data->fetch_assoc()) {
                echo "    - ID: " . $row['role_id'] . ", Name: " . $row['role_name'] . "\n";
            }
        }
    }
} else {
    echo "⚠ roles table does NOT exist\n";
}

// Test 7: PHP Settings
echo "\n=== Test 7: PHP Settings ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "s\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "mysqli extension: " . (extension_loaded('mysqli') ? "✓ Loaded" : "✗ NOT loaded") . "\n";

// Test 8: Try simple insert
echo "\n=== Test 8: Try Simple Insert ===\n";
echo "Testing if we can insert data...\n";

// First create a test table
$conn->query("DROP TABLE IF EXISTS test_table");
$create_test = "CREATE TABLE test_table (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_value VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($create_test)) {
    echo "✓ Test table created\n";
    
    // Try to insert
    $insert_test = "INSERT INTO test_table (test_value) VALUES ('Test Data 1'), ('Test Data 2')";
    if ($conn->query($insert_test)) {
        echo "✓ Insert successful\n";
        
        // Check data
        $result = $conn->query("SELECT * FROM test_table");
        if ($result && $result->num_rows > 0) {
            echo "✓ Data verification: Found " . $result->num_rows . " rows\n";
            while ($row = $result->fetch_assoc()) {
                echo "    - ID: " . $row['id'] . ", Value: " . $row['test_value'] . "\n";
            }
        }
    } else {
        echo "✗ Insert failed: " . $conn->error . "\n";
    }
    
    // Clean up
    $conn->query("DROP TABLE IF EXISTS test_table");
    echo "✓ Test table cleaned up\n";
} else {
    echo "✗ Failed to create test table: " . $conn->error . "\n";
}

// Test 9: Check function availability
echo "\n=== Test 9: Check Functions ===\n";
if (function_exists('getDbConnection')) {
    echo "✓ getDbConnection() function exists\n";
    $test_conn = getDbConnection();
    if ($test_conn) {
        echo "✓ getDbConnection() works\n";
        $test_conn->close();
    } else {
        echo "✗ getDbConnection() returned null\n";
    }
} else {
    echo "⚠ getDbConnection() function NOT found\n";
}

$conn->close();

echo "\n=== END OF TESTS ===\n";
echo "</pre>";
?>