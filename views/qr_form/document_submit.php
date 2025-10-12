<?php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Localization.php';

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    
    $employee_id = $_POST['employee_id'] ?? '';
    $service_category_id = $_POST['service_category_id'] ?? '';
    $service_type_id = $_POST['service_type_id'] ?? '';
    
    // Get employee data
    $stmt = $conn->prepare("SELECT e.*, p.position_name_th, pl.level_name_th, s.section_name_th 
                            FROM employees e 
                            LEFT JOIN position_master p ON e.position_id = p.position_id 
                            LEFT JOIN position_level_master pl ON e.position_level_id = pl.level_id 
                            LEFT JOIN section_master s ON e.section_id = s.section_id 
                            WHERE e.employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($employee) {
        // Insert document submission
        $stmt = $conn->prepare("INSERT INTO document_submissions 
                                (employee_id, employee_name, position, position_level, section, service_category_id, service_type_id, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'New')");
        $stmt->bind_param("sssssii", 
            $employee['employee_id'],
            $employee['full_name_th'],
            $employee['position_name_th'],
            $employee['level_name_th'],
            $employee['section_name_th'],
            $service_category_id,
            $service_type_id
        );
        
        if ($stmt->execute()) {
            $message = 'Document submission recorded successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to record submission';
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Employee not found';
        $message_type = 'error';
    }
    
    $conn->close();
}

// Get master data for dropdowns
$conn = getDbConnection();
$employees = $conn->query("SELECT employee_id, full_name_th, full_name_en FROM employees WHERE status_id = 1 ORDER BY employee_id")->fetch_all(MYSQLI_ASSOC);
$service_categories = $conn->query("SELECT * FROM service_category_master ORDER BY category_id")->fetch_all(MYSQLI_ASSOC);
$service_types = $conn->query("SELECT * FROM service_type_master ORDER BY type_id")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Submission - HR Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen p-4">
    
    <div class="container mx-auto max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-block bg-white rounded-full p-4 shadow-lg mb-4">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Document Submission</h1>
            <p class="text-gray-600 mt-2">Please fill in the form to submit your document</p>
        </div>

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
                <?php if ($message_type === 'success'): ?>
                    <div class="mt-4">
                        <button onclick="resetForm()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Submit Another Document
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <form method="POST" action="" id="documentForm">
                
                <!-- Employee ID Selection (CRITICAL: Dropdown with Auto-fill) -->
                <div class="mb-6">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Employee ID <span class="text-red-500">*</span>
                    </label>
                    <select id="employee_id" name="employee_id" required onchange="loadEmployeeData()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Employee ID --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                <?php echo htmlspecialchars($emp['employee_id']); ?> - <?php echo htmlspecialchars($emp['full_name_th']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Employee Information (Auto-filled, READ-ONLY) -->
                <div id="employeeInfoSection" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                    <h3 class="font-semibold text-gray-800 mb-3">Employee Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Full Name</label>
                            <input type="text" id="display_name" readonly 
                                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-gray-700 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Position</label>
                            <input type="text" id="display_position" readonly 
                                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-gray-700 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Position Level</label>
                            <input type="text" id="display_level" readonly 
                                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-gray-700 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                            <input type="text" id="display_section" readonly 
                                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded text-gray-700 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Service Category -->
                <div class="mb-6">
                    <label for="service_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Category <span class="text-red-500">*</span>
                    </label>
                    <select id="service_category_id" name="service_category_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Service Category --</option>
                        <?php foreach ($service_categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name_th']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Service Type -->
                <div class="mb-6">
                    <label for="service_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Type <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <?php foreach ($service_types as $type): ?>
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="service_type_id" value="<?php echo $type['type_id']; ?>" required
                                       class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 font-medium text-gray-700">
                                    <?php echo htmlspecialchars($type['type_name_th']); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Please Note</p>
                            <ul class="text-xs text-yellow-700 mt-1 list-disc list-inside">
                                <li>Ensure all information is correct before submitting</li>
                                <li>Once submitted, data cannot be modified</li>
                                <li>Keep your submission receipt for reference</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Submit Document
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                © 2025 HR Service System. Scan QR Code to access this form.
            </p>
        </div>
    </div>

    <script>
        // Store employee data
        const employeeData = <?php echo json_encode($employees); ?>;
        
        function loadEmployeeData() {
            const employeeId = document.getElementById('employee_id').value;
            
            if (!employeeId) {
                document.getElementById('employeeInfoSection').classList.add('hidden');
                return;
            }
            
            // Fetch employee details via AJAX
            fetch('/api/get_employee.php?id=' + employeeId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const emp = data.employee;
                        
                        // Auto-fill READ-ONLY fields
                        document.getElementById('display_name').value = emp.full_name_th || '';
                        document.getElementById('display_position').value = emp.position_name || '';
                        document.getElementById('display_level').value = emp.level_name || '';
                        document.getElementById('display_section').value = emp.section_name || '';
                        
                        // Show employee info section
                        document.getElementById('employeeInfoSection').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading employee data:', error);
                    alert('Failed to load employee data');
                });
        }

        // Form validation
        document.getElementById('documentForm').addEventListener('submit', function(e) {
            const employeeId = document.getElementById('employee_id').value;
            const categoryId = document.getElementById('service_category_id').value;
            const typeId = document.querySelector('input[name="service_type_id"]:checked');
            
            if (!employeeId) {
                e.preventDefault();
                alert('Please select an employee');
                return;
            }
            
            if (!categoryId) {
                e.preventDefault();
                alert('Please select a service category');
                return;
            }
            
            if (!typeId) {
                e.preventDefault();
                alert('Please select a service type');
                return;
            }
            
            if (!confirm('Are you sure you want to submit this document?')) {
                e.preventDefault();
            }
        });

        function resetForm() {
            document.getElementById('documentForm').reset();
            document.getElementById('employeeInfoSection').classList.add('hidden');
        }

        // Auto-focus on employee dropdown
        window.addEventListener('load', function() {
            document.getElementById('employee_id').focus();
        });
    </script>
</body>
</html>