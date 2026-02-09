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
    <aside id="widget-sidebar" class="hidden lg:block w-72 bg-mg-bg-secondary fixed right-0 top-12 bottom-0 p-4 border-l border-mg-bg-tertiary overflow-y-auto">

        <?php if ($is_member) { ?>
        <!-- 로그인 상태: 회원 정보 -->
        <div class="card mb-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-accent font-bold text-lg">
                    <?php echo mb_substr($member['mb_nick'], 0, 1); ?>
                </div>
                <div>
                    <p class="font-semibold text-mg-text-primary"><?php echo get_text($member['mb_nick']); ?></p>
                    <p class="text-xs text-mg-text-muted">Lv.<?php echo $member['mb_level']; ?></p>
                </div>
            </div>

            <!-- 포인트 -->
            <div class="flex items-center justify-between py-2 border-t border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-secondary"><?php echo function_exists('mg_point_name') ? mg_point_name() : '포인트'; ?></span>
                <span class="text-sm font-semibold text-mg-accent"><?php echo function_exists('mg_point_format') ? mg_point_format($member['mb_point']) : number_format($member['mb_point']).'P'; ?></span>
            </div>

            <!-- 출석체크 버튼 -->
            <a href="<?php echo G5_BBS_URL; ?>/attendance.php" class="flex items-center justify-center gap-2 w-full py-2 mt-2 bg-mg-accent/10 hover:bg-mg-accent/20 text-mg-accent rounded-md text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                출석체크
            </a>
        </div>

        <!-- 대표 캐릭터 -->
        <?php
        $main_char = null;
        if (function_exists('mg_get_main_character')) {
            $main_char = mg_get_main_character($member['mb_id']);
        }
        ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    대표 캐릭터
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/character.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">관리</a>
            </div>
            <?php if ($main_char) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $main_char['ch_id']; ?>" class="block group">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-lg bg-mg-bg-tertiary flex-shrink-0 overflow-hidden">
                        <?php if (!empty($main_char['ch_thumb'])) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$main_char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        <?php } else { ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-mg-text-primary truncate group-hover:text-mg-accent transition-colors"><?php echo htmlspecialchars($main_char['ch_name']); ?></p>
                        <?php if (!empty($main_char['ch_side'])) { ?>
                        <p class="text-xs text-mg-text-muted truncate"><?php echo htmlspecialchars($main_char['ch_side']); ?></p>
                        <?php } ?>
                    </div>
                </div>
            </a>
            <?php } else { ?>
            <div class="text-center py-4">
                <div class="w-14 h-14 rounded-lg bg-mg-bg-tertiary mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <p class="text-xs text-mg-text-muted mb-2">캐릭터를 등록해주세요</p>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">캐릭터 만들기</a>
            </div>
            <?php } ?>
        </div>

        <!-- 인벤토리 미니 -->
        <?php
        // 인벤토리 데이터 가져오기
        $inventory_items = array();
        $inventory_count = 0;
        if (function_exists('mg_get_inventory')) {
            $inv_data = mg_get_inventory($member['mb_id'], 0, 1, 6);
            $inventory_items = isset($inv_data['items']) ? $inv_data['items'] : array();
            $inventory_count = isset($inv_data['total']) ? $inv_data['total'] : 0;
        }
        ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
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
                    <svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <?php } ?>
                    <?php if ($inv_item['iv_count'] > 1) { ?>
                    <span class="absolute bottom-0.5 right-0.5 bg-mg-bg-primary/90 text-xs px-1 rounded text-mg-text-secondary"><?php echo $inv_item['iv_count']; ?></span>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php for ($i = count($inventory_items); $i < 6; $i++) { ?>
                <div class="aspect-square bg-mg-bg-tertiary/50 rounded-lg border border-dashed border-mg-bg-tertiary"></div>
                <?php } ?>
            </div>
            <?php if ($inventory_count > 6) { ?>
            <p class="text-xs text-mg-text-muted text-center mt-2">+<?php echo $inventory_count - 6; ?>개 더 보유</p>
            <?php } ?>
            <?php } else { ?>
            <div class="text-center py-4">
                <p class="text-xs text-mg-text-muted mb-2">보유한 아이템이 없습니다</p>
                <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">상점 가기</a>
            </div>
            <?php } ?>
        </div>

        <!-- 선물함 미니 -->
        <?php
        // 대기 중인 선물 개수
        $pending_gifts = array();
        $gift_count = 0;
        if (function_exists('mg_get_pending_gifts')) {
            $pending_gifts = mg_get_pending_gifts($member['mb_id'], 3);
            $gift_count = count($pending_gifts);
        }
        ?>
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
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
                        <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
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
                <svg class="w-8 h-8 text-mg-text-muted mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-xs text-mg-text-muted">받은 선물이 없습니다</p>
            </div>
            <?php } ?>
        </div>

        <?php } else { ?>
        <!-- 비로그인 상태 -->
        <div class="card text-center">
            <div class="py-4">
                <svg class="w-12 h-12 text-mg-text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-sm text-mg-text-secondary mb-4">로그인하고 커뮤니티에 참여하세요</p>
                <a href="<?php echo G5_BBS_URL; ?>/login.php" class="btn btn-primary w-full mb-2">로그인</a>
                <a href="<?php echo G5_BBS_URL; ?>/register.php" class="btn btn-secondary w-full">회원가입</a>
            </div>
        </div>
        <?php } ?>

    </aside>

</div>
<!-- End Main Layout -->

<!-- Footer -->
<footer class="bg-mg-bg-secondary border-t border-mg-bg-tertiary py-4 ml-14">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-2 text-sm text-mg-text-muted">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $config['cf_title']; ?>. Powered by Morgan Edition.</p>
            <nav class="flex gap-4">
                <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=provision" class="hover:text-mg-text-primary transition-colors">이용약관</a>
                <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=privacy" class="hover:text-mg-text-primary transition-colors">개인정보처리방침</a>
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
?>

</body>
</html>
