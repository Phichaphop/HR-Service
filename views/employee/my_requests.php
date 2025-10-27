<?php
/**
 * My Requests Page - ENHANCED VERSION with Detailed Information - FIXED
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * FIXES:
 * 1. Added text-gray-900 dark:text-white to all detail values for proper dark mode text color
 * 2. Fixed employee_name display issue
 */
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../db/Localization.php';
AuthController::requireAuth();
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
$label_class = $is_dark ? 'text-gray-300' : 'text-gray-700';
// Multi-language translations - ENHANCED with more fields
$translations = [
    'th' => [
        'page_title' => 'คำขอของฉัน',
        'page_subtitle' => 'จัดการคำขอของคุณ',
        'my_request' => 'คำขอของฉัน',
        'manage_request' => 'จัดการคำขอของคุณ',
        'request_id' => '#',
        'type' => 'ประเภท',
        'submitted_date' => 'วันที่ส่ง',
        'status' => 'สถานะ',
        'rating' => 'คะแนน',
        'actions' => 'การจัดการ',
        'no_requests' => 'ยังไม่มีคำขอ',
        'view_details' => 'ดูรายละเอียด',
        'cancel' => 'ยกเลิก',
        'request_details' => 'รายละเอียดคำขอ',
        'request_id_label' => 'Request ID',
        'status_label' => 'สถานะ',
        'created_date' => 'วันที่สร้าง',
        'updated_date' => 'วันที่อัปเดต',
        'employee_info' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'employee_name' => 'ชื่อพนักงาน',
        'position' => 'ตำแหน่ง',
        'department' => 'แผนก',
        'division' => 'สายการจัดการ',
        'section' => 'ส่วน',
        'handler_info' => 'ข้อมูลผู้ดำเนินการ',
        'handler_id' => 'รหัสผู้ดำเนินการ',
        'handler_name' => 'ชื่อผู้ดำเนินการ',
        'handler_remarks' => 'หมายเหตุจากเจ้าหน้าที่',
        'purpose' => 'วัตถุประสงค์',
        'reason' => 'เหตุผล',
        'remarks' => 'หมายเหตุ',
        'suggestion' => 'ข้อเสนอแนะ',
        // Leave Request
        'leave_type' => 'ประเภทการลา',
        'start_date' => 'วันเริ่มต้น',
        'end_date' => 'วันสิ้นสุด',
        'total_days' => 'จำนวนวันที่ลา',
        'leave_reason' => 'เหตุผลการลา',
        // Certificate Request
        'certificate_type' => 'ประเภทหนังสือรับรอง',
        'certificate_no' => 'เลขที่หนังสือรับรอง',
        'salary_info' => 'ข้อมูลเงินเดือน',
        'base_salary' => 'เงินเดือนพื้นฐาน',
        'hiring_type' => 'ประเภทการจ้าง',
        'date_of_hire' => 'วันที่เริ่มงาน',
        // ID Card Request
        'card_reason' => 'เหตุผลการขอบัตร',
        'card_status' => 'สถานะบัตร',
        // Shuttle Bus Request
        'route' => 'เส้นทาง',
        'pickup_location' => 'สถานที่รับ',
        'start_date_bus' => 'วันเริ่มใช้บริการ',
        // Locker Request
        'locker_number' => 'หมายเลขล็อกเกอร์',
        'assigned_locker' => 'ล็อกเกอร์ที่ได้รับมอบหมาย',
        // Supplies Request
        'request_type' => 'ประเภทคำขอ',
        'items_list' => 'รายการอุปกรณ์',
        'quantity' => 'จำนวน',
        'supplies_reason' => 'เหตุผลการขอเบิก',
        // Skill Test
        'test_info' => 'ข้อมูลการสอบทักษะ',
        // Document Submission
        'service_category' => 'หมวดหมู่บริการ',
        'service_type' => 'ประเภทบริการ',
        'submission_date' => 'วันที่ส่ง',
        // Rating
        'rating_title' => 'ให้คะแนนความพึงพอใจ',
        'rating_label' => 'คะแนน (1-5 ดาว)',
        'additional_feedback' => 'ความคิดเห็นเพิ่มเติม',
        'feedback_placeholder' => 'แสดงความคิดเห็น (ถ้ามี)',
        'submit_rating' => 'ส่งคะแนน',
        'close' => 'ปิด',
        'confirm_cancel' => 'ต้องการยกเลิกคำขอนี้หรือไม่?',
        'cancel_success' => 'ยกเลิกคำขอเรียบร้อยแล้ว',
        'rating_success' => 'ขอบคุณสำหรับการให้คะแนน!',
        'rate_request' => 'ให้คะแนน',
        'error_loading' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล',
        'error_occurred' => 'เกิดข้อผิดพลาด',
        'leave_request' => 'ใบลา',
        'certificate_request' => 'หนังสือรับรอง',
        'id_card_request' => 'บัตรพนักงาน',
        'shuttle_bus_request' => 'รถรับส่ง',
        'locker_request' => 'ตู้ล็อกเกอร์',
        'supplies_request' => 'วัสดุสำนักงาน',
        'skill_test_request' => 'ทดสอบทักษะ',
        'document_submission' => 'ลงชื่อส่งเอกสาร',
        'no_data' => 'ไม่มีข้อมูล',
        'not_assigned' => 'ยังไม่ได้มอบหมาย',
    ],
    'en' => [
        'page_title' => 'My Requests',
        'page_subtitle' => 'Manage Your Requests',
        'my_request' => 'My Request',
        'manage_request' => 'Manage Your Requests',
        'request_id' => '#',
        'type' => 'Type',
        'submitted_date' => 'Submitted Date',
        'status' => 'Status',
        'rating' => 'Rating',
        'actions' => 'Actions',
        'no_requests' => 'No Requests Found',
        'view_details' => 'View Details',
        'cancel' => 'Cancel',
        'request_details' => 'Request Details',
        'request_id_label' => 'Request ID',
        'status_label' => 'Status',
        'created_date' => 'Created Date',
        'updated_date' => 'Updated Date',
        'employee_info' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'employee_name' => 'Employee Name',
        'position' => 'Position',
        'department' => 'Department',
        'division' => 'Division',
        'section' => 'Section',
        'handler_info' => 'Handler Information',
        'handler_id' => 'Handler ID',
        'handler_name' => 'Handler Name',
        'handler_remarks' => 'Handler Remarks',
        'purpose' => 'Purpose',
        'reason' => 'Reason',
        'remarks' => 'Remarks',
        'suggestion' => 'Suggestion',
        'leave_type' => 'Leave Type',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'total_days' => 'Total Days',
        'leave_reason' => 'Leave Reason',
        'certificate_type' => 'Certificate Type',
        'certificate_no' => 'Certificate Number',
        'salary_info' => 'Salary Information',
        'base_salary' => 'Base Salary',
        'hiring_type' => 'Hiring Type',
        'date_of_hire' => 'Date of Hire',
        'card_reason' => 'Card Request Reason',
        'card_status' => 'Card Status',
        'route' => 'Route',
        'pickup_location' => 'Pickup Location',
        'start_date_bus' => 'Service Start Date',
        'locker_number' => 'Locker Number',
        'assigned_locker' => 'Assigned Locker',
        'request_type' => 'Request Type',
        'items_list' => 'Items List',
        'quantity' => 'Quantity',
        'supplies_reason' => 'Supply Reason',
        'test_info' => 'Skill Test Information',
        'service_category' => 'Service Category',
        'service_type' => 'Service Type',
        'submission_date' => 'Submission Date',
        'rating_title' => 'Rate Satisfaction',
        'rating_label' => 'Rating (1-5 Stars)',
        'additional_feedback' => 'Additional Feedback',
        'feedback_placeholder' => 'Add your feedback (if any)',
        'submit_rating' => 'Submit Rating',
        'close' => 'Close',
        'confirm_cancel' => 'Do you want to cancel this request?',
        'cancel_success' => 'Request cancelled successfully',
        'rating_success' => 'Thank you for your rating!',
        'rate_request' => 'Rate',
        'error_loading' => 'Error loading data',
        'error_occurred' => 'An error occurred',
        'leave_request' => 'Leave Request',
        'certificate_request' => 'Certificate Request',
        'id_card_request' => 'ID Card Request',
        'shuttle_bus_request' => 'Shuttle Bus Request',
        'locker_request' => 'Locker Request',
        'supplies_request' => 'Supplies Request',
        'skill_test_request' => 'Skill Test Request',
        'document_submission' => 'Document Submission',
        'no_data' => 'No Data',
        'not_assigned' => 'Not Assigned Yet',
    ],
    'my' => [
        'page_title' => 'ကျွန်ုပ်၏တောင်းခံမှုများ',
        'page_subtitle' => 'သင်၏တောင်းခံမှုများကိုစီမံခန့်ခွဲမည်',
        'my_request' => 'ကျွန်ုပ်၏တောင်းခံ',
        'manage_request' => 'သင်၏တောင်းခံမှုများကိုစီမံခန့်ခွဲမည်',
        'request_id' => '#',
        'type' => 'အမျိုးအစား',
        'submitted_date' => 'တင်သွင်းသည့်နေ့',
        'status' => 'အနေအထား',
        'rating' => 'အဆင့်သတ်မှတ်ခြင်း',
        'actions' => 'အရቀွမ်များ',
        'no_requests' => 'တောင်းခံမှုများမတွေ့ရှိ',
        'view_details' => 'အသေးစိတ်ကြည့်ရှုမည်',
        'cancel' => 'ပယ်ဖျက်မည်',
        'request_details' => 'တောင်းခံမှုအသေးစိတ်',
        'request_id_label' => 'Request ID',
        'status_label' => 'အနေအထား',
        'created_date' => 'ဖန်တီးသည့်နေ့',
        'updated_date' => 'အဆင့်သတ်မှတ်သည့်နေ့',
        'employee_info' => 'အလုပ်သမားအချက်အလက်',
        'employee_id' => 'အလုပ်သမား ID',
        'employee_name' => 'အလုပ်သမားအမည်',
        'position' => 'အနေအထား',
        'department' => 'ဌာန',
        'division' => 'မြဲ',
        'section' => 'အပိုင်းခွဲ',
        'handler_info' => 'ကိုင်တွယ်သူအချက်အလက်',
        'handler_id' => 'ကိုင်တွယ်သူ ID',
        'handler_name' => 'ကိုင်တွယ်သူအမည်',
        'handler_remarks' => 'ကိုင်တွယ်သူမှတ်ချက်များ',
        'purpose' => 'ရည်ရွယ်ချက်',
        'reason' => 'အကြောင်းအရာ',
        'remarks' => 'မှတ်ချက်များ',
        'suggestion' => 'အကြံအစည်',
        'leave_type' => 'အငြိုးပြုစုအမျိုးအစား',
        'start_date' => 'စတင်နေ့',
        'end_date' => 'အဆုံးသတ်နေ့',
        'total_days' => 'စုစုပေါင်းနေ့များ',
        'leave_reason' => 'အငြိုးပြုစုအကြောင်းအရာ',
        'certificate_type' => 'လက်မှတ်အမျိုးအစား',
        'certificate_no' => 'လက်မှတ်နံပါတ်',
        'salary_info' => 'လစာအချက်အလက်',
        'base_salary' => 'အခြေခံလစာ',
        'hiring_type' => 'လုပ်ခန်းအမျိုးအစား',
        'date_of_hire' => 'လုပ်ခန်းနေ့',
        'card_reason' => 'ကဒ်တောင်းခံအကြောင်းအရာ',
        'card_status' => 'ကဒ်အနေအထား',
        'route' => 'လမ်းကြောင်း',
        'pickup_location' => 'ရယူမည့်နေရာ',
        'start_date_bus' => 'ဝန်ဆောင်မှုစတင်နေ့',
        'locker_number' => 'အိတ်နံပါတ်',
        'assigned_locker' => 'သတ်မှတ်သည့်အိတ်',
        'request_type' => 'တောင်းခံအမျိုးအစား',
        'items_list' => 'ပစ္စည်းစာရင်း',
        'quantity' => 'အရေအတွက်',
        'supplies_reason' => 'ပေးအမ်အကြောင်းအရာ',
        'test_info' => 'အရည်အချင်းစမ်းသပ်မှုအချက်အလက်',
        'service_category' => 'ဝန်ဆောင်မှုအမျိုးအစား',
        'service_type' => 'ဝန်ဆောင်မှုအမျိုးအစား',
        'submission_date' => 'တင်သွင်းသည့်နေ့',
        'rating_title' => 'संतुष्टिতां အဆင့်သတ်မှတ်ခြင်း',
        'rating_label' => 'အဆင့် (၁-၅ ကြယ်)',
        'additional_feedback' => 'အခြားအဆင့်သတ်မှတ်ခြင်း',
        'feedback_placeholder' => 'သင်၏အကြံအစည်ကိုထည့်သွင်းပါ (ရှိရင်)',
        'submit_rating' => 'အဆင့်သတ်မှတ်မှုတင်သွင်းမည်',
        'close' => 'ပိတ်မည်',
        'confirm_cancel' => 'ဤတောင်းခံမှုကိုပယ်ဖျက်လိုပါသလား?',
        'cancel_success' => 'တောင်းခံမှုပယ်ဖျက်ခြင်းအောင်မြင်',
        'rating_success' => 'သင်၏အဆင့်သတ်မှတ်မှုအတွက်ကျေးဇူးတင်ပါသည်!',
        'rate_request' => 'အဆင့်သတ်မှတ်မည်',
        'error_loading' => 'အချက်အလက်တင်သွင်းခြင်းအတွင်းအမှားအယွင်း',
        'error_occurred' => 'အမှားအယွင်းပေါ်ပေါက်ခြင်း',
        'leave_request' => 'အငြိုးပြုစုတောင်းခံမှု',
        'certificate_request' => 'လက်မှတ်တောင်းခံမှု',
        'id_card_request' => 'အိုင်ဒီကဒ်တောင်းခံမှု',
        'shuttle_bus_request' => 'ကားရီးယားတောင်းခံမှု',
        'locker_request' => 'အိတ်ဆောင်တင်သွင်းမှုတောင်းခံမှု',
        'supplies_request' => 'ပရိယာယ်တောင်းခံမှု',
        'skill_test_request' => 'အရည်အချင်းစမ်းသပ်မှုတောင်းခံမှု',
        'document_submission' => 'စာ类တင်သွင်းမှု',
        'no_data' => 'အချက်အလက်မရှိ',
        'not_assigned' => 'ဒီတစ်ခါမှ မည့်အပ်မထားရသေးပါ',
    ]
];
// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];
$page_title = $t['page_title'];
ensure_session_started();
$conn = getDbConnection();
// Request type mapping with multi-language support
$request_types = [
    'leave_requests' => ['th' => 'ใบลา', 'en' => 'Leave Request', 'my' => 'အငြိုးပြုစုတောင်းခံမှု'],
    'certificate_requests' => ['th' => 'หนังสือรับรอง', 'en' => 'Certificate Request', 'my' => 'လက်မှတ်တောင်းခံမှု'],
    'id_card_requests' => ['th' => 'บัตรพนักงาน', 'en' => 'ID Card Request', 'my' => 'အိုင်ဒီကဒ်တောင်းခံမှု'],
    'shuttle_bus_requests' => ['th' => 'รถรับส่ง', 'en' => 'Shuttle Bus Request', 'my' => 'ကားရီးယားတောင်းခံမှု'],
    'locker_requests' => ['th' => 'ตู้ล็อกเกอร์', 'en' => 'Locker Request', 'my' => 'အိတ်ဆောင်တင်သွင်းမှုတောင်းခံမှု'],
    'supplies_requests' => ['th' => 'วัสดุสำนักงาน', 'en' => 'Supplies Request', 'my' => 'ပရိယာယ်တောင်းခံမှု'],
    'skill_test_requests' => ['th' => 'ทดสอบทักษะ', 'en' => 'Skill Test Request', 'my' => 'အရည်အချင်းစမ်းသပ်မှုတောင်းခံမှု'],
    'document_submissions' => ['th' => 'ลงชื่อส่งเอกสาร', 'en' => 'Document Submission', 'my' => 'စာ类တင်သွင်းမှု']
];
// Status mapping with multi-language support
$status_map = [
    'th' => ['New' => 'ใหม่', 'In Progress' => 'กำลังดำเนิน', 'Complete' => 'เสร็จสิ้น', 'Cancelled' => 'ยกเลิก'],
    'en' => ['New' => 'New', 'In Progress' => 'In Progress', 'Complete' => 'Complete', 'Cancelled' => 'Cancelled'],
    'my' => ['New' => 'အသစ်', 'In Progress' => 'လုပ်ဆောင်နေ', 'Complete' => 'ပြည့်စုံမည်', 'Cancelled' => 'ပယ်ဖျက်ခြင်း']
];
// Get all requests for this user
$all_requests = [];
foreach ($request_types as $table => $type_names) {
    $id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';
    
    $sql = "SELECT 
        $id_column as request_id,
        status,
        created_at,
        satisfaction_score,
        handler_remarks,
        ? as request_type_key,
        ? as source_table
    FROM $table
    WHERE employee_id = ?
    ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $table, $table, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $all_requests[] = $row;
    }
    $stmt->close();
}
// Sort by date
usort($all_requests, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
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
            overflow-y: auto;
        }
        .modal-backdrop.active {
            display: flex;
        }
        .detail-section {
            background: rgba(0,0,0,0.1);
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .detail-section.dark {
            background: rgba(255,255,255,0.05);
        }
        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        .detail-item {
            padding: 0.5rem 0;
        }
        .detail-label {
            font-size: 0.875rem;
            font-weight: 600;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }
        .detail-value {
            font-size: 1rem;
            font-weight: 500;
            color: inherit;
        }
        @media (max-width: 768px) {
            .detail-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="<?php echo $bg_class; ?> <?php echo $text_class; ?> theme-transition">
    <div class="lg:ml-64 min-h-screen">
        <div class="container mx-auto px-4 py-6">
            
            <!-- Page Header -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <h1 class="text-3xl font-bold text-white"><?php echo $t['my_request']; ?></h1>
                            <p class="text-blue-100 mt-1"><?php echo $t['manage_request']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Requests Table -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow-sm border <?php echo $border_class; ?> overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?>">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['request_id']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['type']; ?></th>
                                <th class="px-6 py-4 text-left text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['submitted_date']; ?></th>
                                <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['status']; ?></th>
                                <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['rating']; ?></th>
                                <th class="px-6 py-4 text-center text-xs font-semibold <?php echo $text_class; ?> uppercase"><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                            <?php if (empty($all_requests)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?>">
                                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-lg font-medium"><?php echo $t['no_requests']; ?></p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_requests as $req): 
                                    $type_config = $request_types[$req['request_type_key']] ?? [];
                                    $req_type_name = $type_config[$current_lang] ?? $req['request_type_key'];
                                    $status_label = $status_map[$current_lang][$req['status']] ?? $req['status'];
                                ?>
                                    <tr class="hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition">
                                        <td class="px-6 py-4">
                                            <span class="font-mono text-sm <?php echo $text_class; ?>">#<?php echo str_pad($req['request_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                <?php echo htmlspecialchars($req_type_name); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="<?php echo $text_class; ?> text-sm">
                                                <?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php
                                            $status_colors = [
                                                'New' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                                'In Progress' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                                                'Complete' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                                'Cancelled' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                                            ];
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$req['status']] ?? ''; ?>">
                                                <?php echo htmlspecialchars($status_label); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php if ($req['status'] === 'Complete' && !empty($req['satisfaction_score'])): ?>
                                                <span class="text-yellow-500 font-medium">
                                                    <?php echo str_repeat('★', $req['satisfaction_score']) . str_repeat('☆', 5 - $req['satisfaction_score']); ?>
                                                </span>
                                            <?php elseif ($req['status'] === 'Complete'): ?>
                                                <button onclick="rateRequest(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')" 
                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm underline">
                                                    <?php echo $t['rate_request']; ?>
                                                </button>
                                            <?php else: ?>
                                                <span class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2 flex-wrap gap-1">
                                                <button onclick="viewDetails(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')" 
                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                                                    <?php echo $t['view_details']; ?>
                                                </button>
                                                <?php if ($req['status'] === 'New'): ?>
                                                    <span class="text-gray-300">|</span>
                                                    <button onclick="cancelRequest(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')" 
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium text-sm">
                                                        <?php echo $t['cancel']; ?>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- View Details Modal - ENHANCED with more detailed information -->
    <div id="detailsModal" class="modal-backdrop">
        <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-y-auto border <?php echo $border_class; ?> m-4 my-auto">
            <div class="p-6 lg:p-8">
                <div class="flex items-center justify-between mb-6 sticky top-0 bg-inherit z-10 pb-4 border-b <?php echo $border_class; ?>">
                    <h3 class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo $t['request_details']; ?></h3>
                    <button onclick="closeModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="detailsContent" class="space-y-6">
                    <!-- Content loaded via JavaScript -->
                    <div class="text-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-4"><?php echo $t['error_loading']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Rating Modal -->
    <div id="ratingModal" class="modal-backdrop">
        <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-md w-full border <?php echo $border_class; ?> m-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold <?php echo $text_class; ?>"><?php echo $t['rating_title']; ?></h3>
                    <button onclick="closeRatingModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?>">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="ratingForm" onsubmit="submitRating(event)">
                    <input type="hidden" id="rating_request_id">
                    <input type="hidden" id="rating_table">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-3 text-center"><?php echo $t['rating_label']; ?></label>
                        <div class="flex justify-center space-x-2">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="score" value="<?php echo $i; ?>" required class="sr-only peer">
                                    <svg class="w-12 h-12 text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-300 transition" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-2"><?php echo $t['additional_feedback']; ?></label>
                        <textarea name="feedback" rows="3" 
                            class="w-full px-4 py-2 border rounded-lg <?php echo $input_class; ?> focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php echo $t['feedback_placeholder']; ?>"></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-medium">
                            <?php echo $t['submit_rating']; ?>
                        </button>
                        <button type="button" onclick="closeRatingModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-medium">
                            <?php echo $t['close']; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const currentLang = '<?php echo $current_lang; ?>';
        const t = <?php echo json_encode($t); ?>;
        const statusMap = <?php echo json_encode($status_map); ?>;
        const isDark = <?php echo json_encode($is_dark); ?>;
        
        function viewDetails(id, table) {
            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('detailsContent');
            
            content.innerHTML = '<div class="text-center py-12"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div></div>';
            modal.classList.add('active');
            
            fetch(`<?php echo BASE_PATH; ?>/api/get_request_details.php?id=${id}&table=${table}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        content.innerHTML = generateDetailedHTML(data.request, table);
                    } else {
                        content.innerHTML = `<div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4"><p class="text-red-800 dark:text-red-200">${data.message || t['error_loading']}</p></div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4"><p class="text-red-800 dark:text-red-200">' + t['error_loading'] + '</p></div>';
                });
        }
        
        function generateDetailedHTML(req, table) {
            let html = ``;
            const detailClass = isDark ? 'detail-section dark' : 'detail-section';
            const valueColorClass = isDark ? 'text-gray-900 dark:text-white' : 'text-gray-900';
            
            // 1. REQUEST HEADER INFO
            html += `
                <div class="${detailClass}">
                    <h4 class="font-bold text-lg mb-4">📋 ${t['request_details']}</h4>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">${t['request_id_label']}</div>
                            <div class="detail-value font-mono text-gray-900 dark:text-white">#${String(req.request_id).padStart(5, '0')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['status_label']}</div>
                            <div class="detail-value text-gray-900 dark:text-white">${statusMap[currentLang][req.status] || req.status}</div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">${t['created_date']}</div>
                            <div class="detail-value text-gray-900 dark:text-white">${new Date(req.created_at).toLocaleString(currentLang === 'th' ? 'th-TH' : currentLang === 'en' ? 'en-US' : 'my-MM')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['updated_date']}</div>
                            <div class="detail-value text-gray-900 dark:text-white">${new Date(req.updated_at).toLocaleString(currentLang === 'th' ? 'th-TH' : currentLang === 'en' ? 'en-US' : 'my-MM')}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // 2. EMPLOYEE INFORMATION - FIXED with dark mode text color
            html += `
                <div class="${detailClass}">
                    <h4 class="font-bold text-lg mb-4">👤 ${t['employee_info']}</h4>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">${t['employee_id']}</div>
                            <div class="detail-value font-mono text-gray-900 dark:text-white">${req.employee_id || t['no_data']}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['employee_name']}</div>
                            <div class="detail-value text-gray-900 dark:text-white">${req.employee_name || t['no_data']}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // 3. REQUEST TYPE SPECIFIC DETAILS
            if (table === 'leave_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">🏖️ ${t['leave_request']}</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['leave_type']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.leave_type || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['total_days']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.total_days || t['no_data']} ${t['total_days']}</div>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['start_date']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.start_date ? new Date(req.start_date).toLocaleDateString() : t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['end_date']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.end_date ? new Date(req.end_date).toLocaleDateString() : t['no_data']}</div>
                            </div>
                        </div>
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['leave_reason']}</div><div class="detail-value break-words text-gray-900 dark:text-white">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'certificate_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">📄 ${t['certificate_request']}</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['certificate_no']}</div>
                                <div class="detail-value font-mono text-gray-900 dark:text-white">${req.certificate_no || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['hiring_type']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.hiring_type || t['no_data']}</div>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['date_of_hire']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.date_of_hire ? new Date(req.date_of_hire).toLocaleDateString() : t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['base_salary']}</div>
                                <div class="detail-value font-mono text-gray-900 dark:text-white">${req.base_salary ? parseFloat(req.base_salary).toLocaleString() : t['no_data']}</div>
                            </div>
                        </div>
                        ${req.purpose ? `<div class="detail-item mt-4"><div class="detail-label">${t['purpose']}</div><div class="detail-value break-words text-gray-900 dark:text-white">${req.purpose}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'shuttle_bus_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">🚌 ${t['shuttle_bus_request']}</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['route']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.route || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['pickup_location']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.pickup_location || t['no_data']}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['start_date_bus']}</div>
                            <div class="detail-value text-gray-900 dark:text-white">${req.start_date ? new Date(req.start_date).toLocaleDateString() : t['no_data']}</div>
                        </div>
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words text-gray-900 dark:text-white">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'locker_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">🔐 ${t['locker_request']}</h4>
                        ${req.assigned_locker_id ? `
                            <div class="detail-item">
                                <div class="detail-label">${t['assigned_locker']}</div>
                                <div class="detail-value font-mono text-gray-900 dark:text-white">Locker #${req.assigned_locker_id}</div>
                            </div>
                        ` : `
                            <div class="detail-item">
                                <div class="detail-label">${t['assigned_locker']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${t['not_assigned']}</div>
                            </div>
                        `}
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words text-gray-900 dark:text-white">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'supplies_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">📦 ${t['supplies_request']}</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['request_type']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.request_type || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['quantity']}</div>
                                <div class="detail-value text-gray-900 dark:text-white">${req.quantity || t['no_data']}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['items_list']}</div>
                            <div class="detail-value break-words whitespace-pre-wrap text-gray-900 dark:text-white">${req.items_list || t['no_data']}</div>
                        </div>
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words text-gray-900 dark:text-white">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'id_card_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">🎫 ${t['id_card_request']}</h4>
                        ${req.reason ? `<div class="detail-item"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words text-gray-900 dark:text-white">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'skill_test_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">🧪 ${t['skill_test_request']}</h4>
                        ${req.reason ? `<div class="detail-item"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words text-gray-900 dark:text-white">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'document_submissions') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">📃 ${t['document_submission']}</h4>
                        <div class="detail-item">
                            <div class="detail-label">${t['submission_date']}</div>
                            <div class="detail-value text-gray-900 dark:text-white">${req.submission_date ? new Date(req.submission_date).toLocaleString() : t['no_data']}</div>
                        </div>
                    </div>
                `;
            }
            
            // 4. HANDLER INFORMATION (if assigned)
            if (req.handler_id) {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">👨‍💼 ${t['handler_info']}</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['handler_id']}</div>
                                <div class="detail-value font-mono text-gray-900 dark:text-white">${req.handler_id}</div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // 5. REMARKS/FEEDBACK
            if (req.handler_remarks) {
                html += `
                    <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 p-4 rounded">
                        <h4 class="font-bold mb-2 flex items-center text-gray-900 dark:text-white">
                            <span class="text-blue-500 mr-2">💬</span>
                            ${t['handler_remarks']}
                        </h4>
                        <p class="break-words whitespace-pre-wrap text-gray-900 dark:text-white">${req.handler_remarks}</p>
                    </div>
                `;
            }
            
            // 6. SATISFACTION RATING (if rated)
            if (req.satisfaction_score) {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">⭐ ให้คะแนน</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">ระดับความพึงพอใจ</div>
                                <div class="detail-value text-gray-900 dark:text-white">${'★'.repeat(req.satisfaction_score)}${'☆'.repeat(5 - req.satisfaction_score)}</div>
                            </div>
                        </div>
                        ${req.satisfaction_feedback ? `
                            <div class="detail-item mt-4">
                                <div class="detail-label">ความเห็น</div>
                                <div class="detail-value break-words text-gray-900 dark:text-white">${req.satisfaction_feedback}</div>
                            </div>
                        ` : ''}
                    </div>
                `;
            }
            
            return html;
        }
        
        function closeModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }
        
        function cancelRequest(id, table) {
            if (!confirm(t['confirm_cancel'])) {
                return;
            }
            
            fetch('<?php echo BASE_PATH; ?>/api/cancel_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, table: table })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(t['cancel_success'], 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(t['error_occurred'], 'error');
            });
        }
        
        function rateRequest(id, table) {
            document.getElementById('rating_request_id').value = id;
            document.getElementById('rating_table').value = table;
            document.getElementById('ratingModal').classList.add('active');
        }
        
        function closeRatingModal() {
            document.getElementById('ratingModal').classList.remove('active');
            document.getElementById('ratingForm').reset();
        }
        
        function submitRating(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = {
                request_id: document.getElementById('rating_request_id').value,
                table: document.getElementById('rating_table').value,
                score: formData.get('score'),
                feedback: formData.get('feedback')
            };
            
            fetch('<?php echo BASE_PATH; ?>/api/submit_rating.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast(t['rating_success'], 'success');
                    closeRatingModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(t['error_occurred'], 'error');
            });
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeRatingModal();
            }
        });
        
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        document.getElementById('ratingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRatingModal();
            }
        });
        
        function showToast(message, type = 'info') {
            const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-blue-500');
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in-up`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.3s ease-in-out;
        }
    </style>
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>