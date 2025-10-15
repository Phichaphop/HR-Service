<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer']);

// ดึงข้อมูล theme และ user (ไม่ต้องประกาศซ้ำ)
extract(get_theme_vars());

// Set page title
$page_title = 'Employees';

// Get filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$function_filter = $_GET['function'] ?? '';
$per_page = 20;

$filters = [
    'search' => $search,
    'status_id' => $status_filter,
    'function_id' => $function_filter
];

// Get employees
$result = Employee::getAll($page, $per_page, $filters);
$employees = $result['data'];
$total_pages = $result['total_pages'];
$total_records = $result['total'];

// Get master data for filters
$conn = getDbConnection();
$statuses = $conn->query("SELECT * FROM status_master ORDER BY status_id")->fetch_all(MYSQLI_ASSOC);
$functions = $conn->query("SELECT * FROM function_master ORDER BY function_id")->fetch_all(MYSQLI_ASSOC);
$conn->close();

// Include header และ sidebar (จะดึง theme vars อัตโนมัติ)
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<!-- Main Content -->
<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <a href="<?php echo BASE_PATH; ?>/index.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm mb-2 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <?php echo __('dashboard'); ?>
                </a>
                <h1 class="text-3xl md:text-4xl font-bold <?php echo $text_class; ?> mb-2">
                    <?php echo __('employees'); ?>
                </h1>
                <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                    <?php echo $total_records; ?> total employees
                </p>
            </div>

            <?php if ($user_role === 'admin'): ?>
                <div class="flex gap-2">
                    <button onclick="exportData()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                    <button onclick="openAddModal()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Employee
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <?php
            $conn = getDbConnection();
            $stats = [
                'total' => $total_records,
                'active' => $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE status_id = 1")->fetch_assoc()['cnt'],
                'inactive' => $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE status_id != 1")->fetch_assoc()['cnt'],
                'new_this_month' => $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE MONTH(date_of_hire) = MONTH(CURDATE()) AND YEAR(date_of_hire) = YEAR(CURDATE())")->fetch_assoc()['cnt']
            ];
            $conn->close();
            
            $stat_cards = [
                ['label' => 'Total', 'value' => $stats['total'], 'color' => 'blue', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                ['label' => 'Active', 'value' => $stats['active'], 'color' => 'green', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Inactive', 'value' => $stats['inactive'], 'color' => 'red', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                ['label' => 'New (Month)', 'value' => $stats['new_this_month'], 'color' => 'purple', 'icon' => 'M12 4v16m8-8H4']
            ];
            
            foreach ($stat_cards as $stat):
            ?>
                <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-4 theme-transition hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1"><?php echo $stat['label']; ?></p>
                            <p class="text-2xl font-bold text-<?php echo $stat['color']; ?>-600"><?php echo $stat['value']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-<?php echo $stat['color']; ?>-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $stat['icon']; ?>"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filters -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6 theme-transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold <?php echo $text_class; ?>">Filters</h3>
                <?php if ($search || $status_filter || $function_filter): ?>
                    <a href="?" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Clear Filters</a>
                <?php endif; ?>
            </div>

            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Search</label>
                    <input type="text" name="search" placeholder="ID, Name, Phone..." value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                </div>

                <div>
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                        <option value="">All Status</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['status_id']; ?>" <?php echo $status_filter == $status['status_id'] ? 'selected' : ''; ?>>
                                <?php echo get_master('status_master', $status['status_id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">Function</label>
                    <select name="function" class="w-full px-4 py-2 border <?php echo $border_class; ?> rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white' : 'bg-white'; ?> theme-transition">
                        <option value="">All Functions</option>
                        <?php foreach ($functions as $func): ?>
                            <option value="<?php echo $func['function_id']; ?>" <?php echo $function_filter == $func['function_id'] ? 'selected' : ''; ?>>
                                <?php echo get_master('function_master', $func['function_id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Employee Table -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg overflow-hidden theme-transition">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Position</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Function</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Years</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <svg class="w-16 h-16 <?php echo $is_dark ? 'text-gray-600' : 'text-gray-400'; ?> mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> font-medium">No employees found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                                <tr class="<?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?> transition">
                                    <td class="px-6 py-4 text-sm font-medium <?php echo $text_class; ?>">
                                        <?php echo htmlspecialchars($emp['employee_id']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-200'; ?> flex items-center justify-center mr-3">
                                                <?php if ($emp['profile_pic_path']): ?>
                                                    <img src="<?php echo htmlspecialchars($emp['profile_pic_path']); ?>" class="w-full h-full rounded-full object-cover" alt="">
                                                <?php else: ?>
                                                    <span class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> font-medium">
                                                        <?php echo strtoupper(substr($emp['full_name_en'], 0, 1)); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium <?php echo $text_class; ?>">
                                                    <?php echo $language === 'en' ? htmlspecialchars($emp['full_name_en']) : htmlspecialchars($emp['full_name_th']); ?>
                                                </p>
                                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>"><?php echo htmlspecialchars($emp['phone_no']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                        <?php echo get_master('position_master', $emp['position_id']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                        <?php echo get_master('function_master', $emp['function_id']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $emp['status_id'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo get_master('status_master', $emp['status_id']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                        <?php echo $emp['year_of_service']; ?> years
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button onclick="viewEmployee('<?php echo $emp['employee_id']; ?>')" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="View">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            <?php if ($user_role === 'admin'): ?>
                                                <button onclick="editEmployee('<?php echo $emp['employee_id']; ?>')" class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition" title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button onclick="deleteEmployee('<?php echo $emp['employee_id']; ?>')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center space-x-2 mt-6">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>" 
                       class="px-4 py-2 border <?php echo $border_class; ?> rounded-lg transition <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?>">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>" 
                       class="px-4 py-2 border rounded-lg transition <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : $border_class . ($is_dark ? ' hover:bg-gray-700' : ' hover:bg-gray-50'); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>" 
                       class="px-4 py-2 border <?php echo $border_class; ?> rounded-lg transition <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<!-- Page-specific JavaScript (เฉพาะหน้านี้) -->
<script>
    function viewEmployee(id) {
        window.location.href = '<?php echo BASE_PATH; ?>/views/admin/employee_detail.php?id=' + id;
    }

    function editEmployee(id) {
        window.location.href = '<?php echo BASE_PATH; ?>/views/admin/employee_edit.php?id=' + id;
    }

    function deleteEmployee(id) {
        if (confirm('Are you sure you want to delete this employee?\n\nThis action cannot be undone.')) {
            window.location.href = '<?php echo BASE_PATH; ?>/api/employee_delete.php?id=' + id;
        }
    }

    function openAddModal() {
        window.location.href = '<?php echo BASE_PATH; ?>/views/admin/employee_add.php';
    }

    function exportData() {
        if (confirm('Export all employee data to CSV?')) {
            window.location.href = '<?php echo BASE_PATH; ?>/api/employee_export.php?<?php echo http_build_query(["search" => $search, "status" => $status_filter, "function" => $function_filter]); ?>';
        }
    }
</script>