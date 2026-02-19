<?php
/**
 * Morgan Edition - 스태프 관리
 */

$sub_menu = "800060";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// ==========================================
// AJAX: 회원 검색
// ==========================================
if (isset($_GET['ajax_member_search'])) {
    header('Content-Type: application/json; charset=utf-8');
    $keyword = trim($_GET['keyword'] ?? '');
    $results = array();
    if (mb_strlen($keyword) >= 1) {
        $keyword = sql_real_escape_string($keyword);
        $sql = "SELECT mb_id, mb_nick, mb_email, mb_level
                FROM {$g5['member_table']}
                WHERE (mb_id LIKE '%{$keyword}%' OR mb_nick LIKE '%{$keyword}%')
                AND mb_leave_date = ''
                ORDER BY mb_nick
                LIMIT 10";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $results[] = array(
                'mb_id'    => $row['mb_id'],
                'mb_nick'  => $row['mb_nick'],
                'mb_email' => $row['mb_email'],
                'mb_level' => (int)$row['mb_level'],
            );
        }
    }
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
    exit;
}

// ==========================================
// AJAX: 역할 데이터 (편집용)
// ==========================================
if (isset($_GET['ajax_role_data'])) {
    header('Content-Type: application/json; charset=utf-8');
    $sr_id = (int)$_GET['sr_id'];
    $role = mg_get_staff_role($sr_id);
    echo json_encode($role ?: null, JSON_UNESCAPED_UNICODE);
    exit;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'roles';

// 데이터 로드
$roles = mg_get_staff_roles();
$staff_members = mg_get_staff_members();
$perm_groups = mg_staff_perm_groups();

// 스태프를 mb_id 기준으로 그룹핑
$grouped_staff = array();
foreach ($staff_members as $sm) {
    if (!isset($grouped_staff[$sm['mb_id']])) {
        $grouped_staff[$sm['mb_id']] = array(
            'mb_id'    => $sm['mb_id'],
            'mb_nick'  => $sm['mb_nick'],
            'mb_email' => $sm['mb_email'],
            'mb_level' => $sm['mb_level'],
            'roles'    => array(),
            'first_created' => $sm['sm_created'],
        );
    }
    $grouped_staff[$sm['mb_id']]['roles'][] = array(
        'sm_id'    => $sm['sm_id'],
        'sr_id'    => $sm['sr_id'],
        'sr_name'  => $sm['sr_name'],
        'sr_color' => $sm['sr_color'],
    );
}

$g5['title'] = '스태프 관리';
include_once('./_head.php');
?>

<div class="mg-admin-content">
    <h2 class="mg-admin-title">
        <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
        </svg>
        스태프 관리
    </h2>

    <!-- 탭 -->
    <div class="mg-tabs mb-6">
        <a href="?tab=roles" class="mg-tab <?php echo $tab === 'roles' ? 'active' : ''; ?>">역할 관리</a>
        <a href="?tab=members" class="mg-tab <?php echo $tab === 'members' ? 'active' : ''; ?>">스태프 목록</a>
    </div>

<?php if ($tab === 'roles'): ?>
    <!-- ============================================ -->
    <!-- 역할 관리 탭 -->
    <!-- ============================================ -->
    <div class="mg-card">
        <div class="mg-card-header flex items-center justify-between">
            <span>역할 목록</span>
            <button type="button" onclick="openRoleModal(0)" class="mg-btn mg-btn-primary text-sm">
                + 역할 추가
            </button>
        </div>
        <div class="mg-card-body p-0">
            <?php if (empty($roles)): ?>
            <div class="p-6 text-center text-mg-text-muted">등록된 역할이 없습니다.</div>
            <?php else: ?>
            <table class="mg-table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>역할명</th>
                        <th>설명</th>
                        <th class="w-20">인원</th>
                        <th class="w-32">권한 수</th>
                        <th class="w-36">관리</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($roles as $i => $role):
                    $perms = json_decode($role['sr_permissions'], true);
                    $perm_count = is_array($perms) ? count($perms) : 0;
                    $member_count = mg_staff_role_count($role['sr_id']);
                ?>
                    <tr>
                        <td class="text-mg-text-muted"><?php echo $i + 1; ?></td>
                        <td>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:<?php echo htmlspecialchars($role['sr_color']); ?>"></span>
                                <strong><?php echo htmlspecialchars($role['sr_name']); ?></strong>
                            </span>
                        </td>
                        <td class="text-mg-text-secondary"><?php echo htmlspecialchars($role['sr_description']); ?></td>
                        <td class="text-center"><?php echo number_format($member_count); ?>명</td>
                        <td class="text-center"><?php echo $perm_count; ?>개 메뉴</td>
                        <td>
                            <div class="flex gap-2">
                                <button type="button" onclick="openRoleModal(<?php echo $role['sr_id']; ?>)" class="mg-btn-sm mg-btn-outline">수정</button>
                                <?php if ($member_count == 0): ?>
                                <form method="post" action="./staff_update.php" onsubmit="return confirm('이 역할을 삭제하시겠습니까?');" class="inline">
                                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                                    <input type="hidden" name="mode" value="role_delete">
                                    <input type="hidden" name="sr_id" value="<?php echo $role['sr_id']; ?>">
                                    <button type="submit" class="mg-btn-sm mg-btn-danger">삭제</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- 역할 편집 모달 -->
    <div id="roleModal" class="mg-modal" style="display:none;">
        <div class="mg-modal-overlay" onclick="closeRoleModal()"></div>
        <div class="mg-modal-content" style="max-width:640px; max-height:85vh; overflow-y:auto;">
            <div class="mg-modal-header">
                <h3 id="roleModalTitle">역할 추가</h3>
                <button type="button" onclick="closeRoleModal()" class="mg-modal-close">&times;</button>
            </div>
            <form method="post" action="./staff_update.php" id="roleForm">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="mode" id="roleMode" value="role_add">
                <input type="hidden" name="sr_id" id="roleSrId" value="0">

                <div class="mg-modal-body space-y-4">
                    <div class="mg-form-group">
                        <label class="mg-form-label">역할명 <span class="text-red-400">*</span></label>
                        <input type="text" name="sr_name" id="roleName" required class="mg-form-input" placeholder="예: 운영 보조">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">설명</label>
                        <input type="text" name="sr_description" id="roleDesc" class="mg-form-input" placeholder="이 역할의 설명">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">뱃지 색상</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="sr_color" id="roleColor" value="#f59f0a" class="w-10 h-10 rounded cursor-pointer" style="background:transparent; border:1px solid var(--mg-bg-tertiary);">
                            <span id="roleColorHex" class="text-sm text-mg-text-muted">#f59f0a</span>
                        </div>
                    </div>

                    <!-- 권한 설정 -->
                    <div class="border-t border-mg-bg-tertiary pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="mg-form-label mb-0">권한 설정</label>
                            <label class="flex items-center gap-2 text-sm text-mg-text-secondary cursor-pointer">
                                <input type="checkbox" id="permSelectAll" onchange="toggleAllPerms(this.checked)" class="mg-checkbox">
                                전체 선택 (r/w/d)
                            </label>
                        </div>

                        <?php foreach ($perm_groups as $group_name => $perms): ?>
                        <div class="mb-3">
                            <div class="text-xs font-semibold text-mg-text-muted uppercase tracking-wider mb-2"><?php echo $group_name; ?></div>
                            <?php foreach ($perms as $pkey => $plabel): ?>
                            <div class="flex items-center justify-between py-1.5 px-2 rounded hover:bg-mg-bg-tertiary/50">
                                <span class="text-sm text-mg-text-primary"><?php echo $plabel; ?></span>
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-1 text-xs text-mg-text-secondary cursor-pointer">
                                        <input type="checkbox" name="perms[<?php echo $pkey; ?>][r]" value="1" class="mg-checkbox perm-cb perm-r" data-key="<?php echo $pkey; ?>"> 읽기
                                    </label>
                                    <label class="flex items-center gap-1 text-xs text-mg-text-secondary cursor-pointer">
                                        <input type="checkbox" name="perms[<?php echo $pkey; ?>][w]" value="1" class="mg-checkbox perm-cb perm-w" data-key="<?php echo $pkey; ?>"> 쓰기
                                    </label>
                                    <label class="flex items-center gap-1 text-xs text-mg-text-secondary cursor-pointer">
                                        <input type="checkbox" name="perms[<?php echo $pkey; ?>][d]" value="1" class="mg-checkbox perm-cb perm-d" data-key="<?php echo $pkey; ?>"> 삭제
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mg-modal-footer">
                    <button type="button" onclick="closeRoleModal()" class="mg-btn mg-btn-outline">취소</button>
                    <button type="submit" class="mg-btn mg-btn-primary">저장</button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($tab === 'members'): ?>
    <!-- ============================================ -->
    <!-- 스태프 목록 탭 -->
    <!-- ============================================ -->
    <div class="mg-card">
        <div class="mg-card-header flex items-center justify-between">
            <span>스태프 목록 (<?php echo count($grouped_staff); ?>명)</span>
            <?php if (!empty($roles)): ?>
            <button type="button" onclick="openMemberModal()" class="mg-btn mg-btn-primary text-sm">
                + 스태프 추가
            </button>
            <?php endif; ?>
        </div>
        <div class="mg-card-body p-0">
            <?php if (empty($grouped_staff)): ?>
            <div class="p-6 text-center text-mg-text-muted">등록된 스태프가 없습니다.</div>
            <?php else: ?>
            <table class="mg-table">
                <thead>
                    <tr>
                        <th>회원</th>
                        <th>역할</th>
                        <th class="w-36">배정일</th>
                        <th class="w-36">관리</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($grouped_staff as $mb_id => $staff): ?>
                    <tr>
                        <td>
                            <div>
                                <strong class="text-mg-text-primary"><?php echo htmlspecialchars($staff['mb_nick']); ?></strong>
                                <span class="text-xs text-mg-text-muted ml-1">(<?php echo htmlspecialchars($staff['mb_id']); ?>)</span>
                            </div>
                            <div class="text-xs text-mg-text-muted">Lv.<?php echo $staff['mb_level']; ?></div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($staff['roles'] as $r): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white" style="background:<?php echo htmlspecialchars($r['sr_color']); ?>">
                                    <?php echo htmlspecialchars($r['sr_name']); ?>
                                    <button type="button" onclick="removeRole('<?php echo htmlspecialchars($staff['mb_id']); ?>', <?php echo $r['sr_id']; ?>, '<?php echo htmlspecialchars($r['sr_name']); ?>')" class="ml-0.5 opacity-70 hover:opacity-100" title="역할 해제">&times;</button>
                                </span>
                            <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="text-sm text-mg-text-secondary"><?php echo substr($staff['first_created'], 0, 10); ?></td>
                        <td>
                            <div class="flex gap-2">
                                <button type="button" onclick="openAddRoleToMember('<?php echo htmlspecialchars($staff['mb_id']); ?>', '<?php echo htmlspecialchars($staff['mb_nick']); ?>')" class="mg-btn-sm mg-btn-outline">역할 추가</button>
                                <button type="button" onclick="removeMember('<?php echo htmlspecialchars($staff['mb_id']); ?>', '<?php echo htmlspecialchars($staff['mb_nick']); ?>')" class="mg-btn-sm mg-btn-danger">전체 해제</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- 스태프 추가 모달 -->
    <div id="memberModal" class="mg-modal" style="display:none;">
        <div class="mg-modal-overlay" onclick="closeMemberModal()"></div>
        <div class="mg-modal-content" style="max-width:480px;">
            <div class="mg-modal-header">
                <h3 id="memberModalTitle">스태프 추가</h3>
                <button type="button" onclick="closeMemberModal()" class="mg-modal-close">&times;</button>
            </div>
            <form method="post" action="./staff_update.php" id="memberForm">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="mode" id="memberMode" value="member_add">
                <input type="hidden" name="mb_id" id="memberMbId" value="">

                <div class="mg-modal-body space-y-4">
                    <div class="mg-form-group" id="memberSearchGroup">
                        <label class="mg-form-label">회원 검색</label>
                        <div class="relative">
                            <input type="text" id="memberSearchInput" class="mg-form-input" placeholder="아이디 또는 닉네임 입력" autocomplete="off">
                            <div id="memberSearchResults" class="absolute top-full left-0 right-0 mt-1 bg-mg-bg-secondary border border-mg-bg-tertiary rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto" style="display:none;"></div>
                        </div>
                        <div id="memberSelected" class="mt-2" style="display:none;">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-mg-bg-tertiary rounded-lg text-sm">
                                <span id="memberSelectedName"></span>
                                <button type="button" onclick="clearMemberSelection()" class="text-mg-text-muted hover:text-red-400">&times;</button>
                            </span>
                        </div>
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label">역할 선택</label>
                        <select name="sr_id" id="memberRoleSelect" class="mg-form-select" required>
                            <option value="">-- 역할 선택 --</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['sr_id']; ?>"><?php echo htmlspecialchars($role['sr_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mg-modal-footer">
                    <button type="button" onclick="closeMemberModal()" class="mg-btn mg-btn-outline">취소</button>
                    <button type="submit" class="mg-btn mg-btn-primary">배정</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 역할 해제/변경용 hidden form -->
    <form id="staffActionForm" method="post" action="./staff_update.php" style="display:none;">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="hidden" name="mode" id="staffActionMode" value="">
        <input type="hidden" name="mb_id" id="staffActionMbId" value="">
        <input type="hidden" name="sr_id" id="staffActionSrId" value="">
    </form>

<?php endif; ?>
</div>

<script>
// ==========================================
// 역할 모달
// ==========================================
function openRoleModal(srId) {
    var modal = document.getElementById('roleModal');
    var title = document.getElementById('roleModalTitle');
    var form = document.getElementById('roleForm');

    // 체크박스 초기화
    form.querySelectorAll('.perm-cb').forEach(function(cb) { cb.checked = false; });
    document.getElementById('permSelectAll').checked = false;

    if (srId > 0) {
        title.textContent = '역할 수정';
        document.getElementById('roleMode').value = 'role_edit';
        document.getElementById('roleSrId').value = srId;

        // AJAX로 역할 데이터 로드
        fetch('?ajax_role_data=1&sr_id=' + srId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data) return;
                document.getElementById('roleName').value = data.sr_name || '';
                document.getElementById('roleDesc').value = data.sr_description || '';
                document.getElementById('roleColor').value = data.sr_color || '#f59f0a';
                document.getElementById('roleColorHex').textContent = data.sr_color || '#f59f0a';

                // 권한 체크박스 설정
                var perms = {};
                try { perms = JSON.parse(data.sr_permissions); } catch(e) {}
                Object.keys(perms).forEach(function(pkey) {
                    var auth = perms[pkey] || '';
                    if (auth.indexOf('r') !== -1) {
                        var cb = form.querySelector('input[name="perms[' + pkey + '][r]"]');
                        if (cb) cb.checked = true;
                    }
                    if (auth.indexOf('w') !== -1) {
                        var cb = form.querySelector('input[name="perms[' + pkey + '][w]"]');
                        if (cb) cb.checked = true;
                    }
                    if (auth.indexOf('d') !== -1) {
                        var cb = form.querySelector('input[name="perms[' + pkey + '][d]"]');
                        if (cb) cb.checked = true;
                    }
                });
            });
    } else {
        title.textContent = '역할 추가';
        document.getElementById('roleMode').value = 'role_add';
        document.getElementById('roleSrId').value = '0';
        document.getElementById('roleName').value = '';
        document.getElementById('roleDesc').value = '';
        document.getElementById('roleColor').value = '#f59f0a';
        document.getElementById('roleColorHex').textContent = '#f59f0a';
    }

    modal.style.display = 'flex';
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

