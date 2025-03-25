<?php
/**
 * AI Tarot Ayarlar Sayfası Şablonu
 * 
 * Bu dosya, eklentinin ayarlar sayfasını görüntüler.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

// Ayarlar sınıfını oluştur
$settings = new AI_Tarot_Settings();
?>

<div class="wrap ai-tarot-admin-container">
    <div class="ai-tarot-admin-header">
        <div class="ai-tarot-admin-logo">
            <img src="<?php echo AI_TAROT_PLUGIN_URL . 'admin/images/tarot-logo.png'; ?>" alt="AI Tarot Logo">
        </div>
        <div class="ai-tarot-admin-title">
            <h2>AI Tarot Falı Ayarları</h2>
            <p>Bu sayfadan, eklentinin temel ayarlarını ve AI servis yapılandırmasını yönetebilirsiniz.</p>
        </div>
    </div>
    
    <div class="ai-tarot-admin-content">
        <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Ayarlar başarıyla kaydedildi.', 'ai-tarot'); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="notice notice-info is-dismissible">
            <p><strong>Not:</strong> AI servisi seçiminize göre ilgili API anahtarını sağlamanız gereklidir. Desteklenen servislerden birini seçip API anahtarınızı girin. API anahtarları, güvenlik nedeniyle şifrelenmiş olarak saklanır.</p>
        </div>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('ai_tarot_settings');
            do_settings_sections('ai_tarot_settings');
            submit_button('Ayarları Kaydet');
            ?>
        </form>
        
        <hr>
        
        <h2>API Bağlantı Testi</h2>
        <p>Seçilen AI servisi ile bağlantıyı test etmek için aşağıdaki düğmeye tıklayın. Bu, API anahtarınızın doğru olduğunu ve servisin çalıştığını doğrulamanıza yardımcı olur.</p>
        
        <div class="api-test-container">
            <button id="ai_tarot_test_connection" class="button button-secondary">Bağlantıyı Test Et</button>
            <div class="spinner" style="float: none; visibility: hidden; margin-top: 4px;"></div>
            <div id="ai_tarot_test_result" class="notice" style="margin-top: 10px; display: none;"></div>
        </div>
        
        <script>
            jQuery(document).ready(function($) {
                $('#ai_tarot_test_connection').on('click', function(e) {
                    e.preventDefault();
                    
                    var button = $(this);
                    var spinner = button.next('.spinner');
                    var result = $('#ai_tarot_test_result');
                    
                    button.prop('disabled', true);
                    spinner.css('visibility', 'visible');
                    
                    result.removeClass('notice-success notice-error').hide();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'ai_tarot_test_connection',
                            nonce: '<?php echo wp_create_nonce('ai_tarot_test_connection'); ?>',
                            service: $('#ai_service').val()
                        },
                        success: function(response) {
                            button.prop('disabled', false);
                            spinner.css('visibility', 'hidden');
                            
                            if (response.success) {
                                result.addClass('notice-success').html('<p>' + response.data + '</p>').show();
                            } else {
                                result.addClass('notice-error').html('<p>' + response.data + '</p>').show();
                            }
                        },
                        error: function() {
                            button.prop('disabled', false);
                            spinner.css('visibility', 'hidden');
                            result.addClass('notice-error').html('<p>Test sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p>').show();
                        }
                    });
                });
            });
        </script>
        
        <hr>
        
        <h2>Kısa Kod Kullanımı</h2>
        <p>Tarot falı formunu bir sayfa veya yazıya eklemek için aşağıdaki kısa kodu kullanabilirsiniz:</p>
        <div class="shortcode-example">
            <code>[ai_tarot]</code>
            <button class="copy-shortcode button button-small">Kopyala</button>
        </div>
        
        <p>İsterseniz kısa kod parametreleri de ekleyebilirsiniz:</p>
        <div class="shortcode-example">
            <code>[ai_tarot theme="dark" default_spread="celtic_cross"]</code>
            <button class="copy-shortcode button button-small">Kopyala</button>
        </div>
        
        <h3>Kullanılabilir Parametreler:</h3>
        <table class="widefat" style="max-width: 800px;">
            <thead>
                <tr>
                    <th>Parametre</th>
                    <th>Açıklama</th>
                    <th>Varsayılan</th>
                    <th>Olası Değerler</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>theme</code></td>
                    <td>Form ve sonuçlar için tema</td>
                    <td><code>light</code></td>
                    <td><code>light</code>, <code>dark</code>, <code>mystic</code></td>
                </tr>
                <tr>
                    <td><code>default_spread</code></td>
                    <td>Varsayılan açılım türü</td>
                    <td><code>three_card</code></td>
                    <td><code>three_card</code>, <code>celtic_cross</code>, <code>astrological</code></td>
                </tr>
                <tr>
                    <td><code>show_animations</code></td>
                    <td>Kart çekme animasyonlarını göster</td>
                    <td><code>true</code></td>
                    <td><code>true</code>, <code>false</code></td>
                </tr>
                <tr>
                    <td><code>allow_save</code></td>
                    <td>Kullanıcıların fal sonuçlarını kaydetmesine izin ver</td>
                    <td><code>true</code></td>
                    <td><code>true</code>, <code>false</code></td>
                </tr>
            </tbody>
        </table>
        
        <script>
            jQuery(document).ready(function($) {
                $('.copy-shortcode').on('click', function() {
                    var shortcode = $(this).prev('code').text();
                    
                    // Geçici bir textarea oluştur
                    var $temp = $("<textarea>");
                    $("body").append($temp);
                    $temp.val(shortcode).select();
                    
                    // Kopyala
                    document.execCommand("copy");
                    $temp.remove();
                    
                    // Düğmeyi güncelle
                    var $button = $(this);
                    $button.text('Kopyalandı!');
                    
                    // 2 saniye sonra düğmeyi eski haline getir
                    setTimeout(function() {
                        $button.text('Kopyala');
                    }, 2000);
                });
            });
        </script>
        
        <hr>
        
        <h2>Otomatik Sayfa Oluşturma</h2>
        <p>Otomatik olarak bir Tarot Falı sayfası oluşturmak için aşağıdaki düğmeyi kullanabilirsiniz. Bu işlem, kısa kodu içeren yeni bir sayfa oluşturacaktır.</p>
        
        <div class="create-page-container">
            <button id="ai_tarot_create_page" class="button button-secondary">Tarot Falı Sayfası Oluştur</button>
            <div class="spinner" style="float: none; visibility: hidden; margin-top: 4px;"></div>
            <div id="ai_tarot_page_result" class="notice" style="margin-top: 10px; display: none;"></div>
        </div>
        
        <script>
            jQuery(document).ready(function($) {
                $('#ai_tarot_create_page').on('click', function(e) {
                    e.preventDefault();
                    
                    var button = $(this);
                    var spinner = button.next('.spinner');
                    var result = $('#ai_tarot_page_result');
                    
                    button.prop('disabled', true);
                    spinner.css('visibility', 'visible');
                    
                    result.removeClass('notice-success notice-error').hide();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'ai_tarot_create_page',
                            nonce: '<?php echo wp_create_nonce('ai_tarot_create_page'); ?>'
                        },
                        success: function(response) {
                            button.prop('disabled', false);
                            spinner.css('visibility', 'hidden');
                            
                            if (response.success) {
                                result.addClass('notice-success').html('<p>' + response.data.message + ' <a href="' + response.data.edit_url + '" target="_blank">Sayfayı Düzenle</a> | <a href="' + response.data.view_url + '" target="_blank">Sayfayı Görüntüle</a></p>').show();
                            } else {
                                result.addClass('notice-error').html('<p>' + response.data + '</p>').show();
                            }
                        },
                        error: function() {
                            button.prop('disabled', false);
                            spinner.css('visibility', 'hidden');
                            result.addClass('notice-error').html('<p>Sayfa oluşturulurken bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p>').show();
                        }
                    });
                });
            });
        </script>
        
        <hr>
        
        <h2>Eklenti Bilgileri</h2>
        <table class="widefat" style="max-width: 500px;">
            <tbody>
                <tr>
                    <td><strong>Eklenti Versiyonu:</strong></td>
                    <td><?php echo AI_TAROT_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong>Veritabanı Versiyonu:</strong></td>
                    <td><?php echo get_option('ai_tarot_db_version', '1.0'); ?></td>
                </tr>
                <tr>
                    <td><strong>Kayıtlı Kartlar:</strong></td>
                    <td><?php echo $this->get_card_count(); ?> / 78</td>
                </tr>
                <tr>
                    <td><strong>Toplam Fal Sayısı:</strong></td>
                    <td><?php echo $this->get_reading_count(); ?></td>
                </tr>
                <tr>
                    <td><strong>Destek:</strong></td>
                    <td><a href="mailto:support@aitarot.com">support@aitarot.com</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
/**
 * Kart sayısını al
 */
function get_card_count() {
    global $wpdb;
    return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tarot_cards");
}

/**
 * Fal sayısını al
 */
function get_reading_count() {
    global $wpdb;
    return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tarot_readings");
}
?>