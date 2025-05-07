<?php //子テーマ用関数
if (!defined('ABSPATH')) exit;

//子テーマ用のビジュアルエディタースタイルを適用
add_editor_style();

//以下に子テーマ用の関数を書く
// 会員登録画面からユーザー名を取り除く
add_filter( 'wpmem_register_form_rows', function( $rows ) {
    unset( $rows['username'] );
    return $rows;
});
// メールアドレスからユーザー名を作成する
add_filter( 'wpmem_pre_validate_form', function( $fields ) {
    $fields['username'] = $fields['user_email'];
    return $fields;
});

// WP-Members関連のエラーを抑制する関数
function suppress_wpmembers_errors() {
    // エラーハンドラー関数を定義
    function custom_error_handler($errno, $errstr, $errfile) {
        // WP-Membersプラグインのエラーを抑制
        if (strpos($errfile, 'wp-members') !== false || 
            strpos($errfile, 'email-as-username-for-wp-members') !== false) {
            // 特定のエラーメッセージのみを抑制
            if (strpos($errstr, 'Undefined array key') !== false) {
                return true; // エラーを抑制
            }
        }
        // その他のエラーは通常通り処理
        return false;
    }
    
    // エラーハンドラーを設定（警告と通知のみ）
    set_error_handler('custom_error_handler', E_WARNING | E_NOTICE);
}

// フロントエンド表示時のみ実行
if (!is_admin() && !defined('DOING_AJAX')) {
    add_action('init', 'suppress_wpmembers_errors', 1);
}


// タクソノミーの子ターム取得用Ajaxハンドラー
function get_taxonomy_children_ajax() {
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
    
    if (!$parent_id || !$taxonomy) {
        wp_send_json_error('パラメータが不正です');
    }
    
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'parent' => $parent_id,
    ));
    
    if (is_wp_error($terms) || empty($terms)) {
        wp_send_json_error('子タームが見つかりませんでした');
    }
    
    $result = array();
    foreach ($terms as $term) {
        $result[] = array(
            'term_id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_get_taxonomy_children', 'get_taxonomy_children_ajax');
add_action('wp_ajax_nopriv_get_taxonomy_children', 'get_taxonomy_children_ajax');

// タームリンク取得用Ajaxハンドラー
function get_term_link_ajax() {
    $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
    
    if (!$term_id || !$taxonomy) {
        wp_send_json_error('パラメータが不正です');
    }
    
    $term = get_term($term_id, $taxonomy);
    
    if (is_wp_error($term) || empty($term)) {
        wp_send_json_error('タームが見つかりませんでした');
    }
    
    $link = get_term_link($term);
    
    if (is_wp_error($link)) {
        wp_send_json_error('リンクの取得に失敗しました');
    }
    
    wp_send_json_success($link);
}
add_action('wp_ajax_get_term_link', 'get_term_link_ajax');
add_action('wp_ajax_nopriv_get_term_link', 'get_term_link_ajax');

// スラッグからタームリンク取得用Ajaxハンドラー
function get_term_link_by_slug_ajax() {
    $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
    
    if (!$slug || !$taxonomy) {
        wp_send_json_error('パラメータが不正です');
    }
    
    $term = get_term_by('slug', $slug, $taxonomy);
    
    if (!$term || is_wp_error($term)) {
        wp_send_json_error('タームが見つかりませんでした');
    }
    
    $link = get_term_link($term);
    
    if (is_wp_error($link)) {
        wp_send_json_error('リンクの取得に失敗しました');
    }
    
    wp_send_json_success($link);
}
add_action('wp_ajax_get_term_link_by_slug', 'get_term_link_by_slug_ajax');
add_action('wp_ajax_nopriv_get_term_link_by_slug', 'get_term_link_by_slug_ajax');


/* ------------------------------------------------------------------------------ 
	親カテゴリー・親タームを選択できないようにする
------------------------------------------------------------------------------ */
require_once(ABSPATH . '/wp-admin/includes/template.php');
class Nocheck_Category_Checklist extends Walker_Category_Checklist {

  function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
    extract($args);
    if ( empty( $taxonomy ) )
      $taxonomy = 'category';

    if ( $taxonomy == 'category' )
      $name = 'post_category';
    else
      $name = 'tax_input['.$taxonomy.']';

    $class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
    $cat_child = get_term_children( $category->term_id, $taxonomy );

    if( !empty( $cat_child ) ) {
      $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->slug . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->slug, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), true, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
    } else {
      $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->slug . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->slug, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
    }
  }

}

