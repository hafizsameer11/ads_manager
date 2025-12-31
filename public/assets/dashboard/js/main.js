/**
 * Dashboard Main JavaScript
 * Shared functionality for all dashboards
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initSidebarToggle();
        initUserDropdown();
        initMobileMenu();
        initAlertDismiss();
        initNotificationDropdown();
    });

    /**
     * Sidebar Toggle Functionality
     */
    function initSidebarToggle() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.dashboard-sidebar');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

            // Restore sidebar state from localStorage
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            }
        }
    }

    /**
     * User Dropdown Menu
     */
    function initUserDropdown() {
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.querySelector('.user-dropdown');

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });
        }
    }

    /**
     * Mobile Menu Toggle
     */
    function initMobileMenu() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.dashboard-sidebar');

        if (sidebarToggle && sidebar && window.innerWidth <= 768) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (sidebar.classList.contains('mobile-open') && 
                    !sidebar.contains(e.target) && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
            }
        });
    }

    /**
     * Notification Dropdown
     */
    function initNotificationDropdown() {
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.querySelector('.notification-dropdown');
        const notificationList = document.getElementById('notificationList');
        const notificationBadge = document.getElementById('notificationBadge');
        const markAllReadBtn = document.getElementById('markAllReadBtn');

        if (!notificationBtn || !notificationDropdown) {
            return; // Not an admin or notifications not available
        }

        // Toggle dropdown
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
            if (notificationDropdown.classList.contains('active')) {
                loadNotifications();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
        });

        // Mark all as read
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                markAllAsRead();
            });
        }

        // Mark all as read when clicking "View All Notifications" link
        const viewAllLink = document.getElementById('viewAllNotificationsLink');
        if (viewAllLink) {
            viewAllLink.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default navigation first
                const href = this.getAttribute('href');
                
                // Mark all as read before navigating
                fetch(getNotificationRoute('mark-all-read'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear the notification list immediately
                        const notificationList = document.getElementById('notificationList');
                        if (notificationList) {
                            notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                        }
                        
                        // Hide the badge
                        const notificationBadge = document.getElementById('notificationBadge');
                        if (notificationBadge) {
                            notificationBadge.style.display = 'none';
                        }
                        
                        // Close the dropdown
                        notificationDropdown.classList.remove('active');
                        
                        // Navigate to the notifications page
                        window.location.href = href;
                    }
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                    // Still navigate even if API call fails
                    window.location.href = href;
                });
            });
        }

        // Load notifications on page load
        loadNotifications();

        // Poll for new notifications every 30 seconds
        setInterval(function() {
            if (!notificationDropdown.classList.contains('active')) {
                loadNotifications(true); // Silent update
            }
        }, 30000);
    }

    /**
     * Get notification API route based on user role
     */
    function getNotificationRoute(action) {
        const path = window.location.pathname;
        if (path.includes('/dashboard/admin/')) {
            return `/dashboard/admin/notifications/${action}`;
        } else if (path.includes('/dashboard/publisher/')) {
            return `/dashboard/publisher/notifications/${action}`;
        } else if (path.includes('/dashboard/advertiser/')) {
            return `/dashboard/advertiser/notifications/${action}`;
        }
        return `/dashboard/admin/notifications/${action}`; // Default to admin
    }

    /**
     * Load notifications from API
     */
    function loadNotifications(silent = false) {
        const notificationList = document.getElementById('notificationList');
        const notificationBadge = document.getElementById('notificationBadge');

        if (!notificationList) return;

        fetch(getNotificationRoute('recent'))
            .then(response => response.json())
            .then(data => {
                // Update badge
                if (notificationBadge) {
                    if (data.unread_count > 0) {
                        notificationBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        notificationBadge.style.display = 'block';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }

                // Update notification list
                if (data.notifications && data.notifications.length > 0) {
                    notificationList.innerHTML = data.notifications.map(notification => {
                        const timeAgo = getTimeAgo(notification.created_at);
                        const unreadClass = !notification.is_read ? 'unread' : '';
                        return `
                            <div class="notification-item ${unreadClass}" data-id="${notification.id}">
                                <div class="notification-item-title">${escapeHtml(notification.title)}</div>
                                <div class="notification-item-message">${escapeHtml(notification.message)}</div>
                                <div class="notification-item-meta">
                                    <span class="notification-item-time">${timeAgo}</span>
                                    <span class="notification-item-category">${escapeHtml(notification.category)}</span>
                                </div>
                            </div>
                        `;
                    }).join('');

                    // Add click handlers to mark as read
                    notificationList.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const notificationId = this.getAttribute('data-id');
                            if (notificationId && !this.classList.contains('read')) {
                                markAsRead(notificationId, this);
                            }
                        });
                    });
                } else {
                    notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                if (!silent) {
                    notificationList.innerHTML = '<div class="notification-empty">Error loading notifications</div>';
                }
            });
    }

    /**
     * Mark notification as read
     */
    function markAsRead(notificationId, element) {
        fetch(getNotificationRoute(`${notificationId}/read`), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.classList.remove('unread');
                loadNotifications(true); // Reload silently
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    /**
     * Mark all notifications as read
     */
    function markAllAsRead() {
        fetch(getNotificationRoute('mark-all-read'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications(true); // Reload silently
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
        });
    }

    /**
     * Get time ago string
     */
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
        if (diffInSeconds < 604800) return Math.floor(diffInSeconds / 86400) + 'd ago';
        return date.toLocaleDateString();
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Table Row Click Handler (optional)
     */
    const tableRows = document.querySelectorAll('.table tbody tr[data-href]');
    tableRows.forEach(function(row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    /**
     * Confirm Delete Actions
     */
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    /**
     * Initialize Alert Dismiss Functionality
     */
    function initAlertDismiss() {
        // Handle dismiss buttons
        document.querySelectorAll('.alert .close, .alert [data-dismiss="alert"]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const alert = this.closest('.alert');
                if (alert) {
                    dismissAlert(alert);
                }
            });
        });
    }

    /**
     * Dismiss Alert with Animation
     */
    function dismissAlert(alert) {
        alert.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        
        setTimeout(function() {
            alert.remove();
        }, 300);
    }

    /**
     * Auto-hide Flash Messages (after 5 seconds)
     */
    const flashMessages = document.querySelectorAll('.alert, [role="alert"]');
    flashMessages.forEach(function(message) {
        // Only auto-hide if it doesn't have a close button (user can dismiss manually)
        const hasCloseButton = message.querySelector('.close, [data-dismiss="alert"]');
        if (!hasCloseButton) {
            setTimeout(function() {
                dismissAlert(message);
            }, 5000);
        }
    });
})();




