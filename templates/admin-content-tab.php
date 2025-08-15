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
        <tr data-post-id="<?php echo $post->ID; ?>" 
            data-link-count="<?php echo $post->link_count; ?>"
            data-title="<?php echo esc_attr(strtolower($post->post_title)); ?>"
            data-content="<?php echo esc_attr(strtolower(wp_strip_all_tags($post->post_content))); ?>">
                    <?php if ($post->link_count > 0): ?>
                        <span class="nexus-link-count"><?php echo $post->link_count; ?> <?php _e('links', 'nexus-wp-link-shortener'); ?></span>
                    <?php else: ?>
                        <span class="nexus-no-links"><?php _e('No links', 'nexus-wp-link-shortener'); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="nexus-link-count">
                        <?php 
                        printf(
                            _n('%d link', '%d links', $post->link_count, 'nexus-wp-link-shortener'),
                            $post->link_count
                        ); 
                        ?>
                    </span>
                            data-post-id="<?php echo $post->ID; ?>" 
                    <span class="nexus-no-links"><?php _e('No links', 'nexus-wp-link-shortener'); ?></span>
                        <?php _e('Instant Create + Copy', 'nexus-wp-link-shortener'); ?>
                    </button>
                    
                    <?php if ($post->link_count > 0): ?>
                    <a href="<?php echo admin_url('admin.php?page=nexus-links-manage&post_id=' . $post->ID . '&post_type=' . $post_type); ?>" 
                       class="button">
                        <?php _e('Manage Links', 'nexus-wp-link-shortener'); ?>
                    </a>
                    <?php endif; ?>
<!-- Search functionality -->
<div class="nexus-search-container">
    <input type="text" id="nexus-search" placeholder="<?php _e('Search content...', 'nexus-wp-link-shortener'); ?>" class="regular-text">
    <button type="button" id="nexus-search-clear" class="button" style="display: none;"><?php _e('Clear', 'nexus-wp-link-shortener'); ?></button>
</div>

                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<div id="nexus-no-results" style="display: none;" class="nexus-no-results">
    <p><?php _e('No content found matching your search criteria.', 'nexus-wp-link-shortener'); ?></p>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('nexus-search');
    const searchClear = document.getElementById('nexus-search-clear');
    const tableBody = document.getElementById('nexus-content-table-body');
    const noResults = document.getElementById('nexus-no-results');
    const allRows = tableBody.querySelectorAll('tr[data-post-id]');
    
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        if (searchTerm === '') {
            // Show all rows
            allRows.forEach(row => {
                row.style.display = '';
                visibleCount++;
            });
            searchClear.style.display = 'none';
        } else {
            // Filter rows
            allRows.forEach(row => {
                const title = row.dataset.title || '';
                const content = row.dataset.content || '';
                
                if (title.includes(searchTerm) || content.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            searchClear.style.display = 'inline-block';
        }
        
        // Show/hide no results message
        if (visibleCount === 0 && searchTerm !== '') {
            noResults.style.display = 'block';
            tableBody.parentElement.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            tableBody.parentElement.style.display = '';
        }
        
        // Apply has-links filter if active
        const hasLinksFilter = document.getElementById('has-links-filter');
        if (hasLinksFilter && hasLinksFilter.checked) {
            applyLinksFilter();
        }
    }
    
    function applyLinksFilter() {
        const showOnlyWithLinks = document.getElementById('has-links-filter').checked;
        const visibleRows = Array.from(allRows).filter(row => row.style.display !== 'none');
        
        visibleRows.forEach(row => {
            const linkCount = parseInt(row.dataset.linkCount);
            if (showOnlyWithLinks && linkCount === 0) {
                row.style.display = 'none';
            } else if (!showOnlyWithLinks) {
                // Only show if it matches search criteria
                const searchTerm = searchInput.value.toLowerCase().trim();
                if (searchTerm === '') {
                    row.style.display = '';
                } else {
                    const title = row.dataset.title || '';
                    const content = row.dataset.content || '';
                    row.style.display = (title.includes(searchTerm) || content.includes(searchTerm)) ? '' : 'none';
                }
            }
        });
    }
    
    // Search event listeners
    searchInput.addEventListener('input', performSearch);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            performSearch();
        }
    });
    
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    });
    
    // Instant create functionality
    document.querySelectorAll('.nexus-instant-create').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const postType = this.dataset.postType;
            const originalText = this.textContent;
            
    <tbody id="nexus-content-table-body">
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
    document.getElementById('has-links-filter').addEventListener('change', applyLinksFilter);
});
</script>