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
// Include header และ sidebar (จะดึง theme vars อัตโนมัติ)
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<!-- Main Content -->
<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6">

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

                                <button onclick="openResetPasswordModal()"
                                    class="block w-full px-4 py-3 bg-yellow-600 hover:bg-yellow-700 text-white text-center rounded-lg transition">
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                    Reset Password
                                </button>

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

<!-- เพิ่ม Modal สำหรับ Reset Password ก่อน closing </div> ของ page -->

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold <?php echo $text_class; ?>">Reset Password</h3>
                <button onclick="closeResetPasswordModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="resetPasswordForm" onsubmit="submitPasswordReset(event)">
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                        Employee ID
                    </label>
                    <input type="text" 
                        value="<?php echo htmlspecialchars($employee['employee_id']); ?>" 
                        readonly
                        class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg <?php echo $is_dark ? 'bg-gray-700 text-gray-300' : 'bg-gray-100 text-gray-700'; ?> cursor-not-allowed">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                        id="new_password" 
                        required 
                        minlength="6"
                        placeholder="Minimum 6 characters"
                        class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">
                        Password must be at least 6 characters long
                    </p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                        id="confirm_password" 
                        required 
                        minlength="6"
                        placeholder="Re-enter new password"
                        class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                </div>
                
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded mb-6">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Warning</p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                The employee will be logged out and must use this new password to login again.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                        class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg transition font-medium">
                        Reset Password
                    </button>
                    <button type="button" 
                        onclick="closeResetPasswordModal()" 
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- เพิ่ม JavaScript functions -->
<script>
function openResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.remove('hidden');
    document.getElementById('new_password').focus();
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
    document.getElementById('resetPasswordForm').reset();
}

function submitPasswordReset(event) {
    event.preventDefault();
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Validate passwords match
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    // Validate password length
    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters!');
        return;
    }
    
    // Confirm action
    if (!confirm('Are you sure you want to reset this employee\'s password?\n\nEmployee: <?php echo addslashes($employee['full_name_en']); ?>\nID: <?php echo $employee_id; ?>')) {
        return;
    }
    
    // Show loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
    
    // Send request
    fetch('<?php echo BASE_PATH; ?>/api/reset_employee_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            employee_id: '<?php echo $employee_id; ?>',
            new_password: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Password reset successfully!\n\nEmployee: ' + data.employee.name_en + '\nNew password has been set.');
            closeResetPasswordModal();
            location.reload();
        } else {
            alert('Failed to reset password:\n' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeResetPasswordModal();
    }
});
</script>

        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this employee?\n\nEmployee: <?php echo addslashes($employee['full_name_en']); ?>\nID: <?php echo $employee_id; ?>\n\nThis action cannot be undone!')) {
                window.location.href = '<?php echo BASE_PATH; ?>/api/employee_delete.php?id=<?php echo $employee_id; ?>';
            }
        }
    </script>
    </body>

    </html>