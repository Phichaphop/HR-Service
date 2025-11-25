<?php
/**
 * My Requests Page - STANDARDIZED UI VERSION
 * ✅ Matches certificate, idcard, leave request forms styling
 * ✅ Updated to match request_certificate.php design
 * ✅ Icon buttons for actions (view details and delete)
 * ✅ Consistent max-width container
 * ✅ Full dark mode support
 * Supports: Thai (ไทย), English (EN), Myanmar (မြန်မာ)
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
// Theme colors
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-white' : 'text-gray-900';
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
$input_class = $is_dark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900';
$label_class = $is_dark ? 'text-gray-300' : 'text-gray-700';
// Multi-language translations
$translations = [
    'th' => [
        'page_title' => 'คำขอของฉัน',
        'page_subtitle' => 'จัดการและติดตามคำขอทั้งหมด',
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
        'employee_info' => 'ข้อมูลพนักงาน',
        'employee_id' => 'รหัสพนักงาน',
        'employee_name' => 'ชื่อพนักงาน',
        'handler_info' => 'ข้อมูลผู้ดำเนินการ',
        'handler_id' => 'รหัสผู้ดำเนินการ',
        'handler_remarks' => 'หมายเหตุจากเจ้าหน้าที่',
        'leave_type' => 'ประเภทการลา',
        'start_date' => 'วันเริ่มต้น',
        'end_date' => 'วันสิ้นสุด',
        'total_days' => 'จำนวนวัน',
        'days' => 'วัน',
        'reason' => 'เหตุผล',
        'certificate_type' => 'ประเภทหนังสือรับรอง',
        'certificate_no' => 'เลขที่หนังสือรับรอง',
        'base_salary' => 'เงินเดือนพื้นฐาน',
        'purpose' => 'วัตถุประสงค์',
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
        'not_assigned' => 'ยังไม่ได้มอบหมาย',
        'rating_1' => '😞 ไม่พอใจ',
        'rating_2' => '😐 พอใจ',
        'rating_3' => '😊 ปานกลาง',
        'rating_4' => '😄 ดี',
        'rating_5' => '😍 ดีเยี่ยม',
        'please_select_rating' => 'กรุณาเลือกคะแนน',
    ],
    'en' => [
        'page_title' => 'My Requests',
        'page_subtitle' => 'Track and manage all your requests',
        'request_id' => '#',
        'type' => 'Type',
        'submitted_date' => 'Submitted',
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
        'employee_info' => 'Employee Information',
        'employee_id' => 'Employee ID',
        'employee_name' => 'Employee Name',
        'handler_info' => 'Handler Information',
        'handler_id' => 'Handler ID',
        'handler_remarks' => 'Handler Remarks',
        'leave_type' => 'Leave Type',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'total_days' => 'Total Days',
        'days' => 'Days',
        'reason' => 'Reason',
        'certificate_type' => 'Certificate Type',
        'certificate_no' => 'Certificate Number',
        'base_salary' => 'Base Salary',
        'purpose' => 'Purpose',
        'rating_title' => 'Rate Your Satisfaction',
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
        'not_assigned' => 'Not Assigned Yet',
        'rating_1' => '😞 Poor',
        'rating_2' => '😐 Fair',
        'rating_3' => '😊 Average',
        'rating_4' => '😄 Good',
        'rating_5' => '😍 Excellent',
        'please_select_rating' => 'Please select a rating',
    ],
    'my' => [
        'page_title' => 'ကျွန်ုပ်၏တောင်းခံမှုများ',
        'page_subtitle' => 'သင်၏တောင်းခံမှုများကိုခြင်းသည်',
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
        'employee_info' => 'အလုပ်သမားအချက်အလက်',
        'employee_id' => 'အလုပ်သမား ID',
        'employee_name' => 'အလုပ်သမားအမည်',
        'handler_info' => 'ကိုင်တွယ်သူအချက်အလက်',
        'handler_id' => 'ကိုင်တွယ်သူ ID',
        'handler_remarks' => 'ကိုင်တွယ်သူမှတ်ချက်များ',
        'leave_type' => 'အငြိုးပြုစုအမျိုးအစား',
        'start_date' => 'စတင်နေ့',
        'end_date' => 'အဆုံးသတ်နေ့',
        'total_days' => 'စုစုပေါင်းနေ့',
        'days' => 'နေ့',
        'reason' => 'အကြောင်းအရာ',
        'certificate_type' => 'လက်မှတ်အမျိုးအစား',
        'certificate_no' => 'လက်မှတ်နံပါတ်',
        'base_salary' => 'အခြေခံလစာ',
        'purpose' => 'ရည်ရွယ်ချက်',
        'rating_title' => 'ကျေးဇူးတင်မှုအဆင့်သတ်မှတ်ခြင်း',
        'rating_label' => 'အဆင့်ရွေးချယ်ခြင်း (၁-၅ ကြယ်)',
        'additional_feedback' => 'အခြားအကြံအစည်',
        'feedback_placeholder' => 'သင်၏အကြံအစည်ကိုထည့်သွင်းပါ',
        'submit_rating' => 'အဆင့်သတ်မှတ်မှုတင်သွင်းမည်',
        'close' => 'ပိတ်မည်',
        'confirm_cancel' => 'ဤတောင်းခံမှုကိုပယ်ဖျက်လိုပါသလား?',
        'cancel_success' => 'တောင်းခံမှုပယ်ဖျက်ခြင်းအောင်မြင်',
        'rating_success' => 'သင်၏အဆင့်သတ်မှတ်မှုအတွက်ကျေးဇူးတင်ပါသည်!',
        'rate_request' => 'အဆင့်သတ်မှတ်မည်',
        'error_loading' => 'အချက်အလက်တင်သွင်းခြင်းအတွင်းအမှားအယွင်း',
        'error_occurred' => 'အမှားအယွင်းပေါ်ပေါက်ခြင်း',
        'not_assigned' => 'ဒီတစ်ခါမှမည့်အပ်မထားရသေးပါ',
        'rating_1' => '😞 ကျေးဇူးမတင်သည်',
        'rating_2' => '😐 ကျေးဇူးတင်သည်',
        'rating_3' => '😊 ပျမ်းမျန်သည်',
        'rating_4' => '😄 ကောင်းမွန်သည်',
        'rating_5' => '😍 အလွန်ကောင်းမွန်သည်',
        'please_select_rating' => 'အဆင့်ရွေးချယ်ပါ',
    ]
];
$t = $translations[$current_lang] ?? $translations['th'];
// Ensure session started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = getDbConnection();
// Request type mapping
$request_types = [
    'leave_requests' => ['th' => 'ใบลา', 'en' => 'Leave', 'my' => 'အငြိုးပြုစု', 'icon' => '🏖️', 'color' => 'green'],
    'certificate_requests' => ['th' => 'หนังสือรับรอง', 'en' => 'Certificate', 'my' => 'လက်မှတ်', 'icon' => '📄', 'color' => 'blue'],
    'id_card_requests' => ['th' => 'บัตรพนักงาน', 'en' => 'ID Card', 'my' => 'အိုင်ဒီကဒ်', 'icon' => '🎫', 'color' => 'purple'],
    'shuttle_bus_requests' => ['th' => 'รถรับส่ง', 'en' => 'Shuttle Bus', 'my' => 'ကားရီးယား', 'icon' => '🚌', 'color' => 'orange'],
    'locker_requests' => ['th' => 'ตู้ล็อกเกอร์', 'en' => 'Locker', 'my' => 'အိတ်', 'icon' => '🔐', 'color' => 'indigo'],
    'supplies_requests' => ['th' => 'วัสดุสำนักงาน', 'en' => 'Supplies', 'my' => 'ပရိယာယ်', 'icon' => '📦', 'color' => 'orange'],
    'skill_test_requests' => ['th' => 'ทดสอบทักษะ', 'en' => 'Skill Test', 'my' => 'အရည်အချင်း', 'icon' => '🧪', 'color' => 'purple'],
    'document_submissions' => ['th' => 'ส่งเอกสาร', 'en' => 'Document', 'my' => 'စာ၍', 'icon' => '📃', 'color' => 'indigo'],
];
// Status colors
$status_colors = [
    'New' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border border-yellow-300 dark:border-yellow-700',
    'In Progress' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 border border-blue-300 dark:border-blue-700',
    'Complete' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-300 dark:border-green-700',
    'Cancelled' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border border-red-300 dark:border-red-700'
];
$status_map = [
    'th' => ['New' => 'ใหม่', 'In Progress' => 'กำลังดำเนิน', 'Complete' => 'เสร็จสิ้น', 'Cancelled' => 'ยกเลิก'],
    'en' => ['New' => 'New', 'In Progress' => 'In Progress', 'Complete' => 'Complete', 'Cancelled' => 'Cancelled'],
    'my' => ['New' => 'အသစ်', 'In Progress' => 'လုပ်ဆောင်နေ', 'Complete' => 'ပြည့်စုံ', 'Cancelled' => 'ပယ်ဖျက်']
];
// Get all requests
$all_requests = [];
foreach ($request_types as $table => $type_config) {
    $id_column = ($table === 'document_submissions') ? 'submission_id' : 'request_id';
    $sql = "SELECT 
        $id_column as request_id,
        status,
        created_at,
        satisfaction_score,
        ? as source_table,
        ? as type_lang
    FROM $table
    WHERE employee_id = ?
    ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $type_lang = $type_config[$current_lang] ?? $type_config['en'];
    $stmt->bind_param('sss', $table, $type_lang, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['type_config'] = $type_config;
        $all_requests[] = $row;
    }
    $stmt->close();
}
usort($all_requests, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$conn->close();
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="lg:ml-64 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        
        <!-- Page Header -->
        <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo $t['page_title']; ?></h1>
                    <p class="text-blue-100 mt-1"><?php echo $t['page_subtitle']; ?></p>
                </div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg border <?php echo $border_class; ?> overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?>">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wide"><?php echo $t['request_id']; ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wide"><?php echo $t['type']; ?></th>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wide"><?php echo $t['submitted_date']; ?></th>
                            <th class="px-6 py-4 text-center text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wide"><?php echo $t['status']; ?></th>
                            <th class="px-6 py-4 text-center text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wide"><?php echo $t['rating']; ?></th>
                            <th class="px-6 py-4 text-center text-xs font-bold <?php echo $text_class; ?> uppercase tracking-wide"><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y <?php echo $is_dark ? 'divide-gray-700' : 'divide-gray-200'; ?>">
                        <?php if (empty($all_requests)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <svg class="w-16 h-16 <?php echo $is_dark ? 'text-gray-600' : 'text-gray-300'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <div>
                                            <p class="text-lg font-bold <?php echo $text_class; ?>"><?php echo $t['no_requests']; ?></p>
                                            <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-1">ยังไม่มีคำขอให้แสดง</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_requests as $req):
                                $type_config = $req['type_config'];
                                $req_type_name = $req['type_lang'];
                                $status_label = $status_map[$current_lang][$req['status']] ?? $req['status'];
                            ?>
                                <tr class="hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">#<?php echo str_pad($req['request_id'], 5, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            <?php echo htmlspecialchars($req_type_name); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="<?php echo $text_class; ?> text-sm font-medium">
                                            <?php echo date('d/m/Y', strtotime($req['created_at'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo $status_colors[$req['status']] ?? ''; ?>">
                                            <?php echo htmlspecialchars($status_label); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($req['status'] === 'Complete' && !empty($req['satisfaction_score'])): ?>
                                            <span class="text-lg tracking-wide text-yellow-400">
                                                <?php echo str_repeat('★', $req['satisfaction_score']); ?><?php echo str_repeat('☆', 5 - $req['satisfaction_score']); ?>
                                            </span>
                                        <?php elseif ($req['status'] === 'Complete'): ?>
                                            <button onclick="rateRequest(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-sm hover:underline transition">
                                                ⭐ <?php echo $t['rate_request']; ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="<?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> text-sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            <!-- View Details Button (Icon only) -->
                                            <button onclick="viewDetails(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')"
                                                title="<?php echo $t['view_details']; ?>"
                                                class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-gray-600 rounded-lg transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>

                                            <!-- Cancel/Delete Button (only for New status, icon only) -->
                                            <?php if ($req['status'] === 'New'): ?>
                                                <button onclick="cancelRequest(<?php echo $req['request_id']; ?>, '<?php echo $req['source_table']; ?>')"
                                                    title="<?php echo $t['cancel']; ?>"
                                                    class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-gray-600 rounded-lg transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
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

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-3xl w-full border <?php echo $border_class; ?> my-auto">
        <div class="sticky top-0 z-10 flex items-center justify-between p-6 border-b <?php echo $border_class; ?> bg-inherit rounded-t-xl">
            <h3 class="text-2xl font-bold <?php echo $text_class; ?>"><?php echo $t['request_details']; ?></h3>
            <button onclick="closeDetailsModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?> transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="detailsContent" class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
            <div class="flex justify-center py-8">
                <div class="animate-spin">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div id="ratingModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
    <div class="<?php echo $card_bg; ?> rounded-xl shadow-2xl max-w-sm w-full border <?php echo $border_class; ?>">
        <div class="flex items-center justify-between p-6 border-b <?php echo $border_class; ?>">
            <h3 class="text-xl font-bold <?php echo $text_class; ?>"><?php echo $t['rating_title']; ?></h3>
            <button onclick="closeRatingModal()" class="<?php echo $is_dark ? 'text-gray-400 hover:text-white' : 'text-gray-500 hover:text-gray-700'; ?> transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="ratingForm" onsubmit="submitRating(event)" class="p-6 space-y-6">
            <input type="hidden" id="rating_request_id">
            <input type="hidden" id="rating_table">
            <input type="hidden" id="rating_score" name="score">
            <!-- Star Rating -->
            <div>
                <label class="block text-sm font-bold <?php echo $text_class; ?> mb-4"><?php echo $t['rating_label']; ?></label>
                <div class="flex justify-center gap-2" id="starContainer">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="star-btn text-gray-300 hover:text-yellow-400 transition" data-rating="<?php echo $i; ?>" onclick="selectRating(<?php echo $i; ?>)">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </button>
                    <?php endfor; ?>
                </div>
                <div id="ratingLabel" class="text-center text-sm font-semibold text-gray-500 dark:text-gray-400 mt-4 min-h-6">
                    <?php echo $t['please_select_rating']; ?>
                </div>
            </div>
            <!-- Feedback -->
            <div>
                <label class="block text-sm font-bold <?php echo $text_class; ?> mb-2"><?php echo $t['additional_feedback']; ?></label>
                <textarea name="feedback" rows="3"
                    class="w-full px-4 py-3 border rounded-lg <?php echo $input_class; ?> focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="<?php echo $t['feedback_placeholder']; ?>"></textarea>
            </div>
            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold transition shadow-md hover:shadow-lg">
                    ✓ <?php echo $t['submit_rating']; ?>
                </button>
                <button type="button" onclick="closeRatingModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 py-3 rounded-lg font-bold hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    ✕ <?php echo $t['close']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
const currentLang = '<?php echo $current_lang; ?>';
const t = <?php echo json_encode($t); ?>;
const isDark = <?php echo json_encode($is_dark); ?>;
const ratingLabels = {
    1: t['rating_1'],
    2: t['rating_2'],
    3: t['rating_3'],
    4: t['rating_4'],
    5: t['rating_5']
};
let currentRating = 0;

function selectRating(score) {
    currentRating = score;
    document.getElementById('rating_score').value = score;
    
    const buttons = document.querySelectorAll('#starContainer .star-btn');
    buttons.forEach((btn, index) => {
        if (index < score) {
            btn.classList.remove('text-gray-300');
            btn.classList.add('text-yellow-400');
        } else {
            btn.classList.add('text-gray-300');
            btn.classList.remove('text-yellow-400');
        }
    });
    
    document.getElementById('ratingLabel').textContent = ratingLabels[score] || '';
}

function viewDetails(id, table) {
    document.getElementById('detailsModal').classList.remove('hidden');
    const content = document.getElementById('detailsContent');
    
    fetch(`<?php echo BASE_PATH; ?>/api/get_request_details.php?id=${id}&table=${table}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = generateDetailsHTML(data.request);
            } else {
                content.innerHTML = `<div class="text-red-600 dark:text-red-400">${t['error_loading']}</div>`;
            }
        })
        .catch(e => {
            console.error(e);
            content.innerHTML = `<div class="text-red-600 dark:text-red-400">${t['error_loading']}</div>`;
        });
}

function generateDetailsHTML(req) {
    let html = `<div class="space-y-6">`;
    
    html += `
        <div class="grid grid-cols-2 gap-4 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
            <div>
                <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">${t['request_id_label']}</div>
                <div class="text-lg font-mono font-bold text-blue-600 dark:text-blue-300">#${String(req.request_id).padStart(5, '0')}</div>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">${t['status_label']}</div>
                <div class="font-semibold <?php echo $text_class; ?>">${req.status || '-'}</div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 border rounded-lg">
                <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">${t['employee_id']}</div>
                <div class="text-lg font-mono font-bold <?php echo $text_class; ?>">${req.employee_id || '-'}</div>
            </div>
            <div class="p-4 border rounded-lg">
                <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">${t['employee_name']}</div>
                <div class="text-lg font-bold <?php echo $text_class; ?>">${req.employee_name || '-'}</div>
            </div>
        </div>
    `;
    
    if (req.reason) {
        html += `<div class="p-4 bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 rounded-r-lg">
            <h4 class="font-bold text-blue-900 dark:text-blue-100 mb-2">${t['reason']}</h4>
            <p class="text-blue-800 dark:text-blue-200 whitespace-pre-wrap">${req.reason}</p>
        </div>`;
    }
    
    if (req.purpose) {
        html += `<div class="p-4 bg-purple-50 dark:bg-purple-900 border-l-4 border-purple-500 rounded-r-lg">
            <h4 class="font-bold text-purple-900 dark:text-purple-100 mb-2">${t['purpose']}</h4>
            <p class="text-purple-800 dark:text-purple-200">${req.purpose}</p>
        </div>`;
    }
    
    if (req.handler_remarks) {
        html += `<div class="p-4 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 rounded-r-lg">
            <h4 class="font-bold text-green-900 dark:text-green-100 mb-2">${t['handler_remarks']}</h4>
            <p class="text-green-800 dark:text-green-200 whitespace-pre-wrap">${req.handler_remarks}</p>
        </div>`;
    }
    
    html += `</div>`;
    return html;
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function rateRequest(id, table) {
    currentRating = 0;
    document.getElementById('rating_request_id').value = id;
    document.getElementById('rating_table').value = table;
    document.getElementById('ratingForm').reset();
    
    document.querySelectorAll('#starContainer .star-btn').forEach(btn => {
        btn.classList.add('text-gray-300');
        btn.classList.remove('text-yellow-400');
    });
    
    document.getElementById('ratingLabel').textContent = t['please_select_rating'];
    document.getElementById('ratingModal').classList.remove('hidden');
}

function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
}

function submitRating(event) {
    event.preventDefault();
    
    if (currentRating === 0) {
        alert(t['please_select_rating']);
        return;
    }
    
    const data = {
        request_id: document.getElementById('rating_request_id').value,
        table: document.getElementById('rating_table').value,
        score: currentRating,
        feedback: document.querySelector('[name="feedback"]').value
    };
    
    fetch('<?php echo BASE_PATH; ?>/api/submit_rating.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            alert(t['rating_success']);
            closeRatingModal();
            location.reload();
        } else {
            alert(result.message || t['error_occurred']);
        }
    })
    .catch(e => alert(t['error_occurred']));
}

function cancelRequest(id, table) {
    if (!confirm(t['confirm_cancel'])) return;
    
    fetch('<?php echo BASE_PATH; ?>/api/cancel_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, table })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(t['cancel_success']);
            location.reload();
        } else {
            alert(data.message || t['error_occurred']);
        }
    })
    .catch(e => alert(t['error_occurred']));
}

// Close modals on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeDetailsModal();
        closeRatingModal();
    }
});

// Close modals on background click
document.getElementById('detailsModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeDetailsModal();
});

document.getElementById('ratingModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeRatingModal();
});
</script>