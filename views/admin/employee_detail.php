<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer_payroll']);

// ====== THEME & LANGUAGE INITIALIZATION ======
$user_role = $_SESSION['role'];
$language = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');

// Centralized theme classes
$theme = [
    'card_bg'         => $is_dark ? 'bg-gray-800' : 'bg-white',
    'card_bg_alt'     => $is_dark ? 'bg-gray-700' : 'bg-gray-50',
    'text'            => $is_dark ? 'text-gray-100' : 'text-gray-800',
    'text_secondary'  => $is_dark ? 'text-gray-400' : 'text-gray-600',
    'text_muted'      => $is_dark ? 'text-gray-500' : 'text-gray-500',
    'border'          => $is_dark ? 'border-gray-700' : 'border-gray-200',
    'bg_page'         => $is_dark ? 'bg-gray-900' : 'bg-gray-50',
    'input_bg'        => $is_dark ? 'bg-gray-700 text-white' : 'bg-white text-gray-900',
    'input_border'    => $is_dark ? 'border-gray-600 focus:ring-blue-500' : 'border-gray-300 focus:ring-blue-500',
    'hover_bg'        => $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-100',
    'accent'          => $_SESSION['theme_color'] ?? '#3B82F6' // Default blue
];

// ====== GET EMPLOYEE DATA ======
$employee_id = $_GET['id'] ?? '';
if (empty($employee_id)) {
    header('Location: ' . BASE_PATH . '/views/admin/employees.php');
    exit();
}

$employee = Employee::getById($employee_id);
if (!$employee) {
    header('Location: ' . BASE_PATH . '/views/admin/employees.php?error=Employee not found');
    exit();
}

// ====== CALCULATE AGE & YEARS OF SERVICE ======
$age = 0;
if ($employee['birthday']) {
    $birthday = new DateTime($employee['birthday']);
    $now = new DateTime();
    $age = $now->diff($birthday)->y;
}

$years_service = 0;
if ($employee['date_of_hire']) {
    $hire_date = new DateTime($employee['date_of_hire']);
    $now = new DateTime();
    $years_service = $now->diff($hire_date)->y;
}

// ====== LOCALIZATION HELPER (USE EXISTING LOCALIZATION CLASS) ======
/**
 * Get localized label text
 * Uses existing Localization::getText() with fallback
 */
function get_label($key) {
    $text = Localization::getText($key);
    // If returns same key, format it nicely
    return $text === $key ? ucwords(str_replace('_', ' ', $key)) : $text;
}

/**
 * Get localized text (wrapper for Localization::getText)
 */
function get_text($key) {
    return Localization::getText($key);
}

