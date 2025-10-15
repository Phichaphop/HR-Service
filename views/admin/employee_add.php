<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin role only
AuthController::requireRole(['admin']);

$page_title = 'Add New Employee';

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = [
        'employee_id', 'prefix_id', 'full_name_th', 'full_name_en',
        'sex_id', 'birthday', 'nationality_id', 'education_level_id',
        'function_id', 'division_id', 'department_id', 'section_id',
        'operation_id', 'position_id', 'position_level_id',
        'labour_cost_id', 'hiring_type_id', 'customer_zone_id',
        'contribution_level_id', 'date_of_hire', 'username', 'password'
    ];
    
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $message = 'Please fill in all required fields';
        $message_type = 'error';
    } else {
        $data = [
            'employee_id' => trim($_POST['employee_id']),
            'prefix_id' => $_POST['prefix_id'],
            'full_name_th' => trim($_POST['full_name_th']),
            'full_name_en' => trim($_POST['full_name_en']),
            'sex_id' => $_POST['sex_id'],
            'birthday' => $_POST['birthday'],
            'nationality_id' => $_POST['nationality_id'],
            'education_level_id' => $_POST['education_level_id'],
            'phone_no' => trim($_POST['phone_no'] ?? ''),
            'address_village' => trim($_POST['address_village'] ?? ''),
            'address_subdistrict' => trim($_POST['address_subdistrict'] ?? ''),
            'address_district' => trim($_POST['address_district'] ?? ''),
            'address_province' => trim($_POST['address_province'] ?? ''),
            'function_id' => $_POST['function_id'],
            'division_id' => $_POST['division_id'],
            'department_id' => $_POST['department_id'],
            'section_id' => $_POST['section_id'],
            'operation_id' => $_POST['operation_id'],
            'position_id' => $_POST['position_id'],
            'position_level_id' => $_POST['position_level_id'],
            'labour_cost_id' => $_POST['labour_cost_id'],
            'hiring_type_id' => $_POST['hiring_type_id'],
            'customer_zone_id' => $_POST['customer_zone_id'],
            'contribution_level_id' => $_POST['contribution_level_id'],
            'date_of_hire' => $_POST['date_of_hire'],
            'status_id' => 1, // Active by default
            'username' => trim($_POST['username']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role_id' => $_POST['role_id'] ?? 3 // Default to employee role
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
}

// Get master data for dropdowns
$conn = getDbConnection();
$prefixes = $conn->query("SELECT * FROM prefix_master ORDER BY prefix_id")->fetch_all(MYSQLI_ASSOC);
$sexes = $conn->query("SELECT * FROM sex_master ORDER BY sex_id")->fetch_all(MYSQLI_ASSOC);
$nationalities = $conn->query("SELECT * FROM nationality_master ORDER BY nationality_id")->fetch_all(MYSQLI_ASSOC);
$education_levels = $conn->query("SELECT * FROM education_level_master ORDER BY education_id")->fetch_all(MYSQLI_ASSOC);
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
$roles = $conn->query("SELECT * FROM roles ORDER BY role_id")->fetch_all(MYSQLI_ASSOC);
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        
        <!-- Breadcrumb -->
        <div class="mb-6 animate-fade-in">
            <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Employees
            </a>
            <h1 class="text-3xl font-bold <?php echo $text_class; ?> mt-2">Add New Employee</h1>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900 border-l-4 border-red-500'; ?>">
                <p class="<?php echo $message_type === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'; ?> font-medium">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="addEmployeeForm" class="space-y-6">
            
            <!-- Basic Information -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Basic Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Employee ID -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Employee ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="employee_id" required maxlength="8"
                               placeholder="e.g., EMP001"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">Max 8 characters</p>
                    </div>

                    <!-- Prefix -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Prefix <span class="text-red-500">*</span>
                        </label>
                        <select name="prefix_id" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select prefix</option>
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
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select name="sex_id" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select gender</option>
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
                               placeholder="ชื่อ-นามสกุล"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Full Name (English) -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Full Name (English) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="full_name_en" required
                               placeholder="First Last"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Birthday -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Birthday <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="birthday" required
                               max="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Nationality <span class="text-red-500">*</span>
                        </label>
                        <select name="nationality_id" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select nationality</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select education</option>
                            <?php foreach ($education_levels as $edu): ?>
                                <option value="<?php echo $edu['education_id']; ?>">
                                    <?php echo get_master('education_level_master', $edu['education_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Phone Number
                        </label>
                        <input type="tel" name="phone_no"
                               placeholder="081-234-5678"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Address</label>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input type="text" name="address_village" placeholder="Village"
                                   class="px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <input type="text" name="address_subdistrict" placeholder="Subdistrict"
                                   class="px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <input type="text" name="address_district" placeholder="District"
                                   class="px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <input type="text" name="address_province" placeholder="Province"
                                   class="px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Employment Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Function -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Function <span class="text-red-500">*</span>
                        </label>
                        <select name="function_id" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select function</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select division</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select department</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select section</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select operation</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select position</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select level</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select type</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select type</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select zone</option>
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
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select level</option>
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
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012
                        2v4a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6m3-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v2m6 0h-2M9 16v-2m6 2v-2"></path>
                    </svg>
                    Account Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" required
                               placeholder="username"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required minlength="6"
                               placeholder="Min 6 characters"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role_id" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_id']; ?>" <?php echo $role['role_id'] == 3 ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name_en'] ?? $role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Important</p>
                        <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                            Please ensure all information is accurate. Employee ID must be unique.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col md:flex-row gap-4">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Employee
                </button>
                <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" 
                   class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
        const employeeId = document.querySelector('input[name="employee_id"]').value;
        const username = document.querySelector('input[name="username"]').value;
        
        if (!confirm(`Are you sure you want to add this employee?\n\nEmployee ID: ${employeeId}\nUsername: ${username}`)) {
            e.preventDefault();
        }
    });
</script>