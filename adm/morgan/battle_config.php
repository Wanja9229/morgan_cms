<?php
/**
 * Morgan Edition - 전투 시스템 설정
 */

$sub_menu = "801900";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth_check_menu($auth, $sub_menu, 'w');

    $config_keys = array(
        'battle_use',
        'battle_energy_max',
        'battle_energy_interval',
        'battle_energy_initial',
        'battle_stat_points',
        'battle_base_hp',
        'battle_hp_regen_pct',
        'battle_base_crit_rate',
        'battle_base_crit_mult',
        'battle_invite_max',
        'battle_expire_no_start',
        'battle_death_penalty',
        'battle_encounter_rate',
        'battle_taunt_turns',
        'battle_guard_turns',
        'battle_guard_reduction',
        'battle_story_regen_pct',
        'battle_story_round_reward_pct',
    );

    foreach ($config_keys as $key) {
        if (isset($_POST[$key])) {
            mg_set_config($key, $_POST[$key]);
        }
    }

    $cookie_data = json_encode(array('msg' => '전투 설정이 저장되었습니다.', 'type' => 'success'));
    setcookie('mg_flash_toast', $cookie_data, time() + 5, '/');
    goto_url('./battle_config.php');
    exit;
}

$g5['title'] = '전투 시스템 설정';
include_once('./_head.php');

// 현재 설정값 로드
$cfg = array(
    'battle_use'                    => mg_config('battle_use', '1'),
    'battle_energy_max'             => mg_config('battle_energy_max', '10'),
    'battle_energy_interval'        => mg_config('battle_energy_interval', '1800'),
    'battle_energy_initial'         => mg_config('battle_energy_initial', '5'),
    'battle_stat_points'            => mg_config('battle_stat_points', '20'),
    'battle_base_hp'                => mg_config('battle_base_hp', '100'),
    'battle_hp_regen_pct'           => mg_config('battle_hp_regen_pct', '5'),
    'battle_base_crit_rate'         => mg_config('battle_base_crit_rate', '5'),
    'battle_base_crit_mult'         => mg_config('battle_base_crit_mult', '150'),
    'battle_invite_max'             => mg_config('battle_invite_max', '2'),
    'battle_expire_no_start'        => mg_config('battle_expire_no_start', '7200'),
    'battle_death_penalty'          => mg_config('battle_death_penalty', '50'),
    'battle_encounter_rate'         => mg_config('battle_encounter_rate', '10'),
    'battle_taunt_turns'            => mg_config('battle_taunt_turns', '5'),
    'battle_guard_turns'            => mg_config('battle_guard_turns', '5'),
    'battle_guard_reduction'        => mg_config('battle_guard_reduction', '30'),
    'battle_story_regen_pct'        => mg_config('battle_story_regen_pct', '5'),
    'battle_story_round_reward_pct' => mg_config('battle_story_round_reward_pct', '15'),
);
?>

<form method="post" action="./battle_config.php">

<!-- 기본 설정 -->
<div class="mg-card">
    <div class="mg-card-header">기본 설정</div>
    <div class="mg-card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">전투 시스템 사용</label>
                <select name="battle_use" class="mg-form-select">
                    <option value="1" <?php echo $cfg['battle_use'] == '1' ? 'selected' : ''; ?>>사용</option>
                    <option value="0" <?php echo $cfg['battle_use'] == '0' ? 'selected' : ''; ?>>미사용</option>
                </select>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">탐사 전투 발생 확률 (%)</label>
                <input type="number" name="battle_encounter_rate" value="<?php echo $cfg['battle_encounter_rate']; ?>" class="mg-form-input" min="0" max="100">
            </div>
        </div>
    </div>
</div>

<!-- 기력 -->
<div class="mg-card" style="margin-top:1.5rem;">
    <div class="mg-card-header">기력 (Energy)</div>
    <div class="mg-card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">최대 기력</label>
                <input type="number" name="battle_energy_max" value="<?php echo $cfg['battle_energy_max']; ?>" class="mg-form-input" min="1">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">충전 간격 (초)</label>
                <input type="number" name="battle_energy_interval" value="<?php echo $cfg['battle_energy_interval']; ?>" class="mg-form-input" min="60">
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">기본 1800초 = 30분</p>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">초기 지급량</label>
                <input type="number" name="battle_energy_initial" value="<?php echo $cfg['battle_energy_initial']; ?>" class="mg-form-input" min="0">
            </div>
        </div>
    </div>
