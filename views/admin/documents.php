<?php
/**
 * Online Documents Management System
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * Features: Multi-language UI, Dark Mode, Mobile Responsive
 * Admin/Officer Access
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

// Require admin or officer role
AuthController::requireRole(['admin', 'officer_payroll', 'officer_hrbp', 'officer_compliance', 'officer_admin', 'officer_ta', 'officer_pdee']);

// Get current settings from session
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
$user_id = $_SESSION['user_id'] ?? '';

// Theme colors based on dark mode
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';

// Multi-language translations for entire page
$translations = [
    'th' => [
        'page_title' => 'เอกสารออนไลน์',
        'page_subtitle' => 'จัดการเอกสารและแบบฟอร์มของบริษัท',
        'admin_only' => 'สำหรับผู้ดูแล / เจ้าหน้าที่',
        'total_documents' => 'เอกสารทั้งหมด',
        'upload_document' => 'อัปโหลดเอกสาร',
        'search' => 'ค้นหา...',
        'search_placeholder' => 'ชื่อเอกสารหรือผู้อัปโหลด...',
        'document_type' => 'ประเภทเอกสาร',
        'all_types' => 'ทั้งหมด',
        'filter' => 'ค้นหา',
        'download' => 'ดาวน์โหลด',
        'delete' => 'ลบ',
        'open' => 'เปิด',
        'view_image' => 'ดูภาพ',
        'open_pdf' => 'เปิด PDF',
        'no_documents' => 'ไม่มีเอกสาร',
        'no_documents_info' => 'อัปโหลดเอกสารแรกของคุณเพื่อเริ่มต้น',
        'uploaded' => 'อัปโหลด',
        'by' => 'โดย',
        'type' => 'ประเภท',
        'previous' => 'ก่อนหน้า',
        'next' => 'ถัดไป',
        'upload_modal_title' => 'อัปโหลดเอกสาร',
        'document_name' => 'ชื่อเอกสาร',
        'document_name_placeholder' => 'เช่น คู่มือพนักงาน 2025',
        'upload_file' => 'อัปโหลดไฟล์',
        'click_to_upload' => 'คลิกเพื่ออัปโหลด หรือลากไฟล์มาวาง',
        'file_types' => 'PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (สูงสุด 10MB)',
        'selected' => 'เลือก',
        'cancel' => 'ยกเลิก',
        'save' => 'บันทึก',
        'close' => 'ปิด',
        'confirm_delete' => 'คุณแน่ใจหรือว่าต้องการลบเอกสารนี้?',
        'delete_success' => 'ลบเรียบร้อยแล้ว',
        'delete_error' => 'ไม่สามารถลบได้',
        'upload_success' => 'อัปโหลดเรียบร้อยแล้ว!',
        'upload_error' => 'ไม่สามารถอัปโหลดได้',
        'required' => 'จำเป็น',
        'important' => 'สำคัญ',
        'important_note' => 'ตรวจสอบให้แน่ใจว่าเอกสารไม่มีข้อมูลที่เป็นความลับเว้นแต่ว่าจะได้รับการปกป้องอย่างเหมาะสม',
        'records' => 'รายการ',
        'total' => 'รวม',
        'no_data' => 'ไม่มีข้อมูล',
    ],
    'en' => [
        'page_title' => 'Online Documents',
        'page_subtitle' => 'Manage company documents, forms, and resources',
        'admin_only' => 'Admin / Officer Access',
        'total_documents' => 'Total Documents',
        'upload_document' => 'Upload Document',
        'search' => 'Search',
        'search_placeholder' => 'Document name or uploader...',
        'document_type' => 'Document Type',
        'all_types' => 'All Types',
        'filter' => 'Filter',
        'download' => 'Download',
        'delete' => 'Delete',
        'open' => 'Open',
        'view_image' => 'View Image',
        'open_pdf' => 'Open PDF',
        'no_documents' => 'No documents found',
        'no_documents_info' => 'Upload your first document to get started',
        'uploaded' => 'Uploaded',
        'by' => 'By',
        'type' => 'Type',
        'previous' => 'Previous',
        'next' => 'Next',
        'upload_modal_title' => 'Upload Document',
        'document_name' => 'Document Name',
        'document_name_placeholder' => 'e.g., Employee Handbook 2025',
        'upload_file' => 'Upload File',
        'click_to_upload' => 'Click to upload or drag and drop',
        'file_types' => 'PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (MAX. 10MB)',
        'selected' => 'Selected',
        'cancel' => 'Cancel',
        'save' => 'Save',
        'close' => 'Close',
        'confirm_delete' => 'Are you sure you want to delete this document?',
        'delete_success' => 'Deleted successfully',
        'delete_error' => 'Failed to delete',
        'upload_success' => 'Document uploaded successfully!',
        'upload_error' => 'Failed to upload',
        'required' => 'Required',
        'important' => 'Important',
        'important_note' => 'Ensure documents do not contain sensitive information unless properly secured.',
        'records' => 'Records',
        'total' => 'Total',
        'no_data' => 'No Data',
    ],
    'my' => [
        'page_title' => 'အွန်လိုင်းမှတ်တမ်းများ',
        'page_subtitle' => 'ကုမ္ပဏီ၏မှတ်တမ်းများ၊ ပုံစံများ နှင့် သြယ်မျှော်တွေကိုစီမံခန့်ခွဲမည်',
        'admin_only' => 'အုပ်ချုပ်ရန်သူ / အရာရှိ အသုံးပြုမှု',
        'total_documents' => 'စုစုပေါင်းမှတ်တမ်းများ',
        'upload_document' => 'မှတ်တမ်းတင်သွင်းမည်',
        'search' => 'ရှာဖွေမည်',
        'search_placeholder' => 'မှတ်တမ်းအမည် သို့မဟုတ် တင်သွင်းသူ...',
        'document_type' => 'မှတ်တမ်းအမျိုးအစား',
        'all_types' => 'အားလုံး',
        'filter' => 'စစ်ထုတ်မည်',
        'download' => 'ကူးယူမည်',
        'delete' => 'ဖျက်မည်',
        'open' => 'ဖွင့်မည်',
        'view_image' => 'ပုံကိုကြည့်မည်',
        'open_pdf' => 'PDF ကိုဖွင့်မည်',
        'no_documents' => 'မှတ်တမ်းများမတွေ့ရှိ',
        'no_documents_info' => 'စတင်ရန်သင်၏ပထမမှတ်တမ်းကိုတင်သွင်းပါ',
        'uploaded' => 'တင်သွင်းသည်',
        'by' => 'အားဖြင့်',
        'type' => 'အမျိုးအစား',
        'previous' => 'အရင်',
        'next' => 'နောက်တစ်ခု',
        'upload_modal_title' => 'မှတ်တမ်းတင်သွင်းမည်',
        'document_name' => 'မှတ်တမ်းအမည်',
        'document_name_placeholder' => 'ဥပမာ - အလုပ်သမားလက်စွဲ 2025',
        'upload_file' => 'ဖိုင်တင်သွင်းမည်',
        'click_to_upload' => 'တင်သွင်းရန်ကလစ်ပါ သို့မဟုတ် ဖိုင်ကိုဆွဲခြင်းနှင့်ချခြင်း',
        'file_types' => 'PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (အများဆုံး 10MB)',
        'selected' => 'ရွေးချယ်သည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'save' => 'သိမ်းဆည်းမည်',
        'close' => 'ပိတ်မည်',
        'confirm_delete' => 'ဤမှတ်တမ်းကိုဖျက်ရန်သေချာပါသလား?',
        'delete_success' => 'ဖျက်ခြင်းအောင်မြင်ခြင်း',
        'delete_error' => 'ဖျက်ရန်ခွင့်မပြုခြင်း',
        'upload_success' => 'မှတ်တမ်းအောင်မြင်စွာတင်သွင်းခြင်း!',
        'upload_error' => 'တင်သွင်းရန်မကြိုးစားနိုင်ခြင်း',
        'required' => 'လိုအပ်သည်',
        'important' => 'အရေးကြီး',
        'important_note' => 'မှတ်တမ်းများသည်သတ်မှတ်ထားသောအန္တရာယ်အချက်အလက်မပါ ၎င်းမှာအားလုံးလုံခြုံစွာသိမ်းဆည်းထားခြင်းမဟုတ်လျှင်သာဖြစ်သည်',
        'records' => 'မှတ်တမ်းများ',
        'total' => 'စုစုပေါင်း',
        'no_data' => 'ဒေတာမရှိ',
    ]
];

// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];

$page_title = $t['page_title'];
ensure_session_started();
$message = '';
$message_type = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getDbConnection();
    
    if ($_POST['action'] === 'upload') {
        $file_name_custom = trim($_POST['file_name_custom'] ?? '');
        $doc_type_id = intval($_POST['doc_type_id'] ?? 0);
        
        error_log("Upload attempt - Name: $file_name_custom, Type: $doc_type_id");
        
        if (empty($file_name_custom)) {
            $message = $t['upload_error'] . ' - ' . $t['document_name'];
            $message_type = 'error';
        } elseif ($doc_type_id <= 0) {
            $message = $t['upload_error'] . ' - ' . $t['document_type'];
            $message_type = 'error';
        } elseif (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOAD_PATH_DOCUMENTS;
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($file_ext, $allowed_exts)) {
                $message = $t['upload_error'] . ' - ' . $t['file_types'];
                $message_type = 'error';
            } elseif ($_FILES['document']['size'] > (UPLOAD_MAX_SIZE * 2)) {
                $message = $t['upload_error'] . ' - Size limit';
                $message_type = 'error';
            } else {
                $new_filename = 'doc_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
                    $file_path = 'uploads/documents/' . $new_filename;
                    
                    $stmt = $conn->prepare("INSERT INTO online_documents (file_name_custom, file_path, doc_type_id, upload_by, upload_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssis", $file_name_custom, $file_path, $doc_type_id, $user_id);
                    
                    if ($stmt->execute()) {
                        $message = $t['upload_success'];
                        $message_type = 'success';
                    } else {
                        $message = $t['upload_error'] . ': ' . $stmt->error;
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = $t['upload_error'];
                    $message_type = 'error';
                }
            }
        } else {
            $message = $t['upload_error'];
            $message_type = 'error';
        }
    } elseif ($_POST['action'] === 'delete') {
        $doc_id = intval($_POST['doc_id'] ?? 0);
        
        $stmt = $conn->prepare("SELECT file_path FROM online_documents WHERE doc_id = ?");
        $stmt->bind_param("i", $doc_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $file_path = __DIR__ . '/../../' . $row['file_path'];
            
            $stmt_delete = $conn->prepare("DELETE FROM online_documents WHERE doc_id = ?");
            $stmt_delete->bind_param("i", $doc_id);
            
            if ($stmt_delete->execute()) {
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $message = $t['delete_success'];
                $message_type = 'success';
            } else {
                $message = $t['delete_error'];
                $message_type = 'error';
            }
            $stmt_delete->close();
        }
        $stmt->close();
    }
    
    $conn->close();
}

// Get filters
$search = $_GET['search'] ?? '';
$doc_type_filter = $_GET['doc_type'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get documents
$conn = getDbConnection();
$where_conditions = [];
$params = [];
$types = '';

if ($search) {
    $where_conditions[] = "(file_name_custom LIKE ? OR e.full_name_th LIKE ? OR e.full_name_en LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

if ($doc_type_filter) {
    $where_conditions[] = "od.doc_type_id = ?";
    $params[] = $doc_type_filter;
    $types .= 'i';
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM online_documents od 
              LEFT JOIN employees e ON od.upload_by = e.employee_id 
              $where_sql";

if (!empty($params)) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total'];
    $stmt->close();
} else {
    $result = $conn->query($count_sql);
    $total_records = $result->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $per_page);

// Get documents with pagination
$sql = "SELECT od.*, dt.type_name_th, dt.type_name_en, dt.type_name_my, e.full_name_th, e.full_name_en 
        FROM online_documents od
        LEFT JOIN doc_type_master dt ON od.doc_type_id = dt.doc_type_id
        LEFT JOIN employees e ON od.upload_by = e.employee_id
        $where_sql
        ORDER BY od.upload_at DESC
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get document types for filter
$doc_types = $conn->query("SELECT * FROM doc_type_master ORDER BY type_name_th")->fetch_all(MYSQLI_ASSOC);
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .theme-transition {
            transition: all 0.3s ease;
        }
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        .modal-backdrop.active {
            display: flex;
        }
    </style>
</head>
<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition">
    <!-- Main Content with proper margin -->
    <div class="lg:ml-64 min-h-screen">
        <div class="container mx-auto px-4 py-6">
            
            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900 border-l-4 border-red-500'; ?>">
                    <div class="flex items-center">
                        <?php if ($message_type === 'success'): ?>
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        <?php else: ?>
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        <?php endif; ?>
                        <p class="<?php echo $message_type === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'; ?> font-medium">
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between flex-col md:flex-row gap-4">
                    <div class="flex items-center">
                        <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                            <p class="text-green-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                        </div>
                    </div>
                    <button onclick="openUploadModal()" 
                            class="hidden md:flex items-center px-6 py-3 bg-white text-green-600 rounded-lg font-medium hover:bg-green-50 transition shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <?php echo $t['upload_document']; ?>
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 border <?php echo $border_class; ?>">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1"><?php echo $t['total_documents']; ?></p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>"><?php echo $total_records; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <?php
                // Get document type statistics
                $conn = getDbConnection();
                $type_stats = $conn->query("SELECT doc_type_id, COUNT(*) as count FROM online_documents GROUP BY doc_type_id ORDER BY count DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
                $conn->close();
                
                $stat_colors = [
                    ['bg' => 'bg-green-600', 'text' => 'text-green-600'],
                    ['bg' => 'bg-purple-600', 'text' => 'text-purple-600'],
                    ['bg' => 'bg-orange-600', 'text' => 'text-orange-600']
                ];
                
                $shown = 0;
                foreach ($type_stats as $idx => $stat):
                    if ($shown >= 3) break;
                    $color = $stat_colors[$idx] ?? $stat_colors[0];
                    $shown++;
                    
                    // Get language-specific type name
                    $type_name = '';
                    if ($current_lang === 'th' && isset($stat['type_name_th'])) {
                        $type_name = $stat['type_name_th'];
                    } elseif ($current_lang === 'en' && isset($stat['type_name_en'])) {
                        $type_name = $stat['type_name_en'];
                    } elseif ($current_lang === 'my' && isset($stat['type_name_my'])) {
                        $type_name = $stat['type_name_my'];
                    }
                ?>
                <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 border <?php echo $border_class; ?>">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1 truncate" title="<?php echo $type_name; ?>">
                                <?php echo htmlspecialchars($type_name ?: 'N/A'); ?>
                            </p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>"><?php echo $stat['count']; ?></p>
                        </div>
                        <div class="w-12 h-12 <?php echo $color['bg']; ?> rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php while ($shown < 3): $shown++; ?>
                <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 border <?php echo $border_class; ?>">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1"><?php echo $t['no_data']; ?></p>
                            <p class="text-3xl font-bold <?php echo $text_class; ?>">-</p>
                        </div>
                        <div class="w-12 h-12 bg-gray-400 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Filters Section -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6 border <?php echo $border_class; ?>">
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['search']; ?></label>
                        <input type="text" name="search" placeholder="<?php echo $t['search_placeholder']; ?>"
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-full px-4 py-2 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2"><?php echo $t['document_type']; ?></label>
                        <select name="doc_type" class="w-full px-4 py-2 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value=""><?php echo $t['all_types']; ?></option>
                            <?php foreach ($doc_types as $type): 
                                $type_label = '';
                                if ($current_lang === 'th') {
                                    $type_label = $type['type_name_th'] ?? 'N/A';
                                } elseif ($current_lang === 'en') {
                                    $type_label = $type['type_name_en'] ?? 'N/A';
                                } elseif ($current_lang === 'my') {
                                    $type_label = $type['type_name_my'] ?? 'N/A';
                                }
                            ?>
                                <option value="<?php echo $type['doc_type_id']; ?>" <?php echo $doc_type_filter == $type['doc_type_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <?php echo $t['filter']; ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Upload Button (Mobile FAB) -->
            <button onclick="openUploadModal()" 
                    class="md:hidden fixed bottom-6 right-6 bg-green-600 hover:bg-green-700 text-white p-4 rounded-full shadow-2xl z-40 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </button>

            <!-- Documents Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
                <?php if (empty($documents)): ?>
                    <div class="col-span-full <?php echo $card_bg; ?> rounded-lg shadow-lg p-12 text-center border <?php echo $border_class; ?>">
                        <svg class="w-20 h-20 mx-auto mb-4 <?php echo $is_dark ? 'text-gray-600' : 'text-gray-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-lg font-medium"><?php echo $t['no_documents']; ?></p>
                        <p class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm mt-2"><?php echo $t['no_documents_info']; ?></p>
                        <button onclick="openUploadModal()" 
                                class="mt-4 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                            <?php echo $t['upload_document']; ?>
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($documents as $doc): 
                        $file_ext = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));
                        $icon_colors = [
                            'pdf' => ['bg' => 'bg-red-100 dark:bg-red-900', 'text' => 'text-red-600 dark:text-red-400'],
                            'doc' => ['bg' => 'bg-blue-100 dark:bg-blue-900', 'text' => 'text-blue-600 dark:text-blue-400'],
                            'docx' => ['bg' => 'bg-blue-100 dark:bg-blue-900', 'text' => 'text-blue-600 dark:text-blue-400'],
                            'xls' => ['bg' => 'bg-green-100 dark:bg-green-900', 'text' => 'text-green-600 dark:text-green-400'],
                            'xlsx' => ['bg' => 'bg-green-100 dark:bg-green-900', 'text' => 'text-green-600 dark:text-green-400'],
                            'jpg' => ['bg' => 'bg-purple-100 dark:bg-purple-900', 'text' => 'text-purple-600 dark:text-purple-400'],
                            'jpeg' => ['bg' => 'bg-purple-100 dark:bg-purple-900', 'text' => 'text-purple-600 dark:text-purple-400'],
                            'png' => ['bg' => 'bg-purple-100 dark:bg-purple-900', 'text' => 'text-purple-600 dark:text-purple-400']
                        ];
                        $colors = $icon_colors[$file_ext] ?? ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-400'];
                        
                        // Get language-specific uploader name
                        $uploader_name = '';
                        if ($current_lang === 'th') {
                            $uploader_name = $doc['full_name_th'] ?? 'Unknown';
                        } elseif ($current_lang === 'en') {
                            $uploader_name = $doc['full_name_en'] ?? 'Unknown';
                        } else {
                            $uploader_name = $doc['full_name_th'] ?? 'Unknown';
                        }
                        
                        // Get language-specific document type
                        $doc_type_name = '';
                        if ($current_lang === 'th') {
                            $doc_type_name = $doc['type_name_th'] ?? 'N/A';
                        } elseif ($current_lang === 'en') {
                            $doc_type_name = $doc['type_name_en'] ?? 'N/A';
                        } elseif ($current_lang === 'my') {
                            $doc_type_name = $doc['type_name_my'] ?? 'N/A';
                        }
                    ?>
                        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg hover:shadow-xl transition overflow-hidden group border <?php echo $border_class; ?>">
                            <div class="p-6">
                                <div class="<?php echo $colors['bg']; ?> w-16 h-16 rounded-lg flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 <?php echo $colors['text']; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="font-semibold <?php echo $text_class; ?> mb-2 truncate" title="<?php echo htmlspecialchars($doc['file_name_custom']); ?>">
                                    <?php echo htmlspecialchars($doc['file_name_custom']); ?>
                                </h3>
                                <div class="space-y-1 mb-4">
                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                        <span class="font-medium"><?php echo $t['type']; ?>:</span> <?php echo htmlspecialchars($doc_type_name); ?>
                                    </p>
                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                        <span class="font-medium"><?php echo $t['uploaded']; ?>:</span> <?php echo date('M d, Y', strtotime($doc['upload_at'])); ?>
                                    </p>
                                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                        <span class="font-medium"><?php echo $t['by']; ?>:</span> <?php echo htmlspecialchars($uploader_name); ?>
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <!-- Preview/Open Button (for PDF and Images) -->
                                    <?php if (in_array($file_ext, ['pdf', 'jpg', 'jpeg', 'png'])): ?>
                                    <a href="<?php echo BASE_PATH . '/' . $doc['file_path']; ?>" target="_blank"
                                       class="flex items-center justify-center w-full px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <?php echo $file_ext === 'pdf' ? $t['open_pdf'] : $t['view_image']; ?>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <!-- Action Buttons Row -->
                                    <div class="flex space-x-2">
                                        <a href="<?php echo BASE_PATH . '/' . $doc['file_path']; ?>" download
                                           class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition text-center">
                                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            <?php echo $t['download']; ?>
                                        </a>
                                        <button onclick="deleteDocument(<?php echo $doc['doc_id']; ?>, '<?php echo addslashes($doc['file_name_custom']); ?>')"
                                                class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center space-x-2 mb-6">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&doc_type=<?php echo $doc_type_filter; ?>" 
                       class="px-4 py-2 border <?php echo $is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition"><?php echo $t['previous']; ?></a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&doc_type=<?php echo $doc_type_filter; ?>" 
                       class="px-4 py-2 border rounded-lg transition <?php echo $i === $page ? 'bg-green-600 text-white border-green-600' : ($is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-50'); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&doc_type=<?php echo $doc_type_filter; ?>" 
                       class="px-4 py-2 border <?php echo $is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition"><?php echo $t['next']; ?></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal-backdrop">
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border <?php echo $border_class; ?> m-4">
            <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4 flex items-center justify-between rounded-t-lg">
                <h3 class="text-xl font-bold text-white"><?php echo $t['upload_modal_title']; ?></h3>
                <button onclick="closeUploadModal()" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data" id="uploadForm" class="p-6 space-y-4">
                <input type="hidden" name="action" value="upload">
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        <?php echo $t['document_name']; ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="file_name_custom" id="file_name_custom" required
                           placeholder="<?php echo $t['document_name_placeholder']; ?>"
                           class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        <?php echo $t['document_type']; ?> <span class="text-red-500">*</span>
                    </label>
                    <select name="doc_type_id" id="doc_type_id" required
                            class="w-full px-4 py-3 border <?php echo $input_class; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">-- <?php echo $t['document_type']; ?> --</option>
                        <?php foreach ($doc_types as $type): 
                            $type_label = '';
                            if ($current_lang === 'th') {
                                $type_label = $type['type_name_th'] ?? 'N/A';
                            } elseif ($current_lang === 'en') {
                                $type_label = $type['type_name_en'] ?? 'N/A';
                            } elseif ($current_lang === 'my') {
                                $type_label = $type['type_name_my'] ?? 'N/A';
                            }
                        ?>
                            <option value="<?php echo $type['doc_type_id']; ?>">
                                <?php echo htmlspecialchars($type_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        <?php echo $t['upload_file']; ?> <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="document" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed <?php echo $is_dark ? 'border-gray-600 hover:border-green-500 bg-gray-700 hover:bg-gray-600' : 'border-gray-300 hover:border-green-500 bg-gray-50 hover:bg-gray-100'; ?> rounded-lg cursor-pointer transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <span class="font-semibold"><?php echo $t['click_to_upload']; ?></span>
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <?php echo $t['file_types']; ?>
                                </p>
                            </div>
                            <input type="file" id="document" name="document" required class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" onchange="displayFileName(this)">
                        </label>
                    </div>
                    <p id="fileName" class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-2"></p>
                </div>

                <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300"><?php echo $t['important']; ?></p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                <?php echo $t['important_note']; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeUploadModal()" 
                            class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        <?php echo $t['cancel']; ?>
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <?php echo $t['upload_document']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="doc_id" id="deleteDocId">
    </form>

    <script>
        const t = <?php echo json_encode($t); ?>;
        const currentLang = '<?php echo $current_lang; ?>';

        function openUploadModal() {
            document.getElementById('uploadModal').classList.add('active');
            document.getElementById('uploadForm').reset();
            document.getElementById('fileName').textContent = '';
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
        }

        function displayFileName(input) {
            const fileName = input.files[0]?.name;
            const fileSize = input.files[0]?.size;
            if (fileName) {
                const sizeMB = (fileSize / (1024 * 1024)).toFixed(2);
                document.getElementById('fileName').innerHTML = `<strong>📄 ${t['selected']}:</strong> ${fileName} (${sizeMB} MB)`;
            }
        }

        function deleteDocument(docId, docName) {
            if (confirm(t['confirm_delete'] + '\n\n' + t['document_name'] + ': ' + docName)) {
                document.getElementById('deleteDocId').value = docId;
                document.getElementById('deleteForm').submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('uploadModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUploadModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUploadModal();
            }
        });

        // Form validation before submit
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileName = document.getElementById('file_name_custom').value.trim();
            const docType = document.getElementById('doc_type_id').value;
            const file = document.getElementById('document').files[0];

            if (!fileName) {
                e.preventDefault();
                alert(t['document_name'] + ' ' + t['required']);
                return false;
            }

            if (!docType) {
                e.preventDefault();
                alert(t['document_type'] + ' ' + t['required']);
                return false;
            }

            if (!file) {
                e.preventDefault();
                alert(t['upload_file'] + ' ' + t['required']);
                return false;
            }

            if (file.size > 10 * 1024 * 1024) {
                e.preventDefault();
                alert('File size too large! Maximum 10MB allowed.\nYour file: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB');
                return false;
            }

            const allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!allowedExts.includes(fileExt)) {
                e.preventDefault();
                alert('Invalid file type! Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG');
                return false;
            }

            return true;
        });

        // Drag and drop functionality
        const dropZone = document.querySelector('label[for="document"]');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-green-500', '!bg-green-50', 'dark:!bg-green-900');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-green-500', '!bg-green-50', 'dark:!bg-green-900');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = document.getElementById('document');
            input.files = files;
            displayFileName(input);
        }
    </script>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>