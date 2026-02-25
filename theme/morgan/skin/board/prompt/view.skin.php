<?php
/**
 * Morgan Edition - Prompt Mission Board View Skin (Renewed)
 *
 * 미션 엔트리 정보 카드 + 표준 뷰 레이아웃.
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 미션 엔트리 조회
$mg_entry = mg_get_entry_by_write($bo_table, $wr_id);

// 상태 배지 헬퍼
function _prompt_view_status_badge($status) {
    $map = array(
        'submitted' => array('bg' => 'rgba(234,179,8,0.15)',  'color' => '#eab308', 'label' => '대기'),
        'approved'  => array('bg' => 'rgba(34,197,94,0.15)',  'color' => '#22c55e', 'label' => '승인'),
        'rejected'  => array('bg' => 'rgba(239,68,68,0.15)',  'color' => '#ef4444', 'label' => '반려'),
        'rewarded'  => array('bg' => 'rgba(59,130,246,0.15)', 'color' => '#3b82f6', 'label' => '보상완료'),
    );
    if (!isset($map[$status])) return '';
    $s = $map[$status];
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:'.$s['bg'].';color:'.$s['color'].';">'.$s['label'].'</span>';
}
?>

<?php if ($mg_entry && $mg_entry['pm_id']) { ?>
<div class="mg-inner mb-4">
    <div class="rounded-2xl overflow-hidden" style="background:var(--mg-bg-secondary);border:1px solid color-mix(in srgb, var(--mg-accent) 25%, var(--mg-bg-tertiary));">
        <div class="flex items-center gap-3 px-5 py-4">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:color-mix(in srgb, var(--mg-accent) 15%, transparent);">
                <svg class="w-5 h-5" style="color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-medium truncate" style="color:var(--mg-accent);"><?php echo htmlspecialchars($mg_entry['pm_title']); ?></span>
                    <?php echo _prompt_view_status_badge($mg_entry['pe_status']); ?>
                    <?php if ((int)$mg_entry['pe_point'] > 0) { ?>
                    <span class="text-sm" style="color:var(--mg-text-muted);">+<?php echo number_format($mg_entry['pe_point']); ?>P</span>
                    <?php } elseif ((int)$mg_entry['pm_point'] > 0 && $mg_entry['pe_status'] !== 'rejected') { ?>
                    <span class="text-sm" style="color:var(--mg-text-muted);">+<?php echo number_format($mg_entry['pm_point']); ?>P</span>
                    <?php } ?>
                    <?php if ((int)$mg_entry['pe_is_bonus']) { ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:color-mix(in srgb, var(--mg-accent) 15%, transparent);color:var(--mg-accent);">우수작</span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php
// 표준 뷰 스킨 사용
include_once(G5_THEME_PATH.'/skin/board/basic/view.skin.php');
?>