</div>

<!-- 스탯 -->
<div class="mg-card" style="margin-top:1.5rem;">
    <div class="mg-card-header">스탯 & 전투 수치</div>
    <div class="mg-card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">초기 스탯 포인트</label>
                <input type="number" name="battle_stat_points" value="<?php echo $cfg['battle_stat_points']; ?>" class="mg-form-input" min="0">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">기본 HP (스탯 보정 전)</label>
                <input type="number" name="battle_base_hp" value="<?php echo $cfg['battle_base_hp']; ?>" class="mg-form-input" min="1">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">HP 자동회복률 (max_hp의 %)</label>
                <input type="number" name="battle_hp_regen_pct" value="<?php echo $cfg['battle_hp_regen_pct']; ?>" class="mg-form-input" min="0" max="100">
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">기력 충전 1회당 회복량</p>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">기본 크리티컬률 (%)</label>
                <input type="number" name="battle_base_crit_rate" value="<?php echo $cfg['battle_base_crit_rate']; ?>" class="mg-form-input" min="0" max="100">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">기본 크리티컬 배율 (%)</label>
                <input type="number" name="battle_base_crit_mult" value="<?php echo $cfg['battle_base_crit_mult']; ?>" class="mg-form-input" min="100">
            </div>
        </div>
    </div>
</div>

<!-- 전투 규칙 -->
<div class="mg-card" style="margin-top:1.5rem;">
    <div class="mg-card-header">전투 규칙</div>
    <div class="mg-card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">발견자 초대 인원</label>
                <input type="number" name="battle_invite_max" value="<?php echo $cfg['battle_invite_max']; ?>" class="mg-form-input" min="0" max="10">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">미시작 소멸 시간 (초)</label>
                <input type="number" name="battle_expire_no_start" value="<?php echo $cfg['battle_expire_no_start']; ?>" class="mg-form-input" min="300">
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">첫 행동 없이 이 시간 경과 시 자동 소멸</p>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">전사 패널티 (%)</label>
                <input type="number" name="battle_death_penalty" value="<?php echo $cfg['battle_death_penalty']; ?>" class="mg-form-input" min="0" max="100">
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">전사 상태로 전투 종료 시 보상 감소율</p>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">도발 지속 횟수</label>
                <input type="number" name="battle_taunt_turns" value="<?php echo $cfg['battle_taunt_turns']; ?>" class="mg-form-input" min="1">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">수호 지속 횟수</label>
                <input type="number" name="battle_guard_turns" value="<?php echo $cfg['battle_guard_turns']; ?>" class="mg-form-input" min="1">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">수호 데미지 감소율 (%)</label>
                <input type="number" name="battle_guard_reduction" value="<?php echo $cfg['battle_guard_reduction']; ?>" class="mg-form-input" min="0" max="100">
            </div>
        </div>
    </div>
</div>

<!-- 스토리 보스 -->
<div class="mg-card" style="margin-top:1.5rem;">
    <div class="mg-card-header">스토리 보스</div>
    <div class="mg-card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">라운드 간 HP 회복률 (%)</label>
                <input type="number" name="battle_story_regen_pct" value="<?php echo $cfg['battle_story_regen_pct']; ?>" class="mg-form-input" min="0" max="100">
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">잔여 HP의 N% 회복</p>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">라운드 참여 보상 (%)</label>
                <input type="number" name="battle_story_round_reward_pct" value="<?php echo $cfg['battle_story_round_reward_pct']; ?>" class="mg-form-input" min="0" max="100">
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">기본 보상의 N%를 라운드 참여 보상으로 지급</p>
            </div>
        </div>
    </div>
</div>

<div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
    <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
</div>

</form>

<?php
include_once('./_tail.php');
