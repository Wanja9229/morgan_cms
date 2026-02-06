<?php
/**
 * Morgan Edition - 전체 캐릭터 목록
 */

include_once('./_common.php');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$g5['title'] = '캐릭터 목록';

// 검색/필터 파라미터
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';  // 검색 필드
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';  // 검색어
$side_id = isset($_GET['side_id']) ? (int)$_GET['side_id'] : 0;
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$sort = isset($_GET['sort']) ? clean_xss_tags($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// WHERE 조건 생성
$where = "c.ch_state = 'approved'";

if ($stx) {
    $stx_escaped = sql_real_escape_string($stx);
    if ($sfl == 'ch_name') {
        $where .= " AND c.ch_name LIKE '%{$stx_escaped}%'";
    } elseif ($sfl == 'mb_nick') {
        $where .= " AND m.mb_nick LIKE '%{$stx_escaped}%'";
    } else {
        $where .= " AND (c.ch_name LIKE '%{$stx_escaped}%' OR m.mb_nick LIKE '%{$stx_escaped}%')";
    }
}

if ($side_id) {
    $where .= " AND c.side_id = {$side_id}";
}

if ($class_id) {
    $where .= " AND c.class_id = {$class_id}";
}

// 정렬
$order = "c.ch_datetime DESC";
switch ($sort) {
    case 'oldest':
        $order = "c.ch_datetime ASC";
        break;
    case 'name':
        $order = "c.ch_name ASC";
        break;
    case 'popular':
        $order = "c.ch_main DESC, c.ch_datetime DESC"; // 추후 조회수 등으로 변경 가능
        break;
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        WHERE {$where}";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$total_page = ceil($total_count / $per_page);

// 캐릭터 목록 조회
$sql = "SELECT c.*, s.side_name, cl.class_name, m.mb_nick
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
        LEFT JOIN {$g5['mg_class_table']} cl ON c.class_id = cl.class_id
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        WHERE {$where}
        ORDER BY {$order}
        LIMIT {$offset}, {$per_page}";
$result = sql_query($sql);

$characters = array();
while ($row = sql_fetch_array($result)) {
    $characters[] = $row;
}

// 세력/종족 목록 (필터용)
$sides = array();
$result = sql_query("SELECT * FROM {$g5['mg_side_table']} WHERE side_use = 1 ORDER BY side_order, side_id");
while ($row = sql_fetch_array($result)) {
    $sides[] = $row;
}

$classes = array();
$result = sql_query("SELECT * FROM {$g5['mg_class_table']} WHERE class_use = 1 ORDER BY class_order, class_id");
while ($row = sql_fetch_array($result)) {
    $classes[] = $row;
}

// 쿼리스트링 생성 함수
function build_query($params = array()) {
    global $sfl, $stx, $side_id, $class_id, $sort;
    $defaults = array(
        'sfl' => $sfl,
        'stx' => $stx,
        'side_id' => $side_id,
        'class_id' => $class_id,
        'sort' => $sort,
    );
    $merged = array_merge($defaults, $params);
    // 빈 값 제거
    $merged = array_filter($merged, function($v) { return $v !== '' && $v !== 0 && $v !== '0'; });
    return http_build_query($merged);
}

include_once(G5_THEME_PATH.'/head.php');
?>

<div class="max-w-6xl mx-auto">
    <!-- 페이지 헤더 -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-mg-text-primary">캐릭터 목록</h1>
        <p class="text-sm text-mg-text-muted mt-1">승인된 캐릭터 <?php echo number_format($total_count); ?>명</p>
    </div>

    <!-- 검색/필터 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 mb-6">
        <form method="get" class="space-y-4">
            <!-- 검색 -->
            <div class="flex flex-col sm:flex-row gap-2">
                <select name="sfl" class="bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary focus:outline-none focus:border-mg-accent sm:w-32">
                    <option value="" <?php echo $sfl == '' ? 'selected' : ''; ?>>전체</option>
                    <option value="ch_name" <?php echo $sfl == 'ch_name' ? 'selected' : ''; ?>>캐릭터명</option>
                    <option value="mb_nick" <?php echo $sfl == 'mb_nick' ? 'selected' : ''; ?>>오너</option>
                </select>
                <div class="flex-1 flex gap-2">
                    <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" placeholder="검색어 입력..."
                           class="flex-1 bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2 text-sm text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent">
                    <button type="submit" class="bg-mg-accent hover:bg-mg-accent-hover text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        검색
                    </button>
                </div>
            </div>

            <!-- 필터 -->
            <div class="flex flex-wrap gap-2 items-center">
                <?php if (count($sides) > 0) { ?>
                <select name="side_id" onchange="this.form.submit()" class="bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-1.5 text-sm text-mg-text-primary focus:outline-none focus:border-mg-accent">
                    <option value="">모든 <?php echo mg_config('side_title', '세력'); ?></option>
                    <?php foreach ($sides as $side) { ?>
                    <option value="<?php echo $side['side_id']; ?>" <?php echo $side_id == $side['side_id'] ? 'selected' : ''; ?>><?php echo $side['side_name']; ?></option>
                    <?php } ?>
                </select>
                <?php } ?>

                <?php if (count($classes) > 0) { ?>
                <select name="class_id" onchange="this.form.submit()" class="bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-1.5 text-sm text-mg-text-primary focus:outline-none focus:border-mg-accent">
                    <option value="">모든 <?php echo mg_config('class_title', '종족'); ?></option>
                    <?php foreach ($classes as $class) { ?>
                    <option value="<?php echo $class['class_id']; ?>" <?php echo $class_id == $class['class_id'] ? 'selected' : ''; ?>><?php echo $class['class_name']; ?></option>
                    <?php } ?>
                </select>
                <?php } ?>

                <div class="flex-1"></div>

                <select name="sort" onchange="this.form.submit()" class="bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-1.5 text-sm text-mg-text-primary focus:outline-none focus:border-mg-accent">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>최신순</option>
                    <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>오래된순</option>
                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>이름순</option>
                </select>
            </div>

            <?php if ($stx || $side_id || $class_id) { ?>
            <div class="flex items-center gap-2 pt-2 border-t border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-muted">필터:</span>
                <?php if ($stx) { ?>
                <a href="?<?php echo build_query(['stx' => '', 'sfl' => '', 'page' => 1]); ?>" class="inline-flex items-center gap-1 bg-mg-bg-tertiary text-mg-text-secondary text-xs px-2 py-1 rounded-full hover:bg-mg-bg-primary transition-colors">
                    "<?php echo htmlspecialchars($stx); ?>"
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                <?php } ?>
                <?php if ($side_id) {
                    $side_name = '';
                    foreach ($sides as $s) { if ($s['side_id'] == $side_id) $side_name = $s['side_name']; }
                ?>
                <a href="?<?php echo build_query(['side_id' => 0, 'page' => 1]); ?>" class="inline-flex items-center gap-1 bg-mg-bg-tertiary text-mg-text-secondary text-xs px-2 py-1 rounded-full hover:bg-mg-bg-primary transition-colors">
                    <?php echo $side_name; ?>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                <?php } ?>
                <?php if ($class_id) {
                    $class_name = '';
                    foreach ($classes as $c) { if ($c['class_id'] == $class_id) $class_name = $c['class_name']; }
                ?>
                <a href="?<?php echo build_query(['class_id' => 0, 'page' => 1]); ?>" class="inline-flex items-center gap-1 bg-mg-bg-tertiary text-mg-text-secondary text-xs px-2 py-1 rounded-full hover:bg-mg-bg-primary transition-colors">
                    <?php echo $class_name; ?>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                <?php } ?>
                <a href="?" class="text-xs text-mg-accent hover:underline ml-2">전체 초기화</a>
            </div>
            <?php } ?>
        </form>
    </div>

    <!-- 캐릭터 그리드 -->
    <?php if (count($characters) > 0) { ?>
    <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
        <?php foreach ($characters as $char) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $char['ch_id']; ?>" class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden hover:border-mg-accent/50 transition-all hover:-translate-y-1 group">
            <!-- 썸네일 -->
            <div class="aspect-square bg-mg-bg-tertiary relative overflow-hidden">
                <?php if ($char['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="<?php echo $char['ch_name']; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <?php } else { ?>
                <div class="w-full h-full flex items-center justify-center text-mg-text-muted">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <?php } ?>

                <!-- 대표 캐릭터 배지 -->
                <?php if ($char['ch_main']) { ?>
                <div class="absolute top-2 left-2">
                    <span class="bg-mg-accent text-white text-xs px-2 py-0.5 rounded-full">대표</span>
                </div>
                <?php } ?>
            </div>

            <!-- 정보 -->
            <div class="p-3">
                <h3 class="font-bold text-mg-text-primary truncate group-hover:text-mg-accent transition-colors"><?php echo $char['ch_name']; ?></h3>
                <div class="flex items-center gap-1 mt-1 text-xs text-mg-text-muted">
                    <?php if ($char['side_name'] || $char['class_name']) { ?>
                    <span class="truncate"><?php echo implode(' · ', array_filter([$char['side_name'], $char['class_name']])); ?></span>
                    <?php } ?>
                </div>
                <p class="text-xs text-mg-text-muted mt-1 truncate">@<?php echo $char['mb_nick']; ?></p>
            </div>
        </a>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) { ?>
    <div class="flex justify-center items-center gap-1 mt-8">
        <?php
        $start_page = max(1, $page - 2);
        $end_page = min($total_page, $page + 2);

        if ($page > 1) {
            echo '<a href="?'.build_query(['page' => 1]).'" class="w-9 h-9 flex items-center justify-center rounded-lg text-mg-text-muted hover:bg-mg-bg-tertiary transition-colors">&laquo;</a>';
            echo '<a href="?'.build_query(['page' => $page - 1]).'" class="w-9 h-9 flex items-center justify-center rounded-lg text-mg-text-muted hover:bg-mg-bg-tertiary transition-colors">&lsaquo;</a>';
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<span class="w-9 h-9 flex items-center justify-center rounded-lg bg-mg-accent text-white font-medium">'.$i.'</span>';
            } else {
                echo '<a href="?'.build_query(['page' => $i]).'" class="w-9 h-9 flex items-center justify-center rounded-lg text-mg-text-secondary hover:bg-mg-bg-tertiary transition-colors">'.$i.'</a>';
            }
        }

        if ($page < $total_page) {
            echo '<a href="?'.build_query(['page' => $page + 1]).'" class="w-9 h-9 flex items-center justify-center rounded-lg text-mg-text-muted hover:bg-mg-bg-tertiary transition-colors">&rsaquo;</a>';
            echo '<a href="?'.build_query(['page' => $total_page]).'" class="w-9 h-9 flex items-center justify-center rounded-lg text-mg-text-muted hover:bg-mg-bg-tertiary transition-colors">&raquo;</a>';
        }
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <!-- 빈 상태 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary py-16 px-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
            <svg class="w-8 h-8 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <?php if ($stx || $side_id || $class_id) { ?>
        <h3 class="text-lg font-medium text-mg-text-primary mb-2">검색 결과가 없습니다</h3>
        <p class="text-mg-text-muted mb-4">다른 검색어나 필터를 시도해보세요.</p>
        <a href="?" class="inline-flex items-center gap-2 text-mg-accent hover:underline">
            <span>필터 초기화</span>
        </a>
        <?php } else { ?>
        <h3 class="text-lg font-medium text-mg-text-primary mb-2">아직 등록된 캐릭터가 없습니다</h3>
        <p class="text-mg-text-muted">첫 번째 캐릭터가 되어보세요!</p>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
