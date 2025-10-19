<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Require admin role only
AuthController::requireRole(['admin']);

$page_title = 'Import Employees';

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Employees
            </a>
            <h1 class="text-3xl font-bold <?php echo $text_class; ?> mt-2">Import Employees</h1>
            <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                Bulk import employees from CSV file
            </p>
        </div>

        <!-- Instructions Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Instructions
            </h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold <?php echo $text_class; ?> mb-2">CSV Format Requirements:</h3>
                    <p class="text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        ระบบรองรับ 2 รูปแบบ:
                    </p>
                    
                    <div class="space-y-3">
                        <!-- Simple Format -->
                        <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> rounded">
                            <p class="font-medium text-blue-600 dark:text-blue-400 mb-1">📋 รูปแบบง่าย (5 Columns - ขั้นต่ำ)</p>
                            <ul class="list-disc list-inside space-y-1 text-xs <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                <li><strong>Employee ID</strong> - รหัสพนักงาน (ต้องไม่ซ้ำ)</li>
                                <li><strong>Full Name (Thai)</strong> - ชื่อ-นามสกุล ภาษาไทย</li>
                                <li><strong>Username</strong> - ชื่อผู้ใช้ (ต้องไม่ซ้ำ)</li>
                                <li><strong>Password</strong> - รหัสผ่าน (ขั้นต่ำ 6 ตัว)</li>
                                <li><strong>Role</strong> - บทบาท (admin/officer/employee)</li>
                            </ul>
                            <p class="text-xs mt-2 text-blue-600 dark:text-blue-400">
                                ⚠️ ฟิลด์อื่นๆ จะใช้ค่า default
                            </p>
                        </div>
                        
                        <!-- Full Format -->
                        <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-green-50'; ?> rounded">
                            <p class="font-medium text-green-600 dark:text-green-400 mb-1">📊 รูปแบบเต็ม (29 Columns - แนะนำ)</p>
                            <div class="grid grid-cols-2 gap-2 text-xs <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                <ul class="list-disc list-inside space-y-0.5">
                                    <li>Employee ID</li>
                                    <li>Prefix (คำนำหน้า)</li>
                                    <li>Full Name (Thai)</li>
                                    <li>Full Name (English)</li>
                                    <li>Sex (เพศ)</li>
                                    <li>Birthday (วันเกิด)</li>
                                    <li>Nationality (สัญชาติ)</li>
                                    <li>Education (การศึกษา)</li>
                                    <li>Phone (เบอร์โทร)</li>
                                    <li>Village (หมู่บ้าน)</li>
                                    <li>Subdistrict (ตำบล)</li>
                                    <li>District (อำเภอ)</li>
                                    <li>Province (จังหวัด)</li>
                                    <li>Function (สายงาน)</li>
                                </ul>
                                <ul class="list-disc list-inside space-y-0.5">
                                    <li>Division (แผนก)</li>
                                    <li>Department (ฝ่าย)</li>
                                    <li>Section (ส่วน)</li>
                                    <li>Operation (ปฏิบัติการ)</li>
                                    <li>Position (ตำแหน่ง)</li>
                                    <li>Position Level (ระดับ)</li>
                                    <li>Labour Cost (ประเภทแรง)</li>
                                    <li>Hiring Type (ประเภทการจ้าง)</li>
                                    <li>Zone (โซน)</li>
                                    <li>Contribution (การมีส่วนร่วม)</li>
                                    <li>Date of Hire (วันเริ่มงาน)</li>
                                    <li>Status (สถานะ)</li>
                                    <li>Username</li>
                                    <li>Password</li>
                                    <li>Role</li>
                                </ul>
                            </div>
                            <p class="text-xs mt-2 text-green-600 dark:text-green-400">
                                ✅ Import ข้อมูลครบทุกฟิลด์
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                    <p class="text-sm font-medium <?php echo $text_class; ?> mb-2">ตัวอย่างรูปแบบง่าย:</p>
                    <pre class="text-xs <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> overflow-x-auto">Employee ID,Full Name (Thai),Username,Password,Role
