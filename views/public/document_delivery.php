<?php
/**
 * Document Delivery System (Public Access - No Login Required)
 * ระบบลงชื่อส่งเอกสาร
 */

require_once __DIR__ . '/../../config/db_config.php';

$conn = getDbConnection();

// Get active employees for datalist
$employees = [];
$emp_result = $conn->query("SELECT employee_id, full_name_th FROM employees WHERE status_id = 1 ORDER BY employee_id");
while ($row = $emp_result->fetch_assoc()) {
    $employees[] = $row;
}

// Get document categories
$categories = [];
$cat_result = $conn->query("SELECT category_id, category_name_th FROM service_category_master ORDER BY category_id");
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบลงชื่อส่งเอกสาร - HR Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .star-rating {
            display: inline-flex;
            gap: 8px;
        }
        
        .star-rating input[type="radio"] {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #d1d5db;
            transition: color 0.2s;
        }
        
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #fbbf24;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ระบบลงชื่อส่งเอกสาร</h1>
                <p class="text-gray-600">Document Delivery System</p>
            </div>

            <!-- Form -->
            <form id="deliveryForm" onsubmit="submitDelivery(event)" class="space-y-6">
                <!-- Employee ID with Datalist -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        รหัสพนักงาน <span class="text-red-500">*</span>
                    </label>
                    <input list="employeeList" 
                           id="employee_id" 
                           name="employee_id" 
                           required
                           placeholder="พิมพ์เพื่อค้นหารหัสพนักงาน"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    <datalist id="employeeList">
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                <?php echo htmlspecialchars($emp['full_name_th']); ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                    <div id="employeePreview" class="mt-2 text-sm text-gray-600"></div>
                </div>

                <!-- Delivery Type (ส่ง/รับ) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        ประเภทการส่งเอกสาร <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition hover:border-blue-500 border-blue-500 bg-blue-50">
                            <input type="radio" name="delivery_type" value="ส่ง" checked class="sr-only">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                </svg>
                                <span class="font-semibold text-blue-700">ส่งเอกสาร</span>
                            </div>
                        </label>
                        
                        <label class="relative flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer transition hover:border-green-500">
                            <input type="radio" name="delivery_type" value="รับ" class="sr-only">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                                <span class="font-semibold text-gray-700">รับเอกสาร</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Service Type (คนเดียว/กลุ่ม) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        ประเภทการส่ง <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition hover:border-blue-500 border-blue-500 bg-blue-50">
                            <input type="radio" name="service_type" value="คนเดียว" checked class="sr-only">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="font-semibold text-blue-700">คนเดียว</span>
                            </div>
                        </label>
                        
                        <label class="relative flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer transition hover:border-purple-500">
                            <input type="radio" name="service_type" value="กลุ่ม" class="sr-only">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="font-semibold text-gray-700">กลุ่ม</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Document Category Buttons -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        เลือกเอกสารที่รับหรือส่ง <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <?php foreach ($categories as $cat): ?>
                            <button type="button" 
                                    onclick="toggleCategory(this, <?php echo $cat['category_id']; ?>)"
                                    class="category-btn p-4 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                                <div class="text-sm font-medium text-gray-700">
                                    <?php echo htmlspecialchars($cat['category_name_th']); ?>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selected_category" name="document_category_id" required>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        หมายเหตุ
                    </label>
                    <textarea name="remarks" 
                              rows="3" 
                              placeholder="ระบุรายละเอียดเพิ่มเติม (ถ้ามี)"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition resize-none"></textarea>
                </div>

                <!-- Rating -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        คะแนนความพึงพอใจ <span class="text-red-500">*</span>
                    </label>
                    <div class="star-rating flex justify-center">
                        <input type="radio" name="satisfaction_score" value="5" id="star5" required>
                        <label for="star5">★</label>
                        <input type="radio" name="satisfaction_score" value="4" id="star4">
                        <label for="star4">★</label>
                        <input type="radio" name="satisfaction_score" value="3" id="star3">
                        <label for="star3">★</label>
                        <input type="radio" name="satisfaction_score" value="2" id="star2">
                        <label for="star2">★</label>
                        <input type="radio" name="satisfaction_score" value="1" id="star1">
                        <label for="star1">★</label>
                    </div>
                    <div class="text-center mt-2 text-sm text-gray-600">
                        กรุณาให้คะแนนความพึงพอใจ (1-5 ดาว)
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-4 rounded-lg font-bold text-lg hover:from-blue-600 hover:to-purple-700 transform hover:scale-105 transition shadow-lg">
                        ✓ ยืนยันการส่งเอกสาร
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>HR Service System © <?php echo date('Y'); ?></p>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 hidden"></div>

    <script>
        // Employee validation and preview
        const employeeData = <?php echo json_encode($employees); ?>;
        
        document.getElementById('employee_id').addEventListener('input', function() {
            const input = this.value;
            const preview = document.getElementById('employeePreview');
            
            const employee = employeeData.find(emp => emp.employee_id === input);
            if (employee) {
                preview.innerHTML = '✓ <strong>' + employee.full_name_th + '</strong>';
                preview.className = 'mt-2 text-sm text-green-600 font-medium';
            } else if (input) {
                preview.innerHTML = '⚠ รหัสพนักงานไม่ถูกต้อง';
                preview.className = 'mt-2 text-sm text-red-600';
            } else {
                preview.innerHTML = '';
            }
        });
        
        // Radio button styling
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const parent = this.closest('.grid');
                parent.querySelectorAll('label').forEach(label => {
                    label.classList.remove('border-blue-500', 'bg-blue-50', 'border-green-500', 'bg-green-50', 'border-purple-500', 'bg-purple-50');
                    label.classList.add('border-gray-300');
                });
                
                const selectedLabel = this.closest('label');
                if (this.value === 'ส่ง') {
                    selectedLabel.classList.add('border-blue-500', 'bg-blue-50');
                } else if (this.value === 'รับ') {
                    selectedLabel.classList.add('border-green-500', 'bg-green-50');
                } else if (this.value === 'กลุ่ม') {
                    selectedLabel.classList.add('border-purple-500', 'bg-purple-50');
                } else {
                    selectedLabel.classList.add('border-blue-500', 'bg-blue-50');
                }
                selectedLabel.classList.remove('border-gray-300');
            });
        });
        
        // Category button toggle
        function toggleCategory(btn, categoryId) {
            // Remove active class from all buttons
            document.querySelectorAll('.category-btn').forEach(b => {
                b.classList.remove('border-blue-500', 'bg-blue-100');
                b.classList.add('border-gray-300');
            });
            
            // Add active class to clicked button
            btn.classList.remove('border-gray-300');
            btn.classList.add('border-blue-500', 'bg-blue-100');
            
            // Set hidden input
            document.getElementById('selected_category').value = categoryId;
        }
        
        // Form submission
        function submitDelivery(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            
            // Validate employee ID
            const empId = formData.get('employee_id');
            const validEmployee = employeeData.find(emp => emp.employee_id === empId);
            
            if (!validEmployee) {
                showToast('กรุณาเลือกรหัสพนักงานที่ถูกต้อง', 'error');
                return;
            }
            
            // Validate category selected
            if (!formData.get('document_category_id')) {
                showToast('กรุณาเลือกประเภทเอกสาร', 'error');
                return;
            }
            
            // Validate satisfaction score
            if (!formData.get('satisfaction_score')) {
                showToast('กรุณาให้คะแนนความพึงพอใจ', 'error');
                return;
            }
            
            // Prepare data
            const data = {
                employee_id: formData.get('employee_id'),
                delivery_type: formData.get('delivery_type'),
                service_type: formData.get('service_type'),
                document_category_id: formData.get('document_category_id'),
                remarks: formData.get('remarks'),
                satisfaction_score: formData.get('satisfaction_score')
            };
            
            // Disable submit button
            const submitBtn = event.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> กำลังบันทึก...';
            
            // Submit
            fetch('<?php echo BASE_PATH; ?>/api/submit_document_delivery.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('บันทึกสำเร็จ! ขอบคุณสำหรับการใช้บริการ', 'success');
                    setTimeout(() => {
                        event.target.reset();
                        document.getElementById('employeePreview').innerHTML = '';
                        document.querySelectorAll('.category-btn').forEach(b => {
                            b.classList.remove('border-blue-500', 'bg-blue-100');
                            b.classList.add('border-gray-300');
                        });
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '✓ ยืนยันการส่งเอกสาร';
                    }, 2000);
                } else {
                    showToast('เกิดข้อผิดพลาด: ' + result.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '✓ ยืนยันการส่งเอกสาร';
                }
            })
            .catch(error => {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✓ ยืนยันการส่งเอกสาร';
            });
        }
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            
            toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${type === 'success' 
                            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                        }
                    </svg>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            
            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => {
                    toast.className = 'fixed top-4 right-4 hidden';
                }, 300);
            }, 3000);
        }
        
        // Star rating interaction
        document.querySelectorAll('.star-rating input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const rating = this.value;
                const labels = document.querySelectorAll('.star-rating label');
                labels.forEach((label, index) => {
                    if (index >= 5 - rating) {
                        label.style.color = '#fbbf24';
                    } else {
                        label.style.color = '#d1d5db';
                    }
                });
            });
        });
    </script>
    
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</body>
</html>