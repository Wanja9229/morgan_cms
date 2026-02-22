<?php
/**
 * Morgan Edition - 게시판 추가/수정
 */

$sub_menu = "800180";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 그룹 체크
$sql = "SELECT COUNT(*) as cnt FROM {$g5['group_table']}";
$row = sql_fetch($sql);
if (!$row['cnt']) {
    alert('게시판그룹이 한개 이상 생성되어야 합니다.', './boardgroup_list.php');
}

$w = isset($_GET['w']) ? $_GET['w'] : '';
$bo_table = isset($_GET['bo_table']) ? clean_xss_tags($_GET['bo_table']) : '';

// 그룹: community 고정 (그룹 기능 미사용)
$default_gr_id = 'community';

// 스킨 목록
$board_skins = array();
$skin_path = G5_SKIN_PATH.'/board';
if (is_dir($skin_path)) {
    $skin_dirs = scandir($skin_path);
    foreach ($skin_dirs as $dir) {
        if ($dir != '.' && $dir != '..' && is_dir($skin_path.'/'.$dir)) {
            $board_skins[] = $dir;
        }
    }
}

// 테마 스킨 목록
$theme_skins = array();
$theme_skin_path = G5_THEME_PATH.'/skin/board';
if (is_dir($theme_skin_path)) {
    $skin_dirs = scandir($theme_skin_path);
    foreach ($skin_dirs as $dir) {
        if ($dir != '.' && $dir != '..' && is_dir($theme_skin_path.'/'.$dir)) {
            $theme_skins[] = $dir;
        }
    }
}

// 기본값
$board = array(
    'bo_table' => '',
    'gr_id' => isset($_GET['gr_id']) ? $_GET['gr_id'] : '',
    'bo_subject' => '',
    'bo_device' => 'both',
    'bo_skin' => 'basic',
    'bo_mobile_skin' => 'basic',
    'bo_admin' => '',
    'bo_list_level' => 1,
    'bo_read_level' => 1,
    'bo_write_level' => 1,
    'bo_reply_level' => 1,
    'bo_comment_level' => 1,
    'bo_link_level' => 1,
    'bo_upload_level' => 1,
    'bo_download_level' => 1,
    'bo_html_level' => 1,
    'bo_read_point' => $config['cf_read_point'],
    'bo_write_point' => $config['cf_write_point'],
    'bo_comment_point' => $config['cf_comment_point'],
    'bo_download_point' => $config['cf_download_point'],
    'bo_use_category' => 0,
    'bo_category_list' => '',
    'bo_use_sideview' => 0,
    'bo_use_good' => 0,
    'bo_use_nogood' => 0,
    'bo_use_secret' => 0,
    'bo_use_search' => 1,
    'bo_use_comment' => 1,
    'bo_page_rows' => $config['cf_page_rows'],
    'bo_mobile_page_rows' => $config['cf_page_rows'],
    'bo_subject_len' => 60,
    'bo_mobile_subject_len' => 30,
    'bo_new' => 24,
    'bo_hot' => 100,
    'bo_image_width' => 600,
    'bo_upload_count' => 2,
    'bo_upload_size' => 1048576,
    'bo_reply_order' => 1,
    'bo_order' => 0,
    'bo_count_modify' => 1,
    'bo_count_delete' => 1,
    'bo_use_list_view' => 0,
    'bo_use_list_file' => 0,
    'bo_use_list_content' => 0,
    'bo_table_width' => 100,
    'bo_gallery_cols' => 4,
    'bo_gallery_width' => 200,
    'bo_gallery_height' => 150,
    'bo_include_head' => '_head.php',
    'bo_include_tail' => '_tail.php',
);

$is_edit = false;
$page_title = '게시판 추가';

