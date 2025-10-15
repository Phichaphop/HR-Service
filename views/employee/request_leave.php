<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireAuth();

$page_title = 'Request Leave';

ensure_session_started();
$user_id = $_SESSION['user_id'];
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

// Get recent requests
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$recent_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="<?php echo BASE_PATH; ?>/index.php" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold <?php echo $text_class; ?> mt-2">Request Leave</h1>
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">Submit your leave application</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900 border-l-4 border-red-500'; ?>">
                <div class="flex items-center">
                    <?php if ($message_type === 'success'): ?>
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php endif; ?>
                    <p class="<?php echo $message_type === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'; ?> font-medium">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
                <?php if ($message_type === 'success'): ?>
                    <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" 
                       class="text-green-700 dark:text-green-300 underline text-sm mt-2 inline-block">
                        View my requests →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6">
            <form method="POST" action="" id="leaveForm">
                
                <!-- Employee Info (Read-only) -->
                <div class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> rounded-lg p-4 mb-6">
                    <h3 class="font-semibold <?php echo $is_dark ? 'text-blue-300' : 'text-blue-900'; ?> mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Employee Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Employee ID
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Name
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($display_name); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Position
                            </label>
                            <input type="text" value="<?php echo get_master('position_master', $employee['position_id']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-blue-800'; ?> mb-1">
                                Department
                            </label>
                            <input type="text" value="<?php echo get_master('department_master', $employee['department_id']); ?>" readonly 
                                   class="w-full px-3 py-2 <?php echo $is_dark ? 'bg-gray-600 border-gray-500 text-gray-200' : 'bg-white border-blue-200 text-gray-700'; ?> border rounded cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Leave Type -->
                <div class="mb-6">
                    <label for="leave_type" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                        Leave Type <span class="text-red-500">*</span>
                    </label>
                    <select id="leave_type" name="leave_type" required 
                            class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select leave type</option>
                        <option value="Sick Leave">Sick Leave (ลาป่วย)</option>
                        <option value="Annual Leave">Annual Leave (ลาพักร้อน)</option>
                        <option value="Personal Leave">Personal Leave (ลากิจ)</option>
                        <option value="Maternity Leave">Maternity Leave (ลาคลอด)</option>
                        <option value="Paternity Leave">Paternity Leave (ลาบวช)</option>
                        <option value="Unpaid Leave">Unpaid Leave (ลาไม่รับค่าจ้าง)</option>
                        <option value="Other">Other (อื่นๆ)</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               onchange="calculateDays()"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="end_date" name="end_date" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               onchange="calculateDays()"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Total Days Display -->
                <div id="totalDaysDisplay" class="mb-6 p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg hidden">
                    <p class="text-sm <?php echo $text_class; ?>">
                        <strong>Total Days:</strong> <span id="totalDaysText" class="text-lg font-bold text-blue-600">0</span> days
                    </p>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" name="reason" required rows="4" 
                              placeholder="Please provide a detailed reason for your leave..."
                              class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">Minimum 10 characters</p>
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
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
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

        <!-- Recent Requests -->
        <div class="mt-6 <?php echo $card_bg; ?> rounded-lg shadow-lg p-6">
            <h3 class="font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recent Leave Requests
            </h3>
            <?php if (empty($recent_requests)): ?>
                <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> text-sm">No previous requests</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_requests as $req): ?>
                        <div class="p-3 border <?php echo $is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-200 hover:bg-gray-50'; ?> rounded-lg transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium <?php echo $text_class; ?>"><?php echo htmlspecialchars($req['leave_type']); ?></p>
                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                        <?php echo date('M d, Y', strtotime($req['start_date'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($req['end_date'])); ?>
                                        (<?php echo $req['total_days']; ?> days)
                                    </p>
                                </div>
                                <?php
                                $status_colors = [
                                    'New' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                    'In Progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                    'Complete' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
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
                    <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" 
                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                        View all requests →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

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