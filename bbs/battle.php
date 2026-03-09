<?php
/**
 * Morgan Edition - 전투 페이지
 *
 * 모드:
 *   list  — 활성 전투(레이드) 목록
 *   view  — 전투 UI (몬스터 + 참여자 + 커맨드)
 *   stat  — 내 전투 프로필 (스탯/장비/스킬)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (mg_config('battle_use', '0') != '1') {
    alert_close('전투 기능이 비활성화되어 있습니다.');
}
if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

$mb_id = $member['mb_id'];
$mode  = isset($_GET['mode']) ? clean_xss_tags($_GET['mode']) : 'list';
if (!in_array($mode, array('list', 'view'))) $mode = 'list';

$be_id = isset($_GET['be_id']) ? (int)$_GET['be_id'] : 0;

// 내 캐릭터 목록 (전투 참여용)
$my_characters = mg_get_usable_characters($mb_id);

// 내 전투 프로필 (첫 번째 캐릭터 기준, 캐릭터 선택 시 변경)
$my_battle_profile = null;
$my_energy = null;
$selected_ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;

if (!empty($my_characters)) {
    // 선택된 캐릭터 또는 첫 번째 캐릭터
    $ch = null;
    if ($selected_ch_id) {
        foreach ($my_characters as $c) {
            if ((int)$c['ch_id'] === $selected_ch_id) { $ch = $c; break; }
        }
    }
    if (!$ch) $ch = $my_characters[0];
    $selected_ch_id = (int)$ch['ch_id'];

    // 스탯 초기화 (없으면 생성)
    mg_battle_init_stat($selected_ch_id, $mb_id);
    mg_battle_init_energy($selected_ch_id, $mb_id);

    $my_battle_profile = mg_battle_get_profile($selected_ch_id, $mb_id);
    $my_energy = mg_battle_get_energy($selected_ch_id);

    // 장비 아이템 정보 로드
    $my_equipment = array('weapon' => null, 'armor' => null, 'accessory' => null);
    if ($my_battle_profile && $my_battle_profile['stat']) {
        $eq_stat = $my_battle_profile['stat'];
        $eq_slots = array('weapon' => (int)($eq_stat['equip_weapon'] ?? 0), 'armor' => (int)($eq_stat['equip_armor'] ?? 0), 'accessory' => (int)($eq_stat['equip_accessory'] ?? 0));
        foreach ($eq_slots as $eq_key => $eq_si_id) {
            if ($eq_si_id > 0) {
                $my_equipment[$eq_key] = sql_fetch("SELECT si_id, si_name, si_desc, si_image, si_effect FROM {$g5['mg_shop_item_table']} WHERE si_id = {$eq_si_id}");
            }
        }
    }
}

// 뷰 모드일 때 전투 데이터 로드
$encounter = null;
$encounter_slots = array();
$encounter_logs = array();
$my_slot = null;

if ($mode === 'view' && $be_id) {
    $encounter = sql_fetch("SELECT * FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
    if (!$encounter) {
        alert_close('전투를 찾을 수 없습니다.');
    }

    // 참여자 목록
    $slot_res = sql_query("SELECT bs.*, s.stat_str, s.stat_dex, s.stat_int, s.stat_con, s.stat_luk
                           FROM {$g5['mg_battle_slot_table']} bs
                           LEFT JOIN {$g5['mg_battle_stat_table']} s ON bs.ch_id = s.ch_id
                           WHERE bs.be_id = {$be_id}
                           ORDER BY bs.total_damage DESC");
    while ($slot = sql_fetch_array($slot_res)) {
        // 캐릭터 이름 조회
        $ch_info = sql_fetch("SELECT ch_name, ch_thumb FROM {$g5['mg_character_table']} WHERE ch_id = {$slot['ch_id']}");
        $slot['ch_name'] = $ch_info ? $ch_info['ch_name'] : '알 수 없음';
        $slot['ch_thumb'] = $ch_info ? $ch_info['ch_thumb'] : '';
        $encounter_slots[] = $slot;

        if ((int)$slot['ch_id'] === $selected_ch_id) {
            $my_slot = $slot;
        }
    }

    // 최근 로그 20건
    $log_res = sql_query("SELECT * FROM {$g5['mg_battle_log_table']}
                          WHERE be_id = {$be_id}
                          ORDER BY bl_id DESC LIMIT 20");
    while ($log = sql_fetch_array($log_res)) {
        $encounter_logs[] = $log;
    }

    // 몬스터 정보
    $monster = sql_fetch("SELECT * FROM {$g5['mg_battle_monster_table']} WHERE bm_id = {$encounter['bm_id']}");
}

$g5['title'] = '전투';
include_once(G5_THEME_PATH.'/head.php');

$api_url = G5_BBS_URL . '/battle_api.php';
?>

<div id="battle-app" class="mx-auto" style="max-width:var(--mg-content-width);">

<?php
$map_image = mg_config('expedition_map_image', '');
$_training_use = mg_config('battle_training_use', '0');
?>
<?php if ($mode === 'list') { ?>
<!-- ═══════════════════════════════════
     전투 로비 (맵 기반)
     ═══════════════════════════════════ -->
<div class="p-4">
    <!-- 헤더: 제목 + 기력 + 네비 버튼들 -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-mg-text-primary flex items-center gap-2">
            <i data-lucide="swords" class="w-5 h-5 text-mg-accent"></i>
            전투
        </h2>
        <?php if ($my_energy) { ?>
        <div class="flex items-center gap-2 text-sm">
            <span class="text-mg-text-muted">기력</span>
            <span class="font-bold" style="color:#22d3ee;"><?php echo $my_energy['current']; ?> / <?php echo $my_energy['max']; ?></span>
            <?php if ($my_energy['current'] < $my_energy['max']) { ?>
            <span class="text-xs text-mg-text-muted">(<?php echo floor($my_energy['next_charge_sec'] / 60); ?>분 후 충전)</span>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 네비게이션 바 -->
    <div class="flex flex-wrap gap-2 mb-4">
        <button type="button" onclick="openBattleProfile()"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-sm bg-mg-bg-secondary border border-mg-bg-tertiary text-mg-text-secondary hover:text-mg-accent transition-colors">
            <i data-lucide="user" class="w-4 h-4"></i>
            전투 프로필
        </button>
    </div>

    <!-- 맵 기반 전투 목록 -->
    <?php if ($map_image) { ?>
    <div class="bg-mg-bg-secondary rounded-lg border border-mg-bg-tertiary overflow-hidden mb-4">
        <div id="battle-map-container" style="position:relative;display:block;max-width:100%;overflow:hidden;">
            <img src="<?php echo htmlspecialchars($map_image); ?>" id="battle-map-image" style="display:block;width:100%;height:auto;" alt="전투 맵" draggable="false">
            <div id="battle-map-markers"></div>
        </div>
    </div>
    <?php } ?>

    <!-- 전투 리스트 (맵 아래에도 카드형으로 표시) -->
    <div id="battle-list" class="space-y-3">
        <div class="text-center py-8 text-mg-text-muted">전투 목록을 불러오는 중...</div>
    </div>
</div>

<style>
.battle-map-marker {
    position: absolute;
    cursor: pointer;
    z-index: 5;
    transition: transform 0.2s;
}
.battle-map-marker:hover {
    transform: scale(1.15);
    z-index: 10;
}
.battle-map-marker img {
    border-radius: 50%;
    border: 2px solid var(--mg-accent);
    background: var(--mg-bg-primary);
    object-fit: cover;
    box-shadow: 0 0 8px rgba(245, 159, 10, 0.4), 0 2px 6px rgba(0,0,0,0.5);
}
.battle-map-marker.status-discovered img {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.4), 0 2px 6px rgba(0,0,0,0.5);
}
.battle-map-marker .marker-count {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 18px;
    height: 18px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid var(--mg-bg-primary);
}
.battle-map-marker .marker-name {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
    font-size: 10px;
    color: var(--mg-text-primary);
    background: rgba(0,0,0,0.75);
    padding: 1px 5px;
    border-radius: 3px;
    margin-top: 2px;
    pointer-events: none;
}
@keyframes bossPulse {
    0%, 100% { box-shadow: 0 0 8px rgba(245, 159, 10, 0.4), 0 2px 6px rgba(0,0,0,0.5); }
    50% { box-shadow: 0 0 16px rgba(245, 159, 10, 0.7), 0 2px 6px rgba(0,0,0,0.5); }
}
.battle-map-marker.status-active img {
    animation: bossPulse 2s ease-in-out infinite;
}
</style>

<?php } elseif ($mode === 'view' && $encounter) { ?>
<!-- ═══════════════════════════════════
     전투 UI
     ═══════════════════════════════════ -->
<div id="battle-view" class="relative" style="min-height:calc(100vh - 4rem);">

    <!-- 보스 HP 바 (상단 고정) -->
    <div class="bg-mg-bg-secondary border-b border-mg-bg-tertiary p-3 flex flex-col items-center">
        <div class="flex items-center gap-2 mb-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-mg-error"></i>
            <span class="font-bold text-mg-accent text-lg" style="font-family:'Bebas Neue',sans-serif; letter-spacing:0.1em;">
                <?php echo htmlspecialchars($monster['bm_name'] ?? ''); ?>
            </span>
            <span class="text-xs text-mg-text-muted"><?php echo $encounter['be_type'] === 'story_boss' ? '스토리 보스' : ($encounter['be_type'] === 'mob_group' ? '몹 그룹' : '보스'); ?></span>
        </div>

        <!-- HP 바 -->
        <div class="w-full relative bg-mg-bg-primary border border-mg-bg-tertiary rounded overflow-hidden" style="max-width:500px; height:18px;">
            <div id="boss-hp-fill" class="h-full rounded transition-all duration-700" style="width:<?php
                $monsters = json_decode($encounter['be_monsters'] ?? '[]', true);
                $total_hp = 0; $total_max = 0;
                if (is_array($monsters)) {
                    foreach ($monsters as $m) { $total_hp += (int)($m['hp'] ?? 0); $total_max += (int)($m['max_hp'] ?? 1); }
                }
                echo $total_max > 0 ? round($total_hp / $total_max * 100, 1) : 0;
            ?>%; background:linear-gradient(90deg, #b91c1c, #ef4444, #f87171);"></div>
            <span id="boss-hp-text" class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white" style="font-family:'Bebas Neue',sans-serif; letter-spacing:0.08em; text-shadow:0 1px 2px rgba(0,0,0,0.6);">
                <?php echo number_format($total_hp); ?> / <?php echo number_format($total_max); ?>
            </span>
        </div>

        <!-- 타이머 -->
        <div class="flex items-center gap-1 mt-1 text-xs text-mg-text-muted">
            <i data-lucide="clock" class="w-3 h-3 text-mg-warning"></i>
            <span id="boss-timer"><?php
                if ($encounter['be_started_at']) {
                    $remaining = (int)$encounter['be_time_limit'] - (time() - strtotime($encounter['be_started_at']));
                    if ($remaining < 0) $remaining = 0;
                    echo sprintf('%02d:%02d:%02d', floor($remaining/3600), floor(($remaining%3600)/60), $remaining%60);
                } else {
                    echo '대기 중';
                }
            ?></span>
        </div>
    </div>

    <div class="flex" style="min-height:calc(100vh - 12rem);">
        <!-- 메인 아레나 -->
        <div class="flex-1 relative flex flex-col">
            <!-- 몬스터 영역 -->
            <div class="flex-1 flex items-center justify-center relative overflow-hidden">
                <!-- 바닥 그리드 -->
                <div class="absolute bottom-0 left-0 right-0" style="height:45%; background:repeating-linear-gradient(90deg, rgba(245,159,10,0.03) 0px, rgba(245,159,10,0.03) 1px, transparent 1px, transparent 60px), repeating-linear-gradient(0deg, rgba(245,159,10,0.02) 0px, rgba(245,159,10,0.02) 1px, transparent 1px, transparent 60px); transform:perspective(500px) rotateX(60deg); transform-origin:bottom center;"></div>

                <div class="relative z-10 text-center">
                    <div id="monster-display" style="width:320px; height:320px; animation:monsterIdle 3s ease-in-out infinite; filter:drop-shadow(0 0 30px rgba(0,0,0,0.4));">
                        <?php if (!empty($monster['bm_image'])) { ?>
                        <img src="<?php echo htmlspecialchars($monster['bm_image']); ?>" alt="" style="width:100%; height:100%; object-fit:contain;">
                        <?php } else { ?>
                        <div class="w-full h-full flex items-center justify-center text-mg-text-muted opacity-30">
                            <i data-lucide="layers" class="w-32 h-32"></i>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- 디버프 표시 -->
                    <div id="monster-debuffs" class="flex gap-1 justify-center mt-2">
                        <?php
                        $debuffs = json_decode($encounter['be_debuffs'] ?? '[]', true);
                        if (is_array($debuffs)) {
                            foreach ($debuffs as $db) {
                                $remaining = max(0, (int)($db['expires_at'] ?? 0) - time());
                                if ($remaining <= 0) continue;
                                $mins = floor($remaining / 60);
                                $secs = $remaining % 60;
                                echo '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold rounded" style="background:rgba(192,132,252,0.15); color:#c084fc; border:1px solid rgba(192,132,252,0.3);">';
                                echo '<i data-lucide="alert-circle" class="w-3 h-3"></i>';
                                echo htmlspecialchars($db['type'] ?? '') . ' ' . $mins . ':' . sprintf('%02d', $secs);
                                echo '</span>';
                            }
                        }
                        ?>
                    </div>

                    <!-- 도발 표시 -->
                    <?php
                    $taunt_queue = json_decode($encounter['taunt_queue'] ?? '[]', true);
                    if (!empty($taunt_queue)) {
                        $taunt = $taunt_queue[0];
                        $taunt_ch = sql_fetch("SELECT ch_name FROM {$g5['mg_character_table']} WHERE ch_id = " . (int)$taunt['ch_id']);
                        echo '<div class="mt-2 inline-flex items-center gap-1 px-3 py-1 text-xs font-bold rounded" style="background:rgba(234,179,8,0.1); color:#eab308; border:1px solid rgba(234,179,8,0.3);">';
                        echo '<i data-lucide="shield" class="w-3 h-3"></i>';
                        echo htmlspecialchars($taunt_ch['ch_name'] ?? '') . ' 도발 중 (' . (int)$taunt['remaining'] . '회 남음)';
                        echo '</div>';
                    }
                    ?>
                </div>

                <!-- 데미지 팝업 컨테이너 -->
                <div id="dmg-popup-container" class="absolute inset-0 pointer-events-none z-50"></div>
            </div>

            <!-- 하단 HUD: 내 캐릭터 + 상태 + 커맨드 -->
            <div class="flex items-end p-2 relative">
                <!-- 내 캐릭터 이미지 -->
                <div class="flex-shrink-0 relative" style="margin-right:0.5rem;">
                    <div style="width:180px; height:360px; margin-top:-200px; filter:drop-shadow(0 4px 20px rgba(0,0,0,0.5));">
                        <?php
                        $my_ch_info = $selected_ch_id ? sql_fetch("SELECT ch_thumb FROM {$g5['mg_character_table']} WHERE ch_id = {$selected_ch_id}") : null;
                        if ($my_ch_info && $my_ch_info['ch_thumb']) { ?>
                        <img src="<?php echo htmlspecialchars($my_ch_info['ch_thumb']); ?>" style="width:100%; height:100%; object-fit:contain; object-position:bottom center;">
                        <?php } else { ?>
                        <div class="w-full h-full flex items-end justify-center text-mg-text-muted opacity-20">
                            <i data-lucide="user" class="w-28"></i>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2" style="width:100px; height:14px; background:radial-gradient(ellipse, rgba(245,159,10,0.15) 0%, transparent 70%); border-radius:50%;"></div>
                </div>

                <!-- 상태 + 커맨드 -->
                <div class="flex-1 min-w-0 flex flex-col gap-1 pb-1">
                    <!-- 내 상태 -->
                    <div>
                        <div class="font-bold text-mg-text-primary text-sm mb-1" style="font-family:'Bebas Neue',sans-serif; letter-spacing:0.1em;">
                            <?php echo htmlspecialchars($ch['ch_name'] ?? ''); ?>
                        </div>
                        <?php if ($my_slot) { ?>
                        <!-- HP -->
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold" style="color:#22c55e; font-family:'Bebas Neue',sans-serif; width:24px; text-align:right;">HP</span>
                            <div class="flex-1 bg-mg-bg-primary border border-mg-bg-tertiary rounded overflow-hidden" style="max-width:280px; height:12px;">
                                <div id="my-hp-fill" class="h-full rounded transition-all duration-500" style="width:<?php echo (int)$my_slot['max_hp'] > 0 ? round((int)$my_slot['current_hp'] / (int)$my_slot['max_hp'] * 100, 1) : 0; ?>%; background:linear-gradient(90deg, #16a34a, #22c55e);"></div>
                            </div>
                            <span id="my-hp-text" class="text-xs font-bold text-mg-text-primary" style="font-family:'Bebas Neue',sans-serif; white-space:nowrap;">
                                <?php echo (int)$my_slot['current_hp']; ?> / <?php echo (int)$my_slot['max_hp']; ?>
                            </span>
                        </div>
                        <?php } ?>
                        <!-- EN -->
                        <?php if ($my_energy) { ?>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold" style="color:#22d3ee; font-family:'Bebas Neue',sans-serif; width:24px; text-align:right;">EN</span>
                            <div class="flex gap-0.5">
                                <?php for ($i = 0; $i < $my_energy['max']; $i++) { ?>
                                <div class="rounded-sm" style="width:12px; height:12px; <?php echo $i < $my_energy['current'] ? 'background:#22d3ee; box-shadow:0 0 4px rgba(34,211,238,0.3);' : 'background:var(--mg-bg-primary); border:1px solid var(--mg-bg-tertiary);'; ?>"></div>
                                <?php } ?>
                            </div>
                            <span class="text-xs font-bold" style="color:#22d3ee; font-family:'Bebas Neue',sans-serif;"><?php echo $my_energy['current']; ?> / <?php echo $my_energy['max']; ?></span>
                            <?php if ($my_energy['current'] < $my_energy['max']) { ?>
                            <span class="text-xs text-mg-text-muted"><?php echo floor($my_energy['next_charge_sec'] / 60); ?>분</span>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- 커맨드 버튼 -->
                    <div id="battle-commands" class="flex gap-1 flex-wrap">
                        <?php if ($my_slot && (int)$my_slot['current_hp'] > 0 && $encounter['be_status'] === 'active') { ?>
                        <button onclick="battleAction('attack')" class="cmd-btn cmd-primary" data-cost="1">
                            <i data-lucide="swords" class="w-4 h-4"></i>
                            ATTACK
                            <span class="cmd-cost-badge">EN 1</span>
                        </button>
                        <button onclick="battleAction('skill')" class="cmd-btn cmd-secondary" data-cost="2">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                            SKILL
                            <span class="cmd-cost-badge">EN 2</span>
                        </button>
                        <button onclick="battleAction('item')" class="cmd-btn cmd-tertiary">
                            <i data-lucide="box" class="w-4 h-4"></i>
                            ITEM
                        </button>
                        <?php } elseif (!$my_slot && $encounter['be_status'] !== 'cleared' && $encounter['be_status'] !== 'failed') { ?>
                        <button onclick="battleJoin()" class="cmd-btn cmd-primary">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            참여하기
                        </button>
                        <?php } ?>
                        <button onclick="toggleLog()" class="cmd-btn cmd-muted">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            LOG
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 우측 패널: 참여자 + 로그 -->
        <div class="bg-mg-bg-secondary border-l border-mg-bg-tertiary flex flex-col overflow-hidden" style="width:300px;">
            <!-- 참여자 -->
            <div class="flex items-center gap-1 px-3 py-2 text-xs font-bold text-mg-accent border-b border-mg-bg-tertiary" style="background:rgba(245,159,10,0.06); font-family:'Bebas Neue',sans-serif; letter-spacing:0.15em;">
                <i data-lucide="users" class="w-3 h-3"></i>
                PARTY <span class="text-mg-text-muted font-normal" style="font-family:'Noto Sans KR',sans-serif;"><?php echo count($encounter_slots); ?>명</span>
            </div>
            <div id="participants-list" class="flex-1 overflow-y-auto" style="scrollbar-width:thin;">
                <?php foreach ($encounter_slots as $slot) {
                    $hp_pct = (int)$slot['max_hp'] > 0 ? round((int)$slot['current_hp'] / (int)$slot['max_hp'] * 100) : 0;
                    $hp_class = $hp_pct > 60 ? '#22c55e' : ($hp_pct > 25 ? '#eab308' : '#ef4444');
                    $is_dead = (int)$slot['current_hp'] <= 0;
                    $is_discoverer = ($slot['mb_id'] === $encounter['discoverer_mb_id']);
                ?>
                <div class="flex items-center gap-2 px-3 py-2 border-b border-white/[0.02] hover:bg-mg-accent/[0.04] transition-colors <?php echo $is_dead ? 'opacity-40' : ''; ?>">
                    <div class="relative flex-shrink-0">
                        <div class="w-8 h-8 rounded-md bg-mg-bg-tertiary flex items-center justify-center overflow-hidden">
                            <?php if ($slot['ch_thumb']) { ?>
                            <img src="<?php echo htmlspecialchars($slot['ch_thumb']); ?>" class="w-full h-full object-cover">
                            <?php } else { ?>
                            <i data-lucide="user" class="w-4 h-4 text-mg-text-muted"></i>
                            <?php } ?>
                        </div>
                        <?php if ($is_discoverer) { ?>
                        <span class="absolute -top-1 -right-1 text-[9px] font-bold bg-mg-accent text-mg-bg-primary px-0.5 rounded" style="line-height:1.3;">발견</span>
                        <?php } ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-mg-text-primary truncate"><?php echo htmlspecialchars($slot['ch_name']); ?></div>
                        <div class="h-1 bg-mg-bg-primary rounded-sm overflow-hidden mt-0.5">
                            <div class="h-full rounded-sm transition-all duration-500" style="width:<?php echo $hp_pct; ?>%; background:<?php echo $hp_class; ?>;"></div>
                        </div>
                        <div class="text-[10px] text-mg-text-muted mt-0.5"><?php echo (int)$slot['current_hp']; ?> / <?php echo (int)$slot['max_hp']; ?><?php echo $is_dead ? ' (전사)' : ''; ?></div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- 전투 로그 -->
            <div id="battle-log-panel" class="border-t border-mg-bg-tertiary" style="max-height:200px; overflow-y:auto; scrollbar-width:thin;">
                <div class="flex items-center gap-1 px-3 py-2 text-xs font-bold text-mg-accent border-b border-mg-bg-tertiary" style="background:rgba(245,159,10,0.06); font-family:'Bebas Neue',sans-serif; letter-spacing:0.15em;">
                    <i data-lucide="file-text" class="w-3 h-3"></i>
                    BATTLE LOG
                </div>
                <div id="log-entries">
                    <?php foreach ($encounter_logs as $log) {
                        $time = date('H:i', strtotime($log['bl_datetime']));
                        $actor_ch = sql_fetch("SELECT ch_name FROM {$g5['mg_character_table']} WHERE ch_id = " . (int)$log['ch_id']);
                    ?>
                    <div class="px-3 py-1 text-[11px] text-mg-text-muted border-b border-white/[0.02] leading-relaxed">
                        <span class="opacity-50" style="font-family:'Bebas Neue',sans-serif;"><?php echo $time; ?></span>
                        <span class="font-bold text-mg-text-primary"><?php echo htmlspecialchars($actor_ch['ch_name'] ?? ''); ?></span>
                        → <?php echo htmlspecialchars($log['bl_action'] ?? ''); ?>
                        <?php if ((int)$log['bl_damage'] > 0 || (int)$log['bl_heal'] > 0) { ?>
                        <span class="font-bold" style="color:<?php echo (int)$log['bl_damage'] > 0 ? '#ef4444' : '#22c55e'; ?>;"><?php echo (int)$log['bl_damage'] > 0 ? '-' . $log['bl_damage'] : '+' . $log['bl_heal']; ?></span>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php } ?>

</div>

<!-- ═══════════════════════════════════
     전투 프로필 모달 (4분할)
     ═══════════════════════════════════ -->
<?php if ($my_battle_profile) {
    $stat = $my_battle_profile['stat'];
    $derived = $my_battle_profile['derived'];
    $_stat_locked_bp = (int)($stat['stat_locked'] ?? 0);
    $_stat_base_bp = (int)mg_config('battle_stat_base', '5');
    $_stat_bonus_bp = (int)mg_config('battle_stat_bonus_points', '15');
    $stat_names = array('stat_hp' => 'HP 체력', 'stat_str' => 'STR 힘', 'stat_dex' => 'DEX 민첩', 'stat_int' => 'INT 지능');
    $equipped = $my_battle_profile['equipped_skills'];
    $derived_labels = array(
        'max_hp' => array('MAX HP', ''),
        'atk' => array('물리공격력', ''),
        'satk' => array('마법공격력', ''),
        'def' => array('방어력', ''),
        'support' => array('지원력', ''),
        'crit_rate' => array('치명타율', '%'),
        'crit_mult' => array('치명타배율', '%'),
        'evasion' => array('회피율', '%'),
    );
    $equip_slot_labels = array('weapon' => array('무기', 'sword'), 'armor' => array('방어구', 'shield'), 'accessory' => array('장신구', 'gem'));
?>
<div id="battle-profile-modal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.65);" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary w-full overflow-hidden" style="max-width:720px;">
            <!-- 헤더 -->
            <div class="px-5 py-3 border-b border-mg-bg-tertiary flex items-center justify-between">
                <h3 class="font-bold text-mg-text-primary flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-mg-accent"></i>
                    전투 프로필
                    <span class="text-sm font-normal text-mg-text-muted">— <?php echo htmlspecialchars($ch['ch_name'] ?? ''); ?></span>
                </h3>
                <button type="button" onclick="document.getElementById('battle-profile-modal').classList.add('hidden')" class="text-mg-text-muted hover:text-mg-text-primary transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- 캐릭터 선택 (다중 캐릭터) -->
            <?php if (count($my_characters) > 1) { ?>
            <div class="px-5 py-2 border-b border-mg-bg-tertiary flex gap-2 flex-wrap" style="background:rgba(0,0,0,0.1);">
                <?php foreach ($my_characters as $c2) { ?>
                <button type="button" onclick="location.href='?mode=<?php echo $mode; ?><?php echo $be_id ? '&be_id='.$be_id : ''; ?>&ch_id=<?php echo $c2['ch_id']; ?>&bp=1'"
                   class="px-2.5 py-1 rounded text-xs border transition-colors <?php echo (int)$c2['ch_id'] === $selected_ch_id ? 'bg-mg-accent text-white border-mg-accent' : 'bg-mg-bg-primary border-mg-bg-tertiary text-mg-text-secondary hover:border-mg-accent'; ?>">
                    <?php echo htmlspecialchars($c2['ch_name']); ?>
                </button>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 4분할 본문 -->
            <div class="p-4 grid gap-3" style="grid-template-columns:1fr 1fr;">

                <!-- ① 기본 스탯 -->
                <div class="bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary p-3">
                    <h4 class="text-xs font-bold text-mg-accent mb-2 flex items-center gap-1.5" style="font-family:'Bebas Neue',sans-serif;letter-spacing:0.1em;">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                        STATS
                        <?php if ($_stat_locked_bp) { ?>
                        <span class="text-mg-text-muted font-normal flex items-center gap-0.5" style="font-family:'Noto Sans KR',sans-serif;"><i data-lucide="lock" class="w-3 h-3"></i>확정</span>
                        <?php } elseif ((int)($stat['stat_points'] ?? 0) > 0) { ?>
                        <span class="text-mg-text-muted font-normal" style="font-family:'Noto Sans KR',sans-serif;">잔여 <span id="bp-stat-remain" class="text-mg-accent"><?php echo (int)($stat['stat_points'] ?? 0); ?></span></span>
                        <?php } ?>
                    </h4>
                    <div class="grid grid-cols-3 gap-1.5">
                        <?php foreach ($stat_names as $key => $label) {
                            $sval = (int)($stat[$key] ?? $_stat_base_bp);
                        ?>
                        <div class="text-center py-1.5 rounded" style="background:rgba(245,159,10,0.04);">
                            <div class="text-[10px] font-bold text-mg-accent" style="font-family:'Bebas Neue',monospace;letter-spacing:0.08em;"><?php echo explode(' ', $label)[0]; ?></div>
                            <?php if (!$_stat_locked_bp && (int)($stat['stat_points'] ?? 0) > 0) { ?>
                            <div class="flex items-center justify-center gap-0.5 my-0.5">
                                <button onclick="bpStatAdjust('<?php echo $key; ?>', -1)" class="w-4 h-4 rounded text-red-400 text-[10px] font-bold hover:bg-red-500/20 leading-none">−</button>
                                <span class="text-sm font-bold text-mg-text-primary bp-stat-val" data-key="<?php echo $key; ?>" data-base="<?php echo $_stat_base_bp; ?>"><?php echo $sval; ?></span>
                                <button onclick="bpStatAdjust('<?php echo $key; ?>', 1)" class="w-4 h-4 rounded text-mg-accent text-[10px] font-bold hover:bg-mg-accent/20 leading-none">+</button>
                            </div>
                            <?php } else { ?>
                            <div class="text-sm font-bold text-mg-text-primary my-0.5"><?php echo $sval; ?></div>
                            <?php } ?>
                            <div class="text-[9px] text-mg-text-muted"><?php echo explode(' ', $label)[1]; ?></div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php if (!$_stat_locked_bp && (int)($stat['stat_points'] ?? 0) > 0) { ?>
                    <div class="mt-2 pt-2 border-t border-mg-bg-tertiary flex items-center justify-between">
                        <span class="text-[10px] text-mg-text-muted">확정 후 변경 불가</span>
                        <button onclick="bpStatSave()" class="px-3 py-1 rounded text-xs font-bold bg-mg-accent text-white hover:bg-mg-accent-hover transition-colors">확정</button>
                    </div>
                    <?php } ?>
                </div>

                <!-- ② 전투 수치 -->
                <div class="bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary p-3">
                    <h4 class="text-xs font-bold text-mg-accent mb-2 flex items-center gap-1.5" style="font-family:'Bebas Neue',sans-serif;letter-spacing:0.1em;">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        COMBAT
                    </h4>
                    <?php foreach ($derived_labels as $key => $info) { ?>
                    <div class="flex items-center justify-between py-1 border-b border-white/[0.03]">
                        <span class="text-[11px] text-mg-text-secondary"><?php echo $info[0]; ?></span>
                        <span class="text-xs font-bold text-mg-text-primary" style="font-family:'Bebas Neue',sans-serif;letter-spacing:0.05em;"><?php echo isset($derived[$key]) ? number_format($derived[$key]) . $info[1] : '-'; ?></span>
                    </div>
                    <?php } ?>
                </div>

                <!-- ③ 장착 스킬 -->
                <div class="bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary p-3">
                    <h4 class="text-xs font-bold text-mg-accent mb-2 flex items-center gap-1.5" style="font-family:'Bebas Neue',sans-serif;letter-spacing:0.1em;">
                        <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                        SKILLS
                    </h4>
                    <?php for ($i = 1; $i <= 3; $i++) {
                        $sk = isset($equipped[$i]) ? $equipped[$i] : null;
                    ?>
                    <div class="flex items-center gap-2 py-1.5 border-b border-white/[0.03]">
                        <span class="w-5 h-5 rounded text-[10px] font-bold flex items-center justify-center" style="background:rgba(245,159,10,0.1);color:var(--mg-accent);font-family:'Bebas Neue',sans-serif;"><?php echo $i; ?></span>
                        <?php if ($sk) { ?>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-mg-text-primary truncate"><?php echo htmlspecialchars($sk['sk_name']); ?></div>
                            <div class="text-[10px] text-mg-text-muted">EN <?php echo (int)$sk['sk_stamina']; ?> · <?php echo htmlspecialchars($sk['sk_type']); ?></div>
                        </div>
                        <?php } else { ?>
                        <span class="text-[11px] text-mg-text-muted">비어 있음</span>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>

                <!-- ④ 장비 아이템 -->
                <div class="bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary p-3">
                    <h4 class="text-xs font-bold text-mg-accent mb-2 flex items-center gap-1.5" style="font-family:'Bebas Neue',sans-serif;letter-spacing:0.1em;">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        EQUIPMENT
                    </h4>
                    <?php foreach ($equip_slot_labels as $eq_key => $eq_info) {
                        $eq_item = $my_equipment[$eq_key];
                    ?>
                    <div class="flex items-center gap-2 py-1.5 border-b border-white/[0.03]">
                        <div class="w-8 h-8 rounded flex-shrink-0 flex items-center justify-center overflow-hidden" style="background:rgba(245,159,10,0.06);border:1px solid var(--mg-bg-tertiary);">
                            <?php if ($eq_item && !empty($eq_item['si_image'])) { ?>
                            <img src="<?php echo htmlspecialchars($eq_item['si_image']); ?>" class="w-full h-full object-cover" alt="">
                            <?php } else { ?>
                            <i data-lucide="<?php echo $eq_info[1]; ?>" class="w-4 h-4 text-mg-text-muted" style="opacity:0.3;"></i>
                            <?php } ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <?php if ($eq_item) { ?>
                            <div class="text-xs font-bold text-mg-text-primary truncate"><?php echo htmlspecialchars($eq_item['si_name']); ?></div>
                            <?php
                            $eq_effect = json_decode($eq_item['si_effect'] ?? '{}', true);
                            if (is_array($eq_effect) && !empty($eq_effect)) {
                                $fx_parts = array();
                                $fx_names = array('atk'=>'공격','satk'=>'마공','def'=>'방어','hp'=>'HP','crit_rate'=>'치명타','evasion'=>'회피','support_power'=>'지원');
                                foreach ($eq_effect as $fk => $fv) {
                                    if (isset($fx_names[$fk]) && (int)$fv != 0) {
                                        $fx_parts[] = $fx_names[$fk] . ($fv > 0 ? '+' : '') . $fv;
                                    }
                                }
                                if ($fx_parts) {
                                    echo '<div class="text-[10px] text-mg-text-muted truncate">' . implode(' · ', $fx_parts) . '</div>';
                                }
                            }
                            ?>
                            <?php } else { ?>
                            <div class="text-[11px] text-mg-text-muted"><?php echo $eq_info[0]; ?> — 비어 있음</div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>
</div>
<?php } ?>

<style>
@keyframes monsterIdle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}
@keyframes dmgPop {
    0% { transform: rotate(-6deg) scale(0); opacity: 0; }
    15% { transform: rotate(-4deg) scale(1.2); opacity: 1; }
    30% { transform: rotate(-6deg) scale(1); }
    80% { opacity: 1; }
    100% { transform: rotate(-6deg) scale(1) translateY(-25px); opacity: 0; }
}
.cmd-btn {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.4rem 1rem;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    position: relative;
    transition: all 0.15s;
    clip-path: polygon(6% 0, 100% 0, 94% 100%, 0 100%);
}
.cmd-primary { background: var(--mg-accent); color: var(--mg-bg-primary); }
.cmd-primary:hover { filter: brightness(1.15); }
.cmd-secondary { background: var(--mg-bg-secondary); border: 1.5px solid var(--mg-accent); color: var(--mg-accent); }
.cmd-secondary:hover { background: var(--mg-accent); color: var(--mg-bg-primary); }
.cmd-tertiary { background: var(--mg-bg-secondary); border: 1.5px solid var(--mg-success); color: var(--mg-success); }
.cmd-tertiary:hover { background: var(--mg-success); color: var(--mg-bg-primary); }
.cmd-muted { background: var(--mg-bg-secondary); border: 1.5px solid var(--mg-text-muted); color: var(--mg-text-muted); }
.cmd-muted:hover { background: var(--mg-bg-tertiary); color: var(--mg-text-primary); }
.cmd-cost-badge {
    position: absolute; top: -4px; right: -4px;
    font-size: 0.55rem; background: #22d3ee; color: var(--mg-bg-primary);
    padding: 0 4px; border-radius: 2px; font-weight: 700;
}
/* 프로필 모달 모바일 반응형 */
@media (max-width: 639px) {
    #battle-profile-modal .p-4.grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
var BATTLE = {
    apiUrl: '<?php echo $api_url; ?>',
    beId: <?php echo $be_id ?: 0; ?>,
    chId: <?php echo $selected_ch_id ?: 0; ?>,
    mode: '<?php echo $mode; ?>',
    pollTimer: null
};

// ── 전투 프로필 모달 ──
function openBattleProfile() {
    var modal = document.getElementById('battle-profile-modal');
    if (modal) {
        modal.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

// ── 전투 목록 로드 ──
function loadBattleList() {
    fetch(BATTLE.apiUrl + '?action=list')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            var el = document.getElementById('battle-list');
            var data = res.data || [];

            // 맵 마커 렌더링
            renderBattleMapMarkers(data);

            if (data.length === 0) {
                el.innerHTML = '<div class="text-center py-12 text-mg-text-muted">' +
                    '<svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.5 17.5L3 6V3h3l11.5 11.5M13 7.5l3.5-3.5 4 4L17 11.5"/></svg>' +
                    '현재 진행 중인 전투가 없습니다.<br><span class="text-xs">파견에서 몬스터가 발견되면 이곳에 표시됩니다.</span></div>';
                return;
            }

            var h = '';
            data.forEach(function(e) {
                var hpPct = e.total_max_hp > 0 ? Math.round(e.total_hp / e.total_max_hp * 100) : 0;
                var hpColor = hpPct > 60 ? '#ef4444' : (hpPct > 25 ? '#eab308' : '#22c55e');
                var statusBadge = e.be_status === 'discovered'
                    ? '<span class="text-xs px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">발견</span>'
                    : e.be_status === 'active'
                    ? '<span class="text-xs px-1.5 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-500/20">전투 중</span>'
                    : '<span class="text-xs px-1.5 py-0.5 rounded bg-gray-500/10 text-gray-400">' + e.be_status + '</span>';

                h += '<a href="?mode=view&be_id=' + e.be_id + '&ch_id=' + BATTLE.chId + '" class="block bg-mg-bg-secondary rounded-lg border border-mg-bg-tertiary hover:border-mg-accent/30 transition-colors overflow-hidden">';
                h += '<div class="flex items-center gap-3 p-3">';
                // 몬스터 썸네일
                if (e.monster_image) {
                    h += '<div style="width:48px;height:48px;flex-shrink:0;border-radius:8px;overflow:hidden;border:1px solid var(--mg-bg-tertiary);">';
                    h += '<img src="' + e.monster_image + '" style="width:100%;height:100%;object-fit:cover;" alt="">';
                    h += '</div>';
                }
                h += '<div class="flex-1 min-w-0">';
                h += '<div class="flex items-center gap-2 mb-1"><span class="font-bold text-mg-text-primary text-sm">' + e.monster_name + '</span>' + statusBadge + '</div>';
                h += '<div class="relative bg-mg-bg-primary rounded overflow-hidden" style="height:6px;">';
                h += '<div class="h-full rounded" style="width:' + hpPct + '%; background:' + hpColor + ';"></div>';
                h += '</div>';
                h += '<div class="flex justify-between mt-1 text-xs text-mg-text-muted">';
                h += '<span>HP ' + hpPct + '%</span>';
                if (e.ea_name) h += '<span>' + e.ea_name + '</span>';
                h += '<span>' + e.slot_count + '명 / ' + (e.time_remaining || '') + '</span>';
                h += '</div>';
                h += '</div>';
                h += '</div>';
                h += '</a>';
            });
            el.innerHTML = h;
        })
        .catch(function() {
            document.getElementById('battle-list').innerHTML = '<div class="text-center py-8 text-mg-text-muted">목록을 불러올 수 없습니다.</div>';
        });
}

// ── 맵에 전투 마커 렌더링 ──
function renderBattleMapMarkers(encounters) {
    var markersEl = document.getElementById('battle-map-markers');
    if (!markersEl) return;
    markersEl.innerHTML = '';

    if (!encounters || encounters.length === 0) return;

    // 좌표별로 그룹핑 (같은 파견지에 여러 보스 가능)
    var grouped = {};
    encounters.forEach(function(e) {
        if (e.ea_map_x == null || e.ea_map_y == null) return;
        var key = e.ea_map_x + ',' + e.ea_map_y;
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(e);
    });

    Object.keys(grouped).forEach(function(key) {
        var group = grouped[key];
        var first = group[0];
        var x = parseFloat(first.ea_map_x);
        var y = parseFloat(first.ea_map_y);
        var sz = 44; // 마커 크기

        if (group.length === 1) {
            // 단일 보스: 썸네일 1개
            var marker = document.createElement('div');
            marker.className = 'battle-map-marker status-' + first.be_status;
            marker.style.left = x + '%';
            marker.style.top = y + '%';
            marker.style.width = sz + 'px';
            marker.style.height = sz + 'px';
            marker.style.marginLeft = (-sz / 2) + 'px';
            marker.style.marginTop = (-sz / 2) + 'px';

            if (first.monster_image) {
                marker.innerHTML = '<img src="' + first.monster_image + '" width="' + sz + '" height="' + sz + '" alt="">' +
                    '<div class="marker-name">' + first.monster_name + '</div>';
            } else {
                marker.innerHTML = '<div style="width:' + sz + 'px;height:' + sz + 'px;border-radius:50%;background:var(--mg-bg-tertiary);border:2px solid var(--mg-accent);display:flex;align-items:center;justify-content:center;">' +
                    '<svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.5 17.5L3 6V3h3l11.5 11.5M13 7.5l3.5-3.5 4 4L17 11.5"/></svg></div>' +
                    '<div class="marker-name">' + first.monster_name + '</div>';
            }
            marker.onclick = function() {
                location.href = '?mode=view&be_id=' + first.be_id + '&ch_id=' + BATTLE.chId;
            };
            markersEl.appendChild(marker);
        } else {
            // 여러 보스: 세로 겹침 배치
            group.forEach(function(e, idx) {
                var marker = document.createElement('div');
                marker.className = 'battle-map-marker status-' + e.be_status;
                marker.style.left = x + '%';
                marker.style.top = y + '%';
                var smallSz = 38;
                marker.style.width = smallSz + 'px';
                marker.style.height = smallSz + 'px';
                marker.style.marginLeft = (-smallSz / 2) + 'px';
                marker.style.marginTop = (-smallSz / 2 + idx * 14) + 'px'; // 14px씩 아래로 겹침
                marker.style.zIndex = 5 + idx;

                if (e.monster_image) {
                    marker.innerHTML = '<img src="' + e.monster_image + '" width="' + smallSz + '" height="' + smallSz + '" alt="">';
                } else {
                    marker.innerHTML = '<div style="width:' + smallSz + 'px;height:' + smallSz + 'px;border-radius:50%;background:var(--mg-bg-tertiary);border:2px solid var(--mg-accent);display:flex;align-items:center;justify-content:center;">' +
                        '<svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.5 17.5L3 6V3h3l11.5 11.5M13 7.5l3.5-3.5 4 4L17 11.5"/></svg></div>';
                }
                // 마지막 마커에만 라벨 + 카운트
                if (idx === group.length - 1) {
                    marker.innerHTML += '<div class="marker-count">' + group.length + '</div>';
                    marker.innerHTML += '<div class="marker-name">' + first.ea_name + '</div>';
                }

                marker.onclick = function() {
                    location.href = '?mode=view&be_id=' + e.be_id + '&ch_id=' + BATTLE.chId;
                };
                markersEl.appendChild(marker);
            });
        }
    });

    // 좌표가 없는 전투들은 맵에 표시 안 됨 (카드 리스트로만 확인)
}

// ── 전투 참여 ──
function battleJoin() {
    if (!BATTLE.chId) { alert('캐릭터를 선택해주세요.'); return; }
    if (!confirm('이 전투에 참여하시겠습니까?')) return;

    fetch(BATTLE.apiUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=join&be_id=' + BATTLE.beId + '&ch_id=' + BATTLE.chId
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) location.reload();
        else alert(res.message || '참여에 실패했습니다.');
    });
}

