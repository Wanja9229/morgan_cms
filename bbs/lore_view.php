<?php
/**
 * Morgan Edition - 세계관 위키 문서 상세 보기
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 세계관 위키 활성화 확인
if (mg_config('lore_use', '1') == '0') {
    alert('세계관 위키가 비활성화되어 있습니다.', G5_BBS_URL);
}

// 문서 ID
$la_id = isset($_GET['la_id']) ? (int)$_GET['la_id'] : 0;
if ($la_id <= 0) {
    alert('잘못된 접근입니다.', G5_BBS_URL.'/lore.php');
}

// 문서 조회
$article = mg_get_lore_article($la_id);
if (!$article || (isset($article['la_use']) && !$article['la_use'])) {
    alert('문서를 찾을 수 없습니다.', G5_BBS_URL.'/lore.php');
}

// 조회수 증가
sql_query("UPDATE {$g5['mg_lore_article_table']} SET la_hit = la_hit + 1 WHERE la_id = {$la_id}");

// 섹션 데이터
$sections = isset($article['sections']) ? $article['sections'] : array();

// 카테고리 정보
$lc_id = (int)($article['lc_id'] ?? 0);
$lc_name = $article['lc_name'] ?? '';

// 같은 카테고리의 관련 문서 (최대 5개, 현재 문서 제외)
$related_articles = array();
if ($lc_id > 0) {
    $sql = "SELECT la_id, la_title, la_subtitle
            FROM {$g5['mg_lore_article_table']}
            WHERE lc_id = {$lc_id}
              AND la_id != {$la_id}
              AND la_use = 1
            ORDER BY la_order ASC, la_id DESC
            LIMIT 5";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $related_articles[] = $row;
    }
}

$g5['title'] = $article['la_title'] . ' - 세계관 위키';
include_once(G5_THEME_PATH.'/head.php');
?>

<style>
.wiki-body { max-width: var(--mg-content-width); margin: 0 auto; padding: 0 1rem; }
.wiki-article { position: relative; }
.wiki-title { font-size: 1.75rem; font-weight: 700; color: var(--mg-text-primary, #e4e6eb); border-bottom: 1px solid var(--mg-bg-tertiary, #3a3b3e); padding-bottom: 0.5rem; margin-bottom: 0.25rem; line-height: 1.3; }
.wiki-subtitle { font-size: 1rem; color: var(--mg-text-muted, #949ba4); margin-bottom: 0.75rem; font-style: italic; }
.wiki-meta { display: flex; gap: 1rem; font-size: 0.75rem; color: var(--mg-text-muted, #949ba4); margin-bottom: 1.5rem; }
/* 인라인 TOC 박스 (위키 스타일) */
.wiki-toc { background: var(--mg-bg-secondary, #2b2d31); border: 1px solid var(--mg-bg-tertiary, #3a3b3e); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: inline-block; min-width: 240px; max-width: 360px; }
.wiki-toc-title { font-size: 0.8125rem; font-weight: 600; color: var(--mg-text-primary, #e4e6eb); margin-bottom: 0.5rem; text-align: center; }
.wiki-toc ol { list-style: none; counter-reset: toc; padding: 0; margin: 0; }
.wiki-toc ol li { counter-increment: toc; }
.wiki-toc ol li a { display: block; padding: 0.2rem 0; font-size: 0.8125rem; color: var(--mg-accent, #5865f2); text-decoration: none; transition: color 0.15s; }
.wiki-toc ol li a::before { content: counter(toc) ". "; color: var(--mg-text-muted, #949ba4); }
.wiki-toc ol li a:hover { text-decoration: underline; }
.wiki-toc ol li a.active { color: var(--mg-text-primary, #e4e6eb); font-weight: 500; }
/* 섹션 */
.wiki-section { margin-bottom: 1.75rem; }
.wiki-section-heading { font-size: 1.25rem; font-weight: 600; color: var(--mg-text-primary, #e4e6eb); border-bottom: 1px solid var(--mg-bg-tertiary, #3a3b3e); padding-bottom: 0.35rem; margin-bottom: 0.75rem; }
.wiki-section-content { font-size: 0.9375rem; line-height: 1.75; color: var(--mg-text-secondary, #b5bac1); }
.wiki-section-content p { margin-bottom: 0.5em; }
/* 이미지 섹션 */
.wiki-figure { margin: 0.75rem 0; background: var(--mg-bg-secondary, #2b2d31); border: 1px solid var(--mg-bg-tertiary, #3a3b3e); padding: 0.5rem; display: inline-block; max-width: 100%; }
.wiki-figure img { max-width: 100%; display: block; }
.wiki-figure figcaption { font-size: 0.75rem; color: var(--mg-text-muted, #949ba4); text-align: center; margin-top: 0.5rem; font-style: italic; }
/* 관련 문서 하단 */
.wiki-related { background: var(--mg-bg-secondary, #2b2d31); border: 1px solid var(--mg-bg-tertiary, #3a3b3e); padding: 1rem 1.25rem; margin-top: 2rem; }
.wiki-related-title { font-size: 0.8125rem; font-weight: 600; color: var(--mg-text-primary, #e4e6eb); margin-bottom: 0.5rem; }
.wiki-related-list { display: flex; flex-wrap: wrap; gap: 0.25rem 1.5rem; }
.wiki-related-list a { font-size: 0.8125rem; color: var(--mg-accent, #5865f2); text-decoration: none; padding: 0.15rem 0; }
.wiki-related-list a:hover { text-decoration: underline; }
/* 브레드크럼 */
.wiki-breadcrumb { font-size: 0.75rem; color: var(--mg-text-muted, #949ba4); margin-bottom: 1rem; }
.wiki-breadcrumb a { color: var(--mg-accent, #5865f2); text-decoration: none; }
.wiki-breadcrumb a:hover { text-decoration: underline; }
.wiki-breadcrumb span { margin: 0 0.35rem; }
/* 우측 스티키 TOC (데스크톱) */
.wiki-sticky-toc { position: sticky; top: 5rem; }
.wiki-sticky-toc-inner { border-left: 2px solid var(--mg-bg-tertiary, #3a3b3e); padding-left: 0.75rem; }
.wiki-sticky-toc-inner a { display: block; font-size: 0.8125rem; color: var(--mg-text-muted, #949ba4); padding: 0.25rem 0; text-decoration: none; transition: all 0.15s; border-left: 2px solid transparent; margin-left: -0.85rem; padding-left: 0.75rem; }
.wiki-sticky-toc-inner a:hover { color: var(--mg-text-primary, #e4e6eb); }
.wiki-sticky-toc-inner a.active { color: var(--mg-accent, #5865f2); border-left-color: var(--mg-accent, #5865f2); }
</style>

<div class="wiki-body py-6">
    <!-- 브레드크럼 -->
    <div class="wiki-breadcrumb">
        <a href="<?php echo G5_BBS_URL; ?>/lore.php">세계관 위키</a>
        <?php if ($lc_name) { ?>
        <span>&rsaquo;</span>
        <a href="<?php echo G5_BBS_URL; ?>/lore.php?category=<?php echo $lc_id; ?>"><?php echo htmlspecialchars($lc_name); ?></a>
        <?php } ?>
        <span>&rsaquo;</span>
        <?php echo htmlspecialchars($article['la_title']); ?>
    </div>

    <!-- 2단 레이아웃 -->
    <div class="flex gap-8 items-start">
        <!-- 본문 -->
        <div class="wiki-article flex-1 min-w-0">
            <!-- 제목 -->
            <h1 class="wiki-title"><?php echo htmlspecialchars($article['la_title']); ?></h1>

            <!-- 부제목 -->
            <?php if (!empty($article['la_subtitle'])) { ?>
            <p class="wiki-subtitle"><?php echo htmlspecialchars($article['la_subtitle']); ?></p>
            <?php } ?>

            <!-- 메타 -->
            <div class="wiki-meta">
                <?php if ($lc_name) { ?>
                <span><?php echo htmlspecialchars($lc_name); ?></span>
                <?php } ?>
                <span>조회 <?php echo number_format((int)$article['la_hit'] + 1); ?></span>
                <?php if (!empty($article['la_created'])) { ?>
                <span><?php echo date('Y.m.d', strtotime($article['la_created'])); ?></span>
                <?php } ?>
            </div>

            <!-- 인라인 목차 (섹션 3개 이상일 때) -->
            <?php if (count($sections) >= 3) { ?>
            <div class="wiki-toc" id="wiki-toc-inline">
                <div class="wiki-toc-title">목차</div>
                <ol>
                    <?php foreach ($sections as $idx => $sec) { ?>
                    <li><a href="#section-<?php echo $sec['ls_id']; ?>" class="toc-link"><?php echo htmlspecialchars($sec['ls_name']); ?></a></li>
                    <?php } ?>
                </ol>
            </div>
            <?php } ?>

            <!-- 섹션 내용 -->
            <?php if (!empty($sections)) { ?>
            <?php foreach ($sections as $idx => $sec) { ?>
            <div id="section-<?php echo $sec['ls_id']; ?>" class="wiki-section scroll-mt-20">
                <h2 class="wiki-section-heading"><?php echo htmlspecialchars($sec['ls_name']); ?></h2>

                <?php if (isset($sec['ls_type']) && $sec['ls_type'] === 'image') { ?>
                <div class="wiki-figure">
                    <img src="<?php echo MG_LORE_IMAGE_URL.'/'.htmlspecialchars($sec['ls_image']); ?>" alt="<?php echo htmlspecialchars($sec['ls_name']); ?>">
                    <?php if (!empty($sec['ls_image_caption'])) { ?>
                    <figcaption><?php echo htmlspecialchars($sec['ls_image_caption']); ?></figcaption>
                    <?php } ?>
                </div>
                <?php } else { ?>
                <div class="wiki-section-content"><?php echo nl2br(htmlspecialchars($sec['ls_content'])); ?></div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php } else { ?>
            <div class="text-center py-12 text-mg-text-muted">
                <p>아직 작성된 내용이 없습니다.</p>
            </div>
            <?php } ?>

            <!-- 관련 문서 (하단) -->
            <?php if (!empty($related_articles)) { ?>
            <div class="wiki-related">
                <div class="wiki-related-title">같은 카테고리 문서</div>
                <div class="wiki-related-list">
                    <?php foreach ($related_articles as $rel) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/lore_view.php?la_id=<?php echo $rel['la_id']; ?>"><?php echo htmlspecialchars($rel['la_title']); ?></a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 우측 스티키 목차 (데스크톱, 섹션 2개 이상) -->
        <?php if (count($sections) >= 2) { ?>
        <div class="hidden lg:block w-52 flex-shrink-0 wiki-sticky-toc">
            <div class="wiki-sticky-toc-inner">
                <?php foreach ($sections as $idx => $sec) { ?>
                <a href="#section-<?php echo $sec['ls_id']; ?>" class="toc-link"><?php echo htmlspecialchars($sec['ls_name']); ?></a>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script>
// 부드러운 스크롤 (목차 링크)
document.addEventListener('DOMContentLoaded', function() {
    var tocLinks = document.querySelectorAll('.toc-link');
    tocLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = this.getAttribute('href').substring(1);
            var target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.pushState(null, null, '#' + targetId);
            }
        });
    });

    // 스크롤 감지 → TOC 활성 항목 하이라이트
    var sectionEls = document.querySelectorAll('[id^="section-"]');
    if (sectionEls.length > 0) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var id = entry.target.getAttribute('id');
                    tocLinks.forEach(function(link) {
                        if (link.getAttribute('href') === '#' + id) {
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                }
            });
        }, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });

        sectionEls.forEach(function(sec) { observer.observe(sec); });
    }
});
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
