<?php
/**
 * Morgan Edition - Head (Header)
 *
 * 헤더 영역 (네비게이션, 사이드바 포함)
 */

if (!defined('_GNUBOARD_')) exit;

// concierge_result 게시판 목록 → 의뢰 페이지로 리다이렉트
if (isset($bo_table) && $bo_table === 'concierge_result'
    && basename($_SERVER['SCRIPT_NAME'] ?? '') === 'board.php'
    && !isset($_GET['wr_id']) && empty($_GET['w'])) {
    header('Location: ' . G5_BBS_URL . '/concierge.php?tab=results');
    exit;
}

// head.sub.php 포함 (HTML 시작)
include_once(G5_THEME_PATH.'/head.sub.php');

// 필요한 라이브러리 로드
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');

// 로고 이미지 가져오기
$site_logo = function_exists('mg_config') ? mg_config('site_logo') : '';
$site_name = function_exists('mg_config') ? mg_config('site_name', 'Morgan') : 'Morgan';

// 사이드바 게시판 메뉴용 - community 그룹 게시판 목록
$sql_boards = "SELECT bo_table, bo_subject, bo_skin FROM {$g5['board_table']} WHERE gr_id = 'community' ORDER BY bo_order, bo_table";
$result_boards = sql_query($sql_boards);
$sidebar_boards = array();
while ($row_b = sql_fetch_array($result_boards)) {
    $sidebar_boards[] = $row_b;
}

// 사이드바 세계관 위키용 - 카테고리별 문서 목록
$sidebar_lore_categories = array();
$sidebar_lore_uncategorized = array();
if (function_exists('mg_config') && mg_config('lore_use', '1') == '1') {
    // 카테고리 목록
    $sql_lc = "SELECT lc_id, lc_name FROM {$g5['mg_lore_category_table']} WHERE lc_use = 1 ORDER BY lc_order, lc_id";
    $result_lc = sql_query($sql_lc);
    while ($row_lc = sql_fetch_array($result_lc)) {
        $row_lc['articles'] = array();
        $sidebar_lore_categories[$row_lc['lc_id']] = $row_lc;
    }
    // 문서 → 카테고리별 분배
    $sql_lore = "SELECT la_id, la_title, lc_id FROM {$g5['mg_lore_article_table']} WHERE la_use = 1 ORDER BY la_order ASC, la_id ASC";
    $result_lore = sql_query($sql_lore);
    while ($row_l = sql_fetch_array($result_lore)) {
        if (isset($sidebar_lore_categories[$row_l['lc_id']])) {
            $sidebar_lore_categories[$row_l['lc_id']]['articles'][] = $row_l;
        } else {
            $sidebar_lore_uncategorized[] = $row_l;
        }
    }
}

// 현재 페이지 감지 (게시판/역극/기타)
$_current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
$_is_rp_page = in_array($_current_script, array('rp_list.php', 'rp_close.php', 'rp_reply.php'));
// 역극/의뢰수행 페이지에서는 게시판 포커싱 제거
$_is_concierge_result = (isset($bo_table) && $bo_table === 'concierge_result');
$_is_vent_page = (isset($bo_table) && $bo_table === 'vent');
$_current_bo_table = ($_is_rp_page || $_is_concierge_result || $_is_vent_page || !isset($bo_table)) ? '' : $bo_table;
$_is_mission_page = ($_current_bo_table === 'mission');
$_is_board_page = !empty($_current_bo_table);
$_is_community_section = $_is_board_page && !$_is_mission_page;
$_is_home = ($_current_script === 'index.php' || $_current_script === '' || $_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === G5_URL.'/');
$_is_character_view_from_list = ($_current_script === 'character_view.php' && isset($_GET['from']) && $_GET['from'] === 'list');
$_is_character_page = !$_is_character_view_from_list && in_array($_current_script, array('character.php', 'character_view.php', 'character_edit.php', 'character_form.php'));
$_is_character_list_page = ($_current_script === 'character_list.php') || $_is_character_view_from_list;
$_is_shop_page = in_array($_current_script, array('shop.php', 'shop_view.php', 'shop_buy.php', 'shop_gift.php'));
$_is_new_page = ($_current_script === 'new.php');
$_is_notification_page = ($_current_script === 'notification.php');
$_is_inventory_page = ($_current_script === 'inventory.php');
$_is_concierge_page = in_array($_current_script, array('concierge.php', 'concierge_view.php', 'concierge_write.php')) || $_is_concierge_result;
$_is_pioneer_page = ($_current_script === 'pioneer.php');
$_is_battle_page = ($_current_script === 'battle.php');
$_is_training_page = ($_current_script === 'training.php');
$_is_mypage = in_array($_current_script, array('mypage.php', 'seal_edit.php'));
$_is_lore_page = in_array($_current_script, array('lore.php', 'lore_view.php', 'lore_timeline.php', 'lore_map.php'));
$_current_la_id = ($_current_script === 'lore_view.php' && isset($_GET['la_id'])) ? (int)$_GET['la_id'] : 0;

