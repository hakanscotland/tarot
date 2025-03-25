<?php
/**
 * AI Tarot API Test
 * 
 * Bu sınıf, AI servisleri ile bağlantı testlerini gerçekleştirir.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Tarot API Test sınıfı
 */
class AI_Tarot_API_Testing {
    
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
        
        // AJAX isteği için hook ekle
        add_action('wp_ajax_ai_tarot_test_connection', array($this, 'test_connection'));
    }
    
    /**
     * API bağlantısını test et
     */
    public function test_connection() {
        // Nonce kontrolü
        check_ajax_referer('ai_tarot_test_connection', 'nonce');
        
        // Yönetici izni kontrolü
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Bu işlem için yetkiniz yok');
            return;
        }
        
        // Seçilen servisi al
        $service = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : 'openai';
        
        // Test mesajı
        $test_message = 'Bu bir test mesajıdır. Lütfen "AI Tarot bağlantı testi başarılı" ile cevap verin.';
        
        // Seçilen servise göre test yap
        switch ($service) {
            case 'claude':
                $result = $this->test_claude_connection($test_message);
                break;
            case 'gemini':
                $result = $this->test_gemini_connection($test_message);
                break;
            case 'perplexity':
                $result = $this->test_perplexity_connection($test_message);
                break;
            case 'deepseek':
                $result = $this->test_deepseek_connection($test_message);
                break;
            case 'grok':
                $result = $this->test_grok_connection($test_message);
                break;
            case 'meta':
                $result = $this->test_meta_connection($test_message);
                break;
            case 'copilot':
                $result = $this->test_copilot_connection($test_message);
                break;
            case 'openai':
            default:
                $result = $this->test_openai_connection($test_message);
                break;
        }
        
        // Sonucu döndür
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * OpenAI bağlantısını test et
     */
    private function test_openai_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['openai_api_key']) ? $this->settings['openai_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'OpenAI API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => isset($this->settings['openai_model']) ? $this->settings['openai_model'] : 'gpt-4',
                'messages' => array(
                    array('role' => 'user', 'content' => $message)
                ),
                'temperature' => 0.7,
                'max_tokens' => 50
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => 'OpenAI bağlantısı başarılı! API yanıtı: "' . substr($body['choices'][0]['message']['content'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
    
    /**
     * Claude bağlantısını test et
     */
    private function test_claude_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['claude_api_key']) ? $this->settings['claude_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Claude API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'headers' => array(
                'x-api-key' => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => isset($this->settings['claude_model']) ? $this->settings['claude_model'] : 'claude-3-opus-20240229',
                'messages' => [
                    array('role' => 'user', 'content' => $message)
                ],
                'max_tokens' => 50
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['content'][0]['text'])) {
            return array(
                'success' => true,
                'message' => 'Claude bağlantısı başarılı! API yanıtı: "' . substr($body['content'][0]['text'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
    
    /**
     * Google Gemini bağlantısını test et
     */
    private function test_gemini_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['gemini_api_key']) ? $this->settings['gemini_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Google Gemini API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $model = isset($this->settings['gemini_model']) ? $this->settings['gemini_model'] : 'gemini-1.5-pro';
        $response = wp_remote_post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}", array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'contents' => array(
                    array(
                        'role' => 'user',
                        'parts' => array(
                            array('text' => $message)
                        )
                    )
                ),
                'generationConfig' => array(
                    'temperature' => 0.7,
                    'maxOutputTokens' => 50
                )
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            return array(
                'success' => true,
                'message' => 'Google Gemini bağlantısı başarılı! API yanıtı: "' . substr($body['candidates'][0]['content']['parts'][0]['text'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
    
    /**
     * Perplexity bağlantısını test et
     */
    private function test_perplexity_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['perplexity_api_key']) ? $this->settings['perplexity_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Perplexity API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $response = wp_remote_post('https://api.perplexity.ai/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => isset($this->settings['perplexity_model']) ? $this->settings['perplexity_model'] : 'pplx-7b-online',
                'messages' => array(
                    array('role' => 'user', 'content' => $message)
                ),
                'temperature' => 0.7,
                'max_tokens' => 50
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => 'Perplexity bağlantısı başarılı! API yanıtı: "' . substr($body['choices'][0]['message']['content'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
    
    /**
     * DeepSeek bağlantısını test et
     */
    private function test_deepseek_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['deepseek_api_key']) ? $this->settings['deepseek_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'DeepSeek API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
               'model' => isset($this->settings['deepseek_model']) ? $this->settings['deepseek_model'] : 'deepseek-chat',
                'messages' => array(
                    array('role' => 'user', 'content' => $message)
                ),
                'temperature' => 0.7,
                'max_tokens' => 50
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => 'DeepSeek bağlantısı başarılı! API yanıtı: "' . substr($body['choices'][0]['message']['content'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
    
    /**
     * Grok bağlantısını test et
     */
    private function test_grok_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['grok_api_key']) ? $this->settings['grok_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Grok API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $response = wp_remote_post('https://api.grok.x/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'grok-1',
                'messages' => array(
                    array('role' => 'user', 'content' => $message)
                ),
                'temperature' => 0.7,
                'max_tokens' => 50
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => 'Grok bağlantısı başarılı! API yanıtı: "' . substr($body['choices'][0]['message']['content'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
    
    /**
     * Meta (Llama) bağlantısını test et
     */
    private function test_meta_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['meta_api_key']) ? $this->settings['meta_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Meta API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $response = wp_remote_post('https://api.meta.ai/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => isset($this->settings['meta_model']) ? $this->settings['meta_model'] : 'llama-3-70b-instruct',
                'messages' => array(
                    array('role' => 'user', 'content' => $message)
                ),
                'temperature' => 0.7,
                'max_tokens' => 50
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => 'Meta bağlantısı başarılı! API yanıtı: "' . substr($body['choices'][0]['message']['content'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
    
    /**
     * Microsoft Copilot bağlantısını test et
     */
    private function test_copilot_connection($message) {
        // API anahtarını al
        $api_key = isset($this->settings['copilot_api_key']) ? $this->settings['copilot_api_key'] : '';
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'Microsoft Copilot API anahtarı eksik. Lütfen ayarlardan API anahtarını ekleyin.'
            );
        }
        
        // API isteği gönder
        $response = wp_remote_post('https://api.microsoft.com/v1/copilot/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'messages' => array(
                    array('role' => 'user', 'content' => $message)
                ),
                'temperature' => 0.7,
                'max_tokens' => 50
            )),
            'timeout' => 15
        ));
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen bir hata oluştu';
            
            return array(
                'success' => false,
                'message' => 'API hatası (HTTP ' . $response_code . '): ' . $error_message
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return array(
                'success' => true,
                'message' => 'Microsoft Copilot bağlantısı başarılı! API yanıtı: "' . substr($body['choices'][0]['message']['content'], 0, 100) . '..."'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'API yanıtı beklenmeyen formatta.'
            );
        }
    }
}