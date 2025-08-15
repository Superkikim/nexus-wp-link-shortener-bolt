<div class="wrap">
    <h1><?php _e('Data Management', 'nexus-wp-link-shortener'); ?></h1>
    
    <div class="nexus-data-management">
        <div class="nexus-current-stats">
            <h2><?php _e('Current Statistics', 'nexus-wp-link-shortener'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Total Links', 'nexus-wp-link-shortener'); ?></th>
                    <td><strong><?php echo intval($total_links); ?></strong></td>
                </tr>
                <tr>
                    <th><?php _e('Active Links', 'nexus-wp-link-shortener'); ?></th>
                    <td><strong><?php echo intval($active_links); ?></strong></td>
                </tr>
                <tr>
                    <th><?php _e('Total Click Records', 'nexus-wp-link-shortener'); ?></th>
                    <td><strong><?php echo intval($total_clicks); ?></strong></td>
                </tr>
            </table>
        </div>
        
        <hr>
        
        <div class="nexus-danger-zone">
            <h2 style="color: #d63638;"><?php _e('⚠️ Danger Zone', 'nexus-wp-link-shortener'); ?></h2>
            <p><?php _e('These actions cannot be undone. Please ensure you have backups before proceeding.', 'nexus-wp-link-shortener'); ?></p>
            
            <div class="nexus-action-section">
                <h3><?php _e('Reset Analytics Data', 'nexus-wp-link-shortener'); ?></h3>
                <p><?php _e('Remove all click tracking data while keeping your short links intact. This is useful for clearing test data before going live.', 'nexus-wp-link-shortener'); ?></p>
                <button type="button" class="button button-secondary" id="reset-analytics-data" 
                        style="border-color: #d63638; color: #d63638;">
                    <?php _e('Reset All Analytics Data', 'nexus-wp-link-shortener'); ?>
                </button>
            </div>
            
            <div class="nexus-action-section">
                <h3><?php _e('Remove All Links', 'nexus-wp-link-shortener'); ?></h3>
                <p><?php _e('Permanently delete all short links and their associated analytics data. This will break any existing short links in use.', 'nexus-wp-link-shortener'); ?></p>
                <button type="button" class="button button-secondary" id="remove-all-links"
                        style="border-color: #d63638; color: #d63638;">
                    <?php _e('Remove All Links', 'nexus-wp-link-shortener'); ?>
                </button>
            </div>
        </div>
        
        <hr>
        
        <div class="nexus-safe-actions">
            <h2><?php _e('Safe Actions', 'nexus-wp-link-shortener'); ?></h2>
            
            <div class="nexus-action-section">
                <h3><?php _e('Export Data', 'nexus-wp-link-shortener'); ?></h3>
                <p><?php _e('Download a complete backup of all your links and analytics data in JSON format.', 'nexus-wp-link-shortener'); ?></p>
                <button type="button" class="button button-primary" id="export-data">
                    <?php _e('Export All Data', 'nexus-wp-link-shortener'); ?>
                </button>
            </div>
            
            <div class="nexus-action-section">
                <h3><?php _e('Clean Old Analytics', 'nexus-wp-link-shortener'); ?></h3>
                <p><?php printf(__('Remove analytics data older than your configured retention period (currently set to %d months).', 'nexus-wp-link-shortener'), get_option('nexus_links_retention_period', 13)); ?></p>
                <button type="button" class="button button-primary" id="cleanup-analytics">
                    <?php _e('Clean Old Analytics', 'nexus-wp-link-shortener'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.nexus-data-management {
    max-width: 800px;
}

.nexus-current-stats {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.nexus-danger-zone {
    background: #fef7f7;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.nexus-safe-actions {
    background: #f7f9f7;
    border: 1px solid #c6f5cb;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.nexus-action-section {
    margin: 20px 0;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.nexus-action-section:last-child {
    border-bottom: none;
}

.nexus-action-section h3 {
    margin-top: 0;
}

.nexus-action-section p {
    margin-bottom: 15px;
    color: #666;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset Analytics Data
    document.getElementById('reset-analytics-data').addEventListener('click', function() {
        if (confirm('<?php _e('Are you sure you want to reset all analytics data? This action cannot be undone.', 'nexus-wp-link-shortener'); ?>')) {
            if (confirm('<?php _e('This will permanently delete all click tracking data. Your short links will remain active. Continue?', 'nexus-wp-link-shortener'); ?>')) {
                performDataAction('nexus_reset_all_data', this);
            }
        }
    });
    
    // Remove All Links
    document.getElementById('remove-all-links').addEventListener('click', function() {
        if (confirm('<?php _e('Are you sure you want to remove ALL short links? This will break any existing links in use.', 'nexus-wp-link-shortener'); ?>')) {
            if (confirm('<?php _e('This action is IRREVERSIBLE and will delete all links and analytics data. Type "DELETE" to confirm:', 'nexus-wp-link-shortener'); ?>')) {
                const confirmation = prompt('<?php _e('Type "DELETE" to confirm:', 'nexus-wp-link-shortener'); ?>');
                if (confirmation === 'DELETE') {
                    performDataAction('nexus_remove_all_links', this);
                }
            }
        }
    });
    
    // Export Data
    document.getElementById('export-data').addEventListener('click', function() {
        window.location.href = nexusLinks.ajaxUrl + '?action=nexus_export_data&nonce=' + nexusLinks.nonce;
    });
    
    // Cleanup Analytics
    document.getElementById('cleanup-analytics').addEventListener('click', function() {
        if (confirm('<?php _e('Remove analytics data older than your retention period?', 'nexus-wp-link-shortener'); ?>')) {
            performDataAction('nexus_cleanup_analytics', this);
        }
    });
    
    function performDataAction(action, button) {
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = '<?php _e('Processing...', 'nexus-wp-link-shortener'); ?>';
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', nexusLinks.nonce);
        
        fetch(nexusLinks.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.data.message || '<?php _e('Operation completed successfully.', 'nexus-wp-link-shortener'); ?>');
                location.reload(); // Refresh to update statistics
            } else {
                alert(data.data || '<?php _e('Operation failed. Please try again.', 'nexus-wp-link-shortener'); ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php _e('An error occurred. Please try again.', 'nexus-wp-link-shortener'); ?>');
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = originalText;
        });
    }
});
</script>