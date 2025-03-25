<?php
/**
 * AI Tarot Falı Uninstall
 *
 * Bu dosya, AI Tarot Falı eklentisi kaldırıldığında çalışır.
 * Veritabanı tablolarını, eklenti seçeneklerini ve diğer kayıtları temizler.
 *
 * @package AI_Tarot
 */

// WordPress ile doğrudan çalıştırılmıyorsa çık
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Tamamen kaldırma seçeneğini kontrol et
$settings = get_option('ai_tarot_settings', array());
$complete_uninstall = isset($settings['complete_uninstall']) ? (bool) $settings['complete_uninstall'] : false;

// Eğer tamamen kaldırma seçilmemişse hiçbir şey yapma
if (!$complete_uninstall) {
    return;
}

// Veritabanı tablolarını kaldır
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

// Eklenti ayarlarını temizle
delete_option('ai_tarot_settings');
delete_option('ai_tarot_db_version');
delete_option('ai_tarot_activation_date');
delete_option('ai_tarot_deactivation_date');
delete_option('ai_tarot_activated');
delete_option('ai_tarot_active');
delete_option('ai_tarot_page_id');

// Transient verileri temizle
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_ai_tarot_%'");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_ai_tarot_%'");

// Kullanıcı meta verilerini temizle - tarot falı tercihleri
$wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE '%ai_tarot_%'");

// Zamanlanmış görevleri temizle
wp_clear_scheduled_hook('ai_tarot_daily_cleanup');

// Tarot sayfasını kaldır (eğer otomatik oluşturulduysa)
$tarot_page_id = get_option('ai_tarot_page_id');
if ($tarot_page_id) {
    wp_delete_post($tarot_page_id, true); // İkinci parametre true olursa, sayfa çöp kutusuna gitmeden tamamen silinir
}

// Eklenti tarafından oluşturulan dosyaları temizle
// Log dosyalarını ve diğer dosyaları temizle
$upload_dir = wp_upload_dir();
$ai_tarot_dir = $upload_dir['basedir'] . '/ai-tarot';

// Dizin varsa içindeki tüm dosyaları ve dizini sil
if (file_exists($ai_tarot_dir)) {
    ai_tarot_delete_directory($ai_tarot_dir);
}

// Log dizinini temizle
$log_dir = plugin_dir_path(dirname(__FILE__)) . 'logs';
if (file_exists($log_dir)) {
    ai_tarot_delete_directory($log_dir);
}

// Önbellek ve geçici dosyaları temizle
$cache_dir = plugin_dir_path(dirname(__FILE__)) . 'cache';
if (file_exists($cache_dir)) {
    ai_tarot_delete_directory($cache_dir);
}

/**
 * Bir dizini ve içindeki tüm dosyaları sil
 *
 * @param string $dir Silinecek dizin
 * @return bool Silme işlemi başarılı olursa true döner
 */
function ai_tarot_delete_directory($dir) {
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
        
        if (!ai_tarot_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}