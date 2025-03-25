<?php
/**
 * AI Tarot Frontend
 * 
 * Bu sınıf, eklentinin frontend işlevlerini yönetir.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Tarot Frontend sınıfı
 */
class AI_Tarot_Frontend {
    
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
     * Frontend stil ve script'leri ekle
     */
    public function enqueue_scripts() {
        // Stil dosyalarını ekle
        wp_enqueue_style(
            'ai-tarot-style',
            AI_TAROT_PLUGIN_URL . 'public/css/tarot.css',
            array(),
            AI_TAROT_VERSION
        );
        
        // Script dosyalarını ekle
        wp_enqueue_script(
            'ai-tarot-script',
            AI_TAROT_PLUGIN_URL . 'public/js/tarot.js',
            array('jquery'),
            AI_TAROT_VERSION,
            true
        );
        
        // AJAX URL ve nonce değerini script'e ekle
        wp_localize_script(
            'ai-tarot-script',
            'ai_tarot_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_tarot_nonce'),
                'is_logged_in' => is_user_logged_in() ? 1 : 0,
                'enable_animations' => isset($this->settings['enable_animations']) ? (int)$this->settings['enable_animations'] : 1,
                'deck_theme' => isset($this->settings['card_deck_theme']) ? $this->settings['card_deck_theme'] : 'classic',
                'card_back_url' => $this->get_card_back_image(),
                'loading_text' => __('Tarot falınız hazırlanıyor...', 'ai-tarot'),
                'error_text' => __('Tarot falı oluşturulurken bir hata oluştu. Lütfen daha sonra tekrar deneyin.', 'ai-tarot'),
                'save_success' => __('Tarot falınız başarıyla kaydedildi!', 'ai-tarot'),
                'save_error' => __('Tarot falı kaydedilirken bir hata oluştu.', 'ai-tarot'),
                'login_required' => __('Falınızı kaydetmek için lütfen giriş yapın.', 'ai-tarot')
            )
        );
    }
    
    /**
     * Kart arka yüzü görselini al
     */
    private function get_card_back_image() {
        // Temanın kart arka yüzünü al
        $deck_theme = isset($this->settings['card_deck_theme']) ? $this->settings['card_deck_theme'] : 'classic';
        
        switch ($deck_theme) {
            case 'modern':
                $card_back = 'card-back-modern.jpg';
                break;
            case 'medieval':
                $card_back = 'card-back-medieval.jpg';
                break;
            case 'mystical':
                $card_back = 'card-back-mystical.jpg';
                break;
            case 'classic':
            default:
                $card_back = 'card-back-classic.jpg';
                break;
        }
        
        return AI_TAROT_PLUGIN_URL . 'public/images/backs/' . $card_back;
    }
    
    /**
     * Tarot formunu render et (kısa kod işlevi)
     */
    public function render_tarot_form($atts = array()) {
        // Kısa kod özelliklerini ayarla
        $attributes = shortcode_atts(
            array(
                'theme' => 'light',
                'default_spread' => 'three_card',
                'show_animations' => 'true',
                'allow_save' => 'true'
            ),
            $atts
        );
        
        // Değerleri dönüştür
        $theme = sanitize_text_field($attributes['theme']);
        $default_spread = sanitize_text_field($attributes['default_spread']);
        $show_animations = filter_var($attributes['show_animations'], FILTER_VALIDATE_BOOLEAN);
        $allow_save = filter_var($attributes['allow_save'], FILTER_VALIDATE_BOOLEAN);
        
        // Değerleri doğrula
        $valid_themes = array('light', 'dark', 'mystic');
        if (!in_array($theme, $valid_themes)) {
            $theme = 'light';
        }
        
        $valid_spreads = array('three_card', 'celtic_cross', 'astrological');
        if (!in_array($default_spread, $valid_spreads)) {
            $default_spread = 'three_card';
        }
        
        // Tema sınıfını oluştur
        $theme_class = 'ai-tarot-theme-' . $theme;
        
        // Çıktıyı almaya başla
        ob_start();
        
        // Form şablonunu yükle
        include AI_TAROT_PLUGIN_DIR . 'public/partials/tarot-form.php';
        
        // Çıktıyı al ve döndür
        return ob_get_clean();
    }
    
    /**
     * Kullanıcı profilindeki tarot fallarını göster
     */
    public function render_user_readings($user) {
        // Kullanıcının fallarını al
        $readings = $this->get_user_readings($user->ID);
        
        if (empty($readings)) {
            echo '<h3>' . __('Tarot Falı Geçmişi', 'ai-tarot') . '</h3>';
            echo '<p>' . __('Henüz kaydedilmiş tarot falınız bulunmamaktadır.', 'ai-tarot') . '</p>';
            return;
        }
        
        // Falları görüntüle
        echo '<h3>' . __('Tarot Falı Geçmişi', 'ai-tarot') . '</h3>';
        echo '<p>' . __('Kaydettiğiniz tarot falları aşağıda listelenmiştir.', 'ai-tarot') . '</p>';
        
        echo '<table class="form-table user-readings-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Tarih', 'ai-tarot') . '</th>';
        echo '<th>' . __('Soru', 'ai-tarot') . '</th>';
        echo '<th>' . __('Açılım', 'ai-tarot') . '</th>';
        echo '<th>' . __('İşlemler', 'ai-tarot') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($readings as $reading) {
            echo '<tr>';
            echo '<td>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reading['created_at'])) . '</td>';
            echo '<td>' . esc_html($reading['question']) . '</td>';
            
            // Açılım türünü formatla
            $spread_type = '';
            switch ($reading['spread_type']) {
                case 'three_card':
                    $spread_type = __('Üç Kartlık', 'ai-tarot');
                    break;
                case 'celtic_cross':
                    $spread_type = __('Kelt Haçı', 'ai-tarot');
                    break;
                case 'astrological':
                    $spread_type = __('Astrolojik', 'ai-tarot');
                    break;
                default:
                    $spread_type = esc_html($reading['spread_type']);
                    break;
            }
            
            echo '<td>' . $spread_type . '</td>';
            echo '<td>';
            echo '<a href="#" class="button view-reading" data-reading-id="' . $reading['id'] . '">' . __('Görüntüle', 'ai-tarot') . '</a> ';
            echo '<a href="#" class="button delete-reading" data-reading-id="' . $reading['id'] . '" data-nonce="' . wp_create_nonce('delete_user_reading_' . $reading['id']) . '">' . __('Sil', 'ai-tarot') . '</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        // JavaScript
        ?>
        <script>
            jQuery(document).ready(function($) {
                // Fal görüntüleme
                $('.view-reading').on('click', function(e) {
                    e.preventDefault();
                    
                    var readingId = $(this).data('reading-id');
                    
                    // AJAX ile fal detaylarını getir
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'get_user_reading',
                            nonce: '<?php echo wp_create_nonce('ai_tarot_user_reading'); ?>',
                            reading_id: readingId
                        },
                        success: function(response) {
                            if (response.success) {
                                // Modal oluştur
                                var modalHtml = '<div class="ai-tarot-modal-overlay">';
                                modalHtml += '<div class="ai-tarot-modal-content">';
                                modalHtml += '<span class="ai-tarot-modal-close">&times;</span>';
                                modalHtml += response.data.html;
                                modalHtml += '</div>';
                                modalHtml += '</div>';
                                
                                // Modalı ekle
                                $('body').append(modalHtml);
                                
                                // Modalı kapat
                                $('.ai-tarot-modal-close, .ai-tarot-modal-overlay').on('click', function(e) {
                                    if (e.target === this) {
                                        $('.ai-tarot-modal-overlay').remove();
                                    }
                                });
                            } else {
                                alert('<?php echo __('Fal detayları alınamadı.', 'ai-tarot'); ?>');
                            }
                        },
                        error: function() {
                            alert('<?php echo __('Fal detayları alınırken bir hata oluştu.', 'ai-tarot'); ?>');
                        }
                    });
                });
                
                // Fal silme
                $('.delete-reading').on('click', function(e) {
                    e.preventDefault();
                    
                    var readingId = $(this).data('reading-id');
                    var nonce = $(this).data('nonce');
                    
                    if (confirm('<?php echo __('Bu falı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.', 'ai-tarot'); ?>')) {
                        // AJAX ile falı sil
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'delete_user_reading',
                                nonce: nonce,
                                reading_id: readingId
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Falı listeden kaldır
                                    $('a[data-reading-id="' + readingId + '"]').closest('tr').fadeOut(300, function() {
                                        $(this).remove();
                                        
                                        // Hiç fal kalmadıysa tabloyu gizle
                                        if ($('.user-readings-table tbody tr').length === 0) {
                                            $('.user-readings-table').replaceWith('<p><?php echo __('Henüz kaydedilmiş tarot falınız bulunmamaktadır.', 'ai-tarot'); ?></p>');
                                        }
                                    });
                                } else {
                                    alert('<?php echo __('Fal silinirken bir hata oluştu.', 'ai-tarot'); ?>');
                                }
                            },
                            error: function() {
                                alert('<?php echo __('Fal silinirken bir hata oluştu.', 'ai-tarot'); ?>');
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }
    
    /**
     * Kullanıcının fallarını al
     */
    private function get_user_readings($user_id) {
        global $wpdb;
        
        // Kullanıcının fallarını al
        $readings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tarot_readings
                WHERE user_id = %d
                ORDER BY created_at DESC",
                $user_id
            ),
            ARRAY_A
        );
        
        return $readings;
    }
}