<?php
/**
 * Reusable Header Component
 * Include this file in all pages after authentication
 */

// Ensure session is started (only once)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get session variables
$user_id = $_SESSION['user_id'] ?? '';
$user_name_th = $_SESSION['full_name_th'] ?? '';
$user_name_en = $_SESSION['full_name_en'] ?? '';
$user_role = $_SESSION['role'] ?? '';
$theme_mode = $_SESSION['theme_mode'] ?? 'light';
$language = $_SESSION['language'] ?? 'th';
$profile_pic = $_SESSION['profile_pic'] ?? '';

// Display name based on language
$display_name = ($language === 'en') ? $user_name_en : $user_name_th;

// Theme classes
$is_dark = ($theme_mode === 'dark');
$bg_class = $is_dark ? 'bg-gray-900' : 'bg-gray-50';
$text_class = $is_dark ? 'text-gray-100' : 'text-gray-800';
$card_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
$header_bg = $is_dark ? 'bg-gray-800' : 'bg-white';
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
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Smooth scroll */
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
    </style>
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body class="<?php echo $bg_class; ?> theme-transition">
    
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

                <!-- Notifications (Optional) -->
                <button class="p-2 rounded-lg <?php echo $is_dark ? 'hover:bg-gray-700' : 'hover:bg-gray-200'; ?> transition relative">
                    <svg class="w-6 h-6 <?php echo $text_class; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
            </div>
        </div>
    </header>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function changeLanguage(lang) {
            fetch('<?php echo BASE_PATH; ?>/api/change_language.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ language: lang })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to change language');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error');
            });
        }

        function toggleTheme() {
            const currentMode = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            const newMode = currentMode === 'dark' ? 'light' : 'dark';
            
            fetch('<?php echo BASE_PATH; ?>/api/change_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mode: newMode })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to change theme');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            if (!sidebar.contains(event.target) && 
                !event.target.closest('button[onclick="toggleMobileMenu()"]') &&
                !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    </script>