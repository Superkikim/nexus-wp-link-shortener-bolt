<div class="wrap">
    <h1><?php _e('Link Shortener Settings', 'nexus-wp-link-shortener'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('nexus_links_save_settings', 'nexus_links_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Analytics Retention Period', 'nexus-wp-link-shortener'); ?></th>
                <td>
                    <select name="retention_period">
                        <option value="3" <?php selected(get_option('nexus_links_retention_period', 13), 3); ?>>3 months</option>
                        <option value="6" <?php selected(get_option('nexus_links_retention_period', 13), 6); ?>>6 months</option>
                        <option value="12" <?php selected(get_option('nexus_links_retention_period', 13), 12); ?>>12 months</option>
                        <option value="13" <?php selected(get_option('nexus_links_retention_period', 13), 13); ?>>13 months (GDPR default)</option>
                        <option value="24" <?php selected(get_option('nexus_links_retention_period', 13), 24); ?>>24 months</option>
                        <option value="36" <?php selected(get_option('nexus_links_retention_period', 13), 36); ?>>36 months</option>
                    </select>
                    <p class="description"><?php _e('How long to keep click analytics data.', 'nexus-wp-link-shortener'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('UTM Parameter Forwarding', 'nexus-wp-link-shortener'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="forward_utm_params" value="1" <?php checked(get_option('nexus_links_forward_utm_params', false)); ?>>
                        <?php _e('Forward UTM parameters from short link to destination URL', 'nexus-wp-link-shortener'); ?>
                    </label>
                    <p class="description">
                        <?php _e('⚠️ <strong>Privacy Notice:</strong> When enabled, UTM parameters (utm_source, utm_medium, etc.) will be forwarded from the short link to the destination URL. This may expose tracking information to the destination site.', 'nexus-wp-link-shortener'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Analytics', 'nexus-wp-link-shortener'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="analytics_enabled" value="1" <?php checked(get_option('nexus_links_analytics_enabled', true)); ?>>
                        <?php _e('Enable click analytics tracking', 'nexus-wp-link-shortener'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Bot Detection', 'nexus-wp-link-shortener'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="bot_detection_enabled" value="1" <?php checked(get_option('nexus_links_bot_detection_enabled', true)); ?>>
                        <?php _e('Enable bot detection and filtering', 'nexus-wp-link-shortener'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('User Permissions', 'nexus-wp-link-shortener'); ?></th>
                <td>
                    <?php
                    $roles = wp_roles()->get_names();
                    $allowed_roles = get_option('nexus_links_allowed_roles', array('administrator', 'editor'));
                    
                    foreach ($roles as $role_key => $role_name):
                    ?>
                    <label>
                        <input type="checkbox" name="allowed_roles[]" value="<?php echo esc_attr($role_key); ?>" 
                               <?php checked(in_array($role_key, $allowed_roles)); ?>>
                        <?php echo esc_html($role_name); ?>
                    </label><br>
                    <?php endforeach; ?>
                    <p class="description"><?php _e('Select which user roles can create and manage short links.', 'nexus-wp-link-shortener'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Supported Content Types', 'nexus-wp-link-shortener'); ?></th>
                <td>
                    <?php
                    $all_post_types = get_post_types(array('public' => true, 'show_ui' => true), 'objects');
                    $enabled_post_types = get_option('nexus_links_enabled_post_types', array('post', 'page'));
                    
                    foreach ($all_post_types as $post_type):
                        if ($post_type->name === 'attachment') continue;
                    ?>
                    <label>
                        <input type="checkbox" name="enabled_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" 
                               <?php checked(in_array($post_type->name, $enabled_post_types)); ?>>
                        <?php echo esc_html($post_type->labels->name); ?>
                        <?php if (!$post_type->_builtin): ?>
                            <em>(<?php _e('Custom Post Type', 'nexus-wp-link-shortener'); ?>)</em>
                        <?php endif; ?>
                    </label><br>
                    <?php endforeach; ?>
                    <p class="description"><?php _e('Select which content types should support short links. Popular extensions like WooCommerce Products, Events Calendar, and other custom post types can be enabled here.', 'nexus-wp-link-shortener'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Short Link Domain Settings', 'nexus-wp-link-shortener'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="remove_www_prefix" value="1" <?php checked(get_option('nexus_links_remove_www_prefix', false)); ?>>
                        <?php _e('Remove "www" prefix from short links', 'nexus-wp-link-shortener'); ?>
                    </label>
                    <p class="description">
                        <?php _e('⚠️ <strong>Important:</strong> Only enable this if your website works without the "www" prefix. Test thoroughly before enabling. This will generate shorter URLs like "domain.com/go/abc123" instead of "www.domain.com/go/abc123".', 'nexus-wp-link-shortener'); ?>
                    </p>
                    
                    <label style="margin-top: 15px; display: block;">
                        <input type="checkbox" name="remove_go_segment" value="1" <?php checked(get_option('nexus_links_remove_go_segment', false)); ?>>
                        <?php _e('Remove "/go/" segment from URLs (Advanced)', 'nexus-wp-link-shortener'); ?>
                    </label>
                    <p class="description">
                        <?php _e('⚠️ <strong>Advanced Option:</strong> This changes URLs from "domain.com/go/abc123" to "domain.com/abc123". This may conflict with existing pages or posts. Existing short links will continue to work, but new ones will use the shorter format.', 'nexus-wp-link-shortener'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <hr>
    
    <h2><?php _e('Data Management', 'nexus-wp-link-shortener'); ?></h2>
    
    <div class="nexus-data-management">
        <h3><?php _e('Analytics Cleanup', 'nexus-wp-link-shortener'); ?></h3>
        <p><?php _e('Remove analytics data older than the retention period.', 'nexus-wp-link-shortener'); ?></p>
        <button type="button" class="button" id="cleanup-analytics">
            <?php _e('Clean Up Old Analytics', 'nexus-wp-link-shortener'); ?>
        </button>
        
        <h3><?php _e('Export Data', 'nexus-wp-link-shortener'); ?></h3>
        <p><?php _e('Export all links and analytics data for backup or migration purposes.', 'nexus-wp-link-shortener'); ?></p>
        <button type="button" class="button" id="export-data">
            <?php _e('Export Data', 'nexus-wp-link-shortener'); ?>
        </button>
        
        <h3><?php _e('Plugin Status', 'nexus-wp-link-shortener'); ?></h3>
        <?php
        global $wpdb;
        $links_table = $wpdb->prefix . 'nexus_links';
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        
        $total_links = $wpdb->get_var("SELECT COUNT(*) FROM $links_table");
        $total_clicks = $wpdb->get_var("SELECT COUNT(*) FROM $clicks_table");
        $active_links = $wpdb->get_var("SELECT COUNT(*) FROM $links_table WHERE active = 1");
        ?>
        <table class="form-table">
            <tr>
                <th><?php _e('Total Links', 'nexus-wp-link-shortener'); ?></th>
                <td><?php echo intval($total_links); ?></td>
            </tr>
            <tr>
                <th><?php _e('Active Links', 'nexus-wp-link-shortener'); ?></th>
                <td><?php echo intval($active_links); ?></td>
            </tr>
            <tr>
                <th><?php _e('Total Clicks', 'nexus-wp-link-shortener'); ?></th>
                <td><?php echo intval($total_clicks); ?></td>
            </tr>
            <tr>
                <th><?php _e('Database Version', 'nexus-wp-link-shortener'); ?></th>
                <td><?php echo get_option('nexus_links_db_version', 'Not set'); ?></td>
            </tr>
        </table>
    </div>
</div>