<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';

AuthController::requireRole(['admin']);

$page_title = 'Company Settings';

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getDbConnection();
    
    if ($_POST['action'] === 'update_company') {
        $company_name_th = $_POST['company_name_th'] ?? '';
        $company_name_en = $_POST['company_name_en'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $fax = $_POST['fax'] ?? '';
        $address = $_POST['address'] ?? '';
        $representative_name = $_POST['representative_name'] ?? '';
        
        // Handle logo upload
        $logo_path = '';
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOAD_PATH_COMPANY;
            $file_ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_exts) && $_FILES['company_logo']['size'] <= UPLOAD_MAX_SIZE) {
                $new_filename = 'company_logo_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                    $logo_path = 'uploads/company/' . $new_filename;
                }
            }
        }
        
        // Check if company record exists
        $check = $conn->query("SELECT company_id FROM company_info LIMIT 1");
        
        if ($check->num_rows > 0) {
            // Update existing
            if ($logo_path) {
                $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, phone = ?, fax = ?, address = ?, representative_name = ?, company_logo_path = ?, updated_at = CURRENT_TIMESTAMP");
                $stmt->bind_param("sssssss", $company_name_th, $company_name_en, $phone, $fax, $address, $representative_name, $logo_path);
            } else {
                $stmt = $conn->prepare("UPDATE company_info SET company_name_th = ?, company_name_en = ?, phone = ?, fax = ?, address = ?, representative_name = ?, updated_at = CURRENT_TIMESTAMP");
                $stmt->bind_param("ssssss", $company_name_th, $company_name_en, $phone, $fax, $address, $representative_name);
            }
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO company_info (company_name_th, company_name_en, phone, fax, address, representative_name, company_logo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $company_name_th, $company_name_en, $phone, $fax, $address, $representative_name, $logo_path);
        }
        
        if ($stmt->execute()) {
            $message = 'Company information updated successfully';
            $message_type = 'success';
        } else {
            $message = 'Failed to update company information';
            $message_type = 'error';
        }
        
        $stmt->close();
    }
    
    $conn->close();
}

// Get company info
$conn = getDbConnection();
$result = $conn->query("SELECT * FROM company_info LIMIT 1");
$company = $result->fetch_assoc();
$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-5xl">
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                <div class="flex items-center">
                    <?php if ($message_type === 'success'): ?>
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php endif; ?>
                    <p class="<?php echo $message_type === 'success' ? 'text-green-700' : 'text-red-700'; ?> font-medium">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Company Settings Form -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Company Settings</h2>
                            <p class="text-blue-100 text-sm">Manage company information and branding</p>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <span class="px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-full text-white text-sm font-medium">
                            Admin Only
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-6">
                <input type="hidden" name="action" value="update_company">
                
                <!-- Current Logo Preview -->
                <?php if ($company && $company['company_logo_path']): ?>
                <div class="mb-6">
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Current Logo
                    </label>
                    <div class="flex items-center space-x-4">
                        <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($company['company_logo_path']); ?>" 
                             alt="Company Logo" 
                             class="h-20 w-auto border-2 border-gray-200 rounded-lg shadow-sm">
                        <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                            Logo is displayed on certificates and documents
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Company Logo Upload -->
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Company Logo <?php if (!$company || !$company['company_logo_path']): ?><span class="text-red-500">*</span><?php endif; ?>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed <?php echo $is_dark ? 'border-gray-600 hover:border-blue-500' : 'border-gray-300 hover:border-blue-500'; ?> rounded-lg cursor-pointer <?php echo $is_dark ? 'bg-gray-700 hover:bg-gray-600' : 'bg-gray-50 hover:bg-gray-100'; ?> transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                    PNG, JPG or GIF (MAX. 5MB)
                                </p>
                            </div>
                            <input type="file" name="company_logo" class="hidden" accept="image/*">
                        </label>
                    </div>
                </div>

                <!-- Company Names -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            Company Name (Thai) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="company_name_th" required
                               value="<?php echo htmlspecialchars($company['company_name_th'] ?? ''); ?>"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            Company Name (English) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="company_name_en" required
                               value="<?php echo htmlspecialchars($company['company_name_en'] ?? ''); ?>"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            Phone <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone" required
                               value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>"
                               placeholder="042-123-456"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                            Fax
                        </label>
                        <input type="tel" name="fax"
                               value="<?php echo htmlspecialchars($company['fax'] ?? ''); ?>"
                               placeholder="042-123-457"
                               class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Address <span class="text-red-500">*</span>
                    </label>
                    <textarea name="address" rows="3" required
                              class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="123 Main Street, District, Province, Postal Code"><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea>
                </div>

                <!-- Representative Name -->
                <div>
                    <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-2">
                        Representative Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="representative_name" required
                           value="<?php echo htmlspecialchars($company['representative_name'] ?? ''); ?>"
                           placeholder="Mr. John Doe"
                           class="w-full px-4 py-3 border <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">
                        This person's name will appear on official documents and certificates
                    </p>
                </div>

                <!-- Important Notice -->
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Important Notice</p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                This information will be displayed on all official documents, certificates, and employee records. Please ensure accuracy.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="<?php echo BASE_PATH; ?>/index.php" 
                       class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>