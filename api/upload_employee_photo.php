<?php
/**
 * Employee Photo Upload Component
 * Reusable component for uploading employee profile photos
 * 
 * Required variables:
 * - $employee_id
 * - $employee (array with profile_pic_path)
 * - $theme (theme classes array)
 * - $is_dark (boolean)
 */

if (!isset($employee_id) || !isset($employee)) {
    return;
}

$profile_pic_url = $employee['profile_pic_path'] ?? '';
?>

<!-- Employee Photo Upload Section -->
<section class="<?php echo $theme['card_bg']; ?> rounded-lg shadow-lg p-6 mb-6 border <?php echo $theme['border']; ?> theme-transition">
    <h2 class="text-xl font-bold <?php echo $theme['text']; ?> mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <?php echo get_text('profile_photo'); ?>
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Current Photo Display -->
        <div>
            <label class="block text-sm font-medium <?php echo $theme['text']; ?> mb-3">
                <?php echo get_text('current_photo'); ?>
            </label>
            <div class="flex justify-center">
                <div class="relative group">
                    <img src="<?php echo !empty($profile_pic_url) ? htmlspecialchars($profile_pic_url) : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23cbd5e0\' stroke-width=\'1.5\'%3E%3Cpath d=\'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\'/%3E%3Ccircle cx=\'12\' cy=\'7\' r=\'4\'/%3E%3C/svg%3E'; ?>" 
                         alt="<?php echo htmlspecialchars($employee_name ?? 'Employee'); ?>" 
                         id="currentPhotoDisplay"
                         class="w-48 h-48 object-cover rounded-xl border-4 <?php echo $is_dark ? 'border-gray-700' : 'border-gray-200'; ?> shadow-xl transition-all group-hover:shadow-2xl"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23cbd5e0\' stroke-width=\'1.5\'%3E%3Cpath d=\'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\'/%3E%3Ccircle cx=\'12\' cy=\'7\' r=\'4\'/%3E%3C/svg%3E'">
                    
                    <?php if (!empty($profile_pic_url)): ?>
                        <div class="absolute -top-3 -right-3 bg-green-500 text-white rounded-full p-2 shadow-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Photo Upload Form -->
        <div>
            <label class="block text-sm font-medium <?php echo $theme['text']; ?> mb-3">
                <?php echo get_text('upload_new_photo'); ?>
            </label>
            
            <form id="employeePhotoUploadForm" class="space-y-4" onsubmit="handlePhotoUpload(event)">
                
                <!-- Preview Area -->
                <div id="photoPreviewContainer" class="hidden flex justify-center mb-4">
                    <div class="relative">
                        <img id="photoPreview" 
                             class="w-48 h-48 object-cover rounded-xl border-4 border-blue-500 shadow-xl" 
                             alt="Preview">
                        <button type="button" 
                                onclick="clearPhotoPreview()"
                                class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg transition-colors"
                                title="<?php echo get_text('remove'); ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- File Input with Drag & Drop -->
                <div class="flex items-center justify-center w-full">
                    <label for="photoFileInput" 
                           class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-xl cursor-pointer transition-all
                                  <?php echo $is_dark ? 'border-gray-600 hover:border-blue-500 bg-gray-800/50 hover:bg-gray-700/50' : 'border-gray-300 hover:border-blue-500 bg-gray-50 hover:bg-blue-50'; ?>"
                           ondrop="handleDrop(event)" 
                           ondragover="handleDragOver(event)" 
                           ondragleave="handleDragLeave(event)">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4">
                            <svg class="w-12 h-12 mb-4 <?php echo $is_dark ? 'text-gray-400' : 'text-gray-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-2 text-sm <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> font-medium text-center">
                                <span class="font-semibold"><?php echo get_text('click_to_upload'); ?></span> 
                            </p>
                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mb-1 text-center">
                                <?php echo get_text('or_drag_and_drop'); ?>
                            </p>
                            <p class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-500'; ?> text-center">
                                JPG, PNG, GIF (<?php echo get_text('max_5mb'); ?>)
                            </p>
                        </div>
                        <input id="photoFileInput" 
                               type="file" 
                               class="hidden" 
                               accept="image/jpeg,image/jpg,image/png,image/gif"
                               onchange="previewEmployeePhoto(event)">
                    </label>
                </div>
                
                <!-- Upload Progress Bar -->
                <div id="uploadProgress" class="hidden">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                        <div id="uploadProgressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p class="text-sm text-center <?php echo $theme['text_secondary']; ?>">
                        <span id="uploadProgressText">0%</span>
                    </p>
                </div>
                
                <!-- Upload Button -->
                <button type="submit" 
                        id="uploadPhotoBtn"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-3.5 rounded-lg transition-colors font-medium disabled:cursor-not-allowed hidden shadow-md hover:shadow-lg">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <span id="uploadBtnText"><?php echo get_text('upload_photo'); ?></span>
                    </span>
                </button>
                
                <!-- Info Alert -->
                <div class="flex items-start p-4 bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm <?php echo $is_dark ? 'text-blue-300' : 'text-blue-800'; ?>">
                        <?php echo get_text('photo_upload_info'); ?>
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Photo upload handling
let selectedFile = null;

