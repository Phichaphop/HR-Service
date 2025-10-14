<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin role only
AuthController::requireRole(['admin']);

$user_role = $_SESSION['role'];
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$language = $_SESSION['language'];

// Define theme classes
$is_dark = ($theme_mode === 'dark');
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-800';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? '';
    
    $data = [
        'employee_id' => $employee_id,
        'prefix_id' => $_POST['prefix_id'] ?? '',
        'full_name_th' => $_POST['full_name_th'] ?? '',
        'full_name_en' => $_POST['full_name_en'] ?? '',
        'function_id' => $_POST['function_id'] ?? '',
        'division_id' => $_POST['division_id'] ?? '',
        'department_id' => $_POST['department_id'] ?? '',
        'section_id' => $_POST['section_id'] ?? '',
        'operation_id' => $_POST['operation_id'] ?? '',
        'position_id' => $_POST['position_id'] ?? '',
        'position_level_id' => $_POST['position_level_id'] ?? '',
        'labour_cost_id' => $_POST['labour_cost_id'] ?? '',
        'hiring_type_id' => $_POST['hiring_type_id'] ?? '',
        'customer_zone_id' => $_POST['customer_zone_id'] ?? '',
        'contribution_level_id' => $_POST['contribution_level_id'] ?? '',
        'sex_id' => $_POST['sex_id'] ?? '',
        'nationality_id' => $_POST['nationality_id'] ?? '',
        'birthday' => $_POST['birthday'] ?? '',
        'education_level_id' => $_POST['education_level_id'] ?? '',
        'phone_no' => $_POST['phone_no'] ?? '',
        'address_village' => $_POST['address_village'] ?? '',
        'address_subdistrict' => $_POST['address_subdistrict'] ?? '',
        'address_district' => $_POST['address_district'] ?? '',
        'address_province' => $_POST['address_province'] ?? '',
        'date_of_hire' => $_POST['date_of_hire'] ?? '',
        'status_id' => $_POST['status_id'] ?? 1,
        'username' => $employee_id, // Use Employee ID as username
        'password' => $employee_id, // Use Employee ID as default password
        'role_id' => $_POST['role_id'] ?? 3
    ];
    
    $result = Employee::create($data);
    
    if ($result['success']) {
        header('Location: ' . BASE_PATH . '/views/admin/employees.php?success=1&message=' . urlencode('Employee added successfully'));
        exit();
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

// Get master data for dropdowns
$conn = getDbConnection();
$prefixes = $conn->query("SELECT * FROM prefix_master ORDER BY prefix_id")->fetch_all(MYSQLI_ASSOC);
$functions = $conn->query("SELECT * FROM function_master ORDER BY function_id")->fetch_all(MYSQLI_ASSOC);
$divisions = $conn->query("SELECT * FROM division_master ORDER BY division_id")->fetch_all(MYSQLI_ASSOC);
$departments = $conn->query("SELECT * FROM department_master ORDER BY department_id")->fetch_all(MYSQLI_ASSOC);
$sections = $conn->query("SELECT * FROM section_master ORDER BY section_id")->fetch_all(MYSQLI_ASSOC);
$operations = $conn->query("SELECT * FROM operation_master ORDER BY operation_id")->fetch_all(MYSQLI_ASSOC);
$positions = $conn->query("SELECT * FROM position_master ORDER BY position_id")->fetch_all(MYSQLI_ASSOC);
$position_levels = $conn->query("SELECT * FROM position_level_master ORDER BY level_id")->fetch_all(MYSQLI_ASSOC);
$labour_costs = $conn->query("SELECT * FROM labour_cost_master ORDER BY labour_cost_id")->fetch_all(MYSQLI_ASSOC);
$hiring_types = $conn->query("SELECT * FROM hiring_type_master ORDER BY hiring_type_id")->fetch_all(MYSQLI_ASSOC);
$customer_zones = $conn->query("SELECT * FROM customer_zone_master ORDER BY zone_id")->fetch_all(MYSQLI_ASSOC);
$contribution_levels = $conn->query("SELECT * FROM contribution_level_master ORDER BY contribution_id")->fetch_all(MYSQLI_ASSOC);
$sexes = $conn->query("SELECT * FROM sex_master ORDER BY sex_id")->fetch_all(MYSQLI_ASSOC);
$nationalities = $conn->query("SELECT * FROM nationality_master ORDER BY nationality_id")->fetch_all(MYSQLI_ASSOC);
$education_levels = $conn->query("SELECT * FROM education_level_master ORDER BY education_id")->fetch_all(MYSQLI_ASSOC);
$statuses = $conn->query("SELECT * FROM status_master ORDER BY status_id")->fetch_all(MYSQLI_ASSOC);
$roles = $conn->query("SELECT * FROM roles ORDER BY role_id")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee - <?php echo __('app_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        .theme-transition {
            transition: all 0.3s ease;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background-color: #3B82F6;
            color: white;
        }
        .step-indicator.completed {
            background-color: #10B981;
            color: white;
        }
    </style>
</head>
<body class="<?php echo $bg_class; ?> theme-transition">
    
    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gradient-to-b from-blue-600 to-indigo-700 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50 overflow-y-auto shadow-xl">
        <div class="p-6">
            <!-- Logo -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold"><?php echo __('app_title'); ?></h1>
                    <p class="text-sm opacity-75 mt-1">v<?php echo APP_VERSION; ?></p>
                </div>
                <button onclick="toggleMobileMenu()" class="lg:hidden text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- User Profile -->
            <div class="bg-white bg-opacity-10 rounded-lg p-4 mb-6 backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center flex-shrink-0">
                        <?php if ($_SESSION['profile_pic'] ?? ''): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Profile" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-sm"><?php echo htmlspecialchars($_SESSION['full_name_th']); ?></p>
                        <p class="text-xs opacity-75"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="space-y-1">
                <!-- Dashboard -->
                <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span><?php echo __('dashboard'); ?></span>
                </a>

                <!-- Employees (Active) -->
                <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" class="flex items-center px-4 py-3 rounded-lg bg-white bg-opacity-20 font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span><?php echo __('employees'); ?></span>
                </a>

                <!-- My Requests -->
                <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span><?php echo __('requests'); ?></span>
                </a>

                <!-- Request Management -->
                <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span>Manage Requests</span>
                </a>

                <!-- Locker Management -->
                <a href="<?php echo BASE_PATH; ?>/views/admin/locker_management.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Locker Management</span>
                </a>

                <!-- Documents -->
                <a href="<?php echo BASE_PATH; ?>/views/admin/documents.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Online Documents</span>
                </a>

                <!-- Admin Section -->
                <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                    <p class="px-4 text-xs font-semibold opacity-75 mb-2">ADMIN</p>
                    <a href="<?php echo BASE_PATH; ?>/views/admin/master_data.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        <span>Master Data</span>
                    </a>
                    <a href="<?php echo BASE_PATH; ?>/views/admin/company_settings.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Company Settings</span>
                    </a>
                </div>

                <!-- Settings & Logout -->
                <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                    <a href="<?php echo BASE_PATH; ?>/views/settings.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span><?php echo __('settings'); ?></span>
                    </a>
                    <a href="<?php echo BASE_PATH; ?>/controllers/logout.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span><?php echo __('logout'); ?></span>
                    </a>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Header -->
        <header class="<?php echo $card_bg; ?> shadow-sm sticky top-0 z-30 theme-transition border-b <?php echo $border_class; ?>">
            <div class="flex items-center justify-between px-4 py-4">
                <div class="flex items-center">
                    <button onclick="toggleMobileMenu()" class="lg:hidden mr-4 <?php echo $text_class; ?> hover:bg-gray-200 dark:hover:bg-gray-700 p-2 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="text-xl font-semibold <?php echo $text_class; ?> hidden md:block">Add New Employee</h2>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Language Switcher -->
                    <select onchange="changeLanguage(this.value)" class="px-3 py-2 border <?php echo $border_class; ?> rounded-lg text-sm focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                        <option value="th" <?php echo $language === 'th' ? 'selected' : ''; ?>>ไทย</option>
                        <option value="en" <?php echo $language === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="my" <?php echo $language === 'my' ? 'selected' : ''; ?>>မြန်မာ</option>
                    </select>

                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Toggle Dark Mode">
                        <?php if ($is_dark): ?>
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        <?php else: ?>
                            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications -->
                    <button class="relative p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Notifications">
                        <svg class="w-6 h-6 <?php echo $text_class; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="container mx-auto px-4 py-6 max-w-6xl">
        <!-- Header -->
        <div class="mb-6 animate-slide-in">
            <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm mb-3 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Employees
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold <?php echo $text_class; ?>">Add New Employee</h1>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">Fill in the employee information below</p>
                </div>
                <div class="hidden md:flex items-center space-x-2 <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900 border-l-4 border-red-500'; ?> animate-slide-in">
                <div class="flex items-center">
                    <svg class="w-6 h-6 <?php echo $message_type === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?> mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php if ($message_type === 'success'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <?php else: ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <?php endif; ?>
                    </svg>
                    <p class="<?php echo $message_type === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'; ?> font-medium">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Progress Steps -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6 animate-slide-in theme-transition">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <div class="step-indicator active w-8 h-8 rounded-full flex items-center justify-center font-bold">1</div>
                    <span class="<?php echo $text_class; ?> font-medium">Basic Info</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 dark:bg-gray-600 mx-4"></div>
                <div class="flex items-center space-x-2">
                    <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold <?php echo $is_dark ? 'bg-gray-700 text-gray-400' : 'bg-gray-200 text-gray-600'; ?>">2</div>
                    <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> font-medium">Position</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 dark:bg-gray-600 mx-4"></div>
                <div class="flex items-center space-x-2">
                    <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold <?php echo $is_dark ? 'bg-gray-700 text-gray-400' : 'bg-gray-200 text-gray-600'; ?>">3</div>
                    <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> font-medium">Account</span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="" id="employeeForm" class="space-y-6">
            
            <!-- Step 1: Basic Information -->
            <div id="step1" class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 animate-slide-in theme-transition">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold <?php echo $text_class; ?>">Basic Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Employee ID -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Employee ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="employee_id" id="employee_id" required maxlength="8" 
                               placeholder="e.g., 90681322"
                               pattern="[0-9]{8}"
                               oninput="updateCredentials()"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">8 digits only (e.g., 90681322)</p>
                    </div>

                    <!-- Prefix -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Prefix <span class="text-red-500">*</span>
                        </label>
                        <select name="prefix_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Prefix</option>
                            <?php foreach ($prefixes as $prefix): ?>
                                <option value="<?php echo $prefix['prefix_id']; ?>">
                                    <?php echo get_master('prefix_master', $prefix['prefix_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sex -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Sex <span class="text-red-500">*</span>
                        </label>
                        <select name="sex_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Sex</option>
                            <?php foreach ($sexes as $sex): ?>
                                <option value="<?php echo $sex['sex_id']; ?>">
                                    <?php echo get_master('sex_master', $sex['sex_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Full Name (Thai) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Full Name (Thai) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="full_name_th" required 
                               placeholder="e.g., สมชาย ใจดี"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                    </div>

                    <!-- Full Name (English) -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Full Name (English) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="full_name_en" required 
                               placeholder="e.g., Somchai Jaidee"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                    </div>

                    <!-- Birthday -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Birthday <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="birthday" required 
                               max="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Nationality <span class="text-red-500">*</span>
                        </label>
                        <select name="nationality_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Nationality</option>
                            <?php foreach ($nationalities as $nationality): ?>
                                <option value="<?php echo $nationality['nationality_id']; ?>">
                                    <?php echo get_master('nationality_master', $nationality['nationality_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Education Level -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Education Level <span class="text-red-500">*</span>
                        </label>
                        <select name="education_level_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Education</option>
                            <?php foreach ($education_levels as $edu): ?>
                                <option value="<?php echo $edu['education_id']; ?>">
                                    <?php echo get_master('education_level_master', $edu['education_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone_no" required 
                               placeholder="e.g., 081-234-5678"
                               pattern="[0-9\-]+"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                    </div>

                    <!-- Address Fields -->
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Address
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input type="text" name="address_village" placeholder="Village" 
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <input type="text" name="address_subdistrict" placeholder="Subdistrict" 
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <input type="text" name="address_district" placeholder="District" 
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <input type="text" name="address_province" placeholder="Province" 
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" onclick="nextStep(2)" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Next Step →
                    </button>
                </div>
            </div>

            <!-- Step 2: Position & Employment Details -->
            <div id="step2" class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 hidden theme-transition">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold <?php echo $text_class; ?>">Position & Employment Details</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Function -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Function <span class="text-red-500">*</span>
                        </label>
                        <select name="function_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Function</option>
                            <?php foreach ($functions as $func): ?>
                                <option value="<?php echo $func['function_id']; ?>">
                                    <?php echo get_master('function_master', $func['function_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Division -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Division <span class="text-red-500">*</span>
                        </label>
                        <select name="division_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?php echo $div['division_id']; ?>">
                                    <?php echo get_master('division_master', $div['division_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select name="department_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>">
                                    <?php echo get_master('department_master', $dept['department_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Section -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Section <span class="text-red-500">*</span>
                        </label>
                        <select name="section_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Section</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section['section_id']; ?>">
                                    <?php echo get_master('section_master', $section['section_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Operation -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Operation <span class="text-red-500">*</span>
                        </label>
                        <select name="operation_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Operation</option>
                            <?php foreach ($operations as $op): ?>
                                <option value="<?php echo $op['operation_id']; ?>">
                                    <?php echo get_master('operation_master', $op['operation_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Position -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Position <span class="text-red-500">*</span>
                        </label>
                        <select name="position_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['position_id']; ?>">
                                    <?php echo get_master('position_master', $pos['position_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Position Level -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Position Level <span class="text-red-500">*</span>
                        </label>
                        <select name="position_level_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Level</option>
                            <?php foreach ($position_levels as $level): ?>
                                <option value="<?php echo $level['level_id']; ?>">
                                    <?php echo get_master('position_level_master', $level['level_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Labour Cost -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Labour Cost <span class="text-red-500">*</span>
                        </label>
                        <select name="labour_cost_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Labour Cost</option>
                            <?php foreach ($labour_costs as $lc): ?>
                                <option value="<?php echo $lc['labour_cost_id']; ?>">
                                    <?php echo get_master('labour_cost_master', $lc['labour_cost_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Hiring Type -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Hiring Type <span class="text-red-500">*</span>
                        </label>
                        <select name="hiring_type_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Hiring Type</option>
                            <?php foreach ($hiring_types as $ht): ?>
                                <option value="<?php echo $ht['hiring_type_id']; ?>">
                                    <?php echo get_master('hiring_type_master', $ht['hiring_type_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Customer Zone -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Customer Zone <span class="text-red-500">*</span>
                        </label>
                        <select name="customer_zone_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Zone</option>
                            <?php foreach ($customer_zones as $zone): ?>
                                <option value="<?php echo $zone['zone_id']; ?>">
                                    <?php echo get_master('customer_zone_master', $zone['zone_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Contribution Level -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Contribution Level <span class="text-red-500">*</span>
                        </label>
                        <select name="contribution_level_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <option value="">Select Level</option>
                            <?php foreach ($contribution_levels as $cl): ?>
                                <option value="<?php echo $cl['contribution_id']; ?>">
                                    <?php echo get_master('contribution_level_master', $cl['contribution_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date of Hire -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Date of Hire <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_of_hire" required 
                               max="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['status_id']; ?>" <?php echo $status['status_id'] == 1 ? 'selected' : ''; ?>>
                                    <?php echo get_master('status_master', $status['status_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="prevStep(1)" 
                            class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition">
                        ← Previous
                    </button>
                    <button type="button" onclick="nextStep(3)" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Next Step →
                    </button>
                </div>
            </div>

            <!-- Step 3: Account Information -->
            <div id="step3" class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 hidden theme-transition">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold <?php echo $text_class; ?>">Account Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- This section has been moved - Username and Password are now in the main Step 3 section above -->

                    <!-- Role -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php foreach ($roles as $role): ?>
                                <label class="flex items-center p-4 border-2 <?php echo $border_class; ?> rounded-lg cursor-pointer hover:border-blue-500 transition">
                                    <input type="radio" name="role_id" value="<?php echo $role['role_id']; ?>" 
                                           <?php echo $role['role_id'] == 3 ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <span class="font-medium <?php echo $text_class; ?>">
                                            <?php echo get_master('roles', $role['role_id']); ?>
                                        </span>
                                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                            <?php 
                                            $role_desc = [
                                                1 => 'Full system access',
                                                2 => 'Manage requests and employees',
                                                3 => 'View and submit requests'
                                            ];
                                            echo $role_desc[$role['role_id']] ?? '';
                                            ?>
                                        </p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Account Information</p>
                            <ul class="text-xs text-blue-700 dark:text-blue-400 mt-1 list-disc list-inside">
                                <li>Username and Password will be automatically set as Employee ID</li>
                                <li>Employee ID must be 8 digits (e.g., 90681322)</li>
                                <li>Employee should change password after first login for security</li>
                                <li>Select appropriate role based on employee's responsibilities</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" onclick="prevStep(2)" 
                            class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition">
                        ← Previous
                    </button>
                    <button type="submit" 
                            class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Create Employee
                    </button>
                </div>
            </div>

        </form>

        <!-- Summary Preview (Optional) -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mt-6 theme-transition">
            <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4">Quick Tips</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium <?php echo $text_class; ?>">Required Fields</p>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">All fields marked with <span class="text-red-500">*</span> are mandatory</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium <?php echo $text_class; ?>">Auto Calculation</p>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">Age and years of service will be calculated automatically</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium <?php echo $text_class; ?>">Default Credentials</p>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">Username and password are set as Employee ID (8 digits)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;

        // Update username and password when Employee ID changes
        function updateCredentials() {
            const employeeId = document.getElementById('employee_id').value;
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            
            if (employeeId && employeeId.length === 8) {
                usernameField.value = employeeId;
                passwordField.value = employeeId;
                
                // Add visual feedback
                usernameField.classList.remove('border-gray-300', 'dark:border-gray-600');
                usernameField.classList.add('border-green-500', 'dark:border-green-500');
                passwordField.classList.remove('border-gray-300', 'dark:border-gray-600');
                passwordField.classList.add('border-green-500', 'dark:border-green-500');
                
                // Show success message
                showToast('Username and Password auto-filled successfully!', 'success');
            } else {
                usernameField.value = '';
                passwordField.value = '';
                
                // Reset border colors
                usernameField.classList.remove('border-green-500', 'dark:border-green-500');
                usernameField.classList.add('border-gray-300', 'dark:border-gray-600');
                passwordField.classList.remove('border-green-500', 'dark:border-green-500');
                passwordField.classList.add('border-gray-300', 'dark:border-gray-600');
            }
        }

        // Debounce function to avoid too many toast messages
        let updateTimeout;
        document.getElementById('employee_id').addEventListener('input', function() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(updateCredentials, 500);
        });

        function nextStep(step) {
            // Validate current step
            if (!validateStep(currentStep)) {
                return;
            }

            // Hide current step
            document.getElementById(`step${currentStep}`).classList.add('hidden');
            
            // Show next step
            document.getElementById(`step${step}`).classList.remove('hidden');
            
            // Update step indicators
            updateStepIndicators(step);
            
            currentStep = step;
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function prevStep(step) {
            // Hide current step
            document.getElementById(`step${currentStep}`).classList.add('hidden');
            
            // Show previous step
            document.getElementById(`step${step}`).classList.remove('hidden');
            
            // Update step indicators
            updateStepIndicators(step);
            
            currentStep = step;
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateStepIndicators(activeStep) {
            for (let i = 1; i <= 3; i++) {
                const indicators = document.querySelectorAll('.step-indicator');
                if (i < activeStep) {
                    indicators[i - 1].classList.remove('active');
                    indicators[i - 1].classList.add('completed');
                } else if (i === activeStep) {
                    indicators[i - 1].classList.remove('completed');
                    indicators[i - 1].classList.add('active');
                } else {
                    indicators[i - 1].classList.remove('active', 'completed');
                }
            }
        }

        function validateStep(step) {
            const stepElement = document.getElementById(`step${step}`);
            const requiredInputs = stepElement.querySelectorAll('[required]');
            
            for (let input of requiredInputs) {
                if (!input.value.trim()) {
                    input.focus();
                    input.classList.add('border-red-500');
                    setTimeout(() => input.classList.remove('border-red-500'), 2000);
                    
                    showToast('Please fill in all required fields', 'error');
                    return false;
                }
            }
            
            return true;
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 animate-slide-in
                ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${type === 'success' 
                            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                        }
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Form submission validation
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            if (!validateStep(3)) {
                e.preventDefault();
                return;
            }

            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="w-5 h-5 inline-block animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Creating Employee...';
        });

        // Auto-format phone number
        document.querySelector('input[name="phone_no"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3 && value.length <= 6) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            } else if (value.length > 6) {
                value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
            }
            e.target.value = value;
        });

        // Validate Employee ID format (8 digits only)
        document.getElementById('employee_id').addEventListener('input', function(e) {
            // Remove non-numeric characters
            e.target.value = e.target.value.replace(/\D/g, '');
            
            // Limit to 8 digits
            if (e.target.value.length > 8) {
                e.target.value = e.target.value.slice(0, 8);
            }
        });

        // Toggle mobile menu
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Change language
        function changeLanguage(lang) {
            // Show loading
            const selectElement = event.target;
            selectElement.disabled = true;
            
            fetch('<?php echo BASE_PATH; ?>/api/change_language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ language: lang })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to change language: ' + (data.message || 'Unknown error'));
                    selectElement.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to change language. Please try again.');
                selectElement.disabled = false;
            });
        }

        // Toggle theme
        function toggleTheme() {
            const currentMode = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            const newMode = currentMode === 'dark' ? 'light' : 'dark';
            
            // Show loading indicator
            const themeButton = event.currentTarget;
            themeButton.disabled = true;
            themeButton.style.opacity = '0.5';
            
            fetch('<?php echo BASE_PATH; ?>/api/change_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ mode: newMode })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to change theme: ' + (data.message || 'Unknown error'));
                    themeButton.disabled = false;
                    themeButton.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to change theme. Please try again.');
                themeButton.disabled = false;
                themeButton.style.opacity = '1';
            });
        }
    </script>
</body>
</html>