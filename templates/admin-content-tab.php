<div class="wrap">
    <h1><?php echo esc_html($post_type_object->labels->name); ?> - <?php _e('Nexus Links', 'nexus-wp-link-shortener'); ?></h1>
    
    <!-- Search and Filter Controls -->
    <div class="nexus-controls-container">
        <div class="nexus-search-container">
            <input type="text" 
                   id="nexus-search" 
                   placeholder="<?php _e('Search by title, category, or author...', 'nexus-wp-link-shortener'); ?>" 
                   class="regular-text"
                   aria-label="<?php _e('Search content items', 'nexus-wp-link-shortener'); ?>">
            <button type="button" 
                    id="nexus-search-clear" 
                    class="button" 
                    style="display: none;"
                    aria-label="<?php _e('Clear search', 'nexus-wp-link-shortener'); ?>">
                <?php _e('Clear', 'nexus-wp-link-shortener'); ?>
            </button>
        </div>
        
        <div class="nexus-filters-container">
            <label for="has-links-filter" class="nexus-filter-label">
                <input type="checkbox" id="has-links-filter" aria-describedby="filter-description"> 
                <?php _e('Show only items with Nexus links', 'nexus-wp-link-shortener'); ?>
            </label>
            <div id="filter-description" class="screen-reader-text">
                <?php _e('Filter to show only content items that have short links created', 'nexus-wp-link-shortener'); ?>
            </div>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped" role="table">
        <thead>
            <tr role="row">
                <th scope="col"><?php _e('Title', 'nexus-wp-link-shortener'); ?></th>
                <th scope="col"><?php _e('Author', 'nexus-wp-link-shortener'); ?></th>
                <th scope="col"><?php _e('Categories', 'nexus-wp-link-shortener'); ?></th>
                <th scope="col"><?php _e('Date', 'nexus-wp-link-shortener'); ?></th>
                <th scope="col"><?php _e('Nexus Links', 'nexus-wp-link-shortener'); ?></th>
                <th scope="col"><?php _e('Actions', 'nexus-wp-link-shortener'); ?></th>
            </tr>
        </thead>
        <tbody id="nexus-content-table-body">
            <?php foreach ($posts as $post): 
                $author = get_userdata($post->post_author);
                $categories = '';
                if ($post_type === 'post') {
                    $post_categories = get_the_category($post->ID);
                    $categories = !empty($post_categories) ? implode(', ', wp_list_pluck($post_categories, 'name')) : __('Uncategorized', 'nexus-wp-link-shortener');
                }
            ?>
            <tr data-post-id="<?php echo $post->ID; ?>" 
                data-link-count="<?php echo $post->link_count; ?>"
                data-title="<?php echo esc_attr(strtolower($post->post_title)); ?>"
                data-author="<?php echo esc_attr(strtolower($author ? $author->display_name : '')); ?>"
                data-categories="<?php echo esc_attr(strtolower($categories)); ?>"
                role="row">
                <td>
                    <strong>
                        <a href="<?php echo get_edit_post_link($post->ID); ?>" 
                           aria-label="<?php printf(__('Edit %s', 'nexus-wp-link-shortener'), esc_attr($post->post_title)); ?>">
                            <?php echo esc_html($post->post_title); ?>
                        </a>
                    </strong>
                    <div class="post-url"><?php echo get_permalink($post->ID); ?></div>
                </td>
                <td><?php echo $author ? esc_html($author->display_name) : __('Unknown', 'nexus-wp-link-shortener'); ?></td>
                <td><?php echo $post_type === 'post' ? esc_html($categories) : '—'; ?></td>
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
                <td class="actions-column">
                    <div class="action-buttons">
                        <button type="button" 
                                class="button button-primary nexus-instant-create" 
                                data-post-id="<?php echo $post->ID; ?>" 
                                data-post-type="<?php echo $post_type; ?>"
                                aria-label="<?php printf(__('Create instant link for %s', 'nexus-wp-link-shortener'), esc_attr($post->post_title)); ?>">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php _e('Create + Copy', 'nexus-wp-link-shortener'); ?>
                        </button>
                        
                        <?php if ($post->link_count > 0): ?>
                        <button type="button" 
                                class="button button-secondary nexus-copy-last" 
                                data-post-id="<?php echo $post->ID; ?>" 
                                data-post-type="<?php echo $post_type; ?>"
                                aria-label="<?php printf(__('Copy most recent link for %s', 'nexus-wp-link-shortener'), esc_attr($post->post_title)); ?>"
                                title="<?php _e('Copy most recent link', 'nexus-wp-link-shortener'); ?>">
                            <span class="dashicons dashicons-admin-page"></span>
                            <?php _e('Copy Last', 'nexus-wp-link-shortener'); ?>
                        </button>
                        
                        <a href="<?php echo admin_url('admin.php?page=nexus-links-manage&post_id=' . $post->ID . '&post_type=' . $post_type); ?>" 
                           class="button"
                           aria-label="<?php printf(__('Manage all links for %s', 'nexus-wp-link-shortener'), esc_attr($post->post_title)); ?>">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php _e('Manage', 'nexus-wp-link-shortener'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div id="nexus-no-results" style="display: none;" class="nexus-no-results" role="status" aria-live="polite">
        <p><?php _e('No content found matching your search criteria.', 'nexus-wp-link-shortener'); ?></p>
    </div>
    
    <!-- Toast Notification for Copy Feedback -->
    <div id="nexus-toast" class="nexus-toast" role="alert" aria-live="assertive" style="display: none;">
        <span id="nexus-toast-message"></span>
    </div>
</div>

<script>
/**
 * Production-ready search and copy functionality
 * Technology: Vanilla JavaScript with jQuery compatibility
 * Browser Support: Chrome 60+, Firefox 55+, Safari 12+, Edge 79+
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Cache DOM elements for performance
    const searchInput = document.getElementById('nexus-search');
    const searchClear = document.getElementById('nexus-search-clear');
    const tableBody = document.getElementById('nexus-content-table-body');
    const noResults = document.getElementById('nexus-no-results');
    const hasLinksFilter = document.getElementById('has-links-filter');
    const allRows = tableBody.querySelectorAll('tr[data-post-id]');
    const toast = document.getElementById('nexus-toast');
    const toastMessage = document.getElementById('nexus-toast-message');
    
    // Search functionality - searches ONLY title, author, and categories
    let searchTimeout;
    
    /**
     * Performs real-time search on title, author, and categories only
     * Excludes: content body, tags, metadata, other fields
     */
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        // Clear previous timeout for debouncing
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            if (searchTerm === '') {
                // Show all rows when search is empty
                allRows.forEach(row => {
                    row.style.display = '';
                    visibleCount++;
                });
                searchClear.style.display = 'none';
            } else {
                // Filter rows based on title, author, and categories ONLY
                allRows.forEach(row => {
                    const title = row.dataset.title || '';
                    const author = row.dataset.author || '';
                    const categories = row.dataset.categories || '';
                    
                    // Search only in specified fields
                    const matchesSearch = title.includes(searchTerm) || 
                                        author.includes(searchTerm) || 
                                        categories.includes(searchTerm);
                    
                    if (matchesSearch) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                searchClear.style.display = 'inline-block';
            }
            
            // Apply filter if active
            if (hasLinksFilter && hasLinksFilter.checked) {
                applyLinksFilter();
            } else {
                updateResultsDisplay(visibleCount, searchTerm);
            }
        }, 150); // Debounce for performance
    }
    
    /**
     * Applies the "has links" filter in combination with search
     */
    function applyLinksFilter() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const showOnlyWithLinks = hasLinksFilter.checked;
        let visibleCount = 0;
        
        allRows.forEach(row => {
            const linkCount = parseInt(row.dataset.linkCount) || 0;
            const title = row.dataset.title || '';
            const author = row.dataset.author || '';
            const categories = row.dataset.categories || '';
            
            // Check search criteria (title, author, categories only)
            const matchesSearch = searchTerm === '' || 
                                title.includes(searchTerm) || 
                                author.includes(searchTerm) || 
                                categories.includes(searchTerm);
            
            // Check filter criteria
            const matchesFilter = !showOnlyWithLinks || linkCount > 0;
            
            if (matchesSearch && matchesFilter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        updateResultsDisplay(visibleCount, searchTerm);
    }
    
    /**
     * Updates the display based on search/filter results
     */
    function updateResultsDisplay(visibleCount, searchTerm) {
        const hasActiveFilters = searchTerm !== '' || (hasLinksFilter && hasLinksFilter.checked);
        
        if (visibleCount === 0 && hasActiveFilters) {
            noResults.style.display = 'block';
            tableBody.parentElement.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            tableBody.parentElement.style.display = '';
        }
    }
    
    /**
     * Shows toast notification with accessibility support
     */
    function showToast(message, type = 'success') {
        toastMessage.textContent = message;
        toast.className = `nexus-toast nexus-toast-${type}`;
        toast.style.display = 'block';
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
    
    /**
     * Cross-browser clipboard functionality with fallback
     */
    async function copyToClipboard(text) {
        try {
            // Modern clipboard API (preferred)
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return true;
            } else {
                // Fallback for older browsers
                return fallbackCopyToClipboard(text);
            }
        } catch (error) {
            console.error('Clipboard copy failed:', error);
            return fallbackCopyToClipboard(text);
        }
    }
    
    /**
     * Fallback clipboard method for older browsers
     */
    function fallbackCopyToClipboard(text) {
        try {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.top = '0';
            textArea.style.left = '0';
            textArea.style.width = '2em';
            textArea.style.height = '2em';
            textArea.style.padding = '0';
            textArea.style.border = 'none';
            textArea.style.outline = 'none';
            textArea.style.boxShadow = 'none';
            textArea.style.background = 'transparent';
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            return successful;
        } catch (error) {
            console.error('Fallback copy failed:', error);
            return false;
        }
    }
    
    // Event Listeners
    
    // Search input with real-time updates
    searchInput.addEventListener('input', performSearch);
    
    // Keyboard shortcuts for search
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            performSearch();
            this.blur();
        }
    });
    
    // Clear search button
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    });
    
    // Filter checkbox
    if (hasLinksFilter) {
        hasLinksFilter.addEventListener('change', applyLinksFilter);
    }
    
    // Individual "Copy Last" buttons for each content item
    document.addEventListener('click', function(e) {
        if (e.target.closest('.nexus-copy-last')) {
            e.preventDefault();
            
            const button = e.target.closest('.nexus-copy-last');
            const postId = button.dataset.postId;
            const postType = button.dataset.postType;
            const originalHtml = button.innerHTML;
            
            // Disable button and show loading state
            button.disabled = true;
            button.innerHTML = '<span class="dashicons dashicons-update spin"></span> <?php _e('Finding...', 'nexus-wp-link-shortener'); ?>';
            
            // Fetch the most recent link for this specific content item
            const formData = new FormData();
            formData.append('action', 'nexus_get_last_link_for_post');
            formData.append('post_id', postId);
            formData.append('post_type', postType);
            formData.append('nonce', nexusLinks.nonce);
            
            fetch(nexusLinks.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(async data => {
                if (data.success && data.data.url) {
                    const copySuccess = await copyToClipboard(data.data.url);
                    
                    if (copySuccess) {
                        button.innerHTML = '<span class="dashicons dashicons-yes"></span> <?php _e('Copied!', 'nexus-wp-link-shortener'); ?>';
                        showToast('<?php _e('Link copied to clipboard!', 'nexus-wp-link-shortener'); ?>', 'success');
                    } else {
                        throw new Error('Clipboard access failed');
                    }
                } else {
                    throw new Error(data.data || '<?php _e('No links found for this item.', 'nexus-wp-link-shortener'); ?>');
                }
            })
            .catch(error => {
                console.error('Copy last link error:', error);
                button.innerHTML = '<span class="dashicons dashicons-dismiss"></span> <?php _e('Failed', 'nexus-wp-link-shortener'); ?>';
                showToast(error.message || '<?php _e('Failed to copy link. Please try again.', 'nexus-wp-link-shortener'); ?>', 'error');
            })
            .finally(() => {
                // Restore button after 2 seconds
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }, 2000);
            });
        }
    });
    
    // Instant create functionality (existing)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.nexus-instant-create')) {
            e.preventDefault();
            
            const button = e.target.closest('.nexus-instant-create');
            const postId = button.dataset.postId;
            const postType = button.dataset.postType;
            const originalHtml = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<span class="dashicons dashicons-update spin"></span> <?php _e('Creating...', 'nexus-wp-link-shortener'); ?>';
            
            const formData = new FormData();
            formData.append('action', 'nexus_create_instant_link');
            formData.append('post_id', postId);
            formData.append('post_type', postType);
            formData.append('nonce', nexusLinks.nonce);
            
            fetch(nexusLinks.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(async data => {
                if (data.success && data.data.url) {
                    const copySuccess = await copyToClipboard(data.data.url);
                    
                    if (copySuccess) {
                        button.innerHTML = '<span class="dashicons dashicons-yes"></span> <?php _e('Created!', 'nexus-wp-link-shortener'); ?>';
                        showToast('<?php _e('Link created and copied to clipboard!', 'nexus-wp-link-shortener'); ?>', 'success');
                        
                        // Refresh page after delay to update link counts
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        throw new Error('Clipboard access failed');
                    }
                } else {
                    throw new Error(data.data || '<?php _e('Failed to create link', 'nexus-wp-link-shortener'); ?>');
                }
            })
            .catch(error => {
                console.error('Create instant link error:', error);
                button.innerHTML = '<span class="dashicons dashicons-dismiss"></span> <?php _e('Failed', 'nexus-wp-link-shortener'); ?>';
                showToast(error.message || '<?php _e('Failed to create link. Please try again.', 'nexus-wp-link-shortener'); ?>', 'error');
            })
            .finally(() => {
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }, 2000);
            });
        }
    });
    
    // Initialize on page load
    performSearch();
});
</script>