EMP001,นายสมชาย ใจดี,somchai.j,password123,employee
EMP002,นางสาวมาลี สวย,malee.s,secure456,officer</pre>
                </div>

                <div class="flex items-start p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-1">Important Notes:</p>
                        <ul class="text-xs text-yellow-700 dark:text-yellow-300 space-y-1 list-disc list-inside">
                            <li>The first row must be a header row (it will be skipped)</li>
                            <li>Employee ID and Username must be unique in the system</li>
                            <li>New employees will be created with default values for other fields</li>
                            <li>You can edit employee details after import</li>
                            <li>Existing Employee IDs will be skipped</li>
                            <li><strong class="text-yellow-900 dark:text-yellow-100">สำหรับภาษาไทย: บันทึกไฟล์เป็น CSV UTF-8 ใน Excel ให้เลือก "CSV UTF-8 (Comma delimited)"</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">💡 วิธีบันทึก CSV ที่รองรับภาษาไทย:</p>
                    <ol class="text-xs text-blue-700 dark:text-blue-300 space-y-1 list-decimal list-inside ml-2">
                        <li><strong>Microsoft Excel:</strong> File → Save As → เลือก "CSV UTF-8 (Comma delimited) (*.csv)"</li>
                        <li><strong>Google Sheets:</strong> File → Download → Comma Separated Values (.csv)</li>
                        <li><strong>LibreOffice Calc:</strong> Save As → เลือก "Text CSV" → Character Set: "Unicode (UTF-8)"</li>
                        <li><strong>Notepad++:</strong> Encoding → Encode in UTF-8</li>
                    </ol>
                </div>
                
                <div class="p-4 bg-purple-50 dark:bg-purple-900 rounded-lg">
                    <p class="text-sm font-medium text-purple-800 dark:text-purple-200 mb-2">📚 รองรับทั้งภาษาไทยและอังกฤษ</p>
                    <p class="text-xs text-purple-700 dark:text-purple-300 mb-2">
                        สำหรับฟิลด์ Master Data (ตำแหน่ง, แผนก, ฯลฯ) สามารถใช้ชื่อภาษาไทยหรือภาษาอังกฤษได้
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 bg-white dark:bg-gray-800 rounded">
                            <p class="font-medium text-purple-800 dark:text-purple-300">ภาษาไทย ✅</p>
                            <p class="text-purple-600 dark:text-purple-400">ฝ่ายผลิต, พนักงานทั่วไป, ชาย</p>
                        </div>
                        <div class="p-2 bg-white dark:bg-gray-800 rounded">
                            <p class="font-medium text-purple-800 dark:text-purple-300">English ✅</p>
                            <p class="text-purple-600 dark:text-purple-400">Production, General Worker, Male</p>
                        </div>
                    </div>
                </div>

                <!-- แทนที่ส่วน Download Template Button -->
<div class="mb-4">
    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-3">
        เลือกประเภท Template:
    </label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition hover:border-blue-500 hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> <?php echo $border_class; ?>">
            <input type="radio" name="template_type" value="simple" checked class="mt-1 sr-only peer">
            <div class="flex-1 peer-checked:font-semibold">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="<?php echo $text_class; ?> font-medium">Simple (5 คอลัมน์)</span>
                </div>
                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                    รหัส, ชื่อ, Username, Password, Role<br>
                    <strong>แนะนำ:</strong> ใช้สำหรับสร้างพนักงานเร็ว
                </p>
            </div>
            <svg class="w-6 h-6 text-blue-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        </label>
        
        <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition hover:border-blue-500 hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> <?php echo $border_class; ?>">
            <input type="radio" name="template_type" value="full" class="mt-1 sr-only peer">
            <div class="flex-1 peer-checked:font-semibold">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="<?php echo $text_class; ?> font-medium">Full (29 คอลัมน์)</span>
                </div>
                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                    ข้อมูลครบถ้วน รวมตำแหน่ง, แผนก, ที่อยู่<br>
                    <strong>ใช้เมื่อ:</strong> มีข้อมูลพนักงานครบถ้วน
                </p>
            </div>
            <svg class="w-6 h-6 text-blue-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        </label>
    </div>
</div>

<div class="flex gap-4">
    <button onclick="downloadTemplate()" 
            class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center justify-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Download CSV Template
    </button>
</div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload CSV File
            </h2>

            <form id="importForm" onsubmit="uploadFile(event)" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                        Select CSV File <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="csv_file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed <?php echo $border_class; ?> rounded-lg cursor-pointer <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-50'; ?> transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-16 h-16 mb-4 <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">CSV file only</p>
                                <p id="fileName" class="mt-2 text-sm font-medium text-blue-600"></p>
                            </div>
                            <input id="csv_file" name="csv_file" type="file" accept=".csv" required class="hidden" onchange="displayFileName(this)">
                        </label>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Upload & Import
                    </button>
                    <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php" 
                       class="flex-1 px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Section (Hidden by default) -->
        <div id="resultsSection" class="hidden <?php echo $card_bg; ?> rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Import Results
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <p class="text-sm text-blue-600 dark:text-blue-400 mb-1">Total Records</p>
                    <p class="text-3xl font-bold text-blue-700 dark:text-blue-300" id="totalCount">0</p>
                </div>
                <div class="p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                    <p class="text-sm text-green-600 dark:text-green-400 mb-1">Successfully Imported</p>
                    <p class="text-3xl font-bold text-green-700 dark:text-green-300" id="successCount">0</p>
                </div>
                <div class="p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                    <p class="text-sm text-red-600 dark:text-red-400 mb-1">Failed</p>
                    <p class="text-3xl font-bold text-red-700 dark:text-red-300" id="failedCount">0</p>
                </div>
            </div>

            <div id="errorsList" class="hidden">
                <h3 class="font-semibold <?php echo $text_class; ?> mb-3">Errors:</h3>
                <div class="max-h-64 overflow-y-auto space-y-2" id="errorsContainer"></div>
            </div>

            <div class="mt-6 flex gap-3">
                <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/admin/employees.php'" 
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    View All Employees
                </button>
                <button onclick="resetForm()" 
                        class="flex-1 px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition">
                    Import Another File
                </button>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
