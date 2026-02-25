<?php
/**
 * Morgan Edition - Board View Skin (Concierge Result) — Renewed
 *
 * 의뢰 연결 카드 + 표준 뷰 레이아웃.
 */

if (!defined('_GNUBOARD_')) exit;

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 연결된 의뢰 정보 조회
$_mg_cr = null;
$cr_sql = "SELECT cr.*, cc.cc_id, cc.cc_title, cc.cc_type, cc.cc_status, cc.cc_content, cc.mb_id as cc_mb_id
           FROM {$g5['mg_concierge_result_table']} cr
           JOIN {$g5['mg_concierge_table']} cc ON cr.cc_id = cc.cc_id
           WHERE cr.bo_table = '".sql_real_escape_string($bo_table)."' AND cr.wr_id = '".(int)$wr_id."'
           LIMIT 1";
$_mg_cr_result = sql_query($cr_sql);
if ($_mg_cr_result) {
    $_mg_cr = sql_fetch_array($_mg_cr_result);
}
$_mg_cc_owner = null;
$_mg_cc_performers = array();
if ($_mg_cr) {
    $_mg_cc_owner = sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($_mg_cr['cc_mb_id'])."'");
    $perf_sql = "SELECT ca.mb_id, m.mb_nick
                 FROM {$g5['mg_concierge_apply_table']} ca
                 JOIN {$g5['member_table']} m ON ca.mb_id = m.mb_id
                 WHERE ca.cc_id = ".(int)$_mg_cr['cc_id']." AND ca.ca_status = 'selected'";
    $perf_result = sql_query($perf_sql);
    while ($perf_row = sql_fetch_array($perf_result)) {
        $_mg_cc_performers[] = $perf_row;
    }
}

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');
$status_labels = array('recruiting' => '모집중', 'matched' => '수행중', 'completed' => '완료', 'expired' => '만료', 'cancelled' => '취소', 'force_closed' => '미이행종료');
$status_colors = array('recruiting' => 'var(--mg-accent)', 'matched' => '#60a5fa', 'completed' => 'var(--mg-success)', 'expired' => 'var(--mg-text-muted)', 'cancelled' => 'var(--mg-text-muted)', 'force_closed' => 'var(--mg-error)');
?>

<?php if ($_mg_cr) {
    $cc_type_label = isset($type_labels[$_mg_cr['cc_type']]) ? $type_labels[$_mg_cr['cc_type']] : '';
    $cc_status_label = isset($status_labels[$_mg_cr['cc_status']]) ? $status_labels[$_mg_cr['cc_status']] : $_mg_cr['cc_status'];
    $cc_status_color = isset($status_colors[$_mg_cr['cc_status']]) ? $status_colors[$_mg_cr['cc_status']] : 'var(--mg-text-muted)';
?>
<div class="mg-inner mb-4">
    <div class="rounded-2xl overflow-hidden" style="background:var(--mg-bg-secondary);border:1px solid color-mix(in srgb, var(--mg-accent) 25%, var(--mg-bg-tertiary));">
        <div class="flex items-start gap-3 px-5 py-4">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:color-mix(in srgb, var(--mg-accent) 15%, transparent);">
                <svg class="w-5 h-5" style="color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="text-xs" style="color:var(--mg-text-muted);">연결된 의뢰</span>
                    <?php if ($cc_type_label) { ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:color-mix(in srgb, var(--mg-accent) 15%, transparent);color:var(--mg-accent);"><?php echo $cc_type_label; ?></span>
                    <?php } ?>
                    <span class="text-xs font-medium" style="color:<?php echo $cc_status_color; ?>;"><?php echo $cc_status_label; ?></span>
                </div>
                <a href="<?php echo G5_BBS_URL; ?>/concierge_view.php?cc_id=<?php echo $_mg_cr['cc_id']; ?>" class="font-medium transition-colors" style="color:var(--mg-text-primary);" onmouseover="this.style.color='var(--mg-accent)'" onmouseout="this.style.color='var(--mg-text-primary)'">
                    <?php echo htmlspecialchars($_mg_cr['cc_title']); ?>
                </a>
                <div class="flex items-center gap-3 mt-1 text-xs" style="color:var(--mg-text-muted);">
                    <?php if ($_mg_cc_owner) { ?>
                    <span>의뢰자: <?php echo htmlspecialchars($_mg_cc_owner['mb_nick']); ?></span>
                    <?php } ?>
                    <?php if ($_mg_cc_performers) { ?>
                    <span>수행자: <?php echo htmlspecialchars(implode(', ', array_column($_mg_cc_performers, 'mb_nick'))); ?></span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php
// 표준 뷰 스킨 — 글쓰기 버튼 텍스트만 오버라이드
$_mg_concierge_write_label = '결과물 등록';
include_once(G5_THEME_PATH.'/skin/board/basic/view.skin.php');
?>