// Include header และ sidebar
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<!-- Main Content -->
<div class="lg:ml-64">
    <div class="<?php echo $theme['bg_page']; ?> min-h-screen py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            
            <!-- ====== BREADCRUMB ====== -->
            <div class="mb-6 animate-fade-in no-print">
                <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <?php echo get_label('back_to_employees'); ?>
                </a>
            </div>

            <!-- ====== PROFILE HEADER CARD ====== -->
            <div class="<?php echo $theme['card_bg']; ?> rounded-lg shadow-lg p-6 mb-6 animate-fade-in border <?php echo $theme['border']; ?> transition-all duration-200">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">

                    <?php 
                    
                    $profile_pic = "../../" . $employee['profile_pic_path'];

                    ?>

                    <!-- Profile Picture -->
                    <div class="flex-shrink-0">
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg overflow-hidden">
                                <?php if ($profile_pic && file_exists($profile_pic)): ?>
                                    <img src="<?php echo htmlspecialchars($profile_pic); ?>"
                                        alt="Profile" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span><?php echo strtoupper(substr($employee['full_name_' . $language], 0, 1)); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($user_role === 'admin'): ?>
                                <button onclick="document.getElementById('quickPhotoUpload').click()"
                                    class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full shadow-lg transition-colors"
                                    title="<?php echo get_label('change_photo'); ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Employee Info -->
                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-bold <?php echo $theme['text']; ?> mb-2">
                                    <?php echo htmlspecialchars($employee['full_name_' . $language]); ?>
                                </h1>
                                <p class="text-lg <?php echo $theme['text_secondary']; ?> mb-4">
                                    <?php echo get_master('position_master', $employee['position_id'], $language); ?>
                                </p>
                            </div>
                            
                            <!-- Status Badge -->
                            <?php
                            $status_colors = [
                                1 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                2 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                3 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            ];
                            $status_color = $status_colors[$employee['status_id']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-4 py-2 rounded-full text-sm font-semibold <?php echo $status_color; ?> whitespace-nowrap">
                                <?php echo get_master('status_master', $employee['status_id'], $language); ?>
                            </span>
                        </div>

                        <!-- Quick Info Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                            <div class="flex items-center p-3 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"></path>
                                </svg>
                                <div>
                                    <p class="text-xs <?php echo $theme['text_muted']; ?>"><?php echo get_label('employee_id'); ?></p>
                                    <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo htmlspecialchars($employee['employee_id']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-3 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs <?php echo $theme['text_muted']; ?>"><?php echo get_label('phone'); ?></p>
                                    <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo htmlspecialchars($employee['phone_no'] ?? '-'); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center p-3 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs <?php echo $theme['text_muted']; ?>"><?php echo get_label('years_of_service'); ?></p>
                                    <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo $years_service; ?> years</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== MAIN CONTENT GRID ====== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Column - Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- ====== PERSONAL INFORMATION ====== -->
                    <div class="<?php echo $theme['card_bg']; ?> rounded-lg shadow-lg p-6 animate-fade-in border <?php echo $theme['border']; ?> transition-all">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold <?php echo $theme['text']; ?> flex items-center">
                                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <?php echo get_label('personal_information'); ?>
                            </h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Full Name TH -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('full_name_th'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo htmlspecialchars($employee['full_name_th']); ?></p>
                            </div>
                            
                            <!-- Full Name EN -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('full_name_en'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo htmlspecialchars($employee['full_name_en']); ?></p>
                            </div>
                            
                            <!-- Prefix -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('prefix'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('prefix_master', $employee['prefix_id'], $language); ?></p>
                            </div>
                            
                            <!-- Sex -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('sex'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('sex_master', $employee['sex_id'], $language); ?></p>
                            </div>
                            
                            <!-- Birthday & Age -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('birthday'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>">
                                    <?php echo $employee['birthday'] ? date('M d, Y', strtotime($employee['birthday'])) : '-'; ?>
                                    <?php if ($age > 0): ?>
                                        <span class="text-sm <?php echo $theme['text_muted']; ?> ml-1">(<?php echo $age; ?> yo)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <!-- Nationality -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('nationality'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('nationality_master', $employee['nationality_id'], $language); ?></p>
                            </div>
                            
                            <!-- Education Level (Full Width) -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg md:col-span-2">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('education_level'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('education_level_master', $employee['education_level_id'], $language); ?></p>
                            </div>
                            
                            <!-- Address (Full Width) -->
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg md:col-span-2">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('address'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>">
                                    <?php
                                    $address_parts = array_filter([
                                        $employee['address_village'] ?? '',
                                        $employee['address_subdistrict'] ?? '',
                                        $employee['address_district'] ?? '',
                                        $employee['address_province'] ?? ''
                                    ]);
                                    echo !empty($address_parts) ? implode(', ', $address_parts) : '-';
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ====== EMPLOYMENT INFORMATION ====== -->
                    <div class="<?php echo $theme['card_bg']; ?> rounded-lg shadow-lg p-6 border <?php echo $theme['border']; ?> transition-all">
                        <h2 class="text-xl font-bold <?php echo $theme['text']; ?> mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <?php echo get_label('employment_details'); ?>
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('function'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('function_master', $employee['function_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('division'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('division_master', $employee['division_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('department'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('department_master', $employee['department_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('section'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('section_master', $employee['section_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('operation'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('operation_master', $employee['operation_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('position_level'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('position_level_master', $employee['position_level_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('hiring_type'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('hiring_type_master', $employee['hiring_type_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('labour_cost'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('labour_cost_master', $employee['labour_cost_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('date_of_hire'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>">
                                    <?php echo $employee['date_of_hire'] ? date('M d, Y', strtotime($employee['date_of_hire'])) : '-'; ?>
                                </p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('customer_zone'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('customer_zone_master', $employee['customer_zone_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('contribution_level'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('contribution_level_master', $employee['contribution_level_id'], $language); ?></p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column - Sidebars -->
                <div class="space-y-6">
                    
                    <!-- ====== QUICK STATS ====== -->
                    <div class="<?php echo $theme['card_bg']; ?> rounded-lg shadow-lg p-6 border <?php echo $theme['border']; ?> transition-all">
                        <h3 class="text-lg font-bold <?php echo $theme['text']; ?> mb-4"><?php echo get_label('quick_stats'); ?></h3>
                        <div class="space-y-3">
                            <!-- Age -->
                            <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold"><?php echo get_label('age'); ?></p>
                                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300"><?php echo $age; ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Years of Service -->
                            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs text-green-600 dark:text-green-400 font-semibold"><?php echo get_label('years_of_service'); ?></p>
                                        <p class="text-2xl font-bold text-green-700 dark:text-green-300"><?php echo $years_service; ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Contribution Level -->
                            <div class="flex items-center justify-between p-4 bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs text-purple-600 dark:text-purple-400 font-semibold"><?php echo get_label('contribution'); ?></p>
                                        <p class="text-sm font-bold text-purple-700 dark:text-purple-300"><?php echo get_master('contribution_level_master', $employee['contribution_level_id'], $language); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ====== ACCOUNT INFORMATION ====== -->
                    <div class="<?php echo $theme['card_bg']; ?> rounded-lg shadow-lg p-6 border <?php echo $theme['border']; ?> transition-all">
                        <h3 class="text-lg font-bold <?php echo $theme['text']; ?> mb-4"><?php echo get_label('account_info'); ?></h3>
                        <div class="space-y-3">
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('username'); ?></p>
                                <p class="font-mono font-semibold <?php echo $theme['text']; ?>"><?php echo htmlspecialchars($employee['username']); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('role'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>"><?php echo get_master('roles', $employee['role_id'], $language); ?></p>
                            </div>
                            <div class="p-4 <?php echo $theme['card_bg_alt']; ?> rounded-lg">
                                <p class="text-xs <?php echo $theme['text_muted']; ?> mb-1 font-semibold"><?php echo get_label('last_updated'); ?></p>
                                <p class="font-semibold <?php echo $theme['text']; ?>">
                                    <?php echo $employee['updated_at'] ? date('M d, Y H:i', strtotime($employee['updated_at'])) : '-'; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ====== ADMIN ACTIONS (Show only for admin) ====== -->
                    <?php if ($user_role === 'admin'): ?>
                        <div class="<?php echo $theme['card_bg']; ?> rounded-lg shadow-lg p-6 border <?php echo $theme['border']; ?> transition-all no-print">
                            <h3 class="text-lg font-bold <?php echo $theme['text']; ?> mb-4"><?php echo get_label('actions'); ?></h3>
                            <div class="space-y-3">
                                <a href="<?php echo BASE_PATH; ?>/views/admin/employee_edit.php?id=<?php echo $employee_id; ?>"
                                    class="block w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition-colors font-medium flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <?php echo get_label('edit_employee'); ?>
                                </a>
                                <button onclick="openResetPasswordModal()"
                                    class="block w-full px-4 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors font-medium flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                    <?php echo get_label('reset_password'); ?>
                                </button>
                                <button onclick="openDeleteConfirmModal()"
                                    class="block w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <?php echo get_label('delete_employee'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- ====== HIDDEN FILE INPUT FOR QUICK PHOTO UPLOAD ====== -->
<input type="file" 
    id="quickPhotoUpload" 
    class="hidden" 
    accept="image/jpeg,image/png,image/gif"
    onchange="handleQuickPhotoUpload(event)">

<!-- ====== TOAST NOTIFICATION ====== -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

<!-- ====== MODALS ====== -->

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $theme['card_bg']; ?> rounded-xl shadow-2xl max-w-md w-full border <?php echo $theme['border']; ?>">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold <?php echo $theme['text']; ?>"><?php echo get_label('reset_password'); ?></h3>
                <button onclick="closeResetPasswordModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="resetPasswordForm" onsubmit="submitPasswordReset(event)">
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $theme['text']; ?> mb-2">
                        <?php echo get_label('employee_id'); ?>
                    </label>
                    <input type="text" 
                        value="<?php echo htmlspecialchars($employee['employee_id']); ?>" 
                        readonly
                        class="w-full px-4 py-2 border <?php echo $theme['border']; ?> rounded-lg <?php echo $is_dark ? 'bg-gray-700 text-gray-300 cursor-not-allowed' : 'bg-gray-100 text-gray-700 cursor-not-allowed'; ?>">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $theme['text']; ?> mb-2">
                        <?php echo get_label('new_password'); ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                        id="new_password" 
                        required 
                        minlength="6"
                        placeholder="<?php echo get_label('minimum_6_characters'); ?>"
                        class="w-full px-4 py-2 border <?php echo $theme['input_border']; ?> rounded-lg <?php echo $theme['input_bg']; ?> focus:ring-2">
                    <p class="text-xs <?php echo $theme['text_muted']; ?> mt-1">
                        💡 <?php echo get_label('password_requirement'); ?>
                    </p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $theme['text']; ?> mb-2">
                        <?php echo get_label('confirm_password'); ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                        id="confirm_password" 
                        required 
                        minlength="6"
                        placeholder="<?php echo get_label('re_enter_password'); ?>"
                        class="w-full px-4 py-2 border <?php echo $theme['input_border']; ?> rounded-lg <?php echo $theme['input_bg']; ?> focus:ring-2">
                </div>
                
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 rounded mb-6">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300"><?php echo get_label('warning'); ?></p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                <?php echo get_label('employee_will_be_logged_out'); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                        class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="resetBtnText"><?php echo get_label('reset_password'); ?></span>
                    </button>
                    <button type="button" 
                        onclick="closeResetPasswordModal()" 
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition-colors font-medium">
                        <?php echo get_label('cancel'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $theme['card_bg']; ?> rounded-xl shadow-2xl max-w-md w-full border border-red-500">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <h3 class="text-lg font-bold <?php echo $theme['text']; ?> text-center mb-2">
                <?php echo get_label('delete_employee'); ?>?
            </h3>
            
            <p class="<?php echo $theme['text_secondary']; ?> text-center mb-6 text-sm">
                <?php echo get_label('delete_confirmation_message'); ?><br>
                <span class="font-semibold"><?php echo htmlspecialchars($employee['full_name_' . $language]); ?></span><br>
                <span class="text-xs"><?php echo get_label('action_cannot_be_undone'); ?></span>
            </p>
            
            <div class="flex space-x-3">
                <button onclick="confirmDelete()"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg transition-colors font-medium">
                    <span id="deleteBtnText"><?php echo get_label('delete'); ?></span>
                </button>
                <button onclick="closeDeleteConfirmModal()"
                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors font-medium">
                    <?php echo get_label('cancel'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ====== SCRIPTS ====== -->
<script>
// Toast Notification System
function showToast(message, type = 'info', duration = 3000) {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    const bgColor = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
    }[type] || 'bg-blue-500';
    
    toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg animate-slide-in-up flex items-center gap-2`;
    toast.innerHTML = `
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16z" clip-rule="evenodd"/>
        </svg>
        <span>${message}</span>
    `;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('animate-slide-out-down');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Reset Password Modal Functions
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
    
    if (newPassword !== confirmPassword) {
        showToast('<?php echo get_label('passwords_do_not_match'); ?>', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showToast('<?php echo get_label('password_too_short'); ?>', 'error');
        return;
    }
    
    const btn = event.target.querySelector('button[type="submit"]');
    const btnText = document.getElementById('resetBtnText');
    btn.disabled = true;
    btnText.textContent = '<?php echo get_label('processing'); ?>...';
    
    fetch('<?php echo BASE_PATH; ?>/api/reset_employee_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            employee_id: '<?php echo $employee_id; ?>',
            new_password: newPassword,
            csrf_token: getCsrfToken()
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✓ <?php echo get_label('password_reset_success'); ?>', 'success');
            closeResetPasswordModal();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || '<?php echo get_label('reset_failed'); ?>', 'error');
        }
    })
    .catch(e => showToast('<?php echo get_label('network_error'); ?>', 'error'))
    .finally(() => {
        btn.disabled = false;
        btnText.textContent = '<?php echo get_label('reset_password'); ?>';
    });
}

// Delete Confirmation Modal Functions
function openDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
}

function confirmDelete() {
    const btn = event.target;
    const btnText = document.getElementById('deleteBtnText');
    btn.disabled = true;
    btnText.textContent = '<?php echo get_label('deleting'); ?>...';
    
    fetch('<?php echo BASE_PATH; ?>/api/employee_delete.php?id=<?php echo $employee_id; ?>&csrf_token=' + getCsrfToken(), {
        method: 'DELETE'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✓ <?php echo get_label('employee_deleted'); ?>', 'success');
            setTimeout(() => {
                window.location.href = '<?php echo BASE_PATH; ?>/views/admin/employees.php';
            }, 800);
        } else {
            showToast(data.message || '<?php echo get_label('delete_failed'); ?>', 'error');
            btn.disabled = false;
            btnText.textContent = '<?php echo get_label('delete'); ?>';
        }
    })
    .catch(e => {
        showToast('<?php echo get_label('network_error'); ?>', 'error');
        btn.disabled = false;
        btnText.textContent = '<?php echo get_label('delete'); ?>';
    });
}

// Quick Photo Upload
function handleQuickPhotoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        showToast('<?php echo get_label('file_too_large'); ?>', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('photo', file);
    formData.append('employee_id', '<?php echo $employee_id; ?>');
    formData.append('csrf_token', getCsrfToken());
    
    showToast('<?php echo get_label('uploading_photo'); ?>', 'info');
    
    fetch('<?php echo BASE_PATH; ?>/api/upload_employee_photo.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✓ <?php echo get_label('photo_uploaded'); ?>', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || '<?php echo get_label('upload_failed'); ?>', 'error');
        }
    })
    .catch(e => showToast('<?php echo get_label('upload_failed'); ?>', 'error'));
    
    document.getElementById('quickPhotoUpload').value = '';
}

// CSRF Token Helper
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// Close modals on ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeResetPasswordModal();
        closeDeleteConfirmModal();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>