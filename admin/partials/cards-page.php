<?php
/**
 * AI Tarot Kartlar Sayfası Şablonu
 * 
 * Bu dosya, eklentinin tarot kartları yönetim sayfasını görüntüler.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

// Admin sınıfını kontrol et
if (!$this instanceof AI_Tarot_Admin) {
    return;
}

// Kartları al
$cards = $this->get_tarot_cards();
?>

<div class="wrap ai-tarot-admin-container">
    <div class="ai-tarot-admin-header">
        <div class="ai-tarot-admin-logo">
            <img src="<?php echo AI_TAROT_PLUGIN_URL . 'admin/images/tarot-logo.png'; ?>" alt="AI Tarot Logo">
        </div>
        <div class="ai-tarot-admin-title">
            <h2>Tarot Kartları Yönetimi</h2>
            <p>Bu sayfadan, tarot kartlarını görüntüleyebilir, düzenleyebilir, ekleyebilir veya silebilirsiniz.</p>
        </div>
    </div>
    
    <div class="ai-tarot-admin-content">
        <div id="card-list">
            <div class="filter-section">
                <div class="filter-box">
                    <label for="filter-cards">Kart Türüne Göre Filtrele:</label>
                    <select id="filter-cards">
                        <option value="all">Tüm Kartlar</option>
                        <option value="major">Major Arcana</option>
                        <option value="minor">Minor Arcana</option>
                        <option value="cups">Kupalar (Cups)</option>
                        <option value="wands">Asalar (Wands)</option>
                        <option value="swords">Kılıçlar (Swords)</option>
                        <option value="pentacles">Pentakıller (Pentacles)</option>
                    </select>
                </div>
                
                <div class="search-box">
                    <label for="search-cards">Kart Ara:</label>
                    <input type="text" id="search-cards" placeholder="Kart adı girin...">
                </div>
                
                <div class="add-card-box">
                    <button id="add-new-card" class="button button-primary">Yeni Kart Ekle</button>
                    <button id="bulk-upload-button" class="button">Toplu Görsel Yükle</button>
                    <button id="add-missing-cards" class="button">Eksik Kartları Ekle</button>
                </div>
            </div>
            
            <div id="bulk-image-preview"></div>
            
            <?php if (empty($cards)): ?>
                <div class="notice notice-warning">
                    <p>Henüz hiç tarot kartı eklenmemiş. Yeni kart ekleyebilir veya "Eksik Kartları Ekle" düğmesini kullanarak standart kartları otomatik olarak ekleyebilirsiniz.</p>
                </div>
            <?php else: ?>
                <table class="cards-table">
                    <thead>
                        <tr>
                            <th width="60">Görsel</th>
                            <th>Kart Adı</th>
                            <th>Tür</th>
                            <th>Suit</th>
                            <th>Numara</th>
                            <th>Element</th>
                            <th width="150">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cards as $card): ?>
                            <tr class="card-row" data-card-id="<?php echo $card['id']; ?>" data-card-type="<?php echo $card['card_type']; ?>" data-card-suit="<?php echo $card['suit']; ?>">
                                <td>
                                    <?php if (!empty($card['image_url'])): ?>
                                        <img src="<?php echo esc_url($card['image_url']); ?>" alt="<?php echo esc_attr($card['name']); ?>" class="card-image">
                                    <?php else: ?>
                                        <div class="no-image">Görsel Yok</div>
                                    <?php endif; ?>
                                </td>
                                <td class="card-name"><?php echo esc_html($card['name']); ?></td>
                                <td><?php echo $card['card_type'] === 'major' ? 'Major Arcana' : 'Minor Arcana'; ?></td>
                                <td><?php echo $card['card_type'] === 'minor' ? ucfirst($card['suit']) : '-'; ?></td>
                                <td><?php echo $card['number']; ?></td>
                                <td><?php echo esc_html($card['element']); ?></td>
                                <td class="card-actions">
                                    <a href="#" class="button button-small view-card-details" data-card-id="<?php echo $card['id']; ?>">Görüntüle</a>
                                    <a href="#" class="button button-small edit-card" data-card-id="<?php echo $card['id']; ?>">Düzenle</a>
                                    <a href="#" class="button button-small delete-card" data-card-id="<?php echo $card['id']; ?>" data-card-name="<?php echo esc_attr($card['name']); ?>">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div id="add-edit-card-form">
            <h2 id="form-title">Yeni Kart Ekle</h2>
            
            <form id="card-form" method="post">
                <input type="hidden" id="card-id" name="card_id" value="">
                
                <div class="form-section">
                    <div class="form-field">
                        <label for="card-name">Kart Adı:</label>
                        <input type="text" id="card-name" name="name" required>
                    </div>
                    
                    <div class="form-field">
                        <label for="card-type">Kart Türü:</label>
                        <select id="card-type" name="card_type" required>
                            <option value="major">Major Arcana</option>
                            <option value="minor">Minor Arcana</option>
                        </select>
                    </div>
                    
                    <div class="form-field field-suit">
                        <label for="card-suit">Kart Suit:</label>
                        <select id="card-suit" name="suit">
                            <option value="cups">Kupalar (Cups)</option>
                            <option value="wands">Asalar (Wands)</option>
                            <option value="swords">Kılıçlar (Swords)</option>
                            <option value="pentacles">Pentakıller (Pentacles)</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="card-number">Numara:</label>
                        <input type="number" id="card-number" name="number" min="0" max="21">
                    </div>
                    
                    <div class="form-field">
                        <label for="card-element">Element:</label>
                        <select id="card-element" name="element">
                            <option value="">Seçiniz</option>
                            <option value="Ateş">Ateş</option>
                            <option value="Su">Su</option>
                            <option value="Hava">Hava</option>
                            <option value="Toprak">Toprak</option>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="card-astrological">Astrolojik İşaret:</label>
                        <input type="text" id="card-astrological" name="astrological_sign">
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-field">
                        <label for="card-keywords">Anahtar Kelimeler:</label>
                        <input type="text" id="card-keywords" name="keywords" placeholder="Kelimeler arasına virgül koyun">
                    </div>
                    
                    <div class="form-field">
                        <label for="card-upright">Düz Duruş Anlamı:</label>
                        <textarea id="card-upright" name="upright_meaning" rows="4"></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label for="card-reversed">Ters Duruş Anlamı:</label>
                        <textarea id="card-reversed" name="reversed_meaning" rows="4"></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label for="card-image">Kart Görseli:</label>
                        <input type="text" id="card-image" name="image_url" class="image-url">
                        <button class="upload-image-button button">Görsel Seç</button>
                        <div id="card-image-preview" class="image-preview"></div>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="button" id="cancel-card-form" class="button">İptal</button>
                    <button type="submit" class="button button-primary">Kartı Kaydet</button>
                </div>
            </form>
        </div>
        
        <div id="card-detail-modal" class="card-modal" style="display: none;">
            <!-- Kart detay modalı AJAX ile doldurulacak -->
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Kart türü değiştiğinde suit alanını göster/gizle
        $('#card-type').on('change', function() {
            if ($(this).val() === 'minor') {
                $('.field-suit').show();
            } else {
                $('.field-suit').hide();
            }
        }).trigger('change');
        
        // Kart düzenleme butonu tıklaması
        $(document).on('click', '.edit-card', function(e) {
            e.preventDefault();
            var cardId = $(this).data('card-id');
            
            // AJAX ile kart verilerini al
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_card_details',
                    nonce: ai_tarot_admin.nonce,
                    card_id: cardId
                },
                success: function(response) {
                    if (response.success) {
                        // Formu doldur
                        var card = response.data;
                        
                        $('#card-id').val(card.id);
                        $('#card-name').val(card.name);
                        $('#card-type').val(card.card_type).trigger('change');
                        $('#card-suit').val(card.suit);
                        $('#card-number').val(card.number);
                        $('#card-element').val(card.element);
                        $('#card-astrological').val(card.astrological_sign);
                        $('#card-keywords').val(card.keywords);
                        $('#card-upright').val(card.upright_meaning);
                        $('#card-reversed').val(card.reversed_meaning);
                        $('#card-image').val(card.image_url);
                        
                        // Görsel önizlemeyi güncelle
                        if (card.image_url) {
                            $('#card-image-preview').html('<img src="' + card.image_url + '" alt="' + card.name + '" style="max-width: 100px; max-height: 150px;">');
                        } else {
                            $('#card-image-preview').empty();
                        }
                        
                        // Formu göster
                        $('#add-edit-card-form').show();
                        $('#card-list').hide();
                        
                        // Form başlığını güncelle
                        $('#form-title').text('Kartı Düzenle: ' + card.name);
                        
                        // Sayfayı form başına kaydır
                        $('html, body').animate({
                            scrollTop: $('#add-edit-card-form').offset().top - 50
                        }, 500);
                    } else {
                        alert('Kart detayları alınamadı: ' + response.data);
                    }
                },
                error: function() {
                    alert('Kart detayları alınırken bir hata oluştu.');
                }
            });
        });
    });
</script>