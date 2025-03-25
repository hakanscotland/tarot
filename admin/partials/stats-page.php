<?php
/**
 * AI Tarot İstatistikler Sayfası Şablonu
 * 
 * Bu dosya, eklentinin istatistikler yönetim sayfasını görüntüler.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

// Admin sınıfını kontrol et
if (!$this instanceof AI_Tarot_Admin) {
    return;
}

// İstatistikleri al
$stats = $this->get_tarot_statistics();

// Aylık verileri JS için hazırla
$monthly_data = array(
    'labels' => array(),
    'data' => array()
);

foreach ($stats['monthly_stats'] as $month_data) {
    // Ayı formatla (2023-01 -> Ocak 2023)
    $date = date_create_from_format('Y-m', $month_data['month']);
    $formatted_month = date_i18n('F Y', $date->getTimestamp());
    
    $monthly_data['labels'][] = $formatted_month;
    $monthly_data['data'][] = $month_data['count'];
}

// Açılım türleri verilerini JS için hazırla
$spread_data = array(
    'labels' => array(),
    'data' => array()
);

foreach ($stats['spread_stats'] as $spread) {
    $label = '';
    switch ($spread['spread_type']) {
        case 'three_card':
            $label = 'Üç Kartlık';
            break;
        case 'celtic_cross':
            $label = 'Kelt Haçı';
            break;
        case 'astrological':
            $label = 'Astrolojik';
            break;
        default:
            $label = $spread['spread_type'];
            break;
    }
    
    $spread_data['labels'][] = $label;
    $spread_data['data'][] = $spread['count'];
}

// AI servisi kullanım verilerini JS için hazırla
$ai_service_data = array(
    'labels' => array(),
    'data' => array()
);

foreach ($stats['ai_service_stats'] as $service) {
    $ai_service_data['labels'][] = $service['ai_service'];
    $ai_service_data['data'][] = $service['count'];
}

// Chart.js için verileri lokalize et
wp_localize_script('ai-tarot-admin', 'ai_tarot_admin', array(
    'monthly_stats' => $monthly_data,
    'spread_stats' => $spread_data,
    'ai_service_stats' => $ai_service_data,
    'nonce' => wp_create_nonce('ai_tarot_admin_nonce')
));
?>

<div class="wrap ai-tarot-admin-container">
    <div class="ai-tarot-admin-header">
        <div class="ai-tarot-admin-logo">
            <img src="<?php echo AI_TAROT_PLUGIN_URL . 'admin/images/tarot-logo.png'; ?>" alt="AI Tarot Logo">
        </div>
        <div class="ai-tarot-admin-title">
            <h2>Tarot Falı İstatistikleri</h2>
            <p>Bu sayfadan, eklentinin kullanımıyla ilgili istatistikleri ve analizleri görüntüleyebilirsiniz.</p>
        </div>
    </div>
    
    <div class="ai-tarot-admin-content">
        <!-- Özet Kartları -->
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Toplam Fal Sayısı</h3>
                <div class="stat-value"><?php echo number_format_i18n($stats['total_readings']); ?></div>
                <div class="stat-label">Tüm zamanlar</div>
            </div>
            
            <div class="stat-card">
                <h3>Bu Ayki Fal Sayısı</h3>
                <div class="stat-value">
                    <?php 
                    $current_month = date('Y-m');
                    $current_month_readings = 0;
                    
                    foreach ($stats['monthly_stats'] as $month_data) {
                        if ($month_data['month'] == $current_month) {
                            $current_month_readings = $month_data['count'];
                            break;
                        }
                    }
                    
                    echo number_format_i18n($current_month_readings);
                    ?>
                </div>
                <div class="stat-label"><?php echo date_i18n('F Y'); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>En Popüler Açılım</h3>
                <div class="stat-value">
                    <?php 
                    $most_popular_spread = !empty($stats['spread_stats']) ? $stats['spread_stats'][0] : null;
                    
                    if ($most_popular_spread) {
                        $label = '';
                        switch ($most_popular_spread['spread_type']) {
                            case 'three_card':
                                echo 'Üç Kartlık';
                                break;
                            case 'celtic_cross':
                                echo 'Kelt Haçı';
                                break;
                            case 'astrological':
                                echo 'Astrolojik';
                                break;
                            default:
                                echo esc_html($most_popular_spread['spread_type']);
                                break;
                        }
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
                <div class="stat-label">En çok kullanılan açılım türü</div>
            </div>
            
            <div class="stat-card">
                <h3>En Popüler AI</h3>
                <div class="stat-value">
                    <?php 
                    $most_popular_ai = !empty($stats['ai_service_stats']) ? $stats['ai_service_stats'][0]['ai_service'] : null;
                    echo $most_popular_ai ? esc_html($most_popular_ai) : '-';
                    ?>
                </div>
                <div class="stat-label">En çok kullanılan AI servisi</div>
            </div>
        </div>
        
        <!-- Aylık İstatistik Grafiği -->
        <div class="chart-container">
            <h3>Aylık Fal Çekim Sayısı</h3>
            <canvas id="reading-stats-chart" class="chart-canvas"></canvas>
        </div>
        
        <!-- Açılım Türleri Grafiği -->
        <div class="chart-container">
            <h3>Açılım Türü Kullanımı</h3>
            <canvas id="spread-stats-chart" class="chart-canvas"></canvas>
        </div>
        
        <!-- AI Servisi Kullanım Grafiği -->
        <div class="chart-container">
            <h3>AI Servisi Kullanımı</h3>
            <canvas id="ai-service-stats-chart" class="chart-canvas"></canvas>
        </div>
        
        <!-- En Çok Çekilen Kartlar -->
        <div class="chart-container">
            <h3>En Çok Çekilen Kartlar</h3>
            
            <?php if (empty($stats['card_stats'])): ?>
                <div class="notice notice-info" style="margin: 10px 0;">
                    <p>Henüz yeterli veri bulunmamaktadır.</p>
                </div>
            <?php else: ?>
                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th width="50">Sıra</th>
                            <th>Kart Adı</th>
                            <th width="150">Çekilme Sayısı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['card_stats'] as $index => $card): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo esc_html($card['name']); ?></td>
                                <td><?php echo number_format_i18n($card['count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Filtreleme Seçenekleri -->
        <div class="chart-container">
            <h3>İstatistik Filtreleme</h3>
            
            <form id="stats-filter-form" method="get" action="">
                <input type="hidden" name="page" value="ai-tarot-stats">
                
                <div class="stats-filter-row">
                    <div class="stats-filter-item">
                        <label for="stats-date-range">Tarih Aralığı:</label>
                        <select id="stats-date-range" name="date_range">
                            <option value="all">Tüm zamanlar</option>
                            <option value="this_month">Bu ay</option>
                            <option value="last_month">Geçen ay</option>
                            <option value="this_year">Bu yıl</option>
                            <option value="last_year">Geçen yıl</option>
                            <option value="custom">Özel tarih aralığı</option>
                        </select>
                    </div>
                    
                    <div class="stats-filter-item stats-date-inputs" style="display: none;">
                        <label for="stats-start-date">Başlangıç:</label>
                        <input type="text" id="stats-start-date" name="start_date" class="datepicker" placeholder="YYYY-MM-DD">
                        
                        <label for="stats-end-date">Bitiş:</label>
                        <input type="text" id="stats-end-date" name="end_date" class="datepicker" placeholder="YYYY-MM-DD">
                    </div>
                    
                    <div class="stats-filter-actions">
                        <button type="submit" class="button button-primary">Filtrele</button>
                        <a href="?page=ai-tarot-stats" class="button">Filtreleri Temizle</a>
                    </div>
                </div>
            </form>
            
            <script>
                jQuery(document).ready(function($) {
                    // Tarih seçicileri
                    $('.datepicker').datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true
                    });
                    
                    // Özel tarih aralığı seçildiğinde tarih giriş alanlarını göster
                    $('#stats-date-range').on('change', function() {
                        if ($(this).val() === 'custom') {
                            $('.stats-date-inputs').show();
                        } else {
                            $('.stats-date-inputs').hide();
                        }
                    });
                });
            </script>
        </div>
        
        <!-- Export Seçenekleri -->
        <div class="chart-container">
            <h3>Verileri Dışa Aktar</h3>
            
            <p>İstatistik verilerinizi CSV veya PDF formatında dışa aktarabilirsiniz.</p>
            
            <div class="export-buttons">
                <a href="<?php echo admin_url('admin-ajax.php?action=export_tarot_stats&format=csv&nonce=' . wp_create_nonce('export_tarot_stats')); ?>" class="button">CSV Olarak İndir</a>
                <a href="<?php echo admin_url('admin-ajax.php?action=export_tarot_stats&format=pdf&nonce=' . wp_create_nonce('export_tarot_stats')); ?>" class="button">PDF Olarak İndir</a>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Aylık istatistik grafiği
        var readingStatsCtx = document.getElementById('reading-stats-chart').getContext('2d');
        var readingStatsChart = new Chart(readingStatsCtx, {
            type: 'bar',
            data: {
                labels: ai_tarot_admin.monthly_stats.labels,
                datasets: [{
                    label: 'Aylık Fal Sayısı',
                    data: ai_tarot_admin.monthly_stats.data,
                    backgroundColor: 'rgba(90, 60, 110, 0.7)',
                    borderColor: 'rgba(90, 60, 110, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Açılım türleri grafiği
        var spreadStatsCtx = document.getElementById('spread-stats-chart').getContext('2d');
        var spreadStatsChart = new Chart(spreadStatsCtx, {
            type: 'pie',
            data: {
                labels: ai_tarot_admin.spread_stats.labels,
                datasets: [{
                    data: ai_tarot_admin.spread_stats.data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // AI servisi kullanım grafiği
        var aiServiceStatsCtx = document.getElementById('ai-service-stats-chart').getContext('2d');
        var aiServiceStatsChart = new Chart(aiServiceStatsCtx, {
            type: 'doughnut',
            data: {
                labels: ai_tarot_admin.ai_service_stats.labels,
                datasets: [{
                    data: ai_tarot_admin.ai_service_stats.data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(201, 203, 207, 0.7)',
                        'rgba(255, 99, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
</script>