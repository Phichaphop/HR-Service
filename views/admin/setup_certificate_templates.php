<!-- 
FILE: /views/admin/setup_certificate_templates.php
PURPOSE: Admin ตั้งค่า Template สำหรับแต่ละประเภทหนังสือรับรอง
FEATURES: 
  - Preview template ก่อนบันทึก
  - Dropdown placeholder ง่ายๆ
  - Multi-language support
-->

<?php
session_start();
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

AuthController::requireRole(['admin']);

$lang = $_SESSION['language'] ?? 'th';
$is_dark = isset($_COOKIE['theme_dark']) && $_COOKIE['theme_dark'] === 'true';

$conn = getDbConnection();

// Get all certificate types
$result = $conn->query("SELECT * FROM certificate_types ORDER BY cert_type_id");
$cert_types = [];
while ($row = $result->fetch_assoc()) {
    $cert_types[] = $row;
}

// Localized text
$texts = [
    'th' => [
        'title' => 'ตั้งค่า Template หนังสือรับรอง',
        'instructions' => 'ตั้งค่า Template HTML สำหรับแต่ละประเภทหนังสือรับรอง',
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
        'save_success' => 'บันทึก Template สำเร็จ',
        'copy_placeholder' => 'คัดลอก',
    ],
    'en' => [
        'title' => 'Certificate Template Setup',
        'instructions' => 'Configure HTML template for each certificate type',
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
        'save_success' => 'Template saved successfully',
        'copy_placeholder' => 'Copy',
    ]
];

$t = $texts[$lang] ?? $texts['th'];

// Sample template
$sample_template = <<<'HTML'
<div class="certificate-content">
    <h1 class="certificate-title">หนังสือรับรองเงินเดือน</h1>
    
    <p>ขอรับรองว่า <strong>{employee_name}</strong> รหัสพนักงาน <strong>{employee_id}</strong></p>
    
    <p>ปฏิบัติงานในตำแหน่ง <strong>{position}</strong> สังกัด <strong>{division}</strong></p>
    
    <p>มีฐานเงินเดือน <strong>{base_salary} บาท/เดือน</strong></p>
    
    <p>ข้อมูลข้างต้นเป็นความจริงทั้งสิ้น</p>
</div>
HTML;

$conn->close();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .placeholder-btn {
            padding: 8px 12px;
            margin: 4px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        .placeholder-btn:hover {
            background: #1976d2;
            color: white;
        }
        .preview-box {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background: #f9f9f9;
            min-height: 400px;
        }
        .preview-box p {
            line-height: 1.8;
            margin-bottom: 12px;
        }
    </style>
</head>
<body class="<?php echo $is_dark ? 'bg-gray-900 text-white' : 'bg-gray-50'; ?>">

<div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-2">📝 <?php echo $t['title']; ?></h1>
        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>"><?php echo $t['instructions']; ?></p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Type Selector -->
        <div class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-white'; ?> rounded-lg shadow-md p-6 h-fit">
            <h3 class="text-lg font-bold mb-4"><?php echo $t['select_type']; ?></h3>
            
            <div class="space-y-2">
                <?php foreach ($cert_types as $type): ?>
                    <button onclick="loadTemplate(<?php echo $type['cert_type_id']; ?>, '<?php echo addslashes($type['type_name_th']); ?>')"
                            class="w-full text-left px-4 py-3 rounded-lg border transition cert-type-btn <?php echo $is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-300 hover:bg-blue-50'; ?>"
                            data-type-id="<?php echo $type['cert_type_id']; ?>">
                        <div class="font-medium"><?php echo htmlspecialchars($type['type_name_th']); ?></div>
                        <div class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>"><?php echo htmlspecialchars($type['type_name_en']); ?></div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Middle: Template Editor -->
        <div class="lg:col-span-1">
            <div class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-white'; ?> rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold mb-4"><?php echo $t['template_content']; ?></h3>
                
                <textarea id="templateContent" 
                          placeholder="<?php echo $t['example']; ?>"
                          rows="15"
                          class="w-full px-4 py-2 border rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                          spellcheck="false"></textarea>
                
                <div class="flex gap-2 mt-4">
                    <button onclick="previewTemplate()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        👁️ <?php echo $t['preview']; ?>
                    </button>
                    <button onclick="saveTemplate()" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        💾 <?php echo $t['save']; ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Placeholders & Preview -->
        <div class="lg:col-span-1">
            <!-- Placeholders -->
            <div class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-white'; ?> rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold mb-4"><?php echo $t['available_placeholders']; ?></h3>
                
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
                                class="placeholder-btn"
                                title="<?php echo htmlspecialchars($label); ?>">
                            <?php echo htmlspecialchars($code); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Preview -->
            <div class="<?php echo $is_dark ? 'bg-gray-800' : 'bg-white'; ?> rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold mb-4">📋 <?php echo $t['preview']; ?></h3>
                <div id="previewContent" class="preview-box">
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">กรุณากด "ตัวอย่าง" เพื่อดูผลลัพธ์</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Placeholder data for preview
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

function insertPlaceholder(placeholder) {
    const textarea = document.getElementById('templateContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
}

function previewTemplate() {
    const template = document.getElementById('templateContent').value;
    if (!template) {
        alert('กรุณากรอก Template ก่อน');
        return;
    }
    
    let html = template;
    for (const [key, value] of Object.entries(previewData)) {
        html = html.replace(new RegExp('{' + key + '}', 'g'), value);
    }
    
    document.getElementById('previewContent').innerHTML = html;
}

function loadTemplate(typeId, typeName) {
    currentTypeId = typeId;
    
    // Mark as selected
    document.querySelectorAll('.cert-type-btn').forEach(btn => {
        btn.classList.remove('<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?>', 'border-blue-500');
        btn.classList.add('<?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>');
    });
    
    event.target.closest('.cert-type-btn').classList.add('<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?>', 'border-blue-500');
    
    // ส่งคำขอไปที่ API เพื่อดึง template
    console.log('Loading template for type:', typeId, typeName);
}

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
    
    fetch('/api/update_certificate_template.php', {
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
            alert('<?php echo $t['save_success']; ?>');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => alert('Error: ' + err));
}
</script>

</body>
</html>