function toggleAllPerms(checked) {
    document.querySelectorAll('.perm-cb').forEach(function(cb) {
        cb.checked = checked;
    });
}

// 색상 입력 실시간 반영
document.getElementById('roleColor').addEventListener('input', function() {
    document.getElementById('roleColorHex').textContent = this.value;
});

// ==========================================
// 스태프 모달
// ==========================================
var searchTimer = null;

function openMemberModal() {
    document.getElementById('memberModal').style.display = 'flex';
    document.getElementById('memberModalTitle').textContent = '스태프 추가';
    document.getElementById('memberMode').value = 'member_add';
    document.getElementById('memberMbId').value = '';
    document.getElementById('memberRoleSelect').value = '';
    document.getElementById('memberSearchInput').value = '';
    document.getElementById('memberSearchGroup').style.display = '';
    document.getElementById('memberSelected').style.display = 'none';
    document.getElementById('memberSearchResults').style.display = 'none';
}

function openAddRoleToMember(mbId, mbNick) {
    document.getElementById('memberModal').style.display = 'flex';
    document.getElementById('memberModalTitle').textContent = mbNick + ' 역할 추가';
    document.getElementById('memberMode').value = 'member_add';
    document.getElementById('memberMbId').value = mbId;
    document.getElementById('memberRoleSelect').value = '';
    document.getElementById('memberSearchGroup').style.display = 'none';
    document.getElementById('memberSelected').style.display = 'block';
    document.getElementById('memberSelectedName').textContent = mbNick + ' (' + mbId + ')';
}

