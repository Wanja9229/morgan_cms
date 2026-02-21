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
/* 위키 본문 타이포그래피 */
.wiki-content { font-size: 0.9375rem; line-height: 1.8; color: var(--mg-text-secondary); }
.wiki-content p { margin-bottom: 0.5em; }
.wiki-content h3 { font-size: 1.125rem; font-weight: 600; color: var(--mg-text-primary); margin: 1em 0 0.5em; }
.wiki-content h4 { font-size: 1rem; font-weight: 600; color: var(--mg-text-primary); margin: 0.75em 0 0.375em; }
.wiki-content ul, .wiki-content ol { padding-left: 1.5em; margin-bottom: 0.75em; }
.wiki-content li { margin-bottom: 0.25em; }
.wiki-content a { color: var(--mg-accent); text-decoration: underline; }
.wiki-content a:hover { opacity: 0.8; }
.wiki-content blockquote { border-left: 3px solid var(--mg-accent); padding-left: 1em; margin: 0.75em 0; color: var(--mg-text-muted); font-style: italic; }
.wiki-content img { max-width: 100%; border-radius: 8px; }
.wiki-content strong { color: var(--mg-text-primary); }
.wiki-content hr { border: none; border-top: 1px solid var(--mg-bg-tertiary); margin: 1.5em 0; }
.wiki-content { overflow-x: auto; }
.wiki-content table { width: 100%; border-collapse: collapse; margin: 0.75em 0; }
.wiki-content th, .wiki-content td { border: 1px solid var(--mg-bg-tertiary); padding: 0.5em 0.75em; text-align: left; }
.wiki-content th { background: var(--mg-bg-tertiary); color: var(--mg-text-primary); font-weight: 600; }
/* 이미지 피겨 */
.wiki-figure img { max-width: 100%; display: block; }
/* TOC 활성 링크 */
.toc-link.active { color: var(--mg-accent) !important; background: color-mix(in srgb, var(--mg-accent) 10%, transparent); }
</style>

<div class="mg-inner px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-mg-accent/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-mg-text-primary truncate"><?php echo htmlspecialchars($article['la_title']); ?></h1>
            <nav class="text-sm text-mg-text-muted flex items-center gap-1 flex-wrap">
                <a href="<?php echo G5_BBS_URL; ?>/lore.php" class="text-mg-accent hover:underline">세계관 위키</a>
                <?php if ($lc_name) { ?>
                <span>&rsaquo;</span>
                <a href="<?php echo G5_BBS_URL; ?>/lore.php?category=<?php echo $lc_id; ?>" class="text-mg-accent hover:underline"><?php echo htmlspecialchars($lc_name); ?></a>
                <?php } ?>
                <span>&rsaquo;</span>
                <span class="truncate"><?php echo htmlspecialchars($article['la_title']); ?></span>
            </nav>
        </div>
    </div>

    <!-- 메인 카드 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
        <!-- 메타 바 -->
        <div class="px-5 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-mg-text-muted">
            <?php if ($lc_name) { ?>
            <span class="bg-mg-accent/15 text-mg-accent px-2 py-0.5 rounded-full font-medium"><?php echo htmlspecialchars($lc_name); ?></span>
            <?php } ?>
            <?php if (!empty($article['la_subtitle'])) { ?>
            <span class="italic text-mg-text-secondary"><?php echo htmlspecialchars($article['la_subtitle']); ?></span>
            <?php } ?>
            <span class="ml-auto flex items-center gap-3">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <?php echo number_format((int)$article['la_hit'] + 1); ?>
                </span>
                <?php if (!empty($article['la_created'])) { ?>
                <span><?php echo date('Y.m.d', strtotime($article['la_created'])); ?></span>
                <?php } ?>
            </span>
        </div>

        <!-- 콘텐츠 영역 -->
        <div class="flex">
            <!-- 본문 -->
            <div class="flex-1 min-w-0 px-5 py-6 md:px-6">
                <!-- 인라인 목차 (섹션 3개 이상) -->
                <?php if (count($sections) >= 3) { ?>
                <div class="bg-mg-bg-tertiary/30 rounded-lg border border-mg-bg-tertiary px-4 py-3 mb-6 inline-block" style="min-width:220px;max-width:340px;">
                    <h3 class="text-xs font-semibold text-mg-text-primary mb-2 text-center uppercase tracking-wider">목차</h3>
                    <ol class="space-y-0.5">
                        <?php foreach ($sections as $idx => $sec) { ?>
                        <li>
                            <a href="#section-<?php echo $sec['ls_id']; ?>" class="toc-link text-sm text-mg-accent hover:underline block py-0.5">
                                <span class="text-mg-text-muted mr-1"><?php echo ($idx + 1); ?>.</span><?php echo htmlspecialchars($sec['ls_name']); ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ol>
                </div>
                <?php } ?>

                <!-- 섹션 -->
                <?php if (!empty($sections)) { ?>
                <?php foreach ($sections as $idx => $sec) { ?>
                <div id="section-<?php echo $sec['ls_id']; ?>" class="mb-7 scroll-mt-20">
                    <h2 class="text-lg font-semibold text-mg-text-primary border-b border-mg-bg-tertiary pb-2 mb-3"><?php echo htmlspecialchars($sec['ls_name']); ?></h2>

                    <?php if (isset($sec['ls_type']) && $sec['ls_type'] === 'image') { ?>
                    <div class="wiki-figure bg-mg-bg-tertiary/30 border border-mg-bg-tertiary rounded-lg p-2 inline-block max-w-full">
                        <img src="<?php echo htmlspecialchars($sec['ls_image']); ?>" alt="<?php echo htmlspecialchars($sec['ls_name']); ?>" class="rounded">
                        <?php if (!empty($sec['ls_image_caption'])) { ?>
                        <p class="text-xs text-mg-text-muted text-center mt-2 italic"><?php echo htmlspecialchars($sec['ls_image_caption']); ?></p>
                        <?php } ?>
                    </div>
                    <?php } else { ?>
                    <div class="wiki-content"><?php echo $sec['ls_content']; ?></div>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php } else { ?>
                <div class="text-center py-12 text-mg-text-muted">
                    <p>아직 작성된 내용이 없습니다.</p>
                </div>
                <?php } ?>
            </div>

            <!-- 우측 스티키 TOC (카드 내부, 데스크톱) -->
            <?php if (count($sections) >= 2) { ?>
            <div class="hidden lg:block w-48 flex-shrink-0 border-l border-mg-bg-tertiary">
                <div class="sticky top-20 p-4">
                    <h4 class="text-xs font-semibold text-mg-text-muted uppercase tracking-wider mb-3">목차</h4>
                    <nav class="space-y-0.5">
                        <?php foreach ($sections as $idx => $sec) { ?>
                        <a href="#section-<?php echo $sec['ls_id']; ?>" class="toc-link block text-xs text-mg-text-muted hover:text-mg-text-primary py-1 px-2 rounded transition-colors"><?php echo htmlspecialchars($sec['ls_name']); ?></a>
                        <?php } ?>
                    </nav>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 관련 문서 -->
    <?php if (!empty($related_articles)) { ?>
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-5 mt-4">
        <h3 class="text-sm font-semibold text-mg-text-primary mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            같은 카테고리 문서
        </h3>
        <div class="flex flex-wrap gap-x-4 gap-y-1">
            <?php foreach ($related_articles as $rel) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/lore_view.php?la_id=<?php echo $rel['la_id']; ?>" class="text-sm text-mg-accent hover:underline"><?php echo htmlspecialchars($rel['la_title']); ?></a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>

<script>
// 부드러운 스크롤 + TOC 하이라이트
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
