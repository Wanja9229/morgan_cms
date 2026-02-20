<?php
/**
 * Morgan Edition - 회원 수정 (관리자)
 */

$sub_menu = "800190";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mb_id = isset($_GET['mb_id']) ? clean_xss_tags($_GET['mb_id']) : '';
if (!$mb_id) { alert('잘못된 접근입니다.'); }

$mb = sql_fetch("SELECT * FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
if (!$mb['mb_id']) { alert('존재하지 않는 회원입니다.'); }

// 보유 캐릭터
$sql = "SELECT * FROM {$g5['mg_character_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."' AND ch_state != 'deleted' ORDER BY ch_main DESC, ch_id ASC";
$char_result = sql_query($sql);
$characters = array();
while ($row = sql_fetch_array($char_result)) {
    $characters[] = $row;
}

// 처리 결과 메시지
$msg = isset($_GET['msg']) ? clean_xss_tags($_GET['msg']) : '';

$g5['title'] = '회원 수정 - ' . $mb['mb_nick'];
include_once __DIR__.'/_head.php';
?>

<?php if ($msg) { ?>
<div class="mg-alert mg-alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php } ?>

<div style="display:flex; gap:1.5rem; flex-wrap:wrap;">

    <!-- 기본 정보 -->
    <div style="flex:1; min-width:400px;">
        <form method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/member_form_update.php">
            <input type="hidden" name="mb_id" value="<?php echo htmlspecialchars($mb['mb_id']); ?>">

            <div class="mg-card">
                <div class="mg-card-header">기본 정보</div>
                <div class="mg-card-body">
                    <div class="mg-form-group">
                        <label class="mg-form-label">아이디</label>
                        <input type="text" class="mg-form-input" value="<?php echo htmlspecialchars($mb['mb_id']); ?>" disabled>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">닉네임</label>
                        <input type="text" name="mb_nick" class="mg-form-input" value="<?php echo htmlspecialchars($mb['mb_nick']); ?>">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">이메일</label>
                        <input type="email" name="mb_email" class="mg-form-input" value="<?php echo htmlspecialchars($mb['mb_email']); ?>">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">비밀번호 변경 (빈칸이면 유지)</label>
                        <input type="password" name="mb_password" class="mg-form-input" placeholder="새 비밀번호 입력">
                    </div>
                    <div style="display:flex; gap:1rem;">
                        <div class="mg-form-group" style="flex:1;">
                            <label class="mg-form-label">포인트</label>
                            <input type="number" name="mb_point" class="mg-form-input" value="<?php echo $mb['mb_point']; ?>">
                        </div>
                        <div class="mg-form-group" style="flex:1;">
                            <label class="mg-form-label">권한 레벨</label>
                            <?php if ($mb['mb_id'] === $member['mb_id']) { ?>
                            <input type="hidden" name="mb_level" value="<?php echo $mb['mb_level']; ?>">
                            <div class="mg-form-input" style="background:var(--mg-bg-tertiary);cursor:default;"><?php echo $mb['mb_level']; ?></div>
                            <?php } else { ?>
                            <input type="number" name="mb_level" class="mg-form-input" value="<?php echo $mb['mb_level']; ?>" min="1" max="10">
                            <?php } ?>
                        </div>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">메모 (관리자용)</label>
                        <textarea name="mb_memo" class="mg-form-textarea" rows="3"><?php echo htmlspecialchars($mb['mb_memo']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="mg-card">
                <div class="mg-card-header">상태 관리</div>
                <div class="mg-card-body">
                    <div style="display:flex; gap:1rem;">
                        <div class="mg-form-group" style="flex:1;">
                            <label class="mg-form-label">차단일 (비워두면 해제)</label>
                            <input type="text" name="mb_intercept_date" class="mg-form-input" value="<?php echo $mb['mb_intercept_date']; ?>" placeholder="YYYYMMDD 또는 비움">
                        </div>
                        <div class="mg-form-group" style="flex:1;">
                            <label class="mg-form-label">탈퇴일</label>
                            <input type="text" class="mg-form-input" value="<?php echo $mb['mb_leave_date'] ?: '-'; ?>" disabled>
                        </div>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">가입일</label>
                        <input type="text" class="mg-form-input" value="<?php echo $mb['mb_datetime']; ?>" disabled>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">최근 로그인</label>
                        <input type="text" class="mg-form-input" value="<?php echo $mb['mb_today_login'] ?: '-'; ?>" disabled>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">가입 IP</label>
                        <input type="text" class="mg-form-input" value="<?php echo $mb['mb_ip'] ?: '-'; ?>" disabled>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
                <a href="<?php echo G5_ADMIN_URL; ?>/morgan/member_list.php" class="mg-btn mg-btn-secondary">목록으로</a>
            </div>
        </form>
    </div>

    <!-- 캐릭터 목록 -->
    <div style="width:350px; flex-shrink:0;">
        <div class="mg-card">
            <div class="mg-card-header">보유 캐릭터 (<?php echo count($characters); ?>)</div>
            <div class="mg-card-body" style="padding:0;">
                <?php if (count($characters) == 0) { ?>
                <div style="padding:2rem; text-align:center; color:var(--mg-text-muted);">등록된 캐릭터가 없습니다.</div>
                <?php } ?>
                <?php foreach ($characters as $ch) {
                    $state_badge = '';
                    if ($ch['ch_state'] == 'approved') $state_badge = '<span class="mg-badge mg-badge-success">승인</span>';
                    elseif ($ch['ch_state'] == 'pending') $state_badge = '<span class="mg-badge mg-badge-warning">대기</span>';
                    elseif ($ch['ch_state'] == 'editing') $state_badge = '<span class="mg-badge">작성중</span>';
                ?>
                <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1.25rem; border-bottom:1px solid var(--mg-bg-tertiary);">
                    <?php if ($ch['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$ch['ch_thumb']; ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                    <?php } else { ?>
                    <div style="width:36px; height:36px; border-radius:50%; background:var(--mg-bg-tertiary); display:flex; align-items:center; justify-content:center; font-weight:bold; color:var(--mg-accent); font-size:0.875rem;">
                        <?php echo mb_substr($ch['ch_name'], 0, 1); ?>
                    </div>
                    <?php } ?>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:0.875rem; font-weight:600;">
                            <?php echo htmlspecialchars($ch['ch_name']); ?>
                            <?php if ($ch['ch_main']) echo '<span style="color:var(--mg-accent); font-size:0.75rem; font-weight:600;"> [대표]</span>'; ?>
                        </div>
                        <div style="font-size:0.75rem; color:var(--mg-text-muted);">
                            <?php echo $state_badge; ?>
                        </div>
                    </div>
                    <a href="<?php echo G5_ADMIN_URL; ?>/morgan/character_form.php?ch_id=<?php echo $ch['ch_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">수정</a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

</div>

<?php include_once __DIR__.'/_tail.php'; ?>