// 역극/미션 사용 여부
$_show_rp = function_exists('mg_config') ? mg_config('rp_use', '1') : '1';
$_show_mission = function_exists('mg_config') ? mg_config('prompt_enable', '1') : '1';
$_show_concierge = function_exists('mg_config') ? mg_config('concierge_use', '1') : '1';
$_show_battle = function_exists('mg_config') ? mg_config('battle_use', '1') : '1';
$_show_training = ($_show_battle === '1' && (function_exists('mg_config') ? mg_config('battle_training_use', '1') : '1') === '1');

// 개척 시스템: 유저 스테미나
$_user_stamina = null;
if ($is_member && function_exists('mg_pioneer_enabled') && mg_pioneer_enabled()) {
    $_user_stamina = mg_get_stamina($member['mb_id']);
}

// 현재 접속자
$_show_connect = function_exists('mg_config') ? mg_config('show_connect_count', '1') : '1';
$_connect_count = 0;
$_connect_members = array();
$_connect_guest_count = 0;
if ($_show_connect == '1') {
    // 현재 사용자 접속 기록 즉시 갱신 (그누보드 run()은 페이지 끝에 실행되므로)
    $_my_ip = $_SERVER['REMOTE_ADDR'];
    $_my_mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';
    $_lo_exists = sql_fetch("SELECT count(*) as cnt FROM {$g5['login_table']} WHERE lo_ip = '{$_my_ip}'");
    if ($_lo_exists['cnt']) {
        sql_query("UPDATE {$g5['login_table']} SET mb_id = '".sql_real_escape_string($_my_mb_id)."', lo_datetime = '".G5_TIME_YMDHIS."' WHERE lo_ip = '{$_my_ip}'", false);
    } else {
        sql_query("INSERT INTO {$g5['login_table']} (lo_ip, mb_id, lo_datetime, lo_location, lo_url) VALUES ('{$_my_ip}', '".sql_real_escape_string($_my_mb_id)."', '".G5_TIME_YMDHIS."', '', '')", false);
    }

    // cf_login_minutes 기준으로 활동 중인 접속자만 카운트 (그누보드 설정 연동)
    $_connect_minutes = isset($config['cf_login_minutes']) ? max(1, (int)$config['cf_login_minutes']) : 10;
    $_connect_cutoff = date('Y-m-d H:i:s', G5_SERVER_TIME - (60 * $_connect_minutes));
    $_connect_row = sql_fetch("SELECT count(*) as cnt FROM {$g5['login_table']} WHERE lo_datetime >= '{$_connect_cutoff}'");
    $_connect_count = (int)$_connect_row['cnt'];
    if ($_connect_count > 0) {
        $sql_conn = "SELECT l.mb_id, l.lo_datetime, COALESCE(m.mb_nick, '') as mb_nick
                     FROM {$g5['login_table']} l
                     LEFT JOIN {$g5['member_table']} m ON l.mb_id = m.mb_id
                     WHERE l.lo_datetime >= '{$_connect_cutoff}'
                     ORDER BY l.lo_datetime DESC";
        $result_conn = sql_query($sql_conn);
        while ($row_conn = sql_fetch_array($result_conn)) {
            if ($row_conn['mb_id']) {
                $_connect_members[] = $row_conn;
            } else {
                $_connect_guest_count++;
            }
        }
    }
}

// SPA-like: AJAX 요청이면 레이아웃 건너뛰기
if (isset($is_ajax_request) && $is_ajax_request) {
    echo '<div id="ajax-content">';
    return;
}
?>

