<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireAuth();

session_start();
$user_id = $_SESSION['user_id'];
$theme_color = $_SESSION['theme_color'];
$language = $_SESSION['language'];

// Get employee data
$employee = Employee::getById($user_id);

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    // Calculate total days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1;
    
    $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'New')");
    $stmt->bind_param("ssssis", $user_id, $leave_type, $start_date, $end_date, $total_days, $reason);
    
    if ($stmt->execute()) {
        $message = 'Leave request submitted successfully';
        $message_type = 'success';
    } else {
        $message = 'Failed to submit leave request';
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
    <title>Request Leave - <?php echo __('app_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --theme-color: <?php echo $theme_color; ?>;
        }
        .theme-bg {
            background-color: var(--theme-color);
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <div class="container mx-auto px-4 py-6 max-w-3xl">
        <!-- Header -->
        <div class="mb-6">
            <a href="/index.php" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
                ← Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Request Leave</h1>
            <p class="text-gray-600 mt-1">Submit your leave application</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                <p class="<?php echo $message_type === 'success' ? 'text-green-700' : 'text-red-700'; ?> font-medium">
                    <?php echo htmlspecialchars($message); ?>
                </p>
                <?php if ($message_type === 'success'): ?>
                    <a href="/views/employee/my_requests.php" class="text-green-700 underline text-sm mt-2 inline-block">
                        View my requests →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="POST" action="" id="leaveForm">
                
                <!-- Employee Info (Read-only) -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-3">Employee Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-800 mb-1">Employee ID</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly 
                                   class="w-full px-3 py-2 bg-white border border-blue-200 rounded text-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-800 mb-1">Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['full_name_th']); ?>" readonly 
                                   class="w-full px-3 py-2 bg-white border border-blue-200 rounded text-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-800 mb-1">Position</label>
                            <input type="text" value="<?php echo get_master('position_master', $employee['position_id']); ?>" readonly 
                                   class="w-full px-3 py-2 bg-white border border-blue-200 rounded text-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-800 mb-1">Department</label>
                            <input type="text" value="<?php echo get_master('department_master', $employee['department_id']); ?>" readonly 
                                   class="w-full px-3 py-2 bg-white border border-blue-200 rounded text-gray-700">
                        </div>
                    </div>
                </div>

                <!-- Leave Type -->
                <div class="mb-6">
                    <label for="leave_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Leave Type <span class="text-red-500">*</span>
                    </label>
                    <select id="leave_type" name="leave_type" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select leave type</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Personal Leave">Personal Leave</option>
                        <option value="Maternity Leave">Maternity Leave</option>
                        <option value="Paternity Leave">Paternity Leave</option>
                        <option value="Unpaid Leave">Unpaid Leave</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               onchange="calculateDays()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="end_date" name="end_date" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               onchange="calculateDays()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Total Days Display -->
                <div id="totalDaysDisplay" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                    <p class="text-sm text-gray-700">
                        <strong>Total Days:</strong> <span id="totalDaysText" class="text-lg font-bold theme-text">0</span> days
                    </p>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" name="reason" required rows="4" 
                              placeholder="Please provide a detailed reason for your leave..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
                </div>

                <!-- Important Notice -->
                <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Important Notice</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                • Once submitted, you cannot edit the request<br>
                                • You can cancel if status is still "New"<br>
                                • Submit at least 3 days in advance for planned leave
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4">
                    <button type="submit" 
                            class="flex-1 theme-bg text-white py-3 px-6 rounded-lg font-medium hover:opacity-90 transition">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Submit Request
                    </button>
                    <a href="/index.php" 
                       class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-300 transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Recent Requests -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <h3 class="font-bold text-gray-800 mb-4">Recent Leave Requests</h3>
            <?php
            $conn = getDbConnection();
            $stmt = $conn->prepare("SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $recent_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $conn->close();
            
            if (empty($recent_requests)):
            ?>
                <p class="text-gray-500 text-sm">No previous requests</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_requests as $req): ?>
                        <div class="p-3 border rounded-lg hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($req['leave_type']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo date('M d, Y', strtotime($req['start_date'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($req['end_date'])); ?>
                                        (<?php echo $req['total_days']; ?> days)
                                    </p>
                                </div>
                                <?php
                                $status_colors = [
                                    'New' => 'bg-blue-100 text-blue-800',
                                    'In Progress' => 'bg-yellow-100 text-yellow-800',
                                    'Complete' => 'bg-green-100 text-green-800',
                                    'Cancelled' => 'bg-red-100 text-red-800'
                                ];
                                $status_color = $status_colors[$req['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                    <?php echo $req['status']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <a href="/views/employee/my_requests.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View all requests →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function calculateDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end < start) {
                    alert('End date must be after start date');
                    document.getElementById('end_date').value = '';
                    return;
                }
                
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                document.getElementById('totalDaysText').textContent = diffDays;
                document.getElementById('totalDaysDisplay').classList.remove('hidden');
            }
        }

        // Form validation
        document.getElementById('leaveForm').addEventListener('submit', function(e) {
            const reason = document.getElementById('reason').value.trim();
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const leaveType = document.getElementById('leave_type').value;
            
            if (!leaveType) {
                e.preventDefault();
                alert('Please select a leave type');
                return;
            }
            
            if (!startDate || !endDate) {
                e.preventDefault();
                alert('Please select start and end dates');
                return;
            }
            
            if (reason.length < 10) {
                e.preventDefault();
                alert('Reason must be at least 10 characters');
                return;
            }
            
            if (!confirm('Are you sure you want to submit this leave request?')) {
                e.preventDefault();
            }
        });

        // Set minimum end date when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</body>
</html>