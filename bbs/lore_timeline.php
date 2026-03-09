<?php
/**
 * Morgan Edition - 세계관 타임라인 페이지
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 세계관 위키 활성화 확인
if (mg_config('lore_use', '1') == '0') {
    alert('세계관 위키가 비활성화되어 있습니다.', G5_BBS_URL);
}

// 타임라인 데이터 조회
$timeline = mg_get_lore_timeline();

$g5['title'] = '타임라인 - 세계관 위키';
include_once(G5_THEME_PATH.'/head.php');
?>

<style>
/* 타임라인 컨테이너 */
.lore-timeline {
    position: relative;
    padding-left: 2.5rem;
}
.lore-timeline::before {
    content: '';
    position: absolute;
    left: 0.8rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--mg-bg-tertiary, #2a2a2a);
}

/* 타임라인 이벤트 */
.lore-event {
    position: relative;
    padding-bottom: 2rem;
    padding-left: 1.5rem;
}
.lore-event:last-child {
    padding-bottom: 0;
}
.lore-event::before {
    content: '';
    position: absolute;
    left: -1.95rem;
    top: 0.35rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--mg-bg-tertiary, #2a2a2a);
    border: 2px solid var(--mg-bg-secondary, #1a1a1a);
    z-index: 1;
}

/* 주요 이벤트 노드 */
.lore-event.major::before {
    width: 16px;
    height: 16px;
    left: -2.05rem;
    top: 0.25rem;
    background: var(--mg-accent, #f59f0a);
    border-color: var(--mg-accent, #f59f0a);
    box-shadow: 0 0 8px rgba(245, 159, 10, 0.4);
}

/* 시대 구분자 */
.lore-era-divider {
    position: relative;
    padding: 0.5rem 0 1.5rem 0;
    margin-left: -2.5rem;
}

/* 이벤트 카드 호버 */
.lore-event-card {
    transition: border-color 0.2s ease, transform 0.2s ease;
    overflow-wrap: break-word;
    word-break: break-word;
}
.lore-event-card:hover {
    border-color: rgba(245, 159, 10, 0.3);
}

/* 모바일 여백 축소 */
@media (max-width: 640px) {
    .lore-timeline { padding-left: 2rem; }
    .lore-timeline::before { left: 0.55rem; }
    .lore-era-divider { margin-left: -2rem; }
    .lore-event { padding-left: 0.75rem; }
    .lore-event::before { left: -1.7rem; }
    .lore-event.major::before { left: -1.8rem; }
}
</style>

<div class="mg-inner px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-mg-accent/20 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-6 h-6 text-mg-accent"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-mg-text-primary">타임라인</h1>
                <p class="text-sm text-mg-text-muted"><?php echo htmlspecialchars(mg_config('lore_timeline_desc', '이 세계의 역사를 시간순으로 살펴보세요')); ?></p>
            </div>
        </div>
    </div>

    <!-- 서브 탭 (2뎁스) -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="<?php echo G5_BBS_URL; ?>/lore.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-bg-secondary border border-mg-bg-tertiary text-mg-text-secondary hover:text-mg-accent hover:border-mg-accent/30">
            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
            위키
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/lore_timeline.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-accent/10 border border-mg-accent/30 text-mg-accent">
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

    <?php if (!empty($timeline)) { ?>
    <!-- 타임라인 -->
    <div class="lore-timeline">
        <?php foreach ($timeline as $era) { ?>
        <!-- 시대 구분자 -->
        <div class="lore-era-divider">
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary px-5 py-3 era-badge-card">
                <h2 class="text-lg font-bold text-mg-accent"><?php echo htmlspecialchars($era['le_name']); ?></h2>
                <?php if (!empty($era['le_period'])) { ?>
                <p class="text-sm text-mg-text-muted mt-0.5"><?php echo htmlspecialchars($era['le_period']); ?></p>
                <?php } ?>
                <?php if (!empty($era['le_desc'])) { ?>
                <p class="text-sm text-mg-text-secondary mt-1"><?php echo nl2br(htmlspecialchars($era['le_desc'])); ?></p>
                <?php } ?>
            </div>
        </div>

        <!-- 시대의 이벤트들 -->
        <?php if (!empty($era['events'])) { ?>
        <?php foreach ($era['events'] as $event) {
            $is_major = !empty($event['lv_is_major']);
        ?>
        <div class="lore-event <?php echo $is_major ? 'major' : ''; ?>">
            <div class="lore-event-card bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-5">
                <!-- 연도 + 제목 -->
                <div class="flex items-start gap-3 mb-2">
                    <?php if (!empty($event['lv_year'])) { ?>
                    <span class="flex-shrink-0 px-2.5 py-0.5 rounded-md text-xs font-bold <?php echo $is_major ? 'bg-mg-accent/20 text-mg-accent' : 'bg-mg-bg-tertiary text-mg-text-muted'; ?>"><?php echo htmlspecialchars($event['lv_year']); ?></span>
                    <?php } ?>
                    <h3 class="font-semibold <?php echo $is_major ? 'text-mg-accent text-lg' : 'text-mg-text-primary'; ?>"><?php echo htmlspecialchars($event['lv_title']); ?></h3>
                </div>

                <!-- 내용 -->
                <?php if (!empty($event['lv_content'])) { ?>
                <div class="text-sm text-mg-text-secondary leading-relaxed <?php echo !empty($event['lv_year']) ? 'ml-0' : ''; ?>">
                    <?php echo nl2br(htmlspecialchars($event['lv_content'])); ?>
                </div>
                <?php } ?>

                <!-- 이미지 -->
                <?php if (!empty($event['lv_image'])) { ?>
                <div class="mt-3 rounded-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($event['lv_image']); ?>" alt="<?php echo htmlspecialchars($event['lv_title']); ?>" class="w-full rounded-lg">
                </div>
                <?php } ?>

                <!-- 연결된 문서 링크 -->
                <?php if (!empty($event['la_id'])) { ?>
                <div class="mt-3 pt-3 border-t border-mg-bg-tertiary">
                    <a href="<?php echo G5_BBS_URL; ?>/lore_view.php?la_id=<?php echo $event['la_id']; ?>" class="inline-flex items-center gap-1.5 text-sm text-mg-accent hover:underline">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        관련 문서 보기
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <?php } ?>
        <?php } ?>

    </div>
    <?php } else { ?>
    <!-- 빈 상태 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary py-16 px-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
            <i data-lucide="clock" class="w-8 h-8 text-mg-text-muted"></i>
        </div>
        <h3 class="text-lg font-medium text-mg-text-primary mb-2">등록된 타임라인이 없습니다</h3>
        <p class="text-mg-text-muted">아직 세계관 타임라인이 작성되지 않았습니다.</p>
        <a href="<?php echo G5_BBS_URL; ?>/lore.php" class="inline-flex items-center gap-1.5 mt-4 text-sm text-mg-accent hover:underline">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            위키로 돌아가기
        </a>
    </div>
    <?php } ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
