<?php
/**
 * 求人検索結果表示テンプレート
 * 
 * このファイルは、検索フォームからの通常の検索結果を表示します。
 * 
 */
get_header();

// 検索クエリを取得
$search_query = get_search_query();

// カスタム投稿タイプのみを対象にした検索
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$search_args = array(
    'post_type' => 'job',
    'posts_per_page' => 10,
    'paged' => $paged,
    's' => $search_query,
);

// 検索クエリを実行
$search_query = new WP_Query($search_args);
?>

<div class="job-search-results-container">
    <div class="job-search-header">
        <h1 class="search-title">
            <?php if (!empty($search_query)): ?>
                「<?php echo esc_html($search_query); ?>」の検索結果
            <?php else: ?>
                求人検索結果
            <?php endif; ?>
        </h1>
        
        <div class="job-count">
            <p>検索結果: <span class="count-number"><?php echo esc_html($search_query->found_posts); ?></span>件</p>
        </div>
        
        <!-- 検索フォームを表示 -->
        <?php get_template_part('search', 'form'); ?>
    </div>
    
    <div class="job-list-container">
        <?php if ($search_query->have_posts()): ?>
            <div class="job-list">
                <?php while ($search_query->have_posts()): $search_query->the_post(); ?>
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
                'total' => $search_query->max_num_pages,
                'prev_text' => '&laquo; 前へ',
                'next_text' => '次へ &raquo;',
            ));
            echo '</div>';
            
            wp_reset_postdata();
            ?>
            
        <?php else: ?>
            <div class="no-jobs-found">
                <?php if (!empty($search_query)): ?>
                    <p>「<?php echo esc_html($search_query); ?>」に一致する求人が見つかりませんでした。検索条件を変更して再度お試しください。</p>
                <?php else: ?>
                    <p>条件に一致する求人が見つかりませんでした。検索条件を変更して再度お試しください。</p>
                <?php endif; ?>
                
                <div class="search-suggestions">
                    <h3>検索のヒント</h3>
                    <ul>
                        <li>キーワードの綴りを確認してください</li>
                        <li>別のキーワードを試してみてください</li>
                        <li>より一般的なキーワードを使用してください</li>
                        <li>検索フォームから条件を選択して検索してみてください</li>
                    </ul>
                </div>
            </div>
            
            <!-- 人気の検索条件 -->
            <div class="popular-searches-section">
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
                        echo '<div class="popular-category">';
                        echo '<h4>人気の職種</h4>';
                        echo '<div class="popular-terms">';
                        foreach ($popular_positions as $position) {
                            $url = home_url('/jobs/position/' . $position->slug . '/');
                            echo '<a href="' . esc_url($url) . '" class="popular-term-link">' . esc_html($position->name) . '</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    // 人気のあるエリアを取得
                    $popular_locations = get_terms(array(
                        'taxonomy' => 'job_location',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 5,
                        'hide_empty' => true,
                        'parent' => 0, // トップレベルのみ
                    ));
                    
                    if (!empty($popular_locations) && !is_wp_error($popular_locations)) {
                        echo '<div class="popular-category">';
                        echo '<h4>人気のエリア</h4>';
                        echo '<div class="popular-terms">';
                        foreach ($popular_locations as $location) {
                            $url = home_url('/jobs/location/' . $location->slug . '/');
                            echo '<a href="' . esc_url($url) . '" class="popular-term-link">' . esc_html($location->name) . '</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    // 人気のある特徴を取得
                    $popular_features = get_terms(array(
                        'taxonomy' => 'job_feature',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 5,
                        'hide_empty' => true,
                    ));
                    
                    if (!empty($popular_features) && !is_wp_error($popular_features)) {
                        echo '<div class="popular-category">';
                        echo '<h4>人気の特徴</h4>';
                        echo '<div class="popular-terms">';
                        foreach ($popular_features as $feature) {
                            $url = home_url('/jobs/feature/' . $feature->slug . '/');
                            echo '<a href="' . esc_url($url) . '" class="popular-term-link">' . esc_html($feature->name) . '</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>

<?php get_footer(); ?>