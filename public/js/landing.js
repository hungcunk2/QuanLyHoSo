// Landing Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // Header scroll effect
    const header = document.querySelector('.main-header');
    let lastScroll = 0;

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                const headerHeight = header.offsetHeight;
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Navbar active link highlighting
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    function highlightNavLink() {
        let current = '';
        const scrollPosition = window.pageYOffset + 150;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            const sectionId = section.getAttribute('id');

            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                current = sectionId;
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', highlightNavLink);

    // Counter animation for stats
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = Math.floor(target) + '+';
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(start) + '+';
            }
        }, 16);
    }

    // Intersection Observer for stats animation
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = entry.target.querySelectorAll('.stat-number');
                statNumbers.forEach(stat => {
                    const target = parseInt(stat.textContent);
                    if (!isNaN(target)) {
                        stat.textContent = '0+';
                        animateCounter(stat, target);
                    }
                });
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    const statsSection = document.querySelector('.stats-row');
    if (statsSection) {
        statsObserver.observe(statsSection.closest('.about-section'));
    }

    // News card hover effect enhancement
    const newsCards = document.querySelectorAll('.news-card');
    newsCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Value card animation on scroll
    const valueCards = document.querySelectorAll('.value-card');
    const valueObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                valueObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    valueCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        valueObserver.observe(card);
    });

    // Mobile menu close on link click
    const navLinksMobile = document.querySelectorAll('.navbar-nav .nav-link');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navLinksMobile.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }
        });
    });

    // Parallax effect for hero section - removed to prevent overlapping issues
    // const heroSection = document.querySelector('.hero-section');
    // if (heroSection) {
    //     window.addEventListener('scroll', () => {
    //         const scrolled = window.pageYOffset;
    //         const rate = scrolled * 0.5;
    //         if (scrolled < heroSection.offsetHeight) {
    //             heroSection.style.transform = `translateY(${rate}px)`;
    //         }
    //     });
    // }

    // Add loading animation
    window.addEventListener('load', () => {
        document.body.classList.add('loaded');
    });

    // Form validation (if forms are added later)
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (form.id !== 'loginForm') {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        }
    });

    // Login form handling
    const loginForm = document.getElementById('loginForm');
    const loginModal = document.getElementById('loginModal');
    const loginError = document.getElementById('loginError');
    const loginErrorText = document.getElementById('loginErrorText');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Reset error display
            loginError.classList.add('d-none');
            loginErrorText.textContent = '';
            
            // Validate form
            if (!loginForm.checkValidity()) {
                loginForm.classList.add('was-validated');
                return;
            }

            // Disable submit button
            loginSubmitBtn.disabled = true;
            loginSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đăng nhập...';

            try {
                const formData = new FormData(loginForm);
                const loginUrl = loginForm.getAttribute('data-login-url');
                const response = await fetch(loginUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    // Login successful
                    const dashboardUrl = loginForm.getAttribute('data-dashboard-url');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = dashboardUrl;
                    }
                } else {
                    // Login failed
                    let errorMessage = 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.';
                    
                    if (data.errors) {
                        if (data.errors.email) {
                            errorMessage = data.errors.email[0];
                        } else if (data.message) {
                            errorMessage = data.message;
                        }
                    } else if (data.message) {
                        errorMessage = data.message;
                    }
                    
                    loginErrorText.textContent = errorMessage;
                    loginError.classList.remove('d-none');
                    
                    // Re-enable submit button
                    loginSubmitBtn.disabled = false;
                    loginSubmitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Đăng nhập';
                }
            } catch (error) {
                console.error('Login error:', error);
                loginErrorText.textContent = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
                loginError.classList.remove('d-none');
                
                // Re-enable submit button
                loginSubmitBtn.disabled = false;
                loginSubmitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Đăng nhập';
            }
        });

        // Reset form when modal is closed
        if (loginModal) {
            loginModal.addEventListener('hidden.bs.modal', function() {
                loginForm.reset();
                loginForm.classList.remove('was-validated');
                loginError.classList.add('d-none');
                loginErrorText.textContent = '';
                loginSubmitBtn.disabled = false;
                loginSubmitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Đăng nhập';
            });
        }
    }
});
