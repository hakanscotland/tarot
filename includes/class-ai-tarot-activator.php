<?php
/**
 * Eklenti Aktivasyon
 * 
 * Bu sınıf, eklenti ilk kez etkinleştirildiğinde çalışır.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Tarot Aktivasyon sınıfı
 */
class AI_Tarot_Activator {
    
    /**
     * Eklenti etkinleştirildiğinde çalışacak fonksiyon
     */
    public static function activate() {
        // Veritabanı tablolarını oluştur
        self::create_tables();
        
        // Varsayılan kartları ekle
        self::populate_default_cards();
        
        // Varsayılan ayarları oluştur
        self::create_default_settings();
        
        // Gerekli dizinleri oluştur
        self::create_directories();
        
        // Örnek sayfayı oluştur
        self::create_example_page();
        
        // Veritabanı versiyonunu kaydet
        update_option('ai_tarot_db_version', '1.0');
        
        // Aktivasyon tarihini kaydet
        update_option('ai_tarot_activation_date', current_time('mysql'));
        
        // Eklentinin etkinleştirildiğine dair bir gösterge kaydet
        update_option('ai_tarot_activated', true);
        
        // Transient ekle (eklenti etkinleştirildikten sonra yönetici bildirimi göstermek için)
        set_transient('ai_tarot_activation_notice', true, 5);
        
        // Aktivasyon zamanlamasını ayarla
        if (!wp_next_scheduled('ai_tarot_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'ai_tarot_daily_cleanup');
        }
    }
    
    /**
     * Veritabanı tablolarını oluştur
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tarot kartları tablosu
        $table_cards = $wpdb->prefix . 'tarot_cards';
        
        // Tarot okumaları tablosu
        $table_readings = $wpdb->prefix . 'tarot_readings';
        
        // API log tablosu
        $table_api_logs = $wpdb->prefix . 'tarot_api_logs';
        
        // Hata log tablosu
        $table_error_logs = $wpdb->prefix . 'tarot_error_logs';
        
        // SQL sorguları
        $sql_cards = "CREATE TABLE $table_cards (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            card_type varchar(20) NOT NULL,
            suit varchar(20) NULL,
            number int(11) NULL,
            image_url varchar(255) NOT NULL,
            upright_meaning text NOT NULL,
            reversed_meaning text NOT NULL,
            keywords text NOT NULL,
            element varchar(20) NULL,
            astrological_sign varchar(20) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        $sql_readings = "CREATE TABLE $table_readings (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NULL,
            question text NOT NULL,
            spread_type varchar(50) NOT NULL,
            cards_data longtext NOT NULL,
            interpretation longtext NOT NULL,
            ai_service varchar(50) NULL,
            ip_address varchar(100) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        $sql_api_logs = "CREATE TABLE $table_api_logs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            service varchar(50) NOT NULL,
            endpoint varchar(255) NOT NULL,
            method varchar(10) NOT NULL,
            request_data longtext NULL,
            response_data longtext NULL,
            status tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        $sql_error_logs = "CREATE TABLE $table_error_logs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            message text NOT NULL,
            error_data longtext NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Tabloları oluştur
        dbDelta($sql_cards);
        dbDelta($sql_readings);
        dbDelta($sql_api_logs);
        dbDelta($sql_error_logs);
    }
    
    /**
     * Varsayılan kartları ekle
     */
    private static function populate_default_cards() {
        global $wpdb;
        
        // Kart tablosunun adı
        $table_cards = $wpdb->prefix . 'tarot_cards';
        
        // Tabloda kart var mı kontrol et
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_cards");
        
        if ($count > 0) {
            return; // Kartlar zaten var, tekrar ekleme
        }
        
        // Major Arcana kartları
        $major_arcana = array(
            array(
                'name' => 'The Fool',
                'card_type' => 'major',
                'number' => 0,
                'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/major/fool.jpg',
                'upright_meaning' => 'Yeni başlangıçlar, saf potansiyel, özgür ruh, macera, idealizm, saflık.',
                'reversed_meaning' => 'Dikkatsizlik, riskli davranışlar, sorumsuzluk, naiflik, özgür ruhluluğun eksikliği.',
                'keywords' => 'başlangıç, saflık, özgürlük, ruh, fırsatlar, macera, potansiyel',
                'element' => 'Hava',
                'astrological_sign' => 'Uranüs'
            ),
            array(
                'name' => 'The Magician',
                'card_type' => 'major',
                'number' => 1,
                'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/major/magician.jpg',
                'upright_meaning' => 'Güç, beceri, konsantrasyon, eylem, kararlılık, yaratıcılık, manifestasyon.',
                'reversed_meaning' => 'Manipülasyon, hilekârlık, iletişim kuramama, güç kaybı, beceriksizlik.',
                'keywords' => 'güç, yetenek, beceri, kaynak, konsantrasyon, manifestasyon, yaratıcılık',
                'element' => 'Hava',
                'astrological_sign' => 'Merkür'
            ),
            // Diğer Major Arcana kartları...
        );
        
        // Minor Arcana kartları (Kupalar, Asalar, Kılıçlar, Pentakıller)
        $minor_arcana_cups = array(
            array(
                'name' => 'Ace of Cups',
                'card_type' => 'minor',
                'suit' => 'cups',
                'number' => 1,
                'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/minor/cups/ace_of_cups.jpg',
                'upright_meaning' => 'Yeni duygular, sezgi, ilişkiler, yaratıcılık, ruhsallık, aşk, sevinç.',
                'reversed_meaning' => 'Bloke edilmiş yaratıcılık, duyusal dengesizlik, aşırı duygusallık, boşluk.',
                'keywords' => 'sevgi, sevinç, yaratıcılık, sezgi, ilişkiler, şefkat, bolluk',
                'element' => 'Su',
                'astrological_sign' => ''
            ),
            // Diğer Kupalar kartları...
        );
        
        $minor_arcana_wands = array(
            array(
                'name' => 'Ace of Wands',
                'card_type' => 'minor',
                'suit' => 'wands',
                'number' => 1,
                'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/minor/wands/ace_of_wands.jpg',
                'upright_meaning' => 'İlham, yaratıcılık, yeni başlangıçlar, girişim, büyüme potansiyeli, enerji.',
                'reversed_meaning' => 'Gecikme, erteleme, yetersiz hazırlık, projenin durması, yaratıcı blokaj.',
                'keywords' => 'yaratıcılık, ilham, yeni girişimler, enerji, tutkular, potansiyel, güç',
                'element' => 'Ateş',
                'astrological_sign' => ''
            ),
            // Diğer Asalar kartları...
        );
        
        $minor_arcana_swords = array(
            array(
                'name' => 'Ace of Swords',
                'card_type' => 'minor',
                'suit' => 'swords',
                'number' => 1,
                'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/minor/swords/ace_of_swords.jpg',
                'upright_meaning' => 'Zihinsel netlik, yeni fikirler, ilham, karar, gerçek, doğruluk, adalet.',
                'reversed_meaning' => 'Kafa karışıklığı, kaos, yıkıcı güç, kötüye kullanılmış zekâ, acı.',
                'keywords' => 'gerçek, netlik, zekâ, adalet, objektiflik, iletişim, fikir',
                'element' => 'Hava',
                'astrological_sign' => ''
            ),
            // Diğer Kılıçlar kartları...
        );
        
        $minor_arcana_pentacles = array(
            array(
                'name' => 'Ace of Pentacles',
                'card_type' => 'minor',
                'suit' => 'pentacles',
                'number' => 1,
                'image_url' => AI_TAROT_PLUGIN_URL . 'public/images/cards/minor/pentacles/ace_of_pentacles.jpg',
                'upright_meaning' => 'Maddi imkanlar, refah, bolluk, güvenlik, fiziksel sağlık, yeni girişimler.',
                'reversed_meaning' => 'Kaçırılan fırsatlar, kayıplar, maddi zorluklar, açgözlülük, yanlış yatırım.',
                'keywords' => 'zenginlik, refah, bolluk, emniyet, güvenlik, fiziksel dünya, dünya zevkleri',
                'element' => 'Toprak',
                'astrological_sign' => ''
            ),
            // Diğer Pentakıller kartları...
        );
        
        // Tüm kartları birleştir
        $all_cards = array_merge(
            $major_arcana,
            $minor_arcana_cups,
            $minor_arcana_wands,
            $minor_arcana_swords,
            $minor_arcana_pentacles
        );
        
        // Kartları veritabanına ekle
        foreach ($all_cards as $card) {
            $wpdb->insert($table_cards, $card);
        }
    }
    
    /**
     * Varsayılan ayarları oluştur
     */
    private static function create_default_settings() {
        // Mevcut ayarları kontrol et
        $existing_settings = get_option('ai_tarot_settings');
        
        if ($existing_settings) {
            return; // Ayarlar zaten var
        }
        
        // Varsayılan ayarlar
        $default_settings = array(
            'ai_service' => 'openai',
            'openai_model' => 'gpt-4',
            'openai_temperature' => 0.7,
            'openai_max_tokens' => 2000,
            'claude_model' => 'claude-3-opus-20240229',
            'claude_temperature' => 0.7,
            'claude_max_tokens' => 2000,
            'gemini_model' => 'gemini-1.5-pro',
            'gemini_temperature' => 0.7,
            'gemini_max_tokens' => 2000,
            'perplexity_model' => 'pplx-7b-online',
            'perplexity_temperature' => 0.7,
            'perplexity_max_tokens' => 2000,
            'deepseek_model' => 'deepseek-chat',
            'deepseek_temperature' => 0.7,
            'deepseek_max_tokens' => 2000,
            'grok_temperature' => 0.7,
            'grok_max_tokens' => 2000,
            'meta_model' => 'llama-3-70b-instruct',
            'meta_temperature' => 0.7,
            'meta_max_tokens' => 2000,
            'copilot_temperature' => 0.7,
            'copilot_max_tokens' => 2000,
            'enable_animations' => 1,
            'auto_save' => 1,
            'card_deck_theme' => 'classic',
            'enable_logging' => 0,
            'enable_api_failover' => 1,
            'cache_duration' => 7,
            'data_retention' => 90,
            'system_prompts' => array(
                'openai' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.',
                'claude' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.',
                'gemini' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.',
                'perplexity' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.',
                'deepseek' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.',
                'grok' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.',
                'meta' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.',
                'copilot' => 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.'
            )
        );
        
        // Ayarları kaydet
        update_option('ai_tarot_settings', $default_settings);
    }
    
    /**
     * Gerekli dizinleri oluştur
     */
    private static function create_directories() {
        // Log dizini
        $log_dir = AI_TAROT_PLUGIN_DIR . 'logs';
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
            
            // İndex.php dosyası oluştur (dizin listesini engellemek için)
            file_put_contents($log_dir . '/index.php', '<?php // Silence is golden');
            
            // .htaccess dosyası oluştur (güvenlik için)
            file_put_contents($log_dir . '/.htaccess', 'Deny from all');
        }
        
        // Kart görselleri için dizinler
        $card_dirs = array(
            AI_TAROT_PLUGIN_DIR . 'public/images/cards',
            AI_TAROT_PLUGIN_DIR . 'public/images/cards/major',
            AI_TAROT_PLUGIN_DIR . 'public/images/cards/minor',
            AI_TAROT_PLUGIN_DIR . 'public/images/cards/minor/cups',
            AI_TAROT_PLUGIN_DIR . 'public/images/cards/minor/wands',
            AI_TAROT_PLUGIN_DIR . 'public/images/cards/minor/swords',
            AI_TAROT_PLUGIN_DIR . 'public/images/cards/minor/pentacles'
        );
        
        foreach ($card_dirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                
                // İndex.php dosyası oluştur
                file_put_contents($dir . '/index.php', '<?php // Silence is golden');
            }
        }
    }
    
    /**
     * Örnek sayfayı oluştur
     */
    private static function create_example_page() {
        // Tarot falı sayfası var mı kontrol et
        $tarot_page = get_page_by_path('tarot-fali');
        
        if (!$tarot_page) {
            // Sayfayı oluştur
            $page_id = wp_insert_post(array(
                'post_title' => 'AI Tarot Falı',
                'post_content' => '[ai_tarot]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'tarot-fali'
            ));
            
            // Sayfa oluşturulduğunda ayarlara ekle
            if ($page_id) {
                update_option('ai_tarot_page_id', $page_id);
            }
        }
    }
}