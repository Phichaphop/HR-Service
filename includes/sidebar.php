<?php

/**
 * Sidebar Navigation Component - UPDATED
 * ✨ Added: Anonymous Complaint System Menu
 * Supports 3 languages: Thai, English, Myanmar
 * Added: Document Delivery List
 */
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get current language
$current_lang = $_SESSION['language'] ?? 'th';
$user_role = $_SESSION['role'] ?? 'employee';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
// Define theme classes
$is_dark = ($theme_mode === 'dark');
$sidebar_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$text_class = $is_dark ? 'text-gray-300' : 'text-gray-700';
$hover_bg = $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-100';
$active_bg = $is_dark ? 'bg-gray-700' : 'bg-blue-50';
$active_text = $is_dark ? 'text-blue-400' : 'text-blue-600';
$border_class = $is_dark ? 'border-gray-700' : 'border-gray-200';
// Navigation menu translations - UPDATED with Complaint System
$menu_items = [
    'th' => [
        'dashboard' => 'แดชบอร์ด',
        'my_requests' => 'คำขอของฉัน',
        'request_leave' => 'ขอลาพักงาน',
        'request_certificate' => 'ขอหนังสือรับรอง',
        'request_idcard' => 'ขอบัตรพนักงาน',
        'submit_complaint' => 'ร้องเรียน',
        'my_complaints' => 'การร้องเรียนของฉัน',
        'document_delivery' => 'ระบบส่งเอกสาร',
        'document_delivery_list' => 'รายการส่งเอกสาร',
        'employees' => 'จัดการพนักงาน',
        'request_management' => 'จัดการคำขอ',
        'admin_create_request' => 'สร้างคำขอ',
        'manage_complaints' => 'จัดการการร้องเรียน',
        'complaint_categories' => 'จัดการประเภทร้องเรียน',
        'locker_management' => 'จัดการตู้ล็อกเกอร์',
        'documents' => 'จัดการเอกสารออนไลน์',
        'master_data' => 'ตั้งค่าข้อมูลหลัก',
        'company_settings' => 'ตั้งค่าบริษัท',
        'settings' => 'ตั้งค่า',
        'logout' => 'ออกจากระบบ',
        'employee_services' => 'บริการพนักงาน',
        'admin_tools' => 'เครื่องมือผู้ดูแล',
        'system' => 'ระบบ',
        'setup_certificate' => 'ตั้งค่าใบรับรอง'
    ],
    'en' => [
        'dashboard' => 'Dashboard',
        'my_requests' => 'My Requests',
        'request_leave' => 'Request Leave',
        'request_certificate' => 'Request Certificate',
        'request_idcard' => 'Request ID Card',
        'submit_complaint' => 'Submit Complaint',
        'my_complaints' => 'My Complaints',
        'document_delivery' => 'Document Delivery',
        'document_delivery_list' => 'Delivery List',
        'employees' => 'Manage Employees',
        'request_management' => 'Request Management',
        'admin_create_request' => 'Create request',
        'manage_complaints' => 'Manage Complaints',
        'complaint_categories' => 'Complaint Categories',
        'locker_management' => 'Locker Management',
        'documents' => 'Online Documents',
        'master_data' => 'Master Data',
        'company_settings' => 'Company Settings',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'employee_services' => 'Employee Services',
        'admin_tools' => 'Admin Tools',
        'system' => 'System',
        'setup_certificate' => 'Setup Certificate'
    ],
    'my' => [
        'dashboard' => 'မှတ်တမ်းဘုတ်',
        'my_requests' => 'ကျွန်ုပ်၏တောင်းဆိုချက်များ',
        'request_leave' => 'ခွင့်တောင်းခံရန်',
        'request_certificate' => 'လက်မှတ်တောင်းခံရန်',
        'request_idcard' => 'မှတ်ပုံတင်ကတ်တောင်းခံရန်',
        'submit_complaint' => 'အမည်မဖော်စာချပ်ခွင့်တောင်းခံရန်',
        'my_complaints' => 'ကျွန်ုပ်၏စာချပ်များ',
        'document_delivery' => 'စာ類တင်သွင်းမှုစနစ်',
        'document_delivery_list' => 'တင်သွင်းမှုစာရင်း',
        'employees' => 'ဝန်ထမ်းများစီမံခန့်ခွဲရန်',
        'request_management' => 'တောင်းဆိုချက်စီမံခန့်ခွဲရန်',
        'admin_create_request' => 'တောင်းဆိုချက်များစီမံခန့်ခွဲရန်',
        'manage_complaints' => 'စာချပ်များကိုစီမံခန့်ခွဲ',
        'complaint_categories' => 'စာချပ်အမျိုးအစားများ',
        'locker_management' => 'သော့ခတ်စက်များစီမံခန့်ခွဲရန်',
        'documents' => 'အွန်လိုင်းစာရွက်စာတမ်းများ',
        'master_data' => 'အဓိကဒေတာ',
        'company_settings' => 'ကုမ္ပဏီဆက်တင်များ',
        'settings' => 'ဆက်တင်များ',
        'logout' => 'ထွက်ရန်',
        'employee_services' => 'ဝန်ထမ်းဝန်ဆောင်မှုများ',
        'admin_tools' => 'စီမံခန့်ခွဲသူကိရိယာများ',
        'system' => 'စနစ်',
        'setup_certificate' => 'လက်မှတ်သမ္ပုလ်များစီမံခန့်ခွဲမည်'

    ]
];
// Get menu texts based on current language
$menu = $menu_items[$current_lang];
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside id="sidebar"
    class="fixed left-0 top-0 z-40 h-screen w-64 <?php echo $sidebar_bg; ?> border-r <?php echo $border_class; ?> transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 theme-transition">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between p-4 border-b <?php echo $border_class; ?>">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold <?php echo $is_dark ? 'text-white' : 'text-gray-800'; ?>">HR Service</h2>
                <p class="text-xs <?php echo $text_class; ?>">Management System</p>
            </div>
        </div>
        <button onclick="toggleMobileMenu()" class="lg:hidden <?php echo $text_class; ?> hover:text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto p-4">
        <!-- Dashboard -->
        <a href="<?php echo BASE_PATH; ?>/index.php"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'index.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="font-medium"><?php echo $menu['dashboard']; ?></span>
        </a>
        <!-- Employee Services Section -->
        <div class="mt-6 mb-2">
            <h3 class="px-4 text-xs font-semibold <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> uppercase tracking-wider">
                <?php echo $menu['employee_services']; ?>
            </h3>
        </div>
        <!-- My Requests -->
        <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'my_requests.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium"><?php echo $menu['my_requests']; ?></span>
        </a>
        <!-- Request Leave -->
        <a href="<?php echo BASE_PATH; ?>/views/employee/request_leave.php"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'request_leave.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="font-medium"><?php echo $menu['request_leave']; ?></span>
        </a>
        <!-- Request Certificate -->
        <a href="<?php echo BASE_PATH; ?>/views/employee/request_certificate.php"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'request_certificate.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium"><?php echo $menu['request_certificate']; ?></span>
        </a>
        <!-- Request ID Card -->
        <a href="<?php echo BASE_PATH; ?>/views/employee/request_idcard.php"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'request_idcard.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
            </svg>
            <span class="font-medium"><?php echo $menu['request_idcard']; ?></span>
        </a>

        <a href="<?php echo BASE_PATH; ?>/views/employee/request_complaint.php"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'request_complaint.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="font-medium"><?php echo $menu['submit_complaint']; ?></span>
        </a>

        <?php if ($user_role === 'admin' || $user_role === 'officer'): ?>
            <!-- Admin Tools Section -->
            <div class="mt-6 mb-2">
                <h3 class="px-4 text-xs font-semibold <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?> uppercase tracking-wider">
                    <?php echo $menu['admin_tools']; ?>
                </h3>
            </div>
            <!-- Manage Employees -->
            <?php if ($user_role === 'admin'): ?>
                <a href="<?php echo BASE_PATH; ?>/views/admin/employees.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'employees.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium"><?php echo $menu['employees']; ?></span>
                </a>
            <?php endif; ?>
            <!-- Request Management -->
            <a href="<?php echo BASE_PATH; ?>/views/admin/request_management.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'request_management.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span class="font-medium"><?php echo $menu['request_management']; ?></span>
            </a>
            <!-- Admin Create Request -->
            <a href="<?php echo BASE_PATH; ?>/views/admin/admin_create_request.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'admin_create_request.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span class="font-medium"><?php echo $menu['admin_create_request']; ?></span>
            </a>

            <!-- Online Documents -->
            <a href="<?php echo BASE_PATH; ?>/views/admin/documents.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'documents.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span class="font-medium"><?php echo $menu['documents']; ?></span>
            </a>
            <!-- Setup Certificate Templates -->
            <a href="<?php echo BASE_PATH; ?>/views/admin/certificate_management.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'certificate_management.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span class="font-medium"><?php echo $menu['setup_certificate']; ?></span>
            </a>

            <a href="<?php echo BASE_PATH; ?>/views/admin/complaint_management.php"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'complaint_management.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span class="font-medium"><?php echo $menu['submit_complaint']; ?></span>
            </a>

            <?php if ($user_role === 'admin'): ?>
                <!-- Master Data -->
                <a href="<?php echo BASE_PATH; ?>/views/admin/master_data.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'master_data.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                    <span class="font-medium"><?php echo $menu['master_data']; ?></span>
                </a>
                <!-- Company Settings -->
                <a href="<?php echo BASE_PATH; ?>/views/admin/company_settings.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg mb-1 <?php echo $hover_bg; ?> <?php echo ($current_page === 'company_settings.php') ? $active_bg . ' ' . $active_text : $text_class; ?> transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="font-medium"><?php echo $menu['company_settings']; ?></span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</aside>
<!-- Mobile Menu Overlay -->
<div id="mobileMenuOverlay"
    class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"
    onclick="toggleMobileMenu()">
</div>
<script>
    // Toggle Mobile Menu
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileMenuOverlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
    // Close mobile menu when clicking a link
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                toggleMobileMenu();
            }
        });
    });
    // Close mobile menu on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
        }
    });
</script>