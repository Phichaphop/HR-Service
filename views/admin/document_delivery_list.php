<?php
/**
 * Document Delivery List (Admin/Officer)
 * View all document delivery records
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

AuthController::requireRole(['admin', 'officer']);

$page_title = 'รายการลงชื่อส่งเอกสาร';
extract(get_theme_vars());

$conn = getDbConnection();

// Filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$delivery_type = $_GET['delivery_type'] ?? 'all';

// Build WHERE clause
$where_conditions = ["1=1"];
$params = [];
$types = '';

if (!empty($date_from)) {
    $where_conditions[] = "DATE(dd.delivery_date) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(dd.delivery_date) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if ($delivery_type !== 'all') {
    $where_conditions[] = "dd.delivery_type = ?";
    $params[] = $delivery_type;
    $types .= 's';
}

$where_sql = implode(' AND ', $where_conditions);

// Get records
$sql = "SELECT dd.*, 
        e.full_name_th, e.full_name_en,
        sc.category_name_th
        FROM document_delivery dd
        LEFT JOIN employees e ON dd.employee_id = e.employee_id
        LEFT JOIN service_category_master sc ON dd.document_category_id = sc.category_id
        WHERE $where_sql
        ORDER BY dd.delivery_date DESC
        LIMIT 100";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$deliveries = [];
while ($row = $result->fetch_assoc()) {
    $deliveries[] = $row;
}

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN delivery_type = 'ส่ง' THEN 1 ELSE 0 END) as send_count,
    SUM(CASE WHEN delivery_type = 'รับ' THEN 1 ELSE 0 END) as receive_count,
    AVG(satisfaction_score) as avg_score
    FROM document_delivery";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Handle null values
$stats['total'] = $stats['total'] ?? 0;
$stats['send_count'] = $stats['send_count'] ?? 0;
$stats['receive_count'] = $stats['receive_count'] ?? 0;
$stats['avg_score'] = $stats['avg_score'] ?? 0;

$stmt->close();
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 lg:ml-64 p-6">

        <!-- Page Header -->
        <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Document Delivery List</h1>
                        <p class="text-green-100 mt-1">ตรวจสอบรายการส่งและรับเอกสารทั้งหมด</p>
                    </div>
                </div>
            </div>
        </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="<?php echo $card_bg; ?> p-6 rounded-lg border <?php echo $border_class; ?> shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">ทั้งหมด</p>
                    <p class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="<?php echo $card_bg; ?> p-6 rounded-lg border <?php echo $border_class; ?> shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">ส่งเอกสาร</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['send_count']); ?></p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="<?php echo $card_bg; ?> p-6 rounded-lg border <?php echo $border_class; ?> shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">รับเอกสาร</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['receive_count']); ?></p>
                </div>
                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="<?php echo $card_bg; ?> p-6 rounded-lg border <?php echo $border_class; ?> shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">คะแนนเฉลี่ย</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        <?php echo number_format($stats['avg_score'], 1); ?> ★
                    </p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm p-6 mb-6 border <?php echo $border_class; ?>">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">วันที่เริ่มต้น</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                    class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?>">
            </div>

            <div>
                <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">วันที่สิ้นสุด</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                    class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?>">
            </div>

            <div>
                <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">ประเภท</label>
                <select name="delivery_type" class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?>">
                    <option value="all" <?php echo $delivery_type === 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                    <option value="ส่ง" <?php echo $delivery_type === 'ส่ง' ? 'selected' : ''; ?>>ส่งเอกสาร</option>
                    <option value="รับ" <?php echo $delivery_type === 'รับ' ? 'selected' : ''; ?>>รับเอกสาร</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    ค้นหา
                </button>
                <a href="?=" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    ล้าง
                </a>
            </div>
        </form>
    </div>

    <!-- Deliveries Table -->
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm border <?php echo $border_class; ?> overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?>">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">#</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">วันที่-เวลา</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">รหัสพนักงาน</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">ชื่อพนักงาน</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase">ประเภท</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">เอกสาร</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase">คะแนน</th>
                    </tr>
                </thead>
                <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                    <?php if (empty($deliveries)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                ไม่พบข้อมูล
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($deliveries as $d): ?>
                            <tr class="hover:<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?> transition">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm <?php echo $text_class; ?>">#<?php echo $d['delivery_id']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="<?php echo $text_class; ?> text-sm">
                                        <?php echo date('d/m/Y H:i', strtotime($d['delivery_date'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono <?php echo $text_class; ?>"><?php echo htmlspecialchars($d['employee_id']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $text_class; ?> font-medium"><?php echo htmlspecialchars($d['full_name_th']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($d['delivery_type'] === 'ส่ง'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            ส่งเอกสาร
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            รับเอกสาร
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $text_class; ?> text-sm"><?php echo htmlspecialchars($d['category_name_th']); ?></span>
                                    <?php if ($d['service_type'] === 'กลุ่ม'): ?>
                                        <span class="ml-2 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 px-2 py-1 rounded">กลุ่ม</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-yellow-500 font-medium">
                                        <?php echo str_repeat('★', $d['satisfaction_score']) . str_repeat('☆', 5 - $d['satisfaction_score']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>