/**
 * 求人検索のパスURLを処理するための関数
 */

/**
 * カスタムリライトルールを追加
 */
function job_search_rewrite_rules() {
    // 特徴のみのクエリパラメータ対応
    add_rewrite_rule(
        'jobs/features/?$',
        'index.php?post_type=job&job_features_only=1',
        'top'
    );
    
    // /jobs/location/tokyo/ のようなURLルール
    add_rewrite_rule(
        'jobs/location/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]',
        'top'
    );
    
    // /jobs/position/nurse/ のようなURLルール
    add_rewrite_rule(
        'jobs/position/([^/]+)/?$',
        'index.php?post_type=job&job_position=$matches[1]',
        'top'
    );
    
    // /jobs/type/full-time/ のようなURLルール
    add_rewrite_rule(
        'jobs/type/([^/]+)/?$',
        'index.php?post_type=job&job_type=$matches[1]',
        'top'
    );
    
    // /jobs/facility/hospital/ のようなURLルール
    add_rewrite_rule(
        'jobs/facility/([^/]+)/?$',
        'index.php?post_type=job&facility_type=$matches[1]',
        'top'
    );
    
    // /jobs/feature/high-salary/ のようなURLルール
    add_rewrite_rule(
        'jobs/feature/([^/]+)/?$',
        'index.php?post_type=job&job_feature=$matches[1]',
        'top'
    );
    
    // 複合条件のURLルール
    
    // エリア + 職種
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]',
        'top'
    );
    
    // エリア + 雇用形態
    add_rewrite_rule(
        'jobs/location/([^/]+)/type/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_type=$matches[2]',
        'top'
    );
    
    // エリア + 施設形態
    add_rewrite_rule(
        'jobs/location/([^/]+)/facility/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&facility_type=$matches[2]',
        'top'
    );
    
    // エリア + 特徴
    add_rewrite_rule(
        'jobs/location/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_feature=$matches[2]',
        'top'
    );
    
    // 職種 + 雇用形態
    add_rewrite_rule(
        'jobs/position/([^/]+)/type/([^/]+)/?$',
        'index.php?post_type=job&job_position=$matches[1]&job_type=$matches[2]',
        'top'
    );
    
    // 職種 + 施設形態
    add_rewrite_rule(
        'jobs/position/([^/]+)/facility/([^/]+)/?$',
        'index.php?post_type=job&job_position=$matches[1]&facility_type=$matches[2]',
        'top'
    );
    
    // 職種 + 特徴
    add_rewrite_rule(
        'jobs/position/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_position=$matches[1]&job_feature=$matches[2]',
        'top'
    );
    
    // 三つの条件の組み合わせ
    
    // エリア + 職種 + 雇用形態
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/type/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&job_type=$matches[3]',
        'top'
    );
    
    // エリア + 職種 + 施設形態
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/facility/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&facility_type=$matches[3]',
        'top'
    );
    
    // エリア + 職種 + 特徴
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&job_feature=$matches[3]',
        'top'
    );
    
    // 追加: 四つの条件の組み合わせ
    
    // エリア + 職種 + 雇用形態 + 施設形態
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/type/([^/]+)/facility/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&job_type=$matches[3]&facility_type=$matches[4]',
        'top'
    );
    
    // エリア + 職種 + 雇用形態 + 特徴
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/type/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&job_type=$matches[3]&job_feature=$matches[4]',
        'top'
    );
    
    // エリア + 職種 + 施設形態 + 特徴
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/facility/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&facility_type=$matches[3]&job_feature=$matches[4]',
        'top'
    );
    
    // エリア + 雇用形態 + 施設形態 + 特徴
    add_rewrite_rule(
        'jobs/location/([^/]+)/type/([^/]+)/facility/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_type=$matches[2]&facility_type=$matches[3]&job_feature=$matches[4]',
        'top'
    );
    
    // 職種 + 雇用形態 + 施設形態 + 特徴
    add_rewrite_rule(
        'jobs/position/([^/]+)/type/([^/]+)/facility/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_position=$matches[1]&job_type=$matches[2]&facility_type=$matches[3]&job_feature=$matches[4]',
        'top'
    );
    
    // 追加: 五つの条件の組み合わせ（全条件）
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/type/([^/]+)/facility/([^/]+)/feature/([^/]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&job_type=$matches[3]&facility_type=$matches[4]&job_feature=$matches[5]',
        'top'
    );
    
    // ページネーション対応（例：エリア + 職種の場合）
    add_rewrite_rule(
        'jobs/location/([^/]+)/position/([^/]+)/page/([0-9]+)/?$',
        'index.php?post_type=job&job_location=$matches[1]&job_position=$matches[2]&paged=$matches[3]',
        'top'
    );
    
    // 他のページネーションパターンも必要に応じて追加
}
add_action('init', 'job_search_rewrite_rules');

