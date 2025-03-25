<?php
/**
 * AI Tarot Admin Sınıfı
 * 
 * Bu sınıf, eklentinin admin panelini ve işlevlerini yönetir.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

class AI_Tarot_Admin {
    
    /**
     * Ayarlar
     */
    private $settings;
    
    /**
     * Sınıfı başlat
     */
    public function __construct() {
        // Ayarları yükle
        $this->settings = get_option('ai_tarot_settings', array());
        
        // Admin ortamında gerekli sınıfları yükle
        $this->load_dependencies();
    }
    
    /**
     * Bağımlılıkları yükle
     */
    private function load_dependencies() {
        // Ayarlar sınıfını dahil et
        require_once AI_TAROT_PLUGIN_DIR . 'admin/class-ai-tarot-settings.php';
        
        // API Test sınıfını dahil et
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-api-testing.php';
    }
    
    /**
     * Admin menüyü kaydet
     */
    public function register_admin_menu() {
        // Ana menü sayfası
        add_menu_page(
            'AI Tarot Falı', 
            'AI Tarot', 
            'manage_options',
            'ai-tarot-settings',
            array($this, 'render_admin_page'),
            'dashicons-visibility',
            30
        );
        
        // Alt sayfalar
        add_submenu_page(
            'ai-tarot-settings',
            'Tarot Ayarları',
            'Ayarlar',
            'manage_options',
            'ai-tarot-settings'
        );
        
        add_submenu_page(
            'ai-tarot-settings',
            'Tarot Kartları',
            'Kartlar',
            'manage_options',
            'ai-tarot-cards',
            array($this, 'render_cards_page')
        );
        
        add_submenu_page(
            'ai-tarot-settings',
            'Tarot Geçmişi',
            'Fal Geçmişi',
            'manage_options',
            'ai-tarot-history',
            array($this, 'render_history_page')
        );
        
        add_submenu_page(
            'ai-tarot-settings',
            'Tarot İstatistikleri',
            'İstatistikler',
            'manage_options',
            'ai-tarot-stats',
            array($this, 'render_stats_page')
        );
        
        add_submenu_page(
            'ai-tarot-settings',
            'Tarot Yardım',
            'Yardım',
            'manage_options',
            'ai-tarot-help',
            array($this, 'render_help_page')
        );
    }
    
    /**
     * Admin script ve stil dosyalarını ekle
     */
    public function enqueue_admin_scripts($hook) {
        // Sadece eklenti sayfalarında script ve stil dosyalarını yükle
        if (strpos($hook, 'ai-tarot') === false) {
            return;
        }
        
        // Admin stil dosyasını ekle
        wp_enqueue_style(
            'ai-tarot-admin',
            AI_TAROT_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            AI_TAROT_VERSION,
            'all'
        );
        
        // Admin script dosyasını ekle
        wp_enqueue_script(
            'ai-tarot-admin',
            AI_TAROT_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            AI_TAROT_VERSION,
            true
        );
        
        // Script için verileri hazırla
        wp_localize_script(
            'ai-tarot-admin',
            'ai_tarot_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_tarot_admin_nonce'),
                'delete_confirm' => __('Bu öğeyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.', 'ai-tarot'),
                'delete_selected_confirm' => __('Seçilen öğeleri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.', 'ai-tarot')
            )
        );
        
        // WordPress medya yükleyiciyi ekle
        wp_enqueue_media();
        
        // WordPress renk seçici ekle
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // WordPress tarih seçici ekle
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        
        // Grafik kütüphanesi ekle
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js', array(), '3.7.1', true);
    }
    
    /**
     * Admin ana sayfasını göster
     */
    public function render_admin_page() {
        // Admin sayfası şablonunu yükle
        include AI_TAROT_PLUGIN_DIR . 'admin/partials/admin-page.php';
    }
    
    /**
     * Kartlar sayfasını göster
     */
    public function render_cards_page() {
        // Kart yönetimi sayfasını yükle
        include AI_TAROT_PLUGIN_DIR . 'admin/partials/cards-page.php';
    }
    
    /**
     * Fal geçmişi sayfasını göster
     */
    public function render_history_page() {
        // Fal geçmişi sayfasını yükle
        include AI_TAROT_PLUGIN_DIR . 'admin/partials/history-page.php';
    }
    
    /**
     * İstatistikler sayfasını göster
     */
    public function render_stats_page() {
        // İstatistikler sayfasını yükle
        include AI_TAROT_PLUGIN_DIR . 'admin/partials/stats-page.php';
    }
    
    /**
     * Yardım sayfasını göster
     */
    public function render_help_page() {
        // Yardım sayfasını yükle
        include AI_TAROT_PLUGIN_DIR . 'admin/partials/help-page.php';
    }
    
    /**
     * Tarot kartlarını getir
     */
    public function get_tarot_cards() {
        global $wpdb;
        
        // Kartları veritabanından al
        $cards = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}tarot_cards ORDER BY card_type DESC, suit ASC, number ASC",
            ARRAY_A
        );
        
        return $cards;
    }
    
    /**
     * Yeni kart ekle veya mevcut kartı güncelle
     */
    public function save_tarot_card($card_data) {
        global $wpdb;
        
        // Kart ID'si kontrolü
        $card_id = isset($card_data['id']) ? intval($card_data['id']) : 0;
        
        // Kart verilerini hazırla
        $card = array(
            'name' => sanitize_text_field($card_data['name']),
            'card_type' => sanitize_text_field($card_data['card_type']),
            'suit' => sanitize_text_field($card_data['suit']),
            'number' => intval($card_data['number']),
            'image_url' => esc_url_raw($card_data['image_url']),
            'upright_meaning' => sanitize_textarea_field($card_data['upright_meaning']),
            'reversed_meaning' => sanitize_textarea_field($card_data['reversed_meaning']),
            'keywords' => sanitize_textarea_field($card_data['keywords']),
            'element' => sanitize_text_field($card_data['element']),
            'astrological_sign' => sanitize_text_field($card_data['astrological_sign'])
        );
        
        // Yeni kart mı, güncelleme mi?
        if ($card_id > 0) {
            // Kartı güncelle
            $result = $wpdb->update(
                $wpdb->prefix . 'tarot_cards',
                $card,
                array('id' => $card_id)
            );
            
            if ($result !== false) {
                return array(
                    'success' => true,
                    'message' => __('Kart başarıyla güncellendi.', 'ai-tarot'),
                    'card_id' => $card_id
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Kart güncellenirken bir hata oluştu.', 'ai-tarot')
                );
            }
        } else {
            // Yeni kart ekle
            $result = $wpdb->insert(
                $wpdb->prefix . 'tarot_cards',
                $card
            );
            
            if ($result !== false) {
                return array(
                    'success' => true,
                    'message' => __('Kart başarıyla eklendi.', 'ai-tarot'),
                    'card_id' => $wpdb->insert_id
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Kart eklenirken bir hata oluştu.', 'ai-tarot')
                );
            }
        }
    }
    
    /**
     * Kart sil
     */
    public function delete_tarot_card($card_id) {
        global $wpdb;
        
        // Kartı sil
        $result = $wpdb->delete(
            $wpdb->prefix . 'tarot_cards',
            array('id' => $card_id),
            array('%d')
        );
        
        if ($result !== false) {
            return array(
                'success' => true,
                'message' => __('Kart başarıyla silindi.', 'ai-tarot')
            );
        } else {
            return array(
                'success' => false,
                'message' => __('Kart silinirken bir hata oluştu.', 'ai-tarot')
            );
        }
    }
    
    /**
     * Tarot falı geçmişini getir
     */
    public function get_tarot_readings($per_page = 20, $page = 1, $filters = array()) {
        global $wpdb;
        
        // Sayfalama
        $offset = ($page - 1) * $per_page;
        
        // SQL sorgusunu hazırla
        $sql = "SELECT r.*, u.display_name as user_name 
                FROM {$wpdb->prefix}tarot_readings r 
                LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID";
        
        // Filtre koşulları
        $where_clauses = array();
        $sql_params = array();
        
        // Kullanıcı ID'si filtresi
        if (isset($filters['user_id']) && $filters['user_id'] > 0) {
            $where_clauses[] = "r.user_id = %d";
            $sql_params[] = intval($filters['user_id']);
        }
        
        // Açılım türü filtresi
        if (isset($filters['spread_type']) && !empty($filters['spread_type'])) {
            $where_clauses[] = "r.spread_type = %s";
            $sql_params[] = sanitize_text_field($filters['spread_type']);
        }
        
        // Tarih filtresi
        if (isset($filters['start_date']) && !empty($filters['start_date'])) {
            $where_clauses[] = "r.created_at >= %s";
            $sql_params[] = sanitize_text_field($filters['start_date'] . ' 00:00:00');
        }
        
        if (isset($filters['end_date']) && !empty($filters['end_date'])) {
            $where_clauses[] = "r.created_at <= %s";
            $sql_params[] = sanitize_text_field($filters['end_date'] . ' 23:59:59');
        }
        
        // Arama filtresi
        if (isset($filters['search']) && !empty($filters['search'])) {
            $where_clauses[] = "(r.question LIKE %s OR r.interpretation LIKE %s)";
            $search_term = '%' . $wpdb->esc_like(sanitize_text_field($filters['search'])) . '%';
            $sql_params[] = $search_term;
            $sql_params[] = $search_term;
        }
        
        // WHERE koşullarını ekle
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        // Sıralama
        $sql .= " ORDER BY r.created_at DESC";
        
        // Sayfalama
        $sql .= " LIMIT %d OFFSET %d";
        $sql_params[] = $per_page;
        $sql_params[] = $offset;
        
        // SQL sorgusunu hazırla
        $query = $wpdb->prepare($sql, $sql_params);
        
        // Sorguyu çalıştır
        $readings = $wpdb->get_results($query, ARRAY_A);
        
        // Toplam kayıt sayısını al
        $sql_count = "SELECT COUNT(*) FROM {$wpdb->prefix}tarot_readings r";
        if (!empty($where_clauses)) {
            $sql_count .= " WHERE " . implode(" AND ", $where_clauses);
        }
        $query_count = $wpdb->prepare($sql_count, array_slice($sql_params, 0, count($sql_params) - 2));
        $total_items = $wpdb->get_var($query_count);
        
        return array(
            'readings' => $readings,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $per_page)
        );
    }
    
    /**
     * Tarot falı sil
     */
    public function delete_tarot_reading($reading_id) {
        global $wpdb;
        
        // Falı sil
        $result = $wpdb->delete(
            $wpdb->prefix . 'tarot_readings',
            array('id' => $reading_id),
            array('%d')
        );
        
        if ($result !== false) {
            return array(
                'success' => true,
                'message' => __('Fal geçmişi başarıyla silindi.', 'ai-tarot')
            );
        } else {
            return array(
                'success' => false,
                'message' => __('Fal geçmişi silinirken bir hata oluştu.', 'ai-tarot')
            );
        }
    }
    
    /**
     * İstatistikleri getir
     */
    public function get_tarot_statistics() {
        global $wpdb;
        
        // Toplam fal sayısı
        $total_readings = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tarot_readings");
        
        // Açılım türlerine göre fal sayıları
        $spread_stats = $wpdb->get_results(
            "SELECT spread_type, COUNT(*) as count 
             FROM {$wpdb->prefix}tarot_readings 
             GROUP BY spread_type 
             ORDER BY count DESC",
            ARRAY_A
        );
        
        // Aylık fal sayıları (son 12 ay)
        $monthly_stats = $wpdb->get_results(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
             FROM {$wpdb->prefix}tarot_readings 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
             GROUP BY month 
             ORDER BY month ASC",
            ARRAY_A
        );
        
        // En çok çekilen kartlar
        $card_stats = $wpdb->get_results(
            "SELECT c.name, COUNT(c.id) as count 
             FROM {$wpdb->prefix}tarot_cards c 
             JOIN (
                SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(cards_data, '\"id\":', -1), ',', 1) as card_id
                FROM {$wpdb->prefix}tarot_readings
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
             ) as card_refs ON c.id = card_refs.card_id
             GROUP BY c.id 
             ORDER BY count DESC 
             LIMIT 10",
            ARRAY_A
        );
        
        // AI servis kullanım istatistikleri
        $ai_service_stats = $wpdb->get_results(
            "SELECT 
                CASE 
                    WHEN settings LIKE '%\"ai_service\":\"openai\"%' THEN 'OpenAI'
                    WHEN settings LIKE '%\"ai_service\":\"claude\"%' THEN 'Claude'
                    WHEN settings LIKE '%\"ai_service\":\"gemini\"%' THEN 'Gemini'
                    WHEN settings LIKE '%\"ai_service\":\"perplexity\"%' THEN 'Perplexity'
                    WHEN settings LIKE '%\"ai_service\":\"deepseek\"%' THEN 'DeepSeek'
                    WHEN settings LIKE '%\"ai_service\":\"grok\"%' THEN 'Grok'
                    WHEN settings LIKE '%\"ai_service\":\"meta\"%' THEN 'Meta'
                    WHEN settings LIKE '%\"ai_service\":\"copilot\"%' THEN 'Copilot'
                    ELSE 'Diğer'
                END as ai_service,
                COUNT(*) as count
             FROM {$wpdb->prefix}options
             WHERE option_name = 'ai_tarot_settings'
             GROUP BY ai_service
             ORDER BY count DESC",
            ARRAY_A
        );
        
        return array(
            'total_readings' => $total_readings,
            'spread_stats' => $spread_stats,
            'monthly_stats' => $monthly_stats,
            'card_stats' => $card_stats,
            'ai_service_stats' => $ai_service_stats
        );
    }
    
    /**
     * API kullanım kayıtlarını getir
     */
    public function get_api_usage_logs($per_page = 20, $page = 1) {
        global $wpdb;
        
        // Sayfalama
        $offset = ($page - 1) * $per_page;
        
        // API kullanım kayıtlarını getir
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tarot_api_logs 
                 ORDER BY created_at DESC 
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
        
        // Toplam kayıt sayısını al
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tarot_api_logs");
        
        return array(
            'logs' => $logs,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $per_page)
        );
    }
    
    /**
     * Hata kayıtlarını getir
     */
    public function get_error_logs($per_page = 20, $page = 1) {
        global $wpdb;
        
        // Sayfalama
        $offset = ($page - 1) * $per_page;
        
        // Hata kayıtlarını getir
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tarot_error_logs 
                 ORDER BY created_at DESC 
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
        
        // Toplam kayıt sayısını al
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tarot_error_logs");
        
        return array(
            'logs' => $logs,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $per_page)
        );
    }
    
    /**
     * Sistem bilgilerini getir
     */
    public function get_system_info() {
        // WordPress bilgileri
        $wp_info = array(
            'version' => get_bloginfo('version'),
            'site_url' => get_bloginfo('url'),
            'is_multisite' => is_multisite() ? 'Evet' : 'Hayır',
            'memory_limit' => WP_MEMORY_LIMIT,
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG ? 'Açık' : 'Kapalı'
        );
        
        // PHP bilgileri
        $php_info = array(
            'version' => phpversion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'curl_enabled' => function_exists('curl_version') ? 'Evet' : 'Hayır',
            'json_enabled' => function_exists('json_decode') ? 'Evet' : 'Hayır'
        );
        
        // Server bilgileri
        $server_info = array(
            'software' => $_SERVER['SERVER_SOFTWARE'],
            'os' => PHP_OS
        );
        
        // Eklenti bilgileri
        $plugin_info = array(
            'version' => AI_TAROT_VERSION,
            'db_version' => get_option('ai_tarot_db_version', '1.0')
        );
        
        return array(
            'wp' => $wp_info,
            'php' => $php_info,
            'server' => $server_info,
            'plugin' => $plugin_info
        );
    }
    
    /**
     * Kart görsellerini toplu güncelle
     */
    public function update_card_images($images_data) {
        global $wpdb;
        
        $updated = 0;
        $failed = 0;
        
        foreach ($images_data as $card_id => $image_url) {
            $result = $wpdb->update(
                $wpdb->prefix . 'tarot_cards',
                array('image_url' => esc_url_raw($image_url)),
                array('id' => intval($card_id)),
                array('%s'),
                array('%d')
            );
            
            if ($result !== false) {
                $updated++;
            } else {
                $failed++;
            }
        }
        
        return array(
            'success' => $updated > 0,
            'updated' => $updated,
            'failed' => $failed,
            'message' => sprintf(
                __('%d kart görseli başarıyla güncellendi. %d güncelleme başarısız oldu.', 'ai-tarot'),
                $updated,
                $failed
            )
        );
    }
    
    /**
     * Kullanıcı listesini getir
     */
    public function get_users_list() {
        // Kullanıcıları al
        $users = get_users(array(
            'fields' => array('ID', 'display_name')
        ));
        
        return $users;
    }
    
    /**
     * Eksik kartları ekle
     */
    public function add_missing_cards() {
        global $wpdb;
        
        // Mevcut kartları al
        $existing_cards = $wpdb->get_col("SELECT name FROM {$wpdb->prefix}tarot_cards");
        
        // Eksik kartları ekle
        $cards_added = 0;
        
        // Major Arcana kontrolü
        $major_arcana = array(
            'The Fool', 'The Magician', 'The High Priestess', 'The Empress', 'The Emperor',
            'The Hierophant', 'The Lovers', 'The Chariot', 'Strength', 'The Hermit',
            'Wheel of Fortune', 'Justice', 'The Hanged Man', 'Death', 'Temperance',
            'The Devil', 'The Tower', 'The Star', 'The Moon', 'The Sun',
            'Judgement', 'The World'
        );
        
        foreach ($major_arcana as $index => $card_name) {
            if (!in_array($card_name, $existing_cards)) {
                $wpdb->insert(
                    $wpdb->prefix . 'tarot_cards',
                    array(
                        'name' => $card_name,
                        'card_type' => 'major',
                        'number' => $index,
                        'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/major/' . sanitize_title($card_name) . '.jpg',
                        'upright_meaning' => '',
                        'reversed_meaning' => '',
                        'keywords' => '',
                        'element' => '',
                        'astrological_sign' => '',
                        'created_at' => current_time('mysql')
                    )
                );
                
                $cards_added++;
            }
        }
        
        // Minor Arcana kontrolü
        $suits = array('cups', 'wands', 'swords', 'pentacles');
        $ranks = array(
            'Ace', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Page', 'Knight', 'Queen', 'King'
        );
        
        foreach ($suits as $suit) {
            foreach ($ranks as $index => $rank) {
                $card_name = $rank . ' of ' . ucfirst($suit);
                
                if (!in_array($card_name, $existing_cards)) {
                    $wpdb->insert(
                        $wpdb->prefix . 'tarot_cards',
                        array(
                            'name' => $card_name,
                            'card_type' => 'minor',
                            'suit' => $suit,
                            'number' => $index + 1,
                            'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/minor/' . $suit . '/' . sanitize_title($card_name) . '.jpg',
                            'upright_meaning' => '',
                            'reversed_meaning' => '',
                            'keywords' => '',
                            'element' => $this->get_element_for_suit($suit),
                            'astrological_sign' => '',
                            'created_at' => current_time('mysql')
                        )
                    );
                    
                    $cards_added++;
                }
            }
        }
        
        return array(
            'success' => true,
            'cards_added' => $cards_added,
            'message' => sprintf(__('%d eksik kart eklendi.', 'ai-tarot'), $cards_added)
        );
    }
    
    /**
     * Her suit için element al
     */
    private function get_element_for_suit($suit) {
        switch ($suit) {
            case 'cups':
                return 'Su';
            case 'wands':
                return 'Ateş';
            case 'swords':
                return 'Hava';
            case 'pentacles':
                return 'Toprak';
            default:
                return '';
        }
    }
}