<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer']);

$user_role = $_SESSION['role'];
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$language = $_SESSION['language'];

// Define theme classes
$is_dark = ($theme_mode === 'dark');
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-800';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';

// Get employee ID
$employee_id = $_GET['id'] ?? '';

if (empty($employee_id)) {
    header('Location: ' . BASE_PATH . '/views/admin/employees.php');
    exit();
}

// Get employee data
$employee = Employee::getById($employee_id);

if (!$employee) {
    header('Location: ' . BASE_PATH . '/views/admin/employees.php?error=Employee not found');
    exit();
}

// Calculate age if birthday exists
$age = 0;
if ($employee['birthday']) {
    $birthday = new DateTime($employee['birthday']);
    $now = new DateTime();
    $age = $now->diff($birthday)->y;
}

// Calculate years of service
$years_service = 0;
if ($employee['date_of_hire']) {
    $hire_date = new DateTime($employee['date_of_hire']);
    $now = new DateTime();
    $years_service = $now->diff($hire_date)->y;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details - <?php echo htmlspecialchars($employee['employee_id']); ?></title>
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="<?php echo $bg_class; ?> theme-transition">
    
    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden no-print" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gradient-to-b from-blue-600 to-indigo-700 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50 overflow-y-auto shadow-xl no-print">
        <div class="p-6">
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

            <nav class="space-y-1">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span><?php echo __('dashboard'); ?></span>
                </a>

                <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" class="flex items-center px-4 py-3 rounded-lg bg-white bg-opacity-20 font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span><?php echo __('employees'); ?></span>
                </a>

                <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span><?php echo __('requests'); ?></span>
                </a>

                <?php if (in_array($user_role, ['admin', 'officer'])): ?>
                <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span>Manage Requests</span>
                </a>
                <?php endif; ?>

                <?php if ($user_role === 'admin'): ?>
                <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                    <p class="px-4 text-xs font-semibold opacity-75 mb-2">ADMIN</p>
                    <a href="<?php echo BASE_PATH; ?>/views/admin/master_data.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        <span>Master Data</span>
                    </a>
                </div>
                <?php endif; ?>

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
        <header class="<?php echo $card_bg; ?> shadow-sm sticky top-0 z-30 theme-transition border-b <?php echo $border_class; ?> no-print">
            <div class="flex items-center justify-between px-4 py-4">
                <div class="flex items-center">
                    <button onclick="toggleMobileMenu()" class="lg:hidden mr-4 <?php echo $text_class; ?> hover:bg-gray-200 dark:hover:bg-gray-700 p-2 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="text-xl font-semibold <?php echo $text_class; ?> hidden md:block">Employee Details</h2>
                </div>

                <div class="flex items-center space-x-3">
                    <button onclick="window.print()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>

                    <?php if ($user_role === 'admin'): ?>
                    <a href="<?php echo BASE_PATH; ?>/views/admin/employee_edit.php?id=<?php echo $employee_id; ?>" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="container mx-auto px-4 py-6 max-w-6xl">
            <!-- Breadcrumb -->
            <div class="mb-6 animate-fade-in no-print">
                <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Employees
                </a>
            </div>

            <!-- Profile Header -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6 animate-fade-in theme-transition">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <!-- Profile Picture -->
                    <div class="flex-shrink-0">
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                            <?php if ($employee['profile_pic_path']): ?>
                                <img src="<?php echo htmlspecialchars($employee['profile_pic_path']); ?>" 
                                     alt="Profile" class="w-full h-full rounded-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($employee['full_name_en'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Basic Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-3xl font-bold <?php echo $text_class; ?> mb-2">
                                    <?php echo $language === 'en' ? htmlspecialchars($employee['full_name_en']) : htmlspecialchars($employee['full_name_th']); ?>
                                </h1>
                                <p class="text-lg <?php echo $is_dark ? 'text-gray-300' : 'text-gray-600'; ?> mb-4">
                                    <?php echo get_master('position_master', $employee['position_id']); ?>
                                </p>
                            </div>
                            <?php 
                            $status_colors = [
                                1 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                2 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                3 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            $status_color = $status_colors[$employee['status_id']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-4 py-2 rounded-full text-sm font-medium <?php echo $status_color; ?>">
                                <?php echo get_master('status_master', $employee['status_id']); ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                </svg>
                                <div>
                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">Employee ID</p>
                                    <p class="font-semibold <?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['employee_id']); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">Phone</p>
                                    <p class="font-semibold <?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['phone_no'] ?? '-'); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">Years of Service</p>
                                    <p class="font-semibold <?php echo $text_class; ?>"><?php echo $years_service; ?> years</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 animate-fade-in theme-transition">
                        <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Personal Information
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Full Name (Thai)</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['full_name_th']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Full Name (English)</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['full_name_en']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Prefix</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('prefix_master', $employee['prefix_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Sex</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('sex_master', $employee['sex_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Birthday</p>
                                <p class="font-semibold <?php echo $text_class; ?>">
                                    <?php echo $employee['birthday'] ? date('M d, Y', strtotime($employee['birthday'])) : '-'; ?>
                                    <?php if ($age > 0): ?>
                                        <span class="text-sm text-gray-500">(<?php echo $age; ?> years old)</span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Nationality</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('nationality_master', $employee['nationality_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg md:col-span-2">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Education Level</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('education_level_master', $employee['education_level_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg md:col-span-2">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Address</p>
                                <p class="font-semibold <?php echo $text_class; ?>">
                                    <?php 
                                    $address_parts = array_filter([
                                        $employee['address_village'],
                                        $employee['address_subdistrict'],
                                        $employee['address_district'],
                                        $employee['address_province']
                                    ]);
                                    echo !empty($address_parts) ? implode(', ', $address_parts) : '-';
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 animate-fade-in theme-transition">
                        <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Employment Details
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Function</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('function_master', $employee['function_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Division</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('division_master', $employee['division_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Department</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('department_master', $employee['department_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Section</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('section_master', $employee['section_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Operation</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('operation_master', $employee['operation_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Position Level</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('position_level_master', $employee['position_level_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Hiring Type</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('hiring_type_master', $employee['hiring_type_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Labour Cost</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('labour_cost_master', $employee['labour_cost_id']); ?></p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Date of Hire</p>
                                <p class="font-semibold <?php echo $text_class; ?>">
                                    <?php echo $employee['date_of_hire'] ? date('M d, Y', strtotime($employee['date_of_hire'])) : '-'; ?>
                                </p>
                            </div>

                            <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Customer Zone</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('customer_zone_master', $employee['customer_zone_id']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 animate-fade-in theme-transition">
                        <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-4">Quick Stats</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-blue-600 dark:text-blue-400">Age</p>
                                        <p class="text-xl font-bold text-blue-700 dark:text-blue-300"><?php echo $age; ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-green-600 dark:text-green-400">Years of Service</p>
                                        <p class="text-xl font-bold text-green-700 dark:text-green-300"><?php echo $years_service; ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-purple-600 dark:text-purple-400">Contribution</p>
                                        <p class="text-sm font-bold text-purple-700 dark:text-purple-300"><?php echo get_master('contribution_level_master', $employee['contribution_level_id']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 animate-fade-in theme-transition">
                        <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-4">Account Info</h2>
                        
                        <div class="space-y-3">
                            <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Username</p>
                                <p class="font-mono font-semibold <?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['username']); ?></p>
                            </div>

                            <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Role</p>
                                <p class="font-semibold <?php echo $text_class; ?>"><?php echo get_master('roles', $employee['role_id']); ?></p>
                            </div>

                            <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1">Last Updated</p>
                                <p class="font-semibold <?php echo $text_class; ?>">
                                    <?php echo $employee['updated_at'] ? date('M d, Y H:i', strtotime($employee['updated_at'])) : '-'; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions (Admin Only) -->
                    <?php if ($user_role === 'admin'): ?>
                    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 animate-fade-in theme-transition no-print">
                        <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-4">Actions</h2>
                        
                        <div class="space-y-3">
                            <a href="<?php echo BASE_PATH; ?>/views/admin/employee_edit.php?id=<?php echo $employee_id; ?>" 
                               class="block w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Employee
                            </a>

                            <button onclick="confirmDelete()" 
                                    class="block w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white text-center rounded-lg transition">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Employee
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function confirmDelete() {
            if (confirm('Are you sure you want to delete this employee?\n\nEmployee: <?php echo addslashes($employee['full_name_en']); ?>\nID: <?php echo $employee_id; ?>\n\nThis action cannot be undone!')) {
                window.location.href = '<?php echo BASE_PATH; ?>/api/employee_delete.php?id=<?php echo $employee_id; ?>';
            }
        }
    </script>
</body>
</html>