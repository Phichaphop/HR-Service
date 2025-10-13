<?php
/**
 * Reusable Footer Component
 * Include at the end of page content, before </body>
 */
?>
    </div> <!-- End of main content wrapper -->

    <!-- Footer -->
    <footer class="<?php echo $is_dark ? 'bg-gray-800 text-gray-400' : 'bg-white text-gray-600'; ?> py-8 mt-12 lg:ml-64 border-t <?php echo $is_dark ? 'border-gray-700' : 'border-gray-200'; ?> shadow-inner">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-6">
                <!-- Company Info -->
                <div>
                    <h3 class="font-bold <?php echo $is_dark ? 'text-white' : 'text-gray-800'; ?> mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <?php echo APP_NAME; ?>
                    </h3>
                    <p class="text-sm mb-2">
                        Enterprise HR Management System
                    </p>
                    <p class="text-xs">
                        Version <?php echo APP_VERSION; ?> | Build 2025.01
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="font-bold <?php echo $is_dark ? 'text-white' : 'text-gray-800'; ?> mb-3">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="<?php echo BASE_PATH; ?>/index.php" class="hover:text-blue-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_PATH; ?>/views/employee/my_requests.php" class="hover:text-blue-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                My Requests
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_PATH; ?>/views/settings.php" class="hover:text-blue-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Support & Resources -->
                <div>
                    <h3 class="font-bold <?php echo $is_dark ? 'text-white' : 'text-gray-800'; ?> mb-3">Support & Resources</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="#" class="hover:text-blue-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Help Center
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                Documentation
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Contact Support
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Privacy Policy
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t <?php echo $is_dark ? 'border-gray-700' : 'border-gray-200'; ?> pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <!-- Copyright -->
                    <div class="mb-4 md:mb-0 text-center md:text-left">
                        <p class="text-sm flex items-center justify-center md:justify-start">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"></path>
                            </svg>
                            © <?php echo date('Y'); ?> <strong class="mx-1"><?php echo APP_NAME; ?></strong>
                        </p>
                        <p class="text-xs mt-1">
                            All rights reserved. Powered by <span class="text-blue-600">Anthropic Claude</span>
                        </p>
                    </div>
                    
                    <!-- Social Links & Info -->
                    <div class="flex items-center space-x-4 text-sm">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center <?php echo $is_dark ? 'text-green-400' : 'text-green-600'; ?>">
                                <span class="relative flex h-3 w-3 mr-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                <span class="text-xs font-medium">System Online</span>
                            </div>
                        </div>
                        
                        <span class="hidden md:inline text-gray-400">|</span>
                        
                        <div class="flex items-center space-x-3">
                            <a href="#" class="<?php echo $is_dark ? 'hover:text-blue-400' : 'hover:text-blue-600'; ?> transition" title="Terms">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </a>
                            <a href="#" class="<?php echo $is_dark ? 'hover:text-blue-400' : 'hover:text-blue-600'; ?> transition" title="Help">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </a>
                            <a href="#" class="<?php echo $is_dark ? 'hover:text-blue-400' : 'hover:text-blue-600'; ?> transition" title="Feedback">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tech Stack Badge -->
                <div class="mt-4 text-center">
                    <p class="text-xs <?php echo $is_dark ? 'text-gray-500' : 'text-gray-400'; ?>">
                        Built with 
                        <span class="inline-flex items-center mx-1">
                            <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                            using
                        </span>
                        <span class="font-mono text-blue-600 dark:text-blue-400">PHP</span>, 
                        <span class="font-mono text-blue-600 dark:text-blue-400">MySQL</span>, 
                        <span class="font-mono text-blue-600 dark:text-blue-400">Tailwind CSS</span>
                        <?php if ($user_role === 'admin'): ?>
                        <span class="ml-2 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded">
                            Admin View
                        </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" 
            class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 opacity-0 invisible z-40"
            onclick="scrollToTop()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <!-- Additional Scripts -->
    <script>
        // Back to Top Button
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.classList.remove('opacity-0', 'invisible');
                backToTop.classList.add('opacity-100', 'visible');
            } else {
                backToTop.classList.add('opacity-0', 'invisible');
                backToTop.classList.remove('opacity-100', 'visible');
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Add fade-in animation to footer on load
        document.addEventListener('DOMContentLoaded', function() {
            const footer = document.querySelector('footer');
            footer.style.opacity = '0';
            footer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                footer.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                footer.style.opacity = '1';
                footer.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>

    <?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>