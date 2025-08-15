<div class="wrap">
    <h1><?php echo esc_html($post_type_object->labels->name); ?> - <?php _e('Short Links', 'nexus-wp-link-shortener'); ?></h1>
    
    <!-- Search and Filter Controls -->
    <div class="nexus-controls-container">
        <div class="nexus-search-container">
            <input type="text" id="nexus-search" placeholder="<?php _e('Search content...', 'nexus-wp-link-shortener'); ?>" class="regular-text">
            <button type="button" id="nexus-search-clear" class="button" style="display: none;"><?php _e('Clear', 'nexus-wp-link-shortener'); ?></button>
        </div>
        
        <div class="nexus-actions-container">
            <button type="button" id="copy-last-link" class="button button-secondary" title="<?php _e('Copy last created short link', 'nexus-wp-link-shortener'); ?>">
                <span class="dashicons dashicons-admin-page"></span>
                <?php _e('Copy Last', 'nexus-wp-link-shortener'); ?>
            </button>
        </div>
    </div>
    
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
        <tbody id="nexus-content-table-body">
            <?php foreach ($posts as $post): ?>
            <tr data-post-id="<?php echo $post->ID; ?>" 
                data-link-count="<?php echo $post->link_count; ?>"
                data-title="<?php echo esc_attr(strtolower($post->post_title)); ?>"
                data-content="<?php echo esc_attr(strtolower(wp_strip_all_tags($post->post_content))); ?>">
                <td>
                    <strong><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a></strong>
                    <div class="post-url"><?php echo get_permalink($post->ID); ?></div>
                </td>
                <td><?php echo get_the_date('', $post->ID); ?></td>
                <td>
                    <span class="nexus-link-count">
                        <?php 
                        printf(
                            _n('%d link', '%d links', $post->link_count, 'nexus-wp-link-shortener'),
                            $post->link_count
                        ); 
                        ?>
                    </span>
                </td>
                <td>
                    <button type="button" 
                            class="button button-primary nexus-instant-create" 
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
        let visibleCount = 0;
        
        allRows.forEach(row => {
            const linkCount = parseInt(row.dataset.linkCount);
            const searchTerm = searchInput.value.toLowerCase().trim();
            const title = row.dataset.title || '';
            const content = row.dataset.content || '';
            const matchesSearch = searchTerm === '' || title.includes(searchTerm) || content.includes(searchTerm);
            
            let shouldShow = matchesSearch;
            if (showOnlyWithLinks) {
                shouldShow = shouldShow && linkCount > 0;
            }
            
            if (shouldShow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        const searchTerm = searchInput.value.toLowerCase().trim();
        if (visibleCount === 0 && (searchTerm !== '' || showOnlyWithLinks)) {
            noResults.style.display = 'block';
            tableBody.parentElement.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            tableBody.parentElement.style.display = '';
        }
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
    
    // Copy last created link functionality
    document.getElementById('copy-last-link').addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<span class="dashicons dashicons-update spin"></span> <?php _e('Finding...', 'nexus-wp-link-shortener'); ?>';
        
        const formData = new FormData();
        formData.append('action', 'nexus_get_last_link');
        formData.append('post_type', '<?php echo $post_type; ?>');
        formData.append('nonce', nexusLinks.nonce);
        
        fetch(nexusLinks.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.url) {
                navigator.clipboard.writeText(data.data.url).then(() => {
                    button.innerHTML = '<span class="dashicons dashicons-yes"></span> <?php _e('Copied!', 'nexus-wp-link-shortener'); ?>';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);
                });
            } else {
                alert(data.data || '<?php _e('No links found for this content type.', 'nexus-wp-link-shortener'); ?>');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php _e('Error occurred while fetching last link.', 'nexus-wp-link-shortener'); ?>');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });
    
    // Instant create functionality
    document.querySelectorAll('.nexus-instant-create').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const postType = this.dataset.postType;
            const originalText = this.textContent;
            
            this.disabled = true;
            this.textContent = nexusLinks.strings.creating;
            
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

<style>
.nexus-controls-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.nexus-search-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nexus-actions-container {
    display: flex;
    gap: 10px;
}

#copy-last-link {
    display: flex;
    align-items: center;
    gap: 5px;
}

#copy-last-link .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.nexus-filters {
    margin: 10px 0;
}

.nexus-no-results {
    text-align: center;
    padding: 40px 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #666;
}

.nexus-link-count {
    font-weight: bold;
    color: #00a32a;
}

.post-url {
    font-size: 12px;
    color: #646970;
    margin-top: 4px;
}

.nexus-instant-create {
    background: #00a32a !important;
    border-color: #00a32a !important;
    color: #fff !important;
}

.nexus-instant-create:hover {
    background: #008a20 !important;
    border-color: #008a20 !important;
}

.nexus-instant-create:disabled {
    background: #ddd !important;
    border-color: #ddd !important;
    cursor: not-allowed;
}
</style>