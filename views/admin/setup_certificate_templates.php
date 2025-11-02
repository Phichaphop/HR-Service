<?php
/**
 * Setup Certificate Templates
 * File: /views/admin/setup_certificate_templates.php
 * Purpose: Admin ตั้งค่า Template สำหรับแต่ละประเภทหนังสือรับรอง
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Require admin role
AuthController::requireRole(['admin']);

// Get session data
$current_lang = $_SESSION['language'] ?? 'th';
$user_role = $_SESSION['role'] ?? 'employee';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';

// Define theme classes
$is_dark = ($theme_mode === 'dark');
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-900';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-300';

// Multi-language text
$texts = [
    'th' => [
        'page_title' => 'ตั้งค่า Template หนังสือรับรอง',
        'page_subtitle' => 'กำหนด HTML Template สำหรับแต่ละประเภทหนังสือรับรอง',
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
        'placeholder_salary_text' => 'เงินเดือน (ตัวอักษรไทย)',
        'placeholder_cert_no' => 'หมายเลขใบรับรอง',
        'placeholder_issued_date' => 'วันที่ออกใบรับรอง',
        'placeholder_company' => 'ชื่อบริษัท',
        'placeholder_address' => 'ที่อยู่บริษัท',
        'placeholder_phone' => 'เบอร์โทรศัพท์',
        'preview' => 'ตัวอย่าง',
        'save' => 'บันทึก',
        'cancel' => 'ยกเลิก',
        'example' => 'ตัวอย่าง Template',
        'instructions' => 'ใช้ตัวแปรด้านล่างเพื่อสร้าง Template',
        'insert_placeholder' => 'แทรกตัวแปร',
        'preview_result' => 'ผลลัพธ์ตัวอย่าง',
    ],
    'en' => [
        'page_title' => 'Certificate Template Setup',
        'page_subtitle' => 'Configure HTML template for each certificate type',
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
        'save' => 'Save',
        'cancel' => 'Cancel',
        'example' => 'Template Example',
        'instructions' => 'Use variables below to create template',
        'insert_placeholder' => 'Insert Variable',
        'preview_result' => 'Preview Result',
    ]
];

$t = $texts[$current_lang] ?? $texts['th'];

// Database connection
$conn = getDbConnection();

// Get all certificate types
$cert_types = [];
$result = $conn->query("SELECT * FROM certificate_types ORDER BY cert_type_id");
while ($row = $result->fetch_assoc()) {
    $cert_types[] = $row;
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 lg:ml-64 min-h-screen <?php echo $bg_class; ?>">
    
    <!-- Page Container -->
    <div class="p-4 md:p-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h1 class="text-3xl md:text-4xl font-bold <?php echo $text_class; ?>"><?php echo $t['page_title']; ?></h1>
            </div>
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> ml-11"><?php echo $t['page_subtitle']; ?></p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Type Selector -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-md p-6 h-fit">
                <h3 class="text-lg font-bold mb-4 <?php echo $text_class; ?>">📋 <?php echo $t['select_type']; ?></h3>
                
                <div class="space-y-2 max-h-[500px] overflow-y-auto">
                    <?php foreach ($cert_types as $type): ?>
                        <button onclick="loadTemplate(<?php echo $type['cert_type_id']; ?>, '<?php echo addslashes($type['type_name_th']); ?>')"
                                class="w-full text-left px-4 py-3 rounded-lg border transition cert-type-btn hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> <?php echo $border_class; ?>"
                                data-type-id="<?php echo $type['cert_type_id']; ?>">
                            <div class="font-medium <?php echo $text_class; ?>"><?php echo htmlspecialchars($type['type_name_th']); ?></div>
                            <div class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>"><?php echo htmlspecialchars($type['type_name_en'] ?? ''); ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Middle Column: Template Editor -->
            <div class="lg:col-span-1">
                <div class="<?php echo $card_bg; ?> rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 <?php echo $text_class; ?>">✏️ <?php echo $t['template_content']; ?></h3>
                    
                    <textarea id="templateContent" 
                              placeholder="<?php echo $t['example']; ?>"
                              rows="16"
                              class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> <?php echo $border_class; ?> focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                              spellcheck="false"></textarea>
                    
                    <div class="flex gap-2 mt-4">
                        <button onclick="previewTemplate()" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <?php echo $t['preview']; ?>
                        </button>
                        <button onclick="saveTemplate()" 
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-medium transition flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <?php echo $t['save']; ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Placeholders & Preview -->
            <div class="lg:col-span-1">
                <!-- Placeholders Panel -->
                <div class="<?php echo $card_bg; ?> rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-bold mb-4 <?php echo $text_class; ?>">🔤 <?php echo $t['available_placeholders']; ?></h3>
                    
                    <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-4"><?php echo $t['instructions']; ?></p>
                    
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
                            '{base_salary_text}' => $t['placeholder_salary_text'],
                            '{certificate_no}' => $t['placeholder_cert_no'],
                            '{issued_date}' => $t['placeholder_issued_date'],
                            '{company_name}' => $t['placeholder_company'],
                            '{company_address}' => $t['placeholder_address'],
                            '{company_phone}' => $t['placeholder_phone'],
                        ];
                        
                        foreach ($placeholders as $code => $label):
                        ?>
                            <button onclick="insertPlaceholder('<?php echo $code; ?>')" 
                                    class="px-3 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-medium hover:bg-blue-200 dark:hover:bg-blue-900/50 transition"
                                    title="<?php echo htmlspecialchars($label); ?>">
                                <?php echo htmlspecialchars($code); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="<?php echo $card_bg; ?> rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 <?php echo $text_class; ?>">👁️ <?php echo $t['preview_result']; ?></h3>
                    <div id="previewContent" 
                         class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg p-4 min-h-[400px] border <?php echo $border_class; ?> overflow-auto">
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-sm">กรุณากด "ตัวอย่าง" เพื่อดูผลลัพธ์</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Placeholder preview data
const previewData = {
    employee_name: 'สมชาย ใจดี',
    employee_id: '90681322',
    position: 'Software Engineer',
    division: 'Information Technology',
    date_of_hire: '01 มกราคม 2563',
    hiring_type: 'Full Time',
    base_salary: '50,000.00',
    base_salary_text: 'ห้าหมื่นบาทถ้วน',
    certificate_no: 'CERT-20251029-0001',
    issued_date: '29 ตุลาคม 2568',
    company_name: 'บริษัท ตัวอย่าง จำกัด',
    company_address: '123 ถนนโปรแกรมเมอร์ กรุงเทพฯ',
    company_phone: '02-123-4567',
};

let currentTypeId = null;

// Insert placeholder ที่ cursor
function insertPlaceholder(placeholder) {
    const textarea = document.getElementById('templateContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
}

// Load template เมื่อเลือกประเภท
function loadTemplate(typeId, typeName) {
    currentTypeId = typeId;
    
    // Update button styling
    document.querySelectorAll('.cert-type-btn').forEach(btn => {
        btn.classList.remove('<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?>', 'border-blue-500', 'font-semibold');
        btn.classList.add('<?php echo $border_class; ?>');
    });
    
    const selectedBtn = document.querySelector(`[data-type-id="${typeId}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?>', 'border-blue-500', 'font-semibold');
    }
    
    // Fetch template content
    fetch(`<?php echo BASE_PATH; ?>/api/generate_certificate_form.php?cert_type_id=${typeId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('templateContent').value = data.template_content || '';
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to load template');
        });
}

// Preview template dengan sample data
function previewTemplate() {
    const template = document.getElementById('templateContent').value.trim();
    if (!template) {
        alert('กรุณากรอก Template ก่อน');
        return;
    }
    
    let html = template;
    for (const [key, value] of Object.entries(previewData)) {
        html = html.replace(new RegExp('{' + key + '}', 'g'), value);
    }
    
    document.getElementById('previewContent').innerHTML = `
        <div style="font-family: 'Sarabun', sans-serif; line-height: 1.8; color: #333;">
            ${html}
        </div>
    `;
}

// Save template
function saveTemplate() {
    if (!currentTypeId) {
        alert('กรุณาเลือกประเภทหนังสือรับรองก่อน');
        return;
    }
    
    const templateContent = document.getElementById('templateContent').value.trim();
    if (!templateContent) {
        alert('กรุณากรอก Template');
        return;
    }
    
    fetch(`<?php echo BASE_PATH; ?>/api/update_certificate_template.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cert_type_id: currentTypeId,
            template_content: templateContent
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Template บันทึกสำเร็จ!', 'success');
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to save template');
    });
}
</script>

<!-- Include CSS for tooltip -->
<style>
    [title] {
        position: relative;
    }
</style>

<?php 
// Include Footer if exists
if (file_exists(__DIR__ . '/../../includes/footer.php')) {
    include __DIR__ . '/../../includes/footer.php';
}
?>