/**
 * クエリ変数を追加
 */
function job_search_query_vars($vars) {
    $vars[] = 'job_location';
    $vars[] = 'job_position';
    $vars[] = 'job_type';
    $vars[] = 'facility_type';
    $vars[] = 'job_feature';
    $vars[] = 'job_features_only'; // 追加: 特徴のみの検索フラグ
    return $vars;
}
add_filter('query_vars', 'job_search_query_vars');

/**
 * URLパスとクエリパラメータを解析してフィルター条件を取得する関数
 */
function get_job_filters_from_url() {
    $filters = array();
    
    // 特徴のみのフラグをチェック
    $features_only = get_query_var('job_features_only');
    if (!empty($features_only)) {
        $filters['features_only'] = true;
    }
    
    // パス型URLからの条件取得
    $location = get_query_var('job_location');
    if (!empty($location)) {
        $filters['location'] = $location;
    }
    
    $position = get_query_var('job_position');
    if (!empty($position)) {
        $filters['position'] = $position;
    }
    
    $job_type = get_query_var('job_type');
    if (!empty($job_type)) {
        $filters['type'] = $job_type;
    }
    
    $facility_type = get_query_var('facility_type');
    if (!empty($facility_type)) {
        $filters['facility'] = $facility_type;
    }
    
    // 単一の特徴（パス型URL用）
    $job_feature = get_query_var('job_feature');
    if (!empty($job_feature)) {
        $filters['feature'] = $job_feature;
    }
    
    // クエリパラメータからの複数特徴取得
    if (isset($_GET['features']) && is_array($_GET['features'])) {
        $filters['features'] = array_map('sanitize_text_field', $_GET['features']);
    }
    
    return $filters;
}

/**
 * フィルターを一つ削除したURLを生成する関数
 */
function remove_filter_from_url($filter_to_remove) {
    $current_filters = get_job_filters_from_url();
    
    // 削除するフィルターを配列から除外
    if (isset($current_filters[$filter_to_remove])) {
        unset($current_filters[$filter_to_remove]);
    }
    
    // フィルターが残っていれば新しいURLを構築
    if (!empty($current_filters)) {
        $url_parts = array();
        $query_params = array();
        
        foreach ($current_filters as $type => $value) {
            if ($type === 'features' || $type === 'features_only') {
                // featuresはクエリパラメータとして扱う
                if ($type === 'features') {
                    $query_params['features'] = $value;
                }
            } else {
                $param_name = '';
                
                switch ($type) {
                    case 'location':
                        $param_name = 'location';
                        break;
                    case 'position':
                        $param_name = 'position';
                        break;
                    case 'type':
                        $param_name = 'type';
                        break;
                    case 'facility':
                        $param_name = 'facility';
                        break;
                    case 'feature':
                        $param_name = 'feature';
                        break;
                }
                
                if (!empty($param_name)) {
                    $url_parts[] = $param_name . '/' . $value;
                }
            }
        }
        
        // パス部分の構築
        if (!empty($url_parts)) {
            $base_url = home_url('/jobs/' . implode('/', $url_parts) . '/');
        } else if (isset($current_filters['features_only'])) {
            $base_url = home_url('/jobs/features/');
        } else {
            $base_url = home_url('/jobs/');
        }
        
        // クエリパラメータを追加
        if (!empty($query_params['features'])) {
            $feature_params = array();
            foreach ($query_params['features'] as $feature) {
                $feature_params[] = 'features[]=' . urlencode($feature);
            }
            return $base_url . '?' . implode('&', $feature_params);
        }
        
        return $base_url;
    }
    
    // フィルターが残っていなければ基本URLに戻る
    return home_url('/jobs/');
}

/**
 * タクソノミーの子ターム取得用AJAX処理
 */
