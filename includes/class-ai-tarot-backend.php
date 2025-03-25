<?php
/**
 * AI Tarot Backend
 * 
 * Bu sınıf, eklentinin backend işlevlerini yönetir.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Tarot Backend sınıfı
 */
class AI_Tarot_Backend {
    
    /**
     * Ayarlar
     */
    private $settings;
    
    /**
     * Sınıfı başlat
     */
    public function __construct() {
        $this->settings = get_option('ai_tarot_settings', array());
    }
    
    /**
     * Tarot falı oluştur
     */
    public function process_tarot_reading() {
        // Nonce kontrolü
        check_ajax_referer('ai_tarot_nonce', 'nonce');
        
        // Parametreleri al
        $question = isset($_POST['question']) ? sanitize_text_field($_POST['question']) : '';
        $spread_type = isset($_POST['spread_type']) ? sanitize_text_field($_POST['spread_type']) : 'three_card';
        $card_count = isset($_POST['card_count']) ? intval($_POST['card_count']) : 3;
        
        // Soruyu kontrol et
        if (empty($question)) {
            wp_send_json_error(__('Lütfen bir soru sorun veya niyet belirtin.', 'ai-tarot'));
            return;
        }
        
        // Açılım türünü doğrula
        $valid_spreads = array('three_card', 'celtic_cross', 'astrological');
        if (!in_array($spread_type, $valid_spreads)) {
            $spread_type = 'three_card';
        }
        
        // Kart sayısını doğrula
        switch ($spread_type) {
            case 'three_card':
                $card_count = 3;
                break;
            case 'celtic_cross':
                $card_count = 10;
                break;
            case 'astrological':
                $card_count = 12;
                break;
            default:
                $card_count = 3;
                break;
        }
        
        try {
            // Kartları seç
            $cards = $this->draw_tarot_cards($card_count);
            
            // AI ile yorum oluştur
            $api_integration = new AI_Tarot_API_Integration();
            $interpretation = $api_integration->generate_interpretation($cards, $question, $spread_type);
            
            // AI servisi adını al
            $ai_service = isset($this->settings['ai_service']) ? $this->settings['ai_service'] : 'openai';
            
            // Okumayı veritabanına kaydet
            $reading_id = $this->save_reading($question, $spread_type, $cards, $interpretation, $ai_service);
            
            // Yanıtı oluştur
            $response = array(
                'success' => true,
                'data' => array(
                    'reading_id' => $reading_id,
                    'cards' => $cards,
                    'interpretation' => $interpretation,
                    'question' => $question,
                    'spread_type' => $spread_type,
                    'ai_service' => $ai_service
                )
            );
            
            wp_send_json($response);
        } catch (Exception $e) {
            // Hata kaydını tut
            AI_Tarot::log_error('Reading Error: ' . $e->getMessage());
            
            // Hata mesajı gönder
            wp_send_json_error(__('Tarot falı oluşturulurken bir hata oluştu. Lütfen daha sonra tekrar deneyin.', 'ai-tarot'));
        }
    }
    
