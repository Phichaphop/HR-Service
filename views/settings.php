<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../db/Localization.php';

AuthController::requireAuth();

// Set page title before including header
$page_title = 'Settings';

// Get employee data (session already started in header.php)
ensure_session_started();
$user_id = $_SESSION['user_id'];
$employee = Employee::getById($user_id);

$message = '';
$message_type = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password !== $confirm_password) {
            $message = 'New passwords do not match';
            $message_type = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = 'Password must be at least 6 characters';
            $message_type = 'error';
        } else {
            if (password_verify($current_password, $employee['password'])) {
                $conn = getDbConnection();
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("UPDATE employees SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE employee_id = ?");
                $stmt->bind_param("ss", $new_hash, $user_id);

                if ($stmt->execute()) {
                    $message = 'Password changed successfully';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to change password';
                    $message_type = 'error';
                }

                $stmt->close();
                $conn->close();
            } else {
                $message = 'Current password is incorrect';
                $message_type = 'error';
            }
        }
    }
}

// Include header and sidebar components
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Main Content -->
<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                <div class="flex items-center">
                    <?php if ($message_type === 'success'): ?>
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php endif; ?>
                    <p class="<?php echo $message_type === 'success' ? 'text-green-700' : 'text-red-700'; ?> font-medium">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <div>
                        <h2 class="text-2xl font-bold text-white">Settings</h2>
                        <p class="text-blue-100 text-sm">Settings your account</p>
                    </div>
                </div>
                <div class="hidden md:block">
                    <span class="px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-full text-white text-sm font-medium">
                        Private
                    </span>
                </div>
            </div>
        </div>

        <div class="space-y-6">

            <!-- Profile Information -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">
                    Profile Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">
                            Employee ID
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly
                            class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-gray-300' : 'bg-gray-50 border-gray-300 text-gray-700'; ?> border rounded cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">
                            Username
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['username']); ?>" readonly
                            class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-gray-300' : 'bg-gray-50 border-gray-300 text-gray-700'; ?> border rounded cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">
                            Full Name
                        </label>
                        <input type="text"
                            value="<?php
                                    if (empty($employee['full_name_th']) || $employee['full_name_th'] == '0') {
                                        echo htmlspecialchars($employee['full_name_en']);
                                    } else {
                                        echo htmlspecialchars($employee['full_name_th']);
                                    }
                                    ?>"
                            readonly
                            class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-gray-300' : 'bg-gray-50 border-gray-300 text-gray-700'; ?> border rounded cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">
                            Positiont
                        </label>
                        <input type="text" value="<?php echo get_master('position_master', $employee['position_id']); ?>" readonly
                            class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-gray-300' : 'bg-gray-50 border-gray-300 text-gray-700'; ?> border rounded cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- Appearance Settings -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">
                    <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    Appearance
                </h2>

                <div class="space-y-4">
                    <!-- Theme Mode -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-3">
                            Theme Mode
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <button onclick="changeTheme('light')"
                                class="p-4 border-2 <?php echo $theme_mode === 'light' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900' : ($is_dark ? 'border-gray-600 hover:border-blue-500' : 'border-gray-300 hover:border-blue-500'); ?> rounded-lg transition">
                                <svg class="w-8 h-8 mx-auto mb-2 <?php echo $theme_mode === 'light' ? 'text-blue-600' : ($is_dark ? 'text-yellow-400' : 'text-gray-600'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">Light Mode</span>
                            </button>
                            <button onclick="changeTheme('dark')"
                                class="p-4 border-2 <?php echo $theme_mode === 'dark' ? 'border-blue-500 bg-blue-900' : ($is_dark ? 'border-gray-600 hover:border-blue-500' : 'border-gray-300 hover:border-blue-500'); ?> rounded-lg transition">
                                <svg class="w-8 h-8 mx-auto mb-2 <?php echo $theme_mode === 'dark' ? 'text-blue-400' : 'text-gray-600'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                                <span class="font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">Dark Mode</span>
                            </button>
                        </div>
                    </div>

                    <!-- Language Selection -->
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-3">
                            Language / ภาษา / ဘာသာစကား
                        </label>
                        <select onchange="changeLanguage(this.value)"
                            class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="th" <?php echo $language === 'th' ? 'selected' : ''; ?>>🇹🇭 ไทย (Thai)</option>
                            <option value="en" <?php echo $language === 'en' ? 'selected' : ''; ?>>🇬🇧 English</option>
                            <option value="my" <?php echo $language === 'my' ? 'selected' : ''; ?>>🇲🇲 မြန်မာ (Myanmar)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">
                    <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Change Password
                </h2>

                <form method="POST" action="" id="passwordForm">
                    <input type="hidden" name="action" value="change_password">

                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                                Current Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="current_password" name="current_password" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                                New Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="new_password" name="new_password" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">
                                Minimum 6 characters
                            </p>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                                Confirm New Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Include footer component
    include __DIR__ . '/../includes/footer.php';
    ?>

    <script>
        function changeTheme(mode) {
            fetch('<?php echo BASE_PATH; ?>/api/change_theme.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        mode: mode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to change theme');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error');
                });
        }

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('New passwords do not match');
                return;
            }

            if (newPass.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters');
                return;
            }

            if (!confirm('Are you sure you want to change your password?')) {
                e.preventDefault();
            }
        });
    </script>