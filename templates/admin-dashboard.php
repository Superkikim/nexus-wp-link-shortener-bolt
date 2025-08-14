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
