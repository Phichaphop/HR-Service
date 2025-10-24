<?php

/**
 * Reusable Header Component (Multi-language Support)
 * รองรับ TH / EN / MY
 */
// ดึงข้อมูล theme และ user แบบรวม
extract(get_theme_vars());
// Display name ตาม language
$display_name = get_display_name();
// Multi-language content สำหรับ Header
$header_lang = [
    'th' => [
        'dashboard' => 'แดชบอร์ด',
        'search_placeholder' => 'ค้นหา...',
        'notifications' => 'การแจ้งเตือน',
        'profile' => 'โปรไฟล์',
        'settings' => 'ตั้งค่า',
        'logout' => 'ออกจากระบบ',
        'light_mode' => 'โหมดสว่าง',
        'dark_mode' => 'โหมดมืด',
        'language' => 'ภาษา',
        'thai' => 'ไทย',
        'english' => 'อังกฤษ',
        'myanmar' => 'พม่า'
    ],
    'en' => [
        'dashboard' => 'Dashboard',
        'search_placeholder' => 'Search...',
        'notifications' => 'Notifications',
        'profile' => 'Profile',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'light_mode' => 'Light Mode',
        'dark_mode' => 'Dark Mode',
        'language' => 'Language',
        'thai' => 'Thai',
        'english' => 'English',
        'myanmar' => 'Myanmar'
    ],
    'my' => [
        'dashboard' => 'မှတ်တမ်းဘုတ်',
        'search_placeholder' => 'ရှာဖွေရန်...',
        'notifications' => 'အသိပေးချက်များ',
        'profile' => 'ကိုယ်ရေးအချက်အလက်',
        'settings' => 'ဆက်တင်များ',
        'logout' => 'ထွက်ရန်',
        'light_mode' => 'အလင်းမုဒ်',
        'dark_mode' => 'အမှောင်မုဒ်',
        'language' => 'ဘာသာစကား',
        'thai' => 'ထိုင်း',
        'english' => 'အင်္ဂလိပ်',
        'myanmar' => 'မြန်မာ'
    ]
];
// Get current language content
$h = $header_lang[$_SESSION['language']] ?? $header_lang['th'];
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'HR Service'; ?> - <?php echo __('app_title'); ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_PATH; ?>/assets/images/favicons/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_PATH; ?>/assets/images/favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_PATH; ?>/assets/images/favicons/favicon-32x32.png">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_PATH; ?>/assets/images/favicons/apple-touch-icon.png">

    <!-- Android Chrome Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo BASE_PATH; ?>/assets/images/favicons/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo BASE_PATH; ?>/assets/images/favicons/android-chrome-512x512.png">

    <!-- Web App Manifest (Optional) -->
    <link rel="manifest" href="<?php echo BASE_PATH; ?>/site.webmanifest">

    <!-- Theme Color (สีที่แสดงในแถบเบราว์เซอร์บนมือถือ) -->
    <meta name="theme-color" content="<?php echo $is_dark ? '#1f2937' : '#ffffff'; ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .theme-transition {
            transition: all 0.3s ease;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: <?php echo $is_dark ? '#1f2937' : '#f1f5f9'; ?>;
        }

        ::-webkit-scrollbar-thumb {
            background: <?php echo $is_dark ? '#4b5563' : '#cbd5e1'; ?>;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: <?php echo $is_dark ? '#6b7280' : '#94a3b8'; ?>;
        }

        /* Language dropdown hover effect */
        .language-option:hover {
            background-color: <?php echo $is_dark ? '#374151' : '#f3f4f6'; ?>;
        }
    </style>
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>