if ($w == 'u' && $bo_table) {
    $is_edit = true;
    $page_title = '게시판 수정';

    $loaded = sql_fetch("SELECT * FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
    if (!$loaded['bo_table']) {
        alert('존재하지 않는 게시판입니다.', './board_list.php');
    }
    $board = array_merge($board, $loaded);
}

$g5['title'] = $page_title;
require_once __DIR__.'/_head.php';
?>

<form name="fboardform" id="fboardform" method="post" action="./board_form_update.php">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="old_bo_table" value="<?php echo $board['bo_table']; ?>">

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
        <!-- 좌측: 기본 설정 -->
        <div>
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">기본 설정</div>
                <div class="mg-card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">게시판 테이블명 <span style="color:var(--mg-error);">*</span></label>
                            <input type="text" name="bo_table" value="<?php echo htmlspecialchars($board['bo_table']); ?>" class="mg-form-input" required pattern="[a-z0-9_]+" <?php echo $is_edit ? 'readonly' : ''; ?> placeholder="영문소문자, 숫자, 언더스코어">
                            <?php if (!$is_edit) { ?>
                            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">20자 이내, 영문소문자+숫자+_ 만 사용</div>
                            <?php } ?>
                        </div>
                        <input type="hidden" name="gr_id" value="<?php echo $default_gr_id; ?>">
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label">게시판 제목 <span style="color:var(--mg-error);">*</span></label>
                        <input type="text" name="bo_subject" value="<?php echo htmlspecialchars($board['bo_subject']); ?>" class="mg-form-input" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">PC 스킨</label>
                            <select name="bo_skin" class="mg-form-select">
                                <optgroup label="테마 스킨">
                                    <?php foreach ($theme_skins as $skin) { ?>
                                    <option value="<?php echo $skin; ?>" <?php echo $board['bo_skin'] == $skin ? 'selected' : ''; ?>><?php echo $skin; ?></option>
                                    <?php } ?>
                                </optgroup>
                                <optgroup label="기본 스킨">
                                    <?php foreach ($board_skins as $skin) { ?>
                                    <option value="<?php echo $skin; ?>" <?php echo $board['bo_skin'] == $skin ? 'selected' : ''; ?>><?php echo $skin; ?></option>
                                    <?php } ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">모바일 스킨</label>
                            <select name="bo_mobile_skin" class="mg-form-select">
                                <optgroup label="테마 스킨">
                                    <?php foreach ($theme_skins as $skin) { ?>
                                    <option value="<?php echo $skin; ?>" <?php echo $board['bo_mobile_skin'] == $skin ? 'selected' : ''; ?>><?php echo $skin; ?></option>
                                    <?php } ?>
                                </optgroup>
                                <optgroup label="기본 스킨">
                                    <?php foreach ($board_skins as $skin) { ?>
                                    <option value="<?php echo $skin; ?>" <?php echo $board['bo_mobile_skin'] == $skin ? 'selected' : ''; ?>><?php echo $skin; ?></option>
                                    <?php } ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">기기</label>
                            <select name="bo_device" class="mg-form-select">
                                <option value="both" <?php echo $board['bo_device'] == 'both' ? 'selected' : ''; ?>>PC + 모바일</option>
                                <option value="pc" <?php echo $board['bo_device'] == 'pc' ? 'selected' : ''; ?>>PC 전용</option>
                                <option value="mobile" <?php echo $board['bo_device'] == 'mobile' ? 'selected' : ''; ?>>모바일 전용</option>
                            </select>
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">정렬 순서</label>
                            <input type="number" name="bo_order" value="<?php echo $board['bo_order']; ?>" class="mg-form-input">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">게시판 관리자</label>
                            <input type="text" name="bo_admin" value="<?php echo htmlspecialchars($board['bo_admin']); ?>" class="mg-form-input" placeholder="회원 ID">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 권한 설정 -->
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">권한 설정 (레벨)</div>
                <div class="mg-card-body">
                    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">목록</label>
                            <input type="number" name="bo_list_level" value="<?php echo $board['bo_list_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">읽기</label>
                            <input type="number" name="bo_read_level" value="<?php echo $board['bo_read_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">글쓰기</label>
                            <input type="number" name="bo_write_level" value="<?php echo $board['bo_write_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">답글</label>
                            <input type="number" name="bo_reply_level" value="<?php echo $board['bo_reply_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">댓글</label>
                            <input type="number" name="bo_comment_level" value="<?php echo $board['bo_comment_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">링크</label>
                            <input type="number" name="bo_link_level" value="<?php echo $board['bo_link_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">업로드</label>
                            <input type="number" name="bo_upload_level" value="<?php echo $board['bo_upload_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">다운로드</label>
                            <input type="number" name="bo_download_level" value="<?php echo $board['bo_download_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">HTML 쓰기</label>
                            <input type="number" name="bo_html_level" value="<?php echo $board['bo_html_level']; ?>" class="mg-form-input" min="1" max="10">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 포인트 설정 -->
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">포인트 설정</div>
                <div class="mg-card-body">
                    <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">읽기</label>
                            <input type="number" name="bo_read_point" value="<?php echo $board['bo_read_point']; ?>" class="mg-form-input">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">글쓰기</label>
                            <input type="number" name="bo_write_point" value="<?php echo $board['bo_write_point']; ?>" class="mg-form-input">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">댓글</label>
                            <input type="number" name="bo_comment_point" value="<?php echo $board['bo_comment_point']; ?>" class="mg-form-input">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">다운로드</label>
                            <input type="number" name="bo_download_point" value="<?php echo $board['bo_download_point']; ?>" class="mg-form-input">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 우측: 기능/표시 설정 -->
        <div>
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">기능 설정</div>
                <div class="mg-card-body">
                    <div class="mg-form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="bo_use_category" value="1" <?php echo $board['bo_use_category'] ? 'checked' : ''; ?>>
                            <span>카테고리 사용</span>
                        </label>
                    </div>
                    <div class="mg-form-group" id="category_list_wrap" style="<?php echo $board['bo_use_category'] ? '' : 'display:none;'; ?>margin-left:1.5rem;">
                        <label class="mg-form-label">카테고리 목록</label>
                        <input type="text" name="bo_category_list" value="<?php echo htmlspecialchars($board['bo_category_list']); ?>" class="mg-form-input" placeholder="|로 구분 (예: 공지|일반|질문)">
                    </div>

                    <div class="mg-form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="bo_use_good" value="1" <?php echo $board['bo_use_good'] ? 'checked' : ''; ?>>
                            <span>좋아요 사용</span>
                        </label>
                    </div>
                    <div class="mg-form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="bo_use_nogood" value="1" <?php echo $board['bo_use_nogood'] ? 'checked' : ''; ?>>
                            <span>싫어요 사용</span>
                        </label>
                    </div>
                    <div class="mg-form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="bo_use_secret" value="1" <?php echo $board['bo_use_secret'] ? 'checked' : ''; ?>>
                            <span>비밀글 사용</span>
                        </label>
                    </div>
                    <div class="mg-form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="bo_use_search" value="1" <?php echo $board['bo_use_search'] ? 'checked' : ''; ?>>
                            <span>전체검색 사용</span>
                        </label>
                    </div>
                    <div class="mg-form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="bo_use_sideview" value="1" <?php echo $board['bo_use_sideview'] ? 'checked' : ''; ?>>
                            <span>사이드뷰 사용</span>
                        </label>
                    </div>
                    <div class="mg-form-group" style="border-top:1px solid rgba(255,255,255,0.06);padding-top:0.75rem;margin-top:0.25rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="bo_anonymous" value="1" <?php echo ($board['bo_1'] ?? '') === 'anonymous' ? 'checked' : ''; ?>>
                            <span>익명 게시판</span>
                        </label>
                        <p style="font-size:0.75rem;color:var(--mg-text-muted);margin:0.25rem 0 0 1.5rem;">작성자명이 "익명"으로 표시됩니다. 본인과 관리자만 실제 작성자를 확인할 수 있습니다.</p>
                    </div>
                </div>
            </div>

            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">표시 설정</div>
                <div class="mg-card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">페이지당 목록</label>
                            <input type="number" name="bo_page_rows" value="<?php echo $board['bo_page_rows']; ?>" class="mg-form-input" min="1">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">모바일 목록</label>
                            <input type="number" name="bo_mobile_page_rows" value="<?php echo $board['bo_mobile_page_rows']; ?>" class="mg-form-input" min="1">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">제목 길이</label>
                            <input type="number" name="bo_subject_len" value="<?php echo $board['bo_subject_len']; ?>" class="mg-form-input" min="1">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">모바일 제목</label>
                            <input type="number" name="bo_mobile_subject_len" value="<?php echo $board['bo_mobile_subject_len']; ?>" class="mg-form-input" min="1">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">새글 (시간)</label>
                            <input type="number" name="bo_new" value="<?php echo $board['bo_new']; ?>" class="mg-form-input" min="0">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">인기글 (조회)</label>
                            <input type="number" name="bo_hot" value="<?php echo $board['bo_hot']; ?>" class="mg-form-input" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">업로드 설정</div>
                <div class="mg-card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">업로드 개수</label>
                            <input type="number" name="bo_upload_count" value="<?php echo $board['bo_upload_count']; ?>" class="mg-form-input" min="0">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">파일 크기 (byte)</label>
                            <input type="number" name="bo_upload_size" value="<?php echo $board['bo_upload_size']; ?>" class="mg-form-input" min="0">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">이미지 너비</label>
                            <input type="number" name="bo_image_width" value="<?php echo $board['bo_image_width']; ?>" class="mg-form-input" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($is_edit) { ?>
            <div class="mg-alert mg-alert-info">
                <a href="<?php echo G5_ADMIN_URL; ?>/board_form.php?w=u&bo_table=<?php echo $board['bo_table']; ?>" target="_blank" style="color:var(--mg-accent);">
                    고급 설정 (그누보드 관리자)
                </a>
            </div>
            <?php } ?>
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
        <a href="./board_list.php" class="mg-btn mg-btn-secondary">목록</a>
        <button type="submit" class="mg-btn mg-btn-primary"><?php echo $is_edit ? '수정' : '추가'; ?></button>
        <?php
        // 시스템 게시판은 삭제 불가
        $system_boards = array('vent', 'commission', 'mission', 'lordby', 'lb_terminal', 'lb_intranet', 'lb_corkboard');
        if ($is_edit && !in_array($board['bo_table'], $system_boards)) { ?>
        <button type="button" class="mg-btn mg-btn-danger" style="margin-left:auto;" onclick="deleteBoard()">삭제</button>
        <?php } elseif ($is_edit && in_array($board['bo_table'], $system_boards)) { ?>
        <span style="margin-left:auto; font-size:0.8rem; color:var(--mg-text-muted);">시스템 게시판은 삭제할 수 없습니다</span>
        <?php } ?>
    </div>
</form>

<script>
document.querySelector('input[name="bo_use_category"]').addEventListener('change', function() {
    document.getElementById('category_list_wrap').style.display = this.checked ? '' : 'none';
});

function deleteBoard() {
    if (!confirm('이 게시판을 삭제하시겠습니까?\n\n게시판의 모든 글과 댓글이 함께 삭제됩니다.')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = './board_form_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="bo_table" value="<?php echo $board['bo_table']; ?>">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
