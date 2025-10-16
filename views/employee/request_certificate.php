<?php
/**
 * Request Certificate Form
 * Employee can request certificate with type selection
 */

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

AuthController::requireAuth();

$page_title = 'ขอหนังสือรับรอง';
extract(get_theme_vars());

$conn = getDbConnection();

// Get employee info
$emp_sql = "SELECT e.*, p.position_name_th, d.division_name_th, dep.department_name_th, ht.type_name_th as hiring_type_name
            FROM employees e
            LEFT JOIN position_master p ON e.position_id = p.position_id
            LEFT JOIN division_master d ON e.division_id = d.division_id
            LEFT JOIN department_master dep ON e.department_id = dep.department_id
            LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
            WHERE e.employee_id = ?";
$emp_stmt = $conn->prepare($emp_sql);
$emp_stmt->bind_param("s", $user_id);
$emp_stmt->execute();
$employee = $emp_stmt->get_result()->fetch_assoc();
$emp_stmt->close();

// Get active certificate types
$cert_types = [];
$types_result = $conn->query("SELECT * FROM certificate_types WHERE is_active = 1 ORDER BY cert_type_id");
while ($row = $types_result->fetch_assoc()) {
    $cert_types[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cert_type_id = $_POST['cert_type_id'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    
    if (empty($cert_type_id)) {
        $error = 'กรุณาเลือกประเภทหนังสือรับรอง';
    } else {
        // Generate certificate number
        $cert_no = 'CERT-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $insert_sql = "INSERT INTO certificate_requests 
                      (certificate_no, employee_id, cert_type_id, purpose, status, created_at) 
                      VALUES (?, ?, ?, ?, 'New', CURRENT_TIMESTAMP)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssis", $cert_no, $user_id, $cert_type_id, $purpose);
        
        if ($insert_stmt->execute()) {
            $success = "ส่งคำขอเรียบร้อยแล้ว! เลขที่: $cert_no";
        } else {
            $error = "เกิดข้อผิดพลาด: " . $insert_stmt->error;
        }
        $insert_stmt->close();
    }
}

$conn->close();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 lg:ml-64 p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold <?php echo $text_class; ?> flex items-center">
            <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            ขอหนังสือรับรอง
        </h1>
        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mt-1">
            ยื่นคำขอหนังสือรับรองจากบริษัท
        </p>
    </div>

    <?php if (isset($success)): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-green-700 dark:text-green-300 font-medium"><?php echo htmlspecialchars($success); ?></p>
        </div>
        <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="text-green-700 dark:text-green-300 underline text-sm mt-2 inline-block">
            ดูคำขอของฉัน →
        </a>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-red-700 dark:text-red-300 font-medium"><?php echo htmlspecialchars($error); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 border <?php echo $border_class; ?>">
        <form method="POST">
            <!-- Employee Info -->
            <div class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> rounded-lg p-6 mb-6">
                <h3 class="font-semibold <?php echo $text_class; ?> mb-4 flex items-center text-lg">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    ข้อมูลพนักงาน
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">รหัสพนักงาน</label>
                        <p class="<?php echo $text_class; ?> font-semibold"><?php echo htmlspecialchars($employee['employee_id']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">ชื่อ-นามสกุล</label>
                        <p class="<?php echo $text_class; ?> font-semibold"><?php echo htmlspecialchars($employee['full_name_th']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">ตำแหน่ง</label>
                        <p class="<?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['position_name_th']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">สังกัด</label>
                        <p class="<?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['division_name_th']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">วันที่เข้าทำงาน</label>
                        <p class="<?php echo $text_class; ?>"><?php echo date('d/m/Y', strtotime($employee['date_of_hire'])); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium <?php echo $is_dark ? 'text-gray-300' : 'text-gray-700'; ?> mb-1">ประเภทการจ้าง</label>
                        <p class="<?php echo $text_class; ?>"><?php echo htmlspecialchars($employee['hiring_type_name']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Certificate Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-3">
                    ประเภทหนังสือรับรอง <span class="text-red-500">*</span>
                </label>
                
                <?php if (empty($cert_types)): ?>
                    <div class="text-center py-8 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                        <p class="<?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">ยังไม่มีประเภทหนังสือรับรองในระบบ</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($cert_types as $type): ?>
                            <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition hover:border-blue-500 hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-blue-50'; ?> <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>">
                                <input type="radio" name="cert_type_id" value="<?php echo $type['cert_type_id']; ?>" required class="mt-1 sr-only peer">
                                <div class="flex-1 peer-checked:font-semibold">
                                    <div class="<?php echo $text_class; ?> font-medium mb-1">
                                        <?php echo htmlspecialchars($type['type_name_th']); ?>
                                    </div>
                                    <?php if (!empty($type['type_name_en'])): ?>
                                        <div class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?>">
                                            <?php echo htmlspecialchars($type['type_name_en']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <svg class="w-6 h-6 text-blue-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Purpose -->
            <div class="mb-6">
                <label class="block text-sm font-semibold <?php echo $text_class; ?> mb-2">
                    วัตถุประสงค์ <span class="text-red-500">*</span>
                </label>
                <textarea name="purpose" rows="4" required
                    placeholder="ระบุวัตถุประสงค์ในการขอหนังสือรับรอง (เช่น เพื่อทำวีซ่า, เพื่อกู้เงิน, ฯลฯ)"
                    class="w-full px-4 py-3 border-2 rounded-lg <?php echo $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300'; ?> focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition resize-none"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-semibold text-lg shadow-lg">
                    ✓ ส่งคำขอ
                </button>
                <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold text-lg text-center shadow-lg">
                    ยกเลิก
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>