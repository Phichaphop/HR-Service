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
                    <ul class="list-disc list-inside space-y-1 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?>">
                        <li><strong>Column 1:</strong> Employee ID (max 8 characters, must be unique)</li>
                        <li><strong>Column 2:</strong> Full Name (Thai)</li>
                        <li><strong>Column 3:</strong> Username (must be unique)</li>
                        <li><strong>Column 4:</strong> Password (minimum 6 characters)</li>
                        <li><strong>Column 5:</strong> Role (admin, officer, or employee)</li>
                    </ul>
                </div>

                <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                    <p class="text-sm font-medium <?php echo $text_class; ?> mb-2">Example CSV format:</p>
                    <pre class="text-xs <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> overflow-x-auto">Employee ID,Full Name (Thai),Username,Password,Role
EMP001,สมชาย ใจดี,somchai.j,password123,employee
EMP002,สมหญิง รักงาน,somying.r,securepass456,officer
EMP003,วิชัย ดีมาก,wichai.d,mypass789,admin</pre>
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
    // Add BOM for UTF-8 to ensure Thai characters work in Excel
    const BOM = '\uFEFF';
    const template = BOM + 'Employee ID,Full Name (Thai),Username,Password,Role\n' +
                    'EMP001,สมชาย ใจดี,somchai.j,password123,employee\n' +
                    'EMP002,สมหญิง รักงาน,somying.r,securepass456,officer\n' +
                    'EMP003,วิชัย ดีมาก,wichai.d,mypass789,admin';
    
    const blob = new Blob([template], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'employee_import_template.csv';
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
    .then(response => response.json())
    .then(data => {
        console.log('Import results:', data);
        
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
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during import. Please try again.');
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