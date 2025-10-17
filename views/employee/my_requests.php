<?php
/**
 * My Requests Page - Complete Version
 * Employee can view all their requests
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

AuthController::requireAuth();

$page_title = 'คำขอของฉัน';
extract(get_theme_vars());

$conn = getDbConnection();

// Get all requests for this user
$all_requests = [];

// Query each table
$tables = [
    'leave_requests' => 'ใบลา',
    'certificate_requests' => 'หนังสือรับรอง',
    'id_card_requests' => 'บัตรพนักงาน',
    'shuttle_bus_requests' => 'รถรับส่ง',
    'locker_requests' => 'ตู้ล็อกเกอร์',
    'supplies_requests' => 'วัสดุสำนักงาน',
    'skill_test_requests' => 'ทดสอบทักษะ'
];

foreach ($tables as $table => $type_name) {
    $sql = "SELECT request_id, status, created_at, satisfaction_score, '$type_name' as request_type, '$table' as source_table 
            FROM $table 
            WHERE employee_id = ? 
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $all_requests[] = $row;
    }
    $stmt->close();
}

// Sort by date
usort($all_requests, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 lg:ml-64 p-6">

        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <h1 class="text-3xl font-bold text-white">My Request</h1>
                        <p class="text-green-100 mt-1">Manage Your Request</p>
                    </div>
                </div>
            </div>
        </div>

    <!-- Requests Table -->
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm border <?php echo $border_class; ?> overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?>">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">#</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">ประเภท</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">วันที่ส่ง</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase">สถานะ</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase">คะแนน</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                    <?php if (empty($all_requests)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-lg font-medium">ยังไม่มีคำขอ</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_requests as $req): ?>
                            <tr class="hover:<?php echo $is_dark ? 'bg-gray-750' : 'bg-gray-50'; ?> transition">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm <?php echo $text_class; ?>">#<?php echo str_pad($req['request_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <?php echo htmlspecialchars($req['request_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $text_class; ?> text-sm">
                                        <?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php
                                    $status_colors = [
                                        'New' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'Complete' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    ];
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$req['status']] ?? ''; ?>">
                                        <?php echo htmlspecialchars($req['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($req['status'] === 'Complete' && !empty($req['satisfaction_score'])): ?>
                                        <span class="text-yellow-500 font-medium">
                                            <?php echo str_repeat('★', $req['satisfaction_score']) . str_repeat('☆', 5 - $req['satisfaction_score']); ?>
                                        </span>
                                    <?php elseif ($req['status'] === 'Complete'): ?>
                                        <button onclick="rateRequest(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')" 
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm underline">
                                            ให้คะแนน
                                        </button>
                                    <?php else: ?>
                                        <span class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="viewDetails(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')" 
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                                            ดูรายละเอียด
                                        </button>
                                        <?php if ($req['status'] === 'New'): ?>
                                            <span class="text-gray-300">|</span>
                                            <button onclick="cancelRequest(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')" 
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium text-sm">
                                                ยกเลิก
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
</div>

<!-- View Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold <?php echo $text_class; ?>">รายละเอียดคำขอ</h3>
                <button onclick="closeModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="detailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div id="ratingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold <?php echo $text_class; ?>">ให้คะแนนความพึงพอใจ</h3>
                <button onclick="closeRatingModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="ratingForm" onsubmit="submitRating(event)">
                <input type="hidden" id="rating_request_id">
                <input type="hidden" id="rating_table">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-3 text-center">คะแนน (1-5 ดาว)</label>
                    <div class="flex justify-center space-x-2">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="score" value="<?php echo $i; ?>" required class="sr-only peer">
                                <svg class="w-12 h-12 text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-300 transition" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">ความคิดเห็นเพิ่มเติม</label>
                    <textarea name="feedback" rows="3" 
                        class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500"
                        placeholder="แสดงความคิดเห็น (ถ้ามี)"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-medium">
                        ส่งคะแนน
                    </button>
                    <button type="button" onclick="closeRatingModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-medium">
                        ยกเลิก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetails(id, table) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('detailsContent');
    
    content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div></div>';
    modal.classList.remove('hidden');
    
    fetch(`<?php echo BASE_PATH; ?>/api/get_request_details.php?id=${id}&table=${table}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = generateDetailsHTML(data.request, table);
            } else {
                content.innerHTML = `<p class="text-red-600">${data.message}</p>`;
            }
        })
        .catch(error => {
            content.innerHTML = '<p class="text-red-600">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>';
        });
}

function generateDetailsHTML(req, table) {
    let html = `<div class="space-y-4">`;
    
    // Common fields
    html += `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">Request ID</label>
                <p class="<?php echo $text_class; ?> font-mono">#${String(req.request_id).padStart(5, '0')}</p>
            </div>
            <div>
                <label class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">สถานะ</label>
                <p class="<?php echo $text_class; ?>">${req.status}</p>
            </div>
        </div>
        <div>
            <label class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">วันที่สร้าง</label>
            <p class="<?php echo $text_class; ?>">${new Date(req.created_at).toLocaleString('th-TH')}</p>
        </div>
    `;
    
    // Certificate specific fields
    if (table === 'certificate_requests' && req.cert_type_id) {
        html += `
            <div>
                <label class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">ประเภทหนังสือรับรอง</label>
                <p class="<?php echo $text_class; ?> font-medium">${req.cert_type_name || 'ระบุในระบบ'}</p>
            </div>
        `;
    }
    
    if (req.purpose) {
        html += `
            <div>
                <label class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">วัตถุประสงค์</label>
                <p class="<?php echo $text_class; ?>">${req.purpose}</p>
            </div>
        `;
    }
    
    if (req.handler_remarks) {
        html += `
            <div class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> p-4 rounded-lg">
                <label class="text-sm font-medium <?php echo $text_class; ?>">หมายเหตุจากเจ้าหน้าที่</label>
                <p class="<?php echo $text_class; ?> mt-1">${req.handler_remarks}</p>
            </div>
        `;
    }
    
    html += `</div>`;
    return html;
}

function closeModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function cancelRequest(id, table) {
    if (!confirm('ต้องการยกเลิกคำขอนี้หรือไม่?')) {
        return;
    }
    
    fetch('<?php echo BASE_PATH; ?>/api/cancel_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, table: table })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('ยกเลิกคำขอเรียบร้อยแล้ว', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('เกิดข้อผิดพลาด', 'error');
    });
}

function rateRequest(id, table) {
    document.getElementById('rating_request_id').value = id;
    document.getElementById('rating_table').value = table;
    document.getElementById('ratingModal').classList.remove('hidden');
}

function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
    document.getElementById('ratingForm').reset();
}

function submitRating(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        request_id: document.getElementById('rating_request_id').value,
        table: document.getElementById('rating_table').value,
        score: formData.get('score'),
        feedback: formData.get('feedback')
    };
    
    fetch('<?php echo BASE_PATH; ?>/api/submit_rating.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('ขอบคุณสำหรับการให้คะแนน!', 'success');
            closeRatingModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    })
    .catch(error => {
        showToast('เกิดข้อผิดพลาด', 'error');
    });
}

// Close modals on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeRatingModal();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>