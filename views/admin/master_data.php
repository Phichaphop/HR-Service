<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireRole(['admin']);

$page_title = 'Master Data Management';

$message = '';
$message_type = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getDbConnection();
    
    $table = $_POST['table'] ?? '';
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $name_th = $_POST['name_th'] ?? '';
        $name_en = $_POST['name_en'] ?? '';
        $name_my = $_POST['name_my'] ?? '';
        
        // Determine column names based on table
        $columns_map = [
            'function_master' => ['function_name_th', 'function_name_en', 'function_name_my'],
            'division_master' => ['division_name_th', 'division_name_en', 'division_name_my'],
            'department_master' => ['department_name_th', 'department_name_en', 'department_name_my'],
            'section_master' => ['section_name_th', 'section_name_en', 'section_name_my'],
            'operation_master' => ['operation_name_th', 'operation_name_en', 'operation_name_my'],
            'position_master' => ['position_name_th', 'position_name_en', 'position_name_my'],
            'position_level_master' => ['level_name_th', 'level_name_en', 'level_name_my'],
            'labour_cost_master' => ['cost_name_th', 'cost_name_en', 'cost_name_my'],
            'hiring_type_master' => ['type_name_th', 'type_name_en', 'type_name_my'],
            'customer_zone_master' => ['zone_name_th', 'zone_name_en', 'zone_name_my'],
            'contribution_level_master' => ['level_name_th', 'level_name_en', 'level_name_my'],
            'sex_master' => ['sex_name_th', 'sex_name_en', 'sex_name_my'],
            'nationality_master' => ['nationality_th', 'nationality_en', 'nationality_my'],
            'education_level_master' => ['level_name_th', 'level_name_en', 'level_name_my'],
            'status_master' => ['status_name_th', 'status_name_en', 'status_name_my'],
            'service_category_master' => ['category_name_th', 'category_name_en', 'category_name_my'],
            'service_type_master' => ['type_name_th', 'type_name_en', 'type_name_my'],
            'doc_type_master' => ['type_name_th', 'type_name_en', 'type_name_my'],
            'termination_reason_master' => ['reason_th', 'reason_en', 'reason_my'],
            'prefix_master' => ['prefix_th', 'prefix_en', 'prefix_my']
        ];
        
        if (isset($columns_map[$table])) {
            $cols = $columns_map[$table];
            $stmt = $conn->prepare("INSERT INTO $table ({$cols[0]}, {$cols[1]}, {$cols[2]}) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name_th, $name_en, $name_my);
            
            if ($stmt->execute()) {
                $message = 'Record added successfully';
                $message_type = 'success';
            } else {
                $message = 'Failed to add record';
                $message_type = 'error';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        
        // Determine ID column name
        $id_map = [
            'function_master' => 'function_id',
            'division_master' => 'division_id',
            'department_master' => 'department_id',
            'section_master' => 'section_id',
            'operation_master' => 'operation_id',
            'position_master' => 'position_id',
            'position_level_master' => 'level_id',
            'labour_cost_master' => 'labour_cost_id',
            'hiring_type_master' => 'hiring_type_id',
            'customer_zone_master' => 'zone_id',
            'contribution_level_master' => 'contribution_id',
            'sex_master' => 'sex_id',
            'nationality_master' => 'nationality_id',
            'education_level_master' => 'education_id',
            'status_master' => 'status_id',
            'service_category_master' => 'category_id',
            'service_type_master' => 'type_id',
            'doc_type_master' => 'doc_type_id',
            'termination_reason_master' => 'reason_id',
            'prefix_master' => 'prefix_id'
        ];
        
        if (isset($id_map[$table])) {
            $id_col = $id_map[$table];
            $stmt = $conn->prepare("DELETE FROM $table WHERE $id_col = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = 'Record deleted successfully';
                $message_type = 'success';
            } else {
                $message = 'Cannot delete: Record is in use';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
    
    $conn->close();
}

// Get selected table
$selected_table = $_GET['table'] ?? 'function_master';

// Master tables list with icons
$master_tables = [
    'function_master' => ['name' => 'Functions', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
    'division_master' => ['name' => 'Divisions', 'icon' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z'],
    'department_master' => ['name' => 'Departments', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
    'section_master' => ['name' => 'Sections', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    'operation_master' => ['name' => 'Operations', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    'position_master' => ['name' => 'Positions', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    'position_level_master' => ['name' => 'Position Levels', 'icon' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'],
    'prefix_master' => ['name' => 'Prefixes', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    'sex_master' => ['name' => 'Gender', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    'nationality_master' => ['name' => 'Nationalities', 'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    'education_level_master' => ['name' => 'Education Levels', 'icon' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'],
    'status_master' => ['name' => 'Employment Status', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    'labour_cost_master' => ['name' => 'Labour Cost Types', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    'hiring_type_master' => ['name' => 'Hiring Types', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
    'customer_zone_master' => ['name' => 'Customer Zones', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
    'contribution_level_master' => ['name' => 'Contribution Levels', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    'service_category_master' => ['name' => 'Service Categories', 'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
    'service_type_master' => ['name' => 'Service Types', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    'doc_type_master' => ['name' => 'Document Types', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    'termination_reason_master' => ['name' => 'Termination Reasons', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636']
];

// Get data for selected table
$conn = getDbConnection();
$data = [];
if ($selected_table) {
    $result = $conn->query("SELECT * FROM $selected_table ORDER BY 1");
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        
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
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="mb-6 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Master Data Management</h1>
                        <p class="text-purple-100 mt-1">Manage system-wide reference data and configurations</p>
                    </div>
                </div>
                <span class="hidden md:block px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-full text-white text-sm font-medium">
                    <?php echo count($master_tables); ?> Tables
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- Sidebar with Master Tables List -->
            <div class="lg:col-span-1">
                <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-4 sticky top-20">
                    <h3 class="font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Master Tables
                    </h3>
                    <nav class="space-y-1">
                        <?php foreach ($master_tables as $table => $info): ?>
                            <a href="?table=<?php echo $table; ?>" 
                               class="flex items-center px-3 py-2 rounded-lg transition group <?php echo $selected_table === $table ? 'bg-blue-600 text-white' : ($is_dark ? 'hover:bg-gray-700 text-gray-300' : 'hover:bg-gray-100 text-gray-700'); ?>">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 <?php echo $selected_table === $table ? '' : 'opacity-70'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $info['icon']; ?>"></path>
                                </svg>
                                <span class="text-sm truncate"><?php echo $info['name']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-3">
                <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg overflow-hidden">
                    
                    <!-- Table Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white">
                                <?php echo $master_tables[$selected_table]['name'] ?? 'Master Data'; ?>
                            </h2>
                            <button onclick="openAddModal()" 
                                    class="px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition shadow-lg">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add New
                            </button>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> border-b <?php echo $is_dark ? 'border-gray-600' : 'border-gray-200'; ?>">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Thai</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">English</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Myanmar</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                                <?php if (empty($data)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                            No data available
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data as $row): ?>
                                        <?php
                                        $id = reset($row); // First column is ID
                                        $values = array_values($row);
                                        ?>
                                        <tr class="<?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?> transition">
                                            <td class="px-6 py-4 text-sm font-medium <?php echo $text_class; ?>"><?php echo htmlspecialchars($id); ?></td>
                                            <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($values[1] ?? ''); ?></td>
                                            <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($values[2] ?? ''); ?></td>
                                            <td class="px-6 py-4 text-sm <?php echo $text_class; ?>"><?php echo htmlspecialchars($values[3] ?? ''); ?></td>
                                            <td class="px-6 py-4 text-center">
                                                <button onclick="deleteRecord(<?php echo $id; ?>)" 
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition">
                                                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer -->
                    <div class="px-6 py-4 <?php echo $is_dark ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-200'; ?> border-t">
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                            Total Records: <strong><?php echo count($data); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Add New Record</h3>
                <button onclick="closeAddModal()" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="" class="p-6 space-y-4">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="table" value="<?php echo $selected_table; ?>">
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Thai <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name_th" required
                           class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        English <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name_en" required
                           class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Myanmar <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name_my" required
                           class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeAddModal()" 
                            class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="table" value="<?php echo $selected_table; ?>">
        <input type="hidden" name="id" id="deleteId">
    </form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }

    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }

    function deleteRecord(id) {
        if (confirm('Are you sure you want to delete this record?\n\nWarning: This action cannot be undone and may fail if the record is in use.')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    // Close modal when clicking outside
    document.getElementById('addModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
        }
    });
</script>