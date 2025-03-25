<div class="ai-tarot-container">
    <div class="tarot-form-container" id="tarot-form-container">
        <h2 class="tarot-form-title">AI Tarot Falı</h2>
        <p class="tarot-form-description">Sorunuzu sorun ve açılım türünü seçin. Yapay zeka destekli tarot falı size içgörüler sunacaktır.</p>
        
        <form id="tarot-form" class="tarot-form">
            <div class="form-group">
                <label for="tarot-question">Soru veya Niyet:</label>
                <input type="text" id="tarot-question" name="question" placeholder="Tarot'a sormak istediğiniz soruyu yazın..." required>
            </div>
            
            <div class="form-group">
                <label for="tarot-spread">Açılım Türü:</label>
                <select id="tarot-spread" name="spread_type">
                    <option value="three_card">Üç Kartlık Açılım</option>
                    <option value="celtic_cross">Kelt Haçı Açılımı</option>
                    <option value="astrological">Astrolojik Açılım</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="tarot-submit-button">Tarot Falı Çek</button>
            </div>
        </form>
    </div>
    
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
            <div class="loader-text">Tarot falınız hazırlanıyor...</div>
        </div>
    </div>
</div>