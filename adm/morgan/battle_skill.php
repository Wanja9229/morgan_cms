<?php
/**
 * Morgan Edition - 스킬 관리 (목록 + 폼)
 */

$sub_menu = "801920";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// ── POST 처리 ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth_check_menu($auth, $sub_menu, 'w');

    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';
    $sk_id = isset($_POST['sk_id']) ? (int)$_POST['sk_id'] : 0;

    if ($mode === 'delete') {
        $ids = isset($_POST['chk']) ? $_POST['chk'] : array();
        if (!empty($ids)) {
            $ids_safe = implode(',', array_map('intval', $ids));
            sql_query("DELETE FROM {$g5['mg_battle_skill_table']} WHERE sk_id IN ({$ids_safe})");
        }
        $cookie_data = json_encode(array('msg' => count($ids) . '개 스킬이 삭제되었습니다.', 'type' => 'success'));
        setcookie('mg_flash_toast', $cookie_data, time() + 5, '/');
        goto_url('./battle_skill.php');
        exit;
    }

    // add / edit
    $sk_code = isset($_POST['sk_code']) ? trim($_POST['sk_code']) : '';
    $sk_name = isset($_POST['sk_name']) ? trim($_POST['sk_name']) : '';
    if (!$sk_code || !$sk_name) { alert('코드와 이름을 입력해주세요.'); }

    $sk_desc = isset($_POST['sk_desc']) ? trim($_POST['sk_desc']) : '';
    $sk_icon = isset($_POST['sk_icon']) ? trim($_POST['sk_icon']) : '';
    $sk_icon_color = isset($_POST['sk_icon_color']) ? trim($_POST['sk_icon_color']) : '#ffffff';
    $sk_type = isset($_POST['sk_type']) ? $_POST['sk_type'] : 'damage';
    $sk_stamina = max(1, (int)($_POST['sk_stamina'] ?? 2));
    $sk_target = isset($_POST['sk_target']) ? $_POST['sk_target'] : 'enemy_single';
    $sk_target_count = max(1, (int)($_POST['sk_target_count'] ?? 1));
    $sk_base_stat = isset($_POST['sk_base_stat']) ? $_POST['sk_base_stat'] : 'str';
    $sk_multiplier = max(0, (float)($_POST['sk_multiplier'] ?? 1.5));
    $sk_buff_stat = isset($_POST['sk_buff_stat']) ? trim($_POST['sk_buff_stat']) : '';
    $sk_buff_value = max(0, (int)($_POST['sk_buff_value'] ?? 0));
    $sk_buff_turns = max(0, (int)($_POST['sk_buff_turns'] ?? 3));
    $sk_guard_reduction = max(0, min(100, (int)($_POST['sk_guard_reduction'] ?? 0)));
    $sk_stat_req = isset($_POST['sk_stat_req']) ? trim($_POST['sk_stat_req']) : '';
    $sk_unlock_type = isset($_POST['sk_unlock_type']) ? $_POST['sk_unlock_type'] : 'default';
    $sk_unlock_ref = (int)($_POST['sk_unlock_ref'] ?? 0);
    $sk_use = isset($_POST['sk_use']) ? 1 : 0;
    $sk_order = (int)($_POST['sk_order'] ?? 0);

    $esc = function($v) { return sql_real_escape_string($v); };

    if ($mode === 'add') {
        sql_query("INSERT INTO {$g5['mg_battle_skill_table']}
            (sk_code, sk_name, sk_desc, sk_icon, sk_icon_color, sk_type, sk_stamina, sk_target, sk_target_count,
             sk_base_stat, sk_multiplier, sk_buff_stat, sk_buff_value, sk_buff_turns,
             sk_guard_reduction, sk_stat_req, sk_unlock_type, sk_unlock_ref, sk_use, sk_order)
            VALUES ('{$esc($sk_code)}', '{$esc($sk_name)}', '{$esc($sk_desc)}', '{$esc($sk_icon)}', '{$esc($sk_icon_color)}',
                    '{$esc($sk_type)}', {$sk_stamina}, '{$esc($sk_target)}', {$sk_target_count},
                    '{$esc($sk_base_stat)}', {$sk_multiplier}, '{$esc($sk_buff_stat)}', {$sk_buff_value},
                    {$sk_buff_turns}, {$sk_guard_reduction}, '{$esc($sk_stat_req)}',
                    '{$esc($sk_unlock_type)}', {$sk_unlock_ref}, {$sk_use}, {$sk_order})");
        $msg = '스킬이 등록되었습니다.';
    } else {
        sql_query("UPDATE {$g5['mg_battle_skill_table']} SET
            sk_code = '{$esc($sk_code)}', sk_name = '{$esc($sk_name)}', sk_desc = '{$esc($sk_desc)}',
            sk_icon = '{$esc($sk_icon)}', sk_icon_color = '{$esc($sk_icon_color)}', sk_type = '{$esc($sk_type)}', sk_stamina = {$sk_stamina},
            sk_target = '{$esc($sk_target)}', sk_target_count = {$sk_target_count},
            sk_base_stat = '{$esc($sk_base_stat)}', sk_multiplier = {$sk_multiplier},
            sk_buff_stat = '{$esc($sk_buff_stat)}', sk_buff_value = {$sk_buff_value},
            sk_buff_turns = {$sk_buff_turns}, sk_guard_reduction = {$sk_guard_reduction},
            sk_stat_req = '{$esc($sk_stat_req)}', sk_unlock_type = '{$esc($sk_unlock_type)}',
            sk_unlock_ref = {$sk_unlock_ref}, sk_use = {$sk_use}, sk_order = {$sk_order}
            WHERE sk_id = {$sk_id}");
        $msg = '스킬이 수정되었습니다.';
    }

    $cookie_data = json_encode(array('msg' => $msg, 'type' => 'success'));
    setcookie('mg_flash_toast', $cookie_data, time() + 5, '/');
    goto_url('./battle_skill.php');
    exit;
}

// ── 탭: list / form ──
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'list';
$sk_id = isset($_GET['sk_id']) ? (int)$_GET['sk_id'] : 0;

$g5['title'] = '스킬 관리';
include_once('./_head.php');

if ($tab === 'form') {
    // ── 등록/수정 폼 ──
    $item = array(
        'sk_id' => 0, 'sk_code' => '', 'sk_name' => '', 'sk_desc' => '', 'sk_icon' => '',
        'sk_type' => 'damage', 'sk_stamina' => 2, 'sk_target' => 'enemy_single', 'sk_target_count' => 1,
        'sk_base_stat' => 'str', 'sk_multiplier' => '1.50', 'sk_buff_stat' => '', 'sk_buff_value' => 0,
        'sk_buff_turns' => 3, 'sk_guard_reduction' => 0, 'sk_stat_req' => '',
        'sk_unlock_type' => 'default', 'sk_unlock_ref' => 0, 'sk_use' => 1, 'sk_order' => 0
    );
    $is_edit = false;

    if ($sk_id > 0) {
        $loaded = sql_fetch("SELECT * FROM {$g5['mg_battle_skill_table']} WHERE sk_id = {$sk_id}");
        if ($loaded && $loaded['sk_id']) {
            $item = $loaded;
            $is_edit = true;
        }
    }

    $type_options = array('damage' => '데미지', 'heal' => '회복', 'buff' => '버프', 'debuff' => '디버프', 'taunt' => '도발');
    $target_options = array(
        'enemy_single' => '적 1체', 'enemy_all' => '적 전체',
        'ally_single' => '아군 1명', 'ally_multi' => '아군 N명', 'ally_all' => '아군 전체', 'self' => '자신'
    );
    $stat_options = array('str' => 'STR', 'dex' => 'DEX', 'int' => 'INT', 'none' => '없음');
    $unlock_options = array('default' => '기본 제공', 'shop' => '상점 구매', 'drop' => '전투 드랍', 'achievement' => '업적');
?>

<div style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
    <a href="./battle_skill.php" class="mg-btn mg-btn-secondary" style="font-size:0.85rem;">&larr; 목록으로</a>
    <button type="button" onclick="document.getElementById('skillGuideModal').style.display='flex'" class="mg-btn mg-btn-secondary" style="font-size:0.85rem;">&#9432; 스킬 배율 가이드</button>
</div>

<!-- 스킬 배율 가이드 모달 -->
<div id="skillGuideModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:var(--mg-bg-secondary,#2b2d31);border:1px solid var(--mg-bg-tertiary,#313338);border-radius:12px;max-width:760px;width:95%;max-height:85vh;overflow-y:auto;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:1rem;font-weight:700;">스킬 배율 가이드</h3>
            <button type="button" onclick="document.getElementById('skillGuideModal').style.display='none'" style="background:none;border:none;color:var(--mg-text-muted);font-size:1.25rem;cursor:pointer;">&times;</button>
        </div>

        <h4 style="font-weight:600;font-size:0.9rem;margin-bottom:0.5rem;">기본 수치 체계</h4>
        <table class="mg-table" style="font-size:0.8rem;margin-bottom:1.25rem;">
            <thead><tr><th>파생 수치</th><th>계산식</th></tr></thead>
            <tbody>
                <tr><td>ATK (물리 공격력)</td><td>STR &times; 2 + 장비 ATK</td></tr>
                <tr><td>SATK (기술 공격력)</td><td>DEX &times; 2 + 장비 SATK</td></tr>
                <tr><td>INT (회복/지원)</td><td>INT &times; 2 + 장비 SUPPORT</td></tr>
                <tr><td>DEF (방어력)</td><td>(HP + STR) &divide; 4 + 장비 DEF</td></tr>
                <tr><td>MAX HP</td><td>기본HP(100) + HP스탯 &times; 10 + 장비 HP</td></tr>
            </tbody>
        </table>

        <h4 style="font-weight:600;font-size:0.9rem;margin-bottom:0.5rem;">타입별 배율 적용</h4>
        <table class="mg-table" style="font-size:0.8rem;margin-bottom:1.25rem;">
            <thead><tr><th style="width:80px;">타입</th><th>배율 적용 방식</th><th>예시</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>데미지</strong></td>
                    <td>파생 공격력 &times; 배율 &times; 난수(0.9~1.1)</td>
                    <td>기반=STR &rarr; ATK(=STR&times;2) &times; 1.50<br>STR 10이면 약 30 데미지</td>
                </tr>
                <tr>
                    <td><strong>회복</strong></td>
                    <td>INT 파생 &times; 배율</td>
                    <td>기반=INT &rarr; INT&times;2 &times; 1.00<br>INT 10이면 약 20 회복</td>
                </tr>
                <tr>
                    <td><strong>버프</strong></td>
                    <td>대상 스탯을 수치(%)만큼 N횟수 동안 증가<br>배율·기반스탯 미사용</td>
                    <td>대상=ATK, 수치=30, 횟수=3<br>&rarr; 3턴간 ATK +30%</td>
                </tr>
                <tr>
                    <td><strong>디버프</strong></td>
                    <td>적의 스탯을 수치(%)만큼 N횟수 동안 감소<br>배율·기반스탯 미사용</td>
                    <td>대상=DEF, 수치=20, 횟수=2<br>&rarr; 2턴간 적 DEF -20%</td>
                </tr>
                <tr>
                    <td><strong>도발</strong></td>
                    <td>N횟수 동안 보스 반격을 자신에게 유도<br>배율·기반스탯 미사용</td>
                    <td>횟수=3, 수호감소율=30<br>&rarr; 3회 반격 흡수, 피해 30% 감소</td>
                </tr>
            </tbody>
        </table>

        <h4 style="font-weight:600;font-size:0.9rem;margin-bottom:0.5rem;">기반 스탯 &rarr; 파생 수치 매핑</h4>
        <table class="mg-table" style="font-size:0.8rem;margin-bottom:1rem;">
            <thead><tr><th>기반 스탯</th><th>데미지 참조</th><th>회복 참조</th><th>권장 타입</th></tr></thead>
            <tbody>
                <tr><td>STR</td><td>ATK (물리)</td><td>&mdash;</td><td>데미지 (근접)</td></tr>
                <tr><td>DEX</td><td>SATK (기술)</td><td>&mdash;</td><td>데미지 (원거리/기술)</td></tr>
                <tr><td>INT</td><td>&mdash;</td><td>INT &times; 2</td><td>회복, 버프</td></tr>
                <tr><td>NONE</td><td>&mdash;</td><td>&mdash;</td><td>도발, 수호 (스탯 무관)</td></tr>
            </tbody>
        </table>

        <p style="color:var(--mg-text-muted);font-size:0.8rem;line-height:1.6;">
            <strong>수호 감소율</strong>: 도발 사용자가 받는 반격 데미지를 N% 감소. 도발 타입에서만 유효.<br>
            <strong>HP 스탯과 수호</strong>: HP 스탯은 MAX HP 뿐만 아니라 DEF((HP+STR)&divide;4)에도 기여하므로,<br>
            &nbsp;&nbsp;HP를 많이 찍은 탱커는 수호 감소율 + 높은 방어력으로 반격 피해를 이중으로 줄일 수 있음.<br>
            <strong>주사위</strong>: 모든 액션에 1d20 판정. 1=0.3x(대실패) ~ 10=0.95x ~ 20=1.8x(대성공)<br>
            <strong>크리티컬</strong>: 장비 크리티컬률 기반 판정. 적중 시 기본 150% 배율 추가.
        </p>
    </div>
</div>

<form method="post" action="./battle_skill.php">
    <input type="hidden" name="mode" value="<?php echo $is_edit ? 'edit' : 'add'; ?>">
    <input type="hidden" name="sk_id" value="<?php echo (int)$item['sk_id']; ?>">

    <div class="mg-card">
        <div class="mg-card-header"><?php echo $is_edit ? '스킬 수정' : '스킬 등록'; ?></div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">코드 *</label>
                    <input type="text" name="sk_code" value="<?php echo htmlspecialchars($item['sk_code'] ?? ''); ?>" class="mg-form-input" required placeholder="power_attack">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">이름 *</label>
                    <input type="text" name="sk_name" value="<?php echo htmlspecialchars($item['sk_name'] ?? ''); ?>" class="mg-form-input" required>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">아이콘</label>
                    <?php mg_game_icon_picker('sk_icon', $item['sk_icon'] ?? '', array('color' => $item['sk_icon_color'] ?? '#ffffff')); ?>
                </div>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">설명</label>
                <input type="text" name="sk_desc" value="<?php echo htmlspecialchars($item['sk_desc'] ?? ''); ?>" class="mg-form-input">
            </div>

            <h4 style="font-size:0.9rem;font-weight:600;margin:1.25rem 0 0.75rem;color:var(--mg-text-secondary);">스킬 속성</h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">타입</label>
                    <select name="sk_type" class="mg-form-select">
                        <?php foreach ($type_options as $k => $v) { ?>
                            <option value="<?php echo $k; ?>" <?php echo ($item['sk_type'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">기력 소모</label>
                    <input type="number" name="sk_stamina" value="<?php echo (int)($item['sk_stamina'] ?? 2); ?>" class="mg-form-input" min="1">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">대상</label>
                    <select name="sk_target" class="mg-form-select">
                        <?php foreach ($target_options as $k => $v) { ?>
                            <option value="<?php echo $k; ?>" <?php echo ($item['sk_target'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">대상 수</label>
                    <input type="number" name="sk_target_count" value="<?php echo (int)($item['sk_target_count'] ?? 1); ?>" class="mg-form-input" min="1">
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">ally_multi용</p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">기반 스탯</label>
                    <select name="sk_base_stat" class="mg-form-select">
                        <?php foreach ($stat_options as $k => $v) { ?>
                            <option value="<?php echo $k; ?>" <?php echo ($item['sk_base_stat'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">배율</label>
                    <input type="number" name="sk_multiplier" value="<?php echo $item['sk_multiplier'] ?? '1.50'; ?>" class="mg-form-input" step="0.01" min="0">
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header">버프/디버프/수호</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">버프 대상 스탯</label>
                    <?php
                    $buff_stat_options = array(
                        '' => '— 선택 —',
                        'atk' => 'ATK (STR → 물리 공격력)',
                        'satk' => 'SATK (DEX → 기술 공격력)',
                        'def' => 'DEF (방어력)',
                        'max_hp' => 'MAX HP',
                        'int' => 'INT (회복/지원 계열)',
                        'crit_rate' => 'CRIT RATE (치명타 확률)',
                        'crit_mult' => 'CRIT MULT (치명타 배율)',
                        'evasion' => 'EVASION (회피율)',
                    );
                    ?>
                    <select name="sk_buff_stat" class="mg-form-select">
                        <?php foreach ($buff_stat_options as $bk => $bv) { ?>
                            <option value="<?php echo $bk; ?>" <?php echo ($item['sk_buff_stat'] ?? '') === $bk ? 'selected' : ''; ?>><?php echo $bv; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">버프 수치 (%)</label>
                    <input type="number" name="sk_buff_value" value="<?php echo (int)($item['sk_buff_value'] ?? 0); ?>" class="mg-form-input" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">지속 횟수</label>
                    <input type="number" name="sk_buff_turns" value="<?php echo (int)($item['sk_buff_turns'] ?? 3); ?>" class="mg-form-input" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">수호 감소율 (%)</label>
                    <input type="number" name="sk_guard_reduction" value="<?php echo (int)($item['sk_guard_reduction'] ?? 0); ?>" class="mg-form-input" min="0" max="100">
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header">해금 조건</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">해금 방식</label>
                    <select name="sk_unlock_type" class="mg-form-select">
                        <?php foreach ($unlock_options as $k => $v) { ?>
                            <option value="<?php echo $k; ?>" <?php echo ($item['sk_unlock_type'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">해금 참조 ID</label>
                    <input type="number" name="sk_unlock_ref" value="<?php echo (int)($item['sk_unlock_ref'] ?? 0); ?>" class="mg-form-input" min="0">
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">si_id 또는 achievement_id</p>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">스탯 요구 조건</label>
                    <input type="text" name="sk_stat_req" value="<?php echo htmlspecialchars($item['sk_stat_req'] ?? ''); ?>" class="mg-form-input" placeholder="int:15">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">정렬 순서</label>
                    <input type="number" name="sk_order" value="<?php echo (int)($item['sk_order'] ?? 0); ?>" class="mg-form-input">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">활성 상태</label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="sk_use" value="1" <?php echo ($item['sk_use'] ?? 1) ? 'checked' : ''; ?>>
                        사용
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary"><?php echo $is_edit ? '수정' : '등록'; ?></button>
        <a href="./battle_skill.php" class="mg-btn mg-btn-secondary">목록으로</a>
    </div>
</form>

<?php
} else {
    // ── 목록 ──
    $result = sql_query("SELECT * FROM {$g5['mg_battle_skill_table']} ORDER BY sk_order, sk_id");
    $total_count = 0;
?>

<div style="display:flex; justify-content:flex-end; margin-bottom:1rem;">
    <a href="./battle_skill.php?tab=form" class="mg-btn mg-btn-primary" style="font-size:0.85rem;">+ 스킬 등록</a>
</div>

<form method="post" action="./battle_skill.php" id="fskilllist">
    <input type="hidden" name="mode" value="delete">
    <div class="mg-card">
        <div class="mg-card-body" style="padding:0; overflow-x:auto;">
            <table class="mg-table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" onclick="var c=this.form.querySelectorAll('input[name=\'chk[]\']');for(var i=0;i<c.length;i++)c[i].checked=this.checked;"></th>
                        <th>ID</th>
                        <th>아이콘</th>
                        <th>코드</th>
                        <th>이름</th>
                        <th>타입</th>
                        <th>기력</th>
                        <th>대상</th>
                        <th>기반</th>
                        <th>배율</th>
                        <th>해금</th>
                        <th>상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $type_labels = array('damage' => '데미지', 'heal' => '회복', 'buff' => '버프', 'debuff' => '디버프', 'taunt' => '도발');
                $target_labels = array('enemy_single'=>'적1', 'enemy_all'=>'적전체', 'ally_single'=>'아군1', 'ally_multi'=>'아군N', 'ally_all'=>'아군전체', 'self'=>'자신');
                $unlock_labels = array('default'=>'기본', 'shop'=>'상점', 'drop'=>'드랍', 'achievement'=>'업적');
                while ($row = sql_fetch_array($result)) {
                    $total_count++;
                ?>
                    <tr>
                        <td><input type="checkbox" name="chk[]" value="<?php echo $row['sk_id']; ?>"></td>
                        <td><?php echo $row['sk_id']; ?></td>
                        <td style="width:36px;"><?php echo $row['sk_icon'] ? mg_game_icon($row['sk_icon'], '', $row['sk_icon_color'] ?? '#ffffff') : ''; ?></td>
                        <td><code style="font-size:0.8rem;"><?php echo htmlspecialchars($row['sk_code']); ?></code></td>
                        <td><?php echo htmlspecialchars($row['sk_name']); ?></td>
                        <td><?php echo $type_labels[$row['sk_type']] ?? $row['sk_type']; ?></td>
                        <td><?php echo $row['sk_stamina']; ?></td>
                        <td><?php echo $target_labels[$row['sk_target']] ?? $row['sk_target']; ?></td>
                        <td><?php echo strtoupper($row['sk_base_stat']); ?></td>
                        <td><?php echo $row['sk_multiplier']; ?>x</td>
                        <td><?php echo $unlock_labels[$row['sk_unlock_type']] ?? $row['sk_unlock_type']; ?></td>
                        <td><?php echo $row['sk_use'] ? '<span style="color:#22c55e;">ON</span>' : '<span style="color:#ef4444;">OFF</span>'; ?></td>
                        <td><a href="./battle_skill.php?tab=form&sk_id=<?php echo $row['sk_id']; ?>" class="mg-btn mg-btn-sm mg-btn-secondary">수정</a></td>
                    </tr>
                <?php } ?>
                <?php if ($total_count === 0) { ?>
                    <tr><td colspan="13" style="text-align:center; padding:2rem; color:var(--mg-text-muted);">등록된 스킬이 없습니다.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_count > 0) { ?>
    <div style="margin-top:1rem;">
        <button type="submit" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 스킬을 삭제하시겠습니까?');">선택 삭제</button>
    </div>
    <?php } ?>
</form>

<?php
}

include_once('./_tail.php');
