// Admin Panel JavaScript

window.AdminDT = {
    language: {
        processing: 'Đang xử lý...',
        search: 'Tìm kiếm:',
        lengthMenu: 'Hiển thị _MENU_ bản ghi',
        info: 'Hiển thị _START_ đến _END_ trong tổng số _TOTAL_ bản ghi',
        infoEmpty: 'Hiển thị 0 đến 0 trong tổng số 0 bản ghi',
        infoFiltered: '(lọc từ _MAX_ tổng số bản ghi)',
        paginate: {
            first: 'Đầu',
            last: 'Cuối',
            next: 'Sau',
            previous: 'Trước',
        },
        emptyTable: 'Không có dữ liệu',
        zeroRecords: 'Không tìm thấy kết quả',
    },
    dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
    columnDefs: function (colCount, options) {
        options = options || {};
        var actionIndex = options.actionIndex !== undefined ? options.actionIndex : colCount - 1;
        var primary = options.primary || [1, 2];
        var defs = [];

        for (var i = 0; i < colCount; i++) {
            var priority = 10000;
            if (i === actionIndex) {
                priority = 1;
            } else if (primary.indexOf(i) >= 0) {
                priority = primary[0] === i ? 2 : 3;
            } else if (i === 0 && options.checkbox !== false) {
                priority = 5;
            }
            defs.push({ targets: i, responsivePriority: priority });
        }

        return defs;
    },
};

if (typeof jQuery !== 'undefined' && jQuery.fn.dataTable) {
    jQuery.extend(true, jQuery.fn.dataTable.defaults, {
        responsive: true,
        autoWidth: false,
    });
}

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
    
    // User Profile Click (skip Bootstrap dropdown profiles)
    const userProfile = document.querySelector('.user-profile:not(.dropdown)');
    if (userProfile) {
        userProfile.addEventListener('click', function() {
            console.log('User profile clicked');
        });
    }
    
    // Mobile menu + overlay
    const sidebar = document.querySelector('.admin-sidebar');
    const header = document.querySelector('.admin-header');
    const adminWrapper = document.querySelector('.admin-wrapper');
    let overlay = document.querySelector('.admin-sidebar-overlay');

    if (overlay && overlay.parentElement === document.body && adminWrapper) {
        adminWrapper.insertBefore(overlay, adminWrapper.firstChild);
    }

    if (!overlay && adminWrapper) {
        overlay = document.createElement('div');
        overlay.className = 'admin-sidebar-overlay';
        overlay.setAttribute('aria-hidden', 'true');
        adminWrapper.insertBefore(overlay, adminWrapper.firstChild);
    }

    function closeSidebar() {
        sidebar?.classList.remove('active');
        overlay?.classList.remove('is-visible');
        document.body.classList.remove('admin-sidebar-open');
        document.getElementById('mobileMenuToggle')?.setAttribute('aria-expanded', 'false');
    }

    function openSidebar() {
        sidebar?.classList.add('active');
        overlay?.classList.add('is-visible');
        document.body.classList.add('admin-sidebar-open');
    }

    overlay?.addEventListener('click', closeSidebar);

    sidebar?.querySelectorAll('.nav-link[href]').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    const menuToggle = document.getElementById('mobileMenuToggle');

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            if (sidebar?.classList.contains('active')) {
                closeSidebar();
                menuToggle.setAttribute('aria-expanded', 'false');
            } else {
                openSidebar();
                menuToggle.setAttribute('aria-expanded', 'true');
            }
        });
    }

    const syncMobileMenu = () => {
        const isMobile = window.innerWidth <= 768;

        if (!isMobile) {
            closeSidebar();
            menuToggle?.setAttribute('aria-expanded', 'false');
        }
    };

    syncMobileMenu();
    window.addEventListener('resize', syncMobileMenu);
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
