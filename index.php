<?php
/**
 * HR Service Main Entry Point
 * Handles routing and authentication checks
 */

require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db/Localization.php';

// Check if database and tables exist
if (!checkDatabaseExists() || !checkTablesExist()) {
    header('Location: ' . BASE_PATH . '/HR-Service/views/admin/db_manager.php');
    exit();
}

// Require authentication
AuthController::requireAuth();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name_th'];
$user_role = $_SESSION['role'];
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$language = $_SESSION['language'];
$profile_pic = $_SESSION['profile_pic'] ?? '';

// Define theme colors based on mode
$is_dark = ($theme_mode === 'dark');
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-800';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$sidebar_bg = $is_dark ? 'bg-gray-900' : 'bg-blue-600';

?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        .theme-transition {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
</head>
<body class="<?php echo $bg_class; ?> theme-transition <?php echo $is_dark ? 'dark' : ''; ?>">
    
    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 <?php echo $sidebar_bg; ?> text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50 overflow-y-auto">
        <div class="p-6">
            <!-- Logo -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold"><?php echo __('app_title'); ?></h1>
                    <p class="text-sm opacity-75 mt-1">v<?php echo APP_VERSION; ?></p>
                </div>
                <button onclick="toggleMobileMenu()" class="lg:hidden text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- User Profile -->
            <div class="bg-white bg-opacity-10 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                        <?php if ($profile_pic): ?>
                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-sm"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="text-xs opacity-75"><?php echo htmlspecialchars($user_role); ?></p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="space-y-1">
                <!-- Dashboard -->
                <a href="/HR-Service/index.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span><?php echo __('dashboard'); ?></span>
                </a>

                <!-- Employees (Admin/Officer only) -->
                <?php if (in_array($user_role, ['admin', 'officer'])): ?>
                <a href="/HR-Service/views/admin/employees.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span><?php echo __('employees'); ?></span>
                </a>
                <?php endif; ?>

                <!-- My Requests (All) -->
                <a href="/HR-Service/views/employee/my_requests.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span><?php echo __('requests'); ?></span>
                </a>

                <!-- Request Management (Admin/Officer) -->
                <?php if (in_array($user_role, ['admin', 'officer'])): ?>
                <a href="/HR-Service/views/admin/request_management.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span>Manage Requests</span>
                </a>
                <?php endif; ?>

                <!-- Locker Management (Admin/Officer) -->
                <?php if (in_array($user_role, ['admin', 'officer'])): ?>
                <a href="/HR-Service/views/admin/locker_management.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Locker Management</span>
                </a>
                <?php endif; ?>

                <!-- Documents (Admin/Officer) -->
                <?php if (in_array($user_role, ['admin', 'officer'])): ?>
                <a href="/HR-Service/views/admin/documents.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Online Documents</span>
                </a>
                <?php endif; ?>

                <!-- Master Data (Admin only) -->
                <?php if ($user_role === 'admin'): ?>
                <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                    <p class="px-4 text-xs font-semibold opacity-75 mb-2">ADMIN</p>
                    <a href="/HR-Service/views/admin/master_data.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        <span>Master Data</span>
                    </a>
                    <a href="/HR-Service/views/admin/company_settings.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Company Settings</span>
                    </a>
                </div>
                <?php endif; ?>

                <!-- Settings -->
                <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                    <a href="/HR-Service/views/settings.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span><?php echo __('settings'); ?></span>
                    </a>
                    <a href="/HR-Service/controllers/logout.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
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
        <!-- Top Bar -->
        <header class="<?php echo $card_bg; ?> shadow-sm sticky top-0 z-30 theme-transition">
            <div class="flex items-center justify-between px-4 py-4">
                <div class="flex items-center">
                    <button onclick="toggleMobileMenu()" class="lg:hidden mr-4 <?php echo $text_class; ?>">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="text-xl font-semibold <?php echo $text_class; ?>"><?php echo __('dashboard'); ?></h2>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Language Switcher -->
                    <select onchange="changeLanguage(this.value)" class="px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white border-gray-600' : 'bg-white'; ?>">
                        <option value="th" <?php echo $language === 'th' ? 'selected' : ''; ?>>ไทย</option>
                        <option value="en" <?php echo $language === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="my" <?php echo $language === 'my' ? 'selected' : ''; ?>>မြန်မာ</option>
                    </select>

                    <!-- Theme Mode Toggle -->
                    <button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Toggle Dark Mode">
                        <?php if ($is_dark): ?>
                            <!-- Sun Icon (Light Mode) -->
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        <?php else: ?>
                            <!-- Moon Icon (Dark Mode) -->
                            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="p-4 md:p-6">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-6 text-white">
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p class="opacity-90">Here's what's happening with your HR activities today.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Card 1 -->
                <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">Pending Requests</p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>">5</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">Completed</p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>">12</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">Documents</p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>">8</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">Notifications</p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>">3</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
                <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/HR-Service/views/employee/request_leave.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">Request Leave</span>
                    </button>

                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/HR-Service/views/employee/request_certificate.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">Certificate</span>
                    </button>

                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/HR-Service/views/employee/request_idcard.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">ID Card</span>
                    </button>

                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/HR-Service/views/employee/my_requests.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">View Requests</span>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function changeLanguage(lang) {
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
                }
            });
        }

        function toggleTheme() {
            const currentMode = document.body.classList.contains('dark') ? 'dark' : 'light';
            const newMode = currentMode === 'dark' ? 'light' : 'dark';
            
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
                }
            });
        }
    </script>
</body>
</html>