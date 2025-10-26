<?php
/**
 * Database Manager View
 * Super Admin Only - Database & Table Management
 * This page handles database and table creation/deletion
 */

session_start();

// Require authentication
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/DatabaseManager.php';

// Initialize variables
$message = '';
$message_type = '';
$verified = false;
$db_exists = false;
$tables_exist = false;

// Check verification
if (isset($_SESSION['db_manager_verified']) && 
    isset($_SESSION['db_manager_time']) && 
    (time() - $_SESSION['db_manager_time']) < 1800) {
    $verified = true;
}

// Handle verification form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify') {
    $code = $_POST['super_admin_code'] ?? '';
    
    if (DatabaseManager::verifySuperAdminCode($code)) {
        $_SESSION['db_manager_verified'] = true;
        $_SESSION['db_manager_time'] = time();
        $verified = true;
        $message = 'Verified Successfully! ✓';
        $message_type = 'success';
    } else {
        $message = 'Invalid Super Admin Code';
        $message_type = 'error';
    }
}

// Handle database operations (only if verified)
if ($verified && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operation'])) {
    $operation = $_POST['operation'];
    
    try {
        switch ($operation) {
            case 'create_database':
                $result = DatabaseManager::createDatabase();
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
                
            case 'drop_database':
                $result = DatabaseManager::dropDatabase();
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
                
            case 'create_tables':
                $result = DatabaseManager::createAllTables();
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
                
            case 'drop_tables':
                $result = DatabaseManager::dropAllTables();
                $message = $result['message'];
                $message_type = $result['success'] ? 'success' : 'error';
                break;
                
            default:
                $message = 'Unknown operation';
                $message_type = 'error';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Check database and tables status
try {
    $db_exists = checkDatabaseExists();
    if ($db_exists) {
        $tables_exist = checkTablesExist();
    }
} catch (Exception $e) {
    error_log("DB Status Check Error: " . $e->getMessage());
}

// Helper function to determine CSS class for status
function getStatusClass($exists) {
    return $exists ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50';
}

function getStatusIcon($exists) {
    if ($exists) {
        return '<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>';
    } else {
        return '<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager - HR Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .alert {
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800">🗄️ Database Manager</h1>
                    <p class="text-gray-600 mt-2">HR Service Database Setup & Management</p>
                    <p class="text-sm text-gray-500 mt-1">⚠️ Super Admin Access Only</p>
                </div>
                <?php if ($verified): ?>
                    <div class="text-right">
                        <span class="inline-block px-6 py-2 bg-green-100 text-green-800 rounded-full text-sm font-bold">
                            ✓ Verified
                        </span>
                        <p class="text-xs text-gray-500 mt-2 font-mono">
                            Expires in <?php echo round((1800 - (time() - $_SESSION['db_manager_time'])) / 60); ?> minutes
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Message Display -->
        <?php if (!empty($message)): ?>
            <div class="alert mb-6 p-4 rounded-lg <?php 
                echo $message_type === 'success' 
                    ? 'bg-green-100 border-l-4 border-green-500 text-green-700' 
                    : 'bg-red-100 border-l-4 border-red-500 text-red-700'; 
            ?>">
                <p class="font-semibold"><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$verified): ?>
            <!-- Verification Form -->
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">🔐 Super Admin Verification</h2>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="verify">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Super Admin Code</label>
                        <input 
                            type="password" 
                            name="super_admin_code" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition"
                            placeholder="Enter super admin code"
                            required
                            autocomplete="off"
                        >
                        <p class="text-xs text-gray-500 mt-2">Enter the code from SUPER_ADMIN_CODE in config</p>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition"
                    >
                        Verify & Proceed
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="flex items-center p-6 border-2 rounded-lg <?php echo getStatusClass($db_exists); ?>">
                    <div class="flex-shrink-0">
                        <?php echo getStatusIcon($db_exists); ?>
                    </div>
                    <div class="ml-4">
                        <p class="font-bold text-lg <?php echo $db_exists ? 'text-green-800' : 'text-red-800'; ?>">
                            Database
                        </p>
                        <p class="text-sm <?php echo $db_exists ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $db_exists ? '✓ Connected' : '✗ Not Found'; ?>
                        </p>
                        <?php if ($db_exists): ?>
                            <p class="text-xs text-gray-600 mt-1 font-mono"><?php echo DB_NAME; ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center p-6 border-2 rounded-lg <?php echo getStatusClass($tables_exist); ?>">
                    <div class="flex-shrink-0">
                        <?php echo getStatusIcon($tables_exist); ?>
                    </div>
                    <div class="ml-4">
                        <p class="font-bold text-lg <?php echo $tables_exist ? 'text-green-800' : 'text-red-800'; ?>">
                            Tables
                        </p>
                        <p class="text-sm <?php echo $tables_exist ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $tables_exist ? '✓ Initialized (34 tables)' : '✗ Not Created'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Operations Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <!-- Database Operations -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📊 Database Operations</h3>
                    
                    <!-- Create Database -->
                    <form method="POST" action="" class="mb-4">
                        <input type="hidden" name="operation" value="create_database">
                        <button 
                            type="submit" 
                            onclick="return confirm('Create database: <?php echo DB_NAME; ?>?');"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                            <?php echo $db_exists ? 'disabled' : ''; ?>
                        >
                            📁 Create Database
                        </button>
                        <?php if ($db_exists): ?>
                            <p class="text-xs text-gray-500 mt-2">✓ Database already exists</p>
                        <?php endif; ?>
                    </form>

                    <!-- Drop Database -->
                    <form method="POST" action="">
                        <input type="hidden" name="operation" value="drop_database">
                        <button 
                            type="submit" 
                            onclick="return confirm('⚠️ DELETE DATABASE?\n\nThis will permanently delete:\n- Database: <?php echo DB_NAME; ?>\n- All tables\n- All data\n\nThis CANNOT be undone!');"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                            <?php echo !$db_exists ? 'disabled' : ''; ?>
                        >
                            🗑️ Drop Database
                        </button>
                        <?php if (!$db_exists): ?>
                            <p class="text-xs text-gray-500 mt-2">Database doesn't exist</p>
                        <?php else: ?>
                            <p class="text-xs text-red-600 mt-2">⚠️ Permanent deletion</p>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Table Operations -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📋 Table Operations</h3>
                    
                    <!-- Create Tables -->
                    <form method="POST" action="" class="mb-4">
                        <input type="hidden" name="operation" value="create_tables">
                        <button 
                            type="submit" 
                            onclick="return confirm('Create 34 tables and seed initial data?\n\nTables:\n- 2 System tables\n- 20 Master data tables\n- 2 Main tables\n- 8 Request tables\n- 2 Support tables');"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                            <?php echo (!$db_exists || $tables_exist) ? 'disabled' : ''; ?>
                        >
                            ✨ Create All Tables
                        </button>
                        <?php if ($tables_exist): ?>
                            <p class="text-xs text-gray-500 mt-2">✓ Tables already initialized</p>
                        <?php elseif (!$db_exists): ?>
                            <p class="text-xs text-gray-500 mt-2">Create database first</p>
                        <?php else: ?>
                            <p class="text-xs text-green-600 mt-2">✓ Will seed initial data</p>
                        <?php endif; ?>
                    </form>

                    <!-- Drop Tables -->
                    <form method="POST" action="">
                        <input type="hidden" name="operation" value="drop_tables">
                        <button 
                            type="submit" 
                            onclick="return confirm('⚠️ DELETE ALL TABLES?\n\nThis will:\n- Drop all 34 tables\n- Delete all data\n- Clear database structure\n\nThis CANNOT be undone!');"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                            <?php echo !$tables_exist ? 'disabled' : ''; ?>
                        >
                            🗑️ Drop All Tables
                        </button>
                        <?php if (!$tables_exist): ?>
                            <p class="text-xs text-gray-500 mt-2">No tables to drop</p>
                        <?php else: ?>
                            <p class="text-xs text-red-600 mt-2">⚠️ Permanent deletion</p>
                        <?php endif; ?>
                    </form>
                </div>

            </div>

            <!-- Quick Start Guide -->
            <?php if (!$tables_exist && $db_exists): ?>
                <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-blue-800 mb-4">🚀 Quick Start</h3>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-700">
                        <li><strong>Click "Create All Tables"</strong> to initialize the database (34 tables)</li>
                        <li>Wait for completion message</li>
                        <li>Go to <a href="<?php echo BASE_PATH; ?>/index.php" class="underline font-bold text-blue-900 hover:text-blue-600">Dashboard</a></li>
                        <li>Login with admin credentials</li>
                    </ol>
                </div>
            <?php elseif ($tables_exist): ?>
                <div class="bg-green-50 border-2 border-green-300 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-green-800 mb-4">✅ System Ready</h3>
                    <p class="text-sm text-green-700 mb-4">Database and tables are initialized. You can now use the system.</p>
                    <a 
                        href="<?php echo BASE_PATH; ?>/index.php" 
                        class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-2 rounded-lg transition"
                    >
                        Go to Dashboard
                    </a>
                </div>
            <?php endif; ?>

            <!-- Logout -->
            <div class="text-center mt-6">
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="logout">
                    <button 
                        type="button" 
                        onclick="
                            if(confirm('Clear verification?')) {
                                <?php session_destroy(); ?>
                                location.reload();
                            }
                        "
                        class="text-sm text-gray-600 hover:text-gray-800 underline"
                    >
                        Clear Verification
                    </button>
                </form>
            </div>

        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div class="bg-gray-200 text-center py-4 mt-8 text-sm text-gray-600">
        <p>HR Service Database Manager | V1.0 | Protected</p>
    </div>

</body>
</html>