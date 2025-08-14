<?php
/**
 * Analytics class for tracking clicks
 */
class Nexus_Links_Analytics {
    
    /**
     * Record a click
     */
    public function record_click($link_id) {
        $data = array(
            'referrer' => $this->get_referrer(),
            'user_agent' => $this->get_user_agent(),
            'device' => $this->get_device(),
            'browser' => $this->get_browser(),
            'os' => $this->get_os(),
            'anonymized_ip' => $this->get_anonymized_ip(),
            'country' => $this->get_country(),
            'region' => $this->get_region(),
            'is_bot' => $this->is_bot(),
            'utm_source' => $this->get_utm_param('utm_source'),
            'utm_medium' => $this->get_utm_param('utm_medium'),
            'utm_campaign' => $this->get_utm_param('utm_campaign'),
            'utm_term' => $this->get_utm_param('utm_term'),
            'utm_content' => $this->get_utm_param('utm_content'),
            'query_params_json' => $this->get_query_params_json()
        );
        
        Nexus_Links_Database::record_click($link_id, $data);
    }
    
    /**
     * Get referrer
     */
    private function get_referrer() {
        return isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : null;
    }
    
    /**
     * Get user agent
     */
    private function get_user_agent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : null;
    }
    
    /**
     * Get device type
     */
    private function get_device() {
        $user_agent = $this->get_user_agent();
        if (!$user_agent) return 'Unknown';
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
            if (preg_match('/iPad/', $user_agent)) {
                return 'Tablet';
            }
            return 'Mobile';
        }
        
        return 'Desktop';
    }
    
    /**
     * Get browser
     */
    private function get_browser() {
        $user_agent = $this->get_user_agent();
        if (!$user_agent) return 'Unknown';
        
        $browsers = array(
            'Chrome' => '/Chrome/i',
            'Firefox' => '/Firefox/i',
            'Safari' => '/Safari/i',
            'Edge' => '/Edge/i',
            'Internet Explorer' => '/MSIE/i',
            'Opera' => '/Opera/i'
        );
        
        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return $browser;
            }
        }
        
        return 'Other';
    }
    
    /**
     * Get operating system
     */
    private function get_os() {
        $user_agent = $this->get_user_agent();
        if (!$user_agent) return 'Unknown';
        
        $os_array = array(
            'Windows' => '/Windows/i',
            'Mac OS' => '/Mac/i',
            'iOS' => '/iPhone|iPad/i',
            'Android' => '/Android/i',
            'Linux' => '/Linux/i'
        );
        
        foreach ($os_array as $os => $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return $os;
            }
        }
        
        return 'Other';
    }
    
    /**
     * Get anonymized IP address (GDPR compliant)
     */
    private function get_anonymized_ip() {
        $ip = $this->get_real_ip();
        if (!$ip) return null;
        
        // Anonymize IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }
        
        // Anonymize IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            for ($i = 4; $i < count($parts); $i++) {
                $parts[$i] = '0';
            }
            return implode(':', $parts);
        }
        
        return null;
    }
    
    /**
     * Get real IP address
     */
    private function get_real_ip() {
        $ip_keys = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }
    
    /**
     * Get country (placeholder - implement with GeoIP service if needed)
     */
    private function get_country() {
        // Implement GeoIP lookup if needed
        return null;
    }
    
    /**
     * Get region (placeholder - implement with GeoIP service if needed)
     */
    private function get_region() {
        // Implement GeoIP lookup if needed
        return null;
    }
    
    /**
     * Detect if request is from a bot
     */
    private function is_bot() {
        if (!get_option('nexus_links_bot_detection_enabled', true)) {
            return false;
        }
        
        $user_agent = $this->get_user_agent();
        if (!$user_agent) return false;
        
        $bot_patterns = array(
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/googlebot/i',
            '/bingbot/i',
            '/slurp/i',
            '/duckduckbot/i',
            '/baiduspider/i',
            '/yandexbot/i',
            '/facebookexternalhit/i',
            '/twitterbot/i',
            '/linkedinbot/i',
            '/whatsapp/i',
            '/telegrambot/i'
        );
        
        foreach ($bot_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get UTM parameter
     */
    private function get_utm_param($param) {
        if (!get_option('nexus_links_forward_utm_params', false)) {
            return null;
        }
        
        return isset($_GET[$param]) ? sanitize_text_field($_GET[$param]) : null;
    }
    
    /**
     * Get query parameters as JSON
     */
    private function get_query_params_json() {
        if (empty($_GET)) {
            return null;
        }
        
        $params = array();
        foreach ($_GET as $key => $value) {
            if ($key !== 'nexus_short_link') {
                $params[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }
        
        return !empty($params) ? json_encode($params) : null;
    }
    
    /**
     * Get analytics data for dashboard
     */
    public function get_dashboard_data($date_range = '30') {
        global $wpdb;
        
        $clicks_table = $wpdb->prefix . 'nexus_clicks';
        $links_table = $wpdb->prefix . 'nexus_links';
        
        $where_date = '';
        if ($date_range !== 'all') {
            $days = intval($date_range);
            $where_date = $wpdb->prepare(' AND c.timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)', $days);
        }
        
        // Total clicks
        $total_clicks = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM $clicks_table c 
            WHERE 1=1 $where_date
        ");
        
        // Unique clicks (by IP)
        $unique_clicks = $wpdb->get_var("
            SELECT COUNT(DISTINCT c.anonymized_ip) 
            FROM $clicks_table c 
            WHERE c.anonymized_ip IS NOT NULL $where_date
        ");
        
        // Bot clicks
        $bot_clicks = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM $clicks_table c 
            WHERE c.is_bot = 1 $where_date
        ");
        
        // Top referrers
        $top_referrers = $wpdb->get_results("
            SELECT referrer, COUNT(*) as clicks
            FROM $clicks_table c
            WHERE referrer IS NOT NULL 
                AND referrer != '' $where_date
            GROUP BY referrer
            ORDER BY clicks DESC
            LIMIT 10
        ");
        
        // Clicks by day
        $clicks_by_day = $wpdb->get_results("
            SELECT DATE(c.timestamp) as date, COUNT(*) as clicks
            FROM $clicks_table c
            WHERE 1=1 $where_date
            GROUP BY DATE(c.timestamp)
            ORDER BY date DESC
            LIMIT 30
        ");
        
        return array(
            'total_clicks' => $total_clicks,
            'unique_clicks' => $unique_clicks,
            'bot_clicks' => $bot_clicks,
            'human_clicks' => $total_clicks - $bot_clicks,
            'top_referrers' => $top_referrers,
            'clicks_by_day' => $clicks_by_day
        );
    }
}