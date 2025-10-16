<?php
/**
 * Certificate Types Management
 * Admin only - Manage certificate types
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

AuthController::requireRole(['admin']);

$page_title = 'Manage Certificate Types';
extract(get_theme_vars());

$conn = getDbConnection();
if (!$conn) {
    die("Database connection failed");
}

// Get all certificate types
$result = $conn->query("SELECT * FROM certificate_types ORDER BY cert_type_id DESC");
$cert_types = [];
while ($row = $result->fetch_assoc()) {
    $cert_types[] = $row;
}

$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 lg:ml-64 p-6">
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold <?php echo $text_class; ?> flex items-center">
                    <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    จัดการประเภทหนังสือรับรอง
                </h1>
                <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                    เพิ่ม ลบ แก้ไข ประเภทหนังสือรับรอง
                </p>
            </div>
            <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                เพิ่มประเภทใหม่
            </button>
        </div>
    </div>

    <!-- Certificate Types Table -->
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm border <?php echo $border_class; ?> overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?>">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">ชื่อประเภท (ไทย)</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">ชื่อประเภท (อังกฤษ)</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase">สถานะ</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                    <?php if (empty($cert_types)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                ยังไม่มีประเภทหนังสือรับรอง
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cert_types as $type): ?>
                            <tr class="hover:<?php echo $is_dark ? 'bg-gray-800' : 'bg-gray-50'; ?> transition">
                                <td class="px-6 py-4">
                                    <span class="font-mono <?php echo $text_class; ?>">#<?php echo $type['cert_type_id']; ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $text_class; ?> font-medium"><?php echo htmlspecialchars($type['type_name_th']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $text_class; ?>"><?php echo htmlspecialchars($type['type_name_en']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($type['is_active']): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            ใช้งาน
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            ไม่ใช้งาน
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick='editType(<?php echo json_encode($type); ?>)' 
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="deleteType(<?php echo $type['cert_type_id']; ?>, '<?php echo htmlspecialchars($type['type_name_th']); ?>')" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
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

<!-- Add/Edit Modal -->
<div id="typeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold <?php echo $text_class; ?>" id="modalTitle">เพิ่มประเภทหนังสือรับรอง</h3>
                <button onclick="closeModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="typeForm" onsubmit="saveType(event)">
                <input type="hidden" id="cert_type_id" name="cert_type_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">ชื่อประเภท (ไทย) *</label>
                        <input type="text" id="type_name_th" name="type_name_th" required
                            class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">ชื่อประเภท (English)</label>
                        <input type="text" id="type_name_en" name="type_name_en"
                            class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">ชื่อประเภท (Myanmar)</label>
                        <input type="text" id="type_name_my" name="type_name_my"
                            class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">เนื้อหาเทมเพลต *</label>
                        <div class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-2">
                            ใช้ตัวแปร: {employee_name}, {employee_id}, {position}, {division}, {date_of_hire}, {base_salary}
                        </div>
                        <textarea id="template_content" name="template_content" rows="6" required
                            class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked
                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="ml-2 text-sm <?php echo $text_class; ?>">ใช้งาน</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-medium">
                        บันทึก
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-medium">
                        ยกเลิก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มประเภทหนังสือรับรอง';
    document.getElementById('typeForm').reset();
    document.getElementById('cert_type_id').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('typeModal').classList.remove('hidden');
}

function editType(type) {
    document.getElementById('modalTitle').textContent = 'แก้ไขประเภทหนังสือรับรอง';
    document.getElementById('cert_type_id').value = type.cert_type_id;
    document.getElementById('type_name_th').value = type.type_name_th;
    document.getElementById('type_name_en').value = type.type_name_en || '';
    document.getElementById('type_name_my').value = type.type_name_my || '';
    document.getElementById('template_content').value = type.template_content || '';
    document.getElementById('is_active').checked = type.is_active == 1;
    document.getElementById('typeModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('typeModal').classList.add('hidden');
}

function saveType(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        cert_type_id: formData.get('cert_type_id'),
        type_name_th: formData.get('type_name_th'),
        type_name_en: formData.get('type_name_en'),
        type_name_my: formData.get('type_name_my'),
        template_content: formData.get('template_content'),
        is_active: formData.get('is_active') ? 1 : 0
    };
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'กำลังบันทึก...';
    
    fetch('<?php echo BASE_PATH; ?>/api/save_certificate_type.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('บันทึกสำเร็จ', 'success');
            closeModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('Error: ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'บันทึก';
        }
    })
    .catch(error => {
        showToast('เกิดข้อผิดพลาด', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'บันทึก';
    });
}

function deleteType(id, name) {
    if (!confirm(`ต้องการลบประเภท "${name}" หรือไม่?`)) {
        return;
    }
    
    fetch('<?php echo BASE_PATH; ?>/api/delete_certificate_type.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cert_type_id: id })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('ลบสำเร็จ', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('Error: ' + result.message, 'error');
        }
    })
    .catch(error => {
        showToast('เกิดข้อผิดพลาด', 'error');
    });
}

// Close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>