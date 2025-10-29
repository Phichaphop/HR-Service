<?php
/**
 * My Requests Page - FIXED VERSION WITH STAR RATING
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
 * FEATURES:
 * 1. Fixed Hiring Type, Date of Hire, Base Salary showing "No Data"
 * 2. Added certificate type display in certificate request details
 * 3. Proper dark mode text colors on all fields
 * 4. STAR RATING: 5 stars left-to-right with interactive UI
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
        'leave_type' => 'ประเภทการลา',
        'start_date' => 'วันเริ่มต้น',
        'end_date' => 'วันสิ้นสุด',
        'total_days' => 'จำนวนวันที่ลา',
        'leave_reason' => 'เหตุผลการลา',
        'certificate_type' => 'ประเภทหนังสือรับรอง',
        'certificate_no' => 'เลขที่หนังสือรับรอง',
        'salary_info' => 'ข้อมูลเงินเดือน',
        'base_salary' => 'เงินเดือนพื้นฐาน',
        'hiring_type' => 'ประเภทการจ้าง',
        'date_of_hire' => 'วันที่เริ่มงาน',
        'card_reason' => 'เหตุผลการขอบัตร',
        'card_status' => 'สถานะบัตร',
        'route' => 'เส้นทาง',
        'pickup_location' => 'สถานที่รับ',
        'start_date_bus' => 'วันเริ่มใช้บริการ',
        'locker_number' => 'หมายเลขล็อกเกอร์',
        'assigned_locker' => 'ล็อกเกอร์ที่ได้รับมอบหมาย',
        'request_type' => 'ประเภทคำขอ',
        'items_list' => 'รายการอุปกรณ์',
        'quantity' => 'จำนวน',
        'supplies_reason' => 'เหตุผลการขอเบิก',
        'test_info' => 'ข้อมูลการสอบทักษะ',
        'service_category' => 'หมวดหมู่บริการ',
        'service_type' => 'ประเภทบริการ',
        'submission_date' => 'วันที่ส่ง',
        'rating_title' => 'ให้คะแนนความพึงพอใจ',
        'rating_label' => 'เลือกคะแนน (1-5 ดาว)',
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
        'satisfaction_score' => 'ระดับความพึงพอใจ',
        'satisfaction_feedback' => 'ความเห็นของผู้ใช้',
        'rating_1' => 'ไม่พอใจ',
        'rating_2' => 'พอใจ',
        'rating_3' => 'ปานกลาง',
        'rating_4' => 'ดี',
        'rating_5' => 'ดีเยี่ยม',
        'please_select_rating' => 'กรุณาเลือกคะแนน',
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
        'rating_label' => 'Select Rating (1-5 Stars)',
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
        'satisfaction_score' => 'Satisfaction Score',
        'satisfaction_feedback' => 'User Feedback',
        'rating_1' => 'Poor',
        'rating_2' => 'Fair',
        'rating_3' => 'Average',
        'rating_4' => 'Good',
        'rating_5' => 'Excellent',
        'please_select_rating' => 'Please select a rating',
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
        'rating_title' => 'ကျေးဇူးတင်မှုအဆင့်သတ်မှတ်ခြင်း',
        'rating_label' => 'အဆင့်ရွေးချယ်ခြင်း (၁-၅ ကြယ်)',
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
        'document_submission' => 'စာ၍တင်သွင်းမှု',
        'no_data' => 'အချက်အလက်မရှိ',
        'not_assigned' => 'ဒီတစ်ခါမှ မည့်အပ်မထားရသေးပါ',
        'satisfaction_score' => 'ကျေးဇူးတင်သောအဆင့်',
        'satisfaction_feedback' => 'အသုံးပြုသူအကြံအစည်',
        'rating_1' => 'ကျေးဇူးမတင်သည်',
        'rating_2' => 'ကျေးဇူးတင်သည်',
        'rating_3' => 'ပျမ်းမျန်သည်',
        'rating_4' => 'ကောင်းမွန်သည်',
        'rating_5' => 'အလွန်ကောင်းမွန်သည်',
        'please_select_rating' => 'အဆင့်ရွေးချယ်ပါ',
    ]
];
// Get current language strings
$t = $translations[$current_lang] ?? $translations['th'];
$page_title = $t['page_title'];
// Ensure session started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    'document_submissions' => ['th' => 'ลงชื่อส่งเอกสาร', 'en' => 'Document Submission', 'my' => 'စာ၍တင်သွင်းမှု']
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
        }
        @media (max-width: 768px) {
            .detail-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* STAR RATING STYLES */
        .star-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 1.5rem 0;
        }
        
        .star-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            transition: transform 0.2s ease, filter 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .star-button:hover {
            transform: scale(1.15);
            filter: brightness(1.2);
        }
        
        .star-button svg {
            width: 3rem;
            height: 3rem;
            transition: all 0.2s ease;
        }
        
        .star-button.empty svg {
            color: #d1d5db;
            fill: #d1d5db;
        }
        
        .star-button.filled svg {
            color: #fcd34d;
            fill: #fcd34d;
        }
        
        .star-button.filled svg:hover {
            filter: brightness(1.1);
        }
        
        .dark .star-button.empty svg {
            color: #6b7280;
            fill: #6b7280;
        }
        
        .rating-label {
            text-align: center;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 1rem;
            min-height: 1.5rem;
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
    
    <!-- View Details Modal -->
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
                    <div class="text-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-4"><?php echo $t['error_loading']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rating Modal - WITH STAR RATING UI -->
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
                    <input type="hidden" id="rating_score" name="score">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium <?php echo $text_class; ?> mb-3"><?php echo $t['rating_label']; ?></label>
                        
                        <!-- STAR RATING (Left to Right: 1-5) -->
                        <div class="star-container" id="starContainer">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-button empty" data-rating="<?php echo $i; ?>" onclick="selectRating(<?php echo $i; ?>)">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </button>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Rating Label -->
                        <div class="rating-label" id="ratingLabel">
                            <?php echo $t['please_select_rating']; ?>
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
        
        // Rating labels for different scores
        const ratingLabels = {
            1: t['rating_1'],
            2: t['rating_2'],
            3: t['rating_3'],
            4: t['rating_4'],
            5: t['rating_5']
        };
        
        let currentRating = 0;
        
        // SELECT RATING (Click star)
        function selectRating(score) {
            currentRating = score;
            document.getElementById('rating_score').value = score;
            
            // Update UI - make stars yellow
            const buttons = document.querySelectorAll('#starContainer .star-button');
            buttons.forEach((btn, index) => {
                if (index < score) {
                    btn.classList.remove('empty');
                    btn.classList.add('filled');
                } else {
                    btn.classList.add('empty');
                    btn.classList.remove('filled');
                }
            });
            
            // Update label
            document.getElementById('ratingLabel').textContent = ratingLabels[score] || '';
        }
        
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
            const valueColorClass = 'text-gray-900 dark:text-white';
            
            // 1. REQUEST HEADER INFO
            html += `
                <div class="${detailClass}">
                    <h4 class="font-bold text-lg mb-4">📋 ${t['request_details']}</h4>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">${t['request_id_label']}</div>
                            <div class="detail-value font-mono ${valueColorClass}">#${String(req.request_id).padStart(5, '0')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['status_label']}</div>
                            <div class="detail-value ${valueColorClass}">${statusMap[currentLang][req.status] || req.status}</div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">${t['created_date']}</div>
                            <div class="detail-value ${valueColorClass}">${new Date(req.created_at).toLocaleString(currentLang === 'th' ? 'th-TH' : currentLang === 'en' ? 'en-US' : 'my-MM')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['updated_date']}</div>
                            <div class="detail-value ${valueColorClass}">${new Date(req.updated_at).toLocaleString(currentLang === 'th' ? 'th-TH' : currentLang === 'en' ? 'en-US' : 'my-MM')}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // 2. EMPLOYEE INFORMATION
            html += `
                <div class="${detailClass}">
                    <h4 class="font-bold text-lg mb-4">👤 ${t['employee_info']}</h4>
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">${t['employee_id']}</div>
                            <div class="detail-value font-mono ${valueColorClass}">${req.employee_id || t['no_data']}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['employee_name']}</div>
                            <div class="detail-value ${valueColorClass}">${req.employee_name || t['no_data']}</div>
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
                                <div class="detail-value ${valueColorClass}">${req.leave_type || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['total_days']}</div>
                                <div class="detail-value ${valueColorClass}">${req.total_days || t['no_data']} ${t['total_days']}</div>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['start_date']}</div>
                                <div class="detail-value ${valueColorClass}">${req.start_date ? new Date(req.start_date).toLocaleDateString() : t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['end_date']}</div>
                                <div class="detail-value ${valueColorClass}">${req.end_date ? new Date(req.end_date).toLocaleDateString() : t['no_data']}</div>
                            </div>
                        </div>
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['leave_reason']}</div><div class="detail-value break-words ${valueColorClass}">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'certificate_requests') {
                // Get certificate type name based on language
                let certTypeName = t['no_data'];
                if (req.cert_type_id) {
                    if (currentLang === 'th' && req.type_name_th) {
                        certTypeName = req.type_name_th;
                    } else if (currentLang === 'en' && req.type_name_en) {
                        certTypeName = req.type_name_en;
                    } else if (currentLang === 'my' && req.type_name_my) {
                        certTypeName = req.type_name_my;
                    } else {
                        certTypeName = req.type_name_th || req.type_name_en || t['no_data'];
                    }
                }
                
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">📄 ${t['certificate_request']}</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['certificate_type']}</div>
                                <div class="detail-value ${valueColorClass}">${certTypeName}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['certificate_no']}</div>
                                <div class="detail-value font-mono ${valueColorClass}">${req.certificate_no || t['no_data']}</div>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['hiring_type']}</div>
                                <div class="detail-value ${valueColorClass}">${req.hiring_type || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['date_of_hire']}</div>
                                <div class="detail-value ${valueColorClass}">${req.date_of_hire ? new Date(req.date_of_hire).toLocaleDateString() : t['no_data']}</div>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">${t['base_salary']}</div>
                                <div class="detail-value font-mono ${valueColorClass}">${req.base_salary ? parseFloat(req.base_salary).toLocaleString() : t['no_data']}</div>
                            </div>
                        </div>
                        ${req.purpose ? `<div class="detail-item mt-4"><div class="detail-label">${t['purpose']}</div><div class="detail-value break-words ${valueColorClass}">${req.purpose}</div></div>` : ''}
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
                                <div class="detail-value ${valueColorClass}">${req.route || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['pickup_location']}</div>
                                <div class="detail-value ${valueColorClass}">${req.pickup_location || t['no_data']}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['start_date_bus']}</div>
                            <div class="detail-value ${valueColorClass}">${req.start_date ? new Date(req.start_date).toLocaleDateString() : t['no_data']}</div>
                        </div>
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words ${valueColorClass}">${req.reason}</div></div>` : ''}
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
                                <div class="detail-value font-mono ${valueColorClass}">Locker #${req.assigned_locker_id}</div>
                            </div>
                        ` : `
                            <div class="detail-item">
                                <div class="detail-label">${t['assigned_locker']}</div>
                                <div class="detail-value ${valueColorClass}">${t['not_assigned']}</div>
                            </div>
                        `}
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words ${valueColorClass}">${req.reason}</div></div>` : ''}
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
                                <div class="detail-value ${valueColorClass}">${req.request_type || t['no_data']}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">${t['quantity']}</div>
                                <div class="detail-value ${valueColorClass}">${req.quantity || t['no_data']}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">${t['items_list']}</div>
                            <div class="detail-value break-words whitespace-pre-wrap ${valueColorClass}">${req.items_list || t['no_data']}</div>
                        </div>
                        ${req.reason ? `<div class="detail-item mt-4"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words ${valueColorClass}">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'id_card_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">🎫 ${t['id_card_request']}</h4>
                        ${req.reason ? `<div class="detail-item"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words ${valueColorClass}">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'skill_test_requests') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">🧪 ${t['skill_test_request']}</h4>
                        ${req.reason ? `<div class="detail-item"><div class="detail-label">${t['reason']}</div><div class="detail-value break-words ${valueColorClass}">${req.reason}</div></div>` : ''}
                    </div>
                `;
            }
            
            if (table === 'document_submissions') {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">📃 ${t['document_submission']}</h4>
                        <div class="detail-item">
                            <div class="detail-label">${t['submission_date']}</div>
                            <div class="detail-value ${valueColorClass}">${req.submission_date ? new Date(req.submission_date).toLocaleString() : t['no_data']}</div>
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
                                <div class="detail-value font-mono ${valueColorClass}">${req.handler_id}</div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // 5. REMARKS/FEEDBACK
            if (req.handler_remarks) {
                html += `
                    <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 p-4 rounded">
                        <h4 class="font-bold mb-2 flex items-center ${valueColorClass}">
                            <span class="text-blue-500 mr-2">💬</span>
                            ${t['handler_remarks']}
                        </h4>
                        <p class="break-words whitespace-pre-wrap ${valueColorClass}">${req.handler_remarks}</p>
                    </div>
                `;
            }
            
            // 6. SATISFACTION RATING (if rated)
            if (req.satisfaction_score) {
                html += `
                    <div class="${detailClass}">
                        <h4 class="font-bold text-lg mb-4">⭐ ${t['satisfaction_score']}</h4>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">ระดับความพึงพอใจ</div>
                                <div class="detail-value ${valueColorClass}">${'★'.repeat(req.satisfaction_score)}${'☆'.repeat(5 - req.satisfaction_score)}</div>
                            </div>
                        </div>
                        ${req.satisfaction_feedback ? `
                            <div class="detail-item mt-4">
                                <div class="detail-label">${t['satisfaction_feedback']}</div>
                                <div class="detail-value break-words ${valueColorClass}">${req.satisfaction_feedback}</div>
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
            currentRating = 0;
            document.getElementById('rating_request_id').value = id;
            document.getElementById('rating_table').value = table;
            document.getElementById('ratingForm').reset();
            
            // Reset stars
            document.querySelectorAll('#starContainer .star-button').forEach(btn => {
                btn.classList.add('empty');
                btn.classList.remove('filled');
            });
            document.getElementById('ratingLabel').textContent = t['please_select_rating'];
            
            document.getElementById('ratingModal').classList.add('active');
        }
        
        function closeRatingModal() {
            document.getElementById('ratingModal').classList.remove('active');
        }
        
        function submitRating(event) {
            event.preventDefault();
            
            if (currentRating === 0) {
                alert(t['please_select_rating']);
                return;
            }
            
            const formData = new FormData(event.target);
            const data = {
                request_id: document.getElementById('rating_request_id').value,
                table: document.getElementById('rating_table').value,
                score: currentRating,
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