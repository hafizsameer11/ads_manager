/**
 * Ad Network Website - Main JavaScript
 */

(function() {
    'use strict';

    // Mobile Menu Toggle
    const initMobileMenu = () => {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const navList = document.querySelector('.nav-list');
        const authButtons = document.querySelector('.auth-buttons');

        if (toggle && navList) {
            toggle.addEventListener('click', () => {
                toggle.classList.toggle('active');
                navList.classList.toggle('active');
                if (authButtons) {
                    authButtons.classList.toggle('active');
                }
                document.body.classList.toggle('menu-open');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!toggle.contains(e.target) && !navList.contains(e.target) && 
                    (!authButtons || !authButtons.contains(e.target))) {
                    toggle.classList.remove('active');
                    navList.classList.remove('active');
                    if (authButtons) {
                        authButtons.classList.remove('active');
                    }
                    document.body.classList.remove('menu-open');
                }
            });

            // Close menu when clicking a nav link or auth button
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    toggle.classList.remove('active');
                    navList.classList.remove('active');
                    if (authButtons) {
                        authButtons.classList.remove('active');
                    }
                    document.body.classList.remove('menu-open');
                });
            });

            if (authButtons) {
                const authButtonsLinks = authButtons.querySelectorAll('a, button');
                authButtonsLinks.forEach(btn => {
                    btn.addEventListener('click', () => {
                        toggle.classList.remove('active');
                        navList.classList.remove('active');
                        authButtons.classList.remove('active');
                        document.body.classList.remove('menu-open');
                    });
                });
            }
        }
    };

    // Smooth Scroll for Anchor Links
    const initSmoothScroll = () => {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    const headerHeight = document.querySelector('.main-header')?.offsetHeight || 0;
                    const targetPosition = target.offsetTop - headerHeight;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    };

    // Active Navigation Highlight
    const highlightActiveNav = () => {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');

        navLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            if (linkPath === currentPath || (currentPath === '/' && linkPath.includes('home'))) {
                link.classList.add('active');
            }
        });
    };

    // Form Validation (if needed)
    const initFormValidation = () => {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    };

    // Header Scroll Effect
    const initHeaderScroll = () => {
        const header = document.querySelector('.main-header');
        let lastScroll = 0;

        if (header) {
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;

                if (currentScroll > 100) {
                    header.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
                } else {
                    header.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
                }

                lastScroll = currentScroll;
            });
        }
    };

    // Initialize all functions when DOM is ready
    const init = () => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initMobileMenu();
                initSmoothScroll();
                highlightActiveNav();
                initFormValidation();
                initHeaderScroll();
            });
        } else {
            initMobileMenu();
            initSmoothScroll();
            highlightActiveNav();
            initFormValidation();
            initHeaderScroll();
        }
    };

    // Start initialization
    init();
})();




