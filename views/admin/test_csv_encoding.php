<?php
/**
 * CSV Encoding Test Tool
 * สำหรับทดสอบว่าไฟล์ CSV อ่านภาษาไทยได้ถูกต้องหรือไม่
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../api/helpers/csv_helper.php';

AuthController::requireRole(['admin']);

$test_result = null;
$csv_preview = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // ทดสอบอ่านไฟล์
        $test_result = CSVHelper::testReadCSV($file['tmp_name']);
        
        // อ่านข้อมูล
        $csv_data = CSVHelper::readCSVWithEncoding($file['tmp_name']);
        $csv_preview = $csv_data;
    }
}

$page_title = 'CSV Encoding Test';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        
        <h1 class="text-3xl font-bold <?php echo $text_class; ?> mb-6">CSV Encoding Test Tool</h1>
        
        <!-- Upload Form -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">Upload CSV File for Testing</h2>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <input type="file" name="test_file" accept=".csv" required
                           class="w-full px-4 py-3 border <?php echo $border_class; ?> rounded-lg">
                </div>
                
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                    Test File Encoding
                </button>
            </form>
        </div>
        
        <?php if ($test_result): ?>
            <!-- Test Results -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">Test Results</h2>
                
                <div class="space-y-3">
                    <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded">
                        <p class="text-sm font-medium <?php echo $text_class; ?>">File Exists:</p>
                        <p class="text-lg <?php echo $test_result['file_exists'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $test_result['file_exists'] ? '✓ Yes' : '✗ No'; ?>
                        </p>
                    </div>
                    
                    <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded">
                        <p class="text-sm font-medium <?php echo $text_class; ?>">File Size:</p>
                        <p class="text-lg <?php echo $text_class; ?>"><?php echo number_format($test_result['file_size']); ?> bytes</p>
                    </div>
                    
                    <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded">
                        <p class="text-sm font-medium <?php echo $text_class; ?>">Detected Encoding:</p>
                        <p class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($test_result['original_encoding'] ?? 'Unknown'); ?></p>
                    </div>
                    
                    <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded">
                        <p class="text-sm font-medium <?php echo $text_class; ?>">Has BOM:</p>
                        <p class="text-lg <?php echo $test_result['has_bom'] ? 'text-green-600' : 'text-gray-600'; ?>">
                            <?php echo $test_result['has_bom'] ? '✓ Yes (UTF-8 with BOM)' : '✗ No'; ?>
                        </p>
                    </div>
                    
                    <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded">
                        <p class="text-sm font-medium <?php echo $text_class; ?>">Contains Thai:</p>
                        <p class="text-lg <?php echo $test_result['contains_thai'] ? 'text-green-600' : 'text-gray-600'; ?>">
                            <?php echo $test_result['contains_thai'] ? '✓ Yes' : '✗ No'; ?>
                        </p>
                    </div>
                    
                    <div class="p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded">
                        <p class="text-sm font-medium <?php echo $text_class; ?> mb-2">First 100 Characters:</p>
                        <pre class="text-sm <?php echo $text_class; ?> whitespace-pre-wrap break-all"><?php echo htmlspecialchars($test_result['first_100_chars']); ?></pre>
                    </div>
                </div>
            </div>
            
            <?php if ($csv_preview): ?>
                <!-- CSV Preview -->
                <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold <?php echo $text_class; ?> mb-4">CSV Data Preview</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border <?php echo $border_class; ?>">
                            <thead class="bg-blue-600 text-white">
                                <tr>
                                    <th class="px-4 py-2 text-left">Row</th>
                                    <?php 
                                    $max_cols = max(array_map('count', $csv_preview));
                                    for ($i = 0; $i < $max_cols; $i++): 
                                    ?>
                                        <th class="px-4 py-2 text-left">Column <?php echo $i + 1; ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($csv_preview, 0, 10) as $row_index => $row): ?>
                                    <tr class="<?php echo $row_index % 2 === 0 ? ($is_dark ? 'bg-gray-700' : 'bg-gray-50') : ''; ?>">
                                        <td class="px-4 py-2 font-bold <?php echo $text_class; ?>"><?php echo $row_index + 1; ?></td>
                                        <?php for ($i = 0; $i < $max_cols; $i++): ?>
                                            <td class="px-4 py-2 <?php echo $text_class; ?> border-l <?php echo $border_class; ?>">
                                                <?php echo htmlspecialchars($row[$i] ?? ''); ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($csv_preview) > 10): ?>
                        <p class="mt-4 text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                            Showing first 10 rows of <?php echo count($csv_preview); ?> total rows
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>