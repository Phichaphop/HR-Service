<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireAuth();

$user_id = $_SESSION['user_id'];

// Get filter
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';

// Get all requests from different tables
$conn = getDbConnection();

function getRequests($conn, $user_id, $table, $type_name, $status_filter)
{
    $where = "employee_id = ?";
    $params = [$user_id];
    $types = "s";

    if ($status_filter) {
        $where .= " AND status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    $sql = "SELECT *, '$type_name' as request_type FROM $table WHERE $where ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$all_requests = [];

if (!$type_filter || $type_filter === 'leave') {
    $all_requests = array_merge($all_requests, getRequests($conn, $user_id, 'leave_requests', 'Leave', $status_filter));
}
if (!$type_filter || $type_filter === 'certificate') {
    $all_requests = array_merge($all_requests, getRequests($conn, $user_id, 'certificate_requests', 'Certificate', $status_filter));
}
if (!$type_filter || $type_filter === 'idcard') {
    $all_requests = array_merge($all_requests, getRequests($conn, $user_id, 'id_card_requests', 'ID Card', $status_filter));
}
if (!$type_filter || $type_filter === 'shuttle') {
    $all_requests = array_merge($all_requests, getRequests($conn, $user_id, 'shuttle_bus_requests', 'Shuttle Bus', $status_filter));
}
if (!$type_filter || $type_filter === 'locker') {
    $all_requests = array_merge($all_requests, getRequests($conn, $user_id, 'locker_requests', 'Locker', $status_filter));
}
if (!$type_filter || $type_filter === 'supplies') {
    $all_requests = array_merge($all_requests, getRequests($conn, $user_id, 'supplies_requests', 'Supplies', $status_filter));
}
if (!$type_filter || $type_filter === 'skill') {
    $all_requests = array_merge($all_requests, getRequests($conn, $user_id, 'skill_test_requests', 'Skill Test', $status_filter));
}

$conn->close();

// Sort by date
usort($all_requests, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$total_requests = count($all_requests);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-3xl">

        <div class="mb-6">
            <div>
                <a href="/index.php" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
                    ← <?php echo __('dashboard'); ?>
                </a>
                <h1 class="text-3xl font-bold text-gray-800">My Requests</h1>
                <p class="text-gray-600 mt-1"><?php echo $total_requests; ?> total requests</p>
            </div>
            <button onclick="window.location.href='/index.php#quick-actions'" class="theme-bg text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Request
            </button>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <?php
            $status_counts = [
                'New' => 0,
                'In Progress' => 0,
                'Complete' => 0,
                'Cancelled' => 0
            ];
            foreach ($all_requests as $req) {
                $status_counts[$req['status']]++;
            }
            ?>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-blue-600 text-2xl font-bold"><?php echo $status_counts['New']; ?></div>
                <div class="text-gray-600 text-sm">New</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-yellow-600 text-2xl font-bold"><?php echo $status_counts['In Progress']; ?></div>
                <div class="text-gray-600 text-sm">In Progress</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-green-600 text-2xl font-bold"><?php echo $status_counts['Complete']; ?></div>
                <div class="text-gray-600 text-sm">Complete</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-red-600 text-2xl font-bold"><?php echo $status_counts['Cancelled']; ?></div>
                <div class="text-gray-600 text-sm">Cancelled</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Request Type</label>
                    <select name="type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="leave" <?php echo $type_filter === 'leave' ? 'selected' : ''; ?>>Leave</option>
                        <option value="certificate" <?php echo $type_filter === 'certificate' ? 'selected' : ''; ?>>Certificate</option>
                        <option value="idcard" <?php echo $type_filter === 'idcard' ? 'selected' : ''; ?>>ID Card</option>
                        <option value="shuttle" <?php echo $type_filter === 'shuttle' ? 'selected' : ''; ?>>Shuttle Bus</option>
                        <option value="locker" <?php echo $type_filter === 'locker' ? 'selected' : ''; ?>>Locker</option>
                        <option value="supplies" <?php echo $type_filter === 'supplies' ? 'selected' : ''; ?>>Supplies</option>
                        <option value="skill" <?php echo $type_filter === 'skill' ? 'selected' : ''; ?>>Skill Test</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="New" <?php echo $status_filter === 'New' ? 'selected' : ''; ?>>New</option>
                        <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Complete" <?php echo $status_filter === 'Complete' ? 'selected' : ''; ?>>Complete</option>
                        <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full theme-bg text-white px-6 py-2 rounded-lg font-medium hover:opacity-90 transition">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Requests List -->
        <div class="space-y-4">
            <?php if (empty($all_requests)): ?>
                <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-600 text-lg font-medium">No requests found</p>
                    <p class="text-gray-500 text-sm mt-2">Submit your first request to get started</p>
                    <button onclick="window.location.href='/index.php'" class="mt-4 theme-bg text-white px-6 py-2 rounded-lg">
                        Go to Dashboard
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($all_requests as $req): ?>
                    <?php
                    $status_colors = [
                        'New' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'In Progress' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        'Complete' => 'bg-green-100 text-green-800 border-green-300',
                        'Cancelled' => 'bg-red-100 text-red-800 border-red-300'
                    ];
                    $status_color = $status_colors[$req['status']] ?? 'bg-gray-100 text-gray-800 border-gray-300';

                    $type_icons = [
                        'Leave' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                        'Certificate' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                        'ID Card' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2',
                        'Shuttle Bus' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                        'Locker' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                        'Supplies' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                        'Skill Test' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
                    ];
                    $icon_path = $type_icons[$req['request_type']] ?? 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
                    ?>

                    <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 theme-bg rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $icon_path; ?>"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($req['request_type']); ?> Request</h3>
                                        <p class="text-sm text-gray-500">
                                            Request ID: #<?php echo $req['request_id']; ?>
                                        </p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $status_color; ?>">
                                    <?php echo $req['status']; ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Submitted</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo date('M d, Y H:i', strtotime($req['created_at'])); ?>
                                    </p>
                                </div>
                                <?php if ($req['request_type'] === 'Leave'): ?>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Duration</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo $req['total_days']; ?> days
                                            (<?php echo date('M d', strtotime($req['start_date'])); ?> - <?php echo date('M d', strtotime($req['end_date'])); ?>)
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($req['handler_remarks']): ?>
                                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                    <p class="text-xs font-medium text-gray-700 mb-1">Officer Remarks:</p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($req['handler_remarks']); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="flex space-x-2">
                                <button onclick="viewDetails(<?php echo $req['request_id']; ?>, '<?php echo $req['request_type']; ?>')"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                    View Details
                                </button>
                                <?php if ($req['status'] === 'New'): ?>
                                    <button onclick="cancelRequest(<?php echo $req['request_id']; ?>, '<?php echo $req['request_type']; ?>')"
                                        class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm font-medium">
                                        Cancel
                                    </button>
                                <?php endif; ?>
                                <?php if ($req['status'] === 'Complete' && !$req['satisfaction_score']): ?>
                                    <button onclick="rateSatisfaction(<?php echo $req['request_id']; ?>, '<?php echo $req['request_type']; ?>')"
                                        class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-sm font-medium">
                                        Rate
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewDetails(id, type) {
            alert('View request details: ' + type + ' #' + id);
            // Implement detail view modal or redirect to detail page
        }

        function cancelRequest(id, type) {
            if (confirm('Are you sure you want to cancel this request?')) {
                // Implement cancel request API call
                window.location.href = '/api/cancel_request.php?id=' + id + '&type=' + type;
            }
        }

        function rateSatisfaction(id, type) {
            // Implement satisfaction rating modal
            alert('Rate satisfaction for: ' + type + ' #' + id);
        }
    </script>
    </body>

    </html>