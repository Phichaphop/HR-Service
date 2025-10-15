/**
 * Common JavaScript Functions
 * ใช้ร่วมกันทั้งระบบ
 */

// Toggle Mobile Menu
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
}

// Change Language Function
function changeLanguage(lang) {
    const selectElement = event.target;
    selectElement.disabled = true;

    // ดึง BASE_PATH จาก data attribute ของ body
    const basePath = document.body.dataset.basePath || '';

    fetch(`${basePath}/api/change_language.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language: lang })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to change language: ' + (data.message || 'Unknown error'));
            selectElement.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to change language. Please try again.');
        selectElement.disabled = false;
    });
}

// Toggle Theme Function
function toggleTheme() {
    const currentMode = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    const newMode = currentMode === 'dark' ? 'light' : 'dark';
    const themeButton = event.currentTarget;
    
    themeButton.disabled = true;
    themeButton.style.opacity = '0.5';

    const basePath = document.body.dataset.basePath || '';

    fetch(`${basePath}/api/change_theme.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: newMode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to change theme: ' + (data.message || 'Unknown error'));
            themeButton.disabled = false;
            themeButton.style.opacity = '1';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to change theme. Please try again.');
        themeButton.disabled = false;
        themeButton.style.opacity = '1';
    });
}

// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50
        ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
    toast.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
            </svg>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Fade out animation
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Keyboard Shortcuts (Global)
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
    }
});

// Show success message from URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        showToast(urlParams.get('message') || 'Operation completed successfully', 'success');
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    if (urlParams.get('error')) {
        showToast(urlParams.get('error'), 'error');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Back to Top functionality (if button exists)
window.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.remove('opacity-0', 'invisible');
                backToTopBtn.classList.add('opacity-100', 'visible');
            } else {
                backToTopBtn.classList.add('opacity-0', 'invisible');
                backToTopBtn.classList.remove('opacity-100', 'visible');
            }
        });
    }
});

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}