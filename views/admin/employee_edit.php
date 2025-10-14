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

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
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
    ];
    
    $result = Employee::update($employee_id, $data);
    
    if ($result['success']) {
        header('Location: ' . BASE_PATH . '/views/admin/employee_detail.php?id=' . $employee_id . '&success=1&message=' . urlencode('Employee updated successfully'));
        exit();
    } else {
        $message = $result['message'];
        $message_type = 'error';
        // Reload employee data
        $employee = Employee::getById($employee_id);
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
$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - <?php echo htmlspecialchars($employee['employee_id']); ?></title>
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
    </style>
</head>
<body class="<?php echo $bg_class; ?> theme-transition">
    
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo BASE_PATH; ?>/views/admin/employee_detail.php?id=<?php echo $employee_id; ?>" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm mb-3 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Employee Details
            </a>
            <h1 class="text-3xl font-bold <?php echo $text_class; ?>">Edit Employee</h1>
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                Employee ID: <?php echo htmlspecialchars($employee['employee_id']); ?>
            </p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900 border-l-4 border-red-500'; ?>">
                <p class="<?php echo $message_type === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'; ?> font-medium">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="editForm" class="space-y-6">
            
            <!-- Personal Information -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">Personal Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Prefix -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Prefix <span class="text-red-500">*</span>
                        </label>
                        <select name="prefix_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($prefixes as $prefix): ?>
                                <option value="<?php echo $prefix['prefix_id']; ?>" <?php echo $employee['prefix_id'] == $prefix['prefix_id'] ? 'selected' : ''; ?>>
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
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($sexes as $sex): ?>
                                <option value="<?php echo $sex['sex_id']; ?>" <?php echo $employee['sex_id'] == $sex['sex_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('sex_master', $sex['sex_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Birthday -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Birthday <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="birthday" required 
                               value="<?php echo $employee['birthday']; ?>"
                               max="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                    </div>

                    <!-- Full Name (Thai) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Full Name (Thai) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="full_name_th" required 
                               value="<?php echo htmlspecialchars($employee['full_name_th']); ?>"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                    </div>

                    <!-- Full Name (English) -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Full Name (English) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="full_name_en" required 
                               value="<?php echo htmlspecialchars($employee['full_name_en']); ?>"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Nationality <span class="text-red-500">*</span>
                        </label>
                        <select name="nationality_id" required 
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($nationalities as $nationality): ?>
                                <option value="<?php echo $nationality['nationality_id']; ?>" <?php echo $employee['nationality_id'] == $nationality['nationality_id'] ? 'selected' : ''; ?>>
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
                                class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($education_levels as $edu): ?>
                                <option value="<?php echo $edu['education_id']; ?>" <?php echo $employee['education_level_id'] == $edu['education_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('education_level_master', $edu['education_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone_no" required 
                               value="<?php echo htmlspecialchars($employee['phone_no']); ?>"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Address</label>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input type="text" name="address_village" placeholder="Village" 
                                   value="<?php echo htmlspecialchars($employee['address_village'] ?? ''); ?>"
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <input type="text" name="address_subdistrict" placeholder="Subdistrict" 
                                   value="<?php echo htmlspecialchars($employee['address_subdistrict'] ?? ''); ?>"
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <input type="text" name="address_district" placeholder="District" 
                                   value="<?php echo htmlspecialchars($employee['address_district'] ?? ''); ?>"
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <input type="text" name="address_province" placeholder="Province" 
                                   value="<?php echo htmlspecialchars($employee['address_province'] ?? ''); ?>"
                                   class="px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">Employment Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Function -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Function <span class="text-red-500">*</span></label>
                        <select name="function_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($functions as $func): ?>
                                <option value="<?php echo $func['function_id']; ?>" <?php echo $employee['function_id'] == $func['function_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('function_master', $func['function_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Division -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Division <span class="text-red-500">*</span></label>
                        <select name="division_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?php echo $div['division_id']; ?>" <?php echo $employee['division_id'] == $div['division_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('division_master', $div['division_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Department <span class="text-red-500">*</span></label>
                        <select name="department_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>" <?php echo $employee['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('department_master', $dept['department_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Section, Operation, Position -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Section <span class="text-red-500">*</span></label>
                        <select name="section_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section['section_id']; ?>" <?php echo $employee['section_id'] == $section['section_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('section_master', $section['section_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Operation <span class="text-red-500">*</span></label>
                        <select name="operation_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($operations as $op): ?>
                                <option value="<?php echo $op['operation_id']; ?>" <?php echo $employee['operation_id'] == $op['operation_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('operation_master', $op['operation_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Position <span class="text-red-500">*</span></label>
                        <select name="position_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['position_id']; ?>" <?php echo $employee['position_id'] == $pos['position_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('position_master', $pos['position_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Position Level <span class="text-red-500">*</span></label>
                        <select name="position_level_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($position_levels as $level): ?>
                                <option value="<?php echo $level['level_id']; ?>" <?php echo $employee['position_level_id'] == $level['level_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('position_level_master', $level['level_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Labour Cost <span class="text-red-500">*</span></label>
                        <select name="labour_cost_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($labour_costs as $lc): ?>
                                <option value="<?php echo $lc['labour_cost_id']; ?>" <?php echo $employee['labour_cost_id'] == $lc['labour_cost_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('labour_cost_master', $lc['labour_cost_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Hiring Type <span class="text-red-500">*</span></label>
                        <select name="hiring_type_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($hiring_types as $ht): ?>
                                <option value="<?php echo $ht['hiring_type_id']; ?>" <?php echo $employee['hiring_type_id'] == $ht['hiring_type_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('hiring_type_master', $ht['hiring_type_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Customer Zone <span class="text-red-500">*</span></label>
                        <select name="customer_zone_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($customer_zones as $zone): ?>
                                <option value="<?php echo $zone['zone_id']; ?>" <?php echo $employee['customer_zone_id'] == $zone['zone_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('customer_zone_master', $zone['zone_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Contribution Level <span class="text-red-500">*</span></label>
                        <select name="contribution_level_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($contribution_levels as $cl): ?>
                                <option value="<?php echo $cl['contribution_id']; ?>" <?php echo $employee['contribution_level_id'] == $cl['contribution_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('contribution_level_master', $cl['contribution_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Date of Hire <span class="text-red-500">*</span></label>
                        <input type="date" name="date_of_hire" required 
                               value="<?php echo $employee['date_of_hire']; ?>"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status_id" required class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?>">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['status_id']; ?>" <?php echo $employee['status_id'] == $status['status_id'] ? 'selected' : ''; ?>>
                                    <?php echo get_master('status_master', $status['status_id']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col md:flex-row gap-4">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Employee
                </button>
                <a href="<?php echo BASE_PATH; ?>/views/admin/employee_detail.php?id=<?php echo $employee_id; ?>" 
                   class="flex-1 px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('editForm').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to update this employee information?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>