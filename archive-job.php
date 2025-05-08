<?php
/**
 * 求人アーカイブテンプレート
 * 
 * このファイルはURLパス構造で求人を表示するためのメインテンプレートです。
 * /jobs/location/tokyo/position/nurse/ などのURLに対応します。
 */
get_header();

// 現在のURLからフィルター条件を取得
$current_filters = get_job_filters_from_url();

// タイトル部分の設定
$archive_title = '求人情報一覧';
$filter_description = '';

// フィルターに基づいてタイトルを調整
if (!empty($current_filters)) {
    $title_parts = array();
    
    if (!empty($current_filters['location'])) {
        $location_term = get_term_by('slug', $current_filters['location'], 'job_location');
        if ($location_term) {
            $title_parts[] = $location_term->name;
        }
    }
    
    if (!empty($current_filters['position'])) {
        $position_term = get_term_by('slug', $current_filters['position'], 'job_position');
        if ($position_term) {
            $title_parts[] = $position_term->name;
        }
    }
    
    if (!empty($current_filters['type'])) {
        $type_term = get_term_by('slug', $current_filters['type'], 'job_type');
        if ($type_term) {
            $title_parts[] = $type_term->name;
        }
    }
    
    if (!empty($current_filters['facility'])) {
        $facility_term = get_term_by('slug', $current_filters['facility'], 'facility_type');
        if ($facility_term) {
            $title_parts[] = $facility_term->name;
        }
    }
    
    if (!empty($current_filters['feature'])) {
        $feature_term = get_term_by('slug', $current_filters['feature'], 'job_feature');
        if ($feature_term) {
            $title_parts[] = $feature_term->name;
        }
    }
    
    // 特徴の配列がある場合の処理を追加
    if (!empty($current_filters['features']) && is_array($current_filters['features'])) {
        $feature_names = array();
        foreach ($current_filters['features'] as $feature_slug) {
            $feature_term = get_term_by('slug', $feature_slug, 'job_feature');
            if ($feature_term) {
                $feature_names[] = $feature_term->name;
            }
        }
        
        if (!empty($feature_names)) {
            $title_parts = array_merge($title_parts, $feature_names);
        }
    }
    
    if (!empty($title_parts)) {
        $archive_title = implode(' × ', $title_parts) . 'の求人情報';
        $filter_description = implode('、', $title_parts) . 'に関連する求人情報を表示しています。';
    }
}

// カスタムクエリを構築
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$query_args = array(
    'post_type' => 'job',
    'posts_per_page' => 10,
    'paged' => $paged,
);

// タクソノミークエリを追加
$tax_query = array();

if (!empty($current_filters['location'])) {
    $tax_query[] = array(
        'taxonomy' => 'job_location',
        'field'    => 'slug',
        'terms'    => $current_filters['location'],
    );
}

if (!empty($current_filters['position'])) {
    $tax_query[] = array(
        'taxonomy' => 'job_position',
        'field'    => 'slug',
        'terms'    => $current_filters['position'],
    );
}

if (!empty($current_filters['type'])) {
    $tax_query[] = array(
        'taxonomy' => 'job_type',
        'field'    => 'slug',
        'terms'    => $current_filters['type'],
    );
}

if (!empty($current_filters['facility'])) {
    $tax_query[] = array(
        'taxonomy' => 'facility_type',
        'field'    => 'slug',
        'terms'    => $current_filters['facility'],
    );
}

// 複数特徴の条件追加
if (!empty($current_filters['features']) && is_array($current_filters['features'])) {
    $tax_query[] = array(
        'taxonomy' => 'job_feature',
        'field'    => 'slug',
        'terms'    => $current_filters['features'],
        'operator' => 'IN',
    );
} elseif (!empty($current_filters['feature'])) {
    // 従来の単一特徴の条件
    $tax_query[] = array(
        'taxonomy' => 'job_feature',
        'field'    => 'slug',
        'terms'    => $current_filters['feature'],
    );
}

if (!empty($tax_query)) {
    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }
    $query_args['tax_query'] = $tax_query;
}

$job_query = new WP_Query($query_args);
?>

