<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer']);

session_start();
$user_role = $_SESSION['role'];
$theme_color = $_SESSION['theme_color'];
$language = $_SESSION['language'];

// Get filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$function_filter = $_GET['function'] ?? '';

$filters = [
    'search' => $search,
    'status_id' => $status_filter,
    'function_id' => $function_filter
];

// Get employees
$result = Employee::getAll($page, 20, $filters);
$employees = $result['data'];
$total_pages = $result['total_pages'];
$total_records = $result['total'];

// Get master data for filters
$conn = getDbConnection();
$statuses = $conn->query("SELECT * FROM status_master ORDER BY status_id")->fetch_all(MYSQLI_ASSOC);
$functions = $conn->query("SELECT * FROM function_master ORDER BY function_id")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('employees'); ?> - <?php echo __('app_title'); ?></title>
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
    
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="/index.php" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
                    ← <?php echo __('dashboard'); ?>
                </a>
                <h1 class="text-3xl font-bold text-gray-800"><?php echo __('employees'); ?></h1>
                <p class="text-gray-600 mt-1"><?php echo $total_records; ?> total employees</p>
            </div>
            <?php if ($user_role === 'admin'): ?>
            <button onclick="openAddModal()" class="theme-bg text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Employee
            </button>
            <?php endif; ?>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="ID, Name..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['status_id']; ?>" <?php echo $status_filter == $status['status_id'] ? 'selected' : ''; ?>>
                                <?php echo get_master('status_master', $status['status_id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Function Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Function</label>
                    <select name="function" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Functions</option>
                        <?php foreach ($functions as $func): ?>
                            <option value="<?php echo $func['function_id']; ?>" <?php echo $function_filter == $func['function_id'] ? 'selected' : ''; ?>>
                                <?php echo get_master('function_master', $func['function_id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Button -->
                <div class="flex items-end">
                    <button type="submit" class="w-full theme-bg text-white px-6 py-2 rounded-lg font-medium hover:opacity-90 transition">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Employee Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="theme-bg text-white">
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
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    No employees found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($emp['employee_id']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <?php if ($emp['profile_pic_path']): ?>
                                                    <img src="<?php echo htmlspecialchars($emp['profile_pic_path']); ?>" class="w-full h-full rounded-full object-cover" alt="">
                                                <?php else: ?>
                                                    <span class="text-gray-600 font-medium">
                                                        <?php echo strtoupper(substr($emp['full_name_en'], 0, 1)); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    <?php echo $language === 'en' ? htmlspecialchars($emp['full_name_en']) : htmlspecialchars($emp['full_name_th']); ?>
                                                </p>
                                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($emp['phone_no']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo get_master('position_master', $emp['position_id']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo get_master('function_master', $emp['function_id']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        $status = get_master('status_master', $emp['status_id']);
                                        $status_color = $emp['status_id'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo $emp['year_of_service']; ?> years
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="viewEmployee('<?php echo $emp['employee_id']; ?>')" class="text-blue-600 hover:text-blue-800 mr-3" title="View">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <?php if ($user_role === 'admin'): ?>
                                        <button onclick="editEmployee('<?php echo $emp['employee_id']; ?>')" class="text-green-600 hover:text-green-800 mr-3" title="Edit">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="deleteEmployee('<?php echo $emp['employee_id']; ?>')" class="text-red-600 hover:text-red-800" title="Delete">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="md:hidden">
                <?php foreach ($employees as $emp): ?>
                    <div class="p-4 border-b hover:bg-gray-50">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <?php if ($emp['profile_pic_path']): ?>
                                        <img src="<?php echo htmlspecialchars($emp['profile_pic_path']); ?>" class="w-full h-full rounded-full object-cover" alt="">
                                    <?php else: ?>
                                        <span class="text-gray-600 font-medium">
                                            <?php echo strtoupper(substr($emp['full_name_en'], 0, 1)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">
                                        <?php echo $language === 'en' ? htmlspecialchars($emp['full_name_en']) : htmlspecialchars($emp['full_name_th']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($emp['employee_id']); ?></p>
                                </div>
                            </div>
                            <?php 
                            $status_color = $emp['status_id'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                <?php echo get_master('status_master', $emp['status_id']); ?>
                            </span>
                        </div>
                        <div class="space-y-1 mb-3">
                            <p class="text-sm text-gray-700">
                                <strong>Position:</strong> <?php echo get_master('position_master', $emp['position_id']); ?>
                            </p>
                            <p class="text-sm text-gray-700">
                                <strong>Years:</strong> <?php echo $emp['year_of_service']; ?> years
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="viewEmployee('<?php echo $emp['employee_id']; ?>')" class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded text-sm font-medium">
                                View
                            </button>
                            <?php if ($user_role === 'admin'): ?>
                            <button onclick="editEmployee('<?php echo $emp['employee_id']; ?>')" class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded text-sm font-medium">
                                Edit
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-6 space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>" 
                   class="px-4 py-2 border rounded-lg hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>" 
                   class="px-4 py-2 border rounded-lg <?php echo $i === $page ? 'theme-bg text-white' : 'hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&function=<?php echo $function_filter; ?>" 
                   class="px-4 py-2 border rounded-lg hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function viewEmployee(id) {
            window.location.href = '/views/admin/employee_detail.php?id=' + id;
        }

        function editEmployee(id) {
            window.location.href = '/views/admin/employee_edit.php?id=' + id;
        }

        function deleteEmployee(id) {
            if (confirm('Are you sure you want to delete this employee?')) {
                window.location.href = '/api/employee_delete.php?id=' + id;
            }
        }

        function openAddModal() {
            window.location.href = '/views/admin/employee_add.php';
        }
    </script>
</body>
</html>