function displayFileName(input) {
    const fileName = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        fileName.textContent = '📄 ' + input.files[0].name;
    }
}
function downloadTemplate() {
    const templateType = document.querySelector('input[name="template_type"]:checked')?.value || 'simple';
    
    const BOM = '\uFEFF';
    let template;
    
    if (templateType === 'simple') {
        // Simple template (5 columns)
        template = BOM + 'Employee ID,Full Name (Thai),Username,Password,Role\n' +
                  'EMP001,สมชาย ใจดี,somchai.j,password123,employee\n' +
                  'EMP002,สมหญิง รักงาน,somying.r,securepass456,officer\n' +
                  'ADM001,วิชัย ดีเลิศ,wichai.d,admin2024,admin';
    } else {
        // Full template (29 columns)
        template = BOM + 
'Employee ID,Prefix,Full Name (Thai),Full Name (English),Sex,Birthday,Nationality,Education,Phone,Village,Subdistrict,District,Province,Function,Division,Department,Section,Operation,Position,Position Level,Labour Cost,Hiring Type,Zone,Contribution,Date of Hire,Status,Username,Password,Role\n' +
'EMP001,นาย,สมชาย ใจดี,Somchai Jaidee,ชาย,1990-01-15,ไทย,ปริญญาตรี,081-234-5678,หมู่ 1,ตำบลหนองบัว,อำเภอเมือง,อุดรธานี,ฝ่ายผลิต,แผนกตัดเย็บ,แผนกจัดส่ง,แผนกผลิตชิ้นส่วน,งานตัด,พนักงานทั่วไป,ระดับ 1,ค่าแรงตรง,พนักงานประจำ,โซน A,ระดับกลาง,2023-01-01,ทำงานอยู่,somchai.j,password123,employee\n' +
'EMP002,นางสาว,สมหญิง รักงาน,Somying Rakngaan,หญิง,1992-05-20,ไทย,ปริญญาตรี,082-345-6789,หมู่ 2,ตำบลหนองบัว,อำเภอเมือง,อุดรธานี,ฝ่ายคุณภาพ,แผนกควบคุมคุณภาพ,แผนกทรัพยากรบุคคล,แผนกตรวจสอบคุณภาพ,งานตรวจสอบ,เจ้าหน้าที่ธุรการ,ระดับ 2,ค่าแรงอ้อม,พนักงานประจำ,โซน B,ระดับสูง,2023-03-15,ทำงานอยู่,somying.r,securepass456,officer';
    }
    
    const blob = new Blob([template], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = templateType === 'simple' ? 
        'employee_import_simple.csv' : 
        'employee_import_full.csv';
    link.click();
}

function uploadFile(event) {
    event.preventDefault();
    
    const fileInput = document.getElementById('csv_file');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a CSV file');
        return;
    }
    
    if (!file.name.toLowerCase().endsWith('.csv')) {
        alert('Please upload a CSV file');
        return;
    }
    
    const formData = new FormData();
    formData.append('csv_file', file);
    
    const submitButton = event.target.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Importing...';
    
    fetch('<?php echo BASE_PATH; ?>/api/import_employees.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Import results:', data);
        
        if (!data.success && data.message) {
            // Show general error
            alert('Error: ' + data.message);
            return;
        }
        
        // Show results section
        document.getElementById('resultsSection').classList.remove('hidden');
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
        
        // Update counts
        document.getElementById('totalCount').textContent = data.total || 0;
        document.getElementById('successCount').textContent = data.imported || 0;
        document.getElementById('failedCount').textContent = data.failed || 0;
        
        // Show errors if any
        if (data.errors && data.errors.length > 0) {
            const errorsList = document.getElementById('errorsList');
            const errorsContainer = document.getElementById('errorsContainer');
            
            errorsList.classList.remove('hidden');
            errorsContainer.innerHTML = '';
            
            data.errors.forEach(error => {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'p-3 bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-300 rounded text-sm';
                errorDiv.textContent = error;
                errorsContainer.appendChild(errorDiv);
            });
        }
        
        // Show success message
        if (data.imported > 0) {
            showToast(`Successfully imported ${data.imported} employee(s)`, 'success');
        }
        
        if (data.failed > 0) {
            showToast(`${data.failed} record(s) failed to import`, 'error');
        }
        
        if (data.total === 0) {
            showToast('No records found in CSV file', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during import: ' + error.message + '\n\nPlease check:\n1. CSV file format is correct\n2. File is saved as UTF-8\n3. All required fields are present');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.innerHTML = '<svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Upload & Import';
    });
}

function resetForm() {
    document.getElementById('importForm').reset();
    document.getElementById('fileName').textContent = '';
    document.getElementById('resultsSection').classList.add('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
    toast.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
            </svg>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>