<?php
/**
 * Reusable Header Component (Improved)
 * ลดความซ้ำซ้อนโดยใช้ get_theme_vars()
 */

// ดึงข้อมูล theme และ user แบบรวม
extract(get_theme_vars());

// Display name ตาม language
$display_name = get_display_name();
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>" class="<?php echo $is_dark ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'HR Service'; ?> - <?php echo __('app_title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe',
                            300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6',
                            600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .theme-transition { transition: all 0.3s ease; }
        html { scroll-behavior: smooth; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
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
    </style>
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body class="<?php echo $bg_class; ?> theme-transition" data-base-path="<?php echo BASE_PATH; ?>">

    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleMobileMenu()"></div>

    <!-- Top Bar (Mobile & Desktop) -->
    <header class="<?php echo $header_bg; ?> shadow-sm sticky top-0 z-30 theme-transition lg:ml-64">
        <div class="flex items-center justify-between px-4 py-4">
            <div class="flex items-center">
                <button onclick="toggleMobileMenu()" class="lg:hidden mr-4 <?php echo $text_class; ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-xl font-semibold <?php echo $text_class; ?>">
                    <?php echo $page_title ?? 'Dashboard'; ?>
                </h2>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                <select onchange="changeLanguage(this.value)"
                    class="px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 <?php echo $is_dark ? 'bg-gray-700 text-white border-gray-600' : 'bg-white border-gray-300'; ?>">
                    <option value="th" <?php echo $language === 'th' ? 'selected' : ''; ?>>🇹🇭 TH</option>
                    <option value="en" <?php echo $language === 'en' ? 'selected' : ''; ?>>🇬🇧 EN</option>
                    <option value="my" <?php echo $language === 'my' ? 'selected' : ''; ?>>🇲🇲 MY</option>
                </select>

                <!-- Theme Toggle -->
                <button onclick="toggleTheme()"
                    class="p-2 rounded-lg <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-200'; ?> transition"
                    title="Toggle Dark Mode">
                    <?php if ($is_dark): ?>
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </header>

    <!-- Load Common JavaScript -->
    <script src="<?php echo BASE_PATH; ?>/includes/common.js"></script>