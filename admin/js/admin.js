/**
 * AI Tarot Admin JavaScript
 * 
 * Admin panel için JavaScript işlevleri
 */

jQuery(document).ready(function($) {
    
    /**
     * AI Servisi değiştiğinde ilgili bölümleri göster/gizle
     */
    function toggleApiSections() {
        var selectedService = $('#ai_service').val();
        
        // Tüm API bölümlerini gizle
        $('.api-section').hide();
        
        // Seçilen servisi göster
        $('#api-section-' + selectedService).show();
    }
    
    // Sayfa yüklendiğinde ve değiştirildiğinde API bölümlerini kontrol et
    toggleApiSections();
    $('#ai_service').on('change', toggleApiSections);
    
    /**
     * WordPress Medya Yükleyici (Kart görselleri için)
     */
    $('.upload-image-button').click(function(e) {
        e.preventDefault();
        
        var button = $(this);
        var imageField = button.prev('.image-url');
        var imagePreview = button.next('.image-preview');
        
        // Medya Yükleyici'yi aç
        var mediaUploader = wp.media({
            title: 'Kart Görseli Seç',
            button: {
                text: 'Görseli Kullan'
            },
            multiple: false
        });
        
        // Görseli seçtiğinde
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            imageField.val(attachment.url);
            
            // Görsel önizlemeyi güncelle
            if (imagePreview.length) {
                imagePreview.html('<img src="' + attachment.url + '" alt="Kart Görseli" style="max-width: 100px; max-height: 150px;">');
            }
        });
        
        // Medya Yükleyici'yi aç
        mediaUploader.open();
    });
    
    /**
     * Toplu Görsel Seçme
     */
    $('#bulk-upload-button').click(function(e) {
        e.preventDefault();
        
        // Medya Yükleyici'yi aç
        var mediaUploader = wp.media({
            title: 'Tarot Kartı Görsellerini Seç',
            button: {
                text: 'Görselleri Kullan'
            },
            multiple: true
        });
        
        // Görselleri seçtiğinde
        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            
            // Görselleri işle
            var images = {};
            attachments.forEach(function(attachment) {
                // Dosya adından kart ID'sini çıkarmaya çalış
                var filename = attachment.filename.replace(/\.[^/.]+$/, ""); // Uzantıyı kaldır
                var cardId = findCardIdByFilename(filename);
                
                if (cardId) {
                    images[cardId] = attachment.url;
                }
            });
            
            // Toplu güncelleme için görsel bilgilerini göster
            displayBulkImageMapping(images);
        });
        
        // Medya Yükleyici'yi aç
        mediaUploader.open();
    });
    
    /**
     * Dosya adından kart ID'sini bul
     */
    function findCardIdByFilename(filename) {
        // Mevcut kartları kontrol et
        var cardFound = false;
        
        $('.card-row').each(function() {
            var cardName = $(this).find('.card-name').text().toLowerCase();
            var cardId = $(this).data('card-id');
            
            // Dosya adı kart adı ile eşleşiyorsa
            if (filename.toLowerCase() === cardName.replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '') ||
                filename.toLowerCase() === cardName.replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '')) {
                cardFound = cardId;
                return false; // Döngüden çık
            }
        });
        
        return cardFound;
    }
    
    /**
     * Toplu görsel eşleştirmelerini göster
     */
    function displayBulkImageMapping(images) {
        var $container = $('#bulk-image-preview');
        $container.empty();
        
        if (Object.keys(images).length === 0) {
            $container.html('<div class="notice notice-warning"><p>Hiçbir görsel kart ile eşleştirilemedi. Lütfen görsel dosya adlarının kart adlarıyla eşleştiğinden emin olun.</p></div>');
            return;
        }
        
        // Başlık ve açıklama ekle
        $container.append('<h3>Kart Görsel Eşleştirmeleri</h3>');
        $container.append('<p>Aşağıdaki kartlar için görsel eşleştirmeleri yapıldı. "Görselleri Güncelle" düğmesine tıklayarak tüm kartların görsellerini güncelleyebilirsiniz.</p>');
        
        // Tablo oluştur
        var $table = $('<table class="wp-list-table widefat fixed striped">');
        var $thead = $('<thead>').appendTo($table);
        var $tbody = $('<tbody>').appendTo($table);
        
        // Tablo başlıkları
        $('<tr>')
            .append($('<th>').text('Kart Adı'))
            .append($('<th>').text('Görsel Önizleme'))
            .appendTo($thead);
        
        // Tablo satırları
        $.each(images, function(cardId, imageUrl) {
            var cardName = $('.card-row[data-card-id="' + cardId + '"]').find('.card-name').text();
            
            $('<tr>')
                .append($('<td>').text(cardName))
                .append($('<td>').html('<img src="' + imageUrl + '" alt="' + cardName + '" style="max-width: 100px; max-height: 150px;">'))
                .appendTo($tbody);
        });
        
        // Tabloyu ekle
        $container.append($table);
        
        // Güncelleme düğmesi ekle
        var $updateButton = $('<button class="button button-primary">Görselleri Güncelle</button>');
        $container.append($updateButton);
        
        // Güncelleme düğmesine tıklama olayı ekle
        $updateButton.on('click', function(e) {
            e.preventDefault();
            
            // Görselleri AJAX ile güncelle
            updateCardImages(images);
        });
    }
    
    /**
     * Kart görsellerini AJAX ile güncelle
     */
    function updateCardImages(images) {
        // Yükleniyor göster
        $('#bulk-image-preview').append('<div class="spinner is-active" style="float: none; margin: 10px;"></div>');
        
        // AJAX isteği gönder
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_card_images',
                nonce: ai_tarot_admin.nonce,
                images: images
            },
            success: function(response) {
                // Yükleniyor kaldır
                $('#bulk-image-preview .spinner').remove();
                
                if (response.success) {
                    // Başarılı mesajı göster
                    $('#bulk-image-preview').append('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    
                    // Sayfayı yenile
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    // Hata mesajı göster
                    $('#bulk-image-preview').append('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                // Yükleniyor kaldır
                $('#bulk-image-preview .spinner').remove();
                
                // Hata mesajı göster
                $('#bulk-image-preview').append('<div class="notice notice-error"><p>Görsel güncelleme sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p></div>');
            }
        });
    }
    
    /**
     * Renk Seçici
     */
    $('.color-picker').wpColorPicker();
    
    /**
     * Tarih Seçici
     */
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });
    
    /**
     * Kart Filtrele
     */
    $('#filter-cards').on('change', function() {
        var filter = $(this).val();
        
        if (filter === 'all') {
            $('.card-row').show();
        } else {
            $('.card-row').hide();
            $('.card-row[data-card-type="' + filter + '"]').show();
        }
    });
    
    /**
     * Kart Ara
     */
    $('#search-cards').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === '') {
            $('.card-row').show();
        } else {
            $('.card-row').each(function() {
                var cardName = $(this).find('.card-name').text().toLowerCase();
                
                if (cardName.indexOf(searchTerm) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
    
    /**
     * Kart Detayları Modal
     */
    $('.view-card-details').on('click', function(e) {
        e.preventDefault();
        
        var cardId = $(this).data('card-id');
        
        // AJAX ile kart detaylarını getir
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
                    displayCardModal(response.data);
                } else {
                    alert('Kart detayları alınamadı: ' + response.data);
                }
            },
            error: function() {
                alert('Kart detayları alınırken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
            }
        });
    });
    
    /**
     * Kart detay modalını göster
     */
    function displayCardModal(card) {
        // Modal HTML oluştur
        var modalHtml = '<div class="card-modal-overlay">' +
            '<div class="card-modal-content">' +
            '<span class="card-modal-close">&times;</span>' +
            '<h2>' + card.name + '</h2>' +
            '<div class="card-modal-body">' +
            '<div class="card-modal-image">' +
            '<img src="' + card.image_url + '" alt="' + card.name + '">' +
            '</div>' +
            '<div class="card-modal-details">' +
            '<table class="form-table">' +
            '<tr><th>Kart Türü:</th><td>' + (card.card_type === 'major' ? 'Major Arcana' : 'Minor Arcana') + '</td></tr>' +
            (card.card_type === 'minor' ? '<tr><th>Suit:</th><td>' + card.suit + '</td></tr>' : '') +
            '<tr><th>Numara:</th><td>' + card.number + '</td></tr>' +
            '<tr><th>Element:</th><td>' + card.element + '</td></tr>' +
            '<tr><th>Astrolojik İşaret:</th><td>' + card.astrological_sign + '</td></tr>' +
            '<tr><th>Anahtar Kelimeler:</th><td>' + card.keywords + '</td></tr>' +
            '<tr><th>Düz Duruş Anlamı:</th><td>' + card.upright_meaning + '</td></tr>' +
            '<tr><th>Ters Duruş Anlamı:</th><td>' + card.reversed_meaning + '</td></tr>' +
            '</table>' +
            '<div class="card-modal-actions">' +
            '<a href="#" class="button button-primary edit-card" data-card-id="' + card.id + '">Kartı Düzenle</a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        // Modalı sayfaya ekle
        $('body').append(modalHtml);
        
        // Modal kapanma düğmesi
        $('.card-modal-close, .card-modal-overlay').on('click', function(e) {
            if (e.target === this) {
                $('.card-modal-overlay').remove();
            }
        });
        
        // Düzenleme düğmesi
        $('.edit-card').on('click', function(e) {
            e.preventDefault();
            
            var cardId = $(this).data('card-id');
            $('.card-modal-overlay').remove();
            
            // Düzenleme formunu aç
            openCardEditForm(cardId);
        });
    }
    
    /**
     * Kart düzenleme formunu aç
     */
    function openCardEditForm(cardId) {
        // AJAX ile kart verilerini getir
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
                    // Düzenleme formunu doldur
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
                    $('#card-image-preview').html('<img src="' + card.image_url + '" alt="' + card.name + '" style="max-width: 100px; max-height: 150px;">');
                    
                    // Formu göster
                    $('#add-edit-card-form').show();
                    $('#card-list').hide();
                    
                    // Sayfa başına kaydır
                    $('html, body').animate({
                        scrollTop: $('#add-edit-card-form').offset().top - 50
                    }, 500);
                    
                    // Form başlığını güncelle
                    $('#form-title').text('Kartı Düzenle: ' + card.name);
                } else {
                    alert('Kart detayları alınamadı: ' + response.data);
                }
            },
            error: function() {
                alert('Kart detayları alınırken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
            }
        });
    }
    
    /**
     * Yeni Kart Ekle düğmesi
     */
    $('#add-new-card').on('click', function(e) {
        e.preventDefault();
        
        // Formu temizle
        $('#card-form')[0].reset();
        $('#card-id').val('');
        $('#card-image-preview').empty();
        
        // Formu göster
        $('#add-edit-card-form').show();
        $('#card-list').hide();
        
        // Form başlığını güncelle
        $('#form-title').text('Yeni Kart Ekle');
    });
    
    /**
     * Kart Türü değiştiğinde Suit alanını göster/gizle
     */
    $('#card-type').on('change', function() {
        if ($(this).val() === 'minor') {
            $('.field-suit').show();
        } else {
            $('.field-suit').hide();
        }
    });
    
    /**
     * İptal düğmesi
     */
    $('#cancel-card-form').on('click', function(e) {
        e.preventDefault();
        
        // Formu gizle, listeyi göster
        $('#add-edit-card-form').hide();
        $('#card-list').show();
    });
    
    /**
     * Kart formunu gönder
     */
    $('#card-form').on('submit', function(e) {
        e.preventDefault();
        
        // Form verilerini al
        var formData = $(this).serialize();
        
        // AJAX ile kartı kaydet
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=save_card&nonce=' + ai_tarot_admin.nonce,
            success: function(response) {
                if (response.success) {
                    // Başarılı mesajı göster
                    alert(response.data.message);
                    
                    // Sayfayı yenile
                    location.reload();
                } else {
                    // Hata mesajı göster
                    alert('Kart kaydedilirken bir hata oluştu: ' + response.data);
                }
            },
            error: function() {
                // Hata mesajı göster
                alert('Kart kaydedilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
            }
        });
    });
    
    /**
     * Kart silme
     */
    $('.delete-card').on('click', function(e) {
        e.preventDefault();
        
        var cardId = $(this).data('card-id');
        var cardName = $(this).data('card-name');
        
        if (confirm('Bu kartı silmek istediğinizden emin misiniz? (' + cardName + ')\nBu işlem geri alınamaz.')) {
            // AJAX ile kartı sil
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_card',
                    nonce: ai_tarot_admin.nonce,
                    card_id: cardId
                },
                success: function(response) {
                    if (response.success) {
                        // Başarılı mesajı göster
                        alert(response.data.message);
                        
                        // Kartı listeden kaldır
                        $('.card-row[data-card-id="' + cardId + '"]').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        // Hata mesajı göster
                        alert('Kart silinirken bir hata oluştu: ' + response.data);
                    }
                },
                error: function() {
                    // Hata mesajı göster
                    alert('Kart silinirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
                }
            });
        }
    });
    
    /**
     * Toplu kartları ekle
     */
    $('#add-missing-cards').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Eksik kartları otomatik olarak eklemek istediğinizden emin misiniz? Bu işlem, veritabanında bulunmayan tüm standart tarot kartlarını ekleyecektir.')) {
            // AJAX ile eksik kartları ekle
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'add_missing_cards',
                    nonce: ai_tarot_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Başarılı mesajı göster
                        alert(response.data.message);
                        
                        // Sayfayı yenile
                        location.reload();
                    } else {
                        // Hata mesajı göster
                        alert('Kartlar eklenirken bir hata oluştu: ' + response.data);
                    }
                },
                error: function() {
                    // Hata mesajı göster
                    alert('Kartlar eklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
                }
            });
        }
    });
    
    /**
     * İstatistik grafikleri
     */
    if ($('#reading-stats-chart').length) {
        var ctx = document.getElementById('reading-stats-chart').getContext('2d');
        
        var data = {
            labels: ai_tarot_admin.monthly_stats.labels,
            datasets: [{
                label: 'Aylık Fal Sayısı',
                data: ai_tarot_admin.monthly_stats.data,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        
        var options = {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };
        
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
    }
    
    if ($('#spread-stats-chart').length) {
        var ctx = document.getElementById('spread-stats-chart').getContext('2d');
        
        var data = {
            labels: ai_tarot_admin.spread_stats.labels,
            datasets: [{
                label: 'Açılım Türü Kullanımı',
                data: ai_tarot_admin.spread_stats.data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: data
        });
    }
    
    if ($('#ai-service-stats-chart').length) {
        var ctx = document.getElementById('ai-service-stats-chart').getContext('2d');
        
        var data = {
            labels: ai_tarot_admin.ai_service_stats.labels,
            datasets: [{
                label: 'AI Servisi Kullanımı',
                data: ai_tarot_admin.ai_service_stats.data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(201, 203, 207, 0.2)',
                    'rgba(255, 99, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(201, 203, 207, 1)',
                    'rgba(255, 99, 255, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: data
        });
    }
});