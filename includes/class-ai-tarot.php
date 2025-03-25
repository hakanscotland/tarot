<?php
/**
 * Ana Eklenti Sınıfı
 * 
 * Bu sınıf, eklentinin ana işlevlerini tanımlar ve tüm bileşenleri birleştirir.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Tarot ana sınıfı
 */
class AI_Tarot {
    
    /**
     * Yükleyici
     */
    protected $loader;
    
    /**
     * Eklenti versiyonu
     */
    protected $version;
    
    /**
     * Eklenti veritabanı versiyonu
     */
    protected $db_version;
    
    /**
     * Sınıfı başlat
     */
    public function __construct() {
        $this->version = AI_TAROT_VERSION;
        $this->db_version = '1.0';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Gerekli bağımlılıkları yükle
     */
    private function load_dependencies() {
        // Yükleyici sınıfı
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-loader.php';
        
        // Aktivasyon ve deaktivasyon sınıfları
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-activator.php';
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-deactivator.php';
        
        // API entegrasyon sınıfı
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-api-integration.php';
        
        // API test sınıfı
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-api-testing.php';
        
        // Admin sınıfı
        require_once AI_TAROT_PLUGIN_DIR . 'admin/class-ai-tarot-admin.php';
        
        // Frontend sınıfı
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-frontend.php';
        
        // Backend işlevleri sınıfı
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-backend.php';
        
        // Yükleyiciyi oluştur
        $this->loader = new AI_Tarot_Loader();
    }
    
    /**
     * Admin kancalarını tanımla
     */
    private function define_admin_hooks() {
        $admin = new AI_Tarot_Admin();
        
        // Admin menü ve stil
        $this->loader->add_action('admin_menu', $admin, 'register_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_admin_scripts');
        
        // AJAX istekleri
        $this->loader->add_action('wp_ajax_get_card_details', $admin, 'ajax_get_card_details');
        $this->loader->add_action('wp_ajax_save_card', $admin, 'ajax_save_card');
        $this->loader->add_action('wp_ajax_delete_card', $admin, 'ajax_delete_card');
        $this->loader->add_action('wp_ajax_add_missing_cards', $admin, 'ajax_add_missing_cards');
        $this->loader->add_action('wp_ajax_update_card_images', $admin, 'ajax_update_card_images');
        
        $this->loader->add_action('wp_ajax_get_reading_details', $admin, 'ajax_get_reading_details');
        $this->loader->add_action('wp_ajax_delete_reading', $admin, 'ajax_delete_reading');
        
        $this->loader->add_action('wp_ajax_export_tarot_stats', $admin, 'ajax_export_tarot_stats');
        
        $this->loader->add_action('wp_ajax_ai_tarot_create_page', $admin, 'ajax_create_tarot_page');
        
        // API test kancaları
        $api_testing = new AI_Tarot_API_Testing();
        $this->loader->add_action('wp_ajax_ai_tarot_test_connection', $api_testing, 'test_connection');
    }
    
    /**
     * Frontend kancalarını tanımla
     */
    private function define_public_hooks() {
        $frontend = new AI_Tarot_Frontend();
        $backend = new AI_Tarot_Backend();
        
        // Frontend stil ve script
        $this->loader->add_action('wp_enqueue_scripts', $frontend, 'enqueue_scripts');
        
        // Kısa kod
        $this->loader->add_shortcode('ai_tarot', $frontend, 'render_tarot_form');
        
        // AJAX istekleri
        $this->loader->add_action('wp_ajax_get_tarot_reading', $backend, 'process_tarot_reading');
        $this->loader->add_action('wp_ajax_nopriv_get_tarot_reading', $backend, 'process_tarot_reading');
        
        $this->loader->add_action('wp_ajax_save_tarot_reading', $backend, 'save_user_reading');
        $this->loader->add_action('wp_ajax_nopriv_save_tarot_reading', $backend, 'save_guest_reading');
        
        // Kullanıcı profil sayfası
        $this->loader->add_action('show_user_profile', $frontend, 'render_user_readings');
        $this->loader->add_action('edit_user_profile', $frontend, 'render_user_readings');
    }
    
    /**
     * Eklentiyi çalıştır
     */
    public function run() {
        $this->loader->run();
    }
    
    /**
     * Eklenti versiyonu
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Veritabanı versiyonu
     */
    public function get_db_version() {
        return $this->db_version;
    }
    
    /**
     * Eklenti etkinleştirildiğinde çalışacak fonksiyon
     */
    public static function activate() {
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-activator.php';
        AI_Tarot_Activator::activate();
    }
    
    /**
     * Eklenti devre dışı bırakıldığında çalışacak fonksiyon
     */
    public static function deactivate() {
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-deactivator.php';
        AI_Tarot_Deactivator::deactivate();
    }
    
    /**
     * Eklenti kaldırıldığında çalışacak fonksiyon
     */
    public static function uninstall() {
        require_once AI_TAROT_PLUGIN_DIR . 'includes/class-ai-tarot-deactivator.php';
        AI_Tarot_Deactivator::uninstall();
    }
    
    /**
     * Veritabanını güncelle
     */
    public function update_database() {
        $current_db_version = get_option('ai_tarot_db_version', '1.0');
        
        if (version_compare($current_db_version, $this->db_version, '<')) {
            // Güncelleme işlemleri
            update_option('ai_tarot_db_version', $this->db_version);
        }
    }
    
    /**
     * Log kayıtları
     */
    public static function log($message, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            // Log mesajını hazırla
            $log_message = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . PHP_EOL;
            
            // Log dosyasına yaz
            $log_file = AI_TAROT_PLUGIN_DIR . 'logs/debug.log';
            
            // Log dizini yoksa oluştur
            if (!file_exists(dirname($log_file))) {
                wp_mkdir_p(dirname($log_file));
            }
            
            // Log dosyasına yaz
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }
    }
    
