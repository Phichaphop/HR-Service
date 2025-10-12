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
    
    $reason = $_POST['reason'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO id_card_requests (employee_id, reason, status) VALUES (?, ?, 'New')");
    $stmt->bind_param("ss", $user_id, $reason);
    
    if ($stmt->execute()) {
        $message = 'ID Card request submitted successfully!';
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
    <title>Request ID Card - <?php echo __('app_title'); ?></title>
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
                Request ID Card
            </h1>
            <p class="<?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                Request new or replacement ID card
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
            <form method="POST" action="" id="idcardForm">
                
                <!-- Employee Info -->
                <div class="<?php echo $theme_mode === 'dark' ? 'bg-gray-700' : 'bg-purple-50'; ?> rounded-lg p-4 mb-6">
                    <h3 class="font-semibold <?php echo $theme_mode === 'dark' ? 'text-purple-300' : 'text-purple-900'; ?> mb-3">
                        Employee Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-purple-800'; ?> mb-1">
                                Employee ID
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $theme_mode === 'dark' ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-purple-200 text-gray-700'; ?> border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-purple-800'; ?> mb-1">
                                Full Name
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['full_name_th']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $theme_mode === 'dark' ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-purple-200 text-gray-700'; ?> border rounded">
                        </div>
                    </div>
                </div>

                <!-- Reason Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-300' : 'text-gray-700'; ?> mb-3">
                        Reason for Request <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 <?php echo $theme_mode === 'dark' ? 'border-gray-600 hover:border-purple-500' : 'border-gray-200 hover:border-purple-500'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="Lost ID Card" required
                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3">
                                <span class="font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-200' : 'text-gray-700'; ?>">
                                    Lost ID Card
                                </span>
                                <p class="text-xs <?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    I have lost my ID card and need a replacement
                                </p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 <?php echo $theme_mode === 'dark' ? 'border-gray-600 hover:border-purple-500' : 'border-gray-200 hover:border-purple-500'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="Damaged ID Card" required
                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3">
                                <span class="font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-200' : 'text-gray-700'; ?>">
                                    Damaged ID Card
                                </span>
                                <p class="text-xs <?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    My ID card is damaged and needs replacement
                                </p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 <?php echo $theme_mode === 'dark' ? 'border-gray-600 hover:border-purple-500' : 'border-gray-200 hover:border-purple-500'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="First Time Issue" required
                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3">
                                <span class="font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-200' : 'text-gray-700'; ?>">
                                    First Time Issue
                                </span>
                                <p class="text-xs <?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    I am a new employee requesting my first ID card
                                </p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 <?php echo $theme_mode === 'dark' ? 'border-gray-600 hover:border-purple-500' : 'border-gray-200 hover:border-purple-500'; ?> rounded-lg cursor-pointer transition">
                            <input type="radio" name="reason" value="Information Update" required
                                   class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3">
                                <span class="font-medium <?php echo $theme_mode === 'dark' ? 'text-gray-200' : 'text-gray-700'; ?>">
                                    Information Update
                                </span>
                                <p class="text-xs <?php echo $theme_mode === 'dark' ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    My information has changed (position, photo, etc.)
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Important Notice</p>
                            <ul class="text-xs text-yellow-700 dark:text-yellow-400 mt-1 list-disc list-inside">
                                <li>Processing time: 5-7 business days</li>
                                <li>You may need to provide a photo for new cards</li>
                                <li>Lost card may incur a replacement fee</li>
                                <li>Return old damaged card when collecting new one</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4">
                    <button type="submit" 
                            class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-3 px-6 rounded-lg font-medium transition">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
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
        document.getElementById('idcardForm').addEventListener('submit', function(e) {
            const reason = document.querySelector('input[name="reason"]:checked');
            
            if (!reason) {
                e.preventDefault();
                alert('Please select a reason for your request');
                return;
            }
            
            if (!confirm('Are you sure you want to submit this ID card request?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>