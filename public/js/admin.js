// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        body.classList.add(savedTheme);
        updateThemeIcon(savedTheme);
    }
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', '');
                updateThemeIcon('');
            } else {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark-mode');
                updateThemeIcon('dark-mode');
            }
        });
    }
    
    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        if (theme === 'dark-mode') {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        } else {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    }
    
    // Set active menu item based on current route
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace(/\/admin\//, ''))) {
            link.closest('.nav-item').classList.add('active');
        }
    });
    
    // Submenu Toggle
    const submenuItems = document.querySelectorAll('.nav-item.has-submenu');
    submenuItems.forEach(item => {
        const link = item.querySelector('.nav-link');
        link.addEventListener('click', function(e) {
            e.preventDefault();
            item.classList.toggle('active');
        });
    });
    
    // Table Sort Icons
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isAsc = icon.classList.contains('fa-sort-up');
            
            // Reset all icons
            sortableHeaders.forEach(h => {
                const i = h.querySelector('i');
                i.className = 'fas fa-sort ms-1';
            });
            
            // Update clicked header icon
            if (isAsc) {
                icon.className = 'fas fa-sort-down ms-1';
            } else {
                icon.className = 'fas fa-sort-up ms-1';
            }
        });
    });
    
    // Notification Click
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // Add notification dropdown logic here
            console.log('Notifications clicked');
        });
    }
    
    // User Profile Click
    const userProfile = document.querySelector('.user-profile');
    if (userProfile) {
        userProfile.addEventListener('click', function() {
            // Add user menu dropdown logic here
            console.log('User profile clicked');
        });
    }
    
    // Mobile Menu Toggle (for responsive)
    const createMobileMenuToggle = () => {
        const header = document.querySelector('.admin-header');
        if (window.innerWidth <= 768 && !document.querySelector('.mobile-menu-toggle')) {
            const toggle = document.createElement('button');
            toggle.className = 'mobile-menu-toggle btn btn-sm';
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
            toggle.style.marginRight = '15px';
            toggle.addEventListener('click', function() {
                document.querySelector('.admin-sidebar').classList.toggle('active');
            });
            header.querySelector('.header-left').prepend(toggle);
        }
    };
    
    createMobileMenuToggle();
    window.addEventListener('resize', createMobileMenuToggle);
});

// CSRF Token Setup for AJAX
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        }
    });
}
