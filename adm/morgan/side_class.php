<?php
/**
 * Morgan Edition - 소속/유형 관리
 */

$sub_menu = "800400";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$token = get_admin_token();

$side_label = mg_config('side_title', '소속');
$class_label = mg_config('class_title', '유형');

// 소속 목록
$sides = array();
$result = sql_query("SELECT * FROM {$g5['mg_side_table']} ORDER BY side_order, side_id");
if ($result) {
    while ($row = sql_fetch_array($result)) {
        $sides[] = $row;
    }
}

// 유형 목록
$classes = array();
$result = sql_query("SELECT c.*, s.side_name FROM {$g5['mg_class_table']} c
    LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
    ORDER BY c.class_order, c.class_id");
if ($result) {
    while ($row = sql_fetch_array($result)) {
        $classes[] = $row;
    }
}

$g5['title'] = '소속/유형 관리';
require_once __DIR__.'/_head.php';
?>

<!-- ===== 소속 관리 ===== -->
<div class="mg-card sc-section" data-type="side">
    <div class="mg-card-header" style="cursor:pointer;display:flex;justify-content:space-between;align-items:center;user-select:none;" onclick="toggleSection(this)">
        <div>
            <?php echo $side_label; ?> 관리
            <span style="font-weight:normal;font-size:0.875rem;color:var(--mg-text-muted);margin-left:0.75rem;">
                <?php echo count($sides); ?>개
            </span>
        </div>
        <svg class="sc-chevron" style="width:22px;height:22px;transition:transform .2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <div class="sc-body">
        <div class="mg-card-body" style="padding:0;">
            <form name="fsidelist" id="fsidelist" method="post" action="./side_class_update.php" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="type" value="side">

                <div style="overflow-x:auto;">
                <table class="mg-table" style="min-width:550px;">
                    <thead>
                        <tr>
                            <th style="width:30px;"></th>
                            <th style="width:36px;"><input type="checkbox" onclick="checkAll(this, 'fsidelist');"></th>
                            <th>소속명</th>
                            <th style="width:120px;">아이콘</th>
                            <th style="width:50px;">사용</th>
                            <th style="width:40px;">ID</th>
                        </tr>
                    </thead>
                    <tbody id="side-sortable">
                        <?php foreach ($sides as $side) {
                            $icon_val = $side['side_image'] ?? '';
                            $is_image = $icon_val && (strpos($icon_val, '/') !== false || strpos($icon_val, 'http') === 0);
                        ?>
                        <tr data-id="<?php echo $side['side_id']; ?>">
                            <td style="cursor:grab;text-align:center;color:var(--mg-text-muted);" class="drag-handle">≡</td>
                            <td><input type="checkbox" name="chk[]" value="<?php echo $side['side_id']; ?>"></td>
                            <td>
                                <input type="hidden" name="item_id[]" value="<?php echo $side['side_id']; ?>">
                                <input type="text" name="item_name[]" value="<?php echo htmlspecialchars($side['side_name'] ?? ''); ?>" class="mg-form-input">
                            </td>
                            <td>
                                <input type="hidden" name="item_icon[]" value="<?php echo htmlspecialchars($icon_val); ?>">
                                <?php if ($is_image) { ?>
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <img src="<?php echo htmlspecialchars($icon_val); ?>" style="width:24px;height:24px;object-fit:contain;">
                                    <label style="color:var(--mg-error);font-size:0.75rem;cursor:pointer;">
                                        <input type="checkbox" name="del_icon[<?php echo $side['side_id']; ?>]" value="1"> 삭제
                                    </label>
                                </div>
                                <?php } elseif ($icon_val) { ?>
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;color:var(--mg-text-secondary);">
                                    <?php echo mg_icon($icon_val, 'w-4 h-4'); ?>
                                    <span style="font-size:0.75rem;"><?php echo htmlspecialchars($icon_val); ?></span>
                                </span>
                                <?php } else { ?>
                                <span style="color:var(--mg-text-muted);font-size:0.875rem;">-</span>
                                <?php } ?>
                            </td>
                            <td style="text-align:center;">
                                <input type="checkbox" name="item_use[<?php echo $side['side_id']; ?>]" value="1" <?php echo $side['side_use'] ? 'checked' : ''; ?>>
                            </td>
                            <td style="text-align:center;color:var(--mg-text-muted);font-size:0.85rem;"><?php echo $side['side_id']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot style="background:var(--mg-bg-primary);">
                        <tr>
                            <td></td>
                            <td></td>
                            <td><input type="text" name="new_item_name" value="" class="mg-form-input" placeholder="새 소속명"></td>
                            <td>
                                <div style="display:flex;gap:0.5rem;margin-bottom:0.25rem;">
                                    <label style="font-size:0.75rem;display:flex;align-items:center;gap:0.2rem;cursor:pointer;">
                                        <input type="radio" name="new_side_icon_type" value="text" checked onchange="toggleIconInput('side');">
                                        <span>텍스트</span>
                                    </label>
                                    <label style="font-size:0.75rem;display:flex;align-items:center;gap:0.2rem;cursor:pointer;">
                                        <input type="radio" name="new_side_icon_type" value="file" onchange="toggleIconInput('side');">
                                        <span>파일</span>
                                    </label>
                                </div>
                                <div id="new_side_icon_text">
                                    <input type="text" name="new_item_icon" value="" class="mg-form-input" placeholder="아이콘명">
                                </div>
                                <div id="new_side_icon_file" style="display:none;">
                                    <input type="file" name="new_item_icon_file" accept="image/*" class="mg-form-input" style="padding:0.25rem;font-size:0.75rem;">
                                </div>
                            </td>
                            <td style="text-align:center;"><input type="checkbox" name="new_item_use" value="1" checked></td>
                            <td style="text-align:center;color:var(--mg-accent);font-size:0.75rem;">NEW</td>
                        </tr>
                    </tfoot>
                </table>
                </div>

                <div style="padding:0.75rem 1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                    <button type="submit" name="btn_submit" class="mg-btn mg-btn-primary mg-btn-sm">저장</button>
                    <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 소속을 삭제하시겠습니까?');">선택 삭제</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== 유형 관리 ===== -->
<div class="mg-card sc-section" style="margin-top:1.5rem;" data-type="class">
    <div class="mg-card-header" style="cursor:pointer;display:flex;justify-content:space-between;align-items:center;user-select:none;" onclick="toggleSection(this)">
        <div>
            <?php echo $class_label; ?> 관리
            <span style="font-weight:normal;font-size:0.875rem;color:var(--mg-text-muted);margin-left:0.75rem;">
                <?php echo count($classes); ?>개
            </span>
        </div>
        <svg class="sc-chevron" style="width:22px;height:22px;transition:transform .2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <div class="sc-body">
        <div class="mg-card-body" style="padding:0;">
            <form name="fclasslist" id="fclasslist" method="post" action="./side_class_update.php" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="type" value="class">

                <div style="overflow-x:auto;">
                <table class="mg-table" style="min-width:650px;">
                    <thead>
                        <tr>
                            <th style="width:30px;"></th>
                            <th style="width:36px;"><input type="checkbox" onclick="checkAll(this, 'fclasslist');"></th>
                            <th>유형명</th>
                            <th style="width:140px;">소속</th>
                            <th style="width:120px;">아이콘</th>
                            <th style="width:50px;">사용</th>
                            <th style="width:40px;">ID</th>
                        </tr>
                    </thead>
                    <tbody id="class-sortable">
                        <?php foreach ($classes as $class) {
                            $icon_val = $class['class_image'] ?? '';
                            $is_image = $icon_val && (strpos($icon_val, '/') !== false || strpos($icon_val, 'http') === 0);
                        ?>
                        <tr data-id="<?php echo $class['class_id']; ?>">
                            <td style="cursor:grab;text-align:center;color:var(--mg-text-muted);" class="drag-handle">≡</td>
                            <td><input type="checkbox" name="chk[]" value="<?php echo $class['class_id']; ?>"></td>
                            <td>
                                <input type="hidden" name="item_id[]" value="<?php echo $class['class_id']; ?>">
                                <input type="text" name="item_name[]" value="<?php echo htmlspecialchars($class['class_name'] ?? ''); ?>" class="mg-form-input">
                            </td>
                            <td>
                                <select name="item_side_id[]" class="mg-form-input">
                                    <option value="0">공용 (전체)</option>
                                    <?php foreach ($sides as $side) { ?>
                                    <option value="<?php echo $side['side_id']; ?>" <?php echo ($class['side_id'] ?? 0) == $side['side_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($side['side_name']); ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" name="item_icon[]" value="<?php echo htmlspecialchars($icon_val); ?>">
                                <?php if ($is_image) { ?>
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <img src="<?php echo htmlspecialchars($icon_val); ?>" style="width:24px;height:24px;object-fit:contain;">
                                    <label style="color:var(--mg-error);font-size:0.75rem;cursor:pointer;">
                                        <input type="checkbox" name="del_icon[<?php echo $class['class_id']; ?>]" value="1"> 삭제
                                    </label>
                                </div>
                                <?php } elseif ($icon_val) { ?>
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;color:var(--mg-text-secondary);">
                                    <?php echo mg_icon($icon_val, 'w-4 h-4'); ?>
                                    <span style="font-size:0.75rem;"><?php echo htmlspecialchars($icon_val); ?></span>
                                </span>
                                <?php } else { ?>
                                <span style="color:var(--mg-text-muted);font-size:0.875rem;">-</span>
                                <?php } ?>
                            </td>
                            <td style="text-align:center;">
                                <input type="checkbox" name="item_use[<?php echo $class['class_id']; ?>]" value="1" <?php echo $class['class_use'] ? 'checked' : ''; ?>>
                            </td>
                            <td style="text-align:center;color:var(--mg-text-muted);font-size:0.85rem;"><?php echo $class['class_id']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot style="background:var(--mg-bg-primary);">
                        <tr>
                            <td></td>
                            <td></td>
                            <td><input type="text" name="new_item_name" value="" class="mg-form-input" placeholder="새 유형명"></td>
                            <td>
                                <select name="new_item_side_id" class="mg-form-input">
                                    <option value="0">공용 (전체)</option>
                                    <?php foreach ($sides as $side) { ?>
                                    <option value="<?php echo $side['side_id']; ?>"><?php echo htmlspecialchars($side['side_name']); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <div style="display:flex;gap:0.5rem;margin-bottom:0.25rem;">
                                    <label style="font-size:0.75rem;display:flex;align-items:center;gap:0.2rem;cursor:pointer;">
                                        <input type="radio" name="new_class_icon_type" value="text" checked onchange="toggleIconInput('class');">
                                        <span>텍스트</span>
                                    </label>
                                    <label style="font-size:0.75rem;display:flex;align-items:center;gap:0.2rem;cursor:pointer;">
                                        <input type="radio" name="new_class_icon_type" value="file" onchange="toggleIconInput('class');">
                                        <span>파일</span>
                                    </label>
                                </div>
                                <div id="new_class_icon_text">
                                    <input type="text" name="new_item_icon" value="" class="mg-form-input" placeholder="아이콘명">
                                </div>
                                <div id="new_class_icon_file" style="display:none;">
                                    <input type="file" name="new_item_icon_file" accept="image/*" class="mg-form-input" style="padding:0.25rem;font-size:0.75rem;">
                                </div>
                            </td>
                            <td style="text-align:center;"><input type="checkbox" name="new_item_use" value="1" checked></td>
                            <td style="text-align:center;color:var(--mg-accent);font-size:0.75rem;">NEW</td>
                        </tr>
                    </tfoot>
                </table>
                </div>

                <div style="padding:0.75rem 1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                    <button type="submit" name="btn_submit" class="mg-btn mg-btn-primary mg-btn-sm">저장</button>
                    <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 유형을 삭제하시겠습니까?');">선택 삭제</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mg-alert mg-alert-info" style="margin-top:1rem;line-height:1.8;">
    <strong>사용 안내</strong><br>
    <strong>· 소속</strong> — 캐릭터가 속하는 팩션(세력, 진영, 소속 단체 등)을 등록합니다.<br>
    <strong>· 유형</strong> — 종족, 직업 등 세계관 내에서 선택 가능한 하위 분류를 등록합니다.<br>
    <strong>· 소속별 유형</strong> — 유형의 "소속" 칸에서 특정 소속을 지정하면, 캐릭터 생성 시 해당 소속을 선택한 경우에만 표시됩니다. "공용"은 소속에 관계없이 항상 선택 가능합니다.<br>
    <strong>· 유형만 사용 / 소속만 사용</strong> — 둘 중 하나만 등록해도 됩니다. 등록되지 않은 항목은 캐릭터 폼에 표시되지 않습니다.<br>
    <strong>· 표시 명칭 변경</strong> — 관리자 설정 &gt; 캐릭터에서 "소속"과 "유형"의 표시명을 자유롭게 바꿀 수 있습니다. (예: 세력/종족, 학과/학년 등)<br>
    <strong>· 아이콘</strong> — Heroicons 이름(shield, star, fire 등)을 입력하거나 이미지 파일을 업로드합니다.<br>
    <strong>· 정렬</strong> — ≡ 핸들을 드래그하여 순서를 변경합니다. 변경 즉시 저장됩니다.
</div>

<style>
.sc-body { transition: max-height .3s ease, opacity .2s ease; overflow: hidden; }
.sc-section.collapsed .sc-body { max-height: 0 !important; opacity: 0; }
.sc-section.collapsed .sc-chevron { transform: rotate(-90deg); }
.sc-chevron { flex-shrink:0; }
.drag-handle { font-size:1.2rem; min-width:44px; min-height:44px; touch-action:none; line-height:44px; }
tr.dragging { opacity:0.5; background:var(--mg-bg-tertiary) !important; }
tr.drag-over td { border-top:2px solid var(--mg-accent) !important; }
/* 파일 input 넘침 방지 */
input[type="file"].mg-form-input { min-width:0; overflow:hidden; }
</style>

<script>
// === 접기/펼치기 ===
function toggleSection(header) {
    header.closest('.sc-section').classList.toggle('collapsed');
}

// === 체크박스 전체선택 ===
function checkAll(el, formId) {
    var form = document.getElementById(formId);
    var chks = form.querySelectorAll('input[name="chk[]"]');
    chks.forEach(function(chk) { chk.checked = el.checked; });
}

// === 아이콘 입력 토글 ===
function toggleIconInput(prefix) {
    var type = document.querySelector('input[name="new_' + prefix + '_icon_type"]:checked').value;
    document.getElementById('new_' + prefix + '_icon_text').style.display = type === 'text' ? '' : 'none';
    document.getElementById('new_' + prefix + '_icon_file').style.display = type === 'file' ? '' : 'none';
}

// === 드래그 정렬 (소속/유형 각각 독립) ===
(function() {
    ['side-sortable', 'class-sortable'].forEach(function(tbodyId) {
        var tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        var type = tbodyId.replace('-sortable', '');
        var _dragRow = null;

        // 마우스 드래그
        tbody.addEventListener('mousedown', function(e) {
            var handle = e.target.closest('.drag-handle');
            if (!handle) return;
            e.preventDefault();
            _dragRow = handle.closest('tr');
            _dragRow.classList.add('dragging');

            function onMove(ev) {
                var target = getRowFromPoint(tbody, ev.clientY);
                clearOver(tbody);
                if (target && target !== _dragRow) target.classList.add('drag-over');
            }
            function onUp(ev) {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                var target = getRowFromPoint(tbody, ev.clientY);
                clearOver(tbody);
                if (_dragRow) _dragRow.classList.remove('dragging');
                if (target && target !== _dragRow && _dragRow) {
                    tbody.insertBefore(_dragRow, target);
                    saveOrder(tbody, type);
                }
                _dragRow = null;
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });

        // 터치 드래그
        tbody.addEventListener('touchstart', function(e) {
            var handle = e.target.closest('.drag-handle');
            if (!handle) return;
            _dragRow = handle.closest('tr');
            _dragRow.classList.add('dragging');

            function onMove(ev) {
                ev.preventDefault();
                var touch = ev.touches[0];
                var target = getRowFromPoint(tbody, touch.clientY);
                clearOver(tbody);
                if (target && target !== _dragRow) target.classList.add('drag-over');
            }
            function onEnd(ev) {
                tbody.removeEventListener('touchmove', onMove);
                tbody.removeEventListener('touchend', onEnd);
                tbody.removeEventListener('touchcancel', onEnd);
                var touch = ev.changedTouches[0];
                var target = getRowFromPoint(tbody, touch.clientY);
                clearOver(tbody);
                if (_dragRow) _dragRow.classList.remove('dragging');
                if (target && target !== _dragRow && _dragRow) {
                    tbody.insertBefore(_dragRow, target);
                    saveOrder(tbody, type);
                }
                _dragRow = null;
            }
            tbody.addEventListener('touchmove', onMove, {passive: false});
            tbody.addEventListener('touchend', onEnd);
            tbody.addEventListener('touchcancel', onEnd);
        }, {passive: true});
    });

    function getRowFromPoint(tbody, y) {
        var rows = tbody.querySelectorAll('tr[data-id]');
        for (var i = 0; i < rows.length; i++) {
            var rect = rows[i].getBoundingClientRect();
            if (y < rect.top + rect.height / 2) return rows[i];
        }
        return null;
    }

    function clearOver(tbody) {
        tbody.querySelectorAll('.drag-over').forEach(function(el) { el.classList.remove('drag-over'); });
    }

    function saveOrder(tbody, type) {
        var ids = [];
        tbody.querySelectorAll('tr[data-id]').forEach(function(row) {
            ids.push(row.getAttribute('data-id'));
        });

        var fd = new FormData();
        fd.append('mode', 'reorder');
        fd.append('type', type);
        ids.forEach(function(id) { fd.append('order[]', id); });

        fetch('./side_class_update.php', { method: 'POST', body: fd });
    }
})();
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
