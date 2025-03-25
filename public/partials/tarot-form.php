<?php
/**
 * Tarot Form Template
 *
 * This file contains the template for the tarot reading form and results display.
 * It handles the user input, card display, and interpretation presentation.
 *
 * @package AI_Tarot
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="tarot-container" class="ai-tarot-container <?php echo esc_attr($theme_class); ?>">
    <div class="tarot-form-container" id="tarot-form-container">
        <h2 class="tarot-form-title"><?php _e('AI Tarot Falı', 'ai-tarot'); ?></h2>
        <p class="tarot-form-description"><?php _e('Sorunuzu sorun ve açılım türünü seçin. Yapay zeka destekli tarot falı size içgörüler sunacaktır.', 'ai-tarot'); ?></p>
        
        <form id="tarot-form" class="tarot-form">
            <div class="form-group">
                <label for="tarot-question"><?php _e('Soru veya Niyet:', 'ai-tarot'); ?></label>
                <input type="text" id="tarot-question" name="question" placeholder="<?php _e('Tarot\'a sormak istediğiniz soruyu yazın...', 'ai-tarot'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tarot-spread"><?php _e('Açılım Türü:', 'ai-tarot'); ?></label>
                <select id="tarot-spread" name="spread_type">
                    <option value="three_card" <?php selected($default_spread, 'three_card'); ?>><?php _e('Üç Kartlık Açılım', 'ai-tarot'); ?></option>
                    <option value="celtic_cross" <?php selected($default_spread, 'celtic_cross'); ?>><?php _e('Kelt Haçı Açılımı', 'ai-tarot'); ?></option>
                    <option value="astrological" <?php selected($default_spread, 'astrological'); ?>><?php _e('Astrolojik Açılım', 'ai-tarot'); ?></option>
                </select>
            </div>
            
            <?php if ($show_animations): ?>
            <div class="form-group animation-toggle">
                <label for="enable-animations">
                    <input type="checkbox" id="enable-animations" name="enable_animations" checked>
                    <?php _e('Kart çekme animasyonlarını etkinleştir', 'ai-tarot'); ?>
                </label>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <button type="submit" class="tarot-submit-button"><?php _e('Tarot Falı Çek', 'ai-tarot'); ?></button>
            </div>
        </form>
        
        <?php if (!is_user_logged_in() && $allow_save): ?>
        <div class="login-prompt">
            <p><?php _e('Falınızı kaydetmek ve geçmiş fallarınıza erişmek için', 'ai-tarot'); ?> <a href="<?php echo wp_login_url(get_permalink()); ?>"><?php _e('giriş yapın', 'ai-tarot'); ?></a>.</p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($attributes['help_text']) && !empty($attributes['help_text'])): ?>
        <div class="tarot-help-text">
            <h3><?php _e('Tarot Falı Hakkında', 'ai-tarot'); ?></h3>
            <p><?php echo wp_kses_post($attributes['help_text']); ?></p>
        </div>
        <?php else: ?>
        <div class="tarot-help-text">
            <h3><?php _e('Tarot Falı Hakkında', 'ai-tarot'); ?></h3>
            <p><?php _e('Tarot falı, kişisel içgörüler ve rehberlik sağlayan geleneksel bir metottur. AI Tarot, yapay zeka teknolojisini kullanarak tarot kart açılımlarınızı yorumlar.', 'ai-tarot'); ?></p>
            <p><?php _e('En iyi sonuçlar için, açık ve net bir soru sorun veya bir niyet belirtin.', 'ai-tarot'); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Tarot destesi -->
    <div id="tarot-deck" class="tarot-deck" style="display:none; background-image: url('<?php echo esc_url(AI_TAROT_PLUGIN_URL . 'public/images/backs/card-back-' . $deck_theme . '.jpg'); ?>');"></div>
    
    <!-- Kart okuma alanı -->
    <div id="reading-area" class="reading-area"></div>
    
    <!-- Yorum alanı -->
    <div id="interpretation-area" class="interpretation-area"></div>
    
    <!-- Mesaj alanı -->
    <div id="tarot-messages" class="tarot-messages"></div>
    
    <!-- Yükleniyor animasyonu -->
    <div id="tarot-loader" class="tarot-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-text"><?php _e('Tarot falınız hazırlanıyor...', 'ai-tarot'); ?></div>
        </div>
    </div>
    
    <!-- Açılım bilgileri (bilgilendirme bölümü) -->
    <div class="spread-info-container">
        <div class="spread-info" id="three-card-info" style="display:none;">
            <h3><?php _e('Üç Kartlık Açılım', 'ai-tarot'); ?></h3>
            <p><?php _e('Bu basit açılım, geçmiş, şimdi ve gelecek temalarını temsil eden üç kart içerir. Belirli bir durum hakkında hızlı bir bakış sağlar.', 'ai-tarot'); ?></p>
            <ul>
                <li><strong><?php _e('İlk Kart:', 'ai-tarot'); ?></strong> <?php _e('Geçmiş - Sizi bugüne getiren etkiler', 'ai-tarot'); ?></li>
                <li><strong><?php _e('İkinci Kart:', 'ai-tarot'); ?></strong> <?php _e('Şimdi - Mevcut durumunuz', 'ai-tarot'); ?></li>
                <li><strong><?php _e('Üçüncü Kart:', 'ai-tarot'); ?></strong> <?php _e('Gelecek - Olası bir sonuç', 'ai-tarot'); ?></li>
            </ul>
        </div>
        
        <div class="spread-info" id="celtic-cross-info" style="display:none;">
            <h3><?php _e('Kelt Haçı Açılımı', 'ai-tarot'); ?></h3>
            <p><?php _e('Kelt Haçı, en popüler ve kapsamlı tarot açılımlarından biridir. Bir duruma derinlemesine bakış sağlar.', 'ai-tarot'); ?></p>
            <ul>
                <li><strong><?php _e('1. Kart:', 'ai-tarot'); ?></strong> <?php _e('Mevcut Durum - Sizi etkileyen ana faktör', 'ai-tarot'); ?></li>
                <li><strong><?php _e('2. Kart:', 'ai-tarot'); ?></strong> <?php _e('Zorluk - Karşılaştığınız engel', 'ai-tarot'); ?></li>
                <li><strong><?php _e('3. Kart:', 'ai-tarot'); ?></strong> <?php _e('Bilinçaltı - Altta yatan etkenler', 'ai-tarot'); ?></li>
                <li><strong><?php _e('4. Kart:', 'ai-tarot'); ?></strong> <?php _e('Geçmiş - Yakın geçmişteki etkiler', 'ai-tarot'); ?></li>
                <li><strong><?php _e('5. Kart:', 'ai-tarot'); ?></strong> <?php _e('Olası Sonuç - Mevcut yolunuzda ilerlerseniz', 'ai-tarot'); ?></li>
                <li><strong><?php _e('6. Kart:', 'ai-tarot'); ?></strong> <?php _e('Yakın Gelecek - Önümüzdeki günlerde', 'ai-tarot'); ?></li>
                <li><strong><?php _e('7. Kart:', 'ai-tarot'); ?></strong> <?php _e('Kendiniz - Duruma yaklaşımınız', 'ai-tarot'); ?></li>
                <li><strong><?php _e('8. Kart:', 'ai-tarot'); ?></strong> <?php _e('Dış Etkiler - Çevreniz ve diğer insanlar', 'ai-tarot'); ?></li>
                <li><strong><?php _e('9. Kart:', 'ai-tarot'); ?></strong> <?php _e('Umutlar/Korkular - İçsel dilekler ve endişeler', 'ai-tarot'); ?></li>
                <li><strong><?php _e('10. Kart:', 'ai-tarot'); ?></strong> <?php _e('Sonuç - Nihai sonuç', 'ai-tarot'); ?></li>
            </ul>
        </div>
        
        <div class="spread-info" id="astrological-info" style="display:none;">
            <h3><?php _e('Astrolojik Açılım', 'ai-tarot'); ?></h3>
            <p><?php _e('Astrolojik açılım, 12 astrolojik evi temsil eden kartlarla yaşamınızın farklı alanlarını inceler.', 'ai-tarot'); ?></p>
            <ul>
                <li><strong><?php _e('1. Ev:', 'ai-tarot'); ?></strong> <?php _e('Kendiniz - Kişiliğiniz ve görünüşünüz', 'ai-tarot'); ?></li>
                <li><strong><?php _e('2. Ev:', 'ai-tarot'); ?></strong> <?php _e('Değerler - Finansal durumunuz ve varlıklarınız', 'ai-tarot'); ?></li>
                <li><strong><?php _e('3. Ev:', 'ai-tarot'); ?></strong> <?php _e('İletişim - İletişim tarzınız ve yakın çevre', 'ai-tarot'); ?></li>
                <li><strong><?php _e('4. Ev:', 'ai-tarot'); ?></strong> <?php _e('Yuva - Aileniz ve kökenleriniz', 'ai-tarot'); ?></li>
                <li><strong><?php _e('5. Ev:', 'ai-tarot'); ?></strong> <?php _e('Yaratıcılık - Kendini ifade ve eğlence', 'ai-tarot'); ?></li>
                <li><strong><?php _e('6. Ev:', 'ai-tarot'); ?></strong> <?php _e('Sağlık - Günlük rutinler ve sağlık', 'ai-tarot'); ?></li>
                <li><strong><?php _e('7. Ev:', 'ai-tarot'); ?></strong> <?php _e('İlişkiler - Partnerlikler ve evlilik', 'ai-tarot'); ?></li>
                <li><strong><?php _e('8. Ev:', 'ai-tarot'); ?></strong> <?php _e('Dönüşüm - Paylaşılan kaynaklar ve dönüşüm', 'ai-tarot'); ?></li>
                <li><strong><?php _e('9. Ev:', 'ai-tarot'); ?></strong> <?php _e('Felsefe - Yüksek öğrenim ve inançlar', 'ai-tarot'); ?></li>
                <li><strong><?php _e('10. Ev:', 'ai-tarot'); ?></strong> <?php _e('Kariyer - Mesleki yol ve toplumsal statü', 'ai-tarot'); ?></li>
                <li><strong><?php _e('11. Ev:', 'ai-tarot'); ?></strong> <?php _e('Sosyal Çevre - Arkadaşlıklar ve gruplar', 'ai-tarot'); ?></li>
                <li><strong><?php _e('12. Ev:', 'ai-tarot'); ?></strong> <?php _e('Bilinçaltı - Ruhsal gelişim ve sınırlamalar', 'ai-tarot'); ?></li>
            </ul>
        </div>
        
        <button id="toggle-spread-info" class="toggle-info-button"><?php _e('Açılım Bilgilerini Göster', 'ai-tarot'); ?></button>
    </div>
</div>