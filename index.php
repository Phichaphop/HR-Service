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
    header('Location: ' . BASE_PATH . '/views/admin/db_manager.php');
    exit();
}

// Require authentication
AuthController::requireAuth();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

?>
    <!-- Main Content -->
    <div class="lg:ml-64">

        <!-- Dashboard Content -->
        <main class="p-4 md:p-6">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-6 text-white">
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($display_name); ?>!</h1>
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
                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/request_leave.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">Request Leave</span>
                    </button>

                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/request_certificate.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">Certificate</span>
                    </button>

                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/request_idcard.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">ID Card</span>
                    </button>

                    <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/my_requests.php'" class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition">
                        <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="text-sm font-medium <?php echo $text_class; ?> text-center">View Requests</span>
                    </button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>