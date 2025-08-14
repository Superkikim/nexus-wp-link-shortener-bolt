<div class="wrap">
    <h1><?php echo esc_html($post_type_object->labels->name); ?> - <?php _e('Short Links', 'nexus-wp-link-shortener'); ?></h1>
    
    <div class="nexus-filters">
        <label for="has-links-filter">
            <input type="checkbox" id="has-links-filter"> 
            <?php _e('Show only items with short links', 'nexus-wp-link-shortener'); ?>
        </label>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Title', 'nexus-wp-link-shortener'); ?></th>
                <th><?php _e('Date', 'nexus-wp-link-shortener'); ?></th>
                <th><?php _e('Short Links', 'nexus-wp-link-shortener'); ?></th>
                <th><?php _e('Actions', 'nexus-wp-link-shortener'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr data-post-id="<?php echo $post->ID; ?>" data-link-count="<?php echo $post->link_count; ?>">
                <td>
                    <strong><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a></strong>
                    <div class="post-url"><?php echo get_permalink($post->ID); ?></div>
                </td>
                <td><?php echo get_the_date('', $post->ID); ?></td>
                <td>
                    <?php if ($post->link_count > 0): ?>
                        <span class="nexus-link-count"><?php echo $post->link_count; ?> <?php _e('links', 'nexus-wp-link-shortener'); ?></span>
                    <?php else: ?>
                        <span class="nexus-no-links"><?php _e('No links', 'nexus-wp-link-shortener'); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="button nexus-instant-create" 
                            data-post-id="<?php echo $post->ID; ?>" 
                            data-post-type="<?php echo $post_type; ?>">
                        <?php _e('Instant Create + Copy', 'nexus-wp-link-shortener'); ?>
                    </button>
                    
                    <?php if ($post->link_count > 0): ?>
                    <a href="<?php echo admin_url('admin.php?page=nexus-links-manage&post_id=' . $post->ID . '&post_type=' . $post_type); ?>" 
                       class="button">
                        <?php _e('Manage Links', 'nexus-wp-link-shortener'); ?>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Instant create functionality
    document.querySelectorAll('.nexus-instant-create').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const postType = this.dataset.postType;
            const originalText = this.textContent;
            
            this.textContent = nexusLinks.strings.creating;
            this.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'nexus_create_instant_link');
            formData.append('post_id', postId);
            formData.append('post_type', postType);
            formData.append('nonce', nexusLinks.nonce);
            
            fetch(nexusLinks.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Copy to clipboard
                    navigator.clipboard.writeText(data.data.url).then(() => {
                        this.textContent = nexusLinks.strings.created;
                        setTimeout(() => {
                            this.textContent = originalText;
                            this.disabled = false;
                            location.reload(); // Refresh to update link counts
                        }, 2000);
                    });
                } else {
                    alert(nexusLinks.strings.error);
                    this.textContent = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(nexusLinks.strings.error);
                this.textContent = originalText;
                this.disabled = false;
            });
        });
    });
    
    // Filter functionality
    document.getElementById('has-links-filter').addEventListener('change', function() {
        const showOnlyWithLinks = this.checked;
        const rows = document.querySelectorAll('tbody tr[data-post-id]');
        
        rows.forEach(row => {
            const linkCount = parseInt(row.dataset.linkCount);
            if (showOnlyWithLinks && linkCount === 0) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
            }
        });
    });
});
</script>