<!-- Header -->
<header class="bg-mg-bg-secondary h-12 flex items-center justify-between px-4 border-b border-mg-bg-tertiary fixed top-0 left-0 right-0 z-50">
    <!-- Logo -->
    <div class="flex items-center gap-4">
        <button id="sidebar-toggle" class="lg:hidden text-mg-text-secondary hover:text-mg-text-primary p-3" type="button" aria-label="메뉴">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <a href="<?php echo G5_URL; ?>" class="flex items-center gap-2 text-mg-accent font-bold text-lg hover:text-mg-accent-hover transition-colors">
            <?php if ($site_logo) { ?>
                <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_name); ?>" class="h-7 max-w-[160px] object-contain">
            <?php } else { ?>
                <span><?php echo htmlspecialchars($site_name); ?></span>
            <?php } ?>
        </a>
    </div>

    <!-- Search (Desktop) -->
    <div class="hidden lg:flex flex-1 max-w-md mx-4">
        <form name="fsearchbox" method="get" action="<?php echo G5_BBS_URL ?>/search.php" class="w-full">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <input type="text"
                   name="stx"
                   id="header-search"
                   placeholder="검색..."
                   maxlength="20"
                   class="w-full bg-mg-bg-primary text-mg-text-primary placeholder-mg-text-muted rounded px-4 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-mg-accent">
        </form>
    </div>

    <!-- User Menu -->
    <nav class="flex items-center gap-2">
        <?php if ($_show_connect == '1') { ?>
        <!-- 접속자 수 -->
        <div class="relative" id="mg-connect-wrap">
            <button type="button" id="mg-connect-toggle" class="text-xs bg-mg-accent/15 text-mg-accent rounded-full px-2.5 py-1 hidden sm:inline-flex items-center gap-1 hover:bg-mg-accent/25 transition-colors cursor-pointer" title="현재 접속자 목록">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/></svg>
                접속 <?php echo $_connect_count; ?>
            </button>
            <!-- 접속자 드롭다운 -->
            <div id="mg-connect-panel" class="hidden absolute right-0 top-full mt-2 w-72 bg-mg-bg-secondary border border-mg-bg-tertiary rounded-lg shadow-xl z-50 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-mg-bg-tertiary">
                    <h3 class="text-sm font-semibold text-mg-text-primary">현재 접속자 <span class="text-mg-accent"><?php echo $_connect_count; ?></span>명</h3>
                </div>
                <div class="max-h-64 overflow-y-auto divide-y divide-mg-bg-tertiary">
                    <?php if (count($_connect_members) > 0) { ?>
                    <?php foreach ($_connect_members as $cm) {
                        $cm_time = date('H:i', strtotime($cm['lo_datetime']));
                    ?>
                    <div class="flex items-center justify-between px-4 py-2">
                        <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($cm['mb_nick'] ?: $cm['mb_id']); ?></span>
                        <span class="text-xs text-mg-text-muted"><?php echo $cm_time; ?></span>
                    </div>
                    <?php } ?>
                    <?php } ?>
                    <?php if ($_connect_guest_count > 0) { ?>
                    <div class="flex items-center justify-between px-4 py-2">
                        <span class="text-sm text-mg-text-muted">비회원</span>
                        <span class="text-xs text-mg-text-muted"><?php echo $_connect_guest_count; ?>명</span>
                    </div>
                    <?php } ?>
                    <?php if ($_connect_count == 0) { ?>
                    <div class="px-4 py-6 text-center text-sm text-mg-text-muted">접속자가 없습니다.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>
        <?php if ($is_member) { ?>
            <!-- 로그인 상태 -->
            <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php'); ?>" class="text-sm text-mg-text-secondary hover:text-mg-text-primary transition-colors px-2 py-1.5 hidden sm:inline">
                <?php echo get_text($member['mb_nick']); ?>님
            </a>
            <?php if ($is_admin) { ?>
            <a href="<?php echo G5_ADMIN_URL; ?>/morgan/config.php" class="text-sm text-mg-accent hover:text-mg-accent-hover transition-colors px-2 py-1.5 hidden sm:inline">
                관리자
            </a>
            <?php } ?>
            <!-- 알림 벨 -->
            <?php
            $mg_noti_count = mg_get_unread_notification_count($member['mb_id']);
            ?>
            <div class="relative" id="mg-noti-wrap">
                <button type="button" id="mg-noti-toggle" class="relative text-mg-text-secondary hover:text-mg-text-primary transition-colors p-1.5" title="알림">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span id="mg-noti-badge" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-mg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center <?php echo $mg_noti_count > 0 ? '' : 'hidden'; ?>"><?php echo $mg_noti_count > 99 ? '99+' : $mg_noti_count; ?></span>
                </button>

                <!-- 알림 드롭다운 패널 -->
                <div id="mg-noti-panel" class="hidden absolute right-0 top-full mt-2 w-80 bg-mg-bg-secondary border border-mg-bg-tertiary rounded-lg shadow-xl z-50 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-mg-bg-tertiary">
                        <h3 class="text-sm font-semibold text-mg-text-primary">알림</h3>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="MgNoti.readAll()" class="text-xs text-mg-accent hover:text-mg-accent-hover">전체 읽음</button>
                        </div>
                    </div>
                    <div id="mg-noti-list" class="max-h-80 overflow-y-auto divide-y divide-mg-bg-tertiary">
                        <div class="py-8 text-center text-mg-text-muted text-sm">알림이 없습니다.</div>
                    </div>
                    <div class="border-t border-mg-bg-tertiary px-4 py-2.5 text-center">
                        <a href="<?php echo G5_BBS_URL; ?>/notification.php" class="text-xs text-mg-accent hover:text-mg-accent-hover" onclick="if(window.MgNoti)MgNoti.close();">모든 알림 보기</a>
                    </div>
                </div>
            </div>
            <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="hidden lg:block text-sm text-mg-text-muted hover:text-mg-text-primary transition-colors px-3 py-1.5">
                로그아웃
            </a>
            <!-- 우측 사이드바 토글 (모바일/태블릿) -->
            <button id="widget-toggle" class="lg:hidden text-mg-text-secondary hover:text-mg-text-primary p-1.5" type="button" aria-label="내 정보">
                <i data-lucide="user" class="w-5 h-5"></i>
            </button>
        <?php } else { ?>
            <!-- 비로그인 상태 -->
            <a href="<?php echo G5_BBS_URL; ?>/login.php" class="text-sm text-mg-text-secondary hover:text-mg-text-primary transition-colors px-3 py-1.5">
                로그인
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/register.php" class="text-sm bg-mg-accent hover:bg-mg-accent-hover text-white rounded px-3 py-1.5 transition-colors hidden lg:block">
                회원가입
            </a>
            <!-- 우측 사이드바 토글 (모바일/태블릿) -->
            <button id="widget-toggle" class="lg:hidden text-mg-text-secondary hover:text-mg-text-primary p-1.5" type="button" aria-label="메뉴">
                <i data-lucide="user" class="w-5 h-5"></i>
            </button>
        <?php } ?>
    </nav>
</header>

<?php if ($_show_connect == '1') { ?>
<script>
(function() {
    var btn = document.getElementById('mg-connect-toggle');
    var panel = document.getElementById('mg-connect-panel');
    if (!btn || !panel) return;
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.classList.toggle('hidden');
    });
    document.addEventListener('click', function(e) {
        if (!panel.classList.contains('hidden') && !panel.contains(e.target)) {
            panel.classList.add('hidden');
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') panel.classList.add('hidden');
    });
})();
</script>
<?php } ?>

<!-- Main Layout -->
<div class="flex flex-1 pt-12">

    <!-- Sidebar -->
    <aside id="sidebar" class="w-14 bg-mg-bg-secondary fixed left-0 top-12 bottom-0 hidden lg:flex flex-col items-center py-3 gap-2 border-r border-mg-bg-tertiary z-40 overflow-y-auto">
        <!-- 홈 -->
        <a href="<?php echo G5_URL; ?>" class="sidebar-icon group <?php echo $_is_home ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="홈" data-sidebar-id="home">
            <i data-lucide="home" class="w-6 h-6"></i>
        </a>

        <div class="w-8 h-px bg-mg-bg-tertiary my-1"></div>

        <!-- 세계관 위키 (2뎁스) -->
        <?php if (mg_config('lore_use', '1') == '1') { ?>
        <button id="sidebar-lore-toggle" class="sidebar-icon group <?php echo $_is_lore_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="세계관 위키" type="button" data-sidebar-id="lore">
            <i data-lucide="book-open" class="w-6 h-6"></i>
        </button>
        <?php } ?>

        <!-- 캐릭터 목록 -->
        <a href="<?php echo G5_BBS_URL; ?>/character_list.php" class="sidebar-icon group <?php echo $_is_character_list_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="캐릭터 목록" data-sidebar-id="character_list">
            <i data-lucide="users" class="w-6 h-6"></i>
        </a>

        <div class="w-8 h-px bg-mg-bg-tertiary my-1"></div>

        <!-- 게시판 메뉴 (2뎁스) -->
        <button id="sidebar-board-toggle" class="sidebar-icon group <?php echo $_is_community_section ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="게시판" type="button" data-sidebar-id="board">
            <i data-lucide="square-pen" class="w-6 h-6"></i>
        </button>

        <!-- 역극 -->
        <?php if ($_show_rp == '1') { ?>
        <a href="<?php echo G5_BBS_URL; ?>/rp_list.php" class="sidebar-icon group <?php echo $_is_rp_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="역극" data-sidebar-id="rp">
            <i data-lucide="message-circle" class="w-6 h-6"></i>
        </a>
        <?php } ?>

        <!-- 미션 -->
        <?php if ($_show_mission == '1') { ?>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=mission" class="sidebar-icon group <?php echo $_is_mission_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="미션" data-sidebar-id="mission">
            <i data-lucide="clipboard-check" class="w-6 h-6"></i>
        </a>
        <?php } ?>

        <!-- 의뢰 -->
        <?php if ($_show_concierge == '1') { ?>
        <a href="<?php echo G5_BBS_URL; ?>/concierge.php" class="sidebar-icon group <?php echo $_is_concierge_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="의뢰" data-sidebar-id="concierge">
            <i data-lucide="briefcase" class="w-6 h-6"></i>
        </a>
        <?php } ?>

        <!-- 앓이란 -->
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=vent" class="sidebar-icon group <?php echo $_is_vent_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="앓이란" data-sidebar-id="vent">
            <i data-lucide="sticky-note" class="w-6 h-6"></i>
        </a>

        <!-- 상점 -->
        <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="sidebar-icon group <?php echo $_is_shop_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="상점" data-sidebar-id="shop">
            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
        </a>

        <div class="w-8 h-px bg-mg-bg-tertiary my-1"></div>

        <!-- 전투 -->
        <?php if ($_show_battle == '1') { ?>
        <a href="<?php echo G5_BBS_URL; ?>/battle.php" class="sidebar-icon group <?php echo $_is_battle_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="전투" data-sidebar-id="battle">
            <i data-lucide="swords" class="w-6 h-6"></i>
        </a>
        <?php } ?>
        <!-- 수업 스케줄 -->
        <?php if ($_show_training) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/training.php" class="sidebar-icon group <?php echo $_is_training_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="수업 스케줄" data-sidebar-id="training">
            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
        </a>
        <?php } ?>

        <!-- 개척 -->
        <?php if ($is_member && $_user_stamina) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="sidebar-icon group relative <?php echo $_is_pioneer_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="개척 (스테미나: <?php echo $_user_stamina['current']; ?>/<?php echo $_user_stamina['max']; ?>)" data-sidebar-id="pioneer">
            <i data-lucide="landmark" class="w-6 h-6"></i>
            <?php if ($_user_stamina['current'] > 0) { ?>
            <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-mg-success text-white text-[9px] font-bold rounded-full flex items-center justify-center"><?php echo $_user_stamina['current']; ?></span>
            <?php } ?>
        </a>
        <?php } ?>

        <!-- 구분선 -->
        <div class="flex-1"></div>

        <div class="w-8 h-px bg-mg-bg-tertiary my-1"></div>

        <!-- 알림 -->
        <a href="<?php echo G5_BBS_URL; ?>/notification.php" class="sidebar-icon group <?php echo $_is_notification_page ? '!bg-mg-accent !text-white !rounded-xl' : ''; ?>" title="알림" data-sidebar-id="notification">
            <i data-lucide="bell" class="w-6 h-6"></i>
        </a>

        <!-- 설정 (관리자만) -->
        <?php if ($is_admin) { ?>
        <a href="<?php echo G5_ADMIN_URL; ?>/morgan/config.php" class="sidebar-icon group" title="관리자">
            <i data-lucide="settings" class="w-6 h-6"></i>
        </a>
        <?php } ?>
    </aside>

    <!-- Mobile Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 hidden" style="z-index:35"></div>

    <!-- Board Submenu Panel (2뎁스) -->
    <div id="sidebar-board-panel" class="fixed left-14 top-12 bottom-0 w-48 bg-mg-bg-secondary border-r border-mg-bg-tertiary transform <?php echo $_is_community_section ? 'translate-x-0 opacity-100 pointer-events-auto' : '-translate-x-full opacity-0 pointer-events-none'; ?> transition-all duration-200 ease-in-out flex flex-col" style="z-index:38">
        <div class="px-3 pt-3 pb-2">
            <h3 class="text-xs font-semibold text-mg-text-muted uppercase tracking-wider">게시판</h3>
        </div>
        <nav class="flex-1 overflow-y-auto px-2 pb-3">
            <div class="space-y-0.5">
            <?php foreach ($sidebar_boards as $sb) {
                if (in_array($sb['bo_table'], array('mission', 'concierge_result', 'vent'))) continue;
                $bo_url = G5_BBS_URL . '/board.php?bo_table=' . $sb['bo_table'];
                $is_current = ($_current_bo_table === $sb['bo_table']);
                $active_class = $is_current
                    ? 'bg-mg-accent/15 text-mg-text-primary font-medium'
                    : 'text-mg-text-secondary hover:bg-mg-bg-tertiary/50 hover:text-mg-text-primary';
            ?>
            <a href="<?php echo $bo_url; ?>"
               class="flex items-center gap-2 px-2 py-1.5 rounded text-sm transition-colors <?php echo $active_class; ?>">
                <span class="<?php echo $is_current ? 'text-mg-accent' : 'text-mg-text-muted'; ?> text-xs font-bold">#</span>
                <span class="truncate"><?php echo $sb['bo_subject']; ?></span>
                <?php if ($is_current) { ?><span class="ml-auto w-1 h-1 rounded-full bg-mg-accent"></span><?php } ?>
            </a>
            <?php } ?>
            </div>

        </nav>
    </div>

    <script>
    (function() {
        var toggle = document.getElementById('sidebar-board-toggle');
        var panel = document.getElementById('sidebar-board-panel');
        if (!toggle || !panel) return;

        var isCommunityPage = <?php echo $_is_community_section ? 'true' : 'false'; ?>;
        var isOpen = isCommunityPage;

        function openPanel() {
            if (window.MG_LorePanel) window.MG_LorePanel.close();
            isOpen = true;
            panel.classList.remove('-translate-x-full', 'opacity-0', 'pointer-events-none');
            panel.classList.add('translate-x-0', 'opacity-100', 'pointer-events-auto');
            toggle.classList.add('!bg-mg-accent', '!text-white', '!rounded-xl');
        }

        function closePanel() {
            isOpen = false;
            panel.classList.add('-translate-x-full', 'opacity-0', 'pointer-events-none');
            panel.classList.remove('translate-x-0', 'opacity-100', 'pointer-events-auto');
            if (!isCommunityPage) {
                toggle.classList.remove('!bg-mg-accent', '!text-white', '!rounded-xl');
            }
        }

        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isOpen) {
                closePanel();
                sessionStorage.setItem('mg_board_panel', 'closed');
            } else {
                openPanel();
                sessionStorage.setItem('mg_board_panel', 'open');
            }
        });

        // 외부 클릭 시 패널 닫기
        document.addEventListener('click', function(e) {
            if (!isOpen) return;
            if (panel.contains(e.target) || toggle.contains(e.target)) return;
            var sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.contains(e.target)) return;
            closePanel();
            sessionStorage.setItem('mg_board_panel', 'closed');
        });

        // 초기 로드 시: 모바일에서는 패널 닫기, 데스크탑에서는 저장된 상태 복원
        if (window.innerWidth < 1024) {
            if (isOpen) closePanel();
        } else if (isCommunityPage) {
            var _bpStored = sessionStorage.getItem('mg_board_panel');
            if (_bpStored === 'closed') {
                closePanel();
            }
        }

        window.MG_BoardPanel = {
            open: openPanel,
            close: closePanel,
            setCommunityPage: function(val) { isCommunityPage = val; },
            isOpen: function() { return isOpen; }
        };
    })();
    </script>

    <!-- Lore Submenu Panel (2뎁스) -->
    <?php if (mg_config('lore_use', '1') == '1') { ?>
    <div id="sidebar-lore-panel" class="fixed left-14 top-12 bottom-0 w-48 bg-mg-bg-secondary border-r border-mg-bg-tertiary transform <?php echo $_is_lore_page ? 'translate-x-0 opacity-100 pointer-events-auto' : '-translate-x-full opacity-0 pointer-events-none'; ?> transition-all duration-200 ease-in-out flex flex-col" style="z-index:38">
        <div class="px-3 pt-3 pb-2">
            <h3 class="text-xs font-semibold text-mg-text-muted uppercase tracking-wider">세계관</h3>
        </div>
        <nav class="flex-1 overflow-y-auto px-2 pb-3">
            <!-- 전체 / 연대기 / 지도 -->
            <div class="space-y-0.5">
                <?php
                $lore_all_active = ($_current_script === 'lore.php')
                    ? 'bg-mg-accent/15 text-mg-text-primary font-medium'
                    : 'text-mg-text-secondary hover:bg-mg-bg-tertiary/50 hover:text-mg-text-primary';
                $tl_active = ($_current_script === 'lore_timeline.php')
                    ? 'bg-mg-accent/15 text-mg-text-primary font-medium'
                    : 'text-mg-text-secondary hover:bg-mg-bg-tertiary/50 hover:text-mg-text-primary';
                $map_active = ($_current_script === 'lore_map.php')
                    ? 'bg-mg-accent/15 text-mg-text-primary font-medium'
                    : 'text-mg-text-secondary hover:bg-mg-bg-tertiary/50 hover:text-mg-text-primary';
                ?>
                <a href="<?php echo G5_BBS_URL; ?>/lore.php" data-lore-page="lore.php"
                   class="lp-item flex items-center gap-2 px-2 py-1.5 rounded text-sm transition-colors <?php echo $lore_all_active; ?>">
                    <span class="lp-icon <?php echo ($_current_script === 'lore.php') ? 'text-mg-accent' : 'text-mg-text-muted'; ?> text-xs font-bold">#</span>
                    <span class="truncate">전체</span>
                    <span class="lp-dot ml-auto w-1 h-1 rounded-full bg-mg-accent <?php echo ($_current_script !== 'lore.php') ? 'hidden' : ''; ?>"></span>
                </a>
                <a href="<?php echo G5_BBS_URL; ?>/lore_timeline.php" data-lore-page="lore_timeline.php"
                   class="lp-item flex items-center gap-2 px-2 py-1.5 rounded text-sm transition-colors <?php echo $tl_active; ?>">
                    <svg class="lp-icon w-3.5 h-3.5 flex-shrink-0 <?php echo ($_current_script === 'lore_timeline.php') ? 'text-mg-accent' : 'text-mg-text-muted'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="truncate">연대기</span>
                    <span class="lp-dot ml-auto w-1 h-1 rounded-full bg-mg-accent <?php echo ($_current_script !== 'lore_timeline.php') ? 'hidden' : ''; ?>"></span>
                </a>
                <?php if (mg_config('lore_map_image', '')) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/lore_map.php" data-lore-page="lore_map.php"
                   class="lp-item flex items-center gap-2 px-2 py-1.5 rounded text-sm transition-colors <?php echo $map_active; ?>">
                    <svg class="lp-icon w-3.5 h-3.5 flex-shrink-0 <?php echo ($_current_script === 'lore_map.php') ? 'text-mg-accent' : 'text-mg-text-muted'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <span class="truncate">지도</span>
                    <span class="lp-dot ml-auto w-1 h-1 rounded-full bg-mg-accent <?php echo ($_current_script !== 'lore_map.php') ? 'hidden' : ''; ?>"></span>
                </a>
                <?php } ?>
            </div>

            <!-- 카테고리별 문서 -->
            <?php foreach ($sidebar_lore_categories as $slc) {
                if (empty($slc['articles'])) continue;
            ?>
            <div class="my-2 mx-1 border-t border-mg-bg-tertiary"></div>
            <div class="space-y-0.5">
                <h4 class="text-[10px] font-semibold text-mg-text-muted uppercase tracking-wider px-2 py-1"><?php echo htmlspecialchars($slc['lc_name']); ?></h4>
                <?php foreach ($slc['articles'] as $sla) {
                    $is_current_lore = ($_current_la_id == (int)$sla['la_id']);
                    $lore_active_class = $is_current_lore
                        ? 'bg-mg-accent/15 text-mg-text-primary font-medium'
                        : 'text-mg-text-secondary hover:bg-mg-bg-tertiary/50 hover:text-mg-text-primary';
                ?>
                <a href="<?php echo G5_BBS_URL; ?>/lore_view.php?la_id=<?php echo $sla['la_id']; ?>" data-lore-page="lore_view.php" data-lore-id="<?php echo $sla['la_id']; ?>"
                   class="lp-item flex items-center gap-2 px-2 py-1.5 rounded text-sm transition-colors <?php echo $lore_active_class; ?>">
                    <svg class="lp-icon w-3.5 h-3.5 flex-shrink-0 <?php echo $is_current_lore ? 'text-mg-accent' : 'text-mg-text-muted'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate"><?php echo htmlspecialchars($sla['la_title']); ?></span>
                    <span class="lp-dot ml-auto w-1 h-1 rounded-full bg-mg-accent <?php echo !$is_current_lore ? 'hidden' : ''; ?>"></span>
                </a>
                <?php } ?>
            </div>
            <?php } ?>
        </nav>
    </div>

    <script>
    (function() {
        var loreToggle = document.getElementById('sidebar-lore-toggle');
        var lorePanel = document.getElementById('sidebar-lore-panel');
        if (!loreToggle || !lorePanel) return;

        var isLorePage = <?php echo $_is_lore_page ? 'true' : 'false'; ?>;
        var isLoreOpen = isLorePage;

        var ACTIVE_LINK = ['bg-mg-accent/15', 'text-mg-text-primary', 'font-medium'];
        var INACTIVE_LINK = ['text-mg-text-secondary', 'hover:bg-mg-bg-tertiary/50', 'hover:text-mg-text-primary'];

        // URL 기반으로 패널 포커스 동기화
        function updateLorePanelFocus() {
            var path = window.location.pathname;
            var script = path.substring(path.lastIndexOf('/') + 1);
            var params = new URLSearchParams(window.location.search);
            var laId = parseInt(params.get('la_id') || '0');
            var onLorePage = ['lore.php', 'lore_view.php', 'lore_timeline.php', 'lore_map.php'].indexOf(script) !== -1;

            isLorePage = onLorePage;

            if (onLorePage) {
                loreToggle.classList.add('!bg-mg-accent', '!text-white', '!rounded-xl');
            } else {
                loreToggle.classList.remove('!bg-mg-accent', '!text-white', '!rounded-xl');
            }

            lorePanel.querySelectorAll('.lp-item').forEach(function(link) {
                var linkPage = link.getAttribute('data-lore-page');
                var linkId = parseInt(link.getAttribute('data-lore-id') || '0');
                var isActive = false;

                if (linkPage === script) {
                    if (linkPage === 'lore_view.php') {
                        isActive = (linkId === laId && laId > 0);
                    } else {
                        isActive = true;
                    }
                }

                var icon = link.querySelector('.lp-icon');
                var dot = link.querySelector('.lp-dot');

                ACTIVE_LINK.forEach(function(c) { link.classList.remove(c); });
                INACTIVE_LINK.forEach(function(c) { link.classList.remove(c); });

                if (isActive) {
                    ACTIVE_LINK.forEach(function(c) { link.classList.add(c); });
                    if (icon) { icon.classList.add('text-mg-accent'); icon.classList.remove('text-mg-text-muted'); }
                    if (dot) dot.classList.remove('hidden');
                } else {
                    INACTIVE_LINK.forEach(function(c) { link.classList.add(c); });
                    if (icon) { icon.classList.remove('text-mg-accent'); icon.classList.add('text-mg-text-muted'); }
                    if (dot) dot.classList.add('hidden');
                }
            });
        }

        function openLore() {
            if (window.MG_BoardPanel) window.MG_BoardPanel.close();
            isLoreOpen = true;
            lorePanel.classList.remove('-translate-x-full', 'opacity-0', 'pointer-events-none');
            lorePanel.classList.add('translate-x-0', 'opacity-100', 'pointer-events-auto');
            loreToggle.classList.add('!bg-mg-accent', '!text-white', '!rounded-xl');
        }

        function closeLore() {
            isLoreOpen = false;
            lorePanel.classList.add('-translate-x-full', 'opacity-0', 'pointer-events-none');
            lorePanel.classList.remove('translate-x-0', 'opacity-100', 'pointer-events-auto');
            if (!isLorePage) {
                loreToggle.classList.remove('!bg-mg-accent', '!text-white', '!rounded-xl');
            }
        }

        loreToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isLoreOpen) {
                closeLore();
                sessionStorage.setItem('mg_lore_panel', 'closed');
            } else {
                openLore();
                sessionStorage.setItem('mg_lore_panel', 'open');
            }
        });

        // 외부 클릭 시 패널 닫기
        document.addEventListener('click', function(e) {
            if (!isLoreOpen) return;
            if (lorePanel.contains(e.target) || loreToggle.contains(e.target)) return;
            var sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.contains(e.target)) return;
            closeLore();
            sessionStorage.setItem('mg_lore_panel', 'closed');
        });

        // 초기 로드 시: 모바일에서는 패널 닫기, 데스크탑에서는 저장된 상태 복원
        if (window.innerWidth < 1024) {
            if (isLoreOpen) closeLore();
        } else if (isLorePage) {
            var _lpStored = sessionStorage.getItem('mg_lore_panel');
            if (_lpStored === 'closed') {
                closeLore();
            }
        }

        window.MG_LorePanel = {
            open: openLore,
            close: closeLore,
            setLorePage: function(val) { isLorePage = val; },
            updateFocus: updateLorePanelFocus,
            isOpen: function() { return isLoreOpen; }
        };

        // 매 페이지 로드 시 URL 기반 포커스 동기화 (캐시/bfcache 대응)
        updateLorePanelFocus();
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) updateLorePanelFocus();
        });
    })();
    </script>
    <?php } ?>

    <!-- Main Content Area -->
    <main id="main-content" class="flex-1 min-w-0 ml-0 lg:ml-14 p-4 md:p-6 lg:mr-72">