function closeMemberModal() {
    document.getElementById('memberModal').style.display = 'none';
}

function clearMemberSelection() {
    document.getElementById('memberMbId').value = '';
    document.getElementById('memberSelected').style.display = 'none';
    document.getElementById('memberSearchGroup').style.display = '';
    document.getElementById('memberSearchInput').value = '';
}

// AJAX 회원 검색
document.getElementById('memberSearchInput').addEventListener('input', function() {
    var input = this;
    var keyword = input.value.trim();
    clearTimeout(searchTimer);

    if (keyword.length < 1) {
        document.getElementById('memberSearchResults').style.display = 'none';
        return;
    }

    searchTimer = setTimeout(function() {
        fetch('?ajax_member_search=1&keyword=' + encodeURIComponent(keyword))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var container = document.getElementById('memberSearchResults');
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="px-3 py-2 text-sm text-mg-text-muted">검색 결과 없음</div>';
                    container.style.display = 'block';
                    return;
                }
                var html = '';
                data.forEach(function(m) {
                    html += '<div class="px-3 py-2 hover:bg-mg-bg-tertiary cursor-pointer text-sm" onclick="selectMember(\'' + m.mb_id.replace(/'/g, "\\'") + '\', \'' + m.mb_nick.replace(/'/g, "\\'") + '\')">';
                    html += '<strong>' + escapeHtml(m.mb_nick) + '</strong>';
                    html += ' <span class="text-mg-text-muted">(' + escapeHtml(m.mb_id) + ')</span>';
                    html += ' <span class="text-xs text-mg-text-muted">Lv.' + m.mb_level + '</span>';
                    html += '</div>';
                });
                container.innerHTML = html;
                container.style.display = 'block';
            });
    }, 300);
});

