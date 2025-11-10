<?php
/**
 * Database Manager Handler - FIXED VERSION
 * Ensures clean JSON output only
 */

// CRITICAL: No output before JSON!
ob_start();

// Set headers FIRST
header('Content-Type: application/json; charset=utf-8');

// Error handling - NO OUTPUT!
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set execution time
@ini_set('max_execution_time', 600);
@ini_set('memory_limit', '512M');

// Response array
$response = [
    'success' => false,
    'message' => 'No action specified',
    'debug' => []
];

try {
    // Clear any previous output
    ob_clean();
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get action
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    
    if (empty($action)) {
        throw new Exception('No action specified');
    }
    
    $response['debug'][] = "Action: $action";
    
    // Check authentication
    if (!isset($_SESSION['db_manager_verified']) || $_SESSION['db_manager_verified'] !== true) {
        throw new Exception('Not authenticated');
    }
    
    $response['debug'][] = "Authentication OK";
    
    // Load config
    $config_file = __DIR__ . '/../../config/db_config.php';
    if (!file_exists($config_file)) {
        throw new Exception("Config file not found: $config_file");
    }
    
    require_once $config_file;
    $response['debug'][] = "Config loaded";
    
    // Load DatabaseManager
    $db_manager_file = __DIR__ . '/../../controllers/DatabaseManager.php';
    if (!file_exists($db_manager_file)) {
        throw new Exception("DatabaseManager not found: $db_manager_file");
    }
    
    require_once $db_manager_file;
    $response['debug'][] = "DatabaseManager loaded";
    
    // Check if schema file exists
    $schema_file = __DIR__ . '/../../db/schema.sql';
    if (!file_exists($schema_file)) {
        throw new Exception("Schema file not found at: $schema_file");
    }
    $response['debug'][] = "Schema file found";
    
    // Process actions
    switch ($action) {
        case 'check_status':
            $db_exists = checkDatabaseExists();
            $tables_exist = $db_exists && checkTablesExist();
            
            $response = [
                'success' => true,
                'message' => 'Status checked',
                'db_exists' => $db_exists,
                'tables_exist' => $tables_exist
            ];
            break;
            
        case 'create_database':
            $result = DatabaseManager::createDatabase();
            $response = $result;
            break;
            
        case 'drop_database':
            $result = DatabaseManager::dropDatabase();
            $response = $result;
            break;
            
        case 'create_tables':
            // Create tables
            $result = DatabaseManager::createAllTables();
            
            if ($result['success']) {
                // Wait for tables to fully commit
                sleep(2);
                
                // Seed data
                $seed_result = DatabaseManager::seedAllMasterData();
                
                if ($seed_result['success']) {
                    $response = [
                        'success' => true,
                        'message' => $result['message'] . ' | Seed Data: ' . $seed_result['total_rows'] . ' records',
                        'details' => $seed_result['details'] ?? []
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => $result['message'] . ' | Seed Error: ' . $seed_result['message']
                    ];
                }
            } else {
                $response = $result;
            }
            break;
            
        case 'drop_tables':
            $result = DatabaseManager::dropAllTables();
            $response = $result;
            break;
            
        case 'seed_data':
            $result = DatabaseManager::seedAllMasterData();
            $response = $result;
            break;
            
        default:
            throw new Exception('Unknown action: ' . $action);
    }
    
} catch (Throwable $e) {
    $response = [
        'success' => false,
        'message' => 'ERROR: ' . $e->getMessage(),
        'type' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

// Clear all output
while (ob_get_level()) {
    ob_end_clean();
}

// Output ONLY JSON
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
?>