// ── 전투 행동 ──
function battleAction(type, targetChId) {
    var body = 'action=battle_action&be_id=' + BATTLE.beId + '&ch_id=' + BATTLE.chId + '&type=' + type;
    if (targetChId) body += '&target_ch_id=' + targetChId;

    fetch(BATTLE.apiUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) { alert(res.message || '행동 실패'); return; }
        // 데미지 팝업
        if (res.data && res.data.damage) showDmgPopup(res.data.damage);
        // UI 갱신
        pollBattle();
    });
}

// ── 폴링 ──
function pollBattle() {
    if (!BATTLE.beId) return;
    fetch(BATTLE.apiUrl + '?action=poll&be_id=' + BATTLE.beId)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            var d = res.data;
            // 보스 HP
            if (d.boss_hp !== undefined) {
                var pct = d.boss_max_hp > 0 ? (d.boss_hp / d.boss_max_hp * 100).toFixed(1) : 0;
                var fill = document.getElementById('boss-hp-fill');
                var text = document.getElementById('boss-hp-text');
                if (fill) fill.style.width = pct + '%';
                if (text) text.textContent = d.boss_hp.toLocaleString() + ' / ' + d.boss_max_hp.toLocaleString();
            }
            // 타이머
            if (d.time_remaining !== undefined) {
                var el = document.getElementById('boss-timer');
                if (el) {
                    var s = Math.max(0, d.time_remaining);
                    el.textContent = Math.floor(s/3600).toString().padStart(2,'0') + ':' +
                                     Math.floor((s%3600)/60).toString().padStart(2,'0') + ':' +
                                     (s%60).toString().padStart(2,'0');
                }
            }
            // 전투 종료 체크
            if (d.status === 'cleared' || d.status === 'failed') {
                clearInterval(BATTLE.pollTimer);
                location.reload();
            }
        });
}

