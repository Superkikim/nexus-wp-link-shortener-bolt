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
                    console.error('Could not copy text: ', err);
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
        $.ajax({
            url: nexusLinks.ajaxUrl,
            type: 'POST',
            data: {
                action: 'nexus_get_analytics',
                nonce: nexusLinks.nonce,
                date_range: '30'
            },
            success: function(response) {
                if (response.success) {
                    updateDashboardStats(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Analytics loading error:', error);
            }
        });
    }
    
    /**
     * Update dashboard statistics
     */
    function updateDashboardStats(data) {
        $('#total-clicks').text(data.total_clicks || 0);
        $('#unique-clicks').text(data.unique_clicks || 0);
        $('#human-clicks').text(data.human_clicks || 0);
        $('#bot-clicks').text(data.bot_clicks || 0);
        
        updateReferrersList(data.top_referrers);
        updateClicksChart(data.clicks_by_day);
    }
    
    /**
     * Update referrers list
     */
    function updateReferrersList(referrers) {
        const container = $('#referrers-list');
        container.empty();
        
        if (referrers && referrers.length > 0) {
            const list = $('<ul></ul>');
            referrers.forEach(function(referrer) {
                const domain = extractDomain(referrer.referrer);
                const item = $('<li></li>').html(
                    '<span><strong>' + domain + '</strong></span>' +
                    '<span>' + referrer.clicks + ' clicks</span>'
                );
                list.append(item);
            });
            container.append(list);
        } else {
            container.html('<p>No referrer data available.</p>');
        }
    }
    
    /**
     * Update clicks chart (placeholder for Chart.js integration)
     */
    function updateClicksChart(clicksData) {
        // This would integrate with Chart.js or similar library
        console.log('Clicks data:', clicksData);
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
                console.error('Fallback: Could not copy text');
            }
        } catch (err) {
            console.error('Fallback: Could not copy text: ', err);
        }
        
        document.body.removeChild(textArea);
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
    
})(jQuery);