function get_taxonomy_children_callback() {
    // セキュリティチェック
    if (!isset($_POST['taxonomy']) || !isset($_POST['parent_id'])) {
        wp_send_json_error('Invalid request');
    }
    
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $parent_id = intval($_POST['parent_id']);
    
    // 子タームを取得
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'parent' => $parent_id,
    ));
    
    if (is_wp_error($terms)) {
        wp_send_json_error($terms->get_error_message());
    }
    
    // 結果を整形して返送
    $result = array();
    foreach ($terms as $term) {
        $result[] = array(
            'term_id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        );
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_get_taxonomy_children', 'get_taxonomy_children_callback');
add_action('wp_ajax_nopriv_get_taxonomy_children', 'get_taxonomy_children_callback');

/**
 * タームのURLを取得するAJAX処理
 */
function get_term_link_callback() {
    // セキュリティチェック
    if (!isset($_POST['term_id']) || !isset($_POST['taxonomy'])) {
        wp_send_json_error('Invalid request');
    }
    
    $term_id = intval($_POST['term_id']);
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    
    $term = get_term($term_id, $taxonomy);
    if (is_wp_error($term)) {
        wp_send_json_error($term->get_error_message());
    }
    
    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
        wp_send_json_error($term_link->get_error_message());
    }
    
    wp_send_json_success($term_link);
}
add_action('wp_ajax_get_term_link', 'get_term_link_callback');
add_action('wp_ajax_nopriv_get_term_link', 'get_term_link_callback');

/**
 * スラッグからタームリンクを取得するAJAX処理
 */
function get_term_link_by_slug_callback() {
    // セキュリティチェック
    if (!isset($_POST['slug']) || !isset($_POST['taxonomy'])) {
        wp_send_json_error('Invalid request');
    }
    
    $slug = sanitize_text_field($_POST['slug']);
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    
    $term = get_term_by('slug', $slug, $taxonomy);
    if (!$term || is_wp_error($term)) {
        wp_send_json_error('Term not found');
    }
    
    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
        wp_send_json_error($term_link->get_error_message());
    }
    
    wp_send_json_success($term_link);
}
add_action('wp_ajax_get_term_link_by_slug', 'get_term_link_by_slug_callback');
add_action('wp_ajax_nopriv_get_term_link_by_slug', 'get_term_link_by_slug_callback');

/**
 * URLが変更されたときにリライトルールをフラッシュする
 */
function flush_rewrite_rules_on_theme_activation() {
    if (get_option('job_search_rewrite_rules_flushed') != '1') {
        flush_rewrite_rules();
        update_option('job_search_rewrite_rules_flushed', '1');
    }
}
add_action('after_switch_theme', 'flush_rewrite_rules_on_theme_activation');

// リライトルールの強制フラッシュと再登録
function force_rewrite_rules_refresh() {
    // 初回読み込み時にのみ実行
    if (!get_option('force_rewrite_refresh_done')) {
        // リライトルールを追加
        job_search_rewrite_rules();
        
        // リライトルールをフラッシュ
        flush_rewrite_rules();
        
        // 実行済みフラグを設定
        update_option('force_rewrite_refresh_done', '1');
    }
}
add_action('init', 'force_rewrite_rules_refresh', 99);

// 特徴のみのリライトルールを追加した後にフラッシュする
function flush_features_rewrite_rules() {
    if (!get_option('job_features_rewrite_flushed')) {
        flush_rewrite_rules();
        update_option('job_features_rewrite_flushed', true);
    }
}
add_action('init', 'flush_features_rewrite_rules', 999);

// リライトルールのデバッグ（必要に応じて）
function debug_rewrite_rules() {
    if (current_user_can('manage_options') && isset($_GET['debug_rewrite'])) {
        global $wp_rewrite;
        echo '<pre>';
        print_r($wp_rewrite->rules);
        echo '</pre>';
        exit;
    }
}
add_action('init', 'debug_rewrite_rules', 100);

// 以下のコードがfunctions.phpに追加されているか確認してください
function job_path_query_vars($vars) {
    $vars[] = 'job_path';
    return $vars;
}
add_filter('query_vars', 'job_path_query_vars');

// 求人ステータス変更・削除用のアクション処理
add_action('admin_post_draft_job', 'set_job_to_draft');
add_action('admin_post_publish_job', 'set_job_to_publish');
add_action('admin_post_delete_job', 'delete_job_post');

/**
 * 求人を下書きに変更
 */
