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
include_once(G5_PATH.'/plugin/dice-box/dice-box-loader.php');

if (mg_config('battle_use', '1') != '1') {
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
$my_global_hp = null;
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
    $my_global_hp = mg_battle_get_global_hp($selected_ch_id);

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

    // 전투 소모 아이템
    $battle_items = array();
    $bi_res = sql_query("SELECT ii.iv_id, ii.iv_count as qty, si.si_id, si.si_name, si.si_effect
                          FROM {$g5['mg_inventory_table']} ii
                          JOIN {$g5['mg_shop_item_table']} si ON ii.si_id = si.si_id
                          WHERE ii.mb_id = '{$mb_id}' AND si.si_type = 'battle_consumable' AND ii.iv_count > 0
                          ORDER BY si.si_name");
    if ($bi_res) {
        while ($bi = sql_fetch_array($bi_res)) {
            $battle_items[] = $bi;
        }
    }
}

// 주사위 면 수
$dice_sides = 20;
if (function_exists('mg_battle_dice_sides')) {
    $dice_sides = mg_battle_dice_sides();
}

$g5['title'] = '전투';
include_once(G5_THEME_PATH.'/head.php');

$api_url = G5_BBS_URL . '/battle_api.php';
?>

<div id="battle-app" class="mx-auto" style="max-width:var(--mg-content-width);">

<?php
$map_image = mg_config('expedition_map_image', '');
$_training_use = mg_config('battle_training_use', '1');
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
                        <!-- HP (글로벌) -->
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold" style="color:#22c55e; font-family:'Bebas Neue',sans-serif; width:24px; text-align:right;">HP</span>
                            <div class="flex-1 bg-mg-bg-primary border border-mg-bg-tertiary rounded overflow-hidden" style="max-width:280px; height:12px;">
                                <div id="my-hp-fill" class="h-full rounded transition-all duration-500" style="width:<?php echo $my_global_hp && $my_global_hp['max_hp'] > 0 ? round($my_global_hp['current_hp'] / $my_global_hp['max_hp'] * 100, 1) : 0; ?>%; background:linear-gradient(90deg, #16a34a, #22c55e);"></div>
                            </div>
                            <span id="my-hp-text" class="text-xs font-bold text-mg-text-primary" style="font-family:'Bebas Neue',sans-serif; white-space:nowrap;">
                                <?php echo $my_global_hp ? $my_global_hp['current_hp'] : 0; ?> / <?php echo $my_global_hp ? $my_global_hp['max_hp'] : 0; ?>
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
                        <?php if ($my_slot && $my_global_hp && $my_global_hp['current_hp'] > 0 && $encounter['be_status'] === 'active') { ?>
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
                    $_slot_ghp = mg_battle_get_global_hp((int)$slot['ch_id']);
                    $hp_pct = $_slot_ghp['max_hp'] > 0 ? round($_slot_ghp['current_hp'] / $_slot_ghp['max_hp'] * 100) : 0;
                    $hp_class = $hp_pct > 60 ? '#22c55e' : ($hp_pct > 25 ? '#eab308' : '#ef4444');
                    $is_dead = $_slot_ghp['current_hp'] <= 0;
                    $is_discoverer = ($slot['mb_id'] === $encounter['discoverer_mb_id']);
                ?>
                <div class="flex items-center gap-2 px-3 py-2 border-b border-white/[0.02] hover:bg-mg-accent/[0.04] transition-colors <?php echo $is_dead ? 'opacity-40' : ''; ?>" data-ch-id="<?php echo (int)$slot['ch_id']; ?>" style="position:relative;">
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
                        <div class="text-[10px] text-mg-text-muted mt-0.5"><?php echo $_slot_ghp['current_hp']; ?> / <?php echo $_slot_ghp['max_hp']; ?><?php echo $is_dead ? ' (전사)' : ''; ?></div>
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

<!-- ═══════════════════════════════════
     주사위 판정 모달
     ═══════════════════════════════════ -->
<?php if ($mode === 'view') { ?>
<div id="battle-dice-modal" class="fixed inset-0 z-50" style="background:rgba(0,0,0,0.8); display:none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div style="max-width:480px; width:100%;">
            <div id="dice-result-display" class="text-center mb-3" style="display:none;">
                <div id="dice-result-value" style="font-family:'Bebas Neue',sans-serif; font-size:4.5rem; line-height:1; text-shadow:0 0 30px currentColor;"></div>
                <div id="dice-result-mult" class="mt-1" style="font-family:'Bebas Neue',sans-serif; font-size:1.3rem; color:var(--mg-text-secondary); letter-spacing:0.1em;"></div>
            </div>
            <div id="battle-dice-box" class="dice-box-overlay"></div>
            <div id="dice-action-result" class="text-center mt-3" style="display:none;">
                <div id="dice-action-text" style="font-size:1rem; color:var(--mg-text-primary);"></div>
            </div>
        </div>
    </div>
</div>

<!-- 스킬 선택 팝업 -->
<div id="skill-select-modal" class="fixed inset-0 z-50" style="background:rgba(0,0,0,0.65); display:none;" onclick="if(event.target===this){this.style.display='none';}">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden" style="max-width:360px; width:100%;">
            <div class="px-4 py-3 border-b border-mg-bg-tertiary flex items-center justify-between">
                <h3 class="font-bold text-mg-text-primary flex items-center gap-2" style="font-family:'Bebas Neue',sans-serif; letter-spacing:0.1em;">
                    <i data-lucide="crosshair" class="w-4 h-4 text-mg-accent"></i> SKILL SELECT
                </h3>
                <button onclick="document.getElementById('skill-select-modal').style.display='none'" class="text-mg-text-muted hover:text-mg-text-primary">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="skill-select-list" class="p-3"></div>
        </div>
    </div>
</div>

<!-- 아이템 선택 팝업 -->
<div id="item-select-modal" class="fixed inset-0 z-50" style="background:rgba(0,0,0,0.65); display:none;" onclick="if(event.target===this){this.style.display='none';}">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden" style="max-width:360px; width:100%;">
            <div class="px-4 py-3 border-b border-mg-bg-tertiary flex items-center justify-between">
                <h3 class="font-bold text-mg-text-primary flex items-center gap-2" style="font-family:'Bebas Neue',sans-serif; letter-spacing:0.1em;">
                    <i data-lucide="box" class="w-4 h-4" style="color:#22c55e;"></i> ITEM
                </h3>
                <button onclick="document.getElementById('item-select-modal').style.display='none'" class="text-mg-text-muted hover:text-mg-text-primary">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="item-select-list" class="p-3"></div>
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
/* 주사위 결과 컬러 */
.dice-nat1 { color: #ef4444; }
.dice-low { color: #f97316; }
.dice-mid { color: #eab308; }
.dice-high { color: #22c55e; }
.dice-nat20 { color: #f59f0a; text-shadow: 0 0 40px #f59f0a, 0 0 80px rgba(245,159,10,0.5); }
/* 전투 이펙트 팝업 */
@keyframes battleEffectFloat {
    0% { transform: translateY(0) scale(0.5); opacity: 0; }
    15% { transform: translateY(-5px) scale(1.1); opacity: 1; }
    30% { transform: translateY(-10px) scale(1); }
    80% { opacity: 1; }
    100% { transform: translateY(-40px) scale(0.9); opacity: 0; }
}
@keyframes battleEffectShake {
    0%, 100% { transform: translateX(0); }
    10% { transform: translateX(-6px); }
    30% { transform: translateX(6px); }
    50% { transform: translateX(-4px); }
    70% { transform: translateX(4px); }
    90% { transform: translateX(-2px); }
}
@keyframes critFlash {
    0% { opacity: 0; transform: scale(0.3) rotate(-5deg); }
    20% { opacity: 1; transform: scale(1.2) rotate(2deg); }
    40% { transform: scale(0.95) rotate(-1deg); }
    100% { opacity: 0; transform: scale(1.1) rotate(0deg) translateY(-20px); }
}
.battle-effect-popup {
    position: absolute;
    font-family: 'Bebas Neue', sans-serif;
    font-weight: 700;
    pointer-events: none;
    z-index: 100;
    animation: battleEffectFloat 1.5s forwards;
    white-space: nowrap;
}
.battle-effect-crit {
    animation: critFlash 1.8s forwards;
    font-size: 1.8rem;
}
.monster-hit {
    animation: battleEffectShake 0.5s ease-out;
}
/* 스킬/아이템 선택 버튼 */
.skill-select-btn {
    display: flex; align-items: center; gap: 0.5rem;
    width: 100%; padding: 0.6rem 0.75rem;
    background: var(--mg-bg-primary); border: 1px solid var(--mg-bg-tertiary);
    border-radius: 0.5rem; cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    text-align: left; color: var(--mg-text-primary);
}
.skill-select-btn:hover { border-color: var(--mg-accent); background: rgba(245,159,10,0.04); }
.skill-select-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.skill-select-btn:disabled:hover { border-color: var(--mg-bg-tertiary); background: var(--mg-bg-primary); }
</style>

<script>
var BATTLE = {
    apiUrl: '<?php echo $api_url; ?>',
    beId: <?php echo $be_id ?: 0; ?>,
    chId: <?php echo $selected_ch_id ?: 0; ?>,
    mode: '<?php echo $mode; ?>',
    pollTimer: null,
    diceSides: <?php echo (int)$dice_sides; ?>,
    diceInited: false,
    actionLock: false,
    equippedSkills: <?php
        $js_skills = array();
        if ($my_battle_profile && !empty($my_battle_profile['equipped_skills'])) {
            foreach ($my_battle_profile['equipped_skills'] as $slot => $sk) {
                $js_skills[] = array(
                    'slot' => (int)$slot,
                    'sk_id' => (int)$sk['sk_id'],
                    'sk_name' => $sk['sk_name'],
                    'sk_type' => $sk['sk_type'],
                    'sk_stamina' => (int)$sk['sk_stamina'],
                    'sk_target' => $sk['sk_target'] ?? 'enemy_single',
                    'sk_target_count' => (int)($sk['sk_target_count'] ?? 1),
                );
            }
        }
        echo json_encode($js_skills, JSON_UNESCAPED_UNICODE);
    ?>,
    battleItems: <?php echo json_encode($battle_items ?? array(), JSON_UNESCAPED_UNICODE); ?>,
    myEnergy: <?php echo (int)($my_energy['current'] ?? 0); ?>
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
        var sz = 44;

        if (group.length === 1) {
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
            group.forEach(function(e, idx) {
                var marker = document.createElement('div');
                marker.className = 'battle-map-marker status-' + e.be_status;
                marker.style.left = x + '%';
                marker.style.top = y + '%';
                var smallSz = 38;
                marker.style.width = smallSz + 'px';
                marker.style.height = smallSz + 'px';
                marker.style.marginLeft = (-smallSz / 2) + 'px';
                marker.style.marginTop = (-smallSz / 2 + idx * 14) + 'px';
                marker.style.zIndex = 5 + idx;

                if (e.monster_image) {
                    marker.innerHTML = '<img src="' + e.monster_image + '" width="' + smallSz + '" height="' + smallSz + '" alt="">';
                } else {
                    marker.innerHTML = '<div style="width:' + smallSz + 'px;height:' + smallSz + 'px;border-radius:50%;background:var(--mg-bg-tertiary);border:2px solid var(--mg-accent);display:flex;align-items:center;justify-content:center;">' +
                        '<svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.5 17.5L3 6V3h3l11.5 11.5M13 7.5l3.5-3.5 4 4L17 11.5"/></svg></div>';
                }
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

// ═══════════════════════════════════
//  주사위 + 전투 행동 시스템
// ═══════════════════════════════════

// ── 주사위 모달 표시 + 3D 롤 ──
function showDiceRoll(diceValue, callback) {
    var modal = document.getElementById('battle-dice-modal');
    if (!modal) { if (callback) callback(); return; }

    // 초기화
    var resultDisp = document.getElementById('dice-result-display');
    var actionResult = document.getElementById('dice-action-result');
    resultDisp.style.display = 'none';
    actionResult.style.display = 'none';
    modal.style.display = '';

    var notation = '1d' + BATTLE.diceSides + '@' + diceValue;

    function doRoll() {
        if (window.MorganDice && window.MorganDice.isReady()) {
            window.MorganDice.clear();
            setTimeout(function() {
                window.MorganDice.roll(notation).then(function() {
                    // 롤 완료 후 결과 표시
                    setTimeout(function() {
                        showDiceResult(diceValue);
                        if (callback) setTimeout(callback, 800);
                    }, 600);
                });
            }, 100);
        } else if (window.MorganDice && !BATTLE.diceInited) {
            BATTLE.diceInited = true;
            window.MorganDice.init('#battle-dice-box').then(function() {
                window.MorganDice.roll(notation).then(function() {
                    setTimeout(function() {
                        showDiceResult(diceValue);
                        if (callback) setTimeout(callback, 800);
                    }, 600);
                });
            }).catch(function() {
                showDiceResult(diceValue);
                if (callback) setTimeout(callback, 800);
            });
        } else {
            // MorganDice 없으면 텍스트만 표시
            showDiceResult(diceValue);
            if (callback) setTimeout(callback, 800);
        }
    }

    // MorganDice 모듈이 아직 로드 안 됐을 수 있음
    if (window.MorganDiceLoaded) {
        doRoll();
    } else {
        window.addEventListener('MorganDiceLoaded', function() { doRoll(); }, { once: true });
        // 타임아웃 fallback
        setTimeout(function() {
            if (!window.MorganDiceLoaded) {
                showDiceResult(diceValue);
                if (callback) setTimeout(callback, 800);
            }
        }, 3000);
    }
}

function showDiceResult(roll) {
    var resultDisp = document.getElementById('dice-result-display');
    var valEl = document.getElementById('dice-result-value');
    var multEl = document.getElementById('dice-result-mult');
    var sides = BATTLE.diceSides;

    // 컬러 클래스 결정
    var cls = 'dice-mid';
    if (roll <= 1) cls = 'dice-nat1';
    else if (roll <= Math.ceil(sides * 0.25)) cls = 'dice-low';
    else if (roll >= sides) cls = 'dice-nat20';
    else if (roll >= Math.ceil(sides * 0.75)) cls = 'dice-high';

    valEl.className = cls;
    valEl.textContent = roll;

    var label = roll <= 1 ? 'CRITICAL FAIL' : roll >= sides ? 'NATURAL ' + sides + '!' : '1d' + sides;
    multEl.textContent = label;
    resultDisp.style.display = '';
}

function hideDiceModal() {
    var modal = document.getElementById('battle-dice-modal');
    if (modal) modal.style.display = 'none';
}

// ── 전투 행동 (주사위 연동) ──
function battleAction(type, skId, targetChIds) {
    if (BATTLE.actionLock) return;

    // 스킬 선택 모달
    if (type === 'skill' && !skId) {
        showSkillSelector();
        return;
    }
    // 아이템 선택 모달
    if (type === 'item') {
        showItemSelector();
        return;
    }

    BATTLE.actionLock = true;
    var body = 'action=battle_action&be_id=' + BATTLE.beId + '&ch_id=' + BATTLE.chId + '&type=' + type;
    if (skId) body += '&sk_id=' + skId;
    if (targetChIds) body += '&target_ch_ids=' + targetChIds;

    fetch(BATTLE.apiUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
    })
    .then(r => r.json())
    .then(function(res) {
        if (!res.success) {
            alert(res.message || '행동 실패');
            BATTLE.actionLock = false;
            return;
        }

        var d = res.data || {};
        var diceRoll = d.dice || 0;

        if (diceRoll > 0) {
            // 주사위 모달 → 이펙트 → UI 갱신
            showDiceRoll(diceRoll, function() {
                // 액션 결과 텍스트
                showDiceActionText(d);
                // 이펙트 애니메이션
                setTimeout(function() {
                    playBattleEffect(d);
                    // 반격 이펙트
                    if (d.counter && d.counter.damage > 0 && !d.counter.evaded) {
                        setTimeout(function() {
                            showParticipantEffect(d.counter.target_ch_id, -d.counter.damage, '#ef4444');
                        }, 600);
                    } else if (d.counter && d.counter.evaded) {
                        setTimeout(function() {
                            showParticipantEffect(d.counter.target_ch_id, 'MISS', '#60a5fa');
                        }, 600);
                    }
                    // 모달 닫고 UI 갱신
                    setTimeout(function() {
                        hideDiceModal();
                        pollBattle();
                        BATTLE.actionLock = false;
                    }, 1500);
                }, 400);
            });
        } else {
            // 주사위 없는 행동
            if (d.damage) showMonsterEffect(d.damage, d.is_crit);
            pollBattle();
            BATTLE.actionLock = false;
        }
    })
    .catch(function() {
        BATTLE.actionLock = false;
    });
}

function showDiceActionText(d) {
    var el = document.getElementById('dice-action-result');
    var textEl = document.getElementById('dice-action-text');
    if (!el || !textEl) return;

    textEl.innerHTML = d.message || '';
    el.style.display = '';
}

// ── 전투 이펙트 분기 ──
function playBattleEffect(d) {
    var at = d.action_type || 'attack';

    switch (at) {
        case 'attack':
        case 'damage':
            showMonsterEffect(d.damage, d.is_crit);
            break;
        case 'heal':
            if (d.heal) {
                // 자신에게 힐 이펙트 표시
                showParticipantEffect(BATTLE.chId, '+' + d.heal, '#22c55e');
            }
            break;
        case 'buff':
            showParticipantEffect(BATTLE.chId, (d.buff_stat || 'BUFF') + ' +' + (d.buff_value || '') + '%', '#60a5fa');
            break;
        case 'debuff':
            showMonsterTextEffect((d.debuff_stat || 'DEBUFF') + ' -' + (d.debuff_value || '') + '%', '#c084fc');
            break;
        case 'taunt':
            showMonsterTextEffect('PROVOKED!', '#eab308');
            break;
    }
}

// ── 몬스터에 데미지 이펙트 ──
function showMonsterEffect(val, isCrit) {
    var c = document.getElementById('dmg-popup-container');
    if (!c || !val) return;

    // 쉐이크
    var monsterEl = document.getElementById('monster-display');
    if (monsterEl) {
        monsterEl.classList.add('monster-hit');
        setTimeout(function() { monsterEl.classList.remove('monster-hit'); }, 500);
    }

    var el = document.createElement('div');
    el.className = 'battle-effect-popup' + (isCrit ? ' battle-effect-crit' : '');
    var absVal = Math.abs(val);
    var color = val < 0 ? '#ef4444' : '#22c55e';
    el.style.cssText = 'top:' + (25 + Math.random() * 20) + '%; left:' + (35 + Math.random() * 25) + '%; font-size:' + (isCrit ? '3rem' : '2.2rem') + '; color:' + color + '; text-shadow:0 0 15px ' + color + ', 0 2px 4px rgba(0,0,0,0.5);';
    el.textContent = (val > 0 ? '+' : '') + val;
    c.appendChild(el);

    if (isCrit) {
        var critLabel = document.createElement('div');
        critLabel.className = 'battle-effect-popup';
        critLabel.style.cssText = 'top:' + (parseInt(el.style.top) - 8) + '%; left:' + el.style.left + '; font-size:1rem; color:#f59f0a; text-shadow:0 0 20px #f59f0a;';
        critLabel.textContent = 'CRITICAL!';
        c.appendChild(critLabel);
        setTimeout(function() { critLabel.remove(); }, 1600);
    }

    setTimeout(function() { el.remove(); }, 1600);
}

// ── 몬스터에 텍스트 이펙트 (디버프/도발) ──
function showMonsterTextEffect(text, color) {
    var c = document.getElementById('dmg-popup-container');
    if (!c) return;

    var el = document.createElement('div');
    el.className = 'battle-effect-popup';
    el.style.cssText = 'top:30%; left:' + (30 + Math.random() * 20) + '%; font-size:1.5rem; color:' + color + '; text-shadow:0 0 15px ' + color + ', 0 2px 4px rgba(0,0,0,0.5);';
    el.textContent = text;
    c.appendChild(el);
    setTimeout(function() { el.remove(); }, 1600);
}

// ── 참여자(캐릭터)에 이펙트 ──
function showParticipantEffect(chId, text, color) {
    var row = document.querySelector('#participants-list [data-ch-id="' + chId + '"]');
    if (!row) return;

    var el = document.createElement('div');
    el.className = 'battle-effect-popup';
    el.style.cssText = 'top:-5px; right:8px; font-size:1.1rem; color:' + color + '; text-shadow:0 0 10px ' + color + ';';
    el.textContent = text;
    row.appendChild(el);
    setTimeout(function() { el.remove(); }, 1600);
}

// ═══════════════════════════════════
//  스킬 / 아이템 선택 UI
// ═══════════════════════════════════

function showSkillSelector() {
    var modal = document.getElementById('skill-select-modal');
    var list = document.getElementById('skill-select-list');
    if (!modal || !list) return;

    var skills = BATTLE.equippedSkills;
    if (!skills || skills.length === 0) {
        list.innerHTML = '<div class="text-center py-4 text-mg-text-muted text-sm">장착된 스킬이 없습니다.<br>프로필에서 스킬을 장착해주세요.</div>';
        modal.style.display = '';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        return;
    }

    var typeIcons = { damage: 'swords', heal: 'heart', buff: 'arrow-up', debuff: 'arrow-down', taunt: 'shield' };
    var typeColors = { damage: '#ef4444', heal: '#22c55e', buff: '#60a5fa', debuff: '#c084fc', taunt: '#eab308' };
    var h = '';

    skills.forEach(function(sk) {
        var icon = typeIcons[sk.sk_type] || 'crosshair';
        var color = typeColors[sk.sk_type] || '#f59f0a';
        var disabled = sk.sk_stamina > BATTLE.myEnergy ? ' disabled' : '';
        h += '<button class="skill-select-btn mb-2"' + disabled + ' onclick="onSkillSelect(' + sk.sk_id + ', \'' + sk.sk_type + '\', \'' + sk.sk_target + '\', ' + sk.sk_target_count + ')">';
        h += '<div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0" style="background:' + color + '20; border:1px solid ' + color + '40;">';
        h += '<i data-lucide="' + icon + '" class="w-4 h-4" style="color:' + color + ';"></i></div>';
        h += '<div class="flex-1 min-w-0"><div class="text-sm font-bold truncate">' + sk.sk_name + '</div>';
        h += '<div class="text-[10px] text-mg-text-muted">' + sk.sk_type.toUpperCase() + ' · EN ' + sk.sk_stamina + '</div></div>';
        h += '</button>';
    });

    list.innerHTML = h;
    modal.style.display = '';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function onSkillSelect(skId, skType, skTarget, targetCount) {
    document.getElementById('skill-select-modal').style.display = 'none';

    // 아군 대상 스킬이면 대상 선택 필요
    if ((skType === 'heal' || skType === 'buff') && skTarget !== 'ally_all' && skTarget !== 'self') {
        showTargetSelector(skId, targetCount);
        return;
    }

    battleAction('skill', skId, skTarget === 'self' ? BATTLE.chId : '');
}

function showTargetSelector(skId, maxTargets) {
    // 참여자 목록에서 선택 가능하게
    var rows = document.querySelectorAll('#participants-list [data-ch-id]');
    var selected = [];

    rows.forEach(function(row) {
        var chId = row.getAttribute('data-ch-id');
        row.style.cursor = 'pointer';
        row.style.outline = '2px solid transparent';

        var handler = function() {
            var idx = selected.indexOf(chId);
            if (idx >= 0) {
                selected.splice(idx, 1);
                row.style.outline = '2px solid transparent';
            } else if (selected.length < maxTargets) {
                selected.push(chId);
                row.style.outline = '2px solid var(--mg-accent)';
            }
        };
        row._targetHandler = handler;
        row.addEventListener('click', handler);
    });

    // 확인 버튼 추가
    var confirmBar = document.createElement('div');
    confirmBar.id = 'target-confirm-bar';
    confirmBar.style.cssText = 'position:fixed; bottom:0; left:0; right:0; z-index:60; background:var(--mg-bg-secondary); border-top:2px solid var(--mg-accent); padding:0.75rem; text-align:center;';
    confirmBar.innerHTML = '<span class="text-sm text-mg-text-secondary mr-3">대상 ' + maxTargets + '명 선택</span>' +
        '<button onclick="confirmTargetSelect(' + skId + ')" class="cmd-btn cmd-primary" style="clip-path:none;">확인</button>' +
        '<button onclick="cancelTargetSelect()" class="cmd-btn cmd-muted ml-2" style="clip-path:none;">취소</button>';
    document.body.appendChild(confirmBar);

    BATTLE._targetSelected = selected;
}

function confirmTargetSelect(skId) {
    var selected = BATTLE._targetSelected || [];
    cancelTargetSelect();
    if (selected.length > 0) {
        battleAction('skill', skId, selected.join(','));
    }
}

function cancelTargetSelect() {
    var bar = document.getElementById('target-confirm-bar');
    if (bar) bar.remove();
    document.querySelectorAll('#participants-list [data-ch-id]').forEach(function(row) {
        if (row._targetHandler) {
            row.removeEventListener('click', row._targetHandler);
            delete row._targetHandler;
        }
        row.style.cursor = '';
        row.style.outline = '';
    });
    BATTLE._targetSelected = null;
}

function showItemSelector() {
    var modal = document.getElementById('item-select-modal');
    var list = document.getElementById('item-select-list');
    if (!modal || !list) return;

    var items = BATTLE.battleItems;
    if (!items || items.length === 0) {
        list.innerHTML = '<div class="text-center py-4 text-mg-text-muted text-sm">사용 가능한 전투 소모품이 없습니다.</div>';
        modal.style.display = '';
        return;
    }

    var h = '';
    items.forEach(function(it) {
        var eff = {};
        try { eff = JSON.parse(it.si_effect || '{}'); } catch(e) {}
        var typeLabel = { heal: '회복', revive: '부활', stamina: '기력', dice_lock: '주사위 고정', dice_reroll: '리롤', dice_bless: '축복' };
        var label = typeLabel[eff.type] || '소모품';
        h += '<button class="skill-select-btn mb-2" onclick="useItem(' + it.si_id + ')">';
        h += '<div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0" style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3);">';
        h += '<i data-lucide="box" class="w-4 h-4" style="color:#22c55e;"></i></div>';
        h += '<div class="flex-1 min-w-0"><div class="text-sm font-bold truncate">' + it.si_name + '</div>';
        h += '<div class="text-[10px] text-mg-text-muted">' + label + ' · ' + it.qty + '개</div></div>';
        h += '</button>';
    });

    list.innerHTML = h;
    modal.style.display = '';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function useItem(siId) {
    document.getElementById('item-select-modal').style.display = 'none';

    fetch(BATTLE.apiUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=use_item&be_id=' + BATTLE.beId + '&ch_id=' + BATTLE.chId + '&si_id=' + siId
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.success) {
            if (typeof mgToast === 'function') mgToast(res.message || '아이템 사용!', 'success');
            pollBattle();
        } else {
            alert(res.message || '아이템 사용 실패');
        }
    });
}

// ═══════════════════════════════════
//  폴링 + 유틸
// ═══════════════════════════════════

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
            // 내 HP/EN 갱신
            if (d.my_hp !== undefined) {
                var hpFill = document.getElementById('my-hp-fill');
                var hpText = document.getElementById('my-hp-text');
                if (hpFill && d.my_max_hp) {
                    hpFill.style.width = (d.my_max_hp > 0 ? (d.my_hp / d.my_max_hp * 100).toFixed(1) : 0) + '%';
                }
                if (hpText && d.my_max_hp !== undefined) {
                    hpText.textContent = d.my_hp + ' / ' + d.my_max_hp;
                }
            }
            if (d.my_energy !== undefined) {
                BATTLE.myEnergy = d.my_energy;
            }
            // 참여자 HP 갱신
            if (d.participants || d.slots) {
                (d.participants || d.slots).forEach(function(p) {
                    var row = document.querySelector('#participants-list [data-ch-id="' + p.ch_id + '"]');
                    if (!row) return;
                    var hp = p.hp !== undefined ? p.hp : (p.current_hp || 0);
                    var maxHp = p.max_hp || 0;
                    var hpBar = row.querySelector('.h-1 > div');
                    var hpText = row.querySelector('.text-\\[10px\\]');
                    if (hpBar && maxHp > 0) {
                        var pct = Math.round(hp / maxHp * 100);
                        hpBar.style.width = pct + '%';
                        hpBar.style.background = pct > 60 ? '#22c55e' : (pct > 25 ? '#eab308' : '#ef4444');
                    }
                    if (hpText) {
                        hpText.textContent = hp + ' / ' + maxHp + (hp <= 0 ? ' (전사)' : '');
                    }
                    if (hp <= 0) row.classList.add('opacity-40');
                    else row.classList.remove('opacity-40');
                });
            }
            // 전투 종료 체크
            if (d.status === 'cleared' || d.status === 'failed') {
                clearInterval(BATTLE.pollTimer);
                location.reload();
            }
        });
}

// ── 데미지 팝업 (레거시 호환) ──
function showDmgPopup(val) {
    showMonsterEffect(val, false);
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
    if (new URLSearchParams(location.search).get('bp') === '1') {
        openBattleProfile();
    }
});
</script>

<?php
// 전투 뷰 모드에서 주사위 3D 로더 출력
if ($mode === 'view') {
    mg_dice_box_scripts(array('container' => '#battle-dice-box'));
}
?>

<?php include_once(G5_THEME_PATH.'/tail.php'); ?>