function previewEmployeePhoto(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file
    if (!validatePhotoFile(file)) {
        event.target.value = '';
        return;
    }
    
    selectedFile = file;
    showPhotoPreview(file);
}

function validatePhotoFile(file) {
    // Validate file size (5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('<?php echo get_text('file_too_large'); ?>');
        return false;
    }
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        alert('<?php echo get_text('invalid_file_type'); ?>');
        return false;
    }
    
    return true;
}

function showPhotoPreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('photoPreview');
        const container = document.getElementById('photoPreviewContainer');
        const uploadBtn = document.getElementById('uploadPhotoBtn');
        
        preview.src = e.target.result;
        container.classList.remove('hidden');
        uploadBtn.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function clearPhotoPreview() {
    document.getElementById('photoFileInput').value = '';
    document.getElementById('photoPreviewContainer').classList.add('hidden');
    document.getElementById('uploadPhotoBtn').classList.add('hidden');
    selectedFile = null;
}

function handleDrop(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('border-blue-500', 'bg-blue-50');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        if (validatePhotoFile(file)) {
            selectedFile = file;
            showPhotoPreview(file);
        }
    }
}

function handleDragOver(event) {
    event.preventDefault();
    event.currentTarget.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/30');
}

function handleDragLeave(event) {
    event.currentTarget.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/30');
}

function handlePhotoUpload(event) {
    event.preventDefault();
    
    if (!selectedFile) {
        alert('<?php echo get_text('please_select_photo'); ?>');
        return;
    }
    
    const formData = new FormData();
    formData.append('photo', selectedFile);
    formData.append('employee_id', '<?php echo $employee_id; ?>');
    
    const uploadBtn = document.getElementById('uploadPhotoBtn');
    const uploadBtnText = document.getElementById('uploadBtnText');
    const progressContainer = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    
    const originalText = uploadBtnText.textContent;
    
    // Show progress
    uploadBtn.disabled = true;
    progressContainer.classList.remove('hidden');
    
    // Simulate progress (since we can't track real upload progress easily)
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        if (progress <= 90) {
            progressBar.style.width = progress + '%';
            progressText.textContent = progress + '%';
        }
    }, 100);
    
    uploadBtnText.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> <?php echo get_text('uploading'); ?>...';
    
    const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
    const url = basePath ? `${basePath}/api/upload_employee_photo.php` : '/api/upload_employee_photo.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        progressText.textContent = '100%';
        
        if (result.success) {
            // Update current photo display
            const currentPhoto = document.getElementById('currentPhotoDisplay');
            if (currentPhoto && result.data && result.data.photo_url) {
                currentPhoto.src = result.data.photo_url;
            }
            
            // Show success message
            alert('✓ <?php echo get_text('photo_uploaded'); ?>');
            
            // Reset form
            clearPhotoPreview();
            progressContainer.classList.add('hidden');
            progressBar.style.width = '0%';
            
            // Reload page after delay
            setTimeout(() => location.reload(), 500);
        } else {
            throw new Error(result.message || 'Upload failed');
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        alert('<?php echo get_text('upload_failed'); ?>: ' + error.message);
        progressContainer.classList.add('hidden');
        progressBar.style.width = '0%';
    })
    .finally(() => {
        uploadBtn.disabled = false;
        uploadBtnText.textContent = originalText;
    });
}
</script>