    /**
     * API isteklerini ve yanıtlarını kaydet
     */
    public static function log_api_request($service, $endpoint, $method, $request_data, $response_data, $status) {
        // Ayarlardan log tutma özelliğini kontrol et
        $settings = get_option('ai_tarot_settings', array());
        
        if (!isset($settings['enable_logging']) || $settings['enable_logging'] != 1) {
            return;
        }
        
        global $wpdb;
        
        // Parametreleri hazırla
        $request_json = is_array($request_data) || is_object($request_data) ? json_encode($request_data) : $request_data;
        $response_json = is_array($response_data) || is_object($response_data) ? json_encode($response_data) : $response_data;
        
        // Veritabanına kaydet
        $wpdb->insert(
            $wpdb->prefix . 'tarot_api_logs',
            array(
                'service' => $service,
                'endpoint' => $endpoint,
                'method' => $method,
                'request_data' => $request_json,
                'response_data' => $response_json,
                'status' => $status ? 1 : 0,
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Hata kayıtlarını tut
     */
    public static function log_error($message, $error_data = array()) {
        global $wpdb;
        
        // Hata verilerini hazırla
        $error_json = is_array($error_data) || is_object($error_data) ? json_encode($error_data) : $error_data;
        
        // Veritabanına kaydet
        $wpdb->insert(
            $wpdb->prefix . 'tarot_error_logs',
            array(
                'message' => $message,
                'error_data' => $error_json,
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Önbellek kontrolü
     */
    public static function check_cache($key) {
        // Ayarlardan önbellek süresini al
        $settings = get_option('ai_tarot_settings', array());
        $cache_duration = isset($settings['cache_duration']) ? intval($settings['cache_duration']) : 7;
        
        // Önbellek devre dışı bırakılmışsa
        if ($cache_duration === 0) {
            return false;
        }
        
        // Önbellekten veriyi al
        $cached_data = get_transient('ai_tarot_' . $key);
        
        return $cached_data;
    }
    
    /**
     * Önbelleğe veri ekle
     */
    public static function set_cache($key, $data) {
        // Ayarlardan önbellek süresini al
        $settings = get_option('ai_tarot_settings', array());
        $cache_duration = isset($settings['cache_duration']) ? intval($settings['cache_duration']) : 7;
        
        // Önbellek devre dışı bırakılmışsa
        if ($cache_duration === 0) {
            return;
        }
        
        // Süreyi saat cinsinden hesapla
        $expiration = $cache_duration * DAY_IN_SECONDS;
        
        // Önbelleğe ekle
        set_transient('ai_tarot_' . $key, $data, $expiration);
    }
    
    /**
     * Önbellekten veri sil
     */
    public static function delete_cache($key) {
        delete_transient('ai_tarot_' . $key);
    }
    
    /**
     * Tüm önbelleği temizle
     */
    public static function clear_all_cache() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_ai_tarot_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_ai_tarot_%'");
    }
    
    /**
     * Eski verileri temizle
     */
    public function cleanup_old_data() {
        // Ayarlardan veri saklama süresini al
        $settings = get_option('ai_tarot_settings', array());
        $data_retention = isset($settings['data_retention']) ? intval($settings['data_retention']) : 90;
        
        // Veri saklama süresi sınırsız ise
        if ($data_retention === 0) {
            return;
        }
        
        global $wpdb;
        
        // Belirtilen günden eski okuma kayıtlarını sil
        $date = date('Y-m-d', strtotime("-$data_retention days"));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}tarot_readings WHERE created_at < %s",
            $date . ' 00:00:00'
        ));
        
        // API ve hata kayıtlarını da temizle
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}tarot_api_logs WHERE created_at < %s",
            $date . ' 00:00:00'
        ));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}tarot_error_logs WHERE created_at < %s",
            $date . ' 00:00:00'
        ));
    }
}