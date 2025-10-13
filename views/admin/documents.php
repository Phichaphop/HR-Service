<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireRole(['admin', 'officer']);

$page_title = 'Online Documents';

ensure_session_started();
$user_id = $_SESSION['user_id'];

$message = '';
$message_type = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getDbConnection();
    
    if ($_POST['action'] === 'upload') {
        $file_name_custom = trim($_POST['file_name_custom'] ?? '');
        $doc_type_id = intval($_POST['doc_type_id'] ?? 0);
        
        // Debug
        error_log("Upload attempt - Name: $file_name_custom, Type: $doc_type_id");
        error_log("Files array: " . print_r($_FILES, true));
        
        if (empty($file_name_custom)) {
            $message = 'Document name is required';
            $message_type = 'error';
        } elseif ($doc_type_id <= 0) {
            $message = 'Please select a document type';
            $message_type = 'error';
        } elseif (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOAD_PATH_DOCUMENTS;
            
            // Create directory if not exists
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
            
            error_log("File extension: $file_ext, Size: " . $_FILES['document']['size']);
            
            if (!in_array($file_ext, $allowed_exts)) {
                $message = 'Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG';
                $message_type = 'error';
            } elseif ($_FILES['document']['size'] > (UPLOAD_MAX_SIZE * 2)) {
                $message = 'File size too large (Max 10MB)';
                $message_type = 'error';
            } else {
                $new_filename = 'doc_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                error_log("Attempting to move file to: $upload_path");
                
                if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
                    $file_path = 'uploads/documents/' . $new_filename;
                    
                    $stmt = $conn->prepare("INSERT INTO online_documents (file_name_custom, file_path, doc_type_id, upload_by, upload_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssis", $file_name_custom, $file_path, $doc_type_id, $user_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Document uploaded successfully!';
                        $message_type = 'success';
                        error_log("Upload successful: $file_name_custom");
                    } else {
                        $message = 'Failed to save document information: ' . $stmt->error;
                        $message_type = 'error';
                        error_log("Database error: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $message = 'Failed to upload file. Check folder permissions.';
                    $message_type = 'error';
                    error_log("move_uploaded_file failed. Target: $upload_path");
                }
            }
        } elseif (isset($_FILES['document'])) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
            ];
            $error_code = $_FILES['document']['error'];
            $message = 'Upload error: ' . ($error_messages[$error_code] ?? "Unknown error code: $error_code");
            $message_type = 'error';
            error_log("Upload error code: $error_code");
        } else {
            $message = 'No file selected';
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
                $message = 'Document deleted successfully';
                $message_type = 'success';
            } else {
                $message = 'Failed to delete document';
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
    $where_conditions[] = "(file_name_custom LIKE ? OR e.full_name_th LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
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
$sql = "SELECT od.*, dt.type_name_th, dt.type_name_en, e.full_name_th, e.full_name_en 
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

<!-- Main Content with proper margin -->
<div class="lg:ml-64 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        
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

        <!-- Debug Info (remove in production) -->
        <?php if (isset($_POST['action']) && $_POST['action'] === 'upload'): ?>
            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 rounded text-sm">
                <strong>Debug Info:</strong><br>
                POST Data: <?php echo htmlspecialchars(print_r($_POST, true)); ?><br>
                FILES: <?php echo htmlspecialchars(print_r($_FILES, true)); ?><br>
                Upload Dir: <?php echo UPLOAD_PATH_DOCUMENTS; ?><br>
                Dir Exists: <?php echo file_exists(UPLOAD_PATH_DOCUMENTS) ? 'Yes' : 'No'; ?><br>
                Dir Writable: <?php echo is_writable(UPLOAD_PATH_DOCUMENTS) ? 'Yes' : 'No'; ?>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="mb-6 bg-gradient-to-r from-green-600 to-teal-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Online Documents</h1>
                        <p class="text-green-100 mt-1">Manage company documents, forms, and resources</p>
                    </div>
                </div>
                <button onclick="openUploadModal()" 
                        class="hidden md:flex items-center px-6 py-3 bg-white text-green-600 rounded-lg font-medium hover:bg-green-50 transition shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Upload Document
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">Total Documents</p>
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
            ?>
            <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1 truncate">
                            <?php echo get_master('doc_type_master', $stat['doc_type_id']); ?>
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
            <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">No Data</p>
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

        <!-- Filters -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 mb-6">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">Search</label>
                    <input type="text" name="search" placeholder="Document name or uploader..."
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-2 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">Document Type</label>
                    <select name="doc_type" class="w-full px-4 py-2 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Types</option>
                        <?php foreach ($doc_types as $type): ?>
                            <option value="<?php echo $type['doc_type_id']; ?>" <?php echo $doc_type_filter == $type['doc_type_id'] ? 'selected' : ''; ?>>
                                <?php echo get_master('doc_type_master', $type['doc_type_id']); ?>
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
                        Filter
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
                <div class="col-span-full <?php echo $card_bg; ?> rounded-lg shadow-lg p-12 text-center">
                    <svg class="w-20 h-20 mx-auto mb-4 <?php echo $is_dark ? 'text-gray-600' : 'text-gray-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> text-lg font-medium">No documents found</p>
                    <p class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm mt-2">Upload your first document to get started</p>
                    <button onclick="openUploadModal()" 
                            class="mt-4 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                        Upload Document
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
                ?>
                    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg hover:shadow-xl transition overflow-hidden group">
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
                                    <span class="font-medium">Type:</span> <?php echo get_master('doc_type_master', $doc['doc_type_id']); ?>
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <span class="font-medium">Uploaded:</span> <?php echo date('M d, Y', strtotime($doc['upload_at'])); ?>
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <span class="font-medium">By:</span> <?php echo htmlspecialchars($language === 'en' ? ($doc['full_name_en'] ?? 'Unknown') : ($doc['full_name_th'] ?? 'ไม่ทราบ')); ?>
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
                                    <?php echo $file_ext === 'pdf' ? 'Open PDF' : 'View Image'; ?>
                                </a>
                                <?php endif; ?>
                                
                                <!-- Action Buttons Row -->
                                <div class="flex space-x-2">
                                    <a href="<?php echo BASE_PATH . '/' . $doc['file_path']; ?>" download
                                       class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition text-center">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Download
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
        <div class="flex justify-center space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&doc_type=<?php echo $doc_type_filter; ?>" 
                   class="px-4 py-2 border <?php echo $is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&doc_type=<?php echo $doc_type_filter; ?>" 
                   class="px-4 py-2 border rounded-lg transition <?php echo $i === $page ? 'bg-green-600 text-white border-green-600' : ($is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-50'); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&doc_type=<?php echo $doc_type_filter; ?>" 
                   class="px-4 py-2 border <?php echo $is_dark ? 'border-gray-600 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4 flex items-center justify-between rounded-t-lg">
                <h3 class="text-xl font-bold text-white">Upload Document</h3>
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
                        Document Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="file_name_custom" id="file_name_custom" required
                           placeholder="e.g., Employee Handbook 2025"
                           class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Document Type <span class="text-red-500">*</span>
                    </label>
                    <select name="doc_type_id" id="doc_type_id" required
                            class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">-- Select Document Type --</option>
                        <?php foreach ($doc_types as $type): ?>
                            <option value="<?php echo $type['doc_type_id']; ?>">
                                <?php echo get_master('doc_type_master', $type['doc_type_id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Upload File <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="document" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed <?php echo $is_dark ? 'border-gray-600 hover:border-green-500 bg-gray-700 hover:bg-gray-600' : 'border-gray-300 hover:border-green-500 bg-gray-50 hover:bg-gray-100'; ?> rounded-lg cursor-pointer transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (MAX. 10MB)
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
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Important</p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                Ensure documents do not contain sensitive information unless properly secured.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeUploadModal()" 
                            class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload Document
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
    function openUploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
        // Clear previous values
        document.getElementById('uploadForm').reset();
        document.getElementById('fileName').textContent = '';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    function displayFileName(input) {
        const fileName = input.files[0]?.name;
        const fileSize = input.files[0]?.size;
        if (fileName) {
            const sizeMB = (fileSize / (1024 * 1024)).toFixed(2);
            document.getElementById('fileName').innerHTML = `<strong>📄 Selected:</strong> ${fileName} (${sizeMB} MB)`;
        }
    }

    function deleteDocument(docId, docName) {
        if (confirm('Are you sure you want to delete this document?\n\nDocument: ' + docName + '\n\nThis action cannot be undone.')) {
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
            alert('Please enter a document name');
            return false;
        }

        if (!docType) {
            e.preventDefault();
            alert('Please select a document type');
            return false;
        }

        if (!file) {
            e.preventDefault();
            alert('Please select a file to upload');
            return false;
        }

        // Check file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            e.preventDefault();
            alert('File size too large! Maximum 10MB allowed.\nYour file: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB');
            return false;
        }

        // Check file extension
        const allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!allowedExts.includes(fileExt)) {
            e.preventDefault();
            alert('Invalid file type! Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG');
            return false;
        }

        console.log('Form validation passed. Submitting...');
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