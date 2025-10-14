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
    $employee_id = trim($_POST['employee_id'] ?? '');
    
    // Validate Employee ID (8 digits)
    if (!preg_match('/^\d{8}$/', $employee_id)) {
        $message = 'Employee ID must be exactly 8 digits';
        $message_type = 'error';
    } else {
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
            'username' => $employee_id, // Username = Employee ID
            'password' => $employee_id, // Default password = Employee ID
            'role_id' => $_POST['role_id'] ?? 3
        ];
        
        $result = Employee::create($data);
        
        if ($result['success']) {
            header('Location: ' . BASE_PATH . '/views/admin/employees.php?success=1&message=' . urlencode('Employee added successfully. Default login: ' . $employee_id));
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

$page_title = 'Add New Employee';
$extra_head = '<style>
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-in { animation: slideIn 0.3s ease-out; }
    .step-indicator { transition: all 0.3s ease; }
    .step-indicator.active { background-color: #3B82F6; color: white; }
    .step-indicator.completed { background-color: #10B981; color: white; }
</style>';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="p-4 md:p-6">
    <div class="container mx-auto max-w-6xl">
        
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
                    <span class="<?php echo $text_class; ?> font-medium hidden sm:inline">Basic Info</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 dark:bg-gray-600 mx-4"></div>
                <div class="flex items-center space-x-2">
                    <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold <?php echo $is_dark ? 'bg-gray-700 text-gray-400' : 'bg-gray-200 text-gray-600'; ?>">2</div>
                    <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> font-medium hidden sm:inline">Position</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 dark:bg-gray-600 mx-4"></div>
                <div class="flex items-center space-x-2">
                    <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center font-bold <?php echo $is_dark ? 'bg-gray-700 text-gray-400' : 'bg-gray-200 text-gray-600'; ?>">3</div>
                    <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> font-medium hidden sm:inline">Account</span>
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
                               oninput="this.value = this.value.replace(/\D/g, '').slice(0, 8)"
                               class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">Username and password are set as Employee ID (8 digits)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<!-- JavaScript -->
<script>
let currentStep = 1;

// Step Navigation
function nextStep(step) {
    if (!validateStep(currentStep)) {
        return;
    }
    document.getElementById(`step${currentStep}`).classList.add('hidden');
    document.getElementById(`step${step}`).classList.remove('hidden');
    updateStepIndicators(step);
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep(step) {
    document.getElementById(`step${currentStep}`).classList.add('hidden');
    document.getElementById(`step${step}`).classList.remove('hidden');
    updateStepIndicators(step);
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepIndicators(activeStep) {
    const indicators = document.querySelectorAll('.step-indicator');
    for (let i = 1; i <= 3; i++) {
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

// Toggle Password Visibility
function togglePasswordVisibility() {
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

// Update Credentials from Employee ID
function updateCredentials() {
    const employeeId = document.getElementById('employee_id').value;
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    const previewCard = document.getElementById('credentialsPreview');
    const previewUsername = document.getElementById('previewUsername');
    const previewPassword = document.getElementById('previewPassword');
    
    if (employeeId && employeeId.length === 8 && /^\d{8}$/.test(employeeId)) {
        usernameField.value = employeeId;
        passwordField.value = employeeId;
        
        previewCard.classList.remove('hidden');
        previewUsername.textContent = employeeId;
        previewPassword.textContent = employeeId;
        
        usernameField.classList.remove('border-gray-300', 'dark:border-gray-600');
        usernameField.classList.add('border-green-500');
        passwordField.classList.remove('border-gray-300', 'dark:border-gray-600');
        passwordField.classList.add('border-green-500');
        
        showToast('✅ Login credentials auto-generated successfully!', 'success');
    } else {
        usernameField.value = '';
        passwordField.value = '';
        previewCard.classList.add('hidden');
        
        usernameField.classList.remove('border-green-500');
        usernameField.classList.add('border-gray-300', 'dark:border-gray-600');
        passwordField.classList.remove('border-green-500');
        passwordField.classList.add('border-gray-300', 'dark:border-gray-600');
    }
}

// Debounce for better performance
let updateTimeout;
document.getElementById('employee_id').addEventListener('input', function() {
    clearTimeout(updateTimeout);
    updateTimeout = setTimeout(updateCredentials, 500);
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

// Form submission with loading state
document.getElementById('employeeForm').addEventListener('submit', function(e) {
    if (!validateStep(3)) {
        e.preventDefault();
        return;
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="w-5 h-5 inline-block animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Creating Employee...';
});

// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 animate-slide-in ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('employee_id').value) {
        updateCredentials();
    }
});

// Mobile Menu Toggle
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileMenuOverlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// Change Language
function changeLanguage(lang) {
    const selectElement = event.target;
    selectElement.disabled = true;
    
    fetch('<?php echo BASE_PATH; ?>/api/change_language.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language: lang })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to change language');
            selectElement.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error');
        selectElement.disabled = false;
    });
}

// Toggle Theme
function toggleTheme() {
    const currentMode = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    const newMode = currentMode === 'dark' ? 'light' : 'dark';
    
    const themeButton = event.currentTarget;
    themeButton.disabled = true;
    themeButton.style.opacity = '0.5';
    
    fetch('<?php echo BASE_PATH; ?>/api/change_theme.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: newMode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to change theme');
            themeButton.disabled = false;
            themeButton.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error');
        themeButton.disabled = false;
        themeButton.style.opacity = '1';
    });
}
</script>
="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">8 digits only (e.g., 90681322)</p>
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
                    
                    <!-- Username (Auto-filled from Employee ID) -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                readonly
                                placeholder="Auto-filled from Employee ID"
                                class="w-full pl-10 pr-4 py-3 border <?php echo $border_class; ?> rounded-lg bg-gray-50 dark:bg-gray-900 cursor-not-allowed <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> theme-transition"
                            >
                        </div>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Auto-filled from Employee ID (8 digits)
                        </p>
                    </div>

                    <!-- Password (Auto-filled from Employee ID) -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Password (Default) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                readonly
                                placeholder="Auto-filled from Employee ID"
                                class="w-full pl-10 pr-12 py-3 border <?php echo $border_class; ?> rounded-lg bg-gray-50 dark:bg-gray-900 cursor-not-allowed <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> theme-transition"
                            >
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition"
                                title="Show/Hide Password"
                            >
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Same as Employee ID (must be changed after first login)
                        </p>
                    </div>

                    <!-- Role Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-3">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php foreach ($roles as $role): ?>
                                <label class="flex items-start p-4 border-2 <?php echo $border_class; ?> rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                    <input 
                                        type="radio" 
                                        name="role_id" 
                                        value="<?php echo $role['role_id']; ?>" 
                                        <?php echo $role['role_id'] == 3 ? 'checked' : ''; ?>
                                        class="mt-1 w-4 h-4 text-blue-600 focus:ring-blue-500"
                                    >
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center">
                                            <?php 
                                            $role_icons = [
                                                1 => '<svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
                                                2 => '<svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>',
                                                3 => '<svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'
                                            ];
                                            echo $role_icons[$role['role_id']];
                                            ?>
                                            <span class="font-semibold <?php echo $text_class; ?>">
                                                <?php 
                                                $role_names = [1 => 'Administrator', 2 => 'Officer', 3 => 'Employee'];
                                                echo $role_names[$role['role_id']]; 
                                                ?>
                                            </span>
                                        </div>
                                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">
                                            <?php 
                                            $role_desc = [
                                                1 => 'Full system access with all permissions',
                                                2 => 'Can manage requests and view employees',
                                                3 => 'Can view and submit own requests only'
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

                <!-- Important Security Notice -->
                <div class="mt-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border-l-4 border-blue-500 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-blue-900 dark:text-blue-300 mb-2">
                                🔐 Security Information
                            </p>
                            <ul class="text-xs text-blue-800 dark:text-blue-400 space-y-1.5">
                                <li class="flex items-start">
                                    <span class="mr-2">✓</span>
                                    <span><strong>Username:</strong> Automatically set as Employee ID (8 digits)</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2">✓</span>
                                    <span><strong>Password:</strong> Initially same as Employee ID for first login</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2">⚠️</span>
                                    <span><strong>Important:</strong> Employee MUST change password after first login</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2">🔒</span>
                                    <span><strong>Security:</strong> Password is encrypted using bcrypt hashing</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Preview Credentials Card -->
                <div id="credentialsPreview" class="mt-6 p-5 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed <?php echo $border_class; ?> hidden">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold <?php echo $text_class; ?> flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Login Credentials Preview
                        </h4>
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 text-xs font-medium rounded-full">
                            Ready
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">Username:</p>
                            <p id="previewUsername" class="font-mono font-bold <?php echo $text_class; ?> text-lg">-</p>
                        </div>
                        <div>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">Password:</p>
                            <p id="previewPassword" class="font-mono font-bold <?php echo $text_class; ?> text-lg">-</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-8">
                    <button 
                        type="button" 
                        onclick="prevStep(2)" 
                        class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition shadow-md hover:shadow-lg flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Previous
                    </button>
                    
                    <button 
                        type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg font-bold transition shadow-lg hover:shadow-xl flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Create Employee Account
                    </button>
                </div>
            </div>

        </form>

        <!-- Quick Tips -->
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
                        <p class