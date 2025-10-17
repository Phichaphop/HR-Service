<?php
/**
 * Request Management Page
 * Admin/Officer only - Manage all service requests
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer']);

$page_title = 'Request Management';
extract(get_theme_vars());

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$conn = getDbConnection();
if (!$conn) {
    die("Database connection failed");
}

// Build WHERE clause
$where_conditions = ["1=1"];
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(employee_id LIKE ? OR employee_name LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$where_sql = implode(' AND ', $where_conditions);

// Function to get requests from a table
function getRequests($conn, $table, $type_name, $where_sql, $params, $types, $offset, $per_page) {
    // Determine the primary key column name
    $id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';
    
    $sql = "SELECT 
        $id_column as request_id,
        employee_id,
        '$type_name' as request_type,
        status,
        created_at,
        handler_id,
        handler_remarks,
        satisfaction_score
    FROM $table 
    WHERE $where_sql 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $all_params = array_merge($params, [$per_page, $offset]);
        $all_types = $types . 'ii';
        $stmt->bind_param($all_types, ...$all_params);
    } else {
        $stmt->bind_param('ii', $per_page, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $row['table_name'] = $table;
        $requests[] = $row;
    }
    
    $stmt->close();
    return $requests;
}

// Get all requests based on type filter
$all_requests = [];

$request_types = [
    'leave_requests' => 'Leave Request',
    'certificate_requests' => 'Certificate Request',
    'id_card_requests' => 'ID Card Request',
    'shuttle_bus_requests' => 'Shuttle Bus Request',
    'locker_requests' => 'Locker Request',
    'supplies_requests' => 'Supplies Request',
    'skill_test_requests' => 'Skill Test Request',
    'document_submissions' => 'Document Submission'
];

if ($type_filter === 'all') {
    foreach ($request_types as $table => $type_name) {
        $requests = getRequests($conn, $table, $type_name, $where_sql, $params, $types, 0, $per_page);
        $all_requests = array_merge($all_requests, $requests);
    }
} else {
    if (isset($request_types[$type_filter])) {
        $all_requests = getRequests($conn, $type_filter, $request_types[$type_filter], $where_sql, $params, $types, $offset, $per_page);
    }
}

// Sort by created_at DESC
usort($all_requests, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Limit to page size
$all_requests = array_slice($all_requests, 0, $per_page);

// Get statistics
$stats = [
    'total' => 0,
    'new' => 0,
    'in_progress' => 0,
    'complete' => 0,
    'cancelled' => 0
];

foreach ($request_types as $table => $type_name) {
    $result = $conn->query("SELECT status, COUNT(*) as count FROM $table GROUP BY status");
    while ($row = $result->fetch_assoc()) {
        $stats['total'] += $row['count'];
        $stats[strtolower(str_replace(' ', '_', $row['status']))] += $row['count'];
    }
}

$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 lg:ml-64 p-6">
    <!-- Page Header -->
    <div class="mb-8">

        <!-- Page Header -->
        <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Request Management</h1>
                        <p class="text-green-100 mt-1">Review and manage all employee service requests</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">Total</p>
                        <p class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo number_format($stats['total']); ?></p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">New</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['new']); ?></p>
                    </div>
                    <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">In Progress</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['in_progress']); ?></p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">Complete</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['complete']); ?></p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="<?php echo $card_bg; ?> p-4 rounded-lg border <?php echo $border_class; ?> shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">Cancelled</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo number_format($stats['cancelled']); ?></p>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-6 mb-6 border <?php echo $border_class; ?>">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Employee ID or Name"
                    class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="New" <?php echo $status_filter === 'New' ? 'selected' : ''; ?>>New</option>
                    <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Complete" <?php echo $status_filter === 'Complete' ? 'selected' : ''; ?>>Complete</option>
                    <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Request Type</label>
                <select name="type" class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500">
                    <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                    <?php foreach ($request_types as $table => $type_name): ?>
                        <option value="<?php echo $table; ?>" <?php echo $type_filter === $table ? 'selected' : ''; ?>><?php echo $type_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    Filter
                </button>
                <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm border <?php echo $border_class; ?> overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?>">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Request ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Handler</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                    <?php if (empty($all_requests)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-lg font-medium">No requests found</p>
                                <p class="text-sm mt-1">Try adjusting your filters</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_requests as $request): ?>
                            <tr class="hover:<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?> transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm <?php echo $text_class; ?>">
                                        #<?php echo str_pad($request['request_id'], 5, '0', STR_PAD_LEFT); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <?php echo htmlspecialchars($request['request_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="<?php echo $text_class; ?> font-medium">
                                        <?php echo htmlspecialchars($request['employee_id']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">
                                        <?php echo date('d M Y, H:i', strtotime($request['created_at'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $status_colors = [
                                        'New' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'Complete' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    ];
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$request['status']] ?? ''; ?>">
                                        <?php echo htmlspecialchars($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($request['handler_id']): ?>
                                        <span class="<?php echo $text_class; ?> text-sm">
                                            <?php echo htmlspecialchars($request['handler_id']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm italic">
                                            Unassigned
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="openRequestModal('<?php echo $request['table_name']; ?>', <?php echo $request['request_id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Request Detail Modal -->
<div id="requestModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold <?php echo $text_class; ?>">Request Details</h3>
                <button onclick="closeRequestModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modalContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function openRequestModal(table, requestId) {
    const modal = document.getElementById('requestModal');
    const content = document.getElementById('modalContent');
    
    console.log('Opening modal for:', table, requestId);
    
    // Show loading
    content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 <?php echo $text_class; ?>">Loading request details...</p></div>';
    modal.classList.remove('hidden');
    
    // Fetch request details
    const url = `<?php echo BASE_PATH; ?>/api/get_request_detail.php?table=${table}&id=${requestId}`;
    console.log('Fetching from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Request data:', data);
            
            if (data.success) {
                content.innerHTML = generateRequestHTML(data.request, table);
            } else {
                content.innerHTML = `<div class="text-center py-8"><p class="text-red-600 font-medium">${data.message || 'Error loading request'}</p><button onclick="closeRequestModal()" class="mt-4 px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Close</button></div>`;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            content.innerHTML = `<div class="text-center py-8"><p class="text-red-600 font-medium">Error loading request details</p><p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-2">${error.message}</p><button onclick="closeRequestModal()" class="mt-4 px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Close</button></div>`;
        });
}

function closeRequestModal() {
    document.getElementById('requestModal').classList.add('hidden');
}

function generateRequestHTML(request, table) {
    // Generate HTML based on request data
    let html = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">Request ID</label>
                    <p class="<?php echo $text_class; ?> font-mono">#${String(request.request_id).padStart(5, '0')}</p>
                </div>
                <div>
                    <label class="text-sm font-medium <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">Status</label>
                    <p class="<?php echo $text_class; ?>">${request.status}</p>
                </div>
            </div>
            
            <div>
                <label class="text-sm font-medium <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">Employee ID</label>
                <p class="<?php echo $text_class; ?>">${request.employee_id}</p>
            </div>
            
            <div>
                <label class="text-sm font-medium <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">Created At</label>
                <p class="<?php echo $text_class; ?>">${new Date(request.created_at).toLocaleString()}</p>
            </div>
            
            <div class="pt-4 border-t <?php echo $border_class; ?>">
                <h4 class="font-semibold <?php echo $text_class; ?> mb-3">Update Status</h4>
                <form onsubmit="updateRequestStatus(event, '${table}', ${request.request_id})">
                    <select name="status" class="w-full px-4 py-2 border rounded-lg mb-3 <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?>">
                        <option value="New" ${request.status === 'New' ? 'selected' : ''}>New</option>
                        <option value="In Progress" ${request.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                        <option value="Complete" ${request.status === 'Complete' ? 'selected' : ''}>Complete</option>
                        <option value="Cancelled" ${request.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                    
                    <textarea name="remarks" placeholder="Handler remarks (optional)" rows="3"
                        class="w-full px-4 py-2 border rounded-lg mb-3 <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?>">${request.handler_remarks || ''}</textarea>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-medium">
                        Update Request
                    </button>
                </form>
            </div>
        </div>
    `;
    return html;
}

function updateRequestStatus(event, table, requestId) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        table: table,
        request_id: requestId,
        status: formData.get('status'),
        handler_remarks: formData.get('remarks')
    };
    
    // Debug log
    console.log('Updating request:', data);
    
    // Disable submit button
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Updating...';
    
    fetch('<?php echo BASE_PATH; ?>/api/update_request_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('Result:', result);
        
        if (result.success) {
            showToast('Request updated successfully', 'success');
            closeRequestModal();
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showToast('Error: ' + (result.message || 'Unknown error'), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showToast('Failed to update request: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRequestModal();
    }
});

// Close modal on outside click
document.getElementById('requestModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRequestModal();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>