// 검색 외부 클릭 시 결과 닫기
document.addEventListener('click', function(e) {
    var results = document.getElementById('memberSearchResults');
    var input = document.getElementById('memberSearchInput');
    if (results && !results.contains(e.target) && e.target !== input) {
        results.style.display = 'none';
    }
});

function selectMember(mbId, mbNick) {
    document.getElementById('memberMbId').value = mbId;
    document.getElementById('memberSelected').style.display = 'block';
    document.getElementById('memberSelectedName').textContent = mbNick + ' (' + mbId + ')';
    document.getElementById('memberSearchResults').style.display = 'none';
    document.getElementById('memberSearchInput').value = '';
}

// ==========================================
// 스태프 액션 (역할 해제, 전체 해제)
// ==========================================
function removeRole(mbId, srId, roleName) {
    if (!confirm(roleName + ' 역할을 해제하시겠습니까?')) return;
    document.getElementById('staffActionMode').value = 'member_remove';
    document.getElementById('staffActionMbId').value = mbId;
    document.getElementById('staffActionSrId').value = srId;
    document.getElementById('staffActionForm').submit();
}

function removeMember(mbId, mbNick) {
    if (!confirm(mbNick + '의 모든 역할을 해제하시겠습니까?')) return;
    document.getElementById('staffActionMode').value = 'member_remove_all';
    document.getElementById('staffActionMbId').value = mbId;
    document.getElementById('staffActionSrId').value = '';
    document.getElementById('staffActionForm').submit();
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// 폼 제출 전 검증
document.getElementById('memberForm').addEventListener('submit', function(e) {
    if (!document.getElementById('memberMbId').value) {
        e.preventDefault();
        alert('회원을 선택해주세요.');
        return;
    }
    if (!document.getElementById('memberRoleSelect').value) {
        e.preventDefault();
        alert('역할을 선택해주세요.');
        return;
    }
});
</script>

<?php
include_once('./_tail.php');
?>
