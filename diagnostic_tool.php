<?php
/**
 * Database Manager - Diagnostic Tool
 * ใช้หาปัญหาของ Database Manager
 */

session_start();

// Get all system information
$diagnostics = [];

// 1. PHP Configuration
$diagnostics['php'] = [
    'Version' => phpversion(),
    'Max Execution Time' => ini_get('max_execution_time') . 's',
    'Memory Limit' => ini_get('memory_limit'),
    'Default Error Handler' => ini_get('display_errors') ? 'Displayed' : 'Hidden',
];

// 2. File Paths
$diagnostics['paths'] = [
    'Current Directory' => __DIR__,
    'Config File' => __DIR__ . '/../../config/db_config.php',
    'Schema File' => __DIR__ . '/../../db/schema.sql',
    'DatabaseManager' => __DIR__ . '/../../controllers/DatabaseManager.php',
];

// 3. File Existence Checks
$diagnostics['files_exist'] = [
    'db_config.php' => file_exists(__DIR__ . '/../../config/db_config.php') ? '✅ YES' : '❌ NO',
    'schema.sql' => file_exists(__DIR__ . '/../../db/schema.sql') ? '✅ YES' : '❌ NO',
    'DatabaseManager.php' => file_exists(__DIR__ . '/../../controllers/DatabaseManager.php') ? '✅ YES' : '❌ NO',
    'db_manager.php' => file_exists(__DIR__ . '/db_manager.php') ? '✅ YES' : '❌ NO',
];

// 4. File Permissions
$diagnostics['file_permissions'] = [
    'db_config.php readable' => is_readable(__DIR__ . '/../../config/db_config.php') ? '✅ YES' : '❌ NO',
    'schema.sql readable' => is_readable(__DIR__ . '/../../db/schema.sql') ? '✅ YES' : '❌ NO',
];

// 5. Database Configuration
require_once __DIR__ . '/../../config/db_config.php';

$diagnostics['database_config'] = [
    'Server' => DB_SERVER,
    'Database Name' => DB_NAME,
    'User' => DB_USER,
    'Password Set' => strlen(DB_PASS) > 0 ? '✅ YES' : '⚠️ EMPTY (may be OK for local dev)',
];

// 6. Database Connection Test
$diagnostics['database_connection'] = [];
$conn_test = @new mysqli(DB_SERVER, DB_USER, DB_PASS);
if ($conn_test->connect_error) {
    $diagnostics['database_connection']['Connection Status'] = '❌ FAILED: ' . $conn_test->connect_error;
} else {
    $diagnostics['database_connection']['Connection Status'] = '✅ SUCCESS';
    $diagnostics['database_connection']['MySQL Version'] = $conn_test->server_info;
    $conn_test->close();
}

// 7. Database Existence
$diagnostics['database_status'] = [];
$db_test = @new mysqli(DB_SERVER, DB_USER, DB_PASS);
if (!$db_test->connect_error) {
    $result = $db_test->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $exists = $result && $result->num_rows > 0;
    $diagnostics['database_status']['Database Exists'] = $exists ? '✅ YES' : '❌ NO';
    
    if ($exists) {
        // Count tables
        $db_connect = @new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if (!$db_connect->connect_error) {
            $tables_result = $db_connect->query("SHOW TABLES");
            $table_count = $tables_result ? $tables_result->num_rows : 0;
            $diagnostics['database_status']['Tables Count'] = $table_count;
            $db_connect->close();
        }
    }
    $db_test->close();
}

// 8. Schema File Content Check
$diagnostics['schema_file'] = [];
$schema_path = __DIR__ . '/../../db/schema.sql';
if (file_exists($schema_path)) {
    $schema_content = file_get_contents($schema_path);
    $diagnostics['schema_file']['File Size'] = filesize($schema_path) . ' bytes';
    $diagnostics['schema_file']['Contains CREATE TABLE'] = strpos($schema_content, 'CREATE TABLE') !== false ? '✅ YES' : '❌ NO';
    $diagnostics['schema_file']['Lines Count'] = count(file($schema_path));
} else {
    $diagnostics['schema_file']['Status'] = '❌ File not found';
}

