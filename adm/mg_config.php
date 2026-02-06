<?php
/**
 * Morgan Edition - 설정
 */

$sub_menu = '400400';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'r');

// 폼 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth_check_menu($auth, $sub_menu, 'w');
    check_admin_token();

    // 설정값 저장
    $configs = array(
        'character_approval' => isset($_POST['character_approval']) ? '1' : '0',
        'character_max' => max(1, (int)$_POST['character_max']),
        'attendance_point' => max(0, (int)$_POST['attendance_point']),
        'attendance_bonus' => max(0, (int)$_POST['attendance_bonus']),
        'theme_primary_color' => preg_replace('/[^#a-fA-F0-9]/', '', $_POST['theme_primary_color']),
    );

    foreach ($configs as $key => $value) {
        mg_config_set($key, $value);
    }

    alert('설정이 저장되었습니다.', './mg_config.php');
}

$g5['title'] = 'Morgan 설정';
include_once './admin.head.php';

$token = get_admin_token();

// 현재 설정값
$cfg = array(
    'character_approval' => mg_config('character_approval', '1'),
    'character_max' => mg_config('character_max', '10'),
    'attendance_point' => mg_config('attendance_point', '100'),
    'attendance_bonus' => mg_config('attendance_bonus', '500'),
    'theme_primary_color' => mg_config('theme_primary_color', '#f59f0a'),
);
?>

<div class="local_desc01 local_desc">
    <p>Morgan Edition의 기본 설정을 관리합니다.</p>
</div>

<form name="fmgconfig" method="post">
    <input type="hidden" name="token" value="<?php echo $token; ?>">

    <!-- 캐릭터 설정 -->
    <section id="anc_mg_char_config">
        <h2 class="h2_frm">캐릭터 설정</h2>

        <div class="tbl_frm01 tbl_wrap">
            <table>
                <tbody>
                    <tr>
                        <th scope="row">캐릭터 승인제</th>
                        <td>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="character_approval" value="1" <?php echo $cfg['character_approval'] ? 'checked' : ''; ?>>
                                캐릭터 등록 시 관리자 승인 필요
                            </label>
                            <span class="help-block">체크 해제 시 캐릭터가 즉시 승인됩니다.</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="character_max">회원당 최대 캐릭터 수</label></th>
                        <td>
                            <input type="number" name="character_max" id="character_max" value="<?php echo $cfg['character_max']; ?>" class="form-control" style="width: 100px;" min="1" max="100">
                            <span class="help-block">한 회원이 등록할 수 있는 최대 캐릭터 수</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 출석 설정 -->
    <section id="anc_mg_attendance_config" style="margin-top: 20px;">
        <h2 class="h2_frm">출석 설정</h2>

        <div class="tbl_frm01 tbl_wrap">
            <table>
                <tbody>
                    <tr>
                        <th scope="row"><label for="attendance_point">출석 기본 포인트</label></th>
                        <td>
                            <input type="number" name="attendance_point" id="attendance_point" value="<?php echo $cfg['attendance_point']; ?>" class="form-control" style="width: 100px;" min="0">
                            <span class="help-block">출석 체크 시 지급되는 기본 포인트</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="attendance_bonus">연속 출석 보너스</label></th>
                        <td>
                            <input type="number" name="attendance_bonus" id="attendance_bonus" value="<?php echo $cfg['attendance_bonus']; ?>" class="form-control" style="width: 100px;" min="0">
                            <span class="help-block">7일 연속 출석 시 추가 지급되는 보너스 포인트</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 테마 설정 -->
    <section id="anc_mg_theme_config" style="margin-top: 20px;">
        <h2 class="h2_frm">테마 설정</h2>

        <div class="tbl_frm01 tbl_wrap">
            <table>
                <tbody>
                    <tr>
                        <th scope="row"><label for="theme_primary_color">메인 컬러</label></th>
                        <td>
                            <input type="color" name="theme_primary_color" id="theme_primary_color" value="<?php echo $cfg['theme_primary_color']; ?>" style="width: 60px; height: 30px; padding: 0; border: 1px solid #ccc;">
                            <input type="text" value="<?php echo $cfg['theme_primary_color']; ?>" class="form-control" style="width: 100px; display: inline-block;" readonly>
                            <span class="help-block">테마의 강조 색상 (현재 미적용, 추후 지원 예정)</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <div class="btn_confirm01 btn_confirm" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">설정 저장</button>
    </div>
</form>

<script>
document.getElementById('theme_primary_color').addEventListener('input', function(e) {
    this.nextElementSibling.value = e.target.value;
});
</script>

<?php
include_once './admin.tail.php';
