<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireAuth();

session_start();
$user_id = $_SESSION['user_id'];
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$language = $_SESSION['language'];

$employee = Employee::getById($user_id);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    
    // Generate certificate number
    $cert_no = 'CERT' . date('Ymd') . rand(1000, 9999);
    
    $purpose = $_POST['purpose'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO certificate_requests 
        (certificate_no, employee_id, employee_name, position, division, date_of_hire, hiring_type, base_salary, purpose, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')");
    
    $employee_name = $employee['full_name_th'];
    $position = get_master('position_master', $employee['position_id']);
    $division = get_master('division_master', $employee['division_id']);
    $date_of_hire = $employee['date_of_hire'];
    $hiring_type = get_master('hiring_type_master', $employee['hiring_type_id']);
    $base_salary = 0.00; // Admin will fill this
    
    $stmt->bind_param("sssssssds", 
        $cert_no, $user_id, $employee_name, $position, $division, 
        $date_of_hire, $hiring_type, $base_salary, $purpose);
    
    if ($stmt->execute()) {
        $message = 'Certificate request submitted successfully! Certificate No: ' . $cert_no;
        $message_type = 'success';
    } else {
        $message = 'Failed to submit request';
        $message_type = 'error';
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Certificate - <?php echo __('app_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
</head>
<body class="<?php echo $theme_mode === 'dark' ? 'dark bg-gray-900' : 'bg-gray-50'; ?>">
    
    <div class="container mx-auto px-4 py-6 max-w-3xl">
        <div class="mb-6">
            <a href="<?php echo BASE_PATH; ?>/index.php" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
                ← Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold <?php echo $theme_mode === 'dark' ? 'text-white' : 'text-gray-800'; ?>">
                Request Certificate
            </h1>
            <p class="<?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                Request an employment certificate
            </p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                <p class="<?php echo $message_type === 'success' ? 'text-green-700' : 'text-red-700'; ?> font-medium">
                    <?php echo htmlspecialchars($message); ?>
                </p>
                <?php if ($message_type === 'success'): ?>
                    <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="text-green-700 underline text-sm mt-2 inline-block">
                        View my requests →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="<?php echo $theme_mode === 'dark' ? 'bg-gray-800' : 'bg-white'; ?> rounded-lg shadow-lg p-6">
            <form method="POST" action="" id="certForm">
                
                <!-- Employee Info (Read-only) -->
                <div class="<?php echo $theme_mode === 'dark' ? 'bg-gray-700' : 'bg-blue-50'; ?> rounded-lg p-4 mb-6">
                    <h3 class="font-semibold <?php echo $theme_mode === 'dark' ? 'text-blue-300' : 'text-blue-900'; ?> mb-3">
                        Employee Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Employee ID
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $theme_mode === 'dark' ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Full Name
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['full_name_th']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $theme_mode === 'dark' ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Position
                            </label>
                            <input type="text" value="<?php echo get_master('position_master', $employee['position_id']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $theme_mode === 'dark' ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Division
                            </label>
                            <input type="text" value="<?php echo get_master('division_master', $employee['division_id']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $theme_mode === 'dark' ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded">
                        </div>
                    </div>
                </div>

                <!-- Purpose -->
                <div class="mb-6">
                    <label for="purpose" class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Purpose of Certificate <span class="text-red-500">*</span>
                    </label>
                    <textarea id="purpose" name="purpose" required rows="4" 
                              placeholder="e.g., For bank loan application, visa application, etc."
                              class="w-full px-4 py-3 border <?php echo $theme_mode === 'dark' ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs <?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">
                        Minimum 20 characters
                    </p>
                </div>

                <!-- Certificate Preview -->
                <div class="mb-6 p-4 <?php echo $theme_mode === 'dark' ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-200'; ?> border rounded-lg">
                    <h4 class="font-semibold <?php echo $theme_mode === 'dark' ? 'text-gray-200' : 'text-gray-800'; ?> mb-2">
                        Certificate Preview
                    </h4>
                    <p class="text-sm <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-gray-600'; ?>">
                        This certificate will include:
                    </p>
                    <ul class="text-sm <?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-500'; ?> mt-2 list-disc list-inside">
                        <li>Your employment status</li>
                        <li>Position and department</li>
                        <li>Start date of employment</li>
                        <li>Company seal and signature</li>
                    </ul>
                </div>

                <!-- Important Notice -->
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Important Notice</p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                • Processing time: 3-5 business days<br>
                                • Certificate will be signed by authorized personnel<br>
                                • You will be notified when ready for pickup
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        Submit Request
                    </button>
                    <a href="<?php echo BASE_PATH; ?>/index.php" 
                       class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('certForm').addEventListener('submit', function(e) {
            const purpose = document.getElementById('purpose').value.trim();
            
            if (purpose.length < 20) {
                e.preventDefault();
                alert('Purpose must be at least 20 characters');
                return;
            }
            
            if (!confirm('Are you sure you want to submit this certificate request?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>