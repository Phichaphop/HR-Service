<?php

/**
 * Admin Create Request - WITH EMPLOYEE ID CARD SUPPORT
 * File: /views/admin/admin_create_request.php
 * 
 * ✅ Create Leave requests for employees
 * ✅ Create Certificate requests for employees
 * ✅ Create Employee ID Card requests for employees (NEW)
 * ✅ Auto-fill employee data with datalist
 * ✅ Dropdown list for Leave Types, Certificate Types, and ID Card Request Reasons
 * ✅ FIXED: Removed base_salary from employees table (only in certificate_requests)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
// ============================================
// API ENDPOINT (Handle AJAX requests)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_employee_info') {
    header('Content-Type: application/json');

    try {
        AuthController::requireRole(['admin', 'officer']);

        $employee_id = trim($_GET['id'] ?? '');

        if (empty($employee_id)) {
            throw new Exception('Employee ID required');
        }

        $conn = getDbConnection();

        // Get employee data WITHOUT base_salary (it's only in certificate_requests)
        $sql = "SELECT 
            e.employee_id,
            e.full_name_th,
            e.full_name_en,
            e.date_of_hire,
            pm.position_name_th,
            dm.department_name_th,
            dv.division_name_th,
            sm.section_name_th,
            ht.type_name_th as hiring_type_name
        FROM employees e
        LEFT JOIN position_master pm ON e.position_id = pm.position_id
        LEFT JOIN department_master dm ON e.department_id = dm.department_id
        LEFT JOIN division_master dv ON e.division_id = dv.division_id
        LEFT JOIN section_master sm ON e.section_id = sm.section_id
        LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
        WHERE e.employee_id = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Employee not found');
        }

        $emp = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        echo json_encode([
            'success' => true,
            'data' => [
                'employee_id' => $emp['employee_id'],
                'employee_name' => $emp['full_name_th'] . ' (' . $emp['full_name_en'] . ')',
                'position' => $emp['position_name_th'] ?? '-',
                'department' => $emp['department_name_th'] ?? '-',
                'division' => $emp['division_name_th'] ?? '-',
                'section' => $emp['section_name_th'] ?? '-',
                'hiring_type' => $emp['hiring_type_name'] ?? '-',
                'date_of_hire' => $emp['date_of_hire'] ?? '-',
            ]
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
// ============================================
// PAGE RENDERING
// ============================================
AuthController::requireRole(['admin', 'officer']);
$current_lang = $_SESSION['language'] ?? 'th';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$is_dark = ($theme_mode === 'dark');
// Theme classes
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500';
$label_class = $is_dark ? 'text-gray-300' : 'text-gray-700';
// Translations
// Translations
$t = [
    'th' => [
        'page_title' => 'สร้างคำขอเพื่อพนักงาน',
        'page_subtitle' => 'สร้างคำขอ (ใบลา / หนังสือรับรอง / บัตรพนักงาน) ให้พนักงาน',
        'select_employee' => 'เลือกพนักงาน',
        'search_employee' => 'พิมพ์รหัส หรือ ชื่อพนักงาน...',
        'select_request_type' => 'เลือกประเภทคำขอ',
        'employee_information' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'employee_name' => 'ชื่อพนักงาน',
        'position' => 'ตำแหน่ง',
        'department' => 'แผนก',
        'division' => 'สังกัด',
        'section' => 'สายงาน',
        'date_of_hire' => 'วันที่เข้าทำงาน',
        'hiring_type' => 'ประเภทการจ้าง',
        'base_salary' => 'เงินเดือนพื้นฐาน',
        'leave_request' => 'คำขอใบลา',
        'leave_type' => 'ประเภทใบลา',
        'sick_leave' => 'ลาป่วย',
        'sick_leave_unpaid' => 'ลาป่วยไม่รับค่าจ้าง',
        'annual_leave' => 'ลาพักร้อน',
        'personal_leave' => 'ลากิจ',
        'personal_leave_unpaid' => 'ลากิจไม่รับค่าจ้าง',
        'maternity_leave' => 'ลาคลอด',
        'maternity_leave_unpaid' => 'ลาคลอดไม่รับค่าจ้าง',
        'paternity_leave' => 'ลาบวช',
        'paternity_leave_unpaid' => 'ลาบวชไม่รับค่าจ้าง',
        'start_date' => 'วันที่เริ่มต้น',
        'end_date' => 'วันที่สิ้นสุด',
        'total_days' => 'จำนวนวัน',
        'reason' => 'เหตุผล',
        'certificate_request' => 'คำขอหนังสือรับรอง',
        'certificate_type' => 'ประเภทหนังสือรับรอง',
        'purpose' => 'วัตถุประสงค์',
        'id_card_request' => 'คำขอบัตรพนักงาน',
        'id_card_reason' => 'เหตุผลในการขอ',
        'information_update' => 'อัปเดตข้อมูล',
        'information_update_desc' => 'ข้อมูลเปลี่ยนแปลง (ตำแหน่ง, รูปถ่าย)',
        'lost_id_card' => 'บัตรหาย',
        'lost_id_card_desc' => 'สูญหายบัตรพนักงาน',
        'damaged_id_card' => 'บัตรเสียหาย',
        'damaged_id_card_desc' => 'บัตรเสียหายต้องเปลี่ยน',
        'first_time_issue' => 'ครั้งแรก',
        'first_time_issue_desc' => 'ขอบัตรพนักงานฉบับแรก',
        'important_notice' => 'ประกาศสำคัญ',
        'notice_1' => '✓ เลือกพนักงานและประเภทคำขอ',
        'notice_2' => '✓ ข้อมูลพนักงานจะแสดงอัตโนมัติ',
        'notice_3' => '✓ กรอกข้อมูลให้ครบถ้วนก่อนส่งคำขอ',
        'create_request' => 'สร้างคำขอ',
        'cancel' => 'ยกเลิก',
        'days' => 'วัน',
        'required' => 'จำเป็น',
        'please_fill' => 'กรุณากรอกข้อมูลให้ครบ',
        'success' => 'สร้างคำขอเรียบร้อยแล้ว!',
        'error' => 'เกิดข้อผิดพลาด',
        'no_types_available' => 'ยังไม่มีประเภทหนังสือรับรองในระบบ',
    ],
    'en' => [
        'page_title' => 'Create Request for Employee',
        'page_subtitle' => 'Create requests (Leave / Certificate / ID Card) for employees',
        'select_employee' => 'Select Employee',
        'search_employee' => 'Type ID or employee name...',
        'select_request_type' => 'Select Request Type',
        'employee_information' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'employee_name' => 'Employee Name',
        'position' => 'Position',
        'department' => 'Department',
        'division' => 'Division',
        'section' => 'Section',
        'date_of_hire' => 'Date of Hire',
        'hiring_type' => 'Hiring Type',
        'base_salary' => 'Base Salary',
        'leave_request' => 'Leave Request',
        'leave_type' => 'Leave Type',
        'sick_leave' => 'Sick Leave',
        'sick_leave_unpaid' => 'Sick Leave Unpaid',
        'annual_leave' => 'Annual Leave',
        'personal_leave' => 'Personal Leave',
        'personal_leave_unpaid' => 'Personal Leave Unpaid',
        'maternity_leave' => 'Maternity Leave',
        'maternity_leave_unpaid' => 'Maternity Leave Unpaid',
        'paternity_leave' => 'Paternity Leave',
        'paternity_leave_unpaid' => 'Paternity Leave Unpaid',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'total_days' => 'Total Days',
        'reason' => 'Reason',
        'certificate_request' => 'Certificate Request',
        'certificate_type' => 'Certificate Type',
        'purpose' => 'Purpose',
        'id_card_request' => 'ID Card Request',
        'id_card_reason' => 'Request Reason',
        'information_update' => 'Information Update',
        'information_update_desc' => 'My information has changed',
        'lost_id_card' => 'Lost ID Card',
        'lost_id_card_desc' => 'Lost and need replacement',
        'damaged_id_card' => 'Damaged ID Card',
        'damaged_id_card_desc' => 'Damaged and needs replacement',
        'first_time_issue' => 'First Time Issue',
        'first_time_issue_desc' => 'Requesting first ID card',
        'important_notice' => 'Important Notice',
        'notice_1' => '✓ Select employee and request type',
        'notice_2' => '✓ Employee data will appear automatically',
        'notice_3' => '✓ Fill in all required fields before submitting',
        'create_request' => 'Create Request',
        'cancel' => 'Cancel',
        'days' => 'days',
        'required' => 'Required',
        'please_fill' => 'Please fill in all required fields',
        'success' => 'Request created successfully!',
        'error' => 'An error occurred',
        'no_types_available' => 'No certificate types available in the system',
    ],
    'my' => [
        'page_title' => 'ဝန်ထမ်းအတွက် တောင်းဆိုချက်ဖန်တီးရန်',
        'page_subtitle' => 'ဝန်ထမ်းများအတွက် တောင်းဆိုချက်များ (ခွင့်ရက် / လက်မှတ် / မှတ်ပုံတင်) ဖန်တီးပါ',
        'select_employee' => 'ဝန်ထမ်းရွေးချယ်ပါ',
        'search_employee' => 'ID သို့မဟုတ် ဝန်ထမ်းအမည်ရိုက်ထည့်ပါ...',
        'select_request_type' => 'တောင်းဆိုချက်အမျိုးအစားရွေးချယ်ပါ',
        'employee_information' => 'ဝန်ထမ်းအချက်အလက်',
        'employee_id' => 'ဝန်ထမ်း ID',
        'employee_name' => 'ဝန်ထမ်းအမည်',
        'position' => 'ရာထူး',
        'department' => 'ဌာနခွဲ',
        'division' => 'ဌာန',
        'section' => 'ကဏ္ဍ',
        'date_of_hire' => 'ဝင်ရောက်လုပ်ကိုင်သည့်ရက်',
        'hiring_type' => 'ခန့်အပ်မှုအမျိုးအစား',
        'base_salary' => 'အခြေခံလစာ',
        'leave_request' => 'ခွင့်တောင်းခံခြင်း',
        'leave_type' => 'ခွင့်အမျိုးအစား',
        'sick_leave' => 'နာမကျန်းခွင့်',
        'sick_leave_unpaid' => 'လစာမရှိ နာမကျန်းခွင့်',
        'annual_leave' => 'နှစ်ပတ်လည်ခွင့်',
        'personal_leave' => 'ကိုယ်ရေးကိုယ်တာခွင့်',
        'personal_leave_unpaid' => 'လစာမရှိ ကိုယ်ရေးကိုယ်တာခွင့်',
        'maternity_leave' => 'မီးဖွားခွင့်',
        'maternity_leave_unpaid' => 'လစာမရှိ မီးဖွားခွင့်',
        'paternity_leave' => 'ရဟန်းထွက်ခွင့်',
        'paternity_leave_unpaid' => 'လစာမရှိ ရဟန်းထွက်ခွင့်',
        'start_date' => 'စတင်သည့်ရက်',
        'end_date' => 'ပြီးဆုံးသည့်ရက်',
        'total_days' => 'စုစုပေါင်းရက်',
        'reason' => 'အကြောင်းရင်း',
        'certificate_request' => 'လက်မှတ်တောင်းခံခြင်း',
        'certificate_type' => 'လက်မှတ်အမျိုးအစား',
        'purpose' => 'ရည်ရွယ်ချက်',
        'id_card_request' => 'မှတ်ပုံတင်တောင်းခံခြင်း',
        'id_card_reason' => 'တောင်းခံသည့်အကြောင်းရင်း',
        'information_update' => 'အချက်အလက်အသစ်',
        'information_update_desc' => 'အချက်အလက်ပြောင်းလဲခြင်း (ရာထူး၊ ဓာတ်ပုံ)',
        'lost_id_card' => 'မှတ်ပုံတင်ပျောက်ဆုံးခြင်း',
        'lost_id_card_desc' => 'မှတ်ပုံတင်ပျောက်ဆုံးသွားခြင်း',
        'damaged_id_card' => 'မှတ်ပုံတင်ပျက်စီးခြင်း',
        'damaged_id_card_desc' => 'မှတ်ပုံတင်ပျက်စီး၍ အစားထိုးရန်',
        'first_time_issue' => 'ပထမဆုံးအကြိမ်',
        'first_time_issue_desc' => 'ပထမဆုံးမှတ်ပုံတင်တောင်းခံခြင်း',
        'important_notice' => 'အရေးကြီးသောအသိပေးချက်',
        'notice_1' => '✓ ဝန်ထမ်းနှင့် တောင်းဆိုချက်အမျိုးအစားရွေးချယ်ပါ',
        'notice_2' => '✓ ဝန်ထမ်းအချက်အလက်များ အလိုအလျောက်ပေါ်လာပါမည်',
        'notice_3' => '✓ တင်သွင်းခြင်းမပြုမီ လိုအပ်သောနေရာများပြည့်စုံစွာဖြည့်ပါ',
        'create_request' => 'တောင်းဆိုချက်ဖန်တီးရန်',
        'cancel' => 'ပယ်ဖျက်ရန်',
        'days' => 'ရက်',
        'required' => 'လိုအပ်သည်',
        'please_fill' => 'ကျေးဇူးပြု၍ လိုအပ်သောနေရာများပြည့်စုံစွာဖြည့်ပါ',
        'success' => 'တောင်းဆိုချက်အောင်မြင်စွာဖန်တီးပြီးပါပြီ!',
        'error' => 'အမှားအယွင်းတစ်ခုဖြစ်ပေါ်ခဲ့သည်',
        'no_types_available' => 'စနစ်တွင် လက်မှတ်အမျိုးအစားများမရှိသေးပါ',
    ]
][$current_lang] ?? $t['th'];
$message = '';
$message_type = '';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request'])) {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $request_type = $_POST['request_type'] ?? '';

    if (empty($employee_id) || empty($request_type)) {
        $message = $t['please_fill'];
        $message_type = 'error';
    } else {
        $conn = getDbConnection();
        $inserted = false;

        if ($request_type === 'leave') {
            $leave_type = $_POST['leave_type'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $total_days = intval($_POST['total_days'] ?? 0);
            $reason = $_POST['reason'] ?? '';

            if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($total_days)) {
                $message = $t['please_fill'];
                $message_type = 'error';
            } else {
                $sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, status, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, 'New', NOW(), NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssis", $employee_id, $leave_type, $start_date, $end_date, $total_days, $reason);
                $inserted = $stmt->execute();
                $stmt->close();
            }
        } elseif ($request_type === 'certificate') {
            $cert_type_id = $_POST['cert_type_id'] ?? '';
            $base_salary = floatval($_POST['base_salary_cert'] ?? 0);
            $purpose = $_POST['purpose'] ?? '';

            if (empty($cert_type_id) || empty($base_salary) || empty($purpose)) {
                $message = $t['please_fill'];
                $message_type = 'error';
            } else {
                // Generate certificate number
                $cert_no = 'CERT-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

                // Fetch employee data for certificate
                $emp_sql = "SELECT e.*, 
                    COALESCE(p.position_name_th, p.position_name_en) as position_name,
                    COALESCE(d.division_name_th, d.division_name_en) as division_name,
                    COALESCE(dep.department_name_th, dep.department_name_en) as department_name,
                    COALESCE(ht.type_name_th, ht.type_name_en) as hiring_type_name
                    FROM employees e
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN division_master d ON e.division_id = d.division_id
                    LEFT JOIN department_master dep ON e.department_id = dep.department_id
                    LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
                    WHERE e.employee_id = ? LIMIT 1";
                $emp_stmt = $conn->prepare($emp_sql);
                $emp_stmt->bind_param("s", $employee_id);
                $emp_stmt->execute();
                $emp_result = $emp_stmt->get_result();
                $emp = $emp_result->fetch_assoc();
                $emp_stmt->close();

                if ($emp) {
                    $employee_name = trim(($emp['full_name_th'] ?? '') . ' ' . ($emp['full_name_en'] ?? ''));
                    $position = $emp['position_name'] ?? '';
                    $division = $emp['division_name'] ?? '';
                    $date_of_hire = $emp['date_of_hire'] ?? null;
                    $hiring_type = $emp['hiring_type_name'] ?? '';

                    $cert_sql = "INSERT INTO certificate_requests 
                        (certificate_no, employee_id, cert_type_id, employee_name, position, division, date_of_hire, hiring_type, base_salary, purpose, status, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New', NOW(), NOW())";
                    $cert_stmt = $conn->prepare($cert_sql);
                    $cert_stmt->bind_param("ssisssdsss", $cert_no, $employee_id, $cert_type_id, $employee_name, $position, $division, $date_of_hire, $hiring_type, $base_salary, $purpose);
                    $inserted = $cert_stmt->execute();
                    $cert_stmt->close();
                }
            }
        } elseif ($request_type === 'id_card') {
            $reason = $_POST['id_card_reason'] ?? '';

            if (empty($reason)) {
                $message = $t['please_fill'];
                $message_type = 'error';
            } else {
                $sql = "INSERT INTO id_card_requests (employee_id, reason, status, created_at, updated_at) 
                        VALUES (?, ?, 'New', NOW(), NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $employee_id, $reason);
                $inserted = $stmt->execute();
                $stmt->close();
            }
        }

        $conn->close();

        if ($inserted) {
            $message = $t['success'];
            $message_type = 'success';
        } else {
            $message = $t['error'];
            $message_type = 'error';
        }
    }
}
// Get employees for datalist
$conn = getDbConnection();
$employees_datalist = '';
$result = $conn->query("SELECT employee_id, full_name_th, full_name_en FROM employees ORDER BY employee_id");
while ($row = $result->fetch_assoc()) {
    $display = $row['employee_id'] . ' - ' . $row['full_name_th'];
    $employees_datalist .= "<option value='" . htmlspecialchars($row['employee_id']) . "'>" . htmlspecialchars($display) . "</option>";
}
// Get certificate types
$cert_types = [];
$types_result = $conn->query("SELECT cert_type_id, 
    COALESCE(type_name_th, type_name_en) as type_name
    FROM certificate_types 
    WHERE is_active = 1 
    ORDER BY cert_type_id");
if ($types_result) {
    while ($row = $types_result->fetch_assoc()) {
        $cert_types[] = $row;
    }
}
$conn->close();
// Leave type options
$leave_types = [
    'Annual Leave' => $t['annual_leave'],
    'Sick Leave' => $t['sick_leave'],
    'Sick Leave Unpaid' => $t['sick_leave_unpaid'],
    'Personal Leave' => $t['personal_leave'],
    'Personal Leave Unpaid' => $t['personal_leave_unpaid'],
    'Maternity Leave' => $t['maternity_leave'],
    'Maternity Leave Unpaid' => $t['maternity_leave_unpaid'],
    'Paternity Leave' => $t['paternity_leave'],
    'Paternity Leave Unpaid' => $t['paternity_leave_unpaid'],
];

// ID Card reason options
$id_card_reasons = [
    'Information Update' => $t['information_update_desc'],
    'Lost ID Card' => $t['lost_id_card_desc'],
    'Damaged ID Card' => $t['damaged_id_card_desc'],
    'First Time Issue' => $t['first_time_issue_desc'],
];
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="lg:ml-64">
    <div class="container mx-auto px-4 py-6 max-w-4xl">

        <!-- Error Alert Container -->
        <div id="alertContainer">
            <?php if ($message): ?>
                <div class="mb-6 p-4 <?php echo $message_type === 'success' ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200'; ?> rounded-lg flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1"><?php echo htmlspecialchars($message); ?></div>
                </div>
            <?php endif; ?>
        </div>
        <!-- Page Header -->
        <div class="mb-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-md p-6">
            <div class="flex items-center gap-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-blue-100 text-sm mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>
        <!-- Main Form Card -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-md border <?php echo $border_class; ?> p-6">
            <form method="POST" id="requestForm">

                <!-- Employee Selection Section -->
                <div class="mb-8 pb-8 border-b <?php echo $border_class; ?>">
                    <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php echo $t['select_employee']; ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <!-- Employee Selection with Datalist -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['select_employee']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="employeeInput"
                                name="employee_id"
                                list="employees"
                                class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="<?php echo $t['search_employee']; ?>"
                                required
                                onchange="loadEmployeeData(this.value)">
                            <datalist id="employees">
                                <?php echo $employees_datalist; ?>
                            </datalist>
                        </div>
                        <!-- Request Type Selection -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['select_request_type']; ?> <span class="text-red-500">*</span>
                            </label>
                            <select id="requestTypeSelect" name="request_type" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-blue-500" required onchange="changeRequestType(this.value)">
                                <option value="">-- <?php echo $t['select_request_type']; ?> --</option>
                                <option value="leave"><?php echo $t['leave_request']; ?></option>
                                <option value="certificate"><?php echo $t['certificate_request']; ?></option>
                                <option value="id_card"><?php echo $t['id_card_request']; ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Employee Information Section -->
                <div class="mb-8 pb-8 border-b <?php echo $border_class; ?>">
                    <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo $t['employee_information']; ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Employee ID -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['employee_id']; ?></label>
                            <input type="text" id="dispEmployeeId" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                        <!-- Employee Name -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['employee_name']; ?></label>
                            <input type="text" id="dispEmployeeName" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                        <!-- Position -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['position']; ?></label>
                            <input type="text" id="dispPosition" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                        <!-- Department -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['department']; ?></label>
                            <input type="text" id="dispDepartment" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                        <!-- Division -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['division']; ?></label>
                            <input type="text" id="dispDivision" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                        <!-- Section -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['section']; ?></label>
                            <input type="text" id="dispSection" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                        <!-- Date of Hire -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['date_of_hire']; ?></label>
                            <input type="text" id="dispDateOfHire" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                        <!-- Hiring Type -->
                        <div>
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2"><?php echo $t['hiring_type']; ?></label>
                            <input type="text" id="dispHiringType" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> opacity-75 cursor-not-allowed" readonly>
                        </div>
                    </div>
                </div>
                <!-- Dynamic Form Content -->
                <div id="formContent" class="mb-8">

                    <!-- LEAVE REQUEST FORM -->
                    <div id="leaveForm" class="hidden">
                        <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <?php echo $t['leave_request']; ?>
                        </h2>

                        <div class="mb-6">
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['leave_type']; ?> <span class="text-red-500">*</span>
                            </label>
                            <select name="leave_type" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- <?php echo $t['leave_type']; ?> --</option>
                                <?php foreach ($leave_types as $key => $label): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- Start Date -->
                            <div>
                                <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                    <?php echo $t['start_date']; ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="start_date" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500" onchange="calculateLeaveDays()">
                            </div>
                            <!-- End Date -->
                            <div>
                                <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                    <?php echo $t['end_date']; ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="end_date" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500" onchange="calculateLeaveDays()">
                            </div>
                        </div>
                        <!-- Total Days -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['total_days']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_days" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500" min="1" placeholder="0" readonly>
                        </div>
                        <!-- Reason -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['reason']; ?>
                            </label>
                            <textarea name="reason" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-green-500 resize-none" rows="4" placeholder="..."></textarea>
                        </div>
                    </div>

                    <!-- CERTIFICATE REQUEST FORM -->
                    <div id="certificateForm" class="hidden">
                        <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <?php echo $t['certificate_request']; ?>
                        </h2>

                        <!-- Certificate Type Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-3">
                                <?php echo $t['certificate_type']; ?> <span class="text-red-500">*</span>
                            </label>
                            <?php if (empty($cert_types)): ?>
                                <div class="p-4 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?> text-center rounded-lg">
                                    <p class="<?php echo $label_class; ?>"><?php echo $t['no_types_available']; ?></p>
                                </div>
                            <?php else: ?>
                                <select name="cert_type_id" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">-- <?php echo $t['certificate_type']; ?> --</option>
                                    <?php foreach ($cert_types as $type): ?>
                                        <option value="<?php echo $type['cert_type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <!-- Base Salary (for certificate only) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['base_salary']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="base_salary_cert" step="0.01" min="0" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="0.00">
                        </div>
                        <!-- Purpose -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-2">
                                <?php echo $t['purpose']; ?> <span class="text-red-500">*</span>
                            </label>
                            <textarea name="purpose" class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none" rows="4" placeholder="..."></textarea>
                        </div>
                    </div>

                    <!-- ID CARD REQUEST FORM (NEW) -->
                    <div id="idCardForm" class="hidden">
                        <h2 class="text-lg font-bold <?php echo $text_class; ?> mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v10a2 2 0 002 2h5m0 0h5a2 2 0 002-2v-10a2 2 0 00-2-2h-5m0 0V5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.153m-13.5 0H21m-7 10v2m0-2v-2m0 2h2m-2 0h-2"></path>
                            </svg>
                            <?php echo $t['id_card_request']; ?>
                        </h2>

                        <!-- ID Card Request Reason -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium <?php echo $label_class; ?> mb-3">
                                <?php echo $t['id_card_reason']; ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-3">
                                <?php foreach ($id_card_reasons as $key => $label): ?>
                                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition <?php echo $border_class; ?>">
                                        <input type="radio" name="id_card_reason" value="<?php echo htmlspecialchars($key); ?>" class="w-4 h-4 text-pink-600">
                                        <div class="ml-3">
                                            <p class="font-medium <?php echo $text_class; ?>"><?php echo htmlspecialchars($label); ?></p>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Important Notice -->
                <div class="mb-8 p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 rounded">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-yellow-800 dark:text-yellow-300"><?php echo $t['important_notice']; ?></p>
                            <ul class="text-sm text-yellow-700 dark:text-yellow-400 mt-2 space-y-1">
                                <li><?php echo $t['notice_1']; ?></li>
                                <li><?php echo $t['notice_2']; ?></li>
                                <li><?php echo $t['notice_3']; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Form Buttons -->
                <div class="flex flex-col md:flex-row gap-4 pt-6 border-t <?php echo $border_class; ?>">
                    <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php" class="flex-1 px-6 py-3 border rounded-lg <?php echo $border_class; ?> <?php echo $text_class; ?> hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition font-medium text-center">
                        <?php echo $t['cancel']; ?>
                    </a>
                    <button type="submit" name="create_request" class="flex-1 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8m0 8l-6-2m6 2l6-2"></path>
                        </svg>
                        <?php echo $t['create_request']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
<script>
    function loadEmployeeData(employeeId) {
        if (!employeeId) {
            clearEmployeeFields();
            return;
        }
        const url = '<?php echo BASE_PATH; ?>/views/admin/admin_create_request.php?action=get_employee_info&id=' + encodeURIComponent(employeeId);

        fetch(url)
            .then(r => r.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success && data.data) {
                        document.getElementById('dispEmployeeId').value = data.data.employee_id || '';
                        document.getElementById('dispEmployeeName').value = data.data.employee_name || '';
                        document.getElementById('dispPosition').value = data.data.position || '';
                        document.getElementById('dispDepartment').value = data.data.department || '';
                        document.getElementById('dispDivision').value = data.data.division || '';
                        document.getElementById('dispSection').value = data.data.section || '';
                        document.getElementById('dispDateOfHire').value = data.data.date_of_hire || '';
                        document.getElementById('dispHiringType').value = data.data.hiring_type || '';
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                } catch (e) {
                    alert('Error: ' + e.message);
                }
            })
            .catch(e => {
                alert('Fetch error: ' + e.message);
            });
    }

    function clearEmployeeFields() {
        document.getElementById('dispEmployeeId').value = '';
        document.getElementById('dispEmployeeName').value = '';
        document.getElementById('dispPosition').value = '';
        document.getElementById('dispDepartment').value = '';
        document.getElementById('dispDivision').value = '';
        document.getElementById('dispSection').value = '';
        document.getElementById('dispDateOfHire').value = '';
        document.getElementById('dispHiringType').value = '';
    }

    function changeRequestType(type) {
        document.getElementById('leaveForm').classList.add('hidden');
        document.getElementById('certificateForm').classList.add('hidden');
        document.getElementById('idCardForm').classList.add('hidden');
        if (type === 'leave') {
            document.getElementById('leaveForm').classList.remove('hidden');
        } else if (type === 'certificate') {
            document.getElementById('certificateForm').classList.remove('hidden');
        } else if (type === 'id_card') {
            document.getElementById('idCardForm').classList.remove('hidden');
        }
    }

    function calculateLeaveDays() {
        const startDateEl = document.querySelector('#leaveForm input[name="start_date"]');
        const endDateEl = document.querySelector('#leaveForm input[name="end_date"]');
        const totalDaysEl = document.querySelector('#leaveForm input[name="total_days"]');

        if (startDateEl && endDateEl && startDateEl.value && endDateEl.value) {
            const start = new Date(startDateEl.value);
            const end = new Date(endDateEl.value);

            if (end < start) {
                alert('End date must be after start date');
                endDateEl.value = '';
                totalDaysEl.value = '';
                return;
            }

            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            totalDaysEl.value = diffDays;
        }
    }
    // Custom form validation
    document.getElementById('requestForm').addEventListener('submit', function(e) {
        const employeeId = document.getElementById('employeeInput').value.trim();
        const requestType = document.getElementById('requestTypeSelect').value;

        if (!employeeId || !requestType) {
            e.preventDefault();
            alert('<?php echo $t["please_fill"]; ?>');
            return false;
        }

        // Validate based on request type
        if (requestType === 'leave') {
            const leaveType = document.querySelector('#leaveForm select[name="leave_type"]')?.value || '';
            const startDate = document.querySelector('#leaveForm input[name="start_date"]')?.value || '';
            const endDate = document.querySelector('#leaveForm input[name="end_date"]')?.value || '';
            const totalDays = document.querySelector('#leaveForm input[name="total_days"]')?.value || '';

            if (!leaveType || !startDate || !endDate || !totalDays) {
                e.preventDefault();
                alert('<?php echo $t["please_fill"]; ?>');
                return false;
            }
        } else if (requestType === 'certificate') {
            const certTypeId = document.querySelector('#certificateForm select[name="cert_type_id"]')?.value || '';
            const baseSalary = document.querySelector('#certificateForm input[name="base_salary_cert"]')?.value || '';
            const purpose = document.querySelector('#certificateForm textarea[name="purpose"]')?.value || '';

            if (!certTypeId || !baseSalary || !purpose) {
                e.preventDefault();
                alert('<?php echo $t["please_fill"]; ?>');
                return false;
            }
        } else if (requestType === 'id_card') {
            const idCardReason = document.querySelector('#idCardForm input[name="id_card_reason"]:checked')?.value || '';

            if (!idCardReason) {
                e.preventDefault();
                alert('<?php echo $t["please_fill"]; ?>');
                return false;
            }
        }
    });
</script>
</body>

</html>