<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/DatabaseManager.php';

$message = '';
$message_type = '';
$verified = false;

// Handle super admin verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify') {
    $code = $_POST['super_admin_code'] ?? '';
    
    if (DatabaseManager::verifySuperAdminCode($code)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['db_manager_verified'] = true;
        $_SESSION['db_manager_time'] = time();
        $verified = true;
    } else {
        $message = 'Invalid Super Admin Code';
        $message_type = 'error';
    }
}

// Check if already verified (within 30 minutes)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['db_manager_verified']) && 
    isset($_SESSION['db_manager_time']) && 
    (time() - $_SESSION['db_manager_time']) < 1800) {
    $verified = true;
}

// Handle database operations
if ($verified && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operation'])) {
    switch ($_POST['operation']) {
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
    }
}

// Get database status
$db_exists = checkDatabaseExists();
$tables_exist = $db_exists && checkTablesExist();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager - HR Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Database Manager</h1>
                    <p class="text-gray-600 mt-1">Super Admin Access Only</p>
                </div>
                <div class="text-right">
                    <?php if ($verified): ?>
                        <span class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                            Verified ✓
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!$verified): ?>
            <!-- Verification Form -->
            <div class="max-w-md mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="text-center mb-6">
                        <div class="inline-block bg-yellow-100 rounded-full p-4 mb-4">
                            <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Security Verification</h2>
                        <p class="text-gray-600 mt-2">Enter Super Admin Code to proceed</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                            <p class="text-red-700 text-sm"><?php echo htmlspecialchars($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="verify">
                        
                        <div class="mb-6">
                            <label for="super_admin_code" class="block text-sm font-medium text-gray-700 mb-2">
                                Super Admin Code
                            </label>
                            <input 
                                type="password" 
                                id="super_admin_code" 
                                name="super_admin_code" 
                                required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Enter security code"
                            >
                            <p class="mt-2 text-xs text-gray-500">Default code: HRSA2024</p>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-3 rounded-lg transition"
                        >
                            Verify Access
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="<?php echo BASE_PATH; ?>/views/auth/login.php" class="text-sm text-blue-600 hover:text-blue-800">
                            Back to Login
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Database Operations -->
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700'; ?> border-l-4 rounded-lg">
                    <p class="font-medium"><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Database Status -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Database Status</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center p-4 border rounded-lg <?php echo $db_exists ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50'; ?>">
                        <div class="flex-shrink-0">
                            <?php if ($db_exists): ?>
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium <?php echo $db_exists ? 'text-green-800' : 'text-red-800'; ?>">Database</p>
                            <p class="text-sm <?php echo $db_exists ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $db_exists ? 'Connected' : 'Not Found'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center p-4 border rounded-lg <?php echo $tables_exist ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50'; ?>">
                        <div class="flex-shrink-0">
                            <?php if ($tables_exist): ?>
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium <?php echo $tables_exist ? 'text-green-800' : 'text-red-800'; ?>">Tables</p>
                            <p class="text-sm <?php echo $tables_exist ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $tables_exist ? 'Initialized' : 'Not Created'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operations -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Database Operations -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Database Operations</h3>
                    
                    <form method="POST" action="" onsubmit="return confirmAction('create database')" class="mb-4">
                        <input type="hidden" name="operation" value="create_database">
                        <button 
                            type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition <?php echo $db_exists ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                            <?php echo $db_exists ? 'disabled' : ''; ?>
                        >
                            Create Database
                        </button>
                        <?php if ($db_exists): ?>
                            <p class="text-xs text-gray-500 mt-2">Database already exists</p>
                        <?php endif; ?>
                    </form>

                    <form method="POST" action="" onsubmit="return confirmAction('DROP database (WARNING: All data will be lost!)')">
                        <input type="hidden" name="operation" value="drop_database">
                        <button 
                            type="submit" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-lg transition <?php echo !$db_exists ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                            <?php echo !$db_exists ? 'disabled' : ''; ?>
                        >
                            Drop Database
                        </button>
                        <p class="text-xs text-red-500 mt-2">⚠️ This will delete all data permanently!</p>
                    </form>
                </div>

                <!-- Table Operations -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Table Operations</h3>
                    
                    <form method="POST" action="" onsubmit="return confirmAction('create all tables and seed data')" class="mb-4">
                        <input type="hidden" name="operation" value="create_tables">
                        <button 
                            type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg transition <?php echo !$db_exists || $tables_exist ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                            <?php echo !$db_exists || $tables_exist ? 'disabled' : ''; ?>
                        >
                            Create All Tables
                        </button>
                        <?php if ($tables_exist): ?>
                            <p class="text-xs text-gray-500 mt-2">Tables already exist</p>
                        <?php elseif (!$db_exists): ?>
                            <p class="text-xs text-gray-500 mt-2">Create database first</p>
                        <?php else: ?>
                            <p class="text-xs text-green-600 mt-2">✓ Will also seed initial data</p>
                        <?php endif; ?>
                    </form>

                    <form method="POST" action="" onsubmit="return confirmAction('DROP all tables (WARNING: All data will be lost!)')">
                        <input type="hidden" name="operation" value="drop_tables">
                        <button 
                            type="submit" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-lg transition <?php echo !$tables_exist ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                            <?php echo !$tables_exist ? 'disabled' : ''; ?>
                        >
                            Drop All Tables
                        </button>
                        <p class="text-xs text-red-500 mt-2">⚠️ This will delete all tables and data!</p>
                    </form>
                </div>
            </div>

            <!-- Quick Start Guide -->
            <div class="bg-blue-50 rounded-lg shadow-lg p-6 mt-6">
                <h3 class="text-lg font-bold text-blue-800 mb-4">Quick Start Guide</h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-blue-700">
                    <li>Click <strong>"Create Database"</strong> to create the HR database</li>
                    <li>Click <strong>"Create All Tables"</strong> to initialize all tables and seed sample data</li>
                    <li>Go to <a href="<?php echo BASE_PATH; ?>/views/auth/login.php" class="underline font-medium">Login Page</a> to access the system</li>
                    <li>Use demo credentials provided on the login page</li>
                </ol>
            </div>

            <!-- Navigation -->
            <div class="mt-6 text-center">
                <?php if ($db_exists && $tables_exist): ?>
                    <a 
                        href="<?php echo BASE_PATH; ?>/views/auth/login.php" 
                        class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-8 py-3 rounded-lg transition"
                    >
                        Go to Login Page
                    </a>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>

    <script>
        function confirmAction(action) {
            return confirm(`Are you sure you want to ${action}?\n\nThis action cannot be undone.`);
        }
    </script>
</body>
</html>