function set_job_to_draft() {
    if (!isset($_GET['job_id']) || !isset($_GET['_wpnonce'])) {
        wp_die('無効なリクエストです。');
    }
    
    $job_id = intval($_GET['job_id']);
    
    // ナンス検証
    if (!wp_verify_nonce($_GET['_wpnonce'], 'draft_job_' . $job_id)) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    // 権限チェック
    $job_post = get_post($job_id);
    if (!$job_post || $job_post->post_type !== 'job' || 
        ($job_post->post_author != get_current_user_id() && !current_user_can('administrator'))) {
        wp_die('この求人を編集する権限がありません。');
    }
    
    // 下書きに変更
    wp_update_post(array(
        'ID' => $job_id,
        'post_status' => 'draft'
    ));
    
    // リダイレクト
    wp_redirect(home_url('/job-list/?status=drafted'));
    exit;
}

/**
 * 求人を公開に変更
 */
function set_job_to_publish() {
    if (!isset($_GET['job_id']) || !isset($_GET['_wpnonce'])) {
        wp_die('無効なリクエストです。');
    }
    
    $job_id = intval($_GET['job_id']);
    
    // ナンス検証
    if (!wp_verify_nonce($_GET['_wpnonce'], 'publish_job_' . $job_id)) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    // 権限チェック
    $job_post = get_post($job_id);
    if (!$job_post || $job_post->post_type !== 'job' || 
        ($job_post->post_author != get_current_user_id() && !current_user_can('administrator'))) {
        wp_die('この求人を編集する権限がありません。');
    }
    
    // 公開に変更
    wp_update_post(array(
        'ID' => $job_id,
        'post_status' => 'publish'
    ));
    
    // リダイレクト
    wp_redirect(home_url('/job-list/?status=published'));
    exit;
}

/**
 * 求人を削除
 */
function delete_job_post() {
    if (!isset($_GET['job_id']) || !isset($_GET['_wpnonce'])) {
        wp_die('無効なリクエストです。');
    }
    
    $job_id = intval($_GET['job_id']);
    
    // ナンス検証
    if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_job_' . $job_id)) {
        wp_die('セキュリティチェックに失敗しました。');
    }
    
    // 権限チェック
    $job_post = get_post($job_id);
    if (!$job_post || $job_post->post_type !== 'job' || 
        ($job_post->post_author != get_current_user_id() && !current_user_can('administrator'))) {
        wp_die('この求人を削除する権限がありません。');
    }
    
    // 削除
    wp_trash_post($job_id);
    
    // リダイレクト
    wp_redirect(home_url('/job-list/?status=deleted'));
    exit;
}



/**
 * 求人用カスタムフィールドとメタボックスの設定
 */

/**
 * 求人投稿のメタボックスを追加
 */