<body class="<?php echo $bg_class; ?> theme-transition" data-base-path="<?php echo BASE_PATH; ?>">
    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleMobileMenu()"></div>

    <!-- Top Bar (Mobile & Desktop) -->
    <header class="<?php echo $header_bg; ?> shadow-sm sticky top-0 z-30 theme-transition lg:ml-64 border-b <?php echo $border_class; ?>">
        <div class="flex items-center justify-between px-4 py-3">
            <!-- Left Side: Mobile Menu + Page Title -->
            <div class="flex items-center space-x-3">
                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()"
                    class="lg:hidden p-2 rounded-lg <?php echo $text_class; ?> hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?> transition"
                    title="<?php echo $language === 'th' ? 'เมนู' : ($language === 'my' ? 'မီနူး' : 'Menu'); ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Page Title -->
                <div>
                    <h2 class="text-lg md:text-xl font-semibold <?php echo $text_class; ?>">
                        <?php
                        // แปลชื่อหน้าตามภาษา
                        $page_titles = [
                            'Dashboard' => ['th' => 'แดชบอร์ด', 'en' => 'Dashboard', 'my' => 'မှတ်တမ်းဘုတ်'],
                            'Employees' => ['th' => 'พนักงาน', 'en' => 'Employees', 'my' => 'ဝန်ထမ်းများ'],
                            'My Requests' => ['th' => 'คำขอของฉัน', 'en' => 'My Requests', 'my' => 'ကျွန်ုပ်၏တောင်းဆိုချက်များ'],
                            'Request Management' => ['th' => 'จัดการคำขอ', 'en' => 'Request Management', 'my' => 'တောင်းဆိုချက်စီမံခန့်ခွဲမှု'],
                            'Settings' => ['th' => 'ตั้งค่า', 'en' => 'Settings', 'my' => 'ဆက်တင်များ'],
                        ];

                        $current_page = $page_title ?? 'Dashboard';
                        echo $page_titles[$current_page][$language] ?? $current_page;
                        ?>
                    </h2>
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> hidden md:block">
                        <?php
                        if ($language === 'th') {
                            echo date('d/m/Y H:i');
                        } elseif ($language === 'my') {
                            echo date('d/m/Y H:i');
                        } else {
                            echo date('M d, Y H:i');
                        }
                        ?>
                    </p>
                </div>
            </div>

            <!-- Right Side: Actions -->
            <div class="flex items-center space-x-2 md:space-x-3">

                <!-- Language Switcher -->
                <div class="relative group">
                    <button class="flex items-center space-x-2 px-3 py-2 border <?php echo $border_class; ?> rounded-lg text-sm hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition"
                        onclick="toggleLanguageMenu(event)">
                        <span class="hidden md:inline <?php echo $text_class; ?> font-medium">
                            <?php
                            echo $language === 'th' ? 'TH' : ($language === 'my' ? 'MY' : 'EN');
                            ?>
                        </span>
                        <svg class="w-4 h-4 <?php echo $text_class; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Language Dropdown -->
                    <div id="languageMenu" class="hidden absolute right-0 mt-2 w-48 <?php echo $card_bg; ?> border <?php echo $border_class; ?> rounded-lg shadow-xl z-50">
                        <div class="py-1">
                            <button onclick="changeLanguage('th')"
                                class="language-option w-full flex items-center px-4 py-2.5 <?php echo $text_class; ?> text-sm <?php echo $language === 'th' ? 'bg-blue-50 dark:bg-blue-900 font-semibold' : ''; ?>">
                                <span class="text-xl mr-3">🇹🇭</span>
                                <span><?php echo $h['thai']; ?></span>
                                <?php if ($language === 'th'): ?>
                                    <svg class="w-4 h-4 ml-auto text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                <?php endif; ?>
                            </button>

                            <button onclick="changeLanguage('en')"
                                class="language-option w-full flex items-center px-4 py-2.5 <?php echo $text_class; ?> text-sm <?php echo $language === 'en' ? 'bg-blue-50 dark:bg-blue-900 font-semibold' : ''; ?>">
                                <span class="text-xl mr-3">🇬🇧</span>
                                <span><?php echo $h['english']; ?></span>
                                <?php if ($language === 'en'): ?>
                                    <svg class="w-4 h-4 ml-auto text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                <?php endif; ?>
                            </button>

                            <button onclick="changeLanguage('my')"
                                class="language-option w-full flex items-center px-4 py-2.5 <?php echo $text_class; ?> text-sm <?php echo $language === 'my' ? 'bg-blue-50 dark:bg-blue-900 font-semibold' : ''; ?>">
                                <span class="text-xl mr-3">🇲🇲</span>
                                <span><?php echo $h['myanmar']; ?></span>
                                <?php if ($language === 'my'): ?>
                                    <svg class="w-4 h-4 ml-auto text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Theme Toggle -->
                <button onclick="toggleTheme()"
                    class="p-2 rounded-lg <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-100'; ?> transition group"
                    title="<?php echo $is_dark ? $h['light_mode'] : $h['dark_mode']; ?>">
                    <?php if ($is_dark): ?>
                        <svg class="w-6 h-6 text-yellow-400 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-gray-700 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    <?php endif; ?>
                </button>

                <!-- User Menu -->
                <div class="relative">
                    <button onclick="toggleUserMenu(event)"
                        class="flex items-center space-x-2 p-1.5 rounded-lg hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?> transition">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                            <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                        </div>
                        <svg class="w-4 h-4 <?php echo $text_class; ?> hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- User Dropdown -->
                    <div id="userMenu" class="hidden absolute right-0 mt-2 w-56 <?php echo $card_bg; ?> border <?php echo $border_class; ?> rounded-lg shadow-xl z-50">
                        <div class="px-4 py-3 border-b <?php echo $border_class; ?>">
                            <p class="text-sm font-semibold <?php echo $text_class; ?>"><?php echo htmlspecialchars($display_name); ?></p>
                            <p class="text-xs <?php echo $is_dark ? 'text-gray-400' : 'text-gray-500'; ?> mt-0.5"><?php echo htmlspecialchars($user_id); ?></p>
                        </div>
                        <div class="py-1">
                            <a href="<?php echo BASE_PATH; ?>/views/settings.php"
                                class="flex items-center px-4 py-2.5 text-sm <?php echo $text_class; ?> hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <?php echo $h['settings']; ?>
                            </a>
                            <a href="<?php echo BASE_PATH; ?>/controllers/logout.php"
                                class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900 dark:hover:bg-opacity-20 transition">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <?php echo $h['logout']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Load Common JavaScript -->
    <script src="<?php echo BASE_PATH; ?>/includes/common.js"></script>

    <script>
        // Toggle Language Menu
        function toggleLanguageMenu(event) {
            event.stopPropagation();
            const menu = document.getElementById('languageMenu');
            const userMenu = document.getElementById('userMenu');
            userMenu.classList.add('hidden');
            menu.classList.toggle('hidden');
        }

        // Toggle User Menu
        function toggleUserMenu(event) {
            event.stopPropagation();
            const menu = document.getElementById('userMenu');
            const langMenu = document.getElementById('languageMenu');
            langMenu.classList.add('hidden');
            menu.classList.toggle('hidden');
        }

        // Close menus when clicking outside
        document.addEventListener('click', function() {
            document.getElementById('languageMenu').classList.add('hidden');
            document.getElementById('userMenu').classList.add('hidden');
        });
    </script>