/**
 * Ads Network SDK
 * Version: 1.0.0
 * 
 * This script handles ad serving, impression tracking, and click tracking
 * for publishers using the Ads Network platform.
 */

(function(window, document) {
    'use strict';

    // Configuration
    var CONFIG = {
        apiUrl: window.ADS_NETWORK_API_URL || '{{API_URL}}',
        version: '1.0.0',
        timeout: 10000
    };

    // Utility functions
    var utils = {
        /**
         * Make HTTP request
         */
        request: function(url, options) {
            options = options || {};
            var method = options.method || 'GET';
            var data = options.data || null;
            var timeout = options.timeout || CONFIG.timeout;
            
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open(method, url, true);
                xhr.timeout = timeout;
                
                if (data && method === 'POST') {
                    xhr.setRequestHeader('Content-Type', 'application/json');
                }
                
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            resolve(response);
                        } catch (e) {
                            reject(new Error('Invalid JSON response'));
                        }
                    } else {
                        reject(new Error('Request failed with status: ' + xhr.status));
                    }
                };
                
                xhr.onerror = function() {
                    reject(new Error('Network error'));
                };
                
                xhr.ontimeout = function() {
                    reject(new Error('Request timeout'));
                };
                
                if (data) {
                    xhr.send(JSON.stringify(data));
                } else {
                    xhr.send();
                }
            });
        },

        /**
         * Get visitor information
         */
        getVisitorInfo: function() {
            return {
                referrer: document.referrer || '',
                userAgent: navigator.userAgent || '',
                language: navigator.language || '',
                screenWidth: window.screen ? window.screen.width : 0,
                screenHeight: window.screen ? window.screen.height : 0,
                timestamp: new Date().toISOString()
            };
        },

        /**
         * Create unique impression ID
         */
        generateId: function() {
            return 'imp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        /**
         * Check if element is in viewport
         */
        isInViewport: function(element) {
            var rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    };

    /**
     * Ad Manager Class
     */
    function AdManager(unitCode, container, options) {
        this.unitCode = unitCode;
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.options = options || {};
        this.impressionId = null;
        this.campaignId = null;
        this.adUnitId = null;
        this.adData = null;
        this.impressionTracked = false;
        
        if (!this.container) {
            console.error('Ads Network: Container element not found');
            return;
        }
        
        this.init();
    }

    AdManager.prototype = {
        /**
         * Initialize ad
         */
        init: function() {
            var self = this;
            
            // Load ad
            this.loadAd().then(function(adData) {
                if (adData && adData.success) {
                    self.renderAd(adData.data);
                } else {
                    self.showNoAd();
                }
            }).catch(function(error) {
                console.error('Ads Network: Failed to load ad', error);
                self.showNoAd();
            });
        },

        /**
         * Load ad from API
         */
        loadAd: function() {
            var url = CONFIG.apiUrl + '/api/ad/' + this.unitCode;
            var visitorInfo = utils.getVisitorInfo();
            
            // Add query parameters
            var params = new URLSearchParams();
            params.append('ref', visitorInfo.referrer);
            params.append('ua', encodeURIComponent(visitorInfo.userAgent));
            params.append('lang', visitorInfo.language);
            params.append('sw', visitorInfo.screenWidth);
            params.append('sh', visitorInfo.screenHeight);
            
            if (this.options.type) {
                params.append('type', this.options.type);
            }
            
            url += '?' + params.toString();
            
            return utils.request(url);
        },

        /**
         * Render ad
         */
        renderAd: function(adData) {
            this.adData = adData;
            this.campaignId = adData.campaign_id;
            this.adUnitId = adData.ad_unit_id;
            this.impressionId = utils.generateId();
            
            var self = this;
            
            // Create ad element
            var adElement = document.createElement('div');
            adElement.className = 'ads-network-ad';
            adElement.setAttribute('data-unit-code', this.unitCode);
            adElement.setAttribute('data-campaign-id', this.campaignId);
            adElement.setAttribute('data-ad-unit-id', this.adUnitId);
            
            // Render based on ad type
            if (adData.type === 'banner') {
                this.renderBanner(adElement, adData);
            } else if (adData.type === 'popup') {
                this.renderPopup(adData);
                return; // Popup doesn't need container
            } else if (adData.type === 'popunder') {
                this.renderPopunder(adData);
                return; // Popunder doesn't need container
            }
            
            // Append to container
            this.container.innerHTML = '';
            this.container.appendChild(adElement);
            
            // Track impression when ad is visible
            this.trackImpressionWhenVisible(adElement);
        },

        /**
         * Render banner ad
         */
        renderBanner: function(container, adData) {
            var link = document.createElement('a');
            link.href = '#';
            link.className = 'ads-network-link';
            link.target = adData.target_url ? '_blank' : '_self';
            link.rel = 'nofollow';
            
            if (adData.target_url) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.trackClick(adData.target_url);
                });
            }
            
            if (adData.image_url) {
                var img = document.createElement('img');
                img.src = adData.image_url;
                img.alt = adData.title || 'Advertisement';
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.display = 'block';
                
                if (adData.width) {
                    img.style.width = adData.width + 'px';
                }
                if (adData.height) {
                    img.style.height = adData.height + 'px';
                }
                
                link.appendChild(img);
            } else if (adData.html) {
                link.innerHTML = adData.html;
            } else if (adData.text) {
                link.textContent = adData.text;
            }
            
            container.appendChild(link);
            
            var self = this;
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (adData.target_url) {
                    self.trackClick(adData.target_url);
                }
            });
        },

        /**
         * Render popup ad
         */
        renderPopup: function(adData) {
            var self = this;
            var frequency = this.options.frequency || 30; // Default 30 seconds
            var lastPopup = localStorage.getItem('ads_network_popup_' + this.unitCode);
            var now = Date.now();
            
            // Check frequency limit
            if (lastPopup && (now - parseInt(lastPopup)) < (frequency * 1000)) {
                return; // Too soon, skip popup
            }
            
            // Track popup shown
            localStorage.setItem('ads_network_popup_' + this.unitCode, now.toString());
            
            // Create popup window
            var popup = window.open(
                adData.target_url || '#',
                'ads_network_popup',
                'width=' + (adData.width || 800) + ',height=' + (adData.height || 600) + ',scrollbars=yes,resizable=yes'
            );
            
            if (popup) {
                // Track impression
                this.trackImpression();
                
                // Track click
                setTimeout(function() {
                    self.trackClick(adData.target_url);
                }, 100);
            }
        },

        /**
         * Render popunder ad
         */
        renderPopunder: function(adData) {
            var self = this;
            var frequency = this.options.frequency || 30;
            var lastPopunder = localStorage.getItem('ads_network_popunder_' + this.unitCode);
            var now = Date.now();
            
            // Check frequency limit
            if (lastPopunder && (now - parseInt(lastPopunder)) < (frequency * 1000)) {
                return;
            }
            
            localStorage.setItem('ads_network_popunder_' + this.unitCode, now.toString());
            
            // Create popunder (open in background)
            var popunder = window.open(
                adData.target_url || '#',
                'ads_network_popunder',
                'width=' + (adData.width || 800) + ',height=' + (adData.height || 600)
            );
            
            if (popunder) {
                // Blur popunder and focus main window
                popunder.blur();
                window.focus();
                
                // Track impression
                this.trackImpression();
                
                // Track click
                setTimeout(function() {
                    self.trackClick(adData.target_url);
                }, 100);
            }
        },

        /**
         * Track impression when ad is visible
         */
        trackImpressionWhenVisible: function(element) {
            var self = this;
            
            // Use Intersection Observer if available
            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting && !self.impressionTracked) {
                            self.trackImpression();
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.5 // 50% visible
                });
                
                observer.observe(element);
            } else {
                // Fallback: track after 1 second
                setTimeout(function() {
                    if (utils.isInViewport(element) && !self.impressionTracked) {
                        self.trackImpression();
                    }
                }, 1000);
            }
        },

        /**
         * Track impression
         */
        trackImpression: function() {
            if (this.impressionTracked || !this.campaignId || !this.adUnitId) {
                return;
            }
            
            this.impressionTracked = true;
            
            var url = CONFIG.apiUrl + '/api/ad/impression';
            var data = {
                campaign_id: this.campaignId,
                ad_unit_id: this.adUnitId,
                impression_id: this.impressionId,
                visitor_info: utils.getVisitorInfo()
            };
            
            // Send asynchronously (don't wait for response)
            utils.request(url, {
                method: 'POST',
                data: data
            }).catch(function(error) {
                console.error('Ads Network: Failed to track impression', error);
            });
        },

        /**
         * Track click
         */
        trackClick: function(targetUrl) {
            if (!this.campaignId || !this.adUnitId) {
                return;
            }
            
            var url = CONFIG.apiUrl + '/api/ad/click';
            var data = {
                campaign_id: this.campaignId,
                ad_unit_id: this.adUnitId,
                impression_id: this.impressionId,
                visitor_info: utils.getVisitorInfo()
            };
            
            // Track click
            utils.request(url, {
                method: 'POST',
                data: data
            }).then(function(response) {
                if (response.success && targetUrl) {
                    // Redirect to target URL
                    window.open(targetUrl, '_blank', 'noopener,noreferrer');
                }
            }).catch(function(error) {
                console.error('Ads Network: Failed to track click', error);
                // Still redirect even if tracking fails
                if (targetUrl) {
                    window.open(targetUrl, '_blank', 'noopener,noreferrer');
                }
            });
        },

        /**
         * Show no ad message
         */
        showNoAd: function() {
            if (this.options.hideOnNoAd !== false) {
                this.container.style.display = 'none';
            } else {
                this.container.innerHTML = '<div class="ads-network-no-ad" style="display:none;"></div>';
            }
        }
    };

    /**
     * Initialize ad by unit code
     */
    function initAd(unitCode, container, options) {
        return new AdManager(unitCode, container, options);
    }

    /**
     * Auto-initialize ads on page load
     */
    function autoInit() {
        // Find all elements with data-ads-network attribute
        var adElements = document.querySelectorAll('[data-ads-network]');
        
        adElements.forEach(function(element) {
            var unitCode = element.getAttribute('data-ads-network');
            var type = element.getAttribute('data-ads-type') || 'banner';
            var frequency = element.getAttribute('data-ads-frequency');
            
            var options = {
                type: type
            };
            
            if (frequency) {
                options.frequency = parseInt(frequency);
            }
            
            new AdManager(unitCode, element, options);
        });
    }

    // Expose API
    window.AdsNetwork = {
        init: initAd,
        version: CONFIG.version
    };

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }

})(window, document);

