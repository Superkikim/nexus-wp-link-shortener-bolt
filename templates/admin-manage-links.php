<div class="wrap">
    <h1>
        <?php _e('Manage Short Links', 'nexus-wp-link-shortener'); ?>
        <span class="subtitle">- <?php echo esc_html($post->post_title); ?></span>
    </h1>
    
    <div class="nexus-post-info">
        <p><strong><?php _e('Post URL:', 'nexus-wp-link-shortener'); ?></strong> 
           <a href="<?php echo get_permalink($post->ID); ?>" target="_blank"><?php echo get_permalink($post->ID); ?></a>
        </p>
        <p><strong><?php _e('Edit Post:', 'nexus-wp-link-shortener'); ?></strong> 
           <a href="<?php echo get_edit_post_link($post->ID); ?>"><?php _e('Edit in WordPress', 'nexus-wp-link-shortener'); ?></a>
        </p>
    </div>
    
    <!-- Create New Link Form -->
    <div class="nexus-create-link-form">
        <h2><?php _e('Create New Short Link', 'nexus-wp-link-shortener'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('nexus_links_manage', 'nexus_links_nonce'); ?>
            <input type="hidden" name="action" value="create_link">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Link Name', 'nexus-wp-link-shortener'); ?></th>
                    <td>
                        <input type="text" name="name" value="<?php echo esc_attr($post->post_title); ?>" class="regular-text" required>
                        <p class="description"><?php _e('Human-readable name for this link', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Campaign', 'nexus-wp-link-shortener'); ?></th>
                    <td>
                        <input type="text" name="campaign" value="" class="regular-text">
                        <p class="description"><?php _e('Optional campaign identifier for analytics', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Description', 'nexus-wp-link-shortener'); ?></th>
                    <td>
                        <textarea name="description" rows="3" class="large-text"></textarea>
                        <p class="description"><?php _e('Optional description for internal reference', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Target URL Override', 'nexus-wp-link-shortener'); ?></th>
                    <td>
                        <input type="url" name="target_url" value="" class="regular-text">
                        <p class="description"><?php _e('Leave empty to redirect to the post URL, or enter a custom URL', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Redirect Type', 'nexus-wp-link-shortener'); ?></th>
                    <td>
                        <select name="http_status">
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
            
            <?php submit_button(__('Create Short Link', 'nexus-wp-link-shortener')); ?>
        </form>
    </div>
    
    <hr>
    
    <!-- Existing Links -->
    <h2><?php _e('Existing Short Links', 'nexus-wp-link-shortener'); ?></h2>
    
    <?php if (empty($links)): ?>
        <p><?php _e('No short links found for this content.', 'nexus-wp-link-shortener'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Short URL', 'nexus-wp-link-shortener'); ?></th>
                    <th><?php _e('Name', 'nexus-wp-link-shortener'); ?></th>
                    <th><?php _e('Campaign', 'nexus-wp-link-shortener'); ?></th>
                    <th><?php _e('Status', 'nexus-wp-link-shortener'); ?></th>
                    <th><?php _e('Clicks', 'nexus-wp-link-shortener'); ?></th>
                    <th><?php _e('Created', 'nexus-wp-link-shortener'); ?></th>
                    <th><?php _e('Actions', 'nexus-wp-link-shortener'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link): ?>
                <tr>
                    <td>
                        <strong><?php echo home_url('/go/' . $link->slug); ?></strong>
                        <button class="button-link nexus-copy-button" data-url="<?php echo home_url('/go/' . $link->slug); ?>">
                            <?php _e('Copy', 'nexus-wp-link-shortener'); ?>
                        </button>
                    </td>
                    <td><?php echo esc_html($link->name); ?></td>
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
                        <button class="button nexus-edit-link" data-link-id="<?php echo $link->id; ?>">
                            <?php _e('Edit', 'nexus-wp-link-shortener'); ?>
                        </button>
                        <button class="button nexus-delete-link" data-link-id="<?php echo $link->id; ?>">
                            <?php _e('Delete', 'nexus-wp-link-shortener'); ?>
                        </button>
                    </td>
                </tr>
                
                <!-- Edit Form (Hidden by default) -->
                <tr class="nexus-edit-form" id="edit-form-<?php echo $link->id; ?>" style="display: none;">
                    <td colspan="7">
                        <form method="post" action="">
                            <?php wp_nonce_field('nexus_links_manage', 'nexus_links_nonce'); ?>
                            <input type="hidden" name="action" value="update_link">
                            <input type="hidden" name="link_id" value="<?php echo $link->id; ?>">
                            
                            <table class="form-table">
                                <tr>
                                    <th><?php _e('Name', 'nexus-wp-link-shortener'); ?></th>
                                    <td><input type="text" name="name" value="<?php echo esc_attr($link->name); ?>" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Campaign', 'nexus-wp-link-shortener'); ?></th>
                                    <td><input type="text" name="campaign" value="<?php echo esc_attr($link->campaign); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Description', 'nexus-wp-link-shortener'); ?></th>
                                    <td><textarea name="description" rows="2" class="large-text"><?php echo esc_textarea($link->description); ?></textarea></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Target URL', 'nexus-wp-link-shortener'); ?></th>
                                    <td><input type="url" name="target_url" value="<?php echo esc_attr($link->target_url); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Redirect Type', 'nexus-wp-link-shortener'); ?></th>
                                    <td>
                                        <select name="http_status">
                                            <option value="302" <?php selected($link->http_status, 302); ?>><?php _e('302 - Temporary', 'nexus-wp-link-shortener'); ?></option>
                                            <option value="301" <?php selected($link->http_status, 301); ?>><?php _e('301 - Permanent', 'nexus-wp-link-shortener'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e('Status', 'nexus-wp-link-shortener'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="active" value="1" <?php checked($link->active, 1); ?>>
                                            <?php _e('Active', 'nexus-wp-link-shortener'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <input type="submit" class="button-primary" value="<?php _e('Update Link', 'nexus-wp-link-shortener'); ?>">
                                <button type="button" class="button nexus-cancel-edit"><?php _e('Cancel', 'nexus-wp-link-shortener'); ?></button>
                            </p>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Delete Form (Hidden) -->
    <form method="post" action="" id="delete-form" style="display: none;">
        <?php wp_nonce_field('nexus_links_manage', 'nexus_links_nonce'); ?>
        <input type="hidden" name="action" value="delete_link">
        <input type="hidden" name="link_id" id="delete-link-id">
    </form>
</div>

<style>
.nexus-post-info {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
}

.nexus-create-link-form {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.nexus-status-active {
    color: #00a32a;
    font-weight: bold;
}

.nexus-status-paused {
    color: #d63638;
    font-weight: bold;
}

.nexus-edit-form {
    background: #f9f9f9;
}

.nexus-copy-button {
    margin-left: 10px;
    font-size: 12px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit link functionality
    document.querySelectorAll('.nexus-edit-link').forEach(button => {
        button.addEventListener('click', function() {
            const linkId = this.dataset.linkId;
            const editForm = document.getElementById('edit-form-' + linkId);
            
            // Hide all other edit forms
            document.querySelectorAll('.nexus-edit-form').forEach(form => {
                if (form.id !== 'edit-form-' + linkId) {
                    form.style.display = 'none';
                }
            });
            
            // Toggle this edit form
            editForm.style.display = editForm.style.display === 'none' ? 'table-row' : 'none';
        });
    });
    
    // Cancel edit functionality
    document.querySelectorAll('.nexus-cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.nexus-edit-form').style.display = 'none';
        });
    });
    
    // Delete link functionality
    document.querySelectorAll('.nexus-delete-link').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('<?php _e('Are you sure you want to delete this link? This action cannot be undone.', 'nexus-wp-link-shortener'); ?>')) {
                const linkId = this.dataset.linkId;
                document.getElementById('delete-link-id').value = linkId;
                document.getElementById('delete-form').submit();
            }
        });
    });
});
</script>