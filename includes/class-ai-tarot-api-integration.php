<?php
/**
 * AI Tarot API Entegrasyonu
 * 
 * Bu sınıf, çeşitli AI servislerine bağlantı kurarak tarot yorumlarını oluşturur.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

class AI_Tarot_API_Integration {
    
    /**
     * Yapılandırma ayarları
     */
    private $settings;
    
    /**
     * Sınıfı başlat
     */
    public function __construct() {
        $this->settings = get_option('ai_tarot_settings', array());
    }
    
    /**
     * Seçilen AI servisini kullanarak yorum oluştur
     *
     * @param array $cards Tarot kartları
     * @param string $question Kullanıcı sorusu
     * @param string $spread_type Açılım türü
     * @return string AI tarafından oluşturulan yorum
     */
    public function generate_interpretation($cards, $question, $spread_type) {
        // Seçilen AI servisini al
        $ai_service = isset($this->settings['ai_service']) ? $this->settings['ai_service'] : 'openai';
        
        // Pozisyon isimlerini al
        $position_names = $this->get_position_names($spread_type);
        
        // Kartlar hakkında bilgi oluştur
        $cards_info = '';
        foreach ($cards as $index => $card) {
            $position_name = isset($position_names[$index]) ? $position_names[$index] : "Pozisyon " . ($index + 1);
            $cards_info .= "{$position_name} konumunda: {$card['name']} - ";
            $cards_info .= $card['reversed'] ? "Ters duruşta" : "Düz duruşta";
            $cards_info .= "\n";
            $cards_info .= "Anlamı: {$card['meaning']}\n";
            $cards_info .= "Anahtar Kelimeler: {$card['keywords']}\n\n";
        }
        
        // AI için prompt hazırla
        $prompt = $this->prepare_prompt($question, $spread_type, $cards_info);
        
        // Önbellek anahtarı oluştur
        $cache_key = md5($prompt . $ai_service);
        
        // Önbellekteki yorumu kontrol et
        $cached_interpretation = AI_Tarot::check_cache($cache_key);
        
        if ($cached_interpretation !== false) {
            return $cached_interpretation;
        }
        
        // Yedekleme özelliği etkin mi kontrol et
        $enable_failover = isset($this->settings['enable_api_failover']) ? $this->settings['enable_api_failover'] : 0;
        
        // Seçilen AI servisine göre yorumu oluştur
        $interpretation = '';
        $success = false;
        
        // API isteği gönder
        try {
            switch ($ai_service) {
                case 'claude':
                    $interpretation = $this->generate_with_claude($prompt);
                    $success = !empty($interpretation);
                    break;
                case 'gemini':
                    $interpretation = $this->generate_with_gemini($prompt);
                    $success = !empty($interpretation);
                    break;
                case 'perplexity':
                    $interpretation = $this->generate_with_perplexity($prompt);
                    $success = !empty($interpretation);
                    break;
                case 'deepseek':
                    $interpretation = $this->generate_with_deepseek($prompt);
                    $success = !empty($interpretation);
                    break;
                case 'grok':
                    $interpretation = $this->generate_with_grok($prompt);
                    $success = !empty($interpretation);
                    break;
                case 'meta':
                    $interpretation = $this->generate_with_meta($prompt);
                    $success = !empty($interpretation);
                    break;
                case 'copilot':
                    $interpretation = $this->generate_with_copilot($prompt);
                    $success = !empty($interpretation);
                    break;
                case 'openai':
                default:
                    $interpretation = $this->generate_with_openai($prompt);
                    $success = !empty($interpretation);
                    break;
            }
        } catch (Exception $e) {
            // Hata kaydını tut
            AI_Tarot::log_error('API Error: ' . $e->getMessage(), array(
                'service' => $ai_service,
                'prompt' => $prompt
            ));
            
            $success = false;
        }
        
        // Ana API başarısız olursa ve yedekleme etkinse yedek API kullan
        if (!$success && $enable_failover) {
            // Yedek servisleri tanımla
            $backup_services = array('openai', 'claude', 'gemini');
            
            // Mevcut servisi listeden çıkar
            $backup_services = array_diff($backup_services, array($ai_service));
            
            // Yedek servisler arasında döngü
            foreach ($backup_services as $backup_service) {
                // Yedek servis için API anahtarı var mı kontrol et
                $api_key_name = $backup_service . '_api_key';
                if (empty($this->settings[$api_key_name])) {
                    continue;
                }
                
                AI_Tarot::log("Failover to {$backup_service} service");
                
                try {
                    switch ($backup_service) {
                        case 'claude':
                            $interpretation = $this->generate_with_claude($prompt);
                            break;
                        case 'gemini':
                            $interpretation = $this->generate_with_gemini($prompt);
                            break;
                        case 'openai':
                        default:
                            $interpretation = $this->generate_with_openai($prompt);
                            break;
                    }
                    
                    if (!empty($interpretation)) {
                        $success = true;
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        
        // Başarısız olursa varsayılan yorumu döndür
        if (!$success || empty($interpretation)) {
            $interpretation = $this->get_fallback_interpretation($spread_type, $cards);
        }
        
        // Yorumu önbelleğe ekle
        AI_Tarot::set_cache($cache_key, $interpretation);
        
        return $interpretation;
    }
    
    /**
     * AI için prompt hazırla
     */
    private function prepare_prompt($question, $spread_type, $cards_info) {
        // Seçilen AI servisini al
        $ai_service = isset($this->settings['ai_service']) ? $this->settings['ai_service'] : 'openai';
        
        // Sistem promptunu al
        $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
        $system_prompt = isset($system_prompts[$ai_service]) ? $system_prompts[$ai_service] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
        
        // Ana promptu oluştur
        $prompt = "Aşağıdaki tarot açılımını detaylı olarak yorumla. Soru: '{$question}'\n";
        $prompt .= "Açılım türü: {$spread_type}\n\n";
        $prompt .= "Kartlar:\n{$cards_info}\n";
        $prompt .= "Lütfen bu açılımın detaylı ve kişiselleştirilmiş bir yorumunu yap. ";
        $prompt .= "Her kartın anlamını, pozisyonunu ve birbirleriyle olan ilişkisini açıkla. ";
        $prompt .= "Kartların elementleri ve sembollerinden de bahsederek derinlemesine bir tarot okuması yap. ";
        $prompt .= "Sonunda, soruya net bir cevap veren özet bir paragraf ekle. ";
        $prompt .= "Yorumu paragraflar halinde yaz ve her bölümü kısa başlıklarla ayır.";
        
        return $prompt;
    }
    
    /**
     * OpenAI (ChatGPT) ile yorum oluştur
     */
    private function generate_with_openai($prompt) {
        // API anahtarını al
        $api_key = isset($this->settings['openai_api_key']) ? $this->settings['openai_api_key'] : '';
        
        if (empty($api_key)) {
            return 'OpenAI API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
        }
        
        // Model parametrelerini al
        $model = isset($this->settings['openai_model']) ? $this->settings['openai_model'] : 'gpt-4';
        $temperature = isset($this->settings['openai_temperature']) ? floatval($this->settings['openai_temperature']) : 0.7;
        $max_tokens = isset($this->settings['openai_max_tokens']) ? intval($this->settings['openai_max_tokens']) : 2000;
        
        // Sistem promptunu al
        $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
        $system_prompt = isset($system_prompts['openai']) ? $system_prompts['openai'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
        
        // API isteği gönder
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'system', 'content' => $system_prompt),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'temperature' => $temperature,
                'max_tokens' => $max_tokens
            )),
            'timeout' => 60
        ));
        
        // API isteğini kaydet
        AI_Tarot::log_api_request(
            'openai',
            'chat/completions',
            'POST',
            $prompt,
            $response,
            !is_wp_error($response)
        );
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            throw new Exception('OpenAI API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return $body['choices'][0]['message']['content'];
        } else {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
            throw new Exception('OpenAI API hatası: ' . $error_message);
        }
    }
    
    /**
     * Claude ile yorum oluştur
     */
    private function generate_with_claude($prompt) {
        // API anahtarını al
        $api_key = isset($this->settings['claude_api_key']) ? $this->settings['claude_api_key'] : '';
        
        if (empty($api_key)) {
            return 'Claude API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
        }
        
        // Model parametrelerini al
        $model = isset($this->settings['claude_model']) ? $this->settings['claude_model'] : 'claude-3-opus-20240229';
        $temperature = isset($this->settings['claude_temperature']) ? floatval($this->settings['claude_temperature']) : 0.7;
        $max_tokens = isset($this->settings['claude_max_tokens']) ? intval($this->settings['claude_max_tokens']) : 2000;
        
        // Sistem promptunu al
        $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
        $system_prompt = isset($system_prompts['claude']) ? $system_prompts['claude'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
        
        // API isteği gönder
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'headers' => array(
                'x-api-key' => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
                'system' => $system_prompt,
                'messages' => [
                    array('role' => 'user', 'content' => $prompt)
                ],
                'temperature' => $temperature,
                'max_tokens' => $max_tokens
            )),
            'timeout' => 60
        ));
        
        // API isteğini kaydet
        AI_Tarot::log_api_request(
            'claude',
            'messages',
            'POST',
            $prompt,
            $response,
            !is_wp_error($response)
        );
        
        // Yanıtı işle
        if (is_wp_error($response)) {
            throw new Exception('Claude API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['content'][0]['text'])) {
            return $body['content'][0]['text'];
        } else {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
            throw new Exception('Claude API hatası: ' . $error_message);
        }
    }
    
    /**
     * Google Gemini ile yorum oluştur
     */
    private function generate_with_gemini($prompt) {
        // API anahtarını al
        $api_key = isset($this->settings['gemini_api_key']) ? $this->settings['gemini_api_key'] : '';
        
        if (empty($api_key)) {
            return 'Google Gemini API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
        }
        
       // Model parametrelerini al
       $model = isset($this->settings['gemini_model']) ? $this->settings['gemini_model'] : 'gemini-1.5-pro';
       $temperature = isset($this->settings['gemini_temperature']) ? floatval($this->settings['gemini_temperature']) : 0.7;
       $max_tokens = isset($this->settings['gemini_max_tokens']) ? intval($this->settings['gemini_max_tokens']) : 2000;
       
       // Sistem promptunu al
       $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
       $system_prompt = isset($system_prompts['gemini']) ? $system_prompts['gemini'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
       
       // Tam prompt hazırla
       $full_prompt = $system_prompt . "\n\n" . $prompt;
       
       // API isteği gönder
       $response = wp_remote_post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}", array(
           'headers' => array(
               'Content-Type' => 'application/json'
           ),
           'body' => json_encode(array(
               'contents' => array(
                   array(
                       'role' => 'user',
                       'parts' => array(
                           array('text' => $full_prompt)
                       )
                   )
               ),
               'generationConfig' => array(
                   'temperature' => $temperature,
                   'maxOutputTokens' => $max_tokens
               )
           )),
           'timeout' => 60
       ));
       
       // API isteğini kaydet
       AI_Tarot::log_api_request(
           'gemini',
           'generateContent',
           'POST',
           $full_prompt,
           $response,
           !is_wp_error($response)
       );
       
       // Yanıtı işle
       if (is_wp_error($response)) {
           throw new Exception('Google Gemini API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
       }
       
       $body = json_decode(wp_remote_retrieve_body($response), true);
       
       if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
           return $body['candidates'][0]['content']['parts'][0]['text'];
       } else {
           $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
           throw new Exception('Google Gemini API hatası: ' . $error_message);
       }
   }
   
   /**
    * Perplexity.ai ile yorum oluştur
    */
   private function generate_with_perplexity($prompt) {
       // API anahtarını al
       $api_key = isset($this->settings['perplexity_api_key']) ? $this->settings['perplexity_api_key'] : '';
       
       if (empty($api_key)) {
           return 'Perplexity API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
       }
       
       // Model parametrelerini al
       $model = isset($this->settings['perplexity_model']) ? $this->settings['perplexity_model'] : 'pplx-7b-online';
       $temperature = isset($this->settings['perplexity_temperature']) ? floatval($this->settings['perplexity_temperature']) : 0.7;
       $max_tokens = isset($this->settings['perplexity_max_tokens']) ? intval($this->settings['perplexity_max_tokens']) : 2000;
       
       // Sistem promptunu al
       $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
       $system_prompt = isset($system_prompts['perplexity']) ? $system_prompts['perplexity'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
       
       // API isteği gönder
       $response = wp_remote_post('https://api.perplexity.ai/chat/completions', array(
           'headers' => array(
               'Authorization' => 'Bearer ' . $api_key,
               'Content-Type' => 'application/json'
           ),
           'body' => json_encode(array(
               'model' => $model,
               'messages' => array(
                   array('role' => 'system', 'content' => $system_prompt),
                   array('role' => 'user', 'content' => $prompt)
               ),
               'temperature' => $temperature,
               'max_tokens' => $max_tokens
           )),
           'timeout' => 60
       ));
       
       // API isteğini kaydet
       AI_Tarot::log_api_request(
           'perplexity',
           'chat/completions',
           'POST',
           $prompt,
           $response,
           !is_wp_error($response)
       );
       
       // Yanıtı işle
       if (is_wp_error($response)) {
           throw new Exception('Perplexity API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
       }
       
       $body = json_decode(wp_remote_retrieve_body($response), true);
       
       if (isset($body['choices'][0]['message']['content'])) {
           return $body['choices'][0]['message']['content'];
       } else {
           $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
           throw new Exception('Perplexity API hatası: ' . $error_message);
       }
   }
   
   /**
    * DeepSeek ile yorum oluştur
    */
   private function generate_with_deepseek($prompt) {
       // API anahtarını al
       $api_key = isset($this->settings['deepseek_api_key']) ? $this->settings['deepseek_api_key'] : '';
       
       if (empty($api_key)) {
           return 'DeepSeek API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
       }
       
       // Model parametrelerini al
       $model = isset($this->settings['deepseek_model']) ? $this->settings['deepseek_model'] : 'deepseek-chat';
       $temperature = isset($this->settings['deepseek_temperature']) ? floatval($this->settings['deepseek_temperature']) : 0.7;
       $max_tokens = isset($this->settings['deepseek_max_tokens']) ? intval($this->settings['deepseek_max_tokens']) : 2000;
       
       // Sistem promptunu al
       $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
       $system_prompt = isset($system_prompts['deepseek']) ? $system_prompts['deepseek'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
       
       // API isteği gönder
       $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', array(
           'headers' => array(
               'Authorization' => 'Bearer ' . $api_key,
               'Content-Type' => 'application/json'
           ),
           'body' => json_encode(array(
               'model' => $model,
               'messages' => array(
                   array('role' => 'system', 'content' => $system_prompt),
                   array('role' => 'user', 'content' => $prompt)
               ),
               'temperature' => $temperature,
               'max_tokens' => $max_tokens
           )),
           'timeout' => 60
       ));
       
       // API isteğini kaydet
       AI_Tarot::log_api_request(
           'deepseek',
           'chat/completions',
           'POST',
           $prompt,
           $response,
           !is_wp_error($response)
       );
       
       // Yanıtı işle
       if (is_wp_error($response)) {
           throw new Exception('DeepSeek API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
       }
       
       $body = json_decode(wp_remote_retrieve_body($response), true);
       
       if (isset($body['choices'][0]['message']['content'])) {
           return $body['choices'][0]['message']['content'];
       } else {
           $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
           throw new Exception('DeepSeek API hatası: ' . $error_message);
       }
   }
   
   /**
    * Grok ile yorum oluştur
    */
   private function generate_with_grok($prompt) {
       // API anahtarını al
       $api_key = isset($this->settings['grok_api_key']) ? $this->settings['grok_api_key'] : '';
       
       if (empty($api_key)) {
           return 'Grok API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
       }
       
       // Model parametrelerini al
       $temperature = isset($this->settings['grok_temperature']) ? floatval($this->settings['grok_temperature']) : 0.7;
       $max_tokens = isset($this->settings['grok_max_tokens']) ? intval($this->settings['grok_max_tokens']) : 2000;
       
       // Sistem promptunu al
       $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
       $system_prompt = isset($system_prompts['grok']) ? $system_prompts['grok'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
       
       // API isteği gönder
       $response = wp_remote_post('https://api.grok.x/v1/chat/completions', array(
           'headers' => array(
               'Authorization' => 'Bearer ' . $api_key,
               'Content-Type' => 'application/json'
           ),
           'body' => json_encode(array(
               'model' => 'grok-1',
               'messages' => array(
                   array('role' => 'system', 'content' => $system_prompt),
                   array('role' => 'user', 'content' => $prompt)
               ),
               'temperature' => $temperature,
               'max_tokens' => $max_tokens
           )),
           'timeout' => 60
       ));
       
       // API isteğini kaydet
       AI_Tarot::log_api_request(
           'grok',
           'chat/completions',
           'POST',
           $prompt,
           $response,
           !is_wp_error($response)
       );
       
       // Yanıtı işle
       if (is_wp_error($response)) {
           throw new Exception('Grok API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
       }
       
       $body = json_decode(wp_remote_retrieve_body($response), true);
       
       if (isset($body['choices'][0]['message']['content'])) {
           return $body['choices'][0]['message']['content'];
       } else {
           $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
           throw new Exception('Grok API hatası: ' . $error_message);
       }
   }
   
   /**
    * Meta (Llama) ile yorum oluştur
    */
   private function generate_with_meta($prompt) {
       // API anahtarını al
       $api_key = isset($this->settings['meta_api_key']) ? $this->settings['meta_api_key'] : '';
       
       if (empty($api_key)) {
           return 'Meta API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
       }
       
       // Model parametrelerini al
       $model = isset($this->settings['meta_model']) ? $this->settings['meta_model'] : 'llama-3-70b-instruct';
       $temperature = isset($this->settings['meta_temperature']) ? floatval($this->settings['meta_temperature']) : 0.7;
       $max_tokens = isset($this->settings['meta_max_tokens']) ? intval($this->settings['meta_max_tokens']) : 2000;
       
       // Sistem promptunu al
       $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
       $system_prompt = isset($system_prompts['meta']) ? $system_prompts['meta'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
       
       // API isteği gönder
       $response = wp_remote_post('https://api.meta.ai/v1/chat/completions', array(
           'headers' => array(
               'Authorization' => 'Bearer ' . $api_key,
               'Content-Type' => 'application/json'
           ),
           'body' => json_encode(array(
               'model' => $model,
               'messages' => array(
                   array('role' => 'system', 'content' => $system_prompt),
                   array('role' => 'user', 'content' => $prompt)
               ),
               'temperature' => $temperature,
               'max_tokens' => $max_tokens
           )),
           'timeout' => 60
       ));
       
       // API isteğini kaydet
       AI_Tarot::log_api_request(
           'meta',
           'chat/completions',
           'POST',
           $prompt,
           $response,
           !is_wp_error($response)
       );
       
       // Yanıtı işle
       if (is_wp_error($response)) {
           throw new Exception('Meta API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
       }
       
       $body = json_decode(wp_remote_retrieve_body($response), true);
       
       if (isset($body['choices'][0]['message']['content'])) {
        return $body['choices'][0]['message']['content'];
    } else {
        $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
        throw new Exception('Meta API hatası: ' . $error_message);
    }
}

/**
 * Microsoft Copilot ile yorum oluştur
 */
private function generate_with_copilot($prompt) {
    // API anahtarını al
    $api_key = isset($this->settings['copilot_api_key']) ? $this->settings['copilot_api_key'] : '';
    
    if (empty($api_key)) {
        return 'Microsoft Copilot API anahtarı eksik. Lütfen eklenti ayarlarından API anahtarını ekleyin.';
    }
    
    // Model parametrelerini al
    $temperature = isset($this->settings['copilot_temperature']) ? floatval($this->settings['copilot_temperature']) : 0.7;
    $max_tokens = isset($this->settings['copilot_max_tokens']) ? intval($this->settings['copilot_max_tokens']) : 2000;
    
    // Sistem promptunu al
    $system_prompts = isset($this->settings['system_prompts']) ? $this->settings['system_prompts'] : array();
    $system_prompt = isset($system_prompts['copilot']) ? $system_prompts['copilot'] : 'Sen profesyonel bir tarot uzmanısın. Derin tarot bilgisiyle, kartları, sembolleri ve elementlerini kullanarak anlamlı ve kişiselleştirilmiş yorumlar yapabilirsin.';
    
    // API isteği gönder
    $response = wp_remote_post('https://api.microsoft.com/v1/copilot/completions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode(array(
            'messages' => array(
                array('role' => 'system', 'content' => $system_prompt),
                array('role' => 'user', 'content' => $prompt)
            ),
            'temperature' => $temperature,
            'max_tokens' => $max_tokens
        )),
        'timeout' => 60
    ));
    
    // API isteğini kaydet
    AI_Tarot::log_api_request(
        'copilot',
        'completions',
        'POST',
        $prompt,
        $response,
        !is_wp_error($response)
    );
    
    // Yanıtı işle
    if (is_wp_error($response)) {
        throw new Exception('Microsoft Copilot API ile iletişim sırasında bir hata oluştu: ' . $response->get_error_message());
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['choices'][0]['message']['content'])) {
        return $body['choices'][0]['message']['content'];
    } else {
        $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Bilinmeyen hata';
        throw new Exception('Microsoft Copilot API hatası: ' . $error_message);
    }
}

/**
 * Varsayılan yorum oluştur (API başarısız olduğunda)
 */
private function get_fallback_interpretation($spread_type, $cards) {
    // Basit bir varsayılan yorum oluştur
    $interpretation = "Tarot falı yorumu şu anda kullanılamıyor. Lütfen daha sonra tekrar deneyin.\n\n";
    $interpretation .= "Bu arada, şu kartları çektiniz:\n";
    
    // Kartları listele
    foreach ($cards as $card) {
        $interpretation .= "- " . $card['name'] . " (" . ($card['reversed'] ? 'Ters' : 'Düz') . ")\n";
    }
    
    return $interpretation;
}

/**
 * Açılım türüne göre pozisyon isimlerini al
 */
private function get_position_names($spread_type) {
    switch ($spread_type) {
        case 'three_card':
            return array(
                'Geçmiş',
                'Şimdi',
                'Gelecek'
            );
            
        case 'celtic_cross':
            return array(
                'Mevcut Durum',
                'Zorluk',
                'Bilinçaltı',
                'Geçmiş',
                'Olası Sonuç',
                'Yakın Gelecek',
                'Kendiniz',
                'Dış Etkiler',
                'Umutlar/Korkular',
                'Sonuç'
            );
            
        case 'astrological':
            return array(
                '1. Ev (Kendiniz)',
                '2. Ev (Değerler)',
                '3. Ev (İletişim)',
                '4. Ev (Yuva)',
                '5. Ev (Yaratıcılık)',
                '6. Ev (Sağlık)',
                '7. Ev (İlişkiler)',
                '8. Ev (Dönüşüm)',
                '9. Ev (Felsefe)',
                '10. Ev (Kariyer)',
                '11. Ev (Sosyal Çevre)',
                '12. Ev (Bilinçaltı)'
            );
            
        default:
            return array();
    }
}
}