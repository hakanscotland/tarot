<?php
/**
 * AI Tarot Fal Geçmişi Sayfası Şablonu
 * 
 * Bu dosya, eklentinin fal geçmişi yönetim sayfasını görüntüler.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

// Admin sınıfını kontrol et
if (!$this instanceof AI_Tarot_Admin) {
    return;
}

// Sayfalama değişkenlerini ayarla
$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$per_page = 20;

// Filtre değişkenlerini ayarla
$filters = array();

if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $filters['user_id'] = intval($_GET['user_id']);
}

if (isset($_GET['spread_type']) && !empty($_GET['spread_type'])) {
    $filters['spread_type'] = sanitize_text_field($_GET['spread_type']);
}

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $filters['start_date'] = sanitize_text_field($_GET['start_date']);
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $filters['end_date'] = sanitize_text_field($_GET['end_date']);
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = sanitize_text_field($_GET['search']);
}

// Tarot falı geçmişini al
$readings_data = $this->get_tarot_readings($per_page, $current_page, $filters);
$readings = $readings_data['readings'];
$total_items = $readings_data['total_items'];
$total_pages = $readings_data['total_pages'];

// Kullanıcıları al
$users = $this->get_users_list();
?>

<div class="wrap ai-tarot-admin-container">
    <div class="ai-tarot-admin-header">
        <div class="ai-tarot-admin-logo">
            <img src="<?php echo AI_TAROT_PLUGIN_URL . 'admin/images/tarot-logo.png'; ?>" alt="AI Tarot Logo">
        </div>
        <div class="ai-tarot-admin-title">
            <h2>Tarot Falı Geçmişi</h2>
            <p>Bu sayfadan, kullanıcıların tarot falı geçmişini görüntüleyebilir, filtreleyebilir ve yönetebilirsiniz.</p>
        </div>
    </div>
    
    <div class="ai-tarot-admin-content">
        <div class="filter-section">
            <form method="get" action="">
                <input type="hidden" name="page" value="ai-tarot-history">
                
                <div class="filter-row">
                    <div class="filter-item">
                        <label for="filter-user">Kullanıcı:</label>
                        <select id="filter-user" name="user_id">
                            <option value="">Tüm Kullanıcılar</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user->ID; ?>" <?php selected(isset($filters['user_id']) && $filters['user_id'] == $user->ID); ?>>
                                    <?php echo esc_html($user->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label for="filter-spread">Açılım Türü:</label>
                        <select id="filter-spread" name="spread_type">
                            <option value="">Tüm Açılımlar</option>
                            <option value="three_card" <?php selected(isset($filters['spread_type']) && $filters['spread_type'] == 'three_card'); ?>>Üç Kartlık</option>
                            <option value="celtic_cross" <?php selected(isset($filters['spread_type']) && $filters['spread_type'] == 'celtic_cross'); ?>>Kelt Haçı</option>
                            <option value="astrological" <?php selected(isset($filters['spread_type']) && $filters['spread_type'] == 'astrological'); ?>>Astrolojik</option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label for="filter-start-date">Başlangıç Tarihi:</label>
                        <input type="text" id="filter-start-date" name="start_date" class="datepicker" value="<?php echo isset($filters['start_date']) ? esc_attr($filters['start_date']) : ''; ?>" placeholder="YYYY-MM-DD">
                    </div>
                    
                    <div class="filter-item">
                        <label for="filter-end-date">Bitiş Tarihi:</label>
                        <input type="text" id="filter-end-date" name="end_date" class="datepicker" value="<?php echo isset($filters['end_date']) ? esc_attr($filters['end_date']) : ''; ?>" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-item filter-search">
                        <label for="filter-search">Ara:</label>
                        <input type="text" id="filter-search" name="search" value="<?php echo isset($filters['search']) ? esc_attr($filters['search']) : ''; ?>" placeholder="Soru veya yorumda ara...">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="button button-primary">Filtrele</button>
                        <a href="?page=ai-tarot-history" class="button">Filtreleri Temizle</a>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (empty($readings)): ?>
            <div class="notice notice-info">
                <p>Gösterilecek tarot falı bulunamadı. Lütfen farklı bir filtre deneyin veya tüm tarot fallarını görüntülemek için filtreleri temizleyin.</p>
            </div>
        <?php else: ?>
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_items; ?> öğe</span>
                    
                    <?php if ($total_pages > 1): ?>
                        <span class="pagination-links">
                            <?php
                            // Sayfalama linklerini oluştur
                            $base_url = add_query_arg('page', 'ai-tarot-history', admin_url('admin.php'));
                            
                            // Filtreleri URL'ye ekle
                            foreach ($filters as $key => $value) {
                                $base_url = add_query_arg($key, $value, $base_url);
                            }
                            
                            // İlk sayfa
                            if ($current_page > 1) {
                                echo '<a class="first-page button" href="' . esc_url(add_query_arg('paged', 1, $base_url)) . '"><span class="screen-reader-text">İlk sayfa</span><span aria-hidden="true">&laquo;</span></a>';
                            } else {
                                echo '<span class="first-page button disabled"><span class="screen-reader-text">İlk sayfa</span><span aria-hidden="true">&laquo;</span></span>';
                            }
                            
                            // Önceki sayfa
                            if ($current_page > 1) {
                                echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', $current_page - 1, $base_url)) . '"><span class="screen-reader-text">Önceki sayfa</span><span aria-hidden="true">&lsaquo;</span></a>';
                            } else {
                                echo '<span class="prev-page button disabled"><span class="screen-reader-text">Önceki sayfa</span><span aria-hidden="true">&lsaquo;</span></span>';
                            }
                            
                            // Sayfa numarası
                            echo '<span class="paging-input">' . $current_page . ' / <span class="total-pages">' . $total_pages . '</span></span>';
                            
                            // Sonraki sayfa
                            if ($current_page < $total_pages) {
                                echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', $current_page + 1, $base_url)) . '"><span class="screen-reader-text">Sonraki sayfa</span><span aria-hidden="true">&rsaquo;</span></a>';
                            } else {
                                echo '<span class="next-page button disabled"><span class="screen-reader-text">Sonraki sayfa</span><span aria-hidden="true">&rsaquo;</span></span>';
                            }
                            
                            // Son sayfa
                            if ($current_page < $total_pages) {
                                echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages, $base_url)) . '"><span class="screen-reader-text">Son sayfa</span><span aria-hidden="true">&raquo;</span></a>';
                            } else {
                                echo '<span class="last-page button disabled"><span class="screen-reader-text">Son sayfa</span><span aria-hidden="true">&raquo;</span></span>';
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="readings-table">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="150">Tarih</th>
                        <th width="150">Kullanıcı</th>
                        <th>Soru</th>
                        <th width="100">Açılım</th>
                        <th width="150">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($readings as $reading): ?>
                        <tr class="reading-row" data-reading-id="<?php echo $reading['id']; ?>">
                            <td><?php echo $reading['id']; ?></td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reading['created_at'])); ?></td>
                            <td>
                                <?php 
                                if ($reading['user_id']) {
                                    echo esc_html($reading['user_name']);
                                } else {
                                    echo '<em>Misafir</em>';
                                }
                                ?>
                            </td>
                            <td class="reading-question"><?php echo esc_html($reading['question']); ?></td>
                            <td>
                                <?php
                                switch ($reading['spread_type']) {
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
                                        echo esc_html($reading['spread_type']);
                                        break;
                                }
                                ?>
                            </td>
                            <td class="reading-actions">
                                <a href="#" class="button button-small view-reading-details" data-reading-id="<?php echo $reading['id']; ?>">Görüntüle</a>
                                <a href="#" class="button button-small delete-reading" data-reading-id="<?php echo $reading['id']; ?>" data-nonce="<?php echo wp_create_nonce('delete_reading_' . $reading['id']); ?>">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_items; ?> öğe</span>
                    
                    <?php if ($total_pages > 1): ?>
                        <span class="pagination-links">
                            <?php
                            // Sayfalama linklerini oluştur (üst kısımla aynı)
                            $base_url = add_query_arg('page', 'ai-tarot-history', admin_url('admin.php'));
                            
                            // Filtreleri URL'ye ekle
                            foreach ($filters as $key => $value) {
                                $base_url = add_query_arg($key, $value, $base_url);
                            }
                            
                            // İlk sayfa
                            if ($current_page > 1) {
                                echo '<a class="first-page button" href="' . esc_url(add_query_arg('paged', 1, $base_url)) . '"><span class="screen-reader-text">İlk sayfa</span><span aria-hidden="true">&laquo;</span></a>';
                            } else {
                                echo '<span class="first-page button disabled"><span class="screen-reader-text">İlk sayfa</span><span aria-hidden="true">&laquo;</span></span>';
                            }
                            
                            // Önceki sayfa
                            if ($current_page > 1) {
                                echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', $current_page - 1, $base_url)) . '"><span class="screen-reader-text">Önceki sayfa</span><span aria-hidden="true">&lsaquo;</span></a>';
                            } else {
                                echo '<span class="prev-page button disabled"><span class="screen-reader-text">Önceki sayfa</span><span aria-hidden="true">&lsaquo;</span></span>';
                            }
                            
                            // Sayfa numarası
                            echo '<span class="paging-input">' . $current_page . ' / <span class="total-pages">' . $total_pages . '</span></span>';
                            
                            // Sonraki sayfa
                            if ($current_page < $total_pages) {
                                echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', $current_page + 1, $base_url)) . '"><span class="screen-reader-text">Sonraki sayfa</span><span aria-hidden="true">&rsaquo;</span></a>';
                            } else {
                                echo '<span class="next-page button disabled"><span class="screen-reader-text">Sonraki sayfa</span><span aria-hidden="true">&rsaquo;</span></span>';
                            }
                            
                            // Son sayfa
                            if ($current_page < $total_pages) {
                                echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages, $base_url)) . '"><span class="screen-reader-text">Son sayfa</span><span aria-hidden="true">&raquo;</span></a>';
                            } else {
                                echo '<span class="last-page button disabled"><span class="screen-reader-text">Son sayfa</span><span aria-hidden="true">&raquo;</span></span>';
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Fal Detay Modalı -->
        <div id="reading-detail-modal" class="reading-modal" style="display: none;">
            <!-- Fal detay modalı AJAX ile doldurulacak -->
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Tarih seçicileri
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
        
        // Fal detayları görüntüleme
        $('.view-reading-details').on('click', function(e) {
            e.preventDefault();
            
            var readingId = $(this).data('reading-id');
            
            // Yükleniyor göster
            $('#reading-detail-modal').html('<div class="loading">Yükleniyor...</div>').show();
            
            // AJAX ile fal detaylarını getir
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_reading_details',
                    nonce: ai_tarot_admin.nonce,
                    reading_id: readingId
                },
                success: function(response) {
                    if (response.success) {
                        // Modal içeriğini güncelle
                        $('#reading-detail-modal').html(response.data.html);
                        
                        // Modal kapanma olayı
                        $('.reading-modal-close, .reading-modal-overlay').on('click', function(e) {
                            if (e.target === this) {
                                $('#reading-detail-modal').hide();
                            }
                        });
                    } else {
                        $('#reading-detail-modal').html('<div class="error">Fal detayları alınamadı: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $('#reading-detail-modal').html('<div class="error">Fal detayları alınırken bir hata oluştu.</div>');
                }
            });
        });
        
        // Fal silme
        $('.delete-reading').on('click', function(e) {
            e.preventDefault();
            
            var readingId = $(this).data('reading-id');
            var nonce = $(this).data('nonce');
            
            if (confirm('Bu fal kaydını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                // AJAX ile falı sil
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'delete_reading',
                        nonce: nonce,
                        reading_id: readingId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Falı listeden kaldır
                            $('.reading-row[data-reading-id="' + readingId + '"]').fadeOut(300, function() {
                                $(this).remove();
                                
                                // Hiç fal kalmadıysa bildirim göster
                                if ($('.reading-row').length === 0) {
                                    $('.readings-table').before('<div class="notice notice-info"><p>Gösterilecek tarot falı bulunamadı. Lütfen farklı bir filtre deneyin veya tüm tarot fallarını görüntülemek için filtreleri temizleyin.</p></div>');
                                    $('.readings-table, .tablenav').hide();
                                }
                            });
                        } else {
                            alert('Fal silinirken bir hata oluştu: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Fal silinirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
                    }
                });
            }
        });
    });
</script>