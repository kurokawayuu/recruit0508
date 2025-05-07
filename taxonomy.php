<?php
/**
 * タクソノミーアーカイブテンプレート
 * すべてのタクソノミーアーカイブページに適用されます
 */
get_header();

// 現在のタクソノミー情報を取得
$term = get_queried_object();
$taxonomy = get_taxonomy($term->taxonomy);
?>

<div class="content-wrapper">
    <div class="container">
        <div class="job-search-results">
            <!-- 検索フォームを表示 -->
            <?php get_template_part('search', 'form'); ?>
            
            <!-- 検索タイトル -->
            <div class="search-results-header">
                <h1 class="search-results-title">
                    <?php 
                    // タクソノミーに応じたタイトルを表示
                    switch ($term->taxonomy) {
                        case 'job_location':
                            echo esc_html($term->name) . 'の求人・転職・アルバイト情報';
                            break;
                        case 'job_position':
                            echo esc_html($term->name) . 'の求人・転職・アルバイト情報';
                            break;
                        case 'job_type':
                            echo esc_html($term->name) . 'の求人・転職・アルバイト情報';
                            break;
                        case 'facility_type':
                            echo esc_html($term->name) . 'の求人・転職・アルバイト情報';
                            break;
                        case 'job_feature':
                            echo esc_html($term->name) . 'の求人・転職・アルバイト情報';
                            break;
                        default:
                            echo '求人・転職・アルバイト情報';
                            break;
                    }
                    ?>
                </h1>
            </div>
            
            <!-- 求人リスト -->
            <div class="job-list">
                <?php if (have_posts()): ?>
                    <?php while (have_posts()): the_post(); 
                        // カスタムフィールドの取得
                        $salary = get_post_meta(get_the_ID(), '_salary', true);
                        $company_name = get_post_meta(get_the_ID(), '_company_name', true);
                        $address = get_post_meta(get_the_ID(), '_address', true);
                        
                        // 雇用形態の取得
                        $job_types = get_the_terms(get_the_ID(), 'job_type');
                        $job_type = '';
                        if (!empty($job_types) && !is_wp_error($job_types)) {
                            $job_type = $job_types[0]->name;
                        }
                        
                        // 職種の取得
                        $job_positions = get_the_terms(get_the_ID(), 'job_position');
                        $job_position = '';
                        if (!empty($job_positions) && !is_wp_error($job_positions)) {
                            $job_position = $job_positions[0]->name;
                        }
                        
                        // 特徴タグの取得
                        $job_features = get_the_terms(get_the_ID(), 'job_feature');
                        $feature_names = array();
                        if (!empty($job_features) && !is_wp_error($job_features)) {
                            foreach ($job_features as $feature) {
                                $feature_names[] = $feature->name;
                            }
                        }
                    ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <div class="job-company-name"><?php echo esc_html($company_name); ?></div>
                            <div class="job-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (!empty($job_position)): ?>
                                        <span class="job-position-label">[<?php echo esc_html($job_position); ?>]</span>
                                    <?php endif; ?>
                                    <?php the_title(); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="job-card-body">
                            <div class="job-info">
                                <div class="job-location"><?php echo esc_html($address); ?></div>
                                <div class="job-salary"><?php echo esc_html($salary); ?></div>
                                <div class="job-type"><?php echo esc_html($job_type); ?></div>
                            </div>
                            
                            <?php if (!empty($feature_names)): ?>
                            <div class="job-tags">
                                <?php foreach ($feature_names as $feature_name): ?>
                                    <span class="job-tag"><?php echo esc_html($feature_name); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="job-description">
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                        
                        <div class="job-card-footer">
                            <div class="job-actions">
                                <?php if (is_user_logged_in()): 
                                    // お気に入り状態の確認
                                    $is_favorite = false;
                                    $user_id = get_current_user_id();
                                    $favorites = get_user_meta($user_id, 'job_favorites', true);
                                    if (is_array($favorites) && in_array(get_the_ID(), $favorites)) {
                                        $is_favorite = true;
                                    }
                                ?>
                                    <button class="favorite-toggle <?php echo $is_favorite ? 'active' : ''; ?>" data-job-id="<?php echo get_the_ID(); ?>">
                                        <span class="star-icon">★</span> <?php echo $is_favorite ? 'キープ済み' : 'キープ'; ?>
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo home_url('/login/'); ?>?redirect_to=<?php echo urlencode(get_permalink()); ?>" class="favorite-button">
                                        <span class="star-icon">★</span> キープ
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="detail-button">詳細をみる</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    
                    <!-- ページネーション -->
                    <div class="pagination">
                        <?php
                        echo paginate_links(array(
                            'prev_text' => '&laquo; 前へ',
                            'next_text' => '次へ &raquo;',
                        ));
                        ?>
                    </div>
                    
                <?php else: ?>
                    <!-- 検索結果がない場合 -->
                    <div class="no-results">
                        <div class="no-results-message">
                            <p>選択された条件に一致する求人が見つかりませんでした。</p>
                            <p>検索条件を変更して再度お試しください。</p>
                        </div>
                        <div class="no-results-actions">
                            <a href="<?php echo home_url(); ?>" class="button">トップページに戻る</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- サイドバー（フィルター） -->
            <div class="filter-sidebar">
                <h2 class="filter-title">求人を絞り込む</h2>
                
                <!-- エリアから探す -->
                <div class="filter-section">
                    <h3 class="filter-section-title">●エリアから探す</h3>
                    <div class="filter-list area-filter">
                        <?php
                        // 地域グループの配列
                        $area_groups = array(
                            '関東エリア' => array('東京都', '神奈川県', '埼玉県', '千葉県', '茨城県', '栃木県', '群馬県'),
                            '近畿エリア' => array('大阪府', '兵庫県', '京都府', '滋賀県', '奈良県', '和歌山県'),
                            '東海エリア' => array('愛知県', '静岡県', '岐阜県', '三重県'),
                            '北海道・東北エリア' => array('北海道', '宮城県', '福島県', '青森県', '岩手県', '山形県', '秋田県'),
                            '北陸・甲信越エリア' => array('新潟県', '長野県', '石川県', '富山県', '山梨県', '福井県'),
                            '中国・四国エリア' => array('広島県', '岡山県', '山口県', '島根県', '鳥取県', '愛媛県', '香川県', '徳島県', '高知県'),
                            '九州・沖縄エリア' => array('福岡県', '熊本県', '鹿児島県', '長崎県', '大分県', '宮崎県', '佐賀県', '沖縄県')
                        );
                        
                        // エリアグループの表示
                        foreach ($area_groups as $group_name => $prefectures) {
                            echo '<div class="area-group">';
                            echo '<p class="area-group-name">' . esc_html($group_name) . '：</p>';
                            echo '<div class="prefecture-list">';
                            foreach ($prefectures as $prefecture) {
                                // 都道府県名からタクソノミータームのスラッグを取得
                                $prefecture_term = get_term_by('name', $prefecture, 'job_location');
                                if ($prefecture_term) {
                                    $current = ($term->term_id === $prefecture_term->term_id) ? ' current' : '';
                                    echo '<a href="' . esc_url(get_term_link($prefecture_term)) . '" class="prefecture-link' . $current . '">' . esc_html($prefecture) . '</a>';
                                } else {
                                    echo '<span class="prefecture-link">' . esc_html($prefecture) . '</span>';
                                }
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- 職種から探す -->
                <div class="filter-section">
                    <h3 class="filter-section-title">●職種から探す</h3>
                    <div class="filter-list">
                        <?php
                        $positions = array(
                            '児童発達支援管理責任者',
                            '児童指導員',
                            '保育士',
                            '理学療法士',
                            '言語聴覚士',
                            '作業療法士',
                            'その他'
                        );
                        
                        foreach ($positions as $position) {
                            $position_term = get_term_by('name', $position, 'job_position');
                            if ($position_term) {
                                $current = ($term->term_id === $position_term->term_id) ? ' current' : '';
                                echo '<div class="filter-item">';
                                echo '<a href="' . esc_url(get_term_link($position_term)) . '" class="filter-link' . $current . '">' . esc_html($position) . '</a>';
                                echo '</div>';
                            } else {
                                echo '<div class="filter-item">';
                                echo '<span class="filter-link">' . esc_html($position) . '</span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <!-- 特徴から探す -->
                <div class="filter-section">
                    <h3 class="filter-section-title">●特徴から探す</h3>
                    <div class="filter-list feature-list">
                        <?php
                        $features = array(
                            '送迎業務なし',
                            '高収入求人',
                            'オープニングスタッフ',
                            '施設見学OK',
                            'WEB面接OK',
                            '即日応募'
                        );
                        
                        foreach ($features as $feature) {
                            $feature_term = get_term_by('name', $feature, 'job_feature');
                            if ($feature_term) {
                                $current = ($term->term_id === $feature_term->term_id) ? ' current' : '';
                                echo '<div class="feature-item">';
                                echo '<a href="' . esc_url(get_term_link($feature_term)) . '" class="feature-link' . $current . '">' . esc_html($feature) . '</a>';
                                echo '</div>';
                            } else {
                                echo '<div class="feature-item">';
                                echo '<span class="feature-link">' . esc_html($feature) . '</span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <!-- 雇用形態から探す -->
                <div class="filter-section">
                    <h3 class="filter-section-title">●雇用形態から探す</h3>
                    <div class="filter-list">
                        <?php
                        $employment_types = array(
                            '正社員',
                            'パート・アルバイト',
                            'その他'
                        );
                        
                        foreach ($employment_types as $type) {
                            $type_term = get_term_by('name', $type, 'job_type');
                            if ($type_term) {
                                $current = ($term->term_id === $type_term->term_id) ? ' current' : '';
                                echo '<div class="filter-item">';
                                echo '<a href="' . esc_url(get_term_link($type_term)) . '" class="filter-link' . $current . '">' . esc_html($type) . '</a>';
                                echo '</div>';
                            } else {
                                echo '<div class="filter-item">';
                                echo '<span class="filter-link">' . esc_html($type) . '</span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <!-- 施設形態から探す -->
                <div class="filter-section">
                    <h3 class="filter-section-title">●施設形態から探す</h3>
                    <div class="filter-list">
                        <?php
                        $facility_types = array(
                            '放課後等デイサービス・児童発達支援',
                            '放課後等デイサービスのみ',
                            '児童発達支援のみ'
                        );
                        
                        foreach ($facility_types as $facility) {
                            $facility_term = get_term_by('name', $facility, 'facility_type');
                            if ($facility_term) {
                                $current = ($term->term_id === $facility_term->term_id) ? ' current' : '';
                                echo '<div class="filter-item">';
                                echo '<a href="' . esc_url(get_term_link($facility_term)) . '" class="filter-link' . $current . '">' . esc_html($facility) . '</a>';
                                echo '</div>';
                            } else {
                                echo '<div class="filter-item">';
                                echo '<span class="filter-link">' . esc_html($facility) . '</span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- お気に入り機能のためのJavaScript -->
<script>
jQuery(document).ready(function($) {
    // お気に入り追加・削除の処理
    $('.favorite-toggle').on('click', function() {
        var jobId = $(this).data('job-id');
        var $button = $(this);
        
        if ($button.hasClass('active')) {
            // お気に入りから削除
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                data: {
                    action: 'remove_from_favorites',
                    job_id: jobId,
                    nonce: '<?php echo wp_create_nonce('favorite_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $button.removeClass('active').html('<span class="star-icon">★</span> キープ');
                    } else {
                        alert(response.data);
                    }
                }
            });
        } else {
            // お気に入りに追加
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                data: {
                    action: 'add_to_favorites',
                    job_id: jobId,
                    nonce: '<?php echo wp_create_nonce('favorite_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $button.addClass('active').html('<span class="star-icon">★</span> キープ済み');
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
    });
});
</script>

<?php get_footer(); ?>
