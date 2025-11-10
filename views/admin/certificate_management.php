<?php
/**
 * Unified Certificate Management
 * File: /views/admin/certificate_management.php
 * Layout: Matches Request Certificate Form design
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

AuthController::requireRole(['admin']);

$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-900';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';

$texts = [
    'th' => [
        'page_title' => 'จัดการหนังสือรับรอง',
        'page_subtitle' => 'จัดการประเภทและ Template หนังสือรับรอง',
        'tab_types' => 'ประเภท',
        'tab_templates' => 'Template',
        'add_type' => 'เพิ่มประเภท',
        'manage_types' => 'จัดการประเภท',
        'name_th' => 'ชื่อ (ไทย)',
        'name_en' => 'ชื่อ (English)',
        'name_my' => 'ชื่อ (Myanmar)',
        'status' => 'สถานะ',
        'actions' => 'การจัดการ',
        'active' => 'ใช้งาน',
        'inactive' => 'ไม่ใช้งาน',
        'edit' => 'แก้ไข',
        'delete' => 'ลบ',
        'save' => 'บันทึก',
        'cancel' => 'ยกเลิก',
        'no_data' => 'ยังไม่มีข้อมูล',
        'select_type' => 'เลือกประเภท',
        'template_content' => 'เนื้อหา Template',
        'available_placeholders' => 'ตัวแปรที่ใช้ได้',
        'placeholder_name' => 'ชื่อพนักงาน',
        'placeholder_id' => 'รหัสพนักงาน',
        'placeholder_position' => 'ตำแหน่ง',
        'placeholder_division' => 'สังกัด',
        'placeholder_hire_date' => 'วันที่เข้าทำงาน',
        'placeholder_hiring_type' => 'ประเภทการจ้าง',
        'placeholder_salary' => 'เงินเดือน',
        'placeholder_salary_text' => 'เงินเดือน (ตัวอักษร)',
        'placeholder_cert_no' => 'หมายเลขใบรับรอง',
        'placeholder_issued_date' => 'วันที่ออกใบรับรอง',
        'placeholder_company' => 'ชื่อบริษัท',
        'placeholder_address' => 'ที่อยู่บริษัท',
        'placeholder_phone' => 'เบอร์โทรศัพท์',
        'preview' => 'ตัวอย่าง',
        'instructions' => 'ใช้ตัวแปรด้านล่างเพื่อสร้าง Template',
        'preview_result' => 'ผลลัพธ์ตัวอย่าง',
        'template_saved' => 'Template บันทึกสำเร็จ',
        'required' => 'จำเป็น',
    ],
    'en' => [
        'page_title' => 'Certificate Management',
        'page_subtitle' => 'Manage certificate types and templates',
        'tab_types' => 'Types',
        'tab_templates' => 'Templates',
        'add_type' => 'Add Type',
        'manage_types' => 'Manage Types',
        'name_th' => 'Name (Thai)',
        'name_en' => 'Name (English)',
        'name_my' => 'Name (Myanmar)',
        'status' => 'Status',
        'actions' => 'Actions',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'no_data' => 'No data',
        'select_type' => 'Select Type',
        'template_content' => 'Template Content',
        'available_placeholders' => 'Available Variables',
        'placeholder_name' => 'Employee Name',
        'placeholder_id' => 'Employee ID',
        'placeholder_position' => 'Position',
        'placeholder_division' => 'Division',
        'placeholder_hire_date' => 'Date of Hire',
        'placeholder_hiring_type' => 'Hiring Type',
        'placeholder_salary' => 'Base Salary',
        'placeholder_salary_text' => 'Salary in Thai Text',
        'placeholder_cert_no' => 'Certificate No.',
        'placeholder_issued_date' => 'Issued Date',
        'placeholder_company' => 'Company Name',
        'placeholder_address' => 'Company Address',
        'placeholder_phone' => 'Phone',
        'preview' => 'Preview',
        'instructions' => 'Use variables below to create template',
        'preview_result' => 'Preview Result',
        'template_saved' => 'Template saved successfully',
        'required' => 'Required',
    ]
];

$t = $texts[$current_lang] ?? $texts['th'];

$conn = getDbConnection();
$cert_types = [];
$result = $conn->query("SELECT * FROM certificate_types ORDER BY cert_type_id DESC");
while ($row = $result->fetch_assoc()) {
    $cert_types[] = $row;
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-6xl">
        
        <!-- Page Header -->
        <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-blue-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex border-b <?php echo $border_class; ?> mb-6 bg-white dark:bg-gray-800 rounded-t-lg px-6 py-4">
            <button onclick="switchTab('types')" 
                    id="tab-types-btn"
                    class="px-6 py-2 font-medium border-b-2 border-blue-600 text-blue-600 transition tab-btn">
                📋 <?php echo $t['tab_types']; ?>
            </button>
            <button onclick="switchTab('templates')" 
                    id="tab-templates-btn"
                    class="px-6 py-2 font-medium border-b-2 border-transparent <?php echo $text_class; ?> hover:text-blue-600 transition tab-btn">
                📄 <?php echo $t['tab_templates']; ?>
            </button>
        </div>

        <!-- ============ TAB 1: CERTIFICATE TYPES ============ -->
        <div id="types-section" class="tab-content <?php echo $card_bg; ?> rounded-b-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6 pb-6 border-b <?php echo $border_class; ?>">
                <h2 class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo $t['manage_types']; ?></h2>
                <button onclick="openTypeModal()" 
                        class="flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <?php echo $t['add_type']; ?>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?>">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['name_th']; ?></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['name_en']; ?></th>
                            <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['status']; ?></th>
                            <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y <?php echo $border_class; ?>">
                        <?php if (empty($cert_types)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    ℹ️ <?php echo $t['no_data']; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cert_types as $type): ?>
                                <tr class="hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition">
                                    <td class="px-6 py-4 text-sm font-mono">#<?php echo $type['cert_type_id']; ?></td>
                                    <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($type['type_name_th']); ?></td>
                                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($type['type_name_en'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($type['is_active']): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">✓ <?php echo $t['active']; ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">✕ <?php echo $t['inactive']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick='editType(<?php echo json_encode($type); ?>)' 
                                                class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteType(<?php echo $type['cert_type_id']; ?>, '<?php echo htmlspecialchars($type['type_name_th']); ?>')" 
                                                class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition">
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

        <!-- ============ TAB 2: TEMPLATES ============ -->
        <div id="templates-section" class="tab-content hidden <?php echo $card_bg; ?> rounded-b-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold <?php echo $text_class; ?> mb-6 pb-6 border-b <?php echo $border_class; ?>">📝 <?php echo $t['template_content']; ?></h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left: Type Selector -->
                <div class="lg:col-span-1">
                    <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4">📋 <?php echo $t['select_type']; ?></h3>
                    <div class="space-y-2 max-h-[500px] overflow-y-auto">
                        <?php if (empty($cert_types)): ?>
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">ℹ️ <?php echo $t['no_data']; ?></p>
                        <?php else: ?>
                            <?php foreach ($cert_types as $type): ?>
                                <button onclick="loadTemplate(<?php echo $type['cert_type_id']; ?>)" 
                                        class="w-full text-left px-4 py-3 rounded-lg border transition cert-type-btn hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> <?php echo $border_class; ?>"
                                        data-type-id="<?php echo $type['cert_type_id']; ?>">
                                    <div class="font-medium <?php echo $text_class; ?>"><?php echo htmlspecialchars($type['type_name_th']); ?></div>
                                    <div class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>"><?php echo htmlspecialchars($type['type_name_en'] ?? ''); ?></div>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Middle: Template Editor -->
                <div class="lg:col-span-1">
                    <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4">✏️ <?php echo $t['template_content']; ?></h3>
                    <textarea id="templateContent" 
                              placeholder="<?php echo $t['template_content']; ?>"
                              rows="16"
                              class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                              spellcheck="false"></textarea>
                    
                    <div class="flex gap-2 mt-4 flex-wrap">
                        <button onclick="previewTemplate()" 
                                class="flex-1 min-w-[120px] bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <?php echo $t['preview']; ?>
                        </button>
                        <button onclick="saveTemplate()" 
                                class="flex-1 min-w-[120px] bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-medium transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <?php echo $t['save']; ?>
                        </button>
                    </div>
                </div>

                <!-- Right: Placeholders & Preview -->
                <div class="lg:col-span-1">
                    <!-- Placeholders -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4">🔤 <?php echo $t['available_placeholders']; ?></h3>
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-3"><?php echo $t['instructions']; ?></p>
                        
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $placeholders = [
                                '{employee_name}' => $t['placeholder_name'],
                                '{employee_id}' => $t['placeholder_id'],
                                '{position}' => $t['placeholder_position'],
                                '{division}' => $t['placeholder_division'],
                                '{date_of_hire}' => $t['placeholder_hire_date'],
                                '{hiring_type}' => $t['placeholder_hiring_type'],
                                '{base_salary}' => $t['placeholder_salary'],
                                '{company_name}' => $t['placeholder_company'],
                                '{issued_date}' => $t['placeholder_issued_date'],
                            ];
                            
                            foreach ($placeholders as $code => $label):
                            ?>
                                <button onclick="insertPlaceholder('<?php echo $code; ?>')" 
                                        class="px-3 py-2 bg-blue-100 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-200 transition"
                                        title="<?php echo htmlspecialchars($label); ?>">
                                    <?php echo htmlspecialchars($code); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div>
                        <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4">👁️ <?php echo $t['preview_result']; ?></h3>
                        <div id="previewContent" 
                             class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg p-4 min-h-[300px] border <?php echo $border_class; ?> overflow-auto text-sm">
                            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">ℹ️ กด "<?php echo $t['preview']; ?>" เพื่อดูผลลัพธ์</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Add/Edit Type -->
<div id="typeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold <?php echo $text_class; ?>" id="modalTitle"><?php echo $t['add_type']; ?></h3>
                <button onclick="closeTypeModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="typeForm" onsubmit="saveType(event)">
                <input type="hidden" id="cert_type_id" name="cert_type_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['name_th']; ?> <span class="text-red-500">*</span></label>
                        <input type="text" id="type_name_th" name="type_name_th" required
                            class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['name_en']; ?></label>
                        <input type="text" id="type_name_en" name="type_name_en"
                            class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['name_my']; ?></label>
                        <input type="text" id="type_name_my" name="type_name_my"
                            class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked
                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="ml-2 text-sm <?php echo $text_class; ?>"><?php echo $t['active']; ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6 pt-6 border-t <?php echo $border_class; ?>">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-medium">
                        <?php echo $t['save']; ?>
                    </button>
                    <button type="button" onclick="closeTypeModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-medium">
                        <?php echo $t['cancel']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const previewData = {
    employee_name: 'สมชาย ใจดี',
    employee_id: '90681322',
    position: 'Software Engineer',
    division: 'Information Technology',
    date_of_hire: '01 มกราคม 2563',
    hiring_type: 'Full Time',
    base_salary: '50,000.00',
    company_name: 'บริษัท ตัวอย่าง จำกัด',
    issued_date: '29 ตุลาคม 2568',
};

let currentTypeId = null;

function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-blue-600', 'text-blue-600');
        el.classList.add('border-transparent');
    });
    
    document.getElementById(tabName + '-section').classList.remove('hidden');
    document.getElementById('tab-' + tabName + '-btn').classList.add('border-blue-600', 'text-blue-600');
}

function openTypeModal() {
    document.getElementById('modalTitle').textContent = '<?php echo $t["add_type"]; ?>';
    document.getElementById('typeForm').reset();
    document.getElementById('cert_type_id').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('typeModal').classList.remove('hidden');
}

function editType(type) {
    document.getElementById('modalTitle').textContent = '<?php echo $t["edit"]; ?> ' + type.type_name_th;
    document.getElementById('cert_type_id').value = type.cert_type_id;
    document.getElementById('type_name_th').value = type.type_name_th;
    document.getElementById('type_name_en').value = type.type_name_en || '';
    document.getElementById('type_name_my').value = type.type_name_my || '';
    document.getElementById('is_active').checked = type.is_active == 1;
    document.getElementById('typeModal').classList.remove('hidden');
}

function closeTypeModal() {
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
        is_active: formData.get('is_active') ? 1 : 0
    };
    
    fetch('<?php echo BASE_PATH; ?>/api/save_certificate_type.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            showToast('<?php echo $t["save_success"]; ?>', 'success');
            closeTypeModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('Error: ' + result.message, 'error');
        }
    })
    .catch(err => showToast('<?php echo $t["error_occurred"]; ?>', 'error'));
}

function deleteType(id, name) {
    if (!confirm('<?php echo $t["confirm_delete"]; ?> "' + name + '" หรือไม่?')) return;
    
    fetch('<?php echo BASE_PATH; ?>/api/delete_certificate_type.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({cert_type_id: id})
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            showToast('<?php echo $t["delete_success"]; ?>', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('Error: ' + result.message, 'error');
        }
    })
    .catch(err => showToast('<?php echo $t["error_occurred"]; ?>', 'error'));
}

function insertPlaceholder(placeholder) {
    const textarea = document.getElementById('templateContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
}

function loadTemplate(typeId) {
    currentTypeId = typeId;
    
    document.querySelectorAll('.cert-type-btn').forEach(btn => {
        btn.classList.remove('<?php echo $is_dark ? "bg-gray-700" : "bg-blue-50"; ?>', 'border-blue-500');
    });
    
    const selectedBtn = document.querySelector(`[data-type-id="${typeId}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('<?php echo $is_dark ? "bg-gray-700" : "bg-blue-50"; ?>', 'border-blue-500');
    }
    
    fetch('<?php echo BASE_PATH; ?>/api/get_certificate_template.php?cert_type_id=' + typeId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('templateContent').value = data.template_content || '';
            }
        })
        .catch(err => console.error('Error:', err));
}

function previewTemplate() {
    const template = document.getElementById('templateContent').value.trim();
    if (!template) {
        showToast('<?php echo $t["template_content"]; ?>', 'warning');
        return;
    }
    
    let html = template;
    for (const [key, value] of Object.entries(previewData)) {
        html = html.replace(new RegExp('{' + key + '}', 'g'), value);
    }
    
    document.getElementById('previewContent').innerHTML = `<div style="font-family: 'Sarabun', Arial; line-height: 1.8;">${html}</div>`;
}

function saveTemplate() {
    if (!currentTypeId) {
        showToast('<?php echo $t["select_type"]; ?>', 'warning');
        return;
    }
    
    const content = document.getElementById('templateContent').value.trim();
    if (!content) {
        showToast('<?php echo $t["template_content"]; ?>', 'warning');
        return;
    }
    
    fetch('<?php echo BASE_PATH; ?>/api/update_certificate_template.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            cert_type_id: currentTypeId,
            template_content: content
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('<?php echo $t["template_saved"]; ?>', 'success');
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(err => showToast('<?php echo $t["error_occurred"]; ?>', 'error'));
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>