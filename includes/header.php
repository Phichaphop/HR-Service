<?php

/**
 * Reusable Header Component (Multi-language Support)
 * รองรับ TH / EN / MY
 * 
 * UPDATE: Display employee profile picture with fallback avatar
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Monoton&family=Noto+Sans+Thai:wght@100..900&family=Sankofa+Display&family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

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

        * {
            font-family: "Noto Sans Thai", sans-serif;
        }

        /* Profile image styling */
        .profile-img {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .profile-img:hover {
            transform: scale(1.05);
        }
    </style>
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>

<body class="<?php echo $bg_class; ?> theme-transition" data-base-path="<?php echo BASE_PATH; ?>">
    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleMobileMenu()"></div>
    <!-- Top Bar (Mobile & Desktop) -->
    <header class="<?php echo $header_bg; ?> shadow-sm sticky top-0 z-30 theme-transition lg:ml-64 border-b <?php echo $border_class; ?>">
        <div class="flex items-center justify-between px-4 py-4">
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

                <!-- LINE OV Link (NEW) -->
                <a href="https://line.me/R/ti/p/@785zvvgo"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center justify-center p-2 rounded-lg <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-100'; ?> transition group"
                    title="<?php echo $language === 'th' ? 'ไลน์ OV ของเรา' : ($language === 'my' ? 'ကျွန်ုပ်တို့၏ LINE OV' : 'Our LINE OV'); ?>">
                    <!-- LINE Icon SVG -->
                    <svg class="w-6 h-6 text-green-500 group-hover:scale-110 transition-transform duration-200"
                        viewBox="0 0 24 24"
                        fill="currentColor">
                        <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.771.039 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" />
                    </svg>
                </a>

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

                <!-- Language Switcher with Circular Flags -->
                <div class="relative group">
                    <button class="flex items-center gap-1.5 px-2.5 py-2 border <?php echo $border_class; ?> rounded-lg hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-50'; ?> transition"
                        onclick="toggleLanguageMenu(event)">
                        <!-- SVG Flag Icons - Circular -->
                        <?php if ($language === 'th'): ?>
                            <!-- Thailand Flag -->
                            <svg class="w-7 h-7 rounded-full border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>" viewBox="0 0 32 32">
                                <defs>
                                    <clipPath id="circleTh">
                                        <circle cx="16" cy="16" r="16" />
                                    </clipPath>
                                </defs>
                                <g clip-path="url(#circleTh)">
                                    <rect width="32" height="32" fill="#fff" />
                                    <rect y="0" width="32" height="5.33" fill="#A51931" />
                                    <rect y="26.67" width="32" height="5.33" fill="#A51931" />
                                    <rect y="5.33" width="32" height="21.34" fill="#F4F5F8" />
                                    <rect y="10.67" width="32" height="10.67" fill="#2D2A4A" />
                                </g>
                            </svg>
                        <?php elseif ($language === 'my'): ?>
                            <!-- Myanmar Flag -->
                            <svg class="w-7 h-7 rounded-full border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>" viewBox="0 0 32 32">
                                <defs>
                                    <clipPath id="circleMy">
                                        <circle cx="16" cy="16" r="16" />
                                    </clipPath>
                                </defs>
                                <g clip-path="url(#circleMy)">
                                    <rect width="32" height="32" fill="#fff" />
                                    <rect y="0" width="32" height="10.67" fill="#FECB00" />
                                    <rect y="10.67" width="32" height="10.67" fill="#34B233" />
                                    <rect y="21.34" width="32" height="10.67" fill="#EA2839" />
                                    <path d="M16 8 L18.5 15 L26 15 L20 19.5 L22.5 26.5 L16 22 L9.5 26.5 L12 19.5 L6 15 L13.5 15 Z" fill="#fff" />
                                </g>
                            </svg>
                        <?php else: ?>
                            <!-- UK Flag -->
                            <svg class="w-7 h-7 rounded-full border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>" viewBox="0 0 32 32">
                                <defs>
                                    <clipPath id="circleEn">
                                        <circle cx="16" cy="16" r="16" />
                                    </clipPath>
                                </defs>
                                <g clip-path="url(#circleEn)">
                                    <rect width="32" height="32" fill="#012169" />
                                    <path d="M0 0 L32 21.33 M32 0 L0 21.33 M0 32 L32 10.67 M32 32 L0 10.67" stroke="#fff" stroke-width="3.2" />
                                    <path d="M0 0 L32 21.33 M32 0 L0 21.33 M0 32 L32 10.67 M32 32 L0 10.67" stroke="#C8102E" stroke-width="2.13" />
                                    <path d="M16 0 V32 M0 16 H32" stroke="#fff" stroke-width="5.33" />
                                    <path d="M16 0 V32 M0 16 H32" stroke="#C8102E" stroke-width="3.2" />
                                </g>
                            </svg>
                        <?php endif; ?>

                        <svg class="w-3.5 h-3.5 <?php echo $text_class; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Language Dropdown Menu -->
                    <div id="languageMenu" class="hidden absolute right-0 mt-2 w-48 <?php echo $card_bg; ?> border <?php echo $border_class; ?> rounded-lg shadow-xl z-50">
                        <div class="py-1">
                            <!-- Thai Option -->
                            <button onclick="changeLanguage('th')"
                                class="language-option w-full flex items-center px-4 py-2.5 <?php echo $text_class; ?> text-sm <?php echo $language === 'th' ? 'bg-blue-50 dark:bg-blue-900 font-semibold' : ''; ?> transition">
                                <!-- Thailand Flag - Circular -->
                                <svg class="w-7 h-7 mr-3 rounded-full border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>" viewBox="0 0 32 32">
                                    <defs>
                                        <clipPath id="circleTh2">
                                            <circle cx="16" cy="16" r="16" />
                                        </clipPath>
                                    </defs>
                                    <g clip-path="url(#circleTh2)">
                                        <rect width="32" height="32" fill="#fff" />
                                        <rect y="0" width="32" height="5.33" fill="#A51931" />
                                        <rect y="26.67" width="32" height="5.33" fill="#A51931" />
                                        <rect y="5.33" width="32" height="21.34" fill="#F4F5F8" />
                                        <rect y="10.67" width="32" height="10.67" fill="#2D2A4A" />
                                    </g>
                                </svg>
                                <span><?php echo $h['thai']; ?></span>
                                <?php if ($language === 'th'): ?>
                                    <svg class="w-4 h-4 ml-auto text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                <?php endif; ?>
                            </button>

                            <!-- English Option -->
                            <button onclick="changeLanguage('en')"
                                class="language-option w-full flex items-center px-4 py-2.5 <?php echo $text_class; ?> text-sm <?php echo $language === 'en' ? 'bg-blue-50 dark:bg-blue-900 font-semibold' : ''; ?> transition">
                                <!-- UK Flag - Circular -->
                                <svg class="w-7 h-7 mr-3 rounded-full border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>" viewBox="0 0 32 32">
                                    <defs>
                                        <clipPath id="circleEn2">
                                            <circle cx="16" cy="16" r="16" />
                                        </clipPath>
                                    </defs>
                                    <g clip-path="url(#circleEn2)">
                                        <rect width="32" height="32" fill="#012169" />
                                        <path d="M0 0 L32 21.33 M32 0 L0 21.33 M0 32 L32 10.67 M32 32 L0 10.67" stroke="#fff" stroke-width="3.2" />
                                        <path d="M0 0 L32 21.33 M32 0 L0 21.33 M0 32 L32 10.67 M32 32 L0 10.67" stroke="#C8102E" stroke-width="2.13" />
                                        <path d="M16 0 V32 M0 16 H32" stroke="#fff" stroke-width="5.33" />
                                        <path d="M16 0 V32 M0 16 H32" stroke="#C8102E" stroke-width="3.2" />
                                    </g>
                                </svg>
                                <span><?php echo $h['english']; ?></span>
                                <?php if ($language === 'en'): ?>
                                    <svg class="w-4 h-4 ml-auto text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                <?php endif; ?>
                            </button>

                            <!-- Myanmar Option -->
                            <button onclick="changeLanguage('my')"
                                class="language-option w-full flex items-center px-4 py-2.5 <?php echo $text_class; ?> text-sm <?php echo $language === 'my' ? 'bg-blue-50 dark:bg-blue-900 font-semibold' : ''; ?> transition">
                                <!-- Myanmar Flag - Circular -->
                                <svg class="w-7 h-7 mr-3 rounded-full border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>" viewBox="0 0 32 32">
                                    <defs>
                                        <clipPath id="circleMy2">
                                            <circle cx="16" cy="16" r="16" />
                                        </clipPath>
                                    </defs>
                                    <g clip-path="url(#circleMy2)">
                                        <rect width="32" height="32" fill="#fff" />
                                        <rect y="0" width="32" height="10.67" fill="#FECB00" />
                                        <rect y="10.67" width="32" height="10.67" fill="#34B233" />
                                        <rect y="21.34" width="32" height="10.67" fill="#EA2839" />
                                        <path d="M16 8 L18.5 15 L26 15 L20 19.5 L22.5 26.5 L16 22 L9.5 26.5 L12 19.5 L6 15 L13.5 15 Z" fill="#fff" />
                                    </g>
                                </svg>
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

                <!-- User Menu -->
                <div class="relative">
                    <?php
                    // Get profile picture from session (updated approach)
                    $profile_pic = isset($profile_pic) ? $profile_pic : (isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : '');
                    $first_initial = strtoupper(substr($display_name, 0, 1));
                    ?>
                    <button onclick="toggleUserMenu(event)"
                        class="flex items-center space-x-2 p-1.5 rounded-lg hover:<?php echo $is_dark ? 'bg-gray-700' : 'bg-gray-100'; ?> transition">
                        <!-- Profile Picture or Avatar -->
                        <?php if (!empty($profile_pic) && file_exists(__DIR__ . '/../' . $profile_pic)): ?>
                            <!-- Show actual profile picture -->
                            <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($profile_pic); ?>"
                                alt="<?php echo htmlspecialchars($display_name); ?>"
                                class="profile-img w-8 h-8 rounded-full object-cover border-2 <?php echo $is_dark ? 'border-gray-600' : 'border-gray-300'; ?>"
                                title="<?php echo htmlspecialchars($display_name); ?>"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <?php endif; ?>
                        <!-- Fallback Avatar (show by default if no image or image fails to load) -->
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 border-2 <?php echo $is_dark ? 'border-blue-600' : 'border-blue-400'; ?>"
                            style="<?php echo (!empty($profile_pic) && file_exists(__DIR__ . '/../' . $profile_pic)) ? 'display:none;' : 'display:flex;'; ?>"
                            title="<?php echo htmlspecialchars($display_name); ?>">
                            <?php echo $first_initial; ?>
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