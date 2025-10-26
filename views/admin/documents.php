<?php
/**
 * Online Document Storage System
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Features: Upload, View, Manage Documents with Dark Mode & Multi-language
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin/officer role
AuthController::requireRole(['admin', 'officer']);

// Get current settings from session
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'] ?? '';

// Multi-language translations
$translations = [
    'th' => [
        'page_title' => 'จัดเก็บเอกสารออนไลน์',
        'page_subtitle' => 'บริหารจัดการและเก็บเอกสาร PDF',
        'upload_document' => 'อัปโหลดเอกสาร',
        'document_list' => 'รายการเอกสาร',
        'document_type' => 'ประเภทเอกสาร',
        'file_name' => 'ชื่อเอกสาร',
        'uploaded_by' => 'อัปโหลดโดย',
        'uploaded_date' => 'วันที่อัปโหลด',
        'file_path' => 'ไฟล์',
        'actions' => 'การทำงาน',
        'view' => 'ดู',
        'delete' => 'ลบ',
        'download' => 'ดาวน์โหลด',
        'add_new' => 'เพิ่มเอกสารใหม่',
        'select_file' => 'เลือกไฟล์',
        'select_type' => 'เลือกประเภท',
        'file_name_label' => 'ชื่อเอกสาร',
        'upload' => 'อัปโหลด',
        'cancel' => 'ยกเลิก',
        'save' => 'บันทึก',
        'close' => 'ปิด',
        'required' => 'จำเป็น',
        'no_data' => 'ไม่มีเอกสาร',
        'total_documents' => 'รวมเอกสาร',
        'files' => 'ไฟล์',
        'confirm_delete' => 'คุณแน่ใจหรือว่าต้องการลบเอกสารนี้?',
        'delete_success' => 'ลบเรียบร้อยแล้ว',
        'delete_error' => 'ไม่สามารถลบได้',
        'upload_success' => 'อัปโหลดเรียบร้อยแล้ว',
        'upload_error' => 'ไม่สามารถอัปโหลดได้',
        'file_size_error' => 'ไฟล์ใหญ่เกินไป',
        'file_type_error' => 'ไฟล์ประเภท PDF เท่านั้น',
        'pdf_only' => 'เฉพาะไฟล์ PDF',
        'max_size' => 'ขนาดสูงสุด',
        'language' => 'ภาษา',
        'theme' => 'ธีม',
        'light_mode' => 'โหมดสว่าง',
        'dark_mode' => 'โหมดมืด',
        'admin_only' => 'เฉพาะผู้ดูแลระบบ',
        'manage_types' => 'จัดการประเภท',
        'type_name' => 'ชื่อประเภท',
        'type_name_th' => 'ชื่อ (ไทย)',
        'type_name_en' => 'ชื่อ (อังกฤษ)',
        'type_name_my' => 'ชื่อ (พม่า)',
        'add_type' => 'เพิ่มประเภท',
        'edit_type' => 'แก้ไขประเภท',
        'open_document' => 'เปิดเอกสาร',
    ],
    'en' => [
        'page_title' => 'Online Document Storage',
        'page_subtitle' => 'Manage and Store Documents',
        'upload_document' => 'Upload Document',
        'document_list' => 'Document List',
        'document_type' => 'Document Type',
        'file_name' => 'Document Name',
        'uploaded_by' => 'Uploaded By',
        'uploaded_date' => 'Upload Date',
        'file_path' => 'File',
        'actions' => 'Actions',
        'view' => 'View',
        'delete' => 'Delete',
        'download' => 'Download',
        'add_new' => 'Add New Document',
        'select_file' => 'Select File',
        'select_type' => 'Select Type',
        'file_name_label' => 'Document Name',
        'upload' => 'Upload',
        'cancel' => 'Cancel',
        'save' => 'Save',
        'close' => 'Close',
        'required' => 'Required',
        'no_data' => 'No Documents',
        'total_documents' => 'Total Documents',
        'files' => 'Files',
        'confirm_delete' => 'Are you sure you want to delete this document?',
        'delete_success' => 'Deleted successfully',
        'delete_error' => 'Failed to delete',
        'upload_success' => 'Uploaded successfully',
        'upload_error' => 'Failed to upload',
        'file_size_error' => 'File size is too large',
        'file_type_error' => 'Only PDF files allowed',
        'pdf_only' => 'PDF Files Only',
        'max_size' => 'Max Size',
        'language' => 'Language',
        'theme' => 'Theme',
        'light_mode' => 'Light Mode',
        'dark_mode' => 'Dark Mode',
        'admin_only' => 'Admin Only',
        'manage_types' => 'Manage Types',
        'type_name' => 'Type Name',
        'type_name_th' => 'Name (Thai)',
        'type_name_en' => 'Name (English)',
        'type_name_my' => 'Name (Myanmar)',
        'add_type' => 'Add Type',
        'edit_type' => 'Edit Type',
        'open_document' => 'Open Document',
    ],
    'my' => [
        'page_title' => 'အွန်လိုင်းစာ類သိုလှောင်မှု',
        'page_subtitle' => 'စာရွက်စာတမ်းများ စီမံခန့်ခွဲမှု',
        'upload_document' => 'စာရွက်စာတမ်းအပ်တင်မည်',
        'document_list' => 'စာရွက်စာတမ်းများစာရင်း',
        'document_type' => 'စာရွက်စာတမ်းအမျိုးအစား',
        'file_name' => 'စာရွက်စာတမ်းအမည်',
        'uploaded_by' => 'အပ်တင်သူ',
        'uploaded_date' => 'အပ်တင်သည့်နေ့',
        'file_path' => 'ဖိုင်',
        'actions' => 'လုပ်ဆောင်ချက်များ',
        'view' => 'ကြည့်ရှုမည်',
        'delete' => 'ဖျက်မည်',
        'download' => 'ဒાउင်းလုပ်ဒ်မည်',
        'add_new' => 'အသစ်ထည့်သွင်းမည်',
        'select_file' => 'ဖိုင်ရွေးချယ်မည်',
        'select_type' => 'အမျိုးအစားရွေးချယ်မည်',
        'file_name_label' => 'စာရွက်စာတမ်းအမည်',
        'upload' => 'အပ်တင်မည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'save' => 'သိမ်းဆည်းမည်',
        'close' => 'ပိတ်မည်',
        'required' => 'လိုအပ်သည်',
        'no_data' => 'စာရွက်စာတမ်းမရှိ',
        'total_documents' => 'စုစုပေါင်းစာရွက်စာတမ်း',
        'files' => 'ဖိုင်များ',
        'confirm_delete' => 'ဤစာရွက်စာတမ်းကိုဖျက်ရန်သေချာပါသလား?',
        'delete_success' => 'ဖျက်ခြင်းအောင်မြင်ခြင်း',
        'delete_error' => 'ဖျက်ရန်မကြိုးစားနိုင်ခြင်း',
        'upload_success' => 'အပ်တင်ခြင်းအောင်မြင်ခြင်း',
        'upload_error' => 'အပ်တင်ရန်မကြိုးစားနိုင်ခြင်း',
        'file_size_error' => 'ဖိုင်ကြီးလွန်းသည်',
        'file_type_error' => 'PDF ဖိုင်များသာခွင့်ပြုသည်',
        'pdf_only' => 'PDF ဖိုင်သာ',
        'max_size' => 'အများဆုံးအရွယ်အစား',
        'language' => 'ဘာသာစကား',
        'theme' => 'အပြင်အဆင်',
        'light_mode' => 'အလင်းကွင်း',
        'dark_mode' => 'မှောင်ကွင်း',
        'admin_only' => 'အုပ်ချုပ်ရန်သာ',
        'manage_types' => 'အမျိုးအစားများစီမံခန့်ခွဲမည်',
        'type_name' => 'အမျိုးအစားအမည်',
        'type_name_th' => 'အမည် (ထိုင်း)',
        'type_name_en' => 'အမည် (အင်္ဂလိပ်)',
        'type_name_my' => 'အမည် (မြန်မာ)',
        'add_type' => 'အမျိုးအစားထည့်သွင်းမည်',
        'edit_type' => 'အမျိုးအစားပြင်ဆင်မည်',
        'open_document' => 'စာရွက်စာတမ်းဖွင့်မည်',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

// Handle file upload
$message = '';
$message_type = '';
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    $file = $_FILES['document_file'];
    $doc_type = $_POST['doc_type'] ?? '';
    $file_name = $_POST['file_name'] ?? '';
    
    // Validation
    if (empty($file_name)) {
        $message = $t['file_name'] . ' ' . $t['required'];
        $message_type = 'error';
    } elseif (empty($doc_type)) {
        $message = $t['document_type'] . ' ' . $t['required'];
        $message_type = 'error';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $message = $t['upload_error'];
        $message_type = 'error';
    } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        $message = $t['file_size_error'] . ' (Max 5MB)';
        $message_type = 'error';
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        $message = $t['file_type_error'];
        $message_type = 'error';
    } else {
        // Create upload directory if not exists
        $upload_dir = __DIR__ . '/../../uploads/documents/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid('doc_') . '_' . time() . '.pdf';
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Save to database
            $db_path = 'uploads/documents/' . $filename;
            $sql = "INSERT INTO document_storage (file_name, file_path, doc_type, upload_by, upload_at) 
                    VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $file_name, $db_path, $doc_type, $user_id);
            
            if ($stmt->execute()) {
                $message = $t['upload_success'];
                $message_type = 'success';
            } else {
                $message = $t['upload_error'] . ': ' . $stmt->error;
                $message_type = 'error';
                unlink($filepath); // Remove file if db insert fails
            }
            $stmt->close();
        } else {
            $message = $t['upload_error'];
            $message_type = 'error';
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $doc_id = $_POST['doc_id'] ?? '';
    
    if (!empty($doc_id)) {
        // Get file path before deleting
        $sql = "SELECT file_path FROM document_storage WHERE doc_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $doc_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            // Delete from database
            $sql = "DELETE FROM document_storage WHERE doc_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $doc_id);
            
            if ($stmt->execute()) {
                // Delete file
                $file_path = __DIR__ . '/../../' . $row['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $message = $t['delete_success'];
                $message_type = 'success';
            } else {
                $message = $t['delete_error'];
                $message_type = 'error';
            }
        }
    }
}

// Get all documents
$documents = [];
$result = $conn->query("
    SELECT d.*, dt.type_name_th, dt.type_name_en, dt.type_name_my, e.employee_name
    FROM document_storage d
    LEFT JOIN doc_type_master dt ON d.doc_type = dt.doc_type_id
    LEFT JOIN employees e ON d.upload_by = e.employee_id
    ORDER BY d.upload_at DESC
    LIMIT 100
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}

// Get document types
$doc_types = [];
$result = $conn->query("SELECT * FROM doc_type_master ORDER BY type_name_th");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $doc_types[] = $row;
    }
}

// Include header and sidebar
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

// Theme classes
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-900';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900';
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .theme-transition { transition: all 0.3s ease; }
        .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 40; }
        .modal-backdrop.active { display: flex; align-items: center; justify-content: center; }
        .pdf-viewer { width: 100%; height: 600px; border-radius: 8px; }
    </style>
</head>
<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition">

<div class="lg:ml-64 p-4 md:p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold <?php echo $text_class; ?> mb-2"><?php echo $t['page_title']; ?></h1>
        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>"><?php echo $t['page_subtitle']; ?></p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg flex items-center gap-3 <?php echo $message_type === 'success' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-300 dark:border-green-700' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border border-red-300 dark:border-red-700'; ?>">
        <?php if ($message_type === 'success'): ?>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <?php else: ?>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        <?php endif; ?>
        <span><?php echo htmlspecialchars($message); ?></span>
    </div>
    <?php endif; ?>

    <!-- Upload Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Upload Form -->
        <div class="lg:col-span-1">
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 border <?php echo $border_class; ?> sticky top-4">
                <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-4"><?php echo $t['upload_document']; ?></h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            <?php echo $t['file_name_label']; ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="file_name" required
                               class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            <?php echo $t['document_type']; ?> <span class="text-red-500">*</span>
                        </label>
                        <select name="doc_type" required
                                class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo $t['select_type']; ?></option>
                            <?php foreach ($doc_types as $type): ?>
                            <option value="<?php echo $type['doc_type_id']; ?>">
                                <?php 
                                $type_text = match($current_lang) {
                                    'en' => $type['type_name_en'] ?? $type['type_name_th'],
                                    'my' => $type['type_name_my'] ?? $type['type_name_th'],
                                    default => $type['type_name_th']
                                };
                                echo htmlspecialchars($type_text); 
                                ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2">
                            <?php echo $t['select_file']; ?> <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed <?php echo $border_class; ?> rounded-lg p-4 text-center">
                            <input type="file" name="document_file" accept=".pdf" required
                                   class="w-full" id="fileInput">
                            <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-2">
                                <?php echo $t['pdf_only']; ?> • <?php echo $t['max_size']; ?>: 5MB
                            </p>
                        </div>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        <?php echo $t['upload']; ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Documents List -->
        <div class="lg:col-span-2">
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 border <?php echo $border_class; ?>">
                <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-4"><?php echo $t['document_list']; ?></h2>
                <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-4">
                    <?php echo $t['total_documents']; ?>: <span class="font-bold text-blue-600"><?php echo count($documents); ?></span> <?php echo $t['files']; ?>
                </p>

                <?php if (empty($documents)): ?>
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>"><?php echo $t['no_data']; ?></p>
                </div>
                <?php else: ?>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    <?php foreach ($documents as $doc): ?>
                    <div class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> p-4 rounded-lg border <?php echo $border_class; ?> flex items-between justify-between hover:shadow-md transition">
                        <div class="flex-1">
                            <h3 class="font-semibold <?php echo $text_class; ?>"><?php echo htmlspecialchars($doc['file_name']); ?></h3>
                            <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
                                <?php 
                                $type_text = match($current_lang) {
                                    'en' => $doc['type_name_en'] ?? $doc['type_name_th'],
                                    'my' => $doc['type_name_my'] ?? $doc['type_name_th'],
                                    default => $doc['type_name_th']
                                };
                                echo htmlspecialchars($type_text ?? 'Unknown Type'); 
                                ?> • <?php echo date('d/m/Y', strtotime($doc['upload_at'])); ?>
                            </p>
                            <p class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-500'; ?> mt-1">
                                <?php echo $t['uploaded_by']; ?>: <?php echo htmlspecialchars($doc['employee_name'] ?? 'Unknown'); ?>
                            </p>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="viewPDF('<?php echo htmlspecialchars($doc['file_path']); ?>')"
                                    class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded transition">
                                <?php echo $t['view']; ?>
                            </button>
                            <button onclick="deleteDocument(<?php echo $doc['doc_id']; ?>)"
                                    class="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition">
                                <?php echo $t['delete']; ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- PDF Viewer Modal -->
<div id="pdfModal" class="modal-backdrop">
    <div class="<?php echo $card_bg; ?> rounded-lg shadow-xl max-w-4xl w-full m-4 border <?php echo $border_class; ?>">
        <div class="flex items-center justify-between p-6 border-b <?php echo $border_class; ?>">
            <h3 class="text-xl font-bold <?php echo $text_class; ?>"><?php echo $t['open_document']; ?></h3>
            <button onclick="closePDF()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <iframe id="pdfViewer" class="pdf-viewer" frameborder="0"></iframe>
        </div>
    </div>
</div>

<script>
const t = <?php echo json_encode($t); ?>;

function viewPDF(filePath) {
    const pdfUrl = filePath.startsWith('http') ? filePath : '../../' + filePath;
    document.getElementById('pdfViewer').src = pdfUrl;
    document.getElementById('pdfModal').classList.add('active');
}

function closePDF() {
    document.getElementById('pdfModal').classList.remove('active');
    document.getElementById('pdfViewer').src = '';
}

function deleteDocument(docId) {
    if (confirm(t['confirm_delete'])) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="doc_id" value="${docId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
document.getElementById('pdfModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePDF();
    }
});

// File input label
document.getElementById('fileInput').addEventListener('change', function() {
    const label = this.parentElement.querySelector('p');
    if (this.files.length > 0) {
        const fileName = this.files[0].name;
        const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
        label.textContent = `✓ ${fileName} (${fileSize}MB)`;
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>

<?php $conn->close(); ?>