<?php
/**
 * Eklenti Deaktivasyon
 * 
 * Bu sınıf, eklenti devre dışı bırakıldığında veya kaldırıldığında çalışır.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Tarot Deaktivasyon sınıfı
 */
class AI_Tarot_Deactivator {
    
    /**
     * Eklenti devre dışı bırakıldığında çalışacak fonksiyon
     */
    public static function deactivate() {
        // Zamanlanmış görevleri temizle
        wp_clear_scheduled_hook('ai_tarot_daily_cleanup');
        
        // Deaktivasyon tarihini kaydet
        update_option('ai_tarot_deactivation_date', current_time('mysql'));
        
        // Eklentinin devre dışı bırakıldığına dair bir gösterge kaydet
        update_option('ai_tarot_active', false);
        
        // Önbelleği temizle
        self::clear_cache();
    }
    
    /**
     * Eklenti kaldırıldığında çalışacak fonksiyon
     */
    public static function uninstall() {
        // Ayarlardan tamamen kaldırma seçeneğini kontrol et
        $settings = get_option('ai_tarot_settings');
        $complete_uninstall = isset($settings['complete_uninstall']) ? $settings['complete_uninstall'] : false;
        
        if ($complete_uninstall) {
            // Veritabanı tablolarını kaldır
            self::drop_tables();
            
            // Eklenti ayarlarını ve seçeneklerini kaldır
            self::remove_options();
            
            // Zamanlanmış görevleri temizle
            wp_clear_scheduled_hook('ai_tarot_daily_cleanup');
            
            // Önbelleği temizle
            self::clear_cache();
            
            // Eklenti klasörünü temizle
            self::clean_directories();
        }
    }
    
    /**
     * Veritabanı tablolarını kaldır
     */
    private static function drop_tables() {
        global $wpdb;
        
        // Tablo adları
        $table_cards = $wpdb->prefix . 'tarot_cards';
        $table_readings = $wpdb->prefix . 'tarot_readings';
        $table_api_logs = $wpdb->prefix . 'tarot_api_logs';
        $table_error_logs = $wpdb->prefix . 'tarot_error_logs';
        
        // Tabloları kaldır
        $wpdb->query("DROP TABLE IF EXISTS $table_cards");
        $wpdb->query("DROP TABLE IF EXISTS $table_readings");
        $wpdb->query("DROP TABLE IF EXISTS $table_api_logs");
        $wpdb->query("DROP TABLE IF EXISTS $table_error_logs");
    }
    
    /**
     * Eklenti ayarlarını ve seçeneklerini kaldır
     */
    private static function remove_options() {
        delete_option('ai_tarot_settings');
        delete_option('ai_tarot_db_version');
        delete_option('ai_tarot_activation_date');
        delete_option('ai_tarot_deactivation_date');
        delete_option('ai_tarot_activated');
        delete_option('ai_tarot_active');
        delete_option('ai_tarot_page_id');
        
        // Önbellekteki tüm seçenekleri temizle
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%ai_tarot%'");
    }
    
    /**
     * Önbelleği temizle
     */
    private static function clear_cache() {
        global $wpdb;
        
        // Transient önbellek verilerini temizle
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_ai_tarot_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_ai_tarot_%'");
    }
    
    /**
     * Eklenti klasörlerini temizle
     */
    private static function clean_directories() {
        // Log dizinini temizle
        $log_dir = AI_TAROT_PLUGIN_DIR . 'logs';
        if (file_exists($log_dir)) {
            self::delete_directory($log_dir);
        }
        
        // Önbellek ve geçici dosyaları temizle
        $cache_dir = AI_TAROT_PLUGIN_DIR . 'cache';
        if (file_exists($cache_dir)) {
            self::delete_directory($cache_dir);
        }
    }
    
    /**
     * Bir dizini ve içindekileri sil
     */
    private static function delete_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
}