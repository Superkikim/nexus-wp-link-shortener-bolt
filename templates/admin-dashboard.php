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
    loadDashboardData();
    
    function loadDashboardData() {
        fetch(nexusLinks.ajaxUrl + '?action=nexus_get_analytics&nonce=' + nexusLinks.nonce)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateDashboard(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading dashboard data:', error);
            });
    }
    
    function updateDashboard(data) {
        document.getElementById('total-clicks').textContent = data.total_clicks || 0;
        document.getElementById('unique-clicks').textContent = data.unique_clicks || 0;
        document.getElementById('human-clicks').textContent = data.human_clicks || 0;
        document.getElementById('bot-clicks').textContent = data.bot_clicks || 0;
        
        // Update referrers list
        const referrersList = document.getElementById('referrers-list');
        referrersList.innerHTML = '';
        
        if (data.top_referrers && data.top_referrers.length > 0) {
            const list = document.createElement('ul');
            data.top_referrers.forEach(referrer => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${referrer.referrer}</strong>: ${referrer.clicks} clicks`;
                list.appendChild(li);
            });
            referrersList.appendChild(list);
        } else {
            referrersList.innerHTML = '<p>No referrer data available.</p>';
        }
    }
});
</script>
</div>