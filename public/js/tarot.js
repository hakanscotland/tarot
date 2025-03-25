/**
 * AI Tarot Frontend JavaScript
 * 
 * Tarot falı çekme işlemlerini yöneten frontend JavaScript dosyası
 */

jQuery(document).ready(function($) {
    // Tarot formunu dinle
    $('#tarot-form').on('submit', function(e) {
        e.preventDefault();
        
        // Form verilerini al
        const question = $('#tarot-question').val();
        const spreadType = $('#tarot-spread').val();
        
        if (!question) {
            showMessage('Lütfen bir soru sorun veya niyet belirtin.', 'error');
            return;
        }
        
        // Fal çekme işlemini başlat
        startTarotReading(question, spreadType);
    });
    
    // Fal çekme işlemi
    function startTarotReading(question, spreadType) {
        // Yükleniyor animasyonunu göster
        showLoadingAnimation();
        
        // Arka planı karartma efekti
        $('#tarot-container').addClass('reading-active');
        
        // Kart seçim animasyonunu başlat
        setTimeout(function() {
            // Kart sayısını belirle
            let cardCount = getCardCountForSpread(spreadType);
            
            // Kart destesini karıştırma animasyonu
            animateShuffling();
            
            // Kart çekme animasyonunu başlat
            setTimeout(function() {
                drawCards(cardCount, spreadType, question);
            }, 2000); // Karıştırma animasyonundan sonra
        }, 1000);
    }
    
    // Açılım türüne göre kart sayısını belirle
    function getCardCountForSpread(spreadType) {
        switch(spreadType) {
            case 'three_card':
                return 3;
            case 'celtic_cross':
                return 10;
            case 'astrological':
                return 12;
            default:
                return 3;
        }
    }
    
    // Kart destesi karıştırma animasyonu
    function animateShuffling() {
        const $deck = $('#tarot-deck');
        
        // Kart destesini göster
        $deck.show();
        
        // Animasyonlar etkinse
        if (ai_tarot_vars.enable_animations == 1) {
            // Karıştırma animasyonu
            for (let i = 0; i < 5; i++) {
                setTimeout(function() {
                    $deck.addClass('shuffle-' + (i % 3 + 1));
                    
                    setTimeout(function() {
                        $deck.removeClass('shuffle-' + (i % 3 + 1));
                    }, 300);
                }, i * 400);
            }
        }
    }
    
    // Kart çekme animasyonu
    function drawCards(cardCount, spreadType, question) {
        const $readingArea = $('#reading-area');
        
        // Okuma alanını temizle
        $readingArea.empty().show();
        
        // Açılım türüne göre pozisyon sınıfını belirle
        $readingArea.attr('data-spread', spreadType);
        
        // Kart pozisyonlarını hazırla
        setupCardPositions($readingArea, spreadType, cardCount);
        
        // Animasyonlar etkinse
        if (ai_tarot_vars.enable_animations == 1) {
            // Kartları çek ve yerleştir
            for (let i = 0; i < cardCount; i++) {
                setTimeout(function() {
                    // Kart elemanını oluştur (arka yüzü gösterilecek)
                    const $card = $('<div>', {
                        'class': 'tarot-card card-back',
                        'data-position': i
                    });
                    
                    // Kart arka yüzü görseli
                    $card.css('background-image', 'url(' + ai_tarot_vars.card_back_url + ')');
                    
                    // Kart yerleşimi animasyonu
                    animateCardPlacement($card, i, spreadType, $readingArea);
                    
                    // Son kart çekildikten sonra Ajax isteği gönder
                    if (i === cardCount - 1) {
                        setTimeout(function() {
                            // Ajax ile kartları ve yorumu getir
                            getCardReadingFromServer(question, spreadType, cardCount);
                        }, 1000);
                    }
                }, i * 600); // Her kart için zamanlama
            }
        } else {
            // Animasyonsuz mod - tüm kartları hemen yerleştir
            for (let i = 0; i < cardCount; i++) {
                // Kart elemanını oluştur (arka yüzü gösterilecek)
                const $card = $('<div>', {
                    'class': 'tarot-card card-back',
                    'data-position': i
                });
                
                // Kart arka yüzü görseli
                $card.css('background-image', 'url(' + ai_tarot_vars.card_back_url + ')');
                
                // Kartı doğrudan yerleştir
                const $position = $readingArea.find(`.position-${i}`);
                $position.append($card);
            }
            
            // Hemen Ajax isteği gönder
            getCardReadingFromServer(question, spreadType, cardCount);
        }
    }
    
    // Açılım türüne göre kart pozisyonlarını ayarla
    function setupCardPositions($readingArea, spreadType, cardCount) {
        // Açılım türüne göre pozisyon sınıflarını belirle
        switch(spreadType) {
            case 'three_card':
                $readingArea.addClass('three-card-spread');
                break;
            case 'celtic_cross':
                $readingArea.addClass('celtic-cross-spread');
                break;
            case 'astrological':
                $readingArea.addClass('astrological-spread');
                break;
        }
        
        // Kart pozisyonları için yerleşim alanları oluştur
        for (let i = 0; i < cardCount; i++) {
            const $position = $('<div>', {
                'class': 'card-position position-' + i,
                'data-position': i
            });
            
            // Pozisyon ismi ekle
            const positionName = getPositionName(spreadType, i);
            const $positionLabel = $('<div>', {
                'class': 'position-label',
                'text': positionName
            });
            
            $position.append($positionLabel);
            $readingArea.append($position);
        }
    }
    
    // Açılım türüne göre kart pozisyon isimlerini belirle
    function getPositionName(spreadType, position) {
        if (spreadType === 'three_card') {
            const positions = ['Geçmiş', 'Şimdi', 'Gelecek'];
            return positions[position] || `Pozisyon ${position + 1}`;
        } else if (spreadType === 'celtic_cross') {
            const positions = [
                'Mevcut Durum', 'Zorluk', 'Bilinçaltı', 'Geçmiş', 
                'Olası Sonuç', 'Yakın Gelecek', 'Kendiniz', 
                'Dış Etkiler', 'Umutlar/Korkular', 'Sonuç'
            ];
            return positions[position] || `Pozisyon ${position + 1}`;
        } else if (spreadType === 'astrological') {
            const positions = [
                '1. Ev (Kendiniz)', '2. Ev (Değerler)', '3. Ev (İletişim)', 
                '4. Ev (Yuva)', '5. Ev (Yaratıcılık)', '6. Ev (Sağlık)', 
                '7. Ev (İlişkiler)', '8. Ev (Dönüşüm)', '9. Ev (Felsefe)', 
                '10. Ev (Kariyer)', '11. Ev (Sosyal Çevre)', '12. Ev (Bilinçaltı)'
            ];
            return positions[position] || `Pozisyon ${position + 1}`;
        }
        
        return `Pozisyon ${position + 1}`;
    }
    
    // Kart yerleşimi animasyonu
    function animateCardPlacement($card, position, spreadType, $readingArea) {
        // Kart destesinden pozisyona doğru hareket
        const $deck = $('#tarot-deck');
        const $position = $readingArea.find(`.position-${position}`);
        
        // Kart başlangıç pozisyonu (deste)
        const deckOffset = $deck.offset();
        
        // Kart hedef pozisyonu
        const positionOffset = $position.offset();
        
        // Kartı başlangıçta desteye yerleştir
        $card.css({
            position: 'absolute',
            left: deckOffset.left,
            top: deckOffset.top,
            zIndex: 100 + position
        });
        
        // Kartı sayfaya ekle
        $('body').append($card);
        
        // Kartı hedefe doğru animate et
        $card.animate({
            left: positionOffset.left,
            top: positionOffset.top
        }, 600, function() {
            // Animasyon tamamlandığında kartı pozisyona yerleştir
            $card.css({
                position: 'relative',
                left: 0,
                top: 0
            });
            
            $position.append($card);
            
            // Kart çevirme efekti için hazırla
            setTimeout(function() {
                $card.addClass('flip-ready');
            }, 200);
        });
    }
    
    // Sunucudan kart yorumu al
    function getCardReadingFromServer(question, spreadType, cardCount) {
        // AJAX isteği
        $.ajax({
            url: ai_tarot_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'get_tarot_reading',
                question: question,
                spread_type: spreadType,
                card_count: cardCount,
                nonce: ai_tarot_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Kartları ve yorumu göster
                    revealCards(response.data.cards);
                    
                    // Yorumu göster
                    setTimeout(function() {
                        displayInterpretation(response.data.interpretation, response.data.reading_id, response.data.ai_service);
                    }, ai_tarot_vars.enable_animations == 1 ? cardCount * 500 + 1000 : 300); // Tüm kartlar açıldıktan sonra
                } else {
                    showMessage(ai_tarot_vars.error_text, 'error');
                    resetTarotReading();
                }
            },
            error: function() {
                showMessage(ai_tarot_vars.error_text, 'error');
                resetTarotReading();
            }
        });
    }
    
    // Kartları aç ve göster
    function revealCards(cards) {
        // Animasyonlar etkinse
        if (ai_tarot_vars.enable_animations == 1) {
            // Her kart için
            $.each(cards, function(index, card) {
                setTimeout(function() {
                    const $cardElement = $(`.card-position[data-position="${index}"] .tarot-card`);
                    
                    // Kart bilgilerini ekle
                    $cardElement.attr('data-card-id', card.id);
                    $cardElement.attr('data-reversed', card.reversed ? '1' : '0');
                    
                    // Kart çevirme animasyonu
                    $cardElement.addClass('flipping');
                    
                    setTimeout(function() {
                        // Arka tarafı gizle, ön tarafı göster
                        $cardElement.removeClass('card-back').addClass('card-front');
                        
                        // Kart görselini ekle
                        const cardImage = $('<img>', {
                            src: card.image_url,
                            alt: card.name
                        });
                        
                        $cardElement.html(cardImage);
                        
                        // Ters kart ise döndür
                        if (card.reversed) {
                            $cardElement.addClass('reversed');
                        }
                        
                        // Kart ismini ekle
                        const $cardName = $('<div>', {
                            'class': 'card-name',
                            'text': card.name + (card.reversed ? ' (Ters)' : '')
                        });
                        
                        $cardElement.parent().append($cardName);
                        
                        setTimeout(function() {
                            $cardElement.removeClass('flipping');
                        }, 300);
                    }, 150);
                }, index * 500); // Her kart için zamanlama
            });
        } else {
            // Animasyonsuz mod - tüm kartları hemen göster
            $.each(cards, function(index, card) {
                const $cardElement = $(`.card-position[data-position="${index}"] .tarot-card`);
                
                // Kart bilgilerini ekle
                $cardElement.attr('data-card-id', card.id);
                $cardElement.attr('data-reversed', card.reversed ? '1' : '0');
                
                // Arka tarafı gizle, ön tarafı göster
                $cardElement.removeClass('card-back').addClass('card-front');
                
                // Kart görselini ekle
                const cardImage = $('<img>', {
                    src: card.image_url,
                    alt: card.name
                });
                
                $cardElement.html(cardImage);
                
                // Ters kart ise döndür
                if (card.reversed) {
                    $cardElement.addClass('reversed');
                }
                
                // Kart ismini ekle
                const $cardName = $('<div>', {
                    'class': 'card-name',
                    'text': card.name + (card.reversed ? ' (Ters)' : '')
                });
                
                $cardElement.parent().append($cardName);
            });
        }
    }
    
    // Yorumu göster
    function displayInterpretation(interpretation, reading_id, ai_service) {
        const $interpretationArea = $('#interpretation-area');
        
        // Yorumu temizle ve göster
        $interpretationArea.empty().show();
        
        // Yorum başlığı
        const $title = $('<h3>', {
            'class': 'interpretation-title',
            text: 'Tarot Falı Yorumu'
        });
        
        // AI Servisi bilgisi
        const $aiInfo = $('<div>', {
            'class': 'ai-service-info'
        });
        
        // AI servisi adını formatlı göster
        let ai_service_name = '';
        switch (ai_service) {
            case 'openai':
                ai_service_name = 'OpenAI (ChatGPT)';
                break;
            case 'claude':
                ai_service_name = 'Anthropic Claude';
                break;
            case 'gemini':
                ai_service_name = 'Google Gemini';
                break;
            case 'perplexity':
                ai_service_name = 'Perplexity.ai';
                break;
            case 'deepseek':
                ai_service_name = 'DeepSeek';
                break;
            case 'grok':
                ai_service_name = 'Grok';
                break;
            case 'meta':
                ai_service_name = 'Meta (Llama)';
                break;
            case 'copilot':
                ai_service_name = 'Microsoft Copilot';
                break;
            default:
                ai_service_name = ai_service;
                break;
        }
        
        $aiInfo.html('Bu yorum <strong>' + ai_service_name + '</strong> tarafından oluşturulmuştur.');
        
        // Yorum içeriği
        const $content = $('<div>', {
            'class': 'interpretation-content'
        });
        
        // Paragrafları oluştur
        const paragraphs = interpretation.split('\n\n');
        $.each(paragraphs, function(i, paragraph) {
            const $p = $('<p>').html(paragraph.replace(/\n/g, '<br>'));
            $content.append($p);
        });
        
        // Paylaşım düğmeleri
        const $shareButtons = $('<div>', {
            'class': 'share-buttons'
        });
        
        const $facebookButton = $('<button>', {
            'class': 'share-button facebook',
            'text': 'Facebook\'ta Paylaş'
        });
        
        const $twitterButton = $('<button>', {
            'class': 'share-button twitter',
            'text': 'Twitter\'da Paylaş'
        });
        
        const $emailButton = $('<button>', {
            'class': 'share-button email',
            'text': 'E-posta ile Gönder'
        });
        
        const $saveButton = $('<button>', {
            'class': 'save-button',
            'text': 'Bu Falı Kaydet'
        });
        
        $shareButtons.append($facebookButton, $twitterButton, $emailButton, $saveButton);
        
        // Yeni bir fal çekme butonu
        const $newReadingButton = $('<button>', {
            'class': 'new-reading-button',
            'text': 'Yeni Fal Çek'
        });
        
        $newReadingButton.on('click', function() {
            resetTarotReading();
        });
        
        // Reading ID'yi gizli alan olarak ekle
        const $readingIdInput = $('<input>', {
            'type': 'hidden',
            'id': 'reading-id',
            'value': reading_id
        });
        
        // Tümünü ekle
        $interpretationArea.append($title, $aiInfo, $content, $shareButtons, $newReadingButton, $readingIdInput);
        
        // Fade-in animasyonu
        $interpretationArea.hide().fadeIn(800);
        
        // Yükleniyor animasyonunu gizle
        hideLoadingAnimation();
        
        // Paylaşım düğmelerini ayarla
        setupShareButtons(reading_id);
    }
    
    // Paylaşım düğmelerini ayarla
    function setupShareButtons(reading_id) {
        // Facebook paylaşım
        $('.share-button.facebook').on('click', function() {
            const shareUrl = window.location.href;
            const shareTitle = 'Tarot Falım';
            
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl), '_blank');
        });
        
        // Twitter paylaşım
        $('.share-button.twitter').on('click', function() {
            const shareUrl = window.location.href;
            const shareText = 'Tarot falıma göz atın:';
            
            window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(shareText) + '&url=' + encodeURIComponent(shareUrl), '_blank');
        });
        
        // E-posta paylaşım
        $('.share-button.email').on('click', function() {
            const shareUrl = window.location.href;
            const shareSubject = 'Tarot Falım';
            const shareBody = 'Tarot falıma göz atın: ' + shareUrl;
            
            window.location.href = 'mailto:?subject=' + encodeURIComponent(shareSubject) + '&body=' + encodeURIComponent(shareBody);
        });
        
        // Falı kaydet
        $('.save-button').on('click', function() {
            saveTarotReading(reading_id);
        });
    }
    
    // Falı kaydet
    function saveTarotReading(reading_id) {
        // Kullanıcı giriş yapmış mı kontrol et
        if (ai_tarot_vars.is_logged_in == 1) {
            // AJAX isteği
            $.ajax({
                url: ai_tarot_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_tarot_reading',
                    reading_id: reading_id,
                    nonce: ai_tarot_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(ai_tarot_vars.save_success, 'success');
                    } else {
                        showMessage(ai_tarot_vars.save_error, 'error');
                    }
                },
                error: function() {
                    showMessage(ai_tarot_vars.save_error, 'error');
                }
            });
        } else {
            // Giriş yapmamış kullanıcılar için
            if (confirm(ai_tarot_vars.login_required + ' Şimdi giriş yapmak ister misiniz?')) {
                window.location.href = '/wp-login.php?redirect_to=' + encodeURIComponent(window.location.href);
            } else {
                // Yine de misafir olarak kaydet
                $.ajax({
                    url: ai_tarot_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'save_guest_reading',
                        reading_id: reading_id,
                        nonce: ai_tarot_vars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage('Falınız misafir olarak kaydedildi. Hesap oluşturursanız, gelecekte bu fala erişebilirsiniz.', 'success');
                        } else {
                            showMessage(ai_tarot_vars.save_error, 'error');
                        }
                    },
                    error: function() {
                        showMessage(ai_tarot_vars.save_error, 'error');
                    }
                });
            }
        }
    }
    
    // Yükleniyor animasyonunu göster
    function showLoadingAnimation() {
        const $loader = $('#tarot-loader');
        $loader.show();
        
        // Yükleniyor mesajını ekle
        const $loaderText = $loader.find('.loader-text');
        $loaderText.text(ai_tarot_vars.loading_text);
    }
    
    // Yükleniyor animasyonunu gizle
    function hideLoadingAnimation() {
        const $loader = $('#tarot-loader');
        $loader.hide();
    }
    
    // Mesaj göster
    function showMessage(message, type) {
        const $messageArea = $('#tarot-messages');
        
        const $message = $('<div>', {
            'class': 'tarot-message ' + type,
            'text': message
        });
        
        $messageArea.empty().append($message).show();
        
        // 5 saniye sonra kaybolsun
        setTimeout(function() {
            $message.fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Tarot falını sıfırla
    function resetTarotReading() {
        // Tüm alanları temizle
        $('#reading-area').empty().hide();
        $('#interpretation-area').empty().hide();
        
        // Formu göster
        $('#tarot-form-container').show();
        
        // Arka plan efektini kaldır
        $('#tarot-container').removeClass('reading-active');
        
        // Yükleniyor animasyonunu gizle
        hideLoadingAnimation();
        
        // Sayfayı form alanına kaydır
        $('html, body').animate({
            scrollTop: $('#tarot-form-container').offset().top - 50
        }, 500);
    }
});