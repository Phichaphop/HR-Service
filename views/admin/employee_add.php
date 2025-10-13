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
    $data = [
        'employee_id' => $_POST['employee_id'] ?? '',
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
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? 'password123',
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
                        <input type="text" name="employee_id" required maxlength="6" 
                               placeholder="e.g., EMP001"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">Maximum 6 characters</p>
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