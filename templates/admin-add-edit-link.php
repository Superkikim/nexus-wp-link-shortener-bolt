<div class="wrap">
    <h1>
        <?php if ($action === 'edit'): ?>
            <?php _e('Edit Nexus Link', 'nexus-wp-link-shortener'); ?>
        <?php else: ?>
            <?php _e('Add New Nexus Link', 'nexus-wp-link-shortener'); ?>
        <?php endif; ?>
        <span class="subtitle">- <?php echo esc_html($post->post_title); ?></span>
    </h1>
    
    <!-- Breadcrumb Navigation -->
    <div class="nexus-breadcrumb">
        <a href="<?php echo admin_url('admin.php?page=nexus-links-manage&post_id=' . $post->ID . '&post_type=' . $post_type); ?>" class="button">
            ← <?php _e('Back to Manage Links', 'nexus-wp-link-shortener'); ?>
        </a>
    </div>
    
    <div class="nexus-post-info">
        <p><strong><?php _e('Post URL:', 'nexus-wp-link-shortener'); ?></strong> 
           <a href="<?php echo get_permalink($post->ID); ?>" target="_blank"><?php echo get_permalink($post->ID); ?></a>
        </p>
    </div>
    
    <!-- Add/Edit Link Form -->
    <div class="nexus-form-container">
        <form method="post" action="">
            <?php wp_nonce_field('nexus_links_add_edit', 'nexus_links_nonce'); ?>
            <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update_link' : 'create_link'; ?>">
            <?php if ($link): ?>
                <input type="hidden" name="link_id" value="<?php echo $link->id; ?>">
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="name"><?php _e('Link Name', 'nexus-wp-link-shortener'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo esc_attr($link ? $link->name : $post->post_title); ?>" 
                               class="regular-text" 
                               required>
                        <p class="description"><?php _e('Human-readable name for this link', 'nexus-wp-link-shortener'); ?></p>
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
                               value="<?php echo esc_attr($link ? $link->campaign : ''); ?>" 
                               class="regular-text">
                        <p class="description"><?php _e('Optional campaign identifier for analytics', 'nexus-wp-link-shortener'); ?></p>
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
                                  class="large-text"><?php echo esc_textarea($link ? $link->description : ''); ?></textarea>
                        <p class="description"><?php _e('Optional description for internal reference', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="target_url"><?php _e('Target URL Override', 'nexus-wp-link-shortener'); ?></label>
                    </th>
                    <td>
                        <input type="url" 
                               id="target_url" 
                               name="target_url" 
                               value="<?php echo esc_attr($link ? $link->target_url : ''); ?>" 
                               class="regular-text">
                        <p class="description"><?php _e('Leave empty to redirect to the post URL, or enter a custom URL', 'nexus-wp-link-shortener'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="http_status"><?php _e('Redirect Type', 'nexus-wp-link-shortener'); ?></label>
                    </th>
                    <td>
                        <select id="http_status" name="http_status">
                            <option value="302" <?php selected($link ? $link->http_status : 302, 302); ?>>
                                <?php _e('302 - Temporary Redirect (Default)', 'nexus-wp-link-shortener'); ?>
                            </option>
                            <option value="301" <?php selected($link ? $link->http_status : 302, 301); ?>>
                                <?php _e('301 - Permanent Redirect', 'nexus-wp-link-shortener'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Status', 'nexus-wp-link-shortener'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="active" 
                                   value="1" 
                                   <?php checked($link ? $link->active : 1, 1); ?>>
                            <?php _e('Active (unchecked = paused)', 'nexus-wp-link-shortener'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <?php if ($action === 'edit'): ?>
                    <?php submit_button(__('Update Nexus Link', 'nexus-wp-link-shortener'), 'primary', 'submit', false); ?>
                <?php else: ?>
                    <?php submit_button(__('Create Nexus Link', 'nexus-wp-link-shortener'), 'primary', 'submit', false); ?>
                <?php endif; ?>
                
                <a href="<?php echo admin_url('admin.php?page=nexus-links-manage&post_id=' . $post->ID . '&post_type=' . $post_type); ?>" 
                   class="button">
                    <?php _e('Cancel', 'nexus-wp-link-shortener'); ?>
                </a>
            </p>
        </form>
    </div>
</div>

<style>
.nexus-breadcrumb {
    margin: 20px 0;
}

.nexus-post-info {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
}

.nexus-form-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
}

.form-table td {
    padding: 15px 10px;
}

.submit {
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.submit .button {
    margin-right: 10px;
}
</style>