<style>
/* Production-ready styles with accessibility support */
.nexus-controls-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    flex-wrap: wrap;
    gap: 15px;
}

.nexus-search-container {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 300px;
}

.nexus-filters-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nexus-filter-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 500;
}

.actions-column {
    width: 280px;
    min-width: 280px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-buttons .button {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    padding: 4px 8px;
    height: auto;
    line-height: 1.4;
}

.action-buttons .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.nexus-instant-create {
    background: #00a32a !important;
    border-color: #00a32a !important;
    color: #fff !important;
}

.nexus-instant-create:hover:not(:disabled) {
    background: #008a20 !important;
    border-color: #008a20 !important;
}

.nexus-copy-last {
    background: #0073aa !important;
    border-color: #0073aa !important;
    color: #fff !important;
}

.nexus-copy-last:hover:not(:disabled) {
    background: #005a87 !important;
    border-color: #005a87 !important;
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed !important;
}

.nexus-no-results {
    text-align: center;
    padding: 40px 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #666;
}

.nexus-toast {
    position: fixed;
    top: 32px;
    right: 20px;
    z-index: 100000;
    padding: 12px 20px;
    border-radius: 4px;
    color: #fff;
    font-weight: 500;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    animation: slideIn 0.3s ease-out;
}

.nexus-toast-success {
    background: #00a32a;
}

.nexus-toast-error {
    background: #d63638;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive design */
@media (max-width: 768px) {
    .nexus-controls-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .nexus-search-container {
        min-width: auto;
    }
    
    .action-buttons {
        justify-content: flex-start;
    }
    
    .actions-column {
        width: auto;
        min-width: auto;
    }
}

/* Accessibility improvements */
.screen-reader-text {
    clip: rect(1px, 1px, 1px, 1px);
    position: absolute !important;
    height: 1px;
    width: 1px;
    overflow: hidden;
}

/* Focus styles for keyboard navigation */
.button:focus,
input:focus {
    outline: 2px solid #0073aa;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .nexus-controls-container {
        border: 2px solid #000;
    }
    
    .button {
        border: 2px solid currentColor;
    }
}
</style>