    /**
     * Tarot kartlarını çek
     */
    private function draw_tarot_cards($count) {
        global $wpdb;
        
        // Veritabanından tüm kartları al
        $cards = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}tarot_cards",
            ARRAY_A
        );
        
        // Kartları karıştır
        shuffle($cards);
        
        // İstenen sayıda kart seç
        $selected_cards = array_slice($cards, 0, $count);
        
        // Her kart için ters/düz durumunu belirle
        foreach ($selected_cards as &$card) {
            // %30 ihtimalle kartı ters çevir
            $card['reversed'] = (mt_rand(1, 100) <= 30);
            
            // Kart anlamını belirle
            $card['meaning'] = $card['reversed'] ? $card['reversed_meaning'] : $card['upright_meaning'];
            
            // Gereksiz verileri temizle
            unset($card['upright_meaning']);
            unset($card['reversed_meaning']);
        }
        
        return $selected_cards;
    }
    
    /**
     * Tarot okumasını veritabanına kaydet
     */
    private function save_reading($question, $spread_type, $cards, $interpretation, $ai_service) {
        global $wpdb;
        
        // Kullanıcı ID'si (giriş yapmışsa)
        $user_id = is_user_logged_in() ? get_current_user_id() : null;
        
        // IP adresi
        $ip_address = $this->get_client_ip();
        
        // Okumayı veritabanına ekle
        $wpdb->insert(
            $wpdb->prefix . 'tarot_readings',
            array(
                'user_id' => $user_id,
                'question' => $question,
                'spread_type' => $spread_type,
                'cards_data' => json_encode($cards),
                'interpretation' => $interpretation,
                'ai_service' => $ai_service,
                'ip_address' => $ip_address,
                'created_at' => current_time('mysql')
            )
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Kullanıcı için fal kaydet
     */
    public function save_user_reading() {
        // Nonce kontrolü
        check_ajax_referer('ai_tarot_nonce', 'nonce');
        
        // Kullanıcı giriş yapmış mı kontrol et
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Kullanıcı giriş yapmamış', 'ai-tarot'));
            return;
        }
        
        // Parametreleri al
        $reading_id = isset($_POST['reading_id']) ? intval($_POST['reading_id']) : 0;
        
        // Mevcut okumayı al
        global $wpdb;
        $reading = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tarot_readings WHERE id = %d",
            $reading_id
        ));
        
        if (!$reading) {
            wp_send_json_error(__('Okuma bulunamadı', 'ai-tarot'));
            return;
        }
        
        // Kullanıcı ID'sini güncelle
        $result = $wpdb->update(
            $wpdb->prefix . 'tarot_readings',
            array('user_id' => get_current_user_id()),
            array('id' => $reading_id)
        );
        
        if ($result !== false) {
            wp_send_json_success(__('Okuma başarıyla kaydedildi', 'ai-tarot'));
        } else {
            wp_send_json_error(__('Okuma kaydedilirken bir hata oluştu', 'ai-tarot'));
        }
    }
    
    /**
     * Misafir (giriş yapmamış) kullanıcı için fal kaydet
     */
    public function save_guest_reading() {
        // Nonce kontrolü
        check_ajax_referer('ai_tarot_nonce', 'nonce');
        
        // Otomatik kayıt özelliği etkin mi kontrol et
        $auto_save = isset($this->settings['auto_save']) ? (int)$this->settings['auto_save'] : 0;
        
        if (!$auto_save) {
            wp_send_json_error(__('Otomatik kayıt devre dışı', 'ai-tarot'));
            return;
        }
        
        // Parametreleri al
        $reading_id = isset($_POST['reading_id']) ? intval($_POST['reading_id']) : 0;
        
        // Mevcut okumayı al
        global $wpdb;
        $reading = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tarot_readings WHERE id = %d",
            $reading_id
        ));
        
        if (!$reading) {
            wp_send_json_error(__('Okuma bulunamadı', 'ai-tarot'));
            return;
        }
        
        // IP adresi güncelle
        $ip_address = $this->get_client_ip();
        
        $result = $wpdb->update(
            $wpdb->prefix . 'tarot_readings',
            array('ip_address' => $ip_address),
            array('id' => $reading_id)
        );
        
        if ($result !== false) {
            wp_send_json_success(__('Okuma başarıyla kaydedildi', 'ai-tarot'));
        } else {
            wp_send_json_error(__('Okuma kaydedilirken bir hata oluştu', 'ai-tarot'));
        }
    }
    
    /**
     * Kullanıcının IP adresini al
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Tarot Sayfası oluştur
     */
    public function create_tarot_page() {
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
                return $page_id;
            }
        }
        
        return false;
    }
    
    /**
     * Zamanlanmış temizlik
     */
    public function daily_cleanup() {
        // Eski verileri temizle
        $ai_tarot = new AI_Tarot();
        $ai_tarot->cleanup_old_data();
    }
    
    /**
     * Kaydedilmiş falların istatistiklerini al
     */
    public function get_reading_statistics() {
        global $wpdb;
        
        // Toplam fal sayısı
        $total_readings = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tarot_readings");
        
        // Son 7 gündeki fal sayısı
        $recent_readings = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}tarot_readings WHERE created_at >= %s",
            date('Y-m-d', strtotime('-7 days'))
        ));
        
        // Açılım türlerine göre dağılım
        $spread_stats = $wpdb->get_results(
            "SELECT spread_type, COUNT(*) as count FROM {$wpdb->prefix}tarot_readings GROUP BY spread_type",
            ARRAY_A
        );
        
        // AI servislerine göre dağılım
        $ai_service_stats = $wpdb->get_results(
            "SELECT ai_service, COUNT(*) as count FROM {$wpdb->prefix}tarot_readings GROUP BY ai_service",
            ARRAY_A
        );
        
        // En çok çekilen kartlar
        $card_stats = $wpdb->get_results(
            "SELECT json_extract(cards_data, '$[*].name') as card_names FROM {$wpdb->prefix}tarot_readings",
            ARRAY_A
        );
        
        // Kart istatistiklerini işle
        $card_counts = array();
        foreach ($card_stats as $row) {
            $card_names = json_decode($row['card_names'], true);
            if (is_array($card_names)) {
                foreach ($card_names as $name) {
                    if (!isset($card_counts[$name])) {
                        $card_counts[$name] = 0;
                    }
                    $card_counts[$name]++;
                }
            }
        }
        
        // Kart sayılarını sırala
        arsort($card_counts);
        
        // İlk 10 kartı al
        $top_cards = array_slice($card_counts, 0, 10, true);
        
        return array(
            'total_readings' => $total_readings,
            'recent_readings' => $recent_readings,
            'spread_stats' => $spread_stats,
            'ai_service_stats' => $ai_service_stats,
            'top_cards' => $top_cards
        );
    }
}