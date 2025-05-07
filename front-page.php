<?php

/**
 * Cocoon WordPress Theme
 * @author: yhira
 * @link: https://wp-cocoon.com/
 * @license: http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 */
if (!defined('ABSPATH')) exit; ?>
<?php get_header(); ?>
<main class="main-content">
        <div class="slider-container">
            <div class="slider">
                <div class="slide" id="slide1">
                    <!-- スライド1のコンテンツ -->
                </div>
                <div class="slide" id="slide2">
                    <!-- スライド2のコンテンツ -->
                </div>
                <div class="slide" id="slide3">
                    <!-- スライド3のコンテンツ -->
                </div>
            </div>
            <div class="slider-nav">
                <button class="prev-btn">❮</button>
                <button class="next-btn">❯</button>
            </div>
            <div class="slider-dots">
                <span class="dot active" data-slide="0"></span>
                <span class="dot" data-slide="1"></span>
                <span class="dot" data-slide="2"></span>
            </div>
        </div>
        
<!-- 求人検索 -->
<?php get_template_part('search', 'form'); ?>

<!-- 職種から探す -->
<section class="job-category">
  <h2 class="section-title">職種から探す</h2>
  <div class="category-container">
    <div class="category-item">
      <h3>児童発達支援管理責任者</h3>
      <div class="category-icon">
        <i class="fas fa-user-shield"></i>
      </div>
    </div>
    <div class="category-item">
      <h3>児童指導員</h3>
      <div class="category-icon">
        <i class="fas fa-users"></i>
      </div>
    </div>
    <div class="category-item">
      <h3>保育士</h3>
      <div class="category-icon">
        <i class="fas fa-baby-carriage"></i>
      </div>
    </div>
    <div class="category-item">
      <h3>理学療法士</h3>
      <div class="category-icon">
        <i class="fas fa-running"></i>
      </div>
    </div>
    <div class="category-item">
      <h3>作業療法士</h3>
      <div class="category-icon">
        <i class="fas fa-heart"></i>
      </div>
    </div>
    <div class="category-item">
      <h3>言語聴覚士</h3>
      <div class="category-icon">
        <i class="fas fa-comment-dots"></i>
      </div>
    </div>
    <div class="category-item">
      <h3>その他</h3>
      <div class="category-icon">
        <i class="fas fa-ellipsis-h"></i>
      </div>
    </div>
  </div>
</section>

<!-- 特徴から探す -->
<section class="feature-search">
  <h2 class="section-title">特徴から探す</h2>
  <div class="tokuchou-container">
    <div class="tokuchou-item">
      <div class="tokuchou-image">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/mikeikenn.jpg" alt="未経験歓迎の求人">
        <div class="tokuchou-title">
          <h3>未経験歓迎の求人</h3>
        </div>
      </div>
    </div>
    <div class="tokuchou-item">
      <div class="tokuchou-image">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/opening-staff.jpg" alt="オープニングスタッフの求人">
        <div class="tokuchou-title">
          <h3>オープニングスタッフの求人</h3>
        </div>
      </div>
    </div>
    <div class="tokuchou-item">
      <div class="tokuchou-image">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/high-income.jpg" alt="高収入の求人">
        <div class="tokuchou-title">
          <h3>高収入の求人</h3>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 新着求人情報 -->
<section class="new-jobs">
  <h2 class="section-title">新着求人情報</h2>
  <div class="job-container">
    <!-- 求人カード1 -->
    <div class="job-card">
      <div class="job-header">
        <div class="company-name">
          <p>こどもプラス・新宿</p>
          <p>○○○○○○・株式会社</p>
        </div>
        <div class="job-tag new">新着</div>
      </div>
      <div class="job-image">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/job-image1.jpg" alt="求人画像">
      </div>
      <div class="job-info">
        <h3 class="job-title">児童発達支援管理責任者</h3>
        <p class="job-location">東京都新宿区西新宿1-2-3</p>
        <p class="job-salary">月給 250,000円～350,000円</p>
        <div class="job-tags">
          <span class="tag">正社員</span>
          <span class="tag">経験者優遇</span>
        </div>
      </div>
      <div class="job-footer">
        <a href="#" class="detail-btn">詳細を見る</a>
      </div>
    </div>
    
    <!-- 求人カード2 -->
    <div class="job-card">
      <div class="job-header">
        <div class="company-name">
          <p>こどもプラス・新宿</p>
          <p>○○○○○○・株式会社</p>
        </div>
        <div class="job-tag popular">人気</div>
      </div>
      <div class="job-image">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/job-image2.jpg" alt="求人画像">
      </div>
      <div class="job-info">
        <h3 class="job-title">児童指導員</h3>
        <p class="job-location">東京都新宿区西新宿1-2-3</p>
        <p class="job-salary">月給 220,000円～280,000円</p>
        <div class="job-tags">
          <span class="tag">正社員</span>
          <span class="tag">未経験者OK</span>
        </div>
      </div>
      <div class="job-footer">
        <a href="#" class="detail-btn">詳細を見る</a>
      </div>
    </div>
    
    <!-- 求人カード3 -->
    <div class="job-card">
      <div class="job-header">
        <div class="company-name">
          <p>こどもプラス・新宿</p>
          <p>○○○○○○・株式会社</p>
        </div>
        <div class="job-tag other">その他</div>
      </div>
      <div class="job-image">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/job-image3.jpg" alt="求人画像">
      </div>
      <div class="job-info">
        <h3 class="job-title">言語聴覚士</h3>
        <p class="job-location">東京都新宿区西新宿1-2-3</p>
        <p class="job-salary">月給 280,000円～350,000円</p>
        <div class="job-tags">
          <span class="tag">正社員</span>
          <span class="tag">経験者優遇</span>
        </div>
      </div>
      <div class="job-footer">
        <a href="#" class="detail-btn">詳細を見る</a>
      </div>
    </div>
    
    <!-- 次へボタン -->
    <div class="next-job-btn">
      <button>→</button>
    </div>
  </div>