<div class="job-archive-container">
    <div class="job-archive-header">
        <h1 class="archive-title"><?php echo esc_html($archive_title); ?></h1>
        
        <?php if (!empty($filter_description)): ?>
            <p class="filter-description"><?php echo esc_html($filter_description); ?></p>
        <?php endif; ?>
        
        <div class="job-count">
            <p>検索結果: <span class="count-number"><?php echo esc_html($job_query->found_posts); ?></span>件</p>
        </div>
        
        <!-- 検索フォームを表示 -->
        <?php get_template_part('search-form'); ?>
        
        <!-- 現在のフィルターの表示 -->
        <?php if (!empty($current_filters)): ?>
            <div class="current-filters">
                <h3>現在の検索条件</h3>
                <div class="filter-tags">
                    <?php foreach ($current_filters as $type => $value): 
                        // 複数特徴と特徴のみのフラグをスキップ（後で個別に処理）
                        if ($type === 'features' || $type === 'features_only') continue;
                        
                        $taxonomy = '';
                        $label = '';
                        
                        switch ($type) {
                            case 'location':
                                $taxonomy = 'job_location';
                                $label = 'エリア';
                                break;
                            case 'position':
                                $taxonomy = 'job_position';
                                $label = '職種';
                                break;
                            case 'type':
                                $taxonomy = 'job_type';
                                $label = '雇用形態';
                                break;
                            case 'facility':
                                $taxonomy = 'facility_type';
                                $label = '施設形態';
                                break;
                            case 'feature':
                                $taxonomy = 'job_feature';
                                $label = '特徴';
                                break;
                        }
                        
                        if (!empty($taxonomy)) {
                            $term = get_term_by('slug', $value, $taxonomy);
                            if ($term) {
                                $remove_url = remove_filter_from_url($type);
                                echo '<div class="filter-tag">';
                                echo '<span class="filter-label">' . esc_html($label) . ':</span>';
                                echo '<span class="filter-value">' . esc_html($term->name) . '</span>';
                                echo '<a href="' . esc_url($remove_url) . '" class="remove-filter" title="この条件を削除"><i class="fas fa-times"></i></a>';
                                echo '</div>';
                            }
                        }
                    endforeach; 
                    
                    // 複数特徴の表示
                    if (!empty($current_filters['features'])): 
                        foreach ($current_filters['features'] as $feature_slug):
                            $feature_term = get_term_by('slug', $feature_slug, 'job_feature');
                            if ($feature_term):
                                // 現在のURLから特定の特徴を削除するURL生成
                                $current_url = add_query_arg(array());
                                $features = isset($_GET['features']) ? $_GET['features'] : array();
                                $features_key = array_search($feature_slug, $features);
                                
                                if ($features_key !== false) {
                                    unset($features[$features_key]);
                                }
                                
                                // 特徴を削除した後のURL作成
                                if (empty($features)) {
                                    // 特徴がなくなる場合
                                    if (!empty($current_filters['location']) || !empty($current_filters['position']) || 
                                        !empty($current_filters['type']) || !empty($current_filters['facility']) || 
                                        !empty($current_filters['feature'])) {
                                        // 他のパス条件がある場合
                                        $remove_url = remove_query_arg('features', $current_url);
                                    } else {
                                        // 特徴のみの検索だった場合はトップに戻る
                                        $remove_url = home_url('/jobs/');
                                    }
                                } else {
                                    // まだ他の特徴が残っている場合
                                    $remove_url = add_query_arg('features', $features, $current_url);
                                }
                                
                                echo '<div class="filter-tag">';
                                echo '<span class="filter-label">特徴:</span>';
                                echo '<span class="filter-value">' . esc_html($feature_term->name) . '</span>';
                                echo '<a href="' . esc_url($remove_url) . '" class="remove-filter" title="この条件を削除"><i class="fas fa-times"></i></a>';
                                echo '</div>';
                            endif;
                        endforeach;
                    endif; ?>
                    
                    <a href="<?php echo esc_url(home_url('/jobs/')); ?>" class="clear-all-filters">すべての条件をクリア</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="job-list-container">
        <?php if ($job_query->have_posts()): ?>
            <div class="job-list">
                <?php while ($job_query->have_posts()): $job_query->the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('job-card'); ?>>
                        <div class="job-card-inner">
                            <header class="job-card-header">
                                <h2 class="job-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <?php 
                                // 求人の基本情報を表示
                                $location_terms = get_the_terms(get_the_ID(), 'job_location');
                                $position_terms = get_the_terms(get_the_ID(), 'job_position');
                                $type_terms = get_the_terms(get_the_ID(), 'job_type');
                                ?>
                                
                                <div class="job-meta">
                                    <?php if ($location_terms && !is_wp_error($location_terms)): ?>
                                        <div class="job-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo esc_html($location_terms[0]->name); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($position_terms && !is_wp_error($position_terms)): ?>
                                        <div class="job-position">
                                            <i class="fas fa-briefcase"></i>
                                            <?php echo esc_html($position_terms[0]->name); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($type_terms && !is_wp_error($type_terms)): ?>
                                        <div class="job-type">
                                            <i class="fas fa-building"></i>
                                            <?php echo esc_html($type_terms[0]->name); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </header>
                            
                            <div class="job-card-content">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="job-thumbnail">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="job-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                            </div>
                            
                            <footer class="job-card-footer">
                                <?php 
                                // 求人の特徴を表示
                                $feature_terms = get_the_terms(get_the_ID(), 'job_feature');
                                if ($feature_terms && !is_wp_error($feature_terms)): ?>
                                    <div class="job-features">
                                        <?php 
                                        $count = 0;
                                        foreach ($feature_terms as $term): 
                                            if ($count < 3): // 最大3つまで表示
                                        ?>
                                            <span class="feature-tag"><?php echo esc_html($term->name); ?></span>
                                        <?php 
                                            endif;
                                            $count++;
                                        endforeach; 
                                        
                                        if (count($feature_terms) > 3): ?>
                                            <span class="feature-more">+<?php echo count($feature_terms) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="job-detail-link">詳細を見る</a>
                            </footer>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php
            // ページネーション
            echo '<div class="pagination">';
            echo paginate_links(array(
                'base' => get_pagenum_link(1) . '%_%',
                'format' => 'page/%#%/',
                'current' => max(1, get_query_var('paged')),
                'total' => $job_query->max_num_pages,
                'prev_text' => '&laquo; 前へ',
                'next_text' => '次へ &raquo;',
            ));
            echo '</div>';
            
            wp_reset_postdata();
            ?>
            
        <?php else: ?>
            <div class="no-jobs-found">
                <p>条件に一致する求人が見つかりませんでした。検索条件を変更して再度お試しください。</p>
                
                <div class="search-suggestions">
                    <h3>検索のヒント</h3>
                    <ul>
                        <li>別のエリアで検索してみる</li>
                        <li>別の職種を選択する</li>
                        <li>特徴や施設形態などの詳細条件を減らす</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- 関連リンクや人気の検索条件 -->
    <div class="related-searches">
        <h3>人気の検索条件</h3>
        <div class="popular-searches">
            <?php
            // 人気のある職種を取得
            $popular_positions = get_terms(array(
                'taxonomy' => 'job_position',
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 5,
                'hide_empty' => true,
            ));
            
            if (!empty($popular_positions) && !is_wp_error($popular_positions)) {
                foreach ($popular_positions as $position) {
                    $url = home_url('/jobs/position/' . $position->slug . '/');
                    echo '<a href="' . esc_url($url) . '" class="popular-search-link">' . esc_html($position->name) . '</a>';
                }
            }
            
            // 人気のあるエリアを取得
            $popular_locations = get_terms(array(
                'taxonomy' => 'job_location',
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 5,
                'hide_empty' => true,
            ));
            
            if (!empty($popular_locations) && !is_wp_error($popular_locations)) {
                foreach ($popular_locations as $location) {
                    $url = home_url('/jobs/location/' . $location->slug . '/');
                    echo '<a href="' . esc_url($url) . '" class="popular-search-link">' . esc_html($location->name) . '</a>';
                }
            }
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>