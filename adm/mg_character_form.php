<?php
/**
 * Morgan Edition - 캐릭터 상세/승인
 */

$sub_menu = '400100';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'r');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;

if (!$ch_id) {
    alert('잘못된 접근입니다.');
}

// 캐릭터 정보 조회
$sql = "SELECT c.*, m.mb_nick, m.mb_email, s.side_name, cl.class_name
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
        LEFT JOIN {$g5['mg_class_table']} cl ON c.class_id = cl.class_id
        WHERE c.ch_id = {$ch_id}";
$char = sql_fetch($sql);

if (!$char['ch_id']) {
    alert('존재하지 않는 캐릭터입니다.');
}

// 프로필 값 조회
$sql = "SELECT pf.*, pv.pv_value
        FROM {$g5['mg_profile_field_table']} pf
        LEFT JOIN {$g5['mg_profile_value_table']} pv ON pf.pf_id = pv.pf_id AND pv.ch_id = {$ch_id}
        WHERE pf.pf_use = 1
        ORDER BY pf.pf_order, pf.pf_id";
$result_fields = sql_query($sql);

// 승인 로그 조회
$sql = "SELECT l.*, m.mb_nick as admin_nick
        FROM {$g5['mg_character_log_table']} l
        LEFT JOIN {$g5['member_table']} m ON l.admin_id = m.mb_id
        WHERE l.ch_id = {$ch_id}
        ORDER BY l.log_datetime DESC
        LIMIT 20";
$result_logs = sql_query($sql);

$g5['title'] = '캐릭터 상세 - '.$char['ch_name'];
include_once './admin.head.php';

$token = get_admin_token();

$state_labels = array(
    'editing' => '<span class="label label-default">수정중</span>',
    'pending' => '<span class="label label-warning">승인대기</span>',
    'approved' => '<span class="label label-success">승인됨</span>',
    'deleted' => '<span class="label label-danger">삭제됨</span>',
);
?>

<div class="local_desc01 local_desc">
    <p>캐릭터 상세 정보 및 승인/반려</p>
</div>

<div class="row" style="display: flex; gap: 20px; flex-wrap: wrap;">
    <!-- 기본 정보 -->
    <div class="col" style="flex: 1; min-width: 300px;">
        <section id="anc_mg_char_info">
            <h2 class="h2_frm">캐릭터 정보</h2>

            <div class="tbl_frm01 tbl_wrap">
                <table>
                    <tbody>
                        <tr>
                            <th scope="row">썸네일</th>
                            <td>
                                <?php if ($char['ch_thumb']) { ?>
                                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                <?php } else { ?>
                                <span class="text-muted">이미지 없음</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">캐릭터명</th>
                            <td>
                                <strong style="font-size: 1.2em;"><?php echo htmlspecialchars($char['ch_name']); ?></strong>
                                <?php if ($char['ch_main']) { ?>
                                <span class="label label-primary">대표 캐릭터</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">상태</th>
                            <td><?php echo $state_labels[$char['ch_state']] ?? ''; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">소유자</th>
                            <td>
                                <a href="./member_form.php?w=u&amp;mb_id=<?php echo $char['mb_id']; ?>">
                                    <?php echo $char['mb_nick']; ?> (<?php echo $char['mb_id']; ?>)
                                </a>
                                <br><small class="text-muted"><?php echo $char['mb_email']; ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">세력</th>
                            <td><?php echo $char['side_name'] ?: '-'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">종족</th>
                            <td><?php echo $char['class_name'] ?: '-'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">등록일</th>
                            <td><?php echo $char['ch_datetime']; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">수정일</th>
                            <td><?php echo $char['ch_update'] ?: '-'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 프로필 정보 -->
        <section id="anc_mg_char_profile" style="margin-top: 20px;">
            <h2 class="h2_frm">프로필 정보</h2>

            <div class="tbl_frm01 tbl_wrap">
                <table>
                    <tbody>
                        <?php
                        while ($field = sql_fetch_array($result_fields)) {
                            $value = $field['pv_value'];
                            if (empty($value)) continue;

                            // 타입별 표시
                            if ($field['pf_type'] == 'url') {
                                $display = '<a href="'.htmlspecialchars($value).'" target="_blank">'.htmlspecialchars($value).'</a>';
                            } else if ($field['pf_type'] == 'textarea') {
                                $display = nl2br(htmlspecialchars($value));
                            } else {
                                $display = htmlspecialchars($value);
                            }
                        ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($field['pf_name']); ?></th>
                            <td><?php echo $display; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- 승인/반려 및 로그 -->
    <div class="col" style="flex: 1; min-width: 300px;">
        <!-- 승인/반려 폼 -->
        <?php if ($char['ch_state'] == 'pending') { ?>
        <section id="anc_mg_char_action">
            <h2 class="h2_frm">승인/반려</h2>

            <form name="fcharaction" method="post" action="./mg_character_update.php">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="ch_id" value="<?php echo $ch_id; ?>">

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th scope="row">메모</th>
                                <td>
                                    <textarea name="log_memo" id="log_memo" rows="4" class="form-control" placeholder="반려 시 사유를 입력하세요 (선택사항)"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="btn_confirm01 btn_confirm" style="margin-top: 10px;">
                    <button type="submit" name="action" value="approve" class="btn btn-success" onclick="return confirm('이 캐릭터를 승인하시겠습니까?');">승인</button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('이 캐릭터를 반려하시겠습니까?');">반려</button>
                </div>
            </form>
        </section>
        <?php } else if ($char['ch_state'] == 'approved') { ?>
        <section id="anc_mg_char_action">
            <h2 class="h2_frm">관리</h2>

            <form name="fcharaction" method="post" action="./mg_character_update.php">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="ch_id" value="<?php echo $ch_id; ?>">

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th scope="row">메모</th>
                                <td>
                                    <textarea name="log_memo" id="log_memo" rows="4" class="form-control" placeholder="관리 메모 (선택사항)"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="btn_confirm01 btn_confirm" style="margin-top: 10px;">
                    <button type="submit" name="action" value="unapprove" class="btn btn-warning" onclick="return confirm('이 캐릭터의 승인을 취소하시겠습니까?');">승인 취소</button>
                    <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('이 캐릭터를 삭제하시겠습니까?');">삭제</button>
                </div>
            </form>
        </section>
        <?php } ?>

        <!-- 처리 로그 -->
        <section id="anc_mg_char_log" style="margin-top: 20px;">
            <h2 class="h2_frm">처리 이력</h2>

            <div class="tbl_head01 tbl_wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">일시</th>
                            <th scope="col">액션</th>
                            <th scope="col">처리자</th>
                            <th scope="col">메모</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $action_labels = array(
                            'submit' => '승인 신청',
                            'approve' => '승인',
                            'reject' => '반려',
                            'edit' => '수정',
                        );
                        while ($log = sql_fetch_array($result_logs)) {
                        ?>
                        <tr>
                            <td class="td_datetime"><?php echo $log['log_datetime']; ?></td>
                            <td><?php echo $action_labels[$log['log_action']] ?? $log['log_action']; ?></td>
                            <td><?php echo $log['admin_nick'] ?: ($log['admin_id'] ?: '회원'); ?></td>
                            <td><?php echo htmlspecialchars($log['log_memo'] ?: '-'); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div class="btn_confirm01 btn_confirm" style="margin-top: 20px;">
    <a href="./mg_character_list.php" class="btn btn-default">목록으로</a>
    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $ch_id; ?>" target="_blank" class="btn btn-info">프론트에서 보기</a>
</div>

<?php
include_once './admin.tail.php';
