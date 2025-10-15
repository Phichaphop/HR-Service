<?php
/**
 * Reusable Sidebar Component
 * Include after header.php
 */

// Get variables from session (already started in header.php)
$user_id = $_SESSION['user_id'] ?? '';
$display_name = ($language === 'en') ? ($_SESSION['full_name_en'] ?? '') : ($_SESSION['full_name_th'] ?? '');
$user_role = $_SESSION['role'] ?? '';
$profile_pic = $_SESSION['profile_pic'] ?? '';

$sidebar_bg = $is_dark ? 'bg-gray-900' : 'bg-gradient-to-b from-blue-600 to-blue-700';
$sidebar_text = 'text-white';
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 <?php echo $sidebar_bg; ?> <?php echo $sidebar_text; ?> transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50 overflow-y-auto shadow-2xl">
    <div class="p-6">
        <!-- Logo & Close Button -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold flex items-center">
                    <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    HR Service
                </h1>
                <p class="text-sm opacity-75 mt-1">Version <?php echo APP_VERSION; ?></p>
            </div>
            <button onclick="toggleMobileMenu()" class="lg:hidden text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- User Profile Card -->
        <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 mb-6 border border-white border-opacity-20">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center overflow-hidden flex-shrink-0 ring-2 ring-white ring-opacity-30">
                    <?php if ($profile_pic && file_exists(__DIR__ . '/../' . $profile_pic)): ?>
                        <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($profile_pic); ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="ml-3 overflow-hidden flex-1">
                    <p class="font-semibold text-sm truncate"><?php echo htmlspecialchars($display_name); ?></p>
                    <p class="text-xs opacity-75 truncate capitalize"><?php echo htmlspecialchars($user_role); ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-1">
            <!-- Dashboard -->
            <a href="<?php echo BASE_PATH; ?>/index.php" 
               class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Employees (Admin/Officer only) -->
            <?php if (in_array($user_role, ['admin', 'officer'])): ?>
            <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" 
               class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'employees.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span>Employees</span>
            </a>
            <?php endif; ?>

            <!-- My Requests (All Users) -->
            <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" 
               class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'my_requests.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span>My Requests</span>
            </a>

            <!-- Request Management (Admin/Officer) -->
            <?php if (in_array($user_role, ['admin', 'officer'])): ?>
            <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php" 
               class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'request_management.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <span>Manage Requests</span>
            </a>
            <?php endif; ?>

            <!-- Locker Management (Admin/Officer) -->
            <?php if (in_array($user_role, ['admin', 'officer'])): ?>
            <a href="<?php echo BASE_PATH; ?>/views/admin/locker_management.php" 
               class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'locker_management.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>Locker Management</span>
            </a>
            <?php endif; ?>

            <!-- Documents (Admin/Officer) -->
            <?php if (in_array($user_role, ['admin', 'officer'])): ?>
            <a href="<?php echo BASE_PATH; ?>/views/admin/documents.php" 
               class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'documents.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Online Documents</span>
            </a>
            <?php endif; ?>

            <!-- Admin Section -->
            <?php if ($user_role === 'admin'): ?>
            <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                <div class="flex items-center px-4 mb-2">
                    <svg class="w-4 h-4 mr-2 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="text-xs font-semibold opacity-75 uppercase tracking-wider">Admin Panel</p>
                </div>
                
                <a href="<?php echo BASE_PATH; ?>/views/admin/master_data.php" 
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'master_data.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                    <span>Master Data</span>
                </a>
                
                <a href="<?php echo BASE_PATH; ?>/views/admin/company_settings.php" 
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'company_settings.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span>Company Settings</span>
                </a>

                <a href="<?php echo BASE_PATH; ?>/views/admin/db_manager.php" 
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'db_manager.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Database Manager</span>
                </a>
            </div>
            <?php endif; ?>

            <!-- Settings & Logout -->
            <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                <a href="<?php echo BASE_PATH; ?>/views/settings.php" 
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-white hover:bg-opacity-20 transition group <?php echo (strpos($_SERVER['PHP_SELF'], 'settings.php') !== false) ? 'bg-white bg-opacity-20 font-semibold' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
                
                <a href="<?php echo BASE_PATH; ?>/controllers/logout.php" 
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-red-500 hover:bg-opacity-90 transition group">
                    <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </div>
</aside>