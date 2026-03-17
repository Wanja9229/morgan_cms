<?php
/**
 * Morgan Edition - 세계관 위키 메인 페이지
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 세계관 위키 활성화 확인
if (mg_config('lore_use', '1') == '0') {
    alert('세계관 위키가 비활성화되어 있습니다.', G5_BBS_URL);
}

// 파라미터
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = (int)mg_config('lore_articles_per_page', 12);

// 데이터 조회
$categories = mg_get_lore_categories();
$articles_data = mg_get_lore_articles($category, $page, $per_page);
$articles = $articles_data['articles'];
$total_count = (int)$articles_data['total'];
$total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 1;

$g5['title'] = '세계관 위키';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-mg-accent/20 flex items-center justify-center flex-shrink-0">
            <i data-lucide="book-open" class="w-6 h-6 text-mg-accent"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-mg-text-primary">세계관 위키</h1>
            <p class="text-sm text-mg-text-muted">이 세계의 역사와 설정을 살펴보세요</p>
        </div>
    </div>

    <!-- 서브 탭 -->
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="<?php echo G5_BBS_URL; ?>/lore.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-accent/10 border border-mg-accent/30 text-mg-accent">
            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
            위키
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/lore_timeline.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-bg-secondary border border-mg-bg-tertiary text-mg-text-secondary hover:text-mg-accent hover:border-mg-accent/30">
            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
            연대기
        </a>
        <?php if (mg_config('lore_map_image', '')) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/lore_map.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-bg-secondary border border-mg-bg-tertiary text-mg-text-secondary hover:text-mg-accent hover:border-mg-accent/30">
            <i data-lucide="map" class="w-3.5 h-3.5"></i>
            지도
        </a>
        <?php } ?>
    </div>

    <!-- 카테고리 탭 -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="<?php echo G5_BBS_URL; ?>/lore.php?category=0" class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?php echo $category == 0 ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">전체</a>
        <?php foreach ($categories as $cat) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/lore.php?category=<?php echo $cat['lc_id']; ?>" class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?php echo $category == (int)$cat['lc_id'] ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>"><?php echo htmlspecialchars($cat['lc_name']); ?></a>
        <?php } ?>
    </div>

    <!-- 문서 리스트 -->
    <?php if (!empty($articles)) { ?>
    <div class="space-y-2">
        <?php foreach ($articles as $article) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/lore_view.php?la_id=<?php echo $article['la_id']; ?>" class="flex items-center gap-4 bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-3 hover:border-mg-accent/50 transition-colors group">
            <!-- 정사각형 썸네일 -->
            <div class="w-14 h-14 rounded-lg bg-mg-bg-tertiary overflow-hidden flex-shrink-0">
                <?php if (!empty($article['la_thumbnail'])) { ?>
                <img src="<?php echo htmlspecialchars($article['la_thumbnail']); ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <?php } else { ?>
                <div class="w-full h-full flex items-center justify-center text-mg-text-muted">
                    <i data-lucide="file-text" class="w-6 h-6 opacity-50"></i>
                </div>
                <?php } ?>
            </div>
            <!-- 문서 정보 -->
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-mg-text-primary text-sm truncate group-hover:text-mg-accent transition-colors"><?php echo htmlspecialchars($article['la_title']); ?></h3>
                <div class="flex items-center gap-2 mt-1">
                    <?php if (!empty($article['lc_name'])) { ?>
                    <span class="text-xs text-mg-accent bg-mg-accent/10 px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($article['lc_name']); ?></span>
                    <?php } ?>
                    <?php if (!empty($article['la_summary'])) { ?>
                    <span class="text-xs text-mg-text-muted truncate"><?php echo htmlspecialchars($article['la_summary']); ?></span>
                    <?php } ?>
                </div>
            </div>
            <!-- 화살표 -->
            <svg class="w-4 h-4 text-mg-text-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1) { ?>
    <div class="flex items-center justify-center gap-1 mt-8">
        <?php
        // 이전 페이지
        if ($page > 1) {
            $prev_url = G5_BBS_URL.'/lore.php?category='.$category.'&page='.($page - 1);
        ?>
        <a href="<?php echo $prev_url; ?>" class="w-10 h-10 flex items-center justify-center rounded-lg bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary transition-colors">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </a>
        <?php } ?>

        <?php
        // 페이지 번호 계산
        $page_range = 5;
        $start_page = max(1, $page - floor($page_range / 2));
        $end_page = min($total_pages, $start_page + $page_range - 1);
        if ($end_page - $start_page < $page_range - 1) {
            $start_page = max(1, $end_page - $page_range + 1);
        }

        if ($start_page > 1) {
        ?>
        <a href="<?php echo G5_BBS_URL; ?>/lore.php?category=<?php echo $category; ?>&page=1" class="w-10 h-10 flex items-center justify-center rounded-lg bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary transition-colors text-sm">1</a>
        <?php if ($start_page > 2) { ?>
        <span class="w-10 h-10 flex items-center justify-center text-mg-text-muted text-sm">...</span>
        <?php } ?>
        <?php } ?>

        <?php for ($i = $start_page; $i <= $end_page; $i++) {
            $page_url = G5_BBS_URL.'/lore.php?category='.$category.'&page='.$i;
        ?>
        <a href="<?php echo $page_url; ?>" class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?php echo $i == $page ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>"><?php echo $i; ?></a>
        <?php } ?>

        <?php if ($end_page < $total_pages) { ?>
        <?php if ($end_page < $total_pages - 1) { ?>
        <span class="w-10 h-10 flex items-center justify-center text-mg-text-muted text-sm">...</span>
        <?php } ?>
        <a href="<?php echo G5_BBS_URL; ?>/lore.php?category=<?php echo $category; ?>&page=<?php echo $total_pages; ?>" class="w-10 h-10 flex items-center justify-center rounded-lg bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary transition-colors text-sm"><?php echo $total_pages; ?></a>
        <?php } ?>

        <?php
        // 다음 페이지
        if ($page < $total_pages) {
            $next_url = G5_BBS_URL.'/lore.php?category='.$category.'&page='.($page + 1);
        ?>
        <a href="<?php echo $next_url; ?>" class="w-10 h-10 flex items-center justify-center rounded-lg bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary transition-colors">
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <!-- 빈 상태 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary py-16 px-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
            <i data-lucide="file-text" class="w-8 h-8 text-mg-text-muted"></i>
        </div>
        <h3 class="text-lg font-medium text-mg-text-primary mb-2">등록된 문서가 없습니다</h3>
        <p class="text-mg-text-muted">아직 이 카테고리에 등록된 세계관 문서가 없습니다.</p>
    </div>
    <?php } ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