// ── 데미지 팝업 ──
function showDmgPopup(val) {
    var c = document.getElementById('dmg-popup-container');
    if (!c) return;
    var el = document.createElement('div');
    el.style.cssText = 'position:absolute; top:35%; left:' + (40 + Math.random()*20) + '%; font-family:"Bebas Neue",sans-serif; font-size:2.5rem; color:' + (val < 0 ? '#ef4444' : '#22c55e') + '; text-shadow:0 0 12px rgba(0,0,0,0.5); letter-spacing:0.05em; animation:dmgPop 1.2s forwards; pointer-events:none;';
    el.textContent = (val > 0 ? '+' : '') + val;
    c.appendChild(el);
    setTimeout(function() { el.remove(); }, 1300);
}

// ── 로그 토글 ──
function toggleLog() {
    var p = document.getElementById('battle-log-panel');
    if (p) p.style.display = p.style.display === 'none' ? '' : 'none';
}

// ── 스탯 배분 (배치 모드) ──
var _bpStatPending = {};
function bpStatAdjust(key, delta) {
    var el = document.querySelector('.bp-stat-val[data-key="' + key + '"]');
    if (!el) return;
    var base = parseInt(el.getAttribute('data-base')) || 5;
    var cur = parseInt(el.textContent) || base;
    if (key in _bpStatPending) cur = _bpStatPending[key];
    var nv = cur + delta;
    if (nv < base) return;
    // 총 사용량 체크
    var remainEl = document.getElementById('bp-stat-remain');
    var totalBonus = <?php echo isset($_stat_bonus_bp) ? $_stat_bonus_bp : 15; ?>;
    var used = 0;
    document.querySelectorAll('.bp-stat-val').forEach(function(s) {
        var k = s.getAttribute('data-key');
        var b = parseInt(s.getAttribute('data-base')) || 5;
        var v = (k in _bpStatPending) ? _bpStatPending[k] : parseInt(s.textContent) || b;
        if (k === key) v = nv;
        used += Math.max(0, v - b);
    });
    if (used > totalBonus) return;
    _bpStatPending[key] = nv;
    el.textContent = nv;
    if (remainEl) remainEl.textContent = totalBonus - used;
}
function bpStatSave() {
    if (!confirm('스탯을 확정하시겠습니까? 이후 변경은 초기화 아이템이 필요합니다.')) return;
    var stats = {};
    document.querySelectorAll('.bp-stat-val').forEach(function(s) {
        var k = s.getAttribute('data-key');
        stats[k] = (k in _bpStatPending) ? _bpStatPending[k] : parseInt(s.textContent);
    });
    var body = 'action=save_stats&ch_id=' + BATTLE.chId;
    for (var k in stats) body += '&' + k + '=' + stats[k];
    fetch(BATTLE.apiUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) { if (typeof mgToast === 'function') mgToast('스탯이 확정되었습니다.', 'success'); location.reload(); }
        else { if (typeof mgToast === 'function') mgToast(res.message || '저장 실패', 'error'); else alert(res.message || '저장 실패'); }
    });
}

// ── 초기화 ──
document.addEventListener('DOMContentLoaded', function() {
    if (BATTLE.mode === 'list') {
        loadBattleList();
    }
    if (BATTLE.mode === 'view' && BATTLE.beId) {
        BATTLE.pollTimer = setInterval(pollBattle, 30000);
    }
    // URL 파라미터 bp=1이면 프로필 모달 자동 열기
    if (new URLSearchParams(location.search).get('bp') === '1') {
        openBattleProfile();
    }
});
</script>

<?php include_once(G5_THEME_PATH.'/tail.php'); ?>