</section>

<!-- サイト案内 -->
<section class="about-site">
  <div class="about-container">
    <h2 class="about-main-title">こどもプラス求人サイトへようこそ！あなたに最適な職場が見つかる場所。</h2>
    
    <div class="about-items">
      <div class="about-item">
        <h3 class="about-item-title">他にはない充実した求人情報</h3>
        <div class="about-item-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/feature-unique.png" alt="充実した求人情報">
        </div>
        <p class="about-item-text">一般的な給与・勤務時間の情報だけでなく、実際に働くスタッフの生の声や職場の雰囲気まで、リアルな情報をお届けします。「どんな職場なのか」が具体的にイメージできる求人情報を提供しています。</p>
      </div>
      
      <div class="about-item">
        <h3 class="about-item-title">スムーズな応募プロセス</h3>
        <div class="about-item-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/feature-process.png" alt="スムーズな応募プロセス">
        </div>
        <p class="about-item-text">会員登録が完了すると、応募フォームに情報が自動入力されます。そのため、面倒な手続きなしで、効率良く求人への応募が可能です。</p>
      </div>
      
      <div class="about-item">
        <h3 class="about-item-title">あなたにぴったりの求人をお届け</h3>
        <div class="about-item-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/feature-matching.png" alt="ぴったりの求人">
        </div>
        <p class="about-item-text">ご登録いただいた希望条件に合わせて、あなたにマッチした求人情報をお知らせします。また、最新の求人情報もいち早くチェックできるので、理想の職場との出会いを逃しません。</p>
      </div>
    </div>
  </div>
</section>

<!-- マッチング案内 -->
<section class="matching-section">
  <div class="matching-container">
    <h2 class="matching-title">あなたにぴったりの求人情報を見てみよう</h2>
    <p class="matching-desc">あなたのスキルや経験、希望に合った求人情報を閲覧できます。会員登録をして、簡単に応募を行うましょう。</p>
    <div class="matching-image">
      <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/matching-puzzle.png" alt="マッチング">
    </div>
    <div class="matching-label">matching</div>
    <a href="#" class="register-large-btn">
      <span class="btn-icon">○</span>
      登録して情報を見る
    </a>
  </div>
</section>

<!-- 職種ごとのリンク処理 -->
<script>
jQuery(document).ready(function($) {
    // 職種から探すセクションの各項目にリンクを設定
    $('.category-item').each(function() {
        var title = $(this).find('h3').text();
        var slug = '';
        
        // タイトルからスラッグを推測（実際の環境に合わせて調整が必要）
        switch (title) {
            case '児童発達支援管理責任者':
                slug = 'child-development-manager';
                break;
            case '児童指導員':
                slug = 'child-instructor';
                break;
            case '保育士':
                slug = 'childcare-worker';
                break;
            case '理学療法士':
                slug = 'physical-therapist';
                break;
            case '作業療法士':
                slug = 'occupational-therapist';
                break;
            case '言語聴覚士':
                slug = 'speech-therapist';
                break;
            case 'その他':
                slug = 'others';
                break;
            default:
                // タイトルをスラッグ化（簡易版）
                slug = title.toLowerCase().replace(/\s+/g, '-');
        }
        
        if (slug) {
            $(this).css('cursor', 'pointer');
            $(this).on('click', function() {
                window.location.href = site_url + '/jobs/position/' + slug + '/';
            });
        }
    });
    
    // 特徴から探すセクションの各項目にリンクを設定（あれば）
    $('.tokuchou-item').each(function() {
        var title = $(this).find('h3').text();
        var featureSlug = '';
        
        // タイトルからスラッグを推測
        if (title.includes('未経験')) {
            featureSlug = 'inexperienced';
        } else if (title.includes('オープニング')) {
            featureSlug = 'opening-staff';
        } else if (title.includes('高収入')) {
            featureSlug = 'high-income';
        }
        
        if (featureSlug) {
            $(this).css('cursor', 'pointer');
            $(this).on('click', function() {
                window.location.href = site_url + '/jobs/feature/' + featureSlug + '/';
            });
        }
    });
});
</script>


<?php get_footer(); ?>

