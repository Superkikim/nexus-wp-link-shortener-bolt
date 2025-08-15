<div class="wrap">
    <h1>
        <?php _e('Manage Short Links', 'nexus-wp-link-shortener'); ?>
        <span class="subtitle">- <?php echo esc_html($post->post_title); ?></span>
    </h1>
    
    <!-- Breadcrumb Navigation -->
    <div class="nexus-breadcrumb">
        <a href="<?php echo esc_url($source_page['url']); ?>" class="button">
            ← <?php printf(__('Back to %s', 'nexus-wp-link-shortener'), $source_page['title']); ?>
        </a>
    </div>
    
    <div class="nexus-post-info">
        <p><strong><?php _e('Post URL:', 'nexus-wp-link-shortener'); ?></strong> 
           <a href="<?php echo get_permalink($post->ID); ?>" target="_blank"><?php echo get_permalink($post->ID); ?></a>
        </p>
        <p><strong><?php _e('Edit Post:', 'nexus-wp-link-shortener'); ?></strong> 
           <a href="<?php echo get_edit_post_link($post->ID); ?>"><?php _e('Edit in WordPress', 'nexus-wp-link-shortener'); ?></a>
        </p>
    </div>
    
    <!-- Short Links Table -->
    <div class="nexus-links-header">
        <h2><?php _e('Nexus Links', 'nexus-wp-link-shortener'); ?></h2>
        <a href="<?php echo admin_url('admin.php?page=nexus-links-add-edit&post_id=' . $post->ID . '&post_type=' . $post_type); ?>" 
           class="button button-primary">
            <?php _e('Add New Link', 'nexus-wp-link-shortener'); ?>
        </a>
    </div>
    
    <?php if (empty($links)): ?>
        <div class="nexus-no-links">
            <p><?php _e('No nexus links found for this content.', 'nexus-wp-link-shortener'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=nexus-links-add-edit&post_id=' . $post->ID . '&post_type=' . $post_type); ?>" 
               class="button button-primary">
                <?php _e('Create Your First Link', 'nexus-wp-link-shortener'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Nexus URL', 'nexus-wp-link-shortener'); ?></th>
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
                        <strong><?php echo home_url('/' . $link->slug); ?></strong>
                        <button class="button-link nexus-copy-button" data-url="<?php echo home_url('/' . $link->slug); ?>" title="<?php _e('Copy to clipboard', 'nexus-wp-link-shortener'); ?>">
                            <span class="dashicons dashicons-admin-page"></span>
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
                        <a href="<?php echo admin_url('admin.php?page=nexus-links-add-edit&post_id=' . $post->ID . '&post_type=' . $post_type . '&link_id=' . $link->id); ?>" 
                           class="button">
                            <?php _e('Edit', 'nexus-wp-link-shortener'); ?>
                        </a>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                            <?php wp_nonce_field('nexus_links_delete', 'nexus_links_nonce'); ?>
                            <input type="hidden" name="action" value="nexus_delete_link">
                            <input type="hidden" name="link_id" value="<?php echo $link->id; ?>">
                            <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
                            <input type="hidden" name="post_type" value="<?php echo $post_type; ?>">
                            <button type="submit" class="button nexus-delete-link" 
                                    onclick="return confirm('<?php _e('Are you sure you want to delete this link? This action cannot be undone.', 'nexus-wp-link-shortener'); ?>')">
                                <?php _e('Delete', 'nexus-wp-link-shortener'); ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
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

.nexus-links-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0 20px 0;
}

.nexus-links-header h2 {
    margin: 0;
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

.nexus-delete-link {
    color: #d63638;
    border-color: #d63638;
}

.nexus-delete-link:hover {
    background: #d63638;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy to clipboard functionality
    document.querySelectorAll('.nexus-copy-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const url = this.dataset.url;
            const originalText = this.textContent;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    this.textContent = '<?php _e('Copied!', 'nexus-wp-link-shortener'); ?>';
                    setTimeout(() => {
                        this.textContent = originalText;
                    }, 2000);
                });
            }
        });
    });
});
</script>