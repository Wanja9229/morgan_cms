<?php
/**
 * Morgan Edition - Tail (Footer)
 *
 * 푸터 영역 및 HTML 종료
 */

if (!defined('_GNUBOARD_')) exit;

// SPA-like: AJAX 요청이면 레이아웃 건너뛰기
if (isset($is_ajax_request) && $is_ajax_request) {
    echo '</div>'; // #ajax-content 닫기
    return;
}
?>
    </main>
    <!-- End Main Content -->

    <!-- Right Sidebar (Member Widget) -->
    <aside id="widget-sidebar" class="hidden lg:block w-72 bg-mg-bg-secondary fixed right-0 top-12 bottom-0 border-l border-mg-bg-tertiary" style="z-index:40;flex-direction:column;overflow:hidden;">
        <div id="widget-sidebar-scroll" class="p-4" style="flex:1;overflow-y:auto;">

        <?php if ($is_member) { ?>
        <?php
        // ─── 위젯 순서 + 표시 상태 조회 ───
        $_wid_mb_esc = sql_real_escape_string($member['mb_id']);
        $_wid_default_order = array('member_card', 'inventory', 'gift', 'achievement', 'notification', 'pioneer', 'expedition', 'radio');
        $_wid_fixed = array('member_card', 'radio');
        $_wid_default_visible = array(
            'inventory' => 1, 'gift' => 1,
            'achievement' => 0, 'notification' => 0,
            'pioneer' => 0, 'expedition' => 0
        );
        $_wid_visible = $_wid_default_visible;
        $_wid_order = $_wid_default_order;

        $_wid_saved = sql_query("SELECT widget_name, widget_order, widget_visible FROM {$g5['mg_user_widget_table']} WHERE mb_id = '{$_wid_mb_esc}' ORDER BY widget_order");
        if ($_wid_saved) {
            $_wid_saved_names = array();
            while ($_wr = sql_fetch_array($_wid_saved)) {
                $_wid_saved_names[] = $_wr['widget_name'];
                if (isset($_wid_default_visible[$_wr['widget_name']])) {
                    $_wid_visible[$_wr['widget_name']] = (int)$_wr['widget_visible'];
                }
            }
            if (count($_wid_saved_names) > 0) {
                $_wid_order = array_values(array_intersect($_wid_saved_names, $_wid_default_order));
                foreach ($_wid_default_order as $_wd) {
                    if (!in_array($_wd, $_wid_order)) $_wid_order[] = $_wd;
                }
            }
        }

        // ─── 각 위젯 output buffer 수집 ───
        $_wid_buffers = array();

        // === member_card ===
        $_show_main_char = function_exists('mg_config') ? mg_config('show_main_character', '1') : '1';
        $main_char = null;
        if ($_show_main_char == '1' && function_exists('mg_get_main_character')) {
            $main_char = mg_get_main_character($member['mb_id']);
        }
        ob_start(); ?>
        <div class="card mb-4">
            <div class="flex items-center gap-3 mb-3">
                <?php if ($main_char && !empty($main_char['ch_thumb'])) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $main_char['ch_id']; ?>" class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-mg-bg-tertiary overflow-hidden ring-2 ring-mg-accent/30 hover:ring-mg-accent transition-all">
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$main_char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                    </div>
                </a>
                <?php } else { ?>
                <div class="w-12 h-12 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-accent font-bold text-lg flex-shrink-0">
                    <?php echo mb_substr($member['mb_nick'], 0, 1); ?>
                </div>
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-mg-text-primary truncate"><?php echo get_text($member['mb_nick']); ?></p>
                    <?php if ($main_char) { ?>
                    <p class="text-xs text-mg-text-muted truncate"><?php echo htmlspecialchars($main_char['ch_name']); ?><?php if (!empty($main_char['ch_side'])) echo ' · '.htmlspecialchars($main_char['ch_side']); ?></p>
                    <?php } else { ?>
                    <p class="text-xs text-mg-text-muted">대표 캐릭터 없음</p>
                    <?php } ?>
                </div>
                <a href="<?php echo G5_BBS_URL; ?>/character.php" class="flex-shrink-0 p-1.5 rounded-md hover:bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-accent transition-colors" title="캐릭터 관리">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="flex items-center justify-between py-2 border-t border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-secondary"><?php echo function_exists('mg_point_name') ? mg_point_name() : '포인트'; ?></span>
                <span class="text-sm font-semibold text-mg-accent"><?php echo function_exists('mg_point_format') ? mg_point_format($member['mb_point']) : number_format($member['mb_point']).'P'; ?></span>
            </div>
            <a href="<?php echo G5_BBS_URL; ?>/attendance.php" class="flex items-center justify-center gap-2 w-full py-2 mt-2 bg-mg-accent/10 hover:bg-mg-accent/20 text-mg-accent rounded-md text-sm font-medium transition-colors">
                <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                출석체크
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/mypage.php" class="flex items-center justify-center gap-2 w-full py-2 mt-2 bg-mg-bg-tertiary hover:bg-mg-bg-tertiary/80 text-mg-text-secondary hover:text-mg-text-primary rounded-md text-sm font-medium transition-colors">
                <i data-lucide="circle-user-round" class="w-4 h-4"></i>
                마이 페이지
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="flex items-center justify-center gap-2 w-full py-2 mt-2 text-mg-text-muted hover:text-mg-error rounded-md text-sm transition-colors" data-no-spa>
                <i data-lucide="log-out" class="w-4 h-4"></i>
                로그아웃
            </a>
        </div>
        <?php $_wid_buffers['member_card'] = ob_get_clean();

        // === inventory ===
        if (!empty($_wid_visible['inventory'])) {
        $inventory_items = array();
        $inventory_count = 0;
        if (function_exists('mg_get_inventory')) {
            $inv_data = mg_get_inventory($member['mb_id'], 0, 1, 3);
            $inventory_items = isset($inv_data['items']) ? $inv_data['items'] : array();
            $inventory_count = isset($inv_data['total']) ? $inv_data['total'] : 0;
        }
        ob_start(); ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <i data-lucide="package" class="w-4 h-4 text-mg-accent"></i>
                    인벤토리
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">전체보기</a>
            </div>
            <?php if (count($inventory_items) > 0) { ?>
            <div class="grid grid-cols-3 gap-2">
                <?php foreach ($inventory_items as $inv_item) { ?>
                <div class="aspect-square bg-mg-bg-tertiary rounded-lg flex items-center justify-center relative group cursor-pointer hover:ring-2 hover:ring-mg-accent transition-all" title="<?php echo htmlspecialchars($inv_item['si_name']); ?>">
                    <?php if (!empty($inv_item['si_image'])) { ?>
                    <img src="<?php echo $inv_item['si_image']; ?>" alt="" class="w-full h-full object-cover rounded-lg">
                    <?php } else { ?>
                    <i data-lucide="box" class="w-6 h-6 text-mg-text-muted"></i>
                    <?php } ?>
                    <?php if ($inv_item['iv_count'] > 1) { ?>
                    <span class="absolute bottom-0.5 right-0.5 bg-mg-bg-primary/90 text-xs px-1 rounded text-mg-text-secondary"><?php echo $inv_item['iv_count']; ?></span>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php for ($i = count($inventory_items); $i < 3; $i++) { ?>
                <div class="aspect-square bg-mg-bg-tertiary/50 rounded-lg border border-dashed border-mg-bg-tertiary"></div>
                <?php } ?>
            </div>
            <?php if ($inventory_count > 3) { ?>
            <p class="text-xs text-mg-text-muted text-center mt-2">+<?php echo $inventory_count - 3; ?>개 더 보유</p>
            <?php } ?>
            <?php } else { ?>
            <div class="text-center py-4">
                <p class="text-xs text-mg-text-muted mb-2">보유한 아이템이 없습니다</p>
                <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">상점 가기</a>
            </div>
            <?php } ?>
        </div>
        <?php $_wid_buffers['inventory'] = ob_get_clean();
        } // end inventory

        // === gift ===
        if (!empty($_wid_visible['gift'])) {
        $pending_gifts = array();
        $gift_count = 0;
        if (function_exists('mg_get_pending_gifts')) {
            $pending_gifts = mg_get_pending_gifts($member['mb_id'], 3);
            $gift_count = count($pending_gifts);
        }
        ob_start(); ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <i data-lucide="gift" class="w-4 h-4 text-mg-accent"></i>
                    선물함
                    <?php if ($gift_count > 0) { ?>
                    <span class="bg-mg-error text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo $gift_count; ?></span>
                    <?php } ?>
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/gift.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">전체보기</a>
            </div>
            <?php if ($gift_count > 0) { ?>
            <ul class="space-y-2">
                <?php foreach ($pending_gifts as $gift) { ?>
                <li class="flex items-center gap-2 p-2 bg-mg-bg-primary rounded-lg">
                    <div class="w-8 h-8 bg-mg-bg-tertiary rounded flex-shrink-0 flex items-center justify-center">
                        <?php if (!empty($gift['si_image'])) { ?>
                        <img src="<?php echo $gift['si_image']; ?>" alt="" class="w-full h-full object-cover rounded">
                        <?php } else { ?>
                        <i data-lucide="gift" class="w-4 h-4 text-mg-text-muted"></i>
                        <?php } ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-mg-text-primary truncate"><?php echo htmlspecialchars($gift['si_name'] ?: '선물'); ?></p>
                        <p class="text-xs text-mg-text-muted">from <?php echo htmlspecialchars($gift['from_nick'] ?: $gift['mb_id_from']); ?></p>
                    </div>
                </li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <div class="text-center py-4">
                <i data-lucide="inbox" class="w-8 h-8 text-mg-text-muted mx-auto mb-2"></i>
                <p class="text-xs text-mg-text-muted">받은 선물이 없습니다</p>
            </div>
            <?php } ?>
        </div>
        <?php $_wid_buffers['gift'] = ob_get_clean();
        } // end gift

        // === achievement ===
        if (!empty($_wid_visible['achievement'])) {
            $_ach_showcase = function_exists('mg_get_achievement_display') ? mg_get_achievement_display($member['mb_id']) : array();
            $_ach_rarity_colors = array('common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6', 'epic' => '#a855f7', 'legendary' => '#f59e0b');
            ob_start(); ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-mg-accent"></i>
                    업적
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">전체보기</a>
            </div>
            <?php if (!empty($_ach_showcase)) { ?>
            <div class="flex gap-2 flex-wrap">
                <?php foreach ($_ach_showcase as $_acd) {
                    $_a_icon = isset($_acd['tier_icon']) ? $_acd['tier_icon'] : (isset($_acd['ac_icon']) ? $_acd['ac_icon'] : '');
                    $_a_name = isset($_acd['tier_name']) && $_acd['tier_name'] ? $_acd['tier_name'] : (isset($_acd['ac_name']) ? $_acd['ac_name'] : '');
                    $_a_rarity = isset($_acd['ac_rarity']) ? $_acd['ac_rarity'] : 'common';
                    $_a_color = isset($_ach_rarity_colors[$_a_rarity]) ? $_ach_rarity_colors[$_a_rarity] : '#949ba4';
                ?>
                <div class="flex flex-col items-center p-2 rounded-lg" style="border:1px solid <?php echo $_a_color; ?>;" title="<?php echo htmlspecialchars($_a_name); ?>">
                    <?php if ($_a_icon) { ?>
                    <img src="<?php echo htmlspecialchars($_a_icon); ?>" alt="" class="w-8 h-8 object-contain">
                    <?php } else { ?>
                    <span class="text-lg">&#127942;</span>
                    <?php } ?>
                    <span class="text-xs text-mg-text-muted mt-0.5 max-w-[50px] truncate"><?php echo htmlspecialchars($_a_name); ?></span>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="text-center py-3">
                <p class="text-xs text-mg-text-muted">쇼케이스가 비어있습니다</p>
            </div>
            <?php } ?>
        </div>
            <?php $_wid_buffers['achievement'] = ob_get_clean();
        }

        // === notification ===
        if (!empty($_wid_visible['notification'])) {
            $_noti_items = array();
            $_noti_unread = 0;
            if (function_exists('mg_get_notifications')) {
                $_noti_data = mg_get_notifications($member['mb_id'], 1, 3);
                $_noti_items = isset($_noti_data['items']) ? $_noti_data['items'] : array();
            }
            if (function_exists('mg_get_unread_notification_count')) {
                $_noti_unread = mg_get_unread_notification_count($member['mb_id']);
            }
            ob_start(); ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <i data-lucide="bell" class="w-4 h-4 text-mg-accent"></i>
                    알림
                    <?php if ($_noti_unread > 0) { ?>
                    <span class="bg-mg-error text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo $_noti_unread > 99 ? '99+' : $_noti_unread; ?></span>
                    <?php } ?>
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/notification.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">전체보기</a>
            </div>
            <?php if (!empty($_noti_items)) { ?>
            <ul class="space-y-1.5">
                <?php foreach ($_noti_items as $_ni) { ?>
                <li>
                    <a href="<?php echo htmlspecialchars($_ni['noti_url'] ?? '#'); ?>" class="block p-2 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors <?php echo empty($_ni['noti_read']) ? '' : 'opacity-60'; ?>">
                        <p class="text-xs text-mg-text-primary truncate"><?php echo htmlspecialchars($_ni['noti_title'] ?? ''); ?></p>
                        <p class="text-xs text-mg-text-muted mt-0.5"><?php echo isset($_ni['noti_datetime']) ? substr($_ni['noti_datetime'], 5, 11) : ''; ?></p>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <div class="text-center py-3">
                <p class="text-xs text-mg-text-muted">새 알림이 없습니다</p>
            </div>
            <?php } ?>
        </div>
            <?php $_wid_buffers['notification'] = ob_get_clean();
        }

        // === pioneer ===
        if (!empty($_wid_visible['pioneer'])) {
            $_pn_stamina = function_exists('mg_get_stamina') ? mg_get_stamina($member['mb_id']) : array('current' => 0, 'max' => 100);
            $_pn_last = null;
            if (isset($g5['mg_facility_contribution_table']) && isset($g5['mg_facility_table'])) {
                $_pn_last = sql_fetch("SELECT fcn.fcn_amount, fcn.fcn_datetime, f.fc_name, f.fc_icon
                    FROM {$g5['mg_facility_contribution_table']} fcn
                    JOIN {$g5['mg_facility_table']} f ON fcn.fc_id = f.fc_id
                    WHERE fcn.mb_id = '{$_wid_mb_esc}' AND fcn.fcn_type = 'stamina'
                    ORDER BY fcn.fcn_datetime DESC LIMIT 1");
            }
            $_pn_pct = $_pn_stamina['max'] > 0 ? round($_pn_stamina['current'] / $_pn_stamina['max'] * 100) : 0;
            ob_start(); ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <i data-lucide="flag" class="w-4 h-4 text-mg-accent"></i>
                    개척
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">보기</a>
            </div>
            <div class="mb-2">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-mg-text-muted">스태미너</span>
                    <span class="text-mg-text-secondary"><?php echo $_pn_stamina['current']; ?>/<?php echo $_pn_stamina['max']; ?></span>
                </div>
                <div class="w-full h-1.5 bg-mg-bg-tertiary rounded-full overflow-hidden">
                    <div class="h-full bg-mg-accent rounded-full" style="width:<?php echo $_pn_pct; ?>%;"></div>
                </div>
            </div>
            <?php if ($_pn_last) { ?>
            <div class="flex items-center gap-2 p-2 bg-mg-bg-primary rounded-lg">
                <?php if (!empty($_pn_last['fc_icon'])) { ?>
                <span class="text-mg-accent flex-shrink-0"><?php echo mg_icon($_pn_last['fc_icon'], 'w-5 h-5'); ?></span>
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-mg-text-primary truncate"><?php echo htmlspecialchars($_pn_last['fc_name'] ?? ''); ?></p>
                    <p class="text-xs text-mg-text-muted"><?php echo substr($_pn_last['fcn_datetime'] ?? '', 5, 11); ?></p>
                </div>
                <span class="text-xs text-mg-text-muted flex-shrink-0"><?php echo (int)($_pn_last['fcn_amount'] ?? 0); ?>스태미너</span>
            </div>
            <?php } else { ?>
            <p class="text-xs text-mg-text-muted text-center py-1">투자 기록 없음</p>
            <?php } ?>
        </div>
            <?php $_wid_buffers['pioneer'] = ob_get_clean();
        }

        // === expedition ===
        if (!empty($_wid_visible['expedition'])) {
            if (!isset($_pn_stamina)) {
                $_pn_stamina = function_exists('mg_get_stamina') ? mg_get_stamina($member['mb_id']) : array('current' => 0, 'max' => 100);
            }
            $_exp_active = function_exists('mg_get_active_expeditions') ? mg_get_active_expeditions($member['mb_id']) : array();
            $_exp_first = !empty($_exp_active) ? reset($_exp_active) : null;
            $_exp_pct = isset($_pn_stamina['max']) && $_pn_stamina['max'] > 0 ? round($_pn_stamina['current'] / $_pn_stamina['max'] * 100) : 0;
            ob_start(); ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <i data-lucide="map" class="w-4 h-4 text-mg-accent"></i>
                    파견
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/pioneer.php?view=expedition" class="text-xs text-mg-accent hover:text-mg-accent-hover">보기</a>
            </div>
            <div class="mb-2">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-mg-text-muted">스태미너</span>
                    <span class="text-mg-text-secondary"><?php echo $_pn_stamina['current']; ?>/<?php echo $_pn_stamina['max']; ?></span>
                </div>
                <div class="w-full h-1.5 bg-mg-bg-tertiary rounded-full overflow-hidden">
                    <div class="h-full bg-mg-accent rounded-full" style="width:<?php echo $_exp_pct; ?>%;"></div>
                </div>
            </div>
            <?php if ($_exp_first) { ?>
            <div class="flex items-center gap-2 p-2 bg-mg-bg-primary rounded-lg">
                <?php if (!empty($_exp_first['ea_icon'])) { ?>
                <img src="<?php echo htmlspecialchars($_exp_first['ea_icon']); ?>" alt="" class="w-6 h-6 object-contain flex-shrink-0">
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-mg-text-primary truncate"><?php echo htmlspecialchars($_exp_first['ea_name'] ?? '파견지'); ?></p>
                    <?php if (!empty($_exp_first['is_complete'])) { ?>
                    <p class="text-xs text-green-400">완료!</p>
                    <?php } else { ?>
                    <div class="w-full h-1 bg-mg-bg-tertiary rounded-full overflow-hidden mt-1">
                        <div class="h-full bg-green-500 rounded-full" style="width:<?php echo isset($_exp_first['progress']) ? (int)$_exp_first['progress'] : 0; ?>%;"></div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } else { ?>
            <p class="text-xs text-mg-text-muted text-center py-1">진행 중인 파견 없음</p>
            <?php } ?>
        </div>
            <?php $_wid_buffers['expedition'] = ob_get_clean();
        }

        // === radio ===
        $_mg_radio_on = false;
        if (isset($g5['mg_radio_config_table'])) {
            $_mg_rcfg = sql_fetch("SELECT is_active FROM {$g5['mg_radio_config_table']} WHERE config_id = 1");
            if ($_mg_rcfg && $_mg_rcfg['is_active']) $_mg_radio_on = true;
        }
        if ($_mg_radio_on && (!function_exists('mg_is_unlocked') || mg_is_unlocked('radio'))) {
            ob_start(); ?>
        <div class="card mb-4" id="mg-radio-widget">
            <!-- 날씨 -->
            <div class="flex items-center gap-2 mb-2" id="radio-weather" style="display:none;">
                <span id="radio-weather-icon" style="font-size:1.1rem;"></span>
                <span id="radio-weather-temp" class="text-sm text-mg-text-primary font-semibold"></span>
                <span id="radio-weather-desc" class="text-xs text-mg-text-muted"></span>
                <a href="https://openweathermap.org/" target="_blank" rel="noopener" id="radio-owm-credit" class="text-mg-text-muted" style="display:none;margin-left:auto;font-size:0.6rem;opacity:0.6;">OWM</a>
            </div>
            <!-- 멘트 -->
            <div class="overflow-hidden mb-2" style="height:20px;">
                <div id="radio-marquee" class="text-xs text-mg-text-muted whitespace-nowrap"></div>
            </div>
            <!-- 플레이어 (트랙 없으면 숨김) -->
            <div id="radio-player-section" style="display:none;">
                <!-- 현재 곡 -->
                <div class="flex items-center gap-2 mb-2 border-t border-mg-bg-tertiary pt-2">
                    <span class="text-mg-accent" style="font-size:0.75rem;">♪</span>
                    <span id="radio-track-title" class="text-xs text-mg-text-primary truncate flex-1">(정지)</span>
                </div>
                <!-- 컨트롤 -->
                <div class="flex items-center gap-2">
                    <button id="radio-play-btn" type="button" class="p-1.5 rounded hover:bg-mg-bg-tertiary text-mg-text-secondary transition-colors" title="재생/정지" style="font-size:0.85rem;line-height:1;">▶</button>
                    <button id="radio-next-btn" type="button" class="p-1.5 rounded hover:bg-mg-bg-tertiary text-mg-text-secondary transition-colors" title="다음 곡" style="font-size:0.75rem;line-height:1;">⏭</button>
                    <input type="range" id="radio-volume" min="0" max="100" value="30" style="flex:1;accent-color:var(--mg-accent);height:4px;">
                    <button id="radio-video-btn" type="button" class="p-1.5 rounded hover:bg-mg-bg-tertiary text-mg-text-secondary transition-colors" title="영상 보기"><i data-lucide="video" class="w-4 h-4"></i></button>
                </div>
                <!-- 영상 (접힘) -->
                <div id="radio-video-wrap" style="height:0;overflow:hidden;transition:height .3s;border-radius:6px;">
                    <div id="radio-player"></div>
                </div>
            </div>
        </div>
        <style>
        @keyframes mg-marquee { 0%{transform:translateX(100%)} 100%{transform:translateX(-100%)} }
        #radio-volume { -webkit-appearance: none; background: var(--mg-bg-tertiary); border-radius: 2px; }
        #radio-volume::-webkit-slider-thumb { -webkit-appearance:none; width:12px; height:12px; border-radius:50%; background:var(--mg-accent); cursor:pointer; }
        </style>
        <script src="https://www.youtube.com/iframe_api"></script>
        <script>
        (function(){
            var MR = {
                player: null, tracks: [], ments: [], mentIdx: 0,
                trackIdx: 0, playing: false, playMode: 'sequential',
                mentMode: 'sequential', mentInterval: 12, mentTimer: null,
                weatherIcons: {
                    sunny:'☀️', partly_cloudy:'⛅', cloudy:'☁️', rain:'🌧️',
                    shower:'🌦️', snow:'❄️', fog:'🌫️', thunderstorm:'⛈️'
                },
                weatherNames: {
                    sunny:'맑음', partly_cloudy:'구름조금', cloudy:'흐림', rain:'비',
                    shower:'소나기', snow:'눈', fog:'안개', thunderstorm:'천둥번개'
                }
            };
            window._MR = MR;

            // API 로드
            fetch('<?php echo G5_BBS_URL; ?>/radio_api.php?action=status')
                .then(function(r){ return r.json(); })
                .then(function(d){
                    if (!d.success) return;
                    var data = d.data;
                    MR.tracks = data.tracks || [];
                    MR.ments = data.ments || [];
                    MR.playMode = data.play_mode;
                    MR.mentMode = data.ment_mode;
                    MR.mentInterval = data.ment_interval || 12;

                    // 날씨
                    if (data.weather) {
                        var wEl = document.getElementById('radio-weather');
                        wEl.style.display = '';
                        document.getElementById('radio-weather-icon').textContent = MR.weatherIcons[data.weather.type] || '☀️';
                        document.getElementById('radio-weather-temp').textContent = data.weather.temp + '°C';
                        document.getElementById('radio-weather-desc').textContent = MR.weatherNames[data.weather.type] || '';
                        if (data.weather_mode === 'api') {
                            var owm = document.getElementById('radio-owm-credit');
                            if (owm) owm.style.display = '';
                        }
                    }

                    // 멘트 시작
                    if (MR.ments.length > 0) {
                        startMent();
                    }

                    // 트랙 있으면 플레이어 표시
                    if (MR.tracks.length > 0) {
                        var ps = document.getElementById('radio-player-section');
                        if (ps) ps.style.display = '';
                    }

                    // 랜덤 모드면 셔플
                    if (MR.playMode === 'random' && MR.tracks.length > 1) {
                        for (var i = MR.tracks.length - 1; i > 0; i--) {
                            var j = Math.floor(Math.random() * (i + 1));
                            var tmp = MR.tracks[i]; MR.tracks[i] = MR.tracks[j]; MR.tracks[j] = tmp;
                        }
                    }
                });

            // 멘트 로테이션
            function startMent() {
                showMent();
                MR.mentTimer = setInterval(function(){
                    if (MR.mentMode === 'random') {
                        MR.mentIdx = Math.floor(Math.random() * MR.ments.length);
                    } else {
                        MR.mentIdx = (MR.mentIdx + 1) % MR.ments.length;
                    }
                    showMent();
                }, MR.mentInterval * 1000);
            }

            function showMent() {
                var el = document.getElementById('radio-marquee');
                if (!el || MR.ments.length === 0) return;
                el.textContent = MR.ments[MR.mentIdx];
                el.style.animation = 'none';
                el.offsetHeight;
                el.style.animation = 'mg-marquee ' + Math.max(6, MR.mentInterval) + 's linear infinite';
            }

            // YouTube Player
            window.onYouTubeIframeAPIReady = function() {
                MR.player = new YT.Player('radio-player', {
                    height: '120',
                    width: '100%',
                    playerVars: {
                        autoplay: 0, controls: 0, disablekb: 1,
                        modestbranding: 1, rel: 0, fs: 0
                    },
                    events: {
                        onReady: function(e) {
                            e.target.setVolume(parseInt(document.getElementById('radio-volume').value));
                        },
                        onStateChange: function(e) {
                            if (e.data === YT.PlayerState.ENDED) {
                                nextTrack();
                            }
                        },
                        onError: function() {
                            nextTrack();
                        }
                    }
                });
            };

            function playTrack(idx) {
                if (!MR.player || MR.tracks.length === 0) return;
                MR.trackIdx = idx % MR.tracks.length;
                var t = MR.tracks[MR.trackIdx];
                document.getElementById('radio-track-title').textContent = t.title;
                MR.player.loadVideoById(t.vid);
                MR.playing = true;
                document.getElementById('radio-play-btn').textContent = '⏸';
            }

            function nextTrack() {
                if (MR.tracks.length === 0) return;
                playTrack(MR.trackIdx + 1);
            }

            // 재생/정지
            document.getElementById('radio-play-btn').addEventListener('click', function() {
                if (!MR.player || MR.tracks.length === 0) return;
                if (MR.playing) {
                    MR.player.pauseVideo();
                    MR.playing = false;
                    this.textContent = '▶';
                } else {
                    if (MR.player.getPlayerState && MR.player.getPlayerState() === YT.PlayerState.PAUSED) {
                        MR.player.playVideo();
                        MR.playing = true;
                        this.textContent = '⏸';
                    } else {
                        playTrack(MR.trackIdx);
                    }
                }
            });

            // 다음 곡
            document.getElementById('radio-next-btn').addEventListener('click', function() {
                nextTrack();
            });

            // 볼륨
            document.getElementById('radio-volume').addEventListener('input', function() {
                if (MR.player && MR.player.setVolume) MR.player.setVolume(parseInt(this.value));
            });

            // 영상 토글
            document.getElementById('radio-video-btn').addEventListener('click', function() {
                var wrap = document.getElementById('radio-video-wrap');
                if (parseInt(wrap.style.height) === 0) {
                    wrap.style.height = '120px';
                    this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
                } else {
                    wrap.style.height = '0';
                    this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
                }
            });
        })();
        </script>
            <?php $_wid_buffers['radio'] = ob_get_clean();
        } // end radio

        // ─── 순서대로 위젯 출력 ───
        ?>
        <div id="widget-sort-container">
        <?php foreach ($_wid_order as $_wname) {
            if (!empty($_wid_buffers[$_wname])) { ?>
            <div class="widget-sortable" data-widget="<?php echo $_wname; ?>">
                <div class="widget-drag-handle" title="드래그하여 순서 변경">⠿</div>
                <?php echo $_wid_buffers[$_wname]; ?>
            </div>
        <?php }
        } ?>
        </div>
        <style>
        .widget-drag-handle { text-align:center; cursor:grab; padding:2px 0 0; color:var(--mg-text-muted); opacity:0; font-size:14px; transition:opacity .2s; user-select:none; line-height:1; }
        .widget-sortable:hover .widget-drag-handle { opacity:0.5; }
        .widget-drag-handle:hover { opacity:0.8 !important; }
        .widget-drag-handle:active { cursor:grabbing; }
        .widget-sortable.sortable-ghost { opacity:0.4; }
        .widget-sortable.sortable-chosen { opacity:0.8; }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script>
        (function(){
            var container = document.getElementById('widget-sort-container');
            if (!container) return;
            Sortable.create(container, {
                handle: '.widget-drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    var order = [];
                    container.querySelectorAll('.widget-sortable').forEach(function(el) {
                        order.push(el.dataset.widget);
                    });
                    var body = order.map(function(n){ return 'order[]=' + encodeURIComponent(n); }).join('&');
                    fetch('<?php echo G5_BBS_URL; ?>/widget_api.php?action=save_order', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: body
                    });
                }
            });
        })();
        </script>

        <?php } else { ?>
        <!-- 비로그인 상태 -->
        <div class="card text-center">
            <div class="py-4">
                <i data-lucide="user" class="w-12 h-12 text-mg-text-muted mx-auto mb-3"></i>
                <p class="text-sm text-mg-text-secondary mb-4">로그인하고 커뮤니티에 참여하세요</p>
                <a href="<?php echo G5_BBS_URL; ?>/login.php" class="btn btn-primary w-full mb-2">로그인</a>
                <a href="<?php echo G5_BBS_URL; ?>/register.php" class="btn btn-secondary w-full">회원가입</a>
            </div>
        </div>
        <?php } ?>

        <?php
        // ─── 활성 전투 위젯 (하단 고정) ───
        if ($is_member && function_exists('mg_config') && mg_config('battle_use', '1') === '1') {
            $_battle_encounters = array();
            $_be_res = sql_query("SELECT e.be_id, e.be_status, e.be_time_limit, e.be_started_at, e.be_monsters,
                                         m.bm_name, m.bm_image, a.ea_name
                                  FROM {$g5['mg_battle_encounter_table']} e
                                  LEFT JOIN {$g5['mg_battle_monster_table']} m ON e.bm_id = m.bm_id
                                  LEFT JOIN {$g5['mg_expedition_area_table']} a ON e.ea_id = a.ea_id
                                  WHERE e.be_status IN ('discovered','active')
                                  ORDER BY e.be_status DESC, e.be_discovered_at DESC
                                  LIMIT 10");
            if ($_be_res !== false) {
                while ($_be_row = sql_fetch_array($_be_res)) {
                    $_monsters = json_decode($_be_row['be_monsters'] ?? '[]', true);
                    $_hp = 0; $_max_hp = 0;
                    if (is_array($_monsters)) {
                        foreach ($_monsters as $_m) { $_hp += (int)($_m['hp'] ?? 0); $_max_hp += (int)($_m['max_hp'] ?? 1); }
                    }
                    $_slot_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_battle_slot_table']} WHERE be_id = " . (int)$_be_row['be_id']);
                    $_battle_encounters[] = array(
                        'be_id'     => (int)$_be_row['be_id'],
                        'status'    => $_be_row['be_status'],
                        'name'      => $_be_row['bm_name'] ?? '',
                        'image'     => $_be_row['bm_image'] ?? '',
                        'area'      => $_be_row['ea_name'] ?? '',
                        'hp'        => $_hp,
                        'max_hp'    => $_max_hp,
                        'slots'     => (int)($_slot_cnt['cnt'] ?? 0),
                        'started'   => $_be_row['be_started_at'] ?? '',
                        'time_limit'=> (int)$_be_row['be_time_limit'],
                    );
                }
            }
            if (!empty($_battle_encounters)) {
                $_has_battle_widget = true;
        ?>
        <!-- 활성 전투 위젯 -->
        </div><!-- /widget-sidebar-scroll -->
        <div id="battle-widget" style="flex-shrink:0;z-index:15;">
            <div style="background:var(--mg-bg-primary);border-top:2px solid var(--mg-accent);padding:10px 12px;box-shadow:0 -8px 24px rgba(0,0,0,0.5);">
                <!-- 슬라이드 컨테이너 -->
                <div id="bw-slides" style="position:relative;overflow:hidden;">
                    <?php foreach ($_battle_encounters as $_bi => $_be) {
                        $_hp_pct = $_be['max_hp'] > 0 ? round($_be['hp'] / $_be['max_hp'] * 100) : 0;
                        $_hp_color = $_hp_pct > 60 ? '#ef4444' : ($_hp_pct > 25 ? '#eab308' : '#22c55e');
                        $_status_label = $_be['status'] === 'active' ? '전투 중' : '발견';
                        $_status_color = $_be['status'] === 'active' ? '#f97316' : '#3b82f6';
                    ?>
                    <div class="bw-slide" data-be-id="<?php echo $_be['be_id']; ?>"
                         data-started="<?php echo $_be['started']; ?>"
                         data-time-limit="<?php echo $_be['time_limit']; ?>"
                         style="<?php echo $_bi > 0 ? 'display:none;' : ''; ?>">
                        <!-- 보스명 + 상태 -->
                        <a href="<?php echo G5_BBS_URL; ?>/battle.php?mode=view&be_id=<?php echo $_be['be_id']; ?>"
                           class="block" style="text-decoration:none;color:inherit;">
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                <?php if ($_be['image']) { ?>
                                <img src="<?php echo htmlspecialchars($_be['image']); ?>" alt=""
                                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1.5px solid <?php echo $_status_color; ?>;flex-shrink:0;">
                                <?php } else { ?>
                                <div style="width:28px;height:28px;border-radius:50%;background:var(--mg-bg-tertiary);border:1.5px solid <?php echo $_status_color; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i data-lucide="swords" style="width:14px;height:14px;color:<?php echo $_status_color; ?>;"></i>
                                </div>
                                <?php } ?>
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:4px;">
                                        <span style="font-size:12px;font-weight:700;color:var(--mg-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($_be['name']); ?></span>
                                        <span style="font-size:9px;padding:1px 4px;border-radius:3px;font-weight:600;color:<?php echo $_status_color; ?>;background:<?php echo $_status_color; ?>15;white-space:nowrap;"><?php echo $_status_label; ?></span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:6px;font-size:10px;color:var(--mg-text-muted);">
                                        <?php if ($_be['area']) { ?><span><?php echo htmlspecialchars($_be['area']); ?></span><?php } ?>
                                        <span><?php echo $_be['slots']; ?>명</span>
                                        <span class="bw-timer" style="color:var(--mg-accent);">--:--</span>
                                    </div>
                                </div>
                            </div>
                            <!-- HP 바 -->
                            <div style="display:flex;align-items:center;gap:6px;">
                                <div style="flex:1;height:4px;background:var(--mg-bg-tertiary);border-radius:2px;overflow:hidden;">
                                    <div class="bw-hp-bar" style="height:100%;border-radius:2px;background:<?php echo $_hp_color; ?>;width:<?php echo $_hp_pct; ?>%;transition:width 0.5s;"></div>
                                </div>
                                <span style="font-size:10px;color:var(--mg-text-muted);white-space:nowrap;">HP <?php echo $_hp_pct; ?>%</span>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                </div>

                <?php if (count($_battle_encounters) > 1) { ?>
                <!-- 슬라이드 컨트롤 -->
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:6px;">
                    <button onclick="bwPrev()" style="background:none;border:none;color:var(--mg-text-muted);cursor:pointer;padding:2px;font-size:14px;">&#9664;</button>
                    <span id="bw-page" style="font-size:10px;color:var(--mg-text-muted);">1 / <?php echo count($_battle_encounters); ?></span>
                    <button onclick="bwNext()" style="background:none;border:none;color:var(--mg-text-muted);cursor:pointer;padding:2px;font-size:14px;">&#9654;</button>
                </div>
                <?php } ?>
            </div>
        </div>

        <script>
        (function() {
            var slides = document.querySelectorAll('.bw-slide');
            var total = slides.length;
            if (total === 0) return;
            var current = 0;

            // 슬라이드 전환
            window.bwPrev = function() {
                slides[current].style.display = 'none';
                current = (current - 1 + total) % total;
                slides[current].style.display = '';
                updatePage();
            };
            window.bwNext = function() {
                slides[current].style.display = 'none';
                current = (current + 1) % total;
                slides[current].style.display = '';
                updatePage();
            };
            function updatePage() {
                var el = document.getElementById('bw-page');
                if (el) el.textContent = (current + 1) + ' / ' + total;
            }

            // 타이머 갱신
            function updateTimers() {
                slides.forEach(function(s) {
                    var timer = s.querySelector('.bw-timer');
                    if (!timer) return;
                    var started = s.getAttribute('data-started');
                    var limit = parseInt(s.getAttribute('data-time-limit')) || 0;
                    if (!started || !limit) {
                        timer.textContent = '대기';
                        return;
                    }
                    var elapsed = Math.floor((Date.now() / 1000) - (new Date(started + ' UTC').getTime() / 1000));
                    var remain = Math.max(0, limit - elapsed);
                    var h = Math.floor(remain / 3600);
                    var m = Math.floor((remain % 3600) / 60);
                    var sec = remain % 60;
                    timer.textContent = (h > 0 ? h + ':' : '') + String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
                    if (remain <= 0) timer.textContent = '종료';
                });
            }
            updateTimers();
            setInterval(updateTimers, 1000);

            // 자동 슬라이드 (10초)
            if (total > 1) {
                setInterval(function() { window.bwNext(); }, 10000);
            }
        })();
        </script>
        <?php
            } // end if !empty encounters
        } // end if battle_use
        if (empty($_has_battle_widget)) {
            echo '</div><!-- /widget-sidebar-scroll (no battle) -->';
        }
        ?>

    </aside>

</div>
<!-- End Main Layout -->

<!-- Footer -->
<footer class="bg-mg-bg-secondary border-t border-mg-bg-tertiary py-4 ml-0 lg:ml-14 lg:mr-72">
    <div class="mg-inner px-4">
        <?php
        $mg_footer_name = function_exists('mg_config') ? mg_config('site_name', $config['cf_title']) : $config['cf_title'];
        $mg_footer_text = function_exists('mg_config') ? mg_config('footer_text', '') : '';
        ?>
        <?php if ($mg_footer_text): ?>
        <div class="text-sm text-mg-text-muted mb-3"><?php echo nl2br(htmlspecialchars($mg_footer_text)); ?></div>
        <?php endif; ?>
        <div class="flex flex-col md:flex-row items-center justify-between gap-2 text-sm text-mg-text-muted">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($mg_footer_name); ?>. Powered by Morgan Edition.</p>
            <nav class="flex gap-4">
                <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=provision" class="hover:text-mg-text-primary transition-colors">이용약관</a>
                <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=privacy" class="hover:text-mg-text-primary transition-colors">개인정보처리방침</a>
                <a href="mailto:morgan_29@naver.com" class="hover:text-mg-text-primary transition-colors">빌더 문의</a>
                <a href="<?php echo G5_BBS_URL; ?>/credits.php" class="hover:text-mg-text-primary transition-colors">Credits</a>
            </nav>
        </div>
    </div>
</footer>

</div>
<!-- End App Container -->

<!-- Morgan Edition JS -->
<script src="<?php echo G5_THEME_URL; ?>/js/app.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script src="<?php echo G5_THEME_URL; ?>/js/emoticon-picker.js?ver=<?php echo G5_JS_VER; ?>"></script>

<?php
// 그누보드 JS 출력
if (function_exists('get_javascript_file')) {
    echo get_javascript_file();
}

// 업적 달성 토스트
if (!empty($_SESSION['mg_achievement_toast'])) {
    $toast = $_SESSION['mg_achievement_toast'];
    unset($_SESSION['mg_achievement_toast']);
    $toast_rarity_colors = array(
        'common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6',
        'epic' => '#a855f7', 'legendary' => '#f59e0b',
    );
    $toast_rarity_labels = array(
        'common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare',
        'epic' => 'Epic', 'legendary' => 'Legendary',
    );
    $toast_color = $toast_rarity_colors[$toast['rarity'] ?? 'common'] ?? '#949ba4';
    $toast_label = $toast_rarity_labels[$toast['rarity'] ?? 'common'] ?? '';
?>
<div id="mg-achievement-toast" style="position:fixed;bottom:-200px;left:50%;transform:translateX(-50%);z-index:9999;min-width:320px;max-width:420px;background:var(--mg-bg-primary,#1e1f22);border:2px solid <?php echo $toast_color; ?>;border-radius:12px;padding:16px 20px;box-shadow:0 0 30px <?php echo $toast_color; ?>40,0 8px 32px rgba(0,0,0,.5);transition:bottom .6s cubic-bezier(.34,1.56,.64,1);pointer-events:auto;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="flex-shrink:0;width:48px;height:48px;border-radius:8px;border:2px solid <?php echo $toast_color; ?>;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.3);">
            <?php if (!empty($toast['icon'])) { ?>
            <img src="<?php echo htmlspecialchars($toast['icon']); ?>" alt="" style="width:36px;height:36px;object-fit:contain;">
            <?php } else { ?>
            <span style="font-size:24px;">&#127942;</span>
            <?php } ?>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:<?php echo $toast_color; ?>;margin-bottom:2px;"><?php echo $toast_label; ?> Achievement Unlocked!</div>
            <div style="font-size:15px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($toast['name']); ?></div>
            <?php if (!empty($toast['desc'])) { ?>
            <div style="font-size:12px;color:var(--mg-text-secondary,#b5bac1);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($toast['desc']); ?></div>
            <?php } ?>
            <?php if (!empty($toast['reward'])) { ?>
            <div style="font-size:11px;color:<?php echo $toast_color; ?>;margin-top:4px;font-weight:500;">&#127873; <?php echo htmlspecialchars($toast['reward']); ?></div>
            <?php } ?>
        </div>
        <button onclick="document.getElementById('mg-achievement-toast').style.bottom='-200px'" style="flex-shrink:0;background:none;border:none;color:var(--mg-text-muted,#949ba4);cursor:pointer;padding:4px;font-size:18px;line-height:1;">&times;</button>
    </div>
</div>
<script>
(function(){
    var t = document.getElementById('mg-achievement-toast');
    if (!t) return;
    setTimeout(function(){ t.style.bottom = '24px'; }, 300);
    setTimeout(function(){ t.style.bottom = '-200px'; }, 6000);
})();
</script>
<?php } ?>

<?php
// ─── 히든 이벤트 위젯 (로그인 + 이벤트 활성 시만) ───
if ($is_member) {
    $he_active = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_hidden_event_table']} WHERE is_active = 1");
    if ((int)($he_active['cnt'] ?? 0) > 0) {
?>
<div id="mg-hidden-event-overlay" style="display:none;position:fixed;z-index:9000;pointer-events:none;">
    <img id="mg-he-image" src="" alt="" style="pointer-events:auto;cursor:pointer;position:absolute;width:80px;height:80px;max-width:none;object-fit:contain;filter:drop-shadow(0 0 8px rgba(245,159,10,0.6));animation:mg-he-float 2s ease-in-out infinite;transition:transform .2s,opacity .4s;">
</div>
<style>
@keyframes mg-he-float {
    0%,100% { transform: translateY(0) rotate(-3deg); }
    50% { transform: translateY(-12px) rotate(3deg); }
}
#mg-he-image:hover { transform: scale(1.2) !important; }
</style>
<script>
(function(){
    var HE = {
        overlay: null,
        img: null,
        token: null,
        cooldown: false,
        fadeTimer: null,
        BBS_URL: '<?php echo G5_BBS_URL; ?>',

        init: function() {
            this.overlay = document.getElementById('mg-hidden-event-overlay');
            this.img = document.getElementById('mg-he-image');
            if (!this.overlay || !this.img) return;

            var self = this;
            this.img.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.claim();
            });

            // SPA 페이지 전환 시 체크
            window.addEventListener('mg:pageLoaded', function() {
                self.check();
            });

            // 최초 로드 시 1회 체크
            setTimeout(function() { self.check(); }, 2000);
        },

        check: function() {
            if (this.cooldown) return;
            this.cooldown = true;
            var self = this;

            // 5초 쿨다운
            setTimeout(function() { self.cooldown = false; }, 5000);

            fetch(this.BBS_URL + '/hidden_event_api.php?action=check', {
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.event) {
                    self.show(data.event);
                }
            })
            .catch(function() {});
        },

        show: function(ev) {
            this.token = ev.token;
            this.img.src = ev.image_url;

            // 랜덤 위치 (viewport 20%~80%)
            var vw = window.innerWidth;
            var vh = window.innerHeight;
            var x = Math.floor(vw * 0.2 + Math.random() * vw * 0.6);
            var y = Math.floor(vh * 0.2 + Math.random() * vh * 0.4);

            this.overlay.style.left = x + 'px';
            this.overlay.style.top = y + 'px';
            this.img.style.opacity = '1';
            this.overlay.style.display = 'block';

            // 5초 후 자동 페이드아웃
            var self = this;
            if (this.fadeTimer) clearTimeout(this.fadeTimer);
            this.fadeTimer = setTimeout(function() {
                self.hide();
            }, 5000);
        },

        hide: function() {
            var self = this;
            this.img.style.opacity = '0';
            setTimeout(function() {
                self.overlay.style.display = 'none';
                self.token = null;
            }, 400);
        },

        claim: function() {
            if (!this.token) return;
            var self = this;
            if (this.fadeTimer) clearTimeout(this.fadeTimer);

            // 클릭 즉시 축소 애니메이션
            this.img.style.transform = 'scale(0.3)';
            this.img.style.opacity = '0';

            var formData = new FormData();
            formData.append('action', 'claim');
            formData.append('token', this.token);

            fetch(this.BBS_URL + '/hidden_event_api.php?action=claim', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                setTimeout(function() {
                    self.overlay.style.display = 'none';
                    self.img.style.transform = '';
                }, 400);

                if (data.success) {
                    self.showRewardToast(data.reward_name);
                }
                self.token = null;
            })
            .catch(function() {
                self.overlay.style.display = 'none';
                self.img.style.transform = '';
                self.token = null;
            });
        },

        showRewardToast: function(rewardName) {
            // 보상 토스트
            var toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;bottom:-80px;left:50%;transform:translateX(-50%);z-index:9999;background:var(--mg-bg-primary,#1e1f22);border:2px solid var(--mg-accent,#f59f0a);border-radius:12px;padding:12px 20px;box-shadow:0 0 20px rgba(245,159,10,0.3),0 8px 24px rgba(0,0,0,.5);transition:bottom .5s cubic-bezier(.34,1.56,.64,1);white-space:nowrap;pointer-events:auto;';
            toast.innerHTML = '<div style="display:flex;align-items:center;gap:10px;">'
                + '<span style="font-size:20px;">&#127873;</span>'
                + '<div>'
                + '<div style="font-size:11px;font-weight:600;color:var(--mg-accent,#f59f0a);text-transform:uppercase;letter-spacing:.5px;">Hidden Event!</div>'
                + '<div style="font-size:14px;font-weight:700;color:#fff;">' + (rewardName || '보상') + ' 획득!</div>'
                + '</div></div>';
            document.body.appendChild(toast);

            setTimeout(function() { toast.style.bottom = '24px'; }, 50);
            setTimeout(function() {
                toast.style.bottom = '-80px';
                setTimeout(function() { toast.remove(); }, 500);
            }, 4000);
        }
    };

    // DOM 준비 후 초기화
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { HE.init(); });
    } else {
        HE.init();
    }
})();
</script>
<?php } } ?>

</body>
</html>