// 9. Class Availability
$diagnostics['classes'] = [
    'DatabaseManager class exists' => class_exists('DatabaseManager') ? '✅ YES' : '❌ NO',
    'mysqli class exists' => class_exists('mysqli') ? '✅ YES' : '❌ NO',
];

// 10. Error Log
$diagnostics['error_log'] = [
    'Error Log Location' => ini_get('error_log'),
    'Error Reporting' => error_reporting(),
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager - Diagnostic Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-ok { color: #10b981; }
        .status-bad { color: #ef4444; }
        .status-warn { color: #f59e0b; }
        .code-block { background: #1f2937; color: #10b981; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800">🔍 Database Manager Diagnostic</h1>
                    <p class="text-gray-600 mt-1">System Health Check & Troubleshooting</p>
                </div>
                <div class="text-right">
                    <a href="db_manager.php" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ← Back to Manager
                    </a>
                </div>
            </div>
        </div>

        <!-- Diagnostic Report -->
        <div class="grid grid-cols-1 gap-6">

            <?php foreach ($diagnostics as $section => $items): ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 capitalize">
                    <?php echo ucfirst(str_replace('_', ' ', $section)); ?>
                </h2>

                <div class="space-y-2">
                    <?php foreach ($items as $key => $value): ?>
                    <div class="flex justify-between items-start p-3 bg-gray-50 rounded-lg border-l-4 border-gray-300">
                        <span class="font-semibold text-gray-700"><?php echo $key; ?></span>
                        <span class="text-right text-gray-900">
                            <?php 
                            // Color code the value
                            if (strpos($value, '✅') !== false) {
                                echo '<span class="status-ok">' . $value . '</span>';
                            } elseif (strpos($value, '❌') !== false) {
                                echo '<span class="status-bad">' . $value . '</span>';
                            } elseif (strpos($value, '⚠️') !== false) {
                                echo '<span class="status-warn">' . $value . '</span>';
                            } else {
                                echo $value;
                            }
                            ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

        </div>

        <!-- Troubleshooting Guide -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">🔧 Troubleshooting Guide</h2>

            <div class="space-y-4">
                <!-- Issue 1 -->
                <div class="border-l-4 border-red-500 p-4 bg-red-50 rounded-lg">
                    <h3 class="font-bold text-red-800 mb-2">❌ Database Connection Failed</h3>
                    <p class="text-red-700 text-sm mb-2">If you see "Connection failed" above:</p>
                    <ul class="text-red-700 text-sm list-disc list-inside space-y-1">
                        <li>Check that MySQL/XAMPP is running</li>
                        <li>Verify DB_SERVER is correct (usually 'localhost')</li>
                        <li>Verify DB_USER is correct (usually 'root')</li>
                        <li>Verify DB_PASS is correct (usually empty for local)</li>
                        <li>Try: <code class="bg-white px-2 py-1 rounded">mysql -u root -p</code></li>
                    </ul>
                </div>

                <!-- Issue 2 -->
                <div class="border-l-4 border-orange-500 p-4 bg-orange-50 rounded-lg">
                    <h3 class="font-bold text-orange-800 mb-2">⚠️ Schema File Not Found</h3>
                    <p class="text-orange-700 text-sm mb-2">If "schema.sql" shows ❌ NO:</p>
                    <ul class="text-orange-700 text-sm list-disc list-inside space-y-1">
                        <li>File should be at: <code class="bg-white px-2 py-1 rounded">/db/schema.sql</code></li>
                        <li>Make sure file exists in your project</li>
                        <li>Check the exact path is correct</li>
                    </ul>
                </div>

                <!-- Issue 3 -->
                <div class="border-l-4 border-yellow-500 p-4 bg-yellow-50 rounded-lg">
                    <h3 class="font-bold text-yellow-800 mb-2">⚠️ DatabaseManager Class Not Found</h3>
                    <p class="text-yellow-700 text-sm mb-2">If "DatabaseManager class exists" shows ❌ NO:</p>
                    <ul class="text-yellow-700 text-sm list-disc list-inside space-y-1">
                        <li>File should be at: <code class="bg-white px-2 py-1 rounded">/controllers/DatabaseManager.php</code></li>
                        <li>Check require_once statement in db_manager.php</li>
                        <li>Verify filename spelling exactly</li>
                    </ul>
                </div>

                <!-- Issue 4 -->
                <div class="border-l-4 border-blue-500 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-bold text-blue-800 mb-2">ℹ️ Database Doesn't Exist</h3>
                    <p class="text-blue-700 text-sm mb-2">If "Database Exists" shows ❌ NO:</p>
                    <ul class="text-blue-700 text-sm list-disc list-inside space-y-1">
                        <li>This is normal on first run</li>
                        <li>Go back to Manager and click "Create Database"</li>
                        <li>Then click "Create All Tables"</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Fixes -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">🛠️ Quick Fixes</h2>

            <div class="space-y-4">
                <!-- Fix 1 -->
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h3 class="font-bold text-green-800 mb-2">Fix: Cannot connect to MySQL</h3>
                    <div class="code-block">
# Make sure MySQL is running in XAMPP
# Or try connecting manually:
mysql -h localhost -u root -p

# If it fails, check:
# - XAMPP MySQL service is started
# - Port 3306 is not blocked
# - Credentials are correct
                    </div>
                </div>

                <!-- Fix 2 -->
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h3 class="font-bold text-green-800 mb-2">Fix: Silent failure (button does nothing)</h3>
                    <div class="code-block">
1. Check browser console (F12 → Console tab)
2. Check server error log:
   - Windows: C:\xampp\apache\logs\error.log
   - Linux: /var/log/apache2/error.log

3. Enable error display in db_config.php:
   error_reporting(E_ALL);
   ini_set('display_errors', 1);

4. Refresh page with Ctrl+Shift+R
                    </div>
                </div>

                <!-- Fix 3 -->
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h3 class="font-bold text-green-800 mb-2">Fix: Memory exhausted error</h3>
                    <div class="code-block">
# In db_config.php or DatabaseManager.php:
ini_set('memory_limit', '1024M');

# Or in php.ini:
memory_limit = 1024M

# Restart Apache after changing php.ini
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Files Info -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📋 Where to Find Logs</h2>

            <div class="space-y-2">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <strong>PHP Error Log:</strong>
                    <p class="text-sm text-gray-600 font-mono"><?php echo ini_get('error_log') ?: 'Not configured'; ?></p>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg">
                    <strong>Browser Console:</strong>
                    <p class="text-sm text-gray-600">Press F12 → Go to "Console" tab to see JavaScript errors</p>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg">
                    <strong>XAMPP Logs (Windows):</strong>
                    <p class="text-sm text-gray-600 font-mono">C:\xampp\apache\logs\error.log</p>
                    <p class="text-sm text-gray-600 font-mono">C:\xampp\apache\logs\access.log</p>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg">
                    <strong>Server Error Output:</strong>
                    <p class="text-sm text-gray-600">Check Network tab in browser DevTools</p>
                    <p class="text-sm text-gray-600">Look for failed requests and their response text</p>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-blue-50 rounded-lg shadow-lg p-6 mt-6 border-l-4 border-blue-500">
            <h2 class="text-2xl font-bold text-blue-800 mb-4">👉 Next Steps</h2>

            <ol class="text-blue-700 space-y-2 list-decimal list-inside">
                <li>Check the diagnostics above for any ❌ or ⚠️</li>
                <li>If MySQL connection fails: Start MySQL/XAMPP</li>
                <li>If files not found: Check file paths are correct</li>
                <li>If everything OK: Go back to Manager and try again</li>
                <li>If still fails: Check PHP error log and browser console</li>
                <li>Send error messages to your developer</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-600">
            <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p class="text-sm">Diagnostic Tool v1.0</p>
            <a href="db_manager.php" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                ← Back to Database Manager
            </a>
        </div>

    </div>

</body>
</html>