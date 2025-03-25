<?php
/**
 * Tarot Results Template
 *
 * This file contains the template for displaying tarot reading results.
 * It shows the cards drawn and the AI-generated interpretation.
 *
 * @package AI_Tarot
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure we have required variables
if (!isset($cards) || !isset($interpretation) || !isset($question) || !isset($spread_type) || !isset($reading_id)) {
    return;
}

// Get spread position names
$position_names = array();
switch ($spread_type) {
    case 'three_card':
        $position_names = array(
            __('Geçmiş', 'ai-tarot'),
            __('Şimdi', 'ai-tarot'),
            __('Gelecek', 'ai-tarot')
        );
        break;
    case 'celtic_cross':
        $position_names = array(
            __('Mevcut Durum', 'ai-tarot'),
            __('Zorluk', 'ai-tarot'),
            __('Bilinçaltı', 'ai-tarot'),
            __('Geçmiş', 'ai-tarot'),
            __('Olası Sonuç', 'ai-tarot'),
            __('Yakın Gelecek', 'ai-tarot'),
            __('Kendiniz', 'ai-tarot'),
            __('Dış Etkiler', 'ai-tarot'),
            __('Umutlar/Korkular', 'ai-tarot'),
            __('Sonuç', 'ai-tarot')
        );
        break;
    case 'astrological':
        $position_names = array(
            __('1. Ev (Kendiniz)', 'ai-tarot'),
            __('2. Ev (Değerler)', 'ai-tarot'),
            __('3. Ev (İletişim)', 'ai-tarot'),
            __('4. Ev (Yuva)', 'ai-tarot'),
            __('5. Ev (Yaratıcılık)', 'ai-tarot'),
            __('6. Ev (Sağlık)', 'ai-tarot'),
            __('7. Ev (İlişkiler)', 'ai-tarot'),
            __('8. Ev (Dönüşüm)', 'ai-tarot'),
            __('9. Ev (Felsefe)', 'ai-tarot'),
            __('10. Ev (Kariyer)', 'ai-tarot'),
            __('11. Ev (Sosyal Çevre)', 'ai-tarot'),
            __('12. Ev (Bilinçaltı)', 'ai-tarot')
        );
        break;
    default:
        // Fallback for unknown spread types
        for ($i = 0; $i < count($cards); $i++) {
            $position_names[] = sprintf(__('Pozisyon %d', 'ai-tarot'), $i + 1);
        }
        break;
}

// Format spread type for display
$spread_type_display = '';
switch ($spread_type) {
    case 'three_card':
        $spread_type_display = __('Üç Kartlık Açılım', 'ai-tarot');
        break;
    case 'celtic_cross':
        $spread_type_display = __('Kelt Haçı Açılımı', 'ai-tarot');
        break;
    case 'astrological':
        $spread_type_display = __('Astrolojik Açılım', 'ai-tarot');
        break;
    default:
        $spread_type_display = ucfirst(str_replace('_', ' ', $spread_type));
        break;
}

// Get AI service display name
$ai_service_display = '';
switch ($ai_service) {
    case 'openai':
        $ai_service_display = 'OpenAI (ChatGPT)';
        break;
    case 'claude':
        $ai_service_display = 'Anthropic Claude';
        break;
    case 'gemini':
        $ai_service_display = 'Google Gemini';
        break;
    case 'perplexity':
        $ai_service_display = 'Perplexity.ai';
        break;
    case 'deepseek':
        $ai_service_display = 'DeepSeek';
        break;
    case 'grok':
        $ai_service_display = 'Grok';
        break;
    case 'meta':
        $ai_service_display = 'Meta (Llama)';
        break;
    case 'copilot':
        $ai_service_display = 'Microsoft Copilot';
        break;
    default:
        $ai_service_display = ucfirst($ai_service);
        break;
}

// Current date and time
$reading_date = current_time(get_option('date_format') . ' ' . get_option('time_format'));
?>

<div class="tarot-results-container">
    <div class="tarot-results-header">
        <h2 class="results-title"><?php _e('Tarot Falı Sonucu', 'ai-tarot'); ?></h2>
        <div class="results-meta">
            <div class="result-question">
                <span class="meta-label"><?php _e('Soru:', 'ai-tarot'); ?></span>
                <span class="meta-value"><?php echo esc_html($question); ?></span>
            </div>
            <div class="result-spread-type">
                <span class="meta-label"><?php _e('Açılım:', 'ai-tarot'); ?></span>
                <span class="meta-value"><?php echo esc_html($spread_type_display); ?></span>
            </div>
            <div class="result-date">
                <span class="meta-label"><?php _e('Tarih:', 'ai-tarot'); ?></span>
                <span class="meta-value"><?php echo esc_html($reading_date); ?></span>
            </div>
        </div>
    </div>
    
    <div class="tarot-cards-display <?php echo esc_attr('spread-' . $spread_type); ?>">
        <h3><?php _e('Çekilen Kartlar', 'ai-tarot'); ?></h3>
        
        <div class="cards-container">
            <?php foreach ($cards as $index => $card) : ?>
                <div class="card-box" data-position="<?php echo esc_attr($index); ?>">
                    <div class="card-image-container <?php echo $card['reversed'] ? 'reversed' : ''; ?>">
                        <img src="<?php echo esc_url($card['image_url']); ?>" alt="<?php echo esc_attr($card['name']); ?>" class="card-image">
                    </div>
                    <div class="card-details">
                        <h4 class="card-title"><?php echo esc_html($card['name']); ?> <?php echo $card['reversed'] ? __('(Ters)', 'ai-tarot') : ''; ?></h4>
                        <div class="card-position"><?php echo isset($position_names[$index]) ? esc_html($position_names[$index]) : sprintf(__('Pozisyon %d', 'ai-tarot'), $index + 1); ?></div>
                        <div class="card-keywords">
                            <span class="keywords-label"><?php _e('Anahtar Kelimeler:', 'ai-tarot'); ?></span>
                            <span class="keywords-value"><?php echo esc_html($card['keywords']); ?></span>
                        </div>
                        <div class="card-meaning">
                            <p><?php echo esc_html($card['meaning']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="tarot-interpretation">
        <h3><?php _e('Tarot Falı Yorumu', 'ai-tarot'); ?></h3>
        
        <div class="ai-service-info">
            <?php printf(__('Bu yorum <strong>%s</strong> tarafından oluşturulmuştur.', 'ai-tarot'), esc_html($ai_service_display)); ?>
        </div>
        
        <div class="interpretation-content">
            <?php 
            // Format interpretation with proper paragraphs
            $paragraphs = explode("\n\n", $interpretation);
            foreach ($paragraphs as $paragraph) {
                if (!empty(trim($paragraph))) {
                    echo '<p>' . nl2br(esc_html($paragraph)) . '</p>';
                }
            }
            ?>
        </div>
    </div>
    
    <div class="tarot-actions">
        <div class="action-buttons">
            <?php if (is_user_logged_in()) : ?>
                <button class="save-reading-button" data-reading-id="<?php echo esc_attr($reading_id); ?>">
                    <?php _e('Bu Falı Kaydet', 'ai-tarot'); ?>
                </button>
            <?php else : ?>
                <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="login-to-save-button">
                    <?php _e('Kaydetmek için Giriş Yap', 'ai-tarot'); ?>
                </a>
            <?php endif; ?>
            
            <button class="print-reading-button">
                <?php _e('Yazdır', 'ai-tarot'); ?>
            </button>
            
            <div class="share-buttons">
                <button class="share-button share-facebook" data-url="<?php echo esc_url(get_permalink()); ?>">
                    <?php _e('Facebook\'ta Paylaş', 'ai-tarot'); ?>
                </button>
                <button class="share-button share-twitter" data-url="<?php echo esc_url(get_permalink()); ?>" data-text="<?php echo esc_attr(__('Tarot falıma göz atın!', 'ai-tarot')); ?>">
                    <?php _e('Twitter\'da Paylaş', 'ai-tarot'); ?>
                </button>
                <button class="share-button share-email" data-subject="<?php echo esc_attr(__('Tarot Falım', 'ai-tarot')); ?>" data-body="<?php echo esc_attr(__('Tarot falıma göz atın:', 'ai-tarot') . ' ' . get_permalink()); ?>">
                    <?php _e('E-posta ile Paylaş', 'ai-tarot'); ?>
                </button>
            </div>
        </div>
        
        <div class="new-reading-buttons">
            <a href="<?php echo esc_url(remove_query_arg(array('reading_id'))); ?>" class="new-reading-button">
                <?php _e('Yeni Fal Çek', 'ai-tarot'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg(array('question' => urlencode($question)))); ?>" class="same-question-button">
                <?php _e('Aynı Soru ile Tekrar Çek', 'ai-tarot'); ?>
            </a>
        </div>
    </div>
    
    <?php if (isset($related_readings) && !empty($related_readings)) : ?>
    <div class="related-readings">
        <h3><?php _e('İlgili Fallar', 'ai-tarot'); ?></h3>
        <ul class="related-readings-list">
            <?php foreach ($related_readings as $related) : ?>
                <li>
                    <a href="<?php echo esc_url(add_query_arg('reading_id', $related['id'])); ?>">
                        <?php echo esc_html($related['question']); ?>
                        <span class="reading-date"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($related['created_at']))); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <input type="hidden" id="reading-id" value="<?php echo esc_attr($reading_id); ?>">
</div>