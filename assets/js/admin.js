/**
 * Nexus WP Link Shortener Admin JavaScript
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Initialize dashboard if on dashboard page
        if ($('#nexus-dashboard').length) {
            initDashboard();
        }
        
        // Copy to clipboard functionality
        $(document).on('click', '.nexus-copy-button', function(e) {
            e.preventDefault();
            
            const url = $(this).data('url');
            const button = $(this);
            const originalText = button.text();
            
            // Use modern clipboard API
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    button.text(nexusLinks.strings.copied);
                    setTimeout(function() {
                        button.text(originalText);
                    }, 2000);
                }).catch(function(err) {
                    console.debug('[Nexus Links] Could not copy text: ', err);
                    fallbackCopyToClipboard(url, button, originalText);
                });
            } else {
                fallbackCopyToClipboard(url, button, originalText);
            }
        });
        
        // Data management buttons
        $('#cleanup-analytics').on('click', function() {
            if (confirm('Are you sure you want to clean up old analytics data? This action cannot be undone.')) {
                cleanupAnalytics();
            }
        });
        
        $('#export-data').on('click', function() {
            exportData();
        });
    });
    
    /**
     * Initialize dashboard functionality
     */
    function initDashboard() {
        loadAnalyticsData();
        
        // Refresh data every 30 seconds
        setInterval(loadAnalyticsData, 30000);
    }
    
    /**
     * Load analytics data via AJAX
     */
    function loadAnalyticsData() {
        const formData = new FormData();
        formData.append('action', 'nexus_get_analytics');
        formData.append('nonce', nexusLinks.nonce);
        formData.append('date_range', '30');
        
        fetch(nexusLinks.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.data);
            } else {
                console.debug('[Nexus Analytics] Loading failed:', data);
            }
        })
        .catch(error => {
            console.debug('[Nexus Analytics] Loading error:', error);
        });
    }
    
    /**
     * Update dashboard statistics
     */
    function updateDashboardStats(data) {
        // Update stat numbers
        const totalClicks = document.getElementById('total-clicks');
        const uniqueClicks = document.getElementById('unique-clicks');
        const humanClicks = document.getElementById('human-clicks');
        const botClicks = document.getElementById('bot-clicks');
        
        if (totalClicks) totalClicks.textContent = data.total_clicks || 0;
        if (uniqueClicks) uniqueClicks.textContent = data.unique_clicks || 0;
        if (humanClicks) humanClicks.textContent = data.human_clicks || 0;
        if (botClicks) botClicks.textContent = data.bot_clicks || 0;
        
        updateReferrersList(data.top_referrers);
        updateClicksChart(data.clicks_by_day);
    }
    
    /**
     * Update referrers list
     */
    function updateReferrersList(referrers) {
        const container = document.getElementById('referrers-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (referrers && referrers.length > 0) {
            const list = document.createElement('ul');
            referrers.forEach(function(referrer) {
                const domain = extractDomain(referrer.referrer);
                const item = document.createElement('li');
                item.innerHTML = 
                    '<span><strong>' + domain + '</strong></span>' +
                    '<span>' + referrer.clicks + ' clicks</span>';
                list.appendChild(item);
            });
            container.appendChild(list);
        } else {
            container.innerHTML = '<p>No referrer data available.</p>';
        }
    }
    
    /**
     * Update clicks chart (placeholder for Chart.js integration)
     */
    function updateClicksChart(clicksData) {
        // This would integrate with Chart.js or similar library
        console.debug('[Nexus Analytics] Clicks data:', clicksData);
    }
    
    /**
     * Extract domain from URL
     */
    function extractDomain(url) {
        try {
            const domain = new URL(url).hostname;
            return domain.replace('www.', '');
        } catch (e) {
            return url;
        }
    }
    
    /**
     * Clean up old analytics data
     */
    function cleanupAnalytics() {
        const formData = new FormData();
        formData.append('action', 'nexus_cleanup_analytics');
        formData.append('nonce', nexusLinks.nonce);
        
        fetch(nexusLinks.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(nexusLinks.strings.cleanup_success || 'Analytics data cleanup completed successfully.');
            } else {
                alert(nexusLinks.strings.error || 'Error occurred during cleanup.');
            }
        })
        .catch(error => {
            console.debug('[Nexus Analytics] Cleanup error:', error);
            alert(nexusLinks.strings.error || 'Error occurred during cleanup.');
        });
    }
    
    /**
     * Export data
     */
    function exportData() {
        window.location.href = nexusLinks.ajaxUrl + '?action=nexus_export_data&nonce=' + nexusLinks.nonce;
    }
    
    /**
     * Fallback copy to clipboard function
     */
    function fallbackCopyToClipboard(text, button, originalText) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                button.text(nexusLinks.strings.copied);
                setTimeout(function() {
                    button.text(originalText);
                }, 2000);
            } else {
                console.debug('[Nexus Links] Fallback: Could not copy text');
            }
        } catch (err) {
            console.debug('[Nexus Links] Fallback: Could not copy text: ', err);
        }
        
        document.body.removeChild(textArea);
    }
    
})(jQuery);