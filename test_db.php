<?php
require_once 'config/db_config.php';

echo "🔍 Testing Database Connection...\n\n";

// Test 1: DB Connection
$conn = getDbConnection();
if ($conn) {
    echo "✅ Database Connection: OK\n";
    echo "   Database: " . DB_NAME . "\n";
    
    // Test 2: Tables exist
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        $table_count = $result->num_rows;
        echo "✅ Tables: $table_count tables found\n";
        
        // List tables
        echo "\n📋 Tables:\n";
        while ($row = $result->fetch_array()) {
            echo "   - " . $row[0] . "\n";
        }
    }
    
    $conn->close();
} else {
    echo "❌ Database Connection: FAILED\n";
}

echo "\n✅ Test complete!\n";
?>