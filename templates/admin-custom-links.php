<div class="wrap">
    <h1><?php _e('Custom Nexus Links', 'nexus-wp-link-shortener'); ?></h1>
    <p class="description"><?php _e('Create short links that redirect to any external URL.', 'nexus-wp-link-shortener'); ?></p>
    
    <!-- Add New Custom Link Form -->
    <div class="nexus-add-custom-link">
        <h2><?php _e('Add New Custom Link', 'nexus-wp-link-shortener'); ?></h2>
        
        <form method="post" action="" class="nexus-custom-link-form">
            <?php wp_nonce_field('nexus_links_custom', 'nexus_links_nonce'); ?>
            <input type="hidden" name="action" value="create_custom_link">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="target_url"><?php _e('Target URL', 'nexus-wp-link-shortener'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="url" 
                               id="target_url" 
                               name="target_url" 
                               value="" 
                               class="regular-text" 
                               placeholder="https://example.com/your-destination"
                               required>
                        <p class="description"><?php _e('The URL where users will be redirected when they click your short link.', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="name"><?php _e('Link Name', 'nexus-wp-link-shortener'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="" 
                               class="regular-text" 
                               placeholder="My Custom Link"
                               required>
                        <p class="description"><?php _e('A descriptive name for this link (for your reference only).', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="campaign"><?php _e('Campaign', 'nexus-wp-link-shortener'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="campaign" 
                               name="campaign" 
                               value="" 
                               class="regular-text"
                               placeholder="social-media">
                        <p class="description"><?php _e('Optional campaign identifier for analytics tracking.', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="description"><?php _e('Description', 'nexus-wp-link-shortener'); ?></label>
                    </th>
                    <td>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3" 
                                  class="large-text"
                                  placeholder="Optional description for internal reference"></textarea>
                        <p class="description"><?php _e('Optional description for internal reference.', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="http_status"><?php _e('Redirect Type', 'nexus-wp-link-shortener'); ?></label>
                    </th>
                    <td>
                        <select id="http_status" name="http_status">
                            <option value="302"><?php _e('302 - Temporary Redirect (Default)', 'nexus-wp-link-shortener'); ?></option>
                            <option value="301"><?php _e('301 - Permanent Redirect', 'nexus-wp-link-shortener'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Status', 'nexus-wp-link-shortener'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="active" value="1" checked>
                            <?php _e('Active (unchecked = paused)', 'nexus-wp-link-shortener'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <?php submit_button(__('Create Custom Link', 'nexus-wp-link-shortener'), 'primary', 'submit', false); ?>
            </p>
        </form>
    </div>
    
    <hr>
    
    <!-- Existing Custom Links -->
    <div class="nexus-custom-links-list">
        <h2><?php _e('Existing Custom Links', 'nexus-wp-link-shortener'); ?></h2>
        
        <?php if (empty($custom_links)): ?>
            <div class="nexus-no-links">
                <p><?php _e('No custom links found. Create your first custom link above!', 'nexus-wp-link-shortener'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Nexus URL', 'nexus-wp-link-shortener'); ?></th>
                        <th><?php _e('Name', 'nexus-wp-link-shortener'); ?></th>
                        <th><?php _e('Target URL', 'nexus-wp-link-shortener'); ?></th>
                        <th><?php _e('Campaign', 'nexus-wp-link-shortener'); ?></th>
                        <th><?php _e('Status', 'nexus-wp-link-shortener'); ?></th>
                        <th><?php _e('Clicks', 'nexus-wp-link-shortener'); ?></th>
                        <th><?php _e('Created', 'nexus-wp-link-shortener'); ?></th>
                        <th><?php _e('Actions', 'nexus-wp-link-shortener'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($custom_links as $link): ?>
                    <tr data-link-id="<?php echo $link->id; ?>">
                        <td>
                            <strong><?php echo home_url('/' . $link->slug); ?></strong>
                            <button class="button-link nexus-copy-button" 
                                    data-url="<?php echo home_url('/' . $link->slug); ?>" 
                                    title="<?php _e('Copy to clipboard', 'nexus-wp-link-shortener'); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                        </td>
                        <td><?php echo esc_html($link->name); ?></td>
                        <td>
                            <a href="<?php echo esc_url($link->target_url); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html(wp_trim_words($link->target_url, 6, '...')); ?>
                                <span class="dashicons dashicons-external"></span>
                            </a>
                        </td>
                        <td><?php echo esc_html($link->campaign ?: '-'); ?></td>
                        <td>
                            <?php if ($link->active): ?>
                                <span class="nexus-status-active"><?php _e('Active', 'nexus-wp-link-shortener'); ?></span>
                            <?php else: ?>
                                <span class="nexus-status-paused"><?php _e('Paused', 'nexus-wp-link-shortener'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            global $wpdb;
                            $clicks_table = $wpdb->prefix . 'nexus_clicks';
                            $click_count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM $clicks_table WHERE link_id = %d",
                                $link->id
                            ));
                            echo intval($click_count);
                            ?>
                        </td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($link->created_at)); ?></td>
                        <td>
                            <button class="button nexus-edit-custom-link" data-link-id="<?php echo $link->id; ?>">
                                <?php _e('Edit', 'nexus-wp-link-shortener'); ?>
                            </button>
                            <button class="button nexus-delete-custom-link" 
                                    data-link-id="<?php echo $link->id; ?>"
                                    style="color: #d63638; border-color: #d63638;">
                                <?php _e('Delete', 'nexus-wp-link-shortener'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Custom Link Modal -->
<div id="nexus-edit-modal" class="nexus-modal" style="display: none;">
    <div class="nexus-modal-content">
        <div class="nexus-modal-header">
            <h3><?php _e('Edit Custom Link', 'nexus-wp-link-shortener'); ?></h3>
            <button class="nexus-modal-close">&times;</button>
        </div>
        <div class="nexus-modal-body">
            <form id="nexus-edit-form">
                <input type="hidden" id="edit-link-id" name="link_id">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="edit-target-url"><?php _e('Target URL', 'nexus-wp-link-shortener'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="edit-target-url" name="target_url" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="edit-name"><?php _e('Link Name', 'nexus-wp-link-shortener'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="edit-name" name="name" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="edit-campaign"><?php _e('Campaign', 'nexus-wp-link-shortener'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="edit-campaign" name="campaign" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="edit-description"><?php _e('Description', 'nexus-wp-link-shortener'); ?></label>
                        </th>
                        <td>
                            <textarea id="edit-description" name="description" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="edit-http-status"><?php _e('Redirect Type', 'nexus-wp-link-shortener'); ?></label>
                        </th>
                        <td>
                            <select id="edit-http-status" name="http_status">
                                <option value="302"><?php _e('302 - Temporary Redirect', 'nexus-wp-link-shortener'); ?></option>
                                <option value="301"><?php _e('301 - Permanent Redirect', 'nexus-wp-link-shortener'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Status', 'nexus-wp-link-shortener'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="edit-active" name="active" value="1">
                                <?php _e('Active', 'nexus-wp-link-shortener'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="nexus-modal-footer">
            <button class="button button-primary" id="nexus-save-edit"><?php _e('Save Changes', 'nexus-wp-link-shortener'); ?></button>
            <button class="button nexus-modal-close"><?php _e('Cancel', 'nexus-wp-link-shortener'); ?></button>
        </div>
    </div>
</div>

<style>
.nexus-add-custom-link {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.nexus-custom-link-form .form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
}

.nexus-custom-link-form .form-table td {
    padding: 15px 10px;
}

.required {
    color: #d63638;
}

.nexus-custom-links-list {
    margin-top: 30px;
}

.nexus-no-links {
    text-align: center;
    padding: 40px 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.nexus-status-active {
    color: #00a32a;
    font-weight: bold;
}

.nexus-status-paused {
    color: #d63638;
    font-weight: bold;
}

.nexus-copy-button {
    margin-left: 10px;
    font-size: 12px;
    text-decoration: none;
}

.nexus-copy-button:hover {
    text-decoration: underline;
}

.nexus-delete-custom-link:hover {
    background: #d63638;
    color: #fff;
}

/* Modal Styles */
.nexus-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.nexus-modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.nexus-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nexus-modal-header h3 {
    margin: 0;
}

.nexus-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.nexus-modal-close:hover {
    color: #000;
}

.nexus-modal-body {
    padding: 20px;
}

.nexus-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.nexus-modal-footer .button {
    margin-left: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy to clipboard functionality
    document.querySelectorAll('.nexus-copy-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const url = this.dataset.url;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<span class="dashicons dashicons-yes"></span>';
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                    }, 2000);
                });
            }
        });
    });
    
    // Edit link functionality
    const modal = document.getElementById('nexus-edit-modal');
    const editButtons = document.querySelectorAll('.nexus-edit-custom-link');
    const closeButtons = document.querySelectorAll('.nexus-modal-close');
    const saveButton = document.getElementById('nexus-save-edit');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const linkId = this.dataset.linkId;
            const row = this.closest('tr');
            
            // Get current values from the row
            const name = row.querySelector('td:nth-child(2)').textContent.trim();
            const targetUrl = row.querySelector('td:nth-child(3) a').href;
            const campaign = row.querySelector('td:nth-child(4)').textContent.trim();
            const isActive = row.querySelector('.nexus-status-active') !== null;
            
            // Populate modal form
            document.getElementById('edit-link-id').value = linkId;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-target-url').value = targetUrl;
            document.getElementById('edit-campaign').value = campaign === '-' ? '' : campaign;
            document.getElementById('edit-active').checked = isActive;
            
            modal.style.display = 'block';
        });
    });
    
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Save edit functionality
    saveButton.addEventListener('click', function() {
        const form = document.getElementById('nexus-edit-form');
        const formData = new FormData(form);
        formData.append('action', 'nexus_update_custom_link');
        formData.append('nonce', nexusLinks.nonce);
        
        this.disabled = true;
        this.textContent = '<?php _e('Saving...', 'nexus-wp-link-shortener'); ?>';
        
        fetch(nexusLinks.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh to show updated data
            } else {
                alert(data.data || '<?php _e('Failed to update link', 'nexus-wp-link-shortener'); ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php _e('An error occurred', 'nexus-wp-link-shortener'); ?>');
        })
        .finally(() => {
            this.disabled = false;
            this.textContent = '<?php _e('Save Changes', 'nexus-wp-link-shortener'); ?>';
            modal.style.display = 'none';
        });
    });
    
    // Delete link functionality
    document.querySelectorAll('.nexus-delete-custom-link').forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('<?php _e('Are you sure you want to delete this custom link? This action cannot be undone.', 'nexus-wp-link-shortener'); ?>')) {
                return;
            }
            
            const linkId = this.dataset.linkId;
            const row = this.closest('tr');
            
            const formData = new FormData();
            formData.append('action', 'nexus_delete_custom_link');
            formData.append('link_id', linkId);
            formData.append('nonce', nexusLinks.nonce);
            
            this.disabled = true;
            this.textContent = '<?php _e('Deleting...', 'nexus-wp-link-shortener'); ?>';
            
            fetch(nexusLinks.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.remove();
                    
                    // Check if table is now empty
                    const tbody = document.querySelector('.nexus-custom-links-list tbody');
                    if (tbody.children.length === 0) {
                        location.reload(); // Refresh to show "no links" message
                    }
                } else {
                    alert(data.data || '<?php _e('Failed to delete link', 'nexus-wp-link-shortener'); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php _e('An error occurred', 'nexus-wp-link-shortener'); ?>');
            })
            .finally(() => {
                this.disabled = false;
                this.textContent = '<?php _e('Delete', 'nexus-wp-link-shortener'); ?>';
            });
        });
    });
});
</script>