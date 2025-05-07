<?php
/**
 * Template Name: 求人編集ページ
 * 
 * 自分が投稿した求人を編集するためのページテンプレート
 */

get_header();

// ログインチェック
if (!is_user_logged_in()) {
    // 非ログインの場合はログインページにリダイレクト
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// メディアアップローダーのスクリプトを読み込む
wp_enqueue_media();

// 現在のユーザー情報を取得
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;

// ユーザーが加盟教室（agency）の権限を持っているかチェック
$is_agency = in_array('agency', $current_user->roles);
if (!$is_agency && !current_user_can('administrator')) {
    // 権限がない場合はエラーメッセージ表示
    echo '<div class="error-message">この機能を利用する権限がありません。</div>';
    get_footer();
    exit;
}

// job_idパラメータから編集対象の投稿IDを取得
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$job_post = get_post($job_id);

// 投稿が存在しない、または自分の投稿でない（管理者は除く）場合はエラー
if (!$job_post || $job_post->post_type !== 'job' || 
    ($job_post->post_author != $current_user_id && !current_user_can('administrator'))) {
    echo '<div class="error-message">編集する求人情報が見つからないか、編集する権限がありません。</div>';
    get_footer();
    exit;
}

// フォームが送信された場合の処理
if (isset($_POST['update_job']) && isset($_POST['job_nonce']) && 
    wp_verify_nonce($_POST['job_nonce'], 'update_job_' . $job_id)) {
    
    // 基本情報を更新
    $job_data = array(
        'ID' => $job_id,
        'post_title' => sanitize_text_field($_POST['job_title']),
        'post_content' => wp_kses_post($_POST['job_content']),
        'post_status' => 'publish'
    );
    
    // 投稿を更新
    $update_result = wp_update_post($job_data);
    
    if (!is_wp_error($update_result)) {
        // タクソノミーの更新（IDからスラッグに変更）
        if (isset($_POST['job_location'])) {
            wp_set_object_terms($job_id, $_POST['job_location'], 'job_location');
        } else {
            wp_set_object_terms($job_id, array(), 'job_location');
        }
        
        if (isset($_POST['job_position'])) {
            wp_set_object_terms($job_id, $_POST['job_position'], 'job_position');
        } else {
            wp_set_object_terms($job_id, array(), 'job_position');
        }
        
        if (isset($_POST['job_type'])) {
            wp_set_object_terms($job_id, $_POST['job_type'], 'job_type');
        } else {
            wp_set_object_terms($job_id, array(), 'job_type');
        }
        
        if (isset($_POST['facility_type'])) {
            wp_set_object_terms($job_id, $_POST['facility_type'], 'facility_type');
        } else {
            wp_set_object_terms($job_id, array(), 'facility_type');
        }
        
        if (isset($_POST['job_feature'])) {
            wp_set_object_terms($job_id, $_POST['job_feature'], 'job_feature');
        } else {
            wp_set_object_terms($job_id, array(), 'job_feature');
        }
        
        // カスタムフィールドの更新
        update_post_meta($job_id, 'job_content_title', sanitize_text_field($_POST['job_content_title']));
        update_post_meta($job_id, 'salary_range', sanitize_text_field($_POST['salary_range']));
        update_post_meta($job_id, 'working_hours', sanitize_text_field($_POST['working_hours']));
        update_post_meta($job_id, 'holidays', sanitize_text_field($_POST['holidays']));
        update_post_meta($job_id, 'benefits', wp_kses_post($_POST['benefits']));
        update_post_meta($job_id, 'requirements', wp_kses_post($_POST['requirements']));
        update_post_meta($job_id, 'application_process', wp_kses_post($_POST['application_process']));
        update_post_meta($job_id, 'contact_info', wp_kses_post($_POST['contact_info']));
        
        // 施設情報の更新
        update_post_meta($job_id, 'facility_name', sanitize_text_field($_POST['facility_name']));
        update_post_meta($job_id, 'facility_address', sanitize_text_field($_POST['facility_address']));
        update_post_meta($job_id, 'facility_tel', sanitize_text_field($_POST['facility_tel']));
        update_post_meta($job_id, 'facility_hours', sanitize_text_field($_POST['facility_hours']));
        update_post_meta($job_id, 'facility_url', esc_url_raw($_POST['facility_url']));
        update_post_meta($job_id, 'facility_company', sanitize_text_field($_POST['facility_company']));
        update_post_meta($job_id, 'facility_map', wp_kses($_POST['facility_map'], array(
            'iframe' => array(
                'src' => array(),
                'width' => array(),
                'height' => array(),
                'frameborder' => array(),
                'style' => array(),
                'allowfullscreen' => array()
            )
        )));
        
        // 追加フィールドの更新
        update_post_meta($job_id, 'bonus_raise', wp_kses_post($_POST['bonus_raise']));
        update_post_meta($job_id, 'capacity', sanitize_text_field($_POST['capacity']));
        update_post_meta($job_id, 'staff_composition', wp_kses_post($_POST['staff_composition']));
        
        // サムネイル画像の処理
        if (isset($_POST['thumbnail_id']) && intval($_POST['thumbnail_id']) > 0) {
            set_post_thumbnail($job_id, intval($_POST['thumbnail_id']));
        } else {
            // サムネイル削除の場合
            delete_post_thumbnail($job_id);
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
            
            update_post_meta($job_id, 'daily_schedule_items', $schedule_items);
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
            
            update_post_meta($job_id, 'staff_voice_items', $voice_items);
        }
        
        // 成功メッセージ表示と求人詳細ページへのリンク
        $success = true;
    } else {
        // エラーメッセージ表示
        $error = $update_result->get_error_message();
        if (empty($error)) {
            $error = '不明なエラーが発生しました。再度お試しください。';
        }
    }
}

// 現在の投稿データを取得
$job_title = $job_post->post_title;
$job_content = $job_post->post_content;

// タクソノミー情報を取得（IDからスラッグに変更）
$current_job_location = wp_get_object_terms($job_id, 'job_location', array('fields' => 'slugs'));
$current_job_position = wp_get_object_terms($job_id, 'job_position', array('fields' => 'slugs'));
$current_job_type = wp_get_object_terms($job_id, 'job_type', array('fields' => 'slugs'));
$current_facility_type = wp_get_object_terms($job_id, 'facility_type', array('fields' => 'slugs'));
$current_job_feature = wp_get_object_terms($job_id, 'job_feature', array('fields' => 'slugs'));

// カスタムフィールドデータを取得
$job_content_title = get_post_meta($job_id, 'job_content_title', true);
$salary_range = get_post_meta($job_id, 'salary_range', true);
$working_hours = get_post_meta($job_id, 'working_hours', true);
$holidays = get_post_meta($job_id, 'holidays', true);
$benefits = get_post_meta($job_id, 'benefits', true);
$requirements = get_post_meta($job_id, 'requirements', true);
$application_process = get_post_meta($job_id, 'application_process', true);
$contact_info = get_post_meta($job_id, 'contact_info', true);

// 追加フィールドデータを取得
$bonus_raise = get_post_meta($job_id, 'bonus_raise', true);
$capacity = get_post_meta($job_id, 'capacity', true);
$staff_composition = get_post_meta($job_id, 'staff_composition', true);

// 一日の流れと職員の声のデータを取得
$daily_schedule_items = get_post_meta($job_id, 'daily_schedule_items', true);
$staff_voice_items = get_post_meta($job_id, 'staff_voice_items', true);

// 施設情報を取得
$facility_name = get_post_meta($job_id, 'facility_name', true);
$facility_address = get_post_meta($job_id, 'facility_address', true);
$facility_tel = get_post_meta($job_id, 'facility_tel', true);
$facility_hours = get_post_meta($job_id, 'facility_hours', true);
$facility_url = get_post_meta($job_id, 'facility_url', true);
$facility_company = get_post_meta($job_id, 'facility_company', true);
$facility_map = get_post_meta($job_id, 'facility_map', true);

// サムネイル画像ID
$thumbnail_id = get_post_thumbnail_id($job_id);
?>

<div class="edit-job-container">
    <h1 class="page-title">求人情報の編集</h1>
    
    <?php if (isset($success) && $success): ?>
    <div class="success-message">
        <p>求人情報を更新しました。</p>
        <p><a href="<?php echo get_permalink($job_id); ?>" class="btn-view">更新した求人を確認する</a></p>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error) && !empty($error)): ?>
    <div class="error-message">
        <p>エラーが発生しました: <?php echo $error; ?></p>
    </div>
    <?php endif; ?>
    
    <form method="post" class="edit-job-form" enctype="multipart/form-data">
        <?php wp_nonce_field('update_job_' . $job_id, 'job_nonce'); ?>
        
        <div class="form-section">
            <h2 class="section-title">基本情報</h2>
            
            <div class="form-row">
                <label for="job_title">求人タイトル <span class="required">*</span></label>
                <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr($job_title); ?>" required>
            </div>
            
            <div class="form-row">
                <label>サムネイル画像 <span class="required">*</span></label>
                <div class="thumbnail-preview">
                    <?php if ($thumbnail_id): ?>
                    <img src="<?php echo wp_get_attachment_url($thumbnail_id); ?>" alt="サムネイル画像">
                    <?php endif; ?>
                </div>
                
                <input type="hidden" name="thumbnail_id" id="thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>">
                <button type="button" class="btn-media-upload" id="upload_thumbnail">画像を選択</button>
                <?php if ($thumbnail_id): ?>
                <button type="button" class="btn-media-remove" id="remove_thumbnail">画像を削除</button>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label for="job_content_title">本文タイトル <span class="required">*</span></label>
                <input type="text" id="job_content_title" name="job_content_title" value="<?php echo esc_attr($job_content_title); ?>" required>
            </div>
            
            <div class="form-row">
                <label for="job_content">本文詳細 <span class="required">*</span></label>
                <?php 
                wp_editor($job_content, 'job_content', array(
                    'media_buttons' => true,
                    'textarea_name' => 'job_content',
                    'textarea_rows' => 10
                )); 
                ?>
            </div>
            
            <div class="form-row">
                <label for="salary_range">給与範囲 <span class="required">*</span></label>
                <input type="text" id="salary_range" name="salary_range" value="<?php echo esc_attr($salary_range); ?>" required>
                <span class="form-hint">例: 月給180,000円〜250,000円</span>
            </div>
        </div>
        
        <div class="form-section">
            <h2 class="section-title">募集内容</h2>
            
            <div class="form-row">
                <label>勤務地域 <span class="required">*</span></label>
                <div class="taxonomy-select">
                    <?php 
                    // 親タームを取得してループ
                    $parent_terms = get_terms(array(
                        'taxonomy' => 'job_location',
                        'hide_empty' => false,
                        'parent' => 0,
                    ));
                    
                    if ($parent_terms && !is_wp_error($parent_terms)) {
                        foreach ($parent_terms as $parent_term) {
                            $parent_checked = in_array($parent_term->slug, $current_job_location) ? 'checked' : '';
                            
                            echo '<div class="parent-term">';
                            echo '<label class="checkbox-label parent-label">';
                            echo '<input type="checkbox" name="job_location[]" value="' . $parent_term->slug . '" ' . $parent_checked . ' class="parent-checkbox" data-term-id="' . $parent_term->term_id . '">';
                            echo $parent_term->name;
                            echo '</label>';
                            
                            // 子タームがあるか確認
                            $child_terms = get_terms(array(
                                'taxonomy' => 'job_location',
                                'hide_empty' => false,
                                'parent' => $parent_term->term_id,
                            ));
                            
                            if ($child_terms && !is_wp_error($child_terms)) {
                                $has_child_checked = false;
                                
                                echo '<div class="child-terms" id="child-terms-' . $parent_term->term_id . '" style="margin-left: 20px;">';
                                
                                foreach ($child_terms as $child_term) {
                                    $child_checked = in_array($child_term->slug, $current_job_location) ? 'checked' : '';
                                    if ($child_checked) {
                                        $has_child_checked = true;
                                    }
                                    
                                    echo '<div class="child-term">';
                                    echo '<label class="checkbox-label child-label">';
                                    echo '<input type="checkbox" name="job_location[]" value="' . $child_term->slug . '" ' . $child_checked . ' class="child-checkbox" data-term-id="' . $child_term->term_id . '">';
                                    echo $child_term->name;
                                    echo '</label>';
                                    
                                    // 孫タームがあるか確認
                                    $grandchild_terms = get_terms(array(
                                        'taxonomy' => 'job_location',
                                        'hide_empty' => false,
                                        'parent' => $child_term->term_id,
                                    ));
                                    
                                    if ($grandchild_terms && !is_wp_error($grandchild_terms)) {
                                        $has_grandchild_checked = false;
                                        
                                        echo '<div class="grandchild-terms" id="child-terms-' . $child_term->term_id . '" style="margin-left: 20px;">';
                                        
                                        foreach ($grandchild_terms as $grandchild_term) {
                                            $grandchild_checked = in_array($grandchild_term->slug, $current_job_location) ? 'checked' : '';
                                            if ($grandchild_checked) {
                                                $has_grandchild_checked = true;
                                            }
                                            
                                            echo '<label class="checkbox-label grandchild-label">';
                                            echo '<input type="checkbox" name="job_location[]" value="' . $grandchild_term->slug . '" ' . $grandchild_checked . '>';
                                            echo $grandchild_term->name;
                                            echo '</label>';
                                        }
                                        
                                        echo '</div>';
                                        
                                        // 孫のチェック状態に基づいて表示・非表示を設定
                                        if (!$has_grandchild_checked) {
                                            echo '<script>jQuery(document).ready(function($) { $("#child-terms-' . $child_term->term_id . '").hide(); });</script>';
                                        }
                                    }
                                    
                                    echo '</div>';
                                }
                                
                                echo '</div>';
                                
                                // 子のチェック状態に基づいて表示・非表示を設定
                                if (!$has_child_checked && !$parent_checked) {
                                    echo '<script>jQuery(document).ready(function($) { $("#child-terms-' . $parent_term->term_id . '").hide(); });</script>';
                                }
                            }
                            
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-row">
                <label>職種 <span class="required">*</span></label>
                <div class="taxonomy-select">
                    <?php 
                    $job_position_terms = get_terms(array(
                        'taxonomy' => 'job_position',
                        'hide_empty' => false,
                    ));
                    
                    if ($job_position_terms && !is_wp_error($job_position_terms)) {
                        foreach ($job_position_terms as $term) {
                            $checked = in_array($term->slug, $current_job_position) ? 'checked' : '';
                            echo '<label class="checkbox-label">';
                            echo '<input type="checkbox" name="job_position[]" value="' . $term->slug . '" ' . $checked . '>';
                            echo $term->name;
                            echo '</label>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-row">
                <label>雇用形態 <span class="required">*</span></label>
                <div class="taxonomy-select">
                    <?php 
                    $job_type_terms = get_terms(array(
                        'taxonomy' => 'job_type',
                        'hide_empty' => false,
                    ));
                    
                    if ($job_type_terms && !is_wp_error($job_type_terms)) {
                        foreach ($job_type_terms as $term) {
                            $checked = in_array($term->slug, $current_job_type) ? 'checked' : '';
                            echo '<label class="checkbox-label">';
                            echo '<input type="checkbox" name="job_type[]" value="' . $term->slug . '" ' . $checked . '>';
                            echo $term->name;
                            echo '</label>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-row">
                <label for="requirements">応募要件 <span class="required">*</span></label>
                <textarea id="requirements" name="requirements" rows="5" required><?php echo esc_textarea($requirements); ?></textarea>
                <span class="form-hint">必要な資格や経験など</span>
            </div>
            
            <div class="form-row">
                <label for="working_hours">勤務時間 <span class="required">*</span></label>
                <input type="text" id="working_hours" name="working_hours" value="<?php echo esc_attr($working_hours); ?>" required>
                <span class="form-hint">例: 9:00〜18:00（休憩60分）</span>
            </div>
            
            <div class="form-row">
                <label for="holidays">休日・休暇 <span class="required">*</span></label>
                <input type="text" id="holidays" name="holidays" value="<?php echo esc_attr($holidays); ?>" required>
                <span class="form-hint">例: 土日祝、年末年始、有給休暇あり</span>
            </div>
            
            <div class="form-row">
                <label for="benefits">福利厚生 <span class="required">*</span></label>
                <textarea id="benefits" name="benefits" rows="5" required><?php echo esc_textarea($benefits); ?></textarea>
                <span class="form-hint">社会保険、交通費支給、各種手当など</span>
            </div>
            
            <div class="form-row">
                <label for="bonus_raise">昇給・賞与</label>
                <textarea id="bonus_raise" name="bonus_raise" rows="5"><?php echo esc_textarea($bonus_raise); ?></textarea>
                <span class="form-hint">昇給制度や賞与の詳細など</span>
            </div>
            
            <div class="form-row">
                <label for="application_process">選考プロセス</label>
                <textarea id="application_process" name="application_process" rows="5"><?php echo esc_textarea($application_process); ?></textarea>
                <span class="form-hint">書類選考、面接回数など</span>
            </div>
            
            <div class="form-row">
                <label for="contact_info">応募方法・連絡先 <span class="required">*</span></label>
                <textarea id="contact_info" name="contact_info" rows="5" required><?php echo esc_textarea($contact_info); ?></textarea>
                <span class="form-hint">電話番号、メールアドレス、応募フォームURLなど</span>
            </div>
        </div>
        
        <div class="form-section">
            <h2 class="section-title">求人の特徴</h2>
            
            <div class="form-row">
                <label>特徴タグ <span class="required">*</span></label>
                <div class="taxonomy-select">
                    <?php 
                    $job_feature_terms = get_terms(array(
                        'taxonomy' => 'job_feature',
                        'hide_empty' => false,
                    ));
                    
                    if ($job_feature_terms && !is_wp_error($job_feature_terms)) {
                        foreach ($job_feature_terms as $term) {
                            $checked = in_array($term->slug, $current_job_feature) ? 'checked' : '';
                            echo '<label class="checkbox-label feature-label">';
                            echo '<input type="checkbox" name="job_feature[]" value="' . $term->slug . '" ' . $checked . '>';
                            echo $term->name;
                            echo '</label>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h2 class="section-title">職場の環境</h2>
            
            <div class="form-row">
                <label>仕事の一日の流れ</label>
                <div id="daily-schedule-container">
                    <?php 
                    if (is_array($daily_schedule_items) && !empty($daily_schedule_items)) {
                        foreach ($daily_schedule_items as $index => $item) {
                            ?>
                            <div class="daily-schedule-item">
                                <div class="schedule-time">
                                    <label>時間</label>
                                    <input type="text" name="daily_schedule_time[]" value="<?php echo esc_attr($item['time']); ?>" placeholder="9:00">
                                </div>
                                <div class="schedule-title">
                                    <label>タイトル</label>
                                    <input type="text" name="daily_schedule_title[]" value="<?php echo esc_attr($item['title']); ?>" placeholder="出社・朝礼">
                                </div>
                                <div class="schedule-description">
                                    <label>詳細</label>
                                    <textarea name="daily_schedule_description[]" rows="3" placeholder="出社して業務の準備をします。朝礼で1日の予定を確認します。"><?php echo esc_textarea($item['description']); ?></textarea>
                                </div>
                                <?php if ($index > 0): ?>
                                <button type="button" class="remove-schedule-item">削除</button>
                                <?php else: ?>
                                <button type="button" class="remove-schedule-item" style="display:none;">削除</button>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    } else {
                        // 一つも項目がない場合は空のテンプレートを表示
                        ?>
                        <div class="daily-schedule-item">
                            <div class="schedule-time">
                                <label>時間</label>
                                <input type="text" name="daily_schedule_time[]" placeholder="9:00">
                            </div>
                            <div class="schedule-title">
                                <label>タイトル</label>
                                <input type="text" name="daily_schedule_title[]" placeholder="出社・朝礼">
                            </div>
                            <div class="schedule-description">
                                <label>詳細</label>
                                <textarea name="daily_schedule_description[]" rows="3" placeholder="出社して業務の準備をします。朝礼で1日の予定を確認します。"></textarea>
                            </div>
                            <button type="button" class="remove-schedule-item" style="display:none;">削除</button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <button type="button" id="add-schedule-item" class="btn-add-item">時間枠を追加</button>
            </div>
            
            <div class="form-row">
                <label>職員の声</label>
                <div id="staff-voice-container">
                    <?php 
                    if (is_array($staff_voice_items) && !empty($staff_voice_items)) {
                        foreach ($staff_voice_items as $index => $item) {
                            $image_url = '';
                            if (!empty($item['image_id'])) {
                                $image_url = wp_get_attachment_url($item['image_id']);
                            }
                            ?>
                            <div class="staff-voice-item">
                                <div class="voice-image">
                                    <label>サムネイル</label>
                                    <div class="voice-image-preview">
                                        <?php if (!empty($image_url)): ?>
                                        <img src="<?php echo esc_url($image_url); ?>" alt="スタッフ画像">
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="staff_voice_image[]" value="<?php echo esc_attr($item['image_id']); ?>">
                                    <button type="button" class="upload-voice-image">画像を選択</button>
                                    <?php if (!empty($image_url)): ?>
                                    <button type="button" class="remove-voice-image">削除</button>
                                    <?php else: ?>
                                    <button type="button" class="remove-voice-image" style="display:none;">削除</button>
                                    <?php endif; ?>
                                </div>
                                <div class="voice-role">
                                    <label>職種</label>
                                    <input type="text" name="staff_voice_role[]" value="<?php echo esc_attr($item['role']); ?>" placeholder="保育士">
                                </div>
                                <div class="voice-years">
                                    <label>勤続年数</label>
                                    <input type="text" name="staff_voice_years[]" value="<?php echo esc_attr($item['years']); ?>" placeholder="3年目">
                                </div>
                                <div class="voice-comment">
                                    <label>コメント</label>
                                    <textarea name="staff_voice_comment[]" rows="4" placeholder="職場の雰囲気や働きやすさについてのコメント"><?php echo esc_textarea($item['comment']); ?></textarea>
                                </div>
                                <?php if ($index > 0): ?>
                                <button type="button" class="remove-voice-item">削除</button>
                                <?php else: ?>
                                <button type="button" class="remove-voice-item" style="display:none;">削除</button>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    } else {
                        // 一つも項目がない場合は空のテンプレートを表示
                        ?>
                        <div class="staff-voice-item">
                            <div class="voice-image">
                                <label>サムネイル</label>
                                <div class="voice-image-preview"></div>
                                <input type="hidden" name="staff_voice_image[]" value="">
                                <button type="button" class="upload-voice-image">画像を選択</button>
                                <button type="button" class="remove-voice-image" style="display:none;">削除</button>
                            </div>
                            <div class="voice-role">
                                <label>職種</label>
                                <input type="text" name="staff_voice_role[]" placeholder="保育士">
                            </div>
                            <div class="voice-years">
                                <label>勤続年数</label>
                                <input type="text" name="staff_voice_years[]" placeholder="3年目">
                            </div>
                            <div class="voice-comment">
                                <label>コメント</label>
                                <textarea name="staff_voice_comment[]" rows="4" placeholder="職場の雰囲気や働きやすさについてのコメント"></textarea>
                            </div>
                            <button type="button" class="remove-voice-item" style="display:none;">削除</button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <button type="button" id="add-voice-item" class="btn-add-item">職員の声を追加</button>
            </div>
        </div>
        
        <div class="form-section">
            <h2 class="section-title">事業所の情報</h2>
            
            <div class="form-row">
                <label for="facility_name">施設名 <span class="required">*</span></label>
                <input type="text" id="facility_name" name="facility_name" value="<?php echo esc_attr($facility_name); ?>" required>
            </div>
            
            <div class="form-row">
                <label for="facility_company">運営会社名 <span class="required">*</span></label>
                <input type="text" id="facility_company" name="facility_company" value="<?php echo esc_attr($facility_company); ?>" required>
            </div>
            
            <div class="form-row">
                <label for="facility_address">施設住所 <span class="required">*</span></label>
                <input type="text" id="facility_address" name="facility_address" value="<?php echo esc_attr($facility_address); ?>" required>
                <span class="form-hint">例: 〒123-4567 神奈川県横浜市○○区△△町1-2-3</span>
            </div>
            
            <div class="form-row">
                <label for="facility_map">GoogleMap <span class="required">*</span></label>
                <textarea id="facility_map" name="facility_map" rows="5" required placeholder="GoogleMapの埋め込みコードを貼り付けてください"><?php echo esc_textarea($facility_map); ?></textarea>
                <span class="form-hint">GoogleMapの「共有」から「地図を埋め込む」を選択して、埋め込みコードをコピーして貼り付けてください。</span>
            </div>
            
            <div class="form-row">
                <label>施設形態 <span class="required">*</span></label>
                <div class="taxonomy-select">
                    <?php 
                    $facility_type_terms = get_terms(array(
                        'taxonomy' => 'facility_type',
                        'hide_empty' => false,
                    ));
                    
                    if ($facility_type_terms && !is_wp_error($facility_type_terms)) {
                        foreach ($facility_type_terms as $term) {
                            $checked = in_array($term->slug, $current_facility_type) ? 'checked' : '';
                            echo '<label class="checkbox-label">';
                            echo '<input type="checkbox" name="facility_type[]" value="' . $term->slug . '" ' . $checked . '>';
                            echo $term->name;
                            echo '</label>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-row">
                <label for="capacity">利用者定員数</label>
                <input type="text" id="capacity" name="capacity" value="<?php echo esc_attr($capacity); ?>">
                <span class="form-hint">例: 60名（0〜5歳児）</span>
            </div>
            
            <div class="form-row">
                <label for="staff_composition">スタッフ構成</label>
                <textarea id="staff_composition" name="staff_composition" rows="4"><?php echo esc_textarea($staff_composition); ?></textarea>
                <span class="form-hint">例: 園長1名、主任保育士2名、保育士12名、栄養士2名、調理員3名、事務員1名</span>
            </div>
            
            <div class="form-row">
                <label for="facility_tel">施設電話番号</label>
                <input type="text" id="facility_tel" name="facility_tel" value="<?php echo esc_attr($facility_tel); ?>">
            </div>
            
            <div class="form-row">
                <label for="facility_hours">施設営業時間</label>
                <input type="text" id="facility_hours" name="facility_hours" value="<?php echo esc_attr($facility_hours); ?>">
            </div>
            
            <div class="form-row">
                <label for="facility_url">施設WebサイトURL</label>
                <input type="url" id="facility_url" name="facility_url" value="<?php echo esc_url($facility_url); ?>">
            </div>
        </div>
        
        <div class="form-actions">
            <input type="submit" name="update_job" value="求人情報を更新する" class="btn-submit">
            <a href="<?php echo get_permalink($job_id); ?>" class="btn-cancel">キャンセル</a>
        </div>
    </form>
	<!-- JavaScript -->
    <script>
    jQuery(document).ready(function($) {
        // メディアアップローダー（サムネイル用）
        $('#upload_thumbnail').click(function(e) {
            e.preventDefault();
            
            var custom_uploader = wp.media({
                title: '求人サムネイル画像を選択',
                button: {
                    text: '画像を選択'
                },
                multiple: false
            });
            
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $('.thumbnail-preview').html('<img src="' + attachment.url + '" alt="サムネイル画像">');
                $('#thumbnail_id').val(attachment.id);
                
                // 削除ボタンを表示
                if ($('#remove_thumbnail').length === 0) {
                    $('.btn-media-upload').after('<button type="button" class="btn-media-remove" id="remove_thumbnail">画像を削除</button>');
                }
            });
            
            custom_uploader.open();
        });
        
        // 画像削除ボタン（サムネイル用）
        $(document).on('click', '#remove_thumbnail', function(e) {
            e.preventDefault();
            $('.thumbnail-preview').empty();
            $('#thumbnail_id').val('');
            $(this).remove();
        });
        
        // 親タームチェックボックスの処理
        $('.parent-checkbox').on('change', function() {
            var termId = $(this).data('term-id');
            var childContainer = $('#child-terms-' + termId);
            
            if ($(this).is(':checked')) {
                childContainer.show();
            } else {
                childContainer.hide();
                // 親のチェックが外れたら子のチェックも外す
                childContainer.find('input[type="checkbox"]').prop('checked', false);
            }
        });
        
        // 子タームチェックボックスの処理
        $('.child-checkbox').on('change', function() {
            var termId = $(this).data('term-id');
            var grandchildContainer = $('#child-terms-' + termId);
            
            if ($(this).is(':checked')) {
                grandchildContainer.show();
            } else {
                grandchildContainer.hide();
                // 子のチェックが外れたら孫のチェックも外す
                grandchildContainer.find('input[type="checkbox"]').prop('checked', false);
            }
        });
        
        // 仕事の一日の流れの項目を追加
        $('#add-schedule-item').on('click', function() {
            var newItem = $('.daily-schedule-item:first').clone();
            newItem.find('input, textarea').val('');
            newItem.find('.remove-schedule-item').show();
            $('#daily-schedule-container').append(newItem);
        });
        
        // 仕事の一日の流れの項目を削除
        $(document).on('click', '.remove-schedule-item', function() {
            $(this).closest('.daily-schedule-item').remove();
        });
        
        // 職員の声の項目を追加
        $('#add-voice-item').on('click', function() {
            var newItem = $('.staff-voice-item:first').clone();
            newItem.find('input, textarea').val('');
            newItem.find('.voice-image-preview').empty();
            newItem.find('.remove-voice-item').show();
            $('#staff-voice-container').append(newItem);
        });
        
        // 職員の声の項目を削除
        $(document).on('click', '.remove-voice-item', function() {
            $(this).closest('.staff-voice-item').remove();
        });
        
        // 職員の声の画像アップローダー
        $(document).on('click', '.upload-voice-image', function() {
            var button = $(this);
            var imageContainer = button.closest('.voice-image');
            var previewContainer = imageContainer.find('.voice-image-preview');
            var inputField = imageContainer.find('input[name^="staff_voice_image"]');
            
            var custom_uploader = wp.media({
                title: '職員の声の画像を選択',
                button: {
                    text: '画像を選択'
                },
                multiple: false
            });
            
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                previewContainer.html('<img src="' + attachment.url + '" alt="スタッフ画像">');
                inputField.val(attachment.id);
                
                // 削除ボタンを表示
                imageContainer.find('.remove-voice-image').show();
            });
            
            custom_uploader.open();
        });
        
        // 職員の声の画像削除
        $(document).on('click', '.remove-voice-image', function() {
            var imageContainer = $(this).closest('.voice-image');
            imageContainer.find('.voice-image-preview').empty();
            imageContainer.find('input[name^="staff_voice_image"]').val('');
            $(this).hide();
        });
    });
    </script>
    
    <style>
    /* 求人投稿フォームのスタイル */
    .edit-job-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .page-title {
        font-size: 24px;
        margin-bottom: 20px;
    }
    
    .success-message {
        background-color: #e8f5e9;
        color: #2e7d32;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .error-message {
        background-color: #ffebee;
        color: #c62828;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .form-section {
        margin-bottom: 30px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 20px;
        background-color: #fff;
    }
    
    .section-title {
        font-size: 18px;
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .form-row {
        margin-bottom: 20px;
    }
    
    .form-row label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-row input[type="text"],
    .form-row input[type="url"],
    .form-row textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    
    .form-hint {
        display: block;
        font-size: 12px;
        color: #757575;
        margin-top: 5px;
    }
    
    .required {
        color: #f44336;
    }
    
    .taxonomy-select {
        display: flex;
        flex-wrap: wrap;
        margin: -5px;
    }
    
    .checkbox-label {
        display: inline-block;
        margin: 5px;
        padding: 6px 12px;
        background-color: #f5f5f5;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .checkbox-label input {
        margin-right: 5px;
    }
    
    .feature-label {
        background-color: #e3f2fd;
    }
    
    .thumbnail-preview, .voice-image-preview {
        margin-bottom: 10px;
    }
    
    .thumbnail-preview img, .voice-image-preview img {
        max-width: 200px;
        max-height: 200px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 2px;
    }
    
    .btn-media-upload,
    .btn-media-remove,
    .btn-submit,
    .btn-cancel,
    .btn-view,
    .btn-new,
    .btn-add-item,
    .upload-voice-image,
    .remove-voice-image,
    .remove-schedule-item,
    .remove-voice-item {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        margin-right: 10px;
        background-color: #f5f5f5;
        color: #333;
        border: 1px solid #ddd;
    }
    
    .btn-media-remove,
    .remove-voice-image,
    .remove-schedule-item,
    .remove-voice-item {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }
    
    .btn-view {
        background-color: #2196f3;
        color: white;
        border: none;
    }
    
    .btn-new, .btn-add-item {
       background-color: #ff9800;
       color: white;
       border: none;
   }
   
   .form-actions {
       margin-top: 20px;
       text-align: center;
   }
   
   .btn-submit {
       background-color: #4caf50;
       color: white;
       border: none;
       font-size: 16px;
       padding: 10px 20px;
   }
   
   .btn-cancel {
       background-color: #f5f5f5;
       color: #333;
       border: 1px solid #ddd;
   }
   
   /* 階層化タクソノミー用スタイル */
   .parent-term {
       width: 100%;
       margin-bottom: 10px;
   }
   
   .parent-label {
       background-color: #e8eaf6;
       font-weight: bold;
   }
   
   .child-label {
       background-color: #f5f5f5;
   }
   
   .grandchild-label {
       background-color: #fafafa;
   }
   
   /* 一日の流れと職員の声のスタイル */
   .daily-schedule-item, .staff-voice-item {
       padding: 15px;
       border: 1px solid #e0e0e0;
       border-radius: 4px;
       margin-bottom: 15px;
       background-color: #fafafa;
       position: relative;
   }
   
   .schedule-time, .schedule-title, .voice-role, .voice-years {
       display: inline-block;
       vertical-align: top;
       margin-right: 15px;
       margin-bottom: 10px;
   }
   
   .schedule-time input, .schedule-title input, .voice-role input, .voice-years input {
       width: 150px;
   }
   
   .schedule-description, .voice-comment, .voice-image {
       margin-bottom: 10px;
   }
   
   .remove-schedule-item, .remove-voice-item {
       position: absolute;
       top: 10px;
       right: 10px;
       padding: 5px 10px;
       font-size: 12px;
   }
   
   /* レスポンシブ対応 */
   @media (max-width: 768px) {
       .edit-job-container {
           padding: 10px;
       }
       
       .form-section {
           padding: 15px;
       }
       
       .taxonomy-select {
           flex-direction: column;
       }
       
       .checkbox-label {
           margin: 3px 0;
       }
       
       .schedule-time, .schedule-title, .voice-role, .voice-years {
           display: block;
           margin-right: 0;
       }
   }
   </style>
</div>

<?php get_footer(); ?>