<?php

/**
 * HR Service Main Entry Point
 * Handles routing and authentication checks
 * Multi-language support: TH, EN, MY
 */
require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db/Localization.php';
require_once __DIR__ . '/db/Localization.php';
// Check if database and tables exist
if (!checkDatabaseExists() || !checkTablesExist()) {
    header('Location: ' . BASE_PATH . '/views/admin/db_manager.php');
    exit();
}
// Require authentication
AuthController::requireAuth();
// Get theme variables
extract(get_theme_vars());
$page_title = 'Dashboard';
// Connect to database
$conn = getDbConnection();
// Get statistics based on user role
$stats = [
    'pending_requests' => 0,
    'completed_requests' => 0,
    'total_documents' => 0,
    'notifications' => 0
];
if ($user_role === 'admin' || $user_role === 'officer') {
    // Admin/Officer: ดูคำขอทั้งหมด
    // นับ Pending Requests (New + In Progress) จากทุกตาราง
    $tables = [
        'leave_requests',
        'certificate_requests',
        'id_card_requests',
        'shuttle_bus_requests',
        'locker_requests',
        'supplies_requests',
        'skill_test_requests',
        'document_submissions'
    ];

    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table WHERE status IN ('New', 'In Progress')");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['pending_requests'] += $row['count'];
        }
    }

    // นับ Completed Requests
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table WHERE status = 'Complete'");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['completed_requests'] += $row['count'];
        }
    }

    // นับจำนวนเอกสารออนไลน์
    $result = $conn->query("SELECT COUNT(*) as count FROM online_documents");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_documents'] = $row['count'];
    }

    // นับ Notifications (คำขอใหม่ที่ยังไม่มี handler)
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table WHERE status = 'New' AND (handler_id IS NULL OR handler_id = '')");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['notifications'] += $row['count'];
        }
    }
} else {
    // Employee: ดูเฉพาะของตัวเอง
    $tables = [
        'leave_requests',
        'certificate_requests',
        'id_card_requests',
        'shuttle_bus_requests',
        'locker_requests',
        'supplies_requests',
        'skill_test_requests',
        'document_submissions'
    ];

    // นับ Pending Requests ของตัวเอง
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table WHERE employee_id = ? AND status IN ('New', 'In Progress')");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['pending_requests'] += $row['count'];
        }
        $stmt->close();
    }

    // นับ Completed Requests ของตัวเอง
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table WHERE employee_id = ? AND status = 'Complete'");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['completed_requests'] += $row['count'];
        }
        $stmt->close();
    }

    // นับเอกสารที่ upload โดยตัวเอง
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM online_documents WHERE upload_by = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['total_documents'] = $row['count'];
    }
    $stmt->close();

    // นับ Notifications (คำขอที่มีการอัปเดตสถานะ แต่ยังไม่ได้ให้คะแนน)
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table WHERE employee_id = ? AND status = 'Complete' AND (satisfaction_score IS NULL OR satisfaction_score = 0)");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['notifications'] += $row['count'];
        }
        $stmt->close();
    }
}
$conn->close();
// Multi-language content
$lang_content = [
    'th' => [
        'welcome' => 'ยินดีต้อนรับกลับมา',
        'welcome_desc' => 'นี่คือสิ่งที่เกิดขึ้นกับกิจกรรม HR ของคุณวันนี้',
        'pending_requests' => 'คำขอที่รอดำเนินการ',
        'completed' => 'เสร็จสมบูรณ์',
        'documents' => 'เอกสาร',
        'notifications' => 'การแจ้งเตือน',
        'view_details' => 'ดูรายละเอียด',
        'successfully_processed' => 'ดำเนินการสำเร็จแล้ว',
        'total_files' => 'ไฟล์ทั้งหมด',
        'new_requests' => 'คำขอใหม่',
        'rate_completed' => 'ให้คะแนนคำขอที่เสร็จแล้ว',
        'all_caught_up' => 'ทุกอย่างเรียบร้อย!',
        'quick_actions' => 'การดำเนินการด่วน',
        'request_leave' => 'ขอลา',
        'submit_leave' => 'ยื่นคำขอลา',
        'certificate' => 'หนังสือรับรอง',
        'request_certificate' => 'ขอหนังสือรับรอง',
        'id_card' => 'บัตรพนักงาน',
        'request_idcard' => 'ขอบัตรพนักงาน',
        'view_requests' => 'ดูคำขอ',
        'pending' => 'รอดำเนินการ',
        'recent_activity' => 'กิจกรรมล่าสุด',
        'you_have' => 'คุณมี',
        'pending_request' => 'คำขอที่รอดำเนินการ',
        'pending_requests_plural' => 'คำขอที่รอดำเนินการ',
        'view' => 'ดู',
        'completed_waiting_rating' => 'คำขอที่เสร็จแล้วรอให้คะแนน',
        'rate' => 'ให้คะแนน'
    ],
    'en' => [
        'welcome' => 'Welcome back',
        'welcome_desc' => "Here's what's happening with your HR activities today",
        'pending_requests' => 'Pending Requests',
        'completed' => 'Completed',
        'documents' => 'Documents',
        'notifications' => 'Notifications',
        'view_details' => 'View Details',
        'successfully_processed' => 'Successfully processed',
        'total_files' => 'Total files',
        'new_requests' => 'New requests',
        'rate_completed' => 'Rate completed requests',
        'all_caught_up' => 'All caught up!',
        'quick_actions' => 'Quick Actions',
        'request_leave' => 'Request Leave',
        'submit_leave' => 'Submit leave request',
        'certificate' => 'Certificate',
        'request_certificate' => 'Request certificate',
        'id_card' => 'ID Card',
        'request_idcard' => 'Request ID card',
        'view_requests' => 'View Requests',
        'pending' => 'pending',
        'recent_activity' => 'Recent Activity',
        'you_have' => 'You have',
        'pending_request' => 'pending request',
        'pending_requests_plural' => 'pending requests',
        'view' => 'View',
        'completed_waiting_rating' => 'completed request(s) waiting for rating',
        'rate' => 'Rate'
    ],
    'my' => [
        'welcome' => 'ပြန်လည်ကြိုဆိုပါသည်',
        'welcome_desc' => 'ဒီနေ့သင့် HR လုပ်ငန်းများနှင့် ဖြစ်ပျက်နေသောအရာများ',
        'pending_requests' => 'ဆောင်ရွက်ရန်ကျန်သော တောင်းဆိုချက်များ',
        'completed' => 'ပြီးစီးပြီ',
        'documents' => 'စာရွက်စာတမ်းများ',
        'notifications' => 'အသိပေးချက်များ',
        'view_details' => 'အသေးစိတ်ကြည့်ရန်',
        'successfully_processed' => 'အောင်မြင်စွာ ဆောင်ရွက်ပြီး',
        'total_files' => 'စုစုပေါင်းဖိုင်များ',
        'new_requests' => 'တောင်းဆိုချက်အသစ်များ',
        'rate_completed' => 'ပြီးစီးသော တောင်းဆိုချက်များကို အမှတ်ပေးရန်',
        'all_caught_up' => 'အားလုံးပြီးပါပြီ!',
        'quick_actions' => 'လျင်မြန်သော လုပ်ဆောင်ချက်များ',
        'request_leave' => 'ခွင့်တောင်းရန်',
        'submit_leave' => 'ခွင့်လျှောက်လွှာတင်ရန်',
        'certificate' => 'လက်မှတ်',
        'request_certificate' => 'လက်မှတ်တောင်းရန်',
        'id_card' => 'မှတ်ပုံတင်',
        'request_idcard' => 'မှတ်ပုံတင်တောင်းရန်',
        'view_requests' => 'တောင်းဆိုချက်များကြည့်ရန်',
        'pending' => 'ဆောင်ရွက်ရန်ကျန်',
        'recent_activity' => 'လတ်တလော လုပ်ဆောင်ချက်များ',
        'you_have' => 'သင့်တွင်',
        'pending_request' => 'ဆောင်ရွက်ရန်ကျန်သော တောင်းဆိုချက်',
        'pending_requests_plural' => 'ဆောင်ရွက်ရန်ကျန်သော တောင်းဆိုချက်များ',
        'view' => 'ကြည့်ရန်',
        'completed_waiting_rating' => 'အမှတ်ပေးရန်ကျန်သော ပြီးစီးသောတောင်းဆိုချက်',
        'rate' => 'အမှတ်ပေးရန်'
    ]
];
// Get current language content
$t = $lang_content[$_SESSION['language']] ?? $lang_content['th'];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<!-- Main Content -->
<div class="lg:ml-64">
    <!-- Dashboard Content -->
    <main class="p-4 md:p-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-6 text-white animate-fade-in">
            <h1 class="text-2xl md:text-3xl font-bold mb-2">
                <?php echo $t['welcome']; ?>, <?php echo htmlspecialchars($display_name); ?>!
            </h1>
            <p class="opacity-90"><?php echo $t['welcome_desc']; ?></p>
            <p class="text-sm mt-2 opacity-75">
                <?php
                if ($language === 'th') {
                    echo 'วัน' . date('l') . ' ที่ ' . date('d/m/Y') . ' เวลา ' . date('H:i');
                } elseif ($language === 'my') {
                    echo date('l, F d, Y') . ' • ' . date('H:i');
                } else {
                    echo date('l, F d, Y') . ' • ' . date('H:i');
                }
                ?>
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Card 1 - Pending Requests -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">
                            <?php echo $t['pending_requests']; ?>
                        </p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo number_format($stats['pending_requests']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                        <?php echo $t['view_details']; ?> →
                    </a>
                </div>
            </div>

            <!-- Card 2 - Completed -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">
                            <?php echo $t['completed']; ?>
                        </p>
                        <p class="text-3xl font-bold text-green-600"><?php echo number_format($stats['completed_requests']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?>">
                        <?php echo $t['successfully_processed']; ?>
                    </span>
                </div>
            </div>

            <!-- Card 3 - Documents -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">
                            <?php echo $t['documents']; ?>
                        </p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['total_documents']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?>">
                        <?php echo $t['total_files']; ?>
                    </span>
                </div>
            </div>

            <!-- Card 4 - Notifications -->
            <div class="<?php echo $card_bg; ?> rounded-lg shadow p-6 theme-transition transform hover:scale-105 transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm <?php echo $is_dark ? 'text-gray-400' : 'text-gray-600'; ?> mb-1">
                            <?php echo $t['notifications']; ?>
                        </p>
                        <p class="text-3xl font-bold text-orange-600"><?php echo number_format($stats['notifications']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <?php if ($stats['notifications'] > 0): ?>
                        <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="text-xs text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 font-medium">
                            <?php echo $user_role === 'employee' ? $t['rate_completed'] : $t['new_requests']; ?> →
                        </a>
                    <?php else: ?>
                        <span class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?>">
                            <?php echo $t['all_caught_up']; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="<?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
            <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <?php echo $t['quick_actions']; ?>
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Request Leave -->
                <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/request_leave.php'"
                    class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-blue-500 hover:bg-gray-700' : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'; ?> rounded-lg transition group">
                    <svg class="w-8 h-8 text-blue-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium <?php echo $text_class; ?> text-center"><?php echo $t['request_leave']; ?></span>
                    <span class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> mt-1"><?php echo $t['submit_leave']; ?></span>
                </button>

                <!-- Request Certificate -->
                <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/request_certificate.php'"
                    class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-green-500 hover:bg-gray-700' : 'border-gray-200 hover:border-green-500 hover:bg-green-50'; ?> rounded-lg transition group">
                    <svg class="w-8 h-8 text-green-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    <span class="text-sm font-medium <?php echo $text_class; ?> text-center"><?php echo $t['certificate']; ?></span>
                    <span class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> mt-1"><?php echo $t['request_certificate']; ?></span>
                </button>

                <!-- Request ID Card -->
                <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/request_idcard.php'"
                    class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-purple-500 hover:bg-gray-700' : 'border-gray-200 hover:border-purple-500 hover:bg-purple-50'; ?> rounded-lg transition group">
                    <svg class="w-8 h-8 text-purple-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                    </svg>
                    <span class="text-sm font-medium <?php echo $text_class; ?> text-center"><?php echo $t['id_card']; ?></span>
                    <span class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> mt-1"><?php echo $t['request_idcard']; ?></span>
                </button>

                <!-- View Requests -->
                <button onclick="window.location.href='<?php echo BASE_PATH; ?>/views/employee/my_requests.php'"
                    class="flex flex-col items-center p-4 border-2 <?php echo $is_dark ? 'border-gray-700 hover:border-orange-500 hover:bg-gray-700' : 'border-gray-200 hover:border-orange-500 hover:bg-orange-50'; ?> rounded-lg transition group">
                    <svg class="w-8 h-8 text-orange-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="text-sm font-medium <?php echo $text_class; ?> text-center"><?php echo $t['view_requests']; ?></span>
                    <span class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> mt-1">
                        <?php echo $stats['pending_requests']; ?> <?php echo $t['pending']; ?>
                    </span>
                </button>
            </div>
        </div>

        <!-- Recent Activity (if any requests exist) -->
        <?php if ($stats['pending_requests'] > 0 || $stats['completed_requests'] > 0): ?>
            <div class="mt-6 <?php echo $card_bg; ?> rounded-lg shadow-lg p-6 theme-transition">
                <h3 class="text-lg font-bold <?php echo $text_class; ?> mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo $t['recent_activity']; ?>
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                            <span class="<?php echo $text_class; ?> text-sm">
                                <?php echo $t['you_have']; ?> <strong><?php echo $stats['pending_requests']; ?></strong>
                                <?php echo $stats['pending_requests'] === 1 ? $t['pending_request'] : $t['pending_requests_plural']; ?>
                            </span>
                        </div>
                        <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <?php echo $t['view']; ?> →
                        </a>
                    </div>

                    <?php if ($stats['notifications'] > 0): ?>
                        <div class="flex items-center justify-between p-3 <?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> rounded-lg">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mr-3"></div>
                                <span class="<?php echo $text_class; ?> text-sm">
                                    <strong><?php echo $stats['notifications']; ?></strong> <?php echo $t['completed_waiting_rating']; ?>
                                </span>
                            </div>
                            <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                                <?php echo $t['rate']; ?> →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>

<!-- Add fade-in animation -->
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
</style>
</body>

<?php include __DIR__ . '/includes/footer.php'; ?>

</html>