function add_job_meta_boxes() {
    add_meta_box(
        'job_details',
        '求人詳細情報',
        'render_job_details_meta_box',
        'job',
        'normal',
        'high'
    );
    
    add_meta_box(
        'facility_details',
        '施設情報',
        'render_facility_details_meta_box',
        'job',
        'normal',
        'high'
    );
    
    add_meta_box(
        'workplace_environment',
        '職場環境',
        'render_workplace_environment_meta_box',
        'job',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_job_meta_boxes');

/**
 * 求人詳細情報のメタボックスをレンダリング
 */
function render_job_details_meta_box($post) {
    // nonce フィールドを作成
    wp_nonce_field('save_job_details', 'job_details_nonce');
    
    // 現在のカスタムフィールド値を取得
    $salary_range = get_post_meta($post->ID, 'salary_range', true);
    $working_hours = get_post_meta($post->ID, 'working_hours', true);
    $holidays = get_post_meta($post->ID, 'holidays', true);
    $benefits = get_post_meta($post->ID, 'benefits', true);
    $requirements = get_post_meta($post->ID, 'requirements', true);
    $application_process = get_post_meta($post->ID, 'application_process', true);
    $contact_info = get_post_meta($post->ID, 'contact_info', true);
    $bonus_raise = get_post_meta($post->ID, 'bonus_raise', true);
    
    // フォームを表示
    ?>
    <style>
        .job-form-row {
            margin-bottom: 15px;
        }
        .job-form-row label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .job-form-row input[type="text"],
        .job-form-row textarea {
            width: 100%;
        }
        .required {
            color: #f00;
        }
    </style>
    
    <div class="job-form-row">
        <label for="salary_range">給与範囲 <span class="required">*</span></label>
        <input type="text" id="salary_range" name="salary_range" value="<?php echo esc_attr($salary_range); ?>" required>
        <p class="description">例: 月給180,000円〜250,000円</p>
    </div>
    
    <div class="job-form-row">
        <label for="working_hours">勤務時間 <span class="required">*</span></label>
        <input type="text" id="working_hours" name="working_hours" value="<?php echo esc_attr($working_hours); ?>" required>
        <p class="description">例: 9:00〜18:00（休憩60分）</p>
    </div>
    
    <div class="job-form-row">
        <label for="holidays">休日・休暇 <span class="required">*</span></label>
        <input type="text" id="holidays" name="holidays" value="<?php echo esc_attr($holidays); ?>" required>
        <p class="description">例: 土日祝、年末年始、有給休暇あり</p>
    </div>
    
    <div class="job-form-row">
        <label for="benefits">福利厚生</label>
        <textarea id="benefits" name="benefits" rows="4"><?php echo esc_textarea($benefits); ?></textarea>
        <p class="description">社会保険、交通費支給、各種手当など</p>
    </div>
    
    <div class="job-form-row">
        <label for="bonus_raise">昇給・賞与</label>
        <textarea id="bonus_raise" name="bonus_raise" rows="4"><?php echo esc_textarea($bonus_raise); ?></textarea>
        <p class="description">昇給制度や賞与の詳細など</p>
    </div>
    
    <div class="job-form-row">
        <label for="requirements">応募要件</label>
        <textarea id="requirements" name="requirements" rows="4"><?php echo esc_textarea($requirements); ?></textarea>
        <p class="description">必要な資格や経験など</p>
    </div>
    
    <div class="job-form-row">
        <label for="application_process">選考プロセス</label>
        <textarea id="application_process" name="application_process" rows="4"><?php echo esc_textarea($application_process); ?></textarea>
        <p class="description">書類選考、面接回数など</p>
    </div>
    
    <div class="job-form-row">
        <label for="contact_info">応募方法・連絡先 <span class="required">*</span></label>
        <textarea id="contact_info" name="contact_info" rows="4" required><?php echo esc_textarea($contact_info); ?></textarea>
        <p class="description">電話番号、メールアドレス、応募フォームURLなど</p>
    </div>
    <?php
}

/**
 * 施設情報のメタボックスをレンダリング
 */
function render_facility_details_meta_box($post) {
    // nonce フィールドを作成
    wp_nonce_field('save_facility_details', 'facility_details_nonce');
    
    // 現在のカスタムフィールド値を取得
    $facility_name = get_post_meta($post->ID, 'facility_name', true);
    $facility_address = get_post_meta($post->ID, 'facility_address', true);
    $facility_tel = get_post_meta($post->ID, 'facility_tel', true);
    $facility_hours = get_post_meta($post->ID, 'facility_hours', true);
    $facility_url = get_post_meta($post->ID, 'facility_url', true);
    $facility_company = get_post_meta($post->ID, 'facility_company', true);
    $capacity = get_post_meta($post->ID, 'capacity', true);
    $staff_composition = get_post_meta($post->ID, 'staff_composition', true);
    
    // フォームを表示
    ?>
    <div class="job-form-row">
        <label for="facility_name">施設名 <span class="required">*</span></label>
        <input type="text" id="facility_name" name="facility_name" value="<?php echo esc_attr($facility_name); ?>" required>
    </div>
    
    <div class="job-form-row">
        <label for="facility_company">運営会社名</label>
        <input type="text" id="facility_company" name="facility_company" value="<?php echo esc_attr($facility_company); ?>">
    </div>
    
    <div class="job-form-row">
        <label for="facility_address">施設住所 <span class="required">*</span></label>
        <input type="text" id="facility_address" name="facility_address" value="<?php echo esc_attr($facility_address); ?>" required>
        <p class="description">例: 〒123-4567 神奈川県横浜市○○区△△町1-2-3</p>
    </div>
    
    <div class="job-form-row">
        <label for="capacity">利用者定員数</label>
        <input type="text" id="capacity" name="capacity" value="<?php echo esc_attr($capacity); ?>">
        <p class="description">例: 60名（0〜5歳児）</p>
    </div>
    
    <div class="job-form-row">
        <label for="staff_composition">スタッフ構成</label>
        <textarea id="staff_composition" name="staff_composition" rows="4"><?php echo esc_textarea($staff_composition); ?></textarea>
        <p class="description">例: 園長1名、主任保育士2名、保育士12名、栄養士2名、調理員3名、事務員1名</p>
    </div>
    
    <div class="job-form-row">
        <label for="facility_tel">施設電話番号</label>
        <input type="text" id="facility_tel" name="facility_tel" value="<?php echo esc_attr($facility_tel); ?>">
    </div>
    
    <div class="job-form-row">
        <label for="facility_hours">施設営業時間</label>
        <input type="text" id="facility_hours" name="facility_hours" value="<?php echo esc_attr($facility_hours); ?>">
    </div>
    
    <div class="job-form-row">
        <label for="facility_url">施設WebサイトURL</label>
        <input type="url" id="facility_url" name="facility_url" value="<?php echo esc_url($facility_url); ?>">
    </div>
    <?php
}

/**
 * 職場環境のメタボックスをレンダリング
 */
function render_workplace_environment_meta_box($post) {
    // nonce フィールドを作成
    wp_nonce_field('save_workplace_environment', 'workplace_environment_nonce');
    
    // 現在のカスタムフィールド値を取得
    $daily_schedule = get_post_meta($post->ID, 'daily_schedule', true);
    $staff_voices = get_post_meta($post->ID, 'staff_voices', true);
    
    // フォームを表示
    ?>
    <div class="job-form-row">
        <label for="daily_schedule">仕事の一日の流れ</label>
        <textarea id="daily_schedule" name="daily_schedule" rows="8"><?php echo esc_textarea($daily_schedule); ?></textarea>
        <p class="description">例：9:00 出勤・朝礼、9:30 午前の業務開始、12:00 お昼休憩 など時間ごとの業務内容</p>
    </div>
    
    <div class="job-form-row">
        <label for="staff_voices">職員の声</label>
        <textarea id="staff_voices" name="staff_voices" rows="8"><?php echo esc_textarea($staff_voices); ?></textarea>
        <p class="description">実際に働いているスタッフの声を入力（職種、勤続年数、コメントなど）</p>
    </div>
    <?php
}

/**
 * カスタムフィールドのデータを保存
 */
function save_job_meta_data($post_id) {
    // 自動保存の場合は何もしない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // 求人詳細情報の保存
    if (isset($_POST['job_details_nonce']) && wp_verify_nonce($_POST['job_details_nonce'], 'save_job_details')) {
        if (isset($_POST['salary_range'])) {
            update_post_meta($post_id, 'salary_range', sanitize_text_field($_POST['salary_range']));
        }
        
        if (isset($_POST['working_hours'])) {
            update_post_meta($post_id, 'working_hours', sanitize_text_field($_POST['working_hours']));
        }
        
        if (isset($_POST['holidays'])) {
            update_post_meta($post_id, 'holidays', sanitize_text_field($_POST['holidays']));
        }
        
        if (isset($_POST['benefits'])) {
            update_post_meta($post_id, 'benefits', wp_kses_post($_POST['benefits']));
        }
        
        if (isset($_POST['bonus_raise'])) {
            update_post_meta($post_id, 'bonus_raise', wp_kses_post($_POST['bonus_raise']));
        }
        
        if (isset($_POST['requirements'])) {
            update_post_meta($post_id, 'requirements', wp_kses_post($_POST['requirements']));
        }
        
        if (isset($_POST['application_process'])) {
            update_post_meta($post_id, 'application_process', wp_kses_post($_POST['application_process']));
        }
        
        if (isset($_POST['contact_info'])) {
            update_post_meta($post_id, 'contact_info', wp_kses_post($_POST['contact_info']));
        }
    }
    
    // 施設情報の保存
    if (isset($_POST['facility_details_nonce']) && wp_verify_nonce($_POST['facility_details_nonce'], 'save_facility_details')) {
        if (isset($_POST['facility_name'])) {
            update_post_meta($post_id, 'facility_name', sanitize_text_field($_POST['facility_name']));
        }
        
        if (isset($_POST['facility_company'])) {
            update_post_meta($post_id, 'facility_company', sanitize_text_field($_POST['facility_company']));
        }
        
        if (isset($_POST['facility_address'])) {
            update_post_meta($post_id, 'facility_address', sanitize_text_field($_POST['facility_address']));
        }
        
        if (isset($_POST['capacity'])) {
            update_post_meta($post_id, 'capacity', sanitize_text_field($_POST['capacity']));
        }
        
        if (isset($_POST['staff_composition'])) {
            update_post_meta($post_id, 'staff_composition', wp_kses_post($_POST['staff_composition']));
        }
        
        if (isset($_POST['facility_tel'])) {
            update_post_meta($post_id, 'facility_tel', sanitize_text_field($_POST['facility_tel']));
        }
        
        if (isset($_POST['facility_hours'])) {
            update_post_meta($post_id, 'facility_hours', sanitize_text_field($_POST['facility_hours']));
        }
        
        if (isset($_POST['facility_url'])) {
            update_post_meta($post_id, 'facility_url', esc_url_raw($_POST['facility_url']));
        }
    }
    
    // 職場環境の保存
    if (isset($_POST['workplace_environment_nonce']) && wp_verify_nonce($_POST['workplace_environment_nonce'], 'save_workplace_environment')) {
        if (isset($_POST['daily_schedule'])) {
            update_post_meta($post_id, 'daily_schedule', wp_kses_post($_POST['daily_schedule']));
        }
        
        if (isset($_POST['staff_voices'])) {
            update_post_meta($post_id, 'staff_voices', wp_kses_post($_POST['staff_voices']));
        }
    }
}
add_action('save_post_job', 'save_job_meta_data');

// 追加のカスタムフィールドを設定
function add_additional_job_fields($post_id) {
    // 本文タイトル
    if (isset($_POST['job_content_title'])) {
        update_post_meta($post_id, 'job_content_title', sanitize_text_field($_POST['job_content_title']));
    }
    
    // GoogleMap埋め込みコード
    if (isset($_POST['facility_map'])) {
        update_post_meta($post_id, 'facility_map', wp_kses($_POST['facility_map'], array(
            'iframe' => array(
                'src' => array(),
                'width' => array(),
                'height' => array(),
                'frameborder' => array(),
                'style' => array(),
                'allowfullscreen' => array()
            )
        )));
    }
    
    // 仕事の一日の流れ（配列形式）
    if (isset($_POST['daily_schedule_time']) && is_array($_POST['daily_schedule_time'])) {
        $schedule_items = array();
        $count = count($_POST['daily_schedule_time']);
        
        for ($i = 0; $i < $count; $i++) {
            if (!empty($_POST['daily_schedule_time'][$i])) {
                $schedule_items[] = array(
                    'time' => sanitize_text_field($_POST['daily_schedule_time'][$i]),
                    'title' => sanitize_text_field($_POST['daily_schedule_title'][$i]),
                    'description' => wp_kses_post($_POST['daily_schedule_description'][$i])
                );
            }
        }
        
        update_post_meta($post_id, 'daily_schedule_items', $schedule_items);
    }
    
    // 職員の声（配列形式）
    if (isset($_POST['staff_voice_role']) && is_array($_POST['staff_voice_role'])) {
        $voice_items = array();
        $count = count($_POST['staff_voice_role']);
        
        for ($i = 0; $i < $count; $i++) {
            if (!empty($_POST['staff_voice_role'][$i])) {
                $voice_items[] = array(
                    'image_id' => intval($_POST['staff_voice_image'][$i]),
                    'role' => sanitize_text_field($_POST['staff_voice_role'][$i]),
                    'years' => sanitize_text_field($_POST['staff_voice_years'][$i]),
                    'comment' => wp_kses_post($_POST['staff_voice_comment'][$i])
                );
            }
        }
        
        update_post_meta($post_id, 'staff_voice_items', $voice_items);
    }
}

// 求人投稿保存時にカスタムフィールドを処理
add_action('save_post_job', function($post_id) {
    // 自動保存の場合は何もしない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // 追加フィールドを保存
    add_additional_job_fields($post_id);
}, 15);


// JavaScriptとCSSを登録・読み込むための関数
function register_job_search_scripts() {
    // URLパラメータを追加して、キャッシュを防止
    $version = '1.0.0';
    
    // スタイルシートの登録（必要に応じて）
    wp_register_style('job-search-style', get_stylesheet_directory_uri() . '/css/job-search.css', array(), $version);
    wp_enqueue_style('job-search-style');
    
    // JavaScriptの登録
    wp_register_script('job-search', get_stylesheet_directory_uri() . '/js/job-search.js', array('jquery'), $version, true);
    
    // JavaScriptにパラメータを渡す
    wp_localize_script('job-search', 'job_search_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'site_url' => home_url(),
        'nonce' => wp_create_nonce('job_search_nonce')
    ));
    
    // JavaScriptを読み込む
    wp_enqueue_script('job-search');
}
add_action('wp_enqueue_scripts', 'register_job_search_scripts');