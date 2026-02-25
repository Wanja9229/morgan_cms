<?php
/**
 * Morgan Edition - 게시판 그룹 관리
 */

$sub_menu = "800180";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 그룹 목록
$sql = "SELECT g.*, (SELECT COUNT(*) FROM {$g5['board_table']} WHERE gr_id = g.gr_id) as board_count
        FROM {$g5['group_table']} g
        ORDER BY g.gr_order, g.gr_id";
$result = sql_query($sql);

$groups = array();
while ($row = sql_fetch_array($result)) {
    $groups[] = $row;
}

$g5['title'] = '게시판 그룹 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 그룹</div>
        <div class="mg-stat-value"><?php echo count($groups); ?></div>
    </div>
</div>

<!-- 그룹 추가 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-header">그룹 추가</div>
    <div class="mg-card-body">
        <form method="post" action="./boardgroup_update.php" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="w" value="">
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">그룹 ID *</label>
                <input type="text" name="gr_id" class="mg-form-input" required pattern="[a-z0-9_]+" style="width:150px;" placeholder="영문소문자+숫자">
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">그룹명 *</label>
                <input type="text" name="gr_subject" class="mg-form-input" required style="width:200px;">
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">관리자</label>
                <input type="text" name="gr_admin" class="mg-form-input" style="width:120px;" placeholder="회원 ID">
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">순서</label>
                <input type="number" name="gr_order" value="0" class="mg-form-input" style="width:80px;">
            </div>
            <button type="submit" class="mg-btn mg-btn-primary">추가</button>
        </form>
    </div>
</div>

<!-- 그룹 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:120px;">그룹 ID</th>
                    <th>그룹명</th>
                    <th style="width:100px;">관리자</th>
                    <th style="width:80px;">게시판</th>
                    <th style="width:80px;">순서</th>
                    <th style="width:150px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $gr) { ?>
                <tr>
                    <td><code><?php echo $gr['gr_id']; ?></code></td>
                    <td>
                        <strong><?php echo htmlspecialchars($gr['gr_subject']); ?></strong>
                    </td>
                    <td style="font-size:0.85rem;color:var(--mg-text-muted);">
                        <?php echo $gr['gr_admin'] ?: '-'; ?>
                    </td>
                    <td style="text-align:center;">
                        <a href="./board_list.php?gr_id=<?php echo $gr['gr_id']; ?>" style="color:var(--mg-accent);">
                            <?php echo $gr['board_count']; ?>개
                        </a>
                    </td>
                    <td style="text-align:center;"><?php echo $gr['gr_order']; ?></td>
                    <td>
                        <div style="display:flex;gap:0.25rem;">
                            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editGroup('<?php echo $gr['gr_id']; ?>', '<?php echo htmlspecialchars($gr['gr_subject'], ENT_QUOTES); ?>', '<?php echo $gr['gr_admin']; ?>', <?php echo $gr['gr_order']; ?>)">수정</button>
                            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteGroup('<?php echo $gr['gr_id']; ?>', <?php echo $gr['board_count']; ?>)">삭제</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($groups)) { ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 그룹이 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 수정 모달 -->
<div id="edit-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:450px;">
        <div class="mg-modal-header">
            <h3>그룹 수정</h3>
            <button type="button" class="mg-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="post" action="./boardgroup_update.php">
            <input type="hidden" name="w" value="u">
            <input type="hidden" name="old_gr_id" id="edit_old_gr_id" value="">
            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">그룹 ID</label>
                    <input type="text" id="edit_gr_id" class="mg-form-input" readonly style="background:var(--mg-bg-tertiary);">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">그룹명 *</label>
                    <input type="text" name="gr_subject" id="edit_gr_subject" class="mg-form-input" required>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">관리자</label>
                    <input type="text" name="gr_admin" id="edit_gr_admin" class="mg-form-input" placeholder="회원 ID">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">순서</label>
                    <input type="number" name="gr_order" id="edit_gr_order" class="mg-form-input">
                </div>
            </div>
            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
function editGroup(gr_id, gr_subject, gr_admin, gr_order) {
    document.getElementById('edit_old_gr_id').value = gr_id;
    document.getElementById('edit_gr_id').value = gr_id;
    document.getElementById('edit_gr_subject').value = gr_subject;
    document.getElementById('edit_gr_admin').value = gr_admin;
    document.getElementById('edit_gr_order').value = gr_order;
    document.getElementById('edit-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

function deleteGroup(gr_id, board_count) {
    if (board_count > 0) {
        alert('이 그룹에 속한 게시판이 ' + board_count + '개 있습니다.\n게시판을 먼저 삭제하거나 다른 그룹으로 이동해주세요.');
        return;
    }

    if (!confirm('이 그룹을 삭제하시겠습니까?')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = './boardgroup_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="gr_id" value="' + gr_id + '">';
    document.body.appendChild(form);
    form.submit();
}

// 모달 외부 클릭 시 닫기
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeModal();
});
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
