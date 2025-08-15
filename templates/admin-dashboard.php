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
                <div id="clicks-chart-container">
                    <canvas id="clicks-chart" width="400" height="200" style="display: none;"></canvas>
                    <div id="clicks-chart-placeholder">
                        <p><?php _e('Loading chart data...', 'nexus-wp-link-shortener'); ?></p>
                    </div>
                    <div id="clicks-chart-no-data" style="display: none;">
                        <p><?php _e('No click data available for the selected period.', 'nexus-wp-link-shortener'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="nexus-chart-card">
                <h3><?php _e('Top Referrers', 'nexus-wp-link-shortener'); ?></h3>
                <div id="referrers-list">
                    <p><?php _e('Loading referrer data...', 'nexus-wp-link-shortener'); ?></p>
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
                updateClicksChart(data.data.clicks_by_day);
            } else {
                console.error('Analytics loading failed:', data);
                showErrorState();
            }
        })
        .catch(error => {
            console.error('Analytics loading error:', error);
            showErrorState();
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
        
    }
    
    function updateReferrersList(referrers) {
        const container = document.getElementById('referrers-list');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (referrers && referrers.length > 0) {
            const list = document.createElement('ul');
            referrers.forEach(function(referrer) {
                const domain = referrer.referrer_domain || extractDomain(referrer.referrer) || 'Direct Traffic';
                const item = document.createElement('li');
                item.innerHTML = 
                    '<span><strong>' + domain + '</strong></span>' +
                    '<span>' + referrer.clicks + ' clicks</span>';
                list.appendChild(item);
            });
            container.appendChild(list);
        } else {
            container.innerHTML = '<p><?php _e('No referrer data available for the selected period.', 'nexus-wp-link-shortener'); ?></p>';
        }
    }
    
    function extractDomain(url) {
     * Update clicks chart with simple bar visualization
            const domain = new URL(url).hostname;
            return domain.replace('www.', '');
        } catch (e) {
        const placeholder = document.getElementById('clicks-chart-placeholder');
        const noDataDiv = document.getElementById('clicks-chart-no-data');
        const canvas = document.getElementById('clicks-chart');
        
        if (!clicksData || clicksData.length === 0) {
            placeholder.style.display = 'none';
            noDataDiv.style.display = 'block';
            canvas.style.display = 'none';
            return;
        }
        
        // Simple text-based chart for now
        placeholder.style.display = 'none';
        noDataDiv.style.display = 'none';
        canvas.style.display = 'none';
        
        // Create simple chart container
        let chartHtml = '<div class="simple-chart">';
        const maxClicks = Math.max(...clicksData.map(d => parseInt(d.clicks)));
        
        clicksData.slice(-7).forEach(function(day) { // Show last 7 days
            const percentage = maxClicks > 0 ? (parseInt(day.clicks) / maxClicks) * 100 : 0;
            chartHtml += `
                <div class="chart-bar-container">
                    <div class="chart-bar" style="height: ${Math.max(percentage, 5)}%; background: #0073aa;">
                        <span class="chart-value">${day.clicks}</span>
                    </div>
                    <div class="chart-label">${formatDate(day.date)}</div>
                </div>
            `;
        });
        chartHtml += '</div>';
        
        const container = document.getElementById('clicks-chart-container');
        const existingChart = container.querySelector('.simple-chart');
        if (existingChart) {
            existingChart.remove();
        }
        container.insertAdjacentHTML('beforeend', chartHtml);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
    
        if (!url || url === '') return 'Direct Traffic';
    function showErrorState() {
        document.getElementById('clicks-chart-placeholder').innerHTML = 
            '<p style="color: #d63638;"><?php _e('Error loading analytics data. Please refresh the page.', 'nexus-wp-link-shortener'); ?></p>';
        
            return 'Direct Traffic';
            '<p style="color: #d63638;"><?php _e('Error loading referrer data.', 'nexus-wp-link-shortener'); ?></p>';
    }
});