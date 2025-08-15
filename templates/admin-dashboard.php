<div class="wrap">
    <h1><?php _e('Link Shortener Dashboard', 'nexus-wp-link-shortener'); ?></h1>
    
    <div id="nexus-dashboard">
        <div class="nexus-stats-grid">
            <div class="nexus-stat-card">
                <h3><?php _e('Total Clicks', 'nexus-wp-link-shortener'); ?></h3>
                <div class="stat-number" id="total-clicks">0</div>
            </div>
            
            <div class="nexus-stat-card">
                <h3><?php _e('Unique Visitors', 'nexus-wp-link-shortener'); ?></h3>
                <div class="stat-number" id="unique-clicks">0</div>
            </div>
            
            <div class="nexus-stat-card">
                <h3><?php _e('Human Clicks', 'nexus-wp-link-shortener'); ?></h3>
                <div class="stat-number" id="human-clicks">0</div>
            </div>
            
            <div class="nexus-stat-card">
                <h3><?php _e('Bot Clicks', 'nexus-wp-link-shortener'); ?></h3>
                <div class="stat-number" id="bot-clicks">0</div>
            </div>
        </div>
        
        <div class="nexus-charts-container">
            <div class="nexus-chart-card">
                <h3><?php _e('Clicks Over Time', 'nexus-wp-link-shortener'); ?></h3>
                <canvas id="clicks-chart" width="400" height="200"></canvas>
            </div>
            
            <div class="nexus-chart-card">
                <h3><?php _e('Top Referrers', 'nexus-wp-link-shortener'); ?></h3>
                <div id="referrers-list">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load analytics data on page load
    loadAnalyticsData();
    
    // Refresh data every 30 seconds
    setInterval(loadAnalyticsData, 30000);
    
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
                console.error('Analytics loading failed:', data);
            }
        })
        .catch(error => {
            console.error('Analytics loading error:', error);
        });
    }
    
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
    }
    
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
    
    function extractDomain(url) {
        try {
            const domain = new URL(url).hostname;
            return domain.replace('www.', '');
        } catch (e) {
            return url;
        }
    }
});
</script>