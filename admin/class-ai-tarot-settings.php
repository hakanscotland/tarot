<?php
/**
 * AI Tarot Ayarlar Sayfası
 * 
 * Bu dosya, eklentinin ayarlar sayfasını içerir.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

// Ayarlar sınıfı
class AI_Tarot_Settings {
    
    /**
     * Ayarlar
     */
    private $settings;
    
    /**
     * Sınıfı başlat
     */
    public function __construct() {
        // Ayarları yükle
        $this->settings = get_option('ai_tarot_settings', array());
        
        // Ayarlar sayfasını oluştur
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Ayarları kaydet
     */
    public function register_settings() {
        register_setting('ai_tarot_settings', 'ai_tarot_settings', array($this, 'sanitize_settings'));
        
        // Genel ayarlar bölümü
        add_settings_section(
            'ai_tarot_general_section',
            'Genel Ayarlar',
            array($this, 'render_general_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'ai_service',
            'AI Servisi',
            array($this, 'render_ai_service_field'),
            'ai_tarot_settings',
            'ai_tarot_general_section'
        );
        
        add_settings_field(
            'auto_save',
            'Otomatik Kayıt',
            array($this, 'render_auto_save_field'),
            'ai_tarot_settings',
            'ai_tarot_general_section'
        );
        
        add_settings_field(
            'enable_animations',
            'Animasyonlar',
            array($this, 'render_enable_animations_field'),
            'ai_tarot_settings',
            'ai_tarot_general_section'
        );
        
        add_settings_field(
            'card_deck_theme',
            'Kart Destesi Teması',
            array($this, 'render_card_deck_theme_field'),
            'ai_tarot_settings',
            'ai_tarot_general_section'
        );
        
        add_settings_field(
            'custom_css',
            'Özel CSS',
            array($this, 'render_custom_css_field'),
            'ai_tarot_settings',
            'ai_tarot_general_section'
        );
        
        // OpenAI ayarlar bölümü
        add_settings_section(
            'ai_tarot_openai_section',
            'OpenAI (ChatGPT) Ayarları',
            array($this, 'render_openai_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'openai_api_key',
            'OpenAI API Anahtarı',
            array($this, 'render_openai_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_openai_section'
        );
        
        add_settings_field(
            'openai_model',
            'OpenAI Model',
            array($this, 'render_openai_model_field'),
            'ai_tarot_settings',
            'ai_tarot_openai_section'
        );
        
        add_settings_field(
            'openai_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_openai_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_openai_section'
        );
        
        add_settings_field(
            'openai_max_tokens',
            'Maksimum Token',
            array($this, 'render_openai_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_openai_section'
        );
        
        // Claude ayarlar bölümü
        add_settings_section(
            'ai_tarot_claude_section',
            'Anthropic Claude Ayarları',
            array($this, 'render_claude_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'claude_api_key',
            'Claude API Anahtarı',
            array($this, 'render_claude_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_claude_section'
        );
        
        add_settings_field(
            'claude_model',
            'Claude Model',
            array($this, 'render_claude_model_field'),
            'ai_tarot_settings',
            'ai_tarot_claude_section'
        );
        
        add_settings_field(
            'claude_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_claude_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_claude_section'
        );
        
        add_settings_field(
            'claude_max_tokens',
            'Maksimum Token',
            array($this, 'render_claude_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_claude_section'
        );
        
        // Google Gemini ayarlar bölümü
        add_settings_section(
            'ai_tarot_gemini_section',
            'Google Gemini Ayarları',
            array($this, 'render_gemini_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'gemini_api_key',
            'Gemini API Anahtarı',
            array($this, 'render_gemini_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_gemini_section'
        );
        
        add_settings_field(
            'gemini_model',
            'Gemini Model',
            array($this, 'render_gemini_model_field'),
            'ai_tarot_settings',
            'ai_tarot_gemini_section'
        );
        
        add_settings_field(
            'gemini_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_gemini_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_gemini_section'
        );
        
        add_settings_field(
            'gemini_max_tokens',
            'Maksimum Token',
            array($this, 'render_gemini_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_gemini_section'
        );
        
        // Perplexity.ai ayarlar bölümü
        add_settings_section(
            'ai_tarot_perplexity_section',
            'Perplexity.ai Ayarları',
            array($this, 'render_perplexity_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'perplexity_api_key',
            'Perplexity API Anahtarı',
            array($this, 'render_perplexity_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_perplexity_section'
        );
        
        add_settings_field(
            'perplexity_model',
            'Perplexity Model',
            array($this, 'render_perplexity_model_field'),
            'ai_tarot_settings',
            'ai_tarot_perplexity_section'
        );
        
        add_settings_field(
            'perplexity_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_perplexity_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_perplexity_section'
        );
        
        add_settings_field(
            'perplexity_max_tokens',
            'Maksimum Token',
            array($this, 'render_perplexity_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_perplexity_section'
        );
        
        // DeepSeek ayarlar bölümü
        add_settings_section(
            'ai_tarot_deepseek_section',
            'DeepSeek Ayarları',
            array($this, 'render_deepseek_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'deepseek_api_key',
            'DeepSeek API Anahtarı',
            array($this, 'render_deepseek_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_deepseek_section'
        );
        
        add_settings_field(
            'deepseek_model',
            'DeepSeek Model',
            array($this, 'render_deepseek_model_field'),
            'ai_tarot_settings',
            'ai_tarot_deepseek_section'
        );
        
        add_settings_field(
            'deepseek_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_deepseek_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_deepseek_section'
        );
        
        add_settings_field(
            'deepseek_max_tokens',
            'Maksimum Token',
            array($this, 'render_deepseek_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_deepseek_section'
        );
        
        // Grok ayarlar bölümü
        add_settings_section(
            'ai_tarot_grok_section',
            'Grok Ayarları',
            array($this, 'render_grok_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'grok_api_key',
            'Grok API Anahtarı',
            array($this, 'render_grok_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_grok_section'
        );
        
        add_settings_field(
            'grok_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_grok_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_grok_section'
        );
        
        add_settings_field(
            'grok_max_tokens',
            'Maksimum Token',
            array($this, 'render_grok_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_grok_section'
        );
        
        // Meta (Llama) ayarlar bölümü
        add_settings_section(
            'ai_tarot_meta_section',
            'Meta (Llama) Ayarları',
            array($this, 'render_meta_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'meta_api_key',
            'Meta API Anahtarı',
            array($this, 'render_meta_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_meta_section'
        );
        
        add_settings_field(
            'meta_model',
            'Meta Model',
            array($this, 'render_meta_model_field'),
            'ai_tarot_settings',
            'ai_tarot_meta_section'
        );
        
        add_settings_field(
            'meta_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_meta_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_meta_section'
        );
        
        add_settings_field(
            'meta_max_tokens',
            'Maksimum Token',
            array($this, 'render_meta_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_meta_section'
        );
        
        // Microsoft Copilot ayarlar bölümü
        add_settings_section(
            'ai_tarot_copilot_section',
            'Microsoft Copilot Ayarları',
            array($this, 'render_copilot_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'copilot_api_key',
            'Copilot API Anahtarı',
            array($this, 'render_copilot_api_key_field'),
            'ai_tarot_settings',
            'ai_tarot_copilot_section'
        );
        
        add_settings_field(
            'copilot_temperature',
            'Sıcaklık (Yaratıcılık)',
            array($this, 'render_copilot_temperature_field'),
            'ai_tarot_settings',
            'ai_tarot_copilot_section'
        );
        
        add_settings_field(
            'copilot_max_tokens',
            'Maksimum Token',
            array($this, 'render_copilot_max_tokens_field'),
            'ai_tarot_settings',
            'ai_tarot_copilot_section'
        );
        
        // Gelişmiş ayarlar bölümü
        add_settings_section(
            'ai_tarot_advanced_section',
            'Gelişmiş Ayarlar',
            array($this, 'render_advanced_section'),
            'ai_tarot_settings'
        );
        
        add_settings_field(
            'enable_logging',
            'Kayıt Tutma',
            array($this, 'render_enable_logging_field'),
            'ai_tarot_settings',
            'ai_tarot_advanced_section'
        );
        
        add_settings_field(
            'enable_api_failover',
            'API Yedekleme',
            array($this, 'render_enable_api_failover_field'),
            'ai_tarot_settings',
            'ai_tarot_advanced_section'
        );
        
        add_settings_field(
            'cache_duration',
            'Önbellek Süresi',
            array($this, 'render_cache_duration_field'),
            'ai_tarot_settings',
            'ai_tarot_advanced_section'
        );
        
        add_settings_field(
            'data_retention',
            'Veri Saklama Süresi',
            array($this, 'render_data_retention_field'),
            'ai_tarot_settings',
            'ai_tarot_advanced_section'
        );
        
        add_settings_field(
            'system_prompts',
            'Sistem Promptları',
            array($this, 'render_system_prompts_field'),
            'ai_tarot_settings',
            'ai_tarot_advanced_section'
        );
    }
    
    /**
     * Genel bölüm açıklaması
     */
    public function render_general_section() {
        echo '<p>Tarot falı için kullanılacak AI servisini ve genel ayarları yapılandırın.</p>';
    }
    
    /**
     * AI servisi seçim alanı
     */
    public function render_ai_service_field() {
        $ai_service = isset($this->settings['ai_service']) ? $this->settings['ai_service'] : 'openai';
        ?>
        <select name="ai_tarot_settings[ai_service]" id="ai_service">
            <option value="openai" <?php selected($ai_service, 'openai'); ?>>OpenAI (ChatGPT)</option>
            <option value="claude" <?php selected($ai_service, 'claude'); ?>>Anthropic Claude</option>
            <option value="gemini" <?php selected($ai_service, 'gemini'); ?>>Google Gemini</option>
            <option value="perplexity" <?php selected($ai_service, 'perplexity'); ?>>Perplexity.ai</option>
            <option value="deepseek" <?php selected($ai_service, 'deepseek'); ?>>DeepSeek</option>
            <option value="grok" <?php selected($ai_service, 'grok'); ?>>Grok</option>
            <option value="meta" <?php selected($ai_service, 'meta'); ?>>Meta (Llama)</option>
            <option value="copilot" <?php selected($ai_service, 'copilot'); ?>>Microsoft Copilot</option>
        </select>
        <p class="description">Tarot yorumlarını oluşturmak için kullanılacak AI servisini seçin. Seçilen servis için API anahtarı sağlamanız gerekecektir.</p>
        
        <script>
            jQuery(document).ready(function($) {
                // AI servis seçimine göre ilgili bölümleri göster/gizle
                function toggleApiSections() {
                    var selectedService = $('#ai_service').val();
                    
                    // Tüm bölümleri gizle
                    $('h2').each(function() {
                        if ($(this).text().indexOf('Ayarları') !== -1) {
                            $(this).closest('table').hide();
                            $(this).hide();
                        }
                    });
                    
                    // Seçilen servisi göster
                    $('h2:contains("' + getServiceTitle(selectedService) + '")').show();
                    $('h2:contains("' + getServiceTitle(selectedService) + '")').closest('table').show();
                }
                
                function getServiceTitle(service) {
                    switch(service) {
                        case 'openai': return 'OpenAI';
                        case 'claude': return 'Claude';
                        case 'gemini': return 'Gemini';
                        case 'perplexity': return 'Perplexity';
                        case 'deepseek': return 'DeepSeek';
                        case 'grok': return 'Grok';
                        case 'meta': return 'Meta';
                        case 'copilot': return 'Copilot';
                        default: return '';
                    }
                }
                
                // Sayfa yüklendiğinde ve değiştirildiğinde kontrol et
                toggleApiSections();
                $('#ai_service').on('change', toggleApiSections);
            });
        </script>
        <?php
    }
    
    /**
     * Otomatik kayıt alanı
     */
    public function render_auto_save_field() {
        $auto_save = isset($this->settings['auto_save']) ? $this->settings['auto_save'] : 0;
        ?>
        <label>
            <input type="checkbox" name="ai_tarot_settings[auto_save]" value="1" <?php checked($auto_save, 1); ?>>
            Tarot falı sonuçlarını otomatik olarak kaydet
        </label>
        <p class="description">Etkinleştirildiğinde, tüm tarot falı sonuçları veritabanında saklanacaktır. Bu, kullanıcılar için fal geçmişi oluşturur.</p>
        <?php
    }
    
    /**
     * Animasyon etkinleştirme alanı
     */
    public function render_enable_animations_field() {
        $enable_animations = isset($this->settings['enable_animations']) ? $this->settings['enable_animations'] : 1;
        ?>
        <label>
            <input type="checkbox" name="ai_tarot_settings[enable_animations]" value="1" <?php checked($enable_animations, 1); ?>>
            Kart çekme animasyonlarını etkinleştir
        </label>
        <p class="description">Etkinleştirildiğinde, kart çekme işlemi sırasında animasyonlar gösterilecektir. Mobil cihazlarda performansı iyileştirmek için devre dışı bırakabilirsiniz.</p>
        <?php
    }
    
    /**
     * Kart destesi teması alanı
     */
    public function render_card_deck_theme_field() {
        $card_deck_theme = isset($this->settings['card_deck_theme']) ? $this->settings['card_deck_theme'] : 'classic';
        ?>
        <select name="ai_tarot_settings[card_deck_theme]">
            <option value="classic" <?php selected($card_deck_theme, 'classic'); ?>>Klasik Rider-Waite</option>
            <option value="modern" <?php selected($card_deck_theme, 'modern'); ?>>Modern Minimalist</option>
            <option value="medieval" <?php selected($card_deck_theme, 'medieval'); ?>>Ortaçağ Stili</option>
            <option value="mystical" <?php selected($card_deck_theme, 'mystical'); ?>>Mistik Desenler</option>
            <option value="custom" <?php selected($card_deck_theme, 'custom'); ?>>Özel (Kartlar sayfasından yönetin)</option>
        </select>
        <p class="description">Tarot kartları için kullanılacak görsel temayı seçin. "Özel" seçeneği, her kart için kendi görselinizi yüklemenize olanak tanır.</p>
        <?php
    }
    
    /**
     * Özel CSS alanı
     */
    public function render_custom_css_field() {
        $custom_css = isset($this->settings['custom_css']) ? $this->settings['custom_css'] : '';
        ?>
        <textarea name="ai_tarot_settings[custom_css]" rows="5" cols="50" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
        <p class="description">Tarot falı formunun görünümünü özelleştirmek için CSS kodları ekleyin. Bu kodlar, sitenizin tema stillerine ek olarak uygulanacaktır.</p>
        <?php
    }
    
    /**
     * OpenAI bölüm açıklaması
     */
    public function render_openai_section() {
        echo '<p>OpenAI (ChatGPT) API bağlantı ayarlarını yapılandırın. <a href="https://platform.openai.com/" target="_blank">OpenAI API anahtarınızı buradan alabilirsiniz</a>.</p>';
    }
    
    /**
     * OpenAI API anahtarı alanı
     */
    public function render_openai_api_key_field() {
        $api_key = isset($this->settings['openai_api_key']) ? $this->settings['openai_api_key'] : '';
        ?>
        <input type="password" name="ai_tarot_settings[openai_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">OpenAI API anahtarınızı girin. API anahtarı genellikle "sk-" ile başlar.</p>
        <?php
    }
    
    /**
     * OpenAI model seçim alanı
     */
    public function render_openai_model_field() {
        $model = isset($this->settings['openai_model']) ? $this->settings['openai_model'] : 'gpt-4';
        ?>
        <select name="ai_tarot_settings[openai_model]">
            <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>>GPT-4</option>
            <option value="gpt-4-turbo" <?php selected($model, 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
            <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
        </select>
        <p class="description">Tarot yorumlarını oluşturmak için kullanılacak OpenAI modelini seçin. GPT-4 daha kaliteli sonuçlar üretir ancak daha pahalıdır.</p>
        <?php
    }
    
    /**
     * OpenAI sıcaklık alanı
     */
    public function render_openai_temperature_field() {
        $temperature = isset($this->settings['openai_temperature']) ? $this->settings['openai_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[openai_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * OpenAI maksimum token alanı
     */
    public function render_openai_max_tokens_field() {
        $max_tokens = isset($this->settings['openai_max_tokens']) ? $this->settings['openai_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[openai_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * Claude bölüm açıklaması
     */
    public function render_claude_section() {
        echo '<p>Anthropic Claude API bağlantı ayarlarını yapılandırın. <a href="https://console.anthropic.com/" target="_blank">Claude API anahtarınızı buradan alabilirsiniz</a>.</p>';
    }
    
    /**
     * Claude API anahtarı alanı
     */
    public function render_claude_api_key_field() {
        $api_key = isset($this->settings['claude_api_key']) ? $this->settings['claude_api_key'] : '';
        ?>
        <input type="password" name="ai_tarot_settings[claude_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">Anthropic Claude API anahtarınızı girin.</p>
        <?php
    }
    
    /**
     * Claude model seçim alanı
     */
    public function render_claude_model_field() {
        $model = isset($this->settings['claude_model']) ? $this->settings['claude_model'] : 'claude-3-opus-20240229';
        ?>
        <select name="ai_tarot_settings[claude_model]">
            <option value="claude-3-opus-20240229" <?php selected($model, 'claude-3-opus-20240229'); ?>>Claude 3 Opus</option>
            <option value="claude-3-sonnet-20240229" <?php selected($model, 'claude-3-sonnet-20240229'); ?>>Claude 3 Sonnet</option>
            <option value="claude-3-haiku-20240307" <?php selected($model, 'claude-3-haiku-20240307'); ?>>Claude 3 Haiku</option>
        </select>
        <p class="description">Tarot yorumlarını oluşturmak için kullanılacak Claude modelini seçin. Opus en kaliteli sonuçları üretir ancak daha pahalıdır.</p>
        <?php
    }
    
    /**
     * Claude sıcaklık alanı
     */
    public function render_claude_temperature_field() {
        $temperature = isset($this->settings['claude_temperature']) ? $this->settings['claude_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[claude_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * Claude maksimum token alanı
     */
    public function render_claude_max_tokens_field() {
        $max_tokens = isset($this->settings['claude_max_tokens']) ? $this->settings['claude_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[claude_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * Gemini bölüm açıklaması
     */
    public function render_gemini_section() {
        echo '<p>Google Gemini API bağlantı ayarlarını yapılandırın. <a href="https://ai.google.dev/" target="_blank">Gemini API anahtarınızı buradan alabilirsiniz</a>.</p>';
    }
    
    /**
     * Gemini API anahtarı alanı
     */
    public function render_gemini_api_key_field() {
        $api_key = isset($this->settings['gemini_api_key']) ? $this->settings['gemini_api_key'] : '';
        ?>
        <input <input type="password" name="ai_tarot_settings[gemini_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">Google Gemini API anahtarınızı girin.</p>
        <?php
    }
    
    /**
     * Gemini model seçim alanı
     */
    public function render_gemini_model_field() {
        $model = isset($this->settings['gemini_model']) ? $this->settings['gemini_model'] : 'gemini-1.5-pro';
        ?>
        <select name="ai_tarot_settings[gemini_model]">
            <option value="gemini-1.5-pro" <?php selected($model, 'gemini-1.5-pro'); ?>>Gemini 1.5 Pro</option>
            <option value="gemini-1.5-flash" <?php selected($model, 'gemini-1.5-flash'); ?>>Gemini 1.5 Flash</option>
        </select>
        <p class="description">Tarot yorumlarını oluşturmak için kullanılacak Gemini modelini seçin.</p>
        <?php
    }
    
    /**
     * Gemini sıcaklık alanı
     */
    public function render_gemini_temperature_field() {
        $temperature = isset($this->settings['gemini_temperature']) ? $this->settings['gemini_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[gemini_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * Gemini maksimum token alanı
     */
    public function render_gemini_max_tokens_field() {
        $max_tokens = isset($this->settings['gemini_max_tokens']) ? $this->settings['gemini_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[gemini_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * Perplexity bölüm açıklaması
     */
    public function render_perplexity_section() {
        echo '<p>Perplexity.ai API bağlantı ayarlarını yapılandırın. <a href="https://www.perplexity.ai/api" target="_blank">Perplexity API anahtarınızı buradan alabilirsiniz</a>.</p>';
    }
    
    /**
     * Perplexity API anahtarı alanı
     */
    public function render_perplexity_api_key_field() {
        $api_key = isset($this->settings['perplexity_api_key']) ? $this->settings['perplexity_api_key'] : '';
        ?>
        <input type="password" name="ai_tarot_settings[perplexity_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">Perplexity.ai API anahtarınızı girin.</p>
        <?php
    }
    
    /**
     * Perplexity model seçim alanı
     */
    public function render_perplexity_model_field() {
        $model = isset($this->settings['perplexity_model']) ? $this->settings['perplexity_model'] : 'pplx-7b-online';
        ?>
        <select name="ai_tarot_settings[perplexity_model]">
            <option value="pplx-7b-online" <?php selected($model, 'pplx-7b-online'); ?>>PPLX 7B Online</option>
            <option value="pplx-70b-online" <?php selected($model, 'pplx-70b-online'); ?>>PPLX 70B Online</option>
        </select>
        <p class="description">Tarot yorumlarını oluşturmak için kullanılacak Perplexity modelini seçin.</p>
        <?php
    }
    
    /**
     * Perplexity sıcaklık alanı
     */
    public function render_perplexity_temperature_field() {
        $temperature = isset($this->settings['perplexity_temperature']) ? $this->settings['perplexity_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[perplexity_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * Perplexity maksimum token alanı
     */
    public function render_perplexity_max_tokens_field() {
        $max_tokens = isset($this->settings['perplexity_max_tokens']) ? $this->settings['perplexity_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[perplexity_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * DeepSeek bölüm açıklaması
     */
    public function render_deepseek_section() {
        echo '<p>DeepSeek API bağlantı ayarlarını yapılandırın. <a href="https://platform.deepseek.com/" target="_blank">DeepSeek API anahtarınızı buradan alabilirsiniz</a>.</p>';
    }
    
    /**
     * DeepSeek API anahtarı alanı
     */
    public function render_deepseek_api_key_field() {
        $api_key = isset($this->settings['deepseek_api_key']) ? $this->settings['deepseek_api_key'] : '';
        ?>
        <input type="password" name="ai_tarot_settings[deepseek_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">DeepSeek API anahtarınızı girin.</p>
        <?php
    }
    
    /**
     * DeepSeek model seçim alanı
     */
    public function render_deepseek_model_field() {
        $model = isset($this->settings['deepseek_model']) ? $this->settings['deepseek_model'] : 'deepseek-chat';
        ?>
        <select name="ai_tarot_settings[deepseek_model]">
            <option value="deepseek-chat" <?php selected($model, 'deepseek-chat'); ?>>DeepSeek Chat</option>
            <option value="deepseek-coder" <?php selected($model, 'deepseek-coder'); ?>>DeepSeek Coder</option>
        </select>
        <p class="description">Tarot yorumlarını oluşturmak için kullanılacak DeepSeek modelini seçin.</p>
        <?php
    }
    
    /**
     * DeepSeek sıcaklık alanı
     */
    public function render_deepseek_temperature_field() {
        $temperature = isset($this->settings['deepseek_temperature']) ? $this->settings['deepseek_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[deepseek_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * DeepSeek maksimum token alanı
     */
    public function render_deepseek_max_tokens_field() {
        $max_tokens = isset($this->settings['deepseek_max_tokens']) ? $this->settings['deepseek_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[deepseek_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * Grok bölüm açıklaması
     */
    public function render_grok_section() {
        echo '<p>Grok API bağlantı ayarlarını yapılandırın. <a href="https://grok.x/" target="_blank">Grok API anahtarınızı X Premium+ aboneliği ile alabilirsiniz</a>.</p>';
    }
    
    /**
     * Grok API anahtarı alanı
     */
    public function render_grok_api_key_field() {
        $api_key = isset($this->settings['grok_api_key']) ? $this->settings['grok_api_key'] : '';
        ?>
        <input type="password" name="ai_tarot_settings[grok_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">Grok API anahtarınızı girin.</p>
        <?php
    }
    
    /**
     * Grok sıcaklık alanı
     */
    public function render_grok_temperature_field() {
        $temperature = isset($this->settings['grok_temperature']) ? $this->settings['grok_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[grok_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * Grok maksimum token alanı
     */
    public function render_grok_max_tokens_field() {
        $max_tokens = isset($this->settings['grok_max_tokens']) ? $this->settings['grok_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[grok_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * Meta bölüm açıklaması
     */
    public function render_meta_section() {
        echo '<p>Meta (Llama) API bağlantı ayarlarını yapılandırın. <a href="https://llama.meta.com/get-started/" target="_blank">Meta API anahtarınızı buradan alabilirsiniz</a>.</p>';
    }
    
    /**
     * Meta API anahtarı alanı
     */
    public function render_meta_api_key_field() {
        $api_key = isset($this->settings['meta_api_key']) ? $this->settings['meta_api_key'] : '';
        ?>
        <input type="password" name="ai_tarot_settings[meta_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">Meta (Llama) API anahtarınızı girin.</p>
        <?php
    }
    
    /**
     * Meta model seçim alanı
     */
    public function render_meta_model_field() {
        $model = isset($this->settings['meta_model']) ? $this->settings['meta_model'] : 'llama-3-70b-instruct';
        ?>
        <select name="ai_tarot_settings[meta_model]">
            <option value="llama-3-70b-instruct" <?php selected($model, 'llama-3-70b-instruct'); ?>>Llama 3 70B Instruct</option>
            <option value="llama-3-8b-instruct" <?php selected($model, 'llama-3-8b-instruct'); ?>>Llama 3 8B Instruct</option>
        </select>
        <p class="description">Tarot yorumlarını oluşturmak için kullanılacak Meta (Llama) modelini seçin.</p>
        <?php
    }
    
    /**
     * Meta sıcaklık alanı
     */
    public function render_meta_temperature_field() {
        $temperature = isset($this->settings['meta_temperature']) ? $this->settings['meta_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[meta_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * Meta maksimum token alanı
     */
    public function render_meta_max_tokens_field() {
        $max_tokens = isset($this->settings['meta_max_tokens']) ? $this->settings['meta_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[meta_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * Copilot bölüm açıklaması
     */
    public function render_copilot_section() {
        echo '<p>Microsoft Copilot API bağlantı ayarlarını yapılandırın. <a href="https://copilot.microsoft.com/api" target="_blank">Microsoft Copilot API anahtarınızı buradan alabilirsiniz</a>.</p>';
    }
    
    /**
     * Copilot API anahtarı alanı
     */
    public function render_copilot_api_key_field() {
        $api_key = isset($this->settings['copilot_api_key']) ? $this->settings['copilot_api_key'] : '';
        ?>
        <input type="password" name="ai_tarot_settings[copilot_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description">Microsoft Copilot API anahtarınızı girin.</p>
        <?php
    }
    
    /**
     * Copilot sıcaklık alanı
     */
    public function render_copilot_temperature_field() {
        $temperature = isset($this->settings['copilot_temperature']) ? $this->settings['copilot_temperature'] : 0.7;
        ?>
        <input type="range" name="ai_tarot_settings[copilot_temperature]" min="0" max="1" step="0.1" value="<?php echo esc_attr($temperature); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_html($temperature); ?></output>
        <p class="description">Sıcaklık değeri, AI'nın yaratıcılık seviyesini kontrol eder. Düşük değerler daha tutarlı, yüksek değerler daha yaratıcı sonuçlar üretir.</p>
        <?php
    }
    
    /**
     * Copilot maksimum token alanı
     */
    public function render_copilot_max_tokens_field() {
        $max_tokens = isset($this->settings['copilot_max_tokens']) ? $this->settings['copilot_max_tokens'] : 2000;
        ?>
        <input type="number" name="ai_tarot_settings[copilot_max_tokens]" value="<?php echo esc_attr($max_tokens); ?>" min="100" max="4000" step="100" class="small-text">
        <p class="description">Bir yorumda kullanılacak maksimum token (kelime parçacığı) sayısı. Daha yüksek değerler daha uzun yorumlar üretir.</p>
        <?php
    }
    
    /**
     * Gelişmiş ayarlar bölüm açıklaması
     */
    public function render_advanced_section() {
        echo '<p>Gelişmiş eklenti ayarlarını yapılandırın. Bu ayarları yalnızca ne yaptığınızı biliyorsanız değiştirin.</p>';
    }
    
    /**
     * Kayıt tutma alanı
     */
    public function render_enable_logging_field() {
        $enable_logging = isset($this->settings['enable_logging']) ? $this->settings['enable_logging'] : 0;
        ?>
        <label>
            <input type="checkbox" name="ai_tarot_settings[enable_logging]" value="1" <?php checked($enable_logging, 1); ?>>
            API isteklerini ve yanıtlarını kaydet
        </label>
        <p class="description">Etkinleştirildiğinde, tüm API istekleri ve yanıtları veritabanında saklanacaktır. Bu, hata ayıklama için yararlıdır.</p>
        <?php
    }
    
    /**
     * API yedekleme alanı
     */
    public function render_enable_api_failover_field() {
        $enable_api_failover = isset($this->settings['enable_api_failover']) ? $this->settings['enable_api_failover'] : 0;
        ?>
        <label>
            <input type="checkbox" name="ai_tarot_settings[enable_api_failover]" value="1" <?php checked($enable_api_failover, 1); ?>>
            API yedeklemeyi etkinleştir
        </label>
        <p class="description">Etkinleştirildiğinde, seçilen API başarısız olursa sistem otomatik olarak yedek bir API'ya geçiş yapacaktır.</p>
        <?php
    }
    
    /**
     * Önbellek süresi alanı
     */
    public function render_cache_duration_field() {
        $cache_duration = isset($this->settings['cache_duration']) ? $this->settings['cache_duration'] : 7;
        ?>
        <select name="ai_tarot_settings[cache_duration]">
            <option value="1" <?php selected($cache_duration, 1); ?>>1 gün</option>
            <option value="3" <?php selected($cache_duration, 3); ?>>3 gün</option>
            <option value="7" <?php selected($cache_duration, 7); ?>>1 hafta</option>
            <option value="14" <?php selected($cache_duration, 14); ?>>2 hafta</option>
            <option value="30" <?php selected($cache_duration, 30); ?>>1 ay</option>
            <option value="0" <?php selected($cache_duration, 0); ?>>Önbellek devre dışı</option>
        </select>
        <p class="description">Benzer sorulara verilen AI yanıtlarının önbellekte saklanma süresi. Önbellek, API çağrı sayısını azaltarak maliyetleri düşürmeye yardımcı olabilir.</p>
        <?php
    }
    
    /**
     * Veri saklama süresi alanı
     */
    public function render_data_retention_field() {
        $data_retention = isset($this->settings['data_retention']) ? $this->settings['data_retention'] : 90;
        ?>
        <select name="ai_tarot_settings[data_retention]">
            <option value="30" <?php selected($data_retention, 30); ?>>1 ay</option>
            <option value="90" <?php selected($data_retention, 90); ?>>3 ay</option>
            <option value="180" <?php selected($data_retention, 180); ?>>6 ay</option>
            <option value="365" <?php selected($data_retention, 365); ?>>1 yıl</option>
            <option value="0" <?php selected($data_retention, 0); ?>>Sınırsız (hiçbir zaman silme)</option>
        </select>
        <p class="description">Kullanıcı falı geçmişinin veritabanında saklanma süresi. Bu süre sonunda eski veriler otomatik olarak silinir.</p>
        <?php
    }
    
    /**
     * Sistem promptları alanı
     */
    public function render_system_prompts_field() {
        $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
        
        // Varsayılan prompt
        $default_prompt = 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
        
        // AI servisleri
        $ai_services = array(
            'openai' => 'OpenAI (ChatGPT)',
            'claude' => 'Anthropic Claude',
            'gemini' => 'Google Gemini',
            'perplexity' => 'Perplexity.ai',
            'deepseek' => 'DeepSeek',
            'grok' => 'Grok',
            'meta' => 'Meta (Llama)',
            'copilot' => 'Microsoft Copilot'
        );
        
        // Her servis için bir metin alanı oluştur
        foreach ($ai_services as $service_key => $service_name) {
            $prompt = isset($system_prompts[$service_key]) ? $system_prompts[$service_key] : $default_prompt;
            ?>
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0 0 5px;"><?php echo esc_html($service_name); ?> Sistem Promptu:</h4>
                <textarea name="ai_tarot_settings[system_prompts][<?php echo esc_attr($service_key); ?>]" rows="3" cols="50" class="large-text"><?php echo esc_textarea($prompt); ?></textarea>
            </div>
            <?php
        }
        
        ?>
        <p class="description">Her AI servisi için kullanılacak sistem promptlarını özelleştirin. Bu promptlar, AI'nın nasıl yanıt vereceğini belirler.</p>
        <?php
    }
    
    /**
     * Ayarları doğrula
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        
        // AI servisini doğrula
        $sanitized_input['ai_service'] = isset($input['ai_service']) ? sanitize_text_field($input['ai_service']) : 'openai';
        
        // Genel ayarları doğrula
        $sanitized_input['auto_save'] = isset($input['auto_save']) ? 1 : 0;
        $sanitized_input['enable_animations'] = isset($input['enable_animations']) ? 1 : 0;
        $sanitized_input['card_deck_theme'] = isset($input['card_deck_theme']) ? sanitize_text_field($input['card_deck_theme']) : 'classic';
        $sanitized_input['custom_css'] = isset($input['custom_css']) ? $input['custom_css'] : '';
        
        // API anahtarlarını doğrula
        $api_keys = array(
            'openai_api_key', 'claude_api_key', 'gemini_api_key', 'perplexity_api_key',
            'deepseek_api_key', 'grok_api_key', 'meta_api_key', 'copilot_api_key'
        );
        
        foreach ($api_keys as $key) {
            if (isset($input[$key])) {
                $sanitized_input[$key] = sanitize_text_field($input[$key]);
            }
        }
        
        // Model seçimlerini doğrula
        $models = array(
            'openai_model', 'claude_model', 'gemini_model', 'perplexity_model',
            'deepseek_model', 'meta_model'
        );
        
        foreach ($models as $key) {
            if (isset($input[$key])) {
                $sanitized_input[$key] = sanitize_text_field($input[$key]);
            }
        }
        
        // Sıcaklık değerlerini doğrula
        $temperatures = array(
            'openai_temperature', 'claude_temperature', 'gemini_temperature', 'perplexity_temperature',
            'deepseek_temperature', 'grok_temperature', 'meta_temperature', 'copilot_temperature'
        );
        
        foreach ($temperatures as $key) {
            if (isset($input[$key])) {
                $sanitized_input[$key] = floatval($input[$key]);
                if ($sanitized_input[$key] < 0) $sanitized_input[$key] = 0;
                if ($sanitized_input[$key] > 1) $sanitized_input[$key] = 1;
            }
        }
        
        // Maksimum token değerlerini doğrula
        $max_tokens = array(
            'openai_max_tokens', 'claude_max_tokens', 'gemini_max_tokens', 'perplexity_max_tokens',
            'deepseek_max_tokens', 'grok_max_tokens', 'meta_max_tokens', 'copilot_max_tokens'
        );
        
        foreach ($max_tokens as $key) {
            if (isset($input[$key])) {
                $sanitized_input[$key] = intval($input[$key]);
                if ($sanitized_input[$key] < 100) $sanitized_input[$key] = 100;
                if ($sanitized_input[$key] > 4000) $sanitized_input[$key] = 4000;
            }
        }
        
        // Gelişmiş ayarları doğrula
        $sanitized_input['enable_logging'] = isset($input['enable_logging']) ? 1 : 0;
        $sanitized_input['enable_api_failover'] = isset($input['enable_api_failover']) ? 1 : 0;
        $sanitized_input['cache_duration'] = isset($input['cache_duration']) ? intval($input['cache_duration']) : 7;
        $sanitized_input['data_retention'] = isset($input['data_retention']) ? intval($input['data_retention']) : 90;
        
        // Sistem promptlarını doğrula
        if (isset($input['system_prompts']) && is_array($input['system_prompts'])) {
            $sanitized_input['system_prompts'] = array();
            
            foreach ($input['system_prompts'] as $key => $value) {
                $sanitized_input['system_prompts'][$key] = sanitize_textarea_field($value);
            }
        }
        
        return $sanitized_input;
    }
    
    /**
     * Ayarlar sayfasını render et
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>AI Tarot Falı Ayarları</h1>
            
            <div class="notice notice-info">
                <p><strong>Not:</strong> AI servisi seçiminize göre ilgili API anahtarını sağlamanız gereklidir. Desteklenen servislerden birini seçip API anahtarınızı girin.</p>
            </div>
            
            <form method="post" action="options.php">
            <?php
                settings_fields('ai_tarot_settings');
                do_settings_sections('ai_tarot_settings');
                submit_button('Ayarları Kaydet');
                ?>
            </form>
            
            <h2>Test Bağlantısı</h2>
            <p>Seçilen AI servisi ile bağlantıyı test etmek için aşağıdaki düğmeye tıklayın:</p>
            <button id="ai_tarot_test_connection" class="button button-secondary">Bağlantıyı Test Et</button>
            <div id="ai_tarot_test_result" style="margin-top: 10px; padding: 10px; display: none;"></div>
            
            <script>
                jQuery(document).ready(function($) {
                    $('#ai_tarot_test_connection').on('click', function(e) {
                        e.preventDefault();
                        
                        var button = $(this);
                        var result = $('#ai_tarot_test_result');
                        
                        button.prop('disabled', true);
                        button.text('Test Ediliyor...');
                        
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
                                button.text('Bağlantıyı Test Et');
                                
                                if (response.success) {
                                    result.addClass('notice notice-success').html('<p>' + response.data + '</p>').show();
                                } else {
                                    result.addClass('notice notice-error').html('<p>' + response.data + '</p>').show();
                                }
                            },
                            error: function() {
                                button.prop('disabled', false);
                                button.text('Bağlantıyı Test Et');
                                result.addClass('notice notice-error').html('<p>Test sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p>').show();
                            }
                        });
                    });
                });
            </script>
            
            <hr>
            
            <h2>Kısa Kod Kullanımı</h2>
            <p>Tarot falı formunu bir sayfa veya yazıya eklemek için aşağıdaki kısa kodu kullanabilirsiniz:</p>
            <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">[ai_tarot]</pre>
            
            <p>Otomatik sayfa oluşturmak için aşağıdaki düğmeyi kullanabilirsiniz:</p>
            <button id="ai_tarot_create_page" class="button button-secondary">Tarot Falı Sayfası Oluştur</button>
            <div id="ai_tarot_page_result" style="margin-top: 10px; padding: 10px; display: none;"></div>
            
            <script>
                jQuery(document).ready(function($) {
                    $('#ai_tarot_create_page').on('click', function(e) {
                        e.preventDefault();
                        
                        var button = $(this);
                        var result = $('#ai_tarot_page_result');
                        
                        button.prop('disabled', true);
                        button.text('Sayfa Oluşturuluyor...');
                        
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
                                button.text('Tarot Falı Sayfası Oluştur');
                                
                                if (response.success) {
                                    result.addClass('notice notice-success').html('<p>' + response.data.message + ' <a href="' + response.data.edit_url + '" target="_blank">Sayfayı Düzenle</a> | <a href="' + response.data.view_url + '" target="_blank">Sayfayı Görüntüle</a></p>').show();
                                } else {
                                    result.addClass('notice notice-error').html('<p>' + response.data + '</p>').show();
                                }
                            },
                            error: function() {
                                button.prop('disabled', false);
                                button.text('Tarot Falı Sayfası Oluştur');
                                result.addClass('notice notice-error').html('<p>Sayfa oluşturulurken bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p>').show();
                            }
                        });
                    });
                });
            </script>
        </div>
        <?php
    }
}