<?php
/**
 * Morgan Edition - Prompt Mission Board Write Skin
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 변수 기본값 설정
$w = isset($w) ? $w : '';
$wr_id = isset($wr_id) ? $wr_id : 0;
$sca = isset($sca) ? $sca : '';
$sfl = isset($sfl) ? $sfl : '';
$stx = isset($stx) ? $stx : '';
$spt = isset($spt) ? $spt : '';
$page = isset($page) ? $page : '';
$name = isset($name) ? $name : '';
$email = isset($email) ? $email : '';
$subject = isset($subject) ? $subject : '';
$notice_checked = isset($notice_checked) ? $notice_checked : '';
$html_checked = isset($html_checked) ? $html_checked : '';
$secret_checked = isset($secret_checked) ? $secret_checked : '';
$html_editor_head_script = isset($html_editor_head_script) ? $html_editor_head_script : '';
$html_editor = isset($html_editor) ? $html_editor : '';
$html_editor_tail_script = isset($html_editor_tail_script) ? $html_editor_tail_script : '';
$editor_js = isset($editor_js) ? $editor_js : '';
$category_option = isset($category_option) ? $category_option : '';
$is_category = isset($is_category) ? $is_category : false;
$is_name = isset($is_name) ? $is_name : false;
$is_password = isset($is_password) ? $is_password : false;
$is_email = isset($is_email) ? $is_email : false;
$is_link = isset($is_link) ? $is_link : false;
$is_file = isset($is_file) ? $is_file : false;
$is_notice = isset($is_notice) ? $is_notice : false;
$is_html = isset($is_html) ? $is_html : false;
$is_secret = isset($is_secret) ? $is_secret : false;
$is_mail = isset($is_mail) ? $is_mail : false;
$link_count = isset($link_count) ? $link_count : 0;
$file_count = isset($file_count) ? $file_count : 0;
$file = isset($file) ? $file : array();
$action_url = isset($action_url) ? $action_url : '';
$list_href = isset($list_href) ? $list_href : '';

$is_edit = $w === 'u';
$form_title = $is_edit ? '글 수정' : '글쓰기';

// 활성 프롬프트 목록
$active_prompts = mg_get_active_prompts($bo_table);

// URL에서 선택된 프롬프트
$selected_pm_id = isset($_GET['pm_id']) ? (int)$_GET['pm_id'] : 0;

// 수정 시 기존 엔트리의 프롬프트
$edit_pm_id = 0;
if ($is_edit && $wr_id) {
    $edit_entry = mg_get_entry_by_write($bo_table, $wr_id);
    if ($edit_entry && $edit_entry['pm_id']) {
        $edit_pm_id = (int)$edit_entry['pm_id'];
    }
}

// 프롬프트 데이터를 JS로 전달할 배열 구성
$prompt_js_data = array();
foreach ($active_prompts as $ap) {
    $my_entries = array();
    if ($is_member) {
        $my_entries = mg_get_my_entries($ap['pm_id'], $member['mb_id']);
    }
    $prompt_js_data[$ap['pm_id']] = array(
        'title'     => $ap['pm_title'],
        'content'   => strip_tags($ap['pm_content']),
        'min_chars' => (int)$ap['pm_min_chars'],
        'point'     => (int)$ap['pm_point'],
        'bonus'     => (int)$ap['pm_bonus_point'],
        'max_entry' => (int)$ap['pm_max_entry'],
        'my_count'  => count($my_entries),
        'start'     => $ap['pm_start_date'] ? date('m/d', strtotime($ap['pm_start_date'])) : '',
        'end'       => $ap['pm_end_date'] ? date('m/d', strtotime($ap['pm_end_date'])) : '',
    );
}

// 보상 유형 (request 모드)
$_mg_br_mode = '';
$_mg_reward_types = array();
if ($is_member && !$is_edit && function_exists('mg_get_board_reward')) {
    $_mg_br = mg_get_board_reward($bo_table);
    if ($_mg_br && $_mg_br['br_mode'] === 'request') {
        $_mg_br_mode = 'request';
        $_mg_reward_types = mg_get_reward_types($bo_table);
    }
}

// 캐릭터 선택기 준비 (로그인 회원만)
$mg_characters = array();
$mg_selected_ch_id = 0;

if ($is_member) {
    $mg_characters = mg_get_usable_characters($member['mb_id']);

    // 수정 시 기존 선택된 캐릭터
    if ($is_edit && $wr_id) {
        $mg_write_char = mg_get_write_character($bo_table, $wr_id);
        if ($mg_write_char) {
            $mg_selected_ch_id = $mg_write_char['ch_id'];
        }
    } else {
        // 신규 작성 시 대표 캐릭터 기본 선택
        foreach ($mg_characters as $ch) {
            if ($ch['ch_main']) {
                $mg_selected_ch_id = $ch['ch_id'];
                break;
            }
        }
    }
}
?>

<div id="bo_write" class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6"><?php echo $form_title; ?></h2>

        <form name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" method="post" enctype="multipart/form-data" onsubmit="return fwrite_submit(this);" autocomplete="off">
            <input type="hidden" name="w" value="<?php echo $w; ?>">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
            <input type="hidden" name="sca" value="<?php echo $sca; ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
            <input type="hidden" name="stx" value="<?php echo $stx; ?>">
            <input type="hidden" name="spt" value="<?php echo $spt; ?>">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <input type="hidden" name="token" value="">
            <?php echo $html_editor_head_script; ?>

            <!-- 프롬프트 선택 -->
            <?php if (count($active_prompts) > 0 || $edit_pm_id) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">프롬프트 선택</label>
                <select name="pm_id" id="pm_id_select" class="input" <?php echo $is_edit && $edit_pm_id ? 'disabled' : ''; ?>>
                    <option value="0">자유 글쓰기 (미션 없음)</option>
                    <?php foreach ($active_prompts as $ap) {
                        $ap_date = '';
                        if ($ap['pm_start_date'] && $ap['pm_end_date']) {
                            $ap_date = ' (' . date('m/d', strtotime($ap['pm_start_date'])) . ' ~ ' . date('m/d', strtotime($ap['pm_end_date'])) . ')';
                        }
                        $is_selected = ($selected_pm_id == $ap['pm_id'] || $edit_pm_id == $ap['pm_id']);
                    ?>
                    <option value="<?php echo $ap['pm_id']; ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ap['pm_title']) . $ap_date; ?>
                    </option>
                    <?php } ?>
                </select>
                <?php if ($is_edit && $edit_pm_id) { ?>
                <input type="hidden" name="pm_id" value="<?php echo $edit_pm_id; ?>">
                <p class="text-xs text-mg-text-muted mt-1">수정 시 프롬프트는 변경할 수 없습니다.</p>
                <?php } ?>
            </div>

            <!-- 프롬프트 정보 박스 -->
            <div id="prompt_info_box" class="mb-4 p-4 bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary" style="display:none;">
                <div class="text-sm text-mg-text-secondary mb-2" id="prompt_desc"></div>
                <div class="flex flex-wrap gap-4 text-xs text-mg-text-muted">
                    <span id="prompt_period"></span>
                    <span id="prompt_chars">최소 <strong>0</strong>자</span>
                    <span id="prompt_reward">보상: <strong>0</strong>P</span>
                    <span id="prompt_limit">1인 <strong>1</strong>회</span>
                    <span id="prompt_my_count"></span>
                </div>
            </div>
            <?php } ?>

            <!-- 카테고리 -->
            <?php if ($is_category) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">카테고리</label>
                <select name="ca_name" class="input">
                    <?php echo $category_option; ?>
                </select>
            </div>
            <?php } ?>

            <!-- 이름 (비회원) -->
            <?php if ($is_name) { ?>
            <div class="mb-4">
                <label for="wr_name" class="block text-sm font-medium text-mg-text-secondary mb-2">이름 <span class="text-mg-error">*</span></label>
                <input type="text" name="wr_name" id="wr_name" value="<?php echo $name; ?>" class="input" required>
            </div>
            <?php } ?>

            <!-- 비밀번호 (비회원) -->
            <?php if ($is_password) { ?>
            <div class="mb-4">
                <label for="wr_password" class="block text-sm font-medium text-mg-text-secondary mb-2">비밀번호 <span class="text-mg-error">*</span></label>
                <input type="password" name="wr_password" id="wr_password" class="input" <?php echo $is_edit ? '' : 'required'; ?>>
            </div>
            <?php } ?>

            <!-- 이메일 -->
            <?php if ($is_email) { ?>
            <div class="mb-4">
                <label for="wr_email" class="block text-sm font-medium text-mg-text-secondary mb-2">이메일</label>
                <input type="email" name="wr_email" id="wr_email" value="<?php echo $email; ?>" class="input">
            </div>
            <?php } ?>

            <!-- 캐릭터 선택 (회원 전용) -->
            <?php if ($is_member && count($mg_characters) > 0) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">캐릭터 선택</label>
                <div class="flex flex-wrap gap-2" id="mg-character-selector">
                    <!-- 캐릭터 없음 옵션 -->
                    <label class="character-option cursor-pointer">
                        <input type="radio" name="mg_ch_id" value="0" <?php echo $mg_selected_ch_id == 0 ? 'checked' : ''; ?> class="hidden">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-mg-bg-tertiary bg-mg-bg-primary hover:border-mg-accent transition-colors character-badge">
                            <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
                                <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-sm text-mg-text-secondary">선택 안함</span>
                        </div>
                    </label>
                    <?php foreach ($mg_characters as $ch) { ?>
                    <label class="character-option cursor-pointer">
                        <input type="radio" name="mg_ch_id" value="<?php echo $ch['ch_id']; ?>" <?php echo $mg_selected_ch_id == $ch['ch_id'] ? 'checked' : ''; ?> class="hidden">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-mg-bg-tertiary bg-mg-bg-primary hover:border-mg-accent transition-colors character-badge">
                            <?php if ($ch['ch_thumb']) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$ch['ch_thumb']; ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                            <?php } else { ?>
                            <div class="w-8 h-8 rounded-full bg-mg-accent/20 flex items-center justify-center">
                                <span class="text-xs font-bold text-mg-accent"><?php echo mb_substr($ch['ch_name'], 0, 1); ?></span>
                            </div>
                            <?php } ?>
                            <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($ch['ch_name']); ?></span>
                            <?php if ($ch['ch_main']) { ?>
                            <span class="text-xs bg-mg-accent text-white px-1.5 py-0.5 rounded">대표</span>
                            <?php } ?>
                        </div>
                    </label>
                    <?php } ?>
                </div>
                <p class="text-xs text-mg-text-muted mt-1">이 게시물을 작성할 캐릭터를 선택하세요.</p>
            </div>
            <?php } elseif ($is_member) { ?>
            <input type="hidden" name="mg_ch_id" value="0">
            <?php } ?>

            <!-- 보상 유형 (request 모드) -->
            <?php if ($_mg_br_mode === 'request' && !empty($_mg_reward_types)) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">보상 요청</label>
                <select name="reward_type" class="input">
                    <option value="">보상 요청 안 함</option>
                    <?php foreach ($_mg_reward_types as $rwt) { ?>
                    <option value="<?php echo $rwt['rwt_id']; ?>">
                        <?php echo htmlspecialchars($rwt['rwt_name']); ?> - <?php echo number_format($rwt['rwt_point']); ?>P
                        <?php if ($rwt['rwt_desc']) echo '(' . htmlspecialchars($rwt['rwt_desc']) . ')'; ?>
                    </option>
                    <?php } ?>
                </select>
                <p class="text-xs text-mg-text-muted mt-1">보상을 요청하면 관리자 검토 후 지급됩니다.</p>
            </div>
            <?php } ?>

            <!-- 제목 -->
            <div class="mb-4">
                <label for="wr_subject" class="block text-sm font-medium text-mg-text-secondary mb-2">제목 <span class="text-mg-error">*</span></label>
                <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" class="input" required>
            </div>

            <!-- 내용 -->
            <div class="mb-4">
                <label for="wr_content" class="block text-sm font-medium text-mg-text-secondary mb-2">내용 <span class="text-mg-error">*</span></label>
                <?php echo $html_editor; ?>
                <div id="prompt_char_counter" class="text-xs text-mg-text-muted mt-1" style="display:none;">
                    글자 수: <span id="prompt_char_count" class="font-medium">0</span>자
                    <span id="prompt_char_min" class="ml-2"></span>
                </div>
            </div>

            <!-- 링크 -->
            <?php if ($is_link) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">링크</label>
                <?php for ($i = 1; $i <= $link_count; $i++) {
                    $link_val = isset(${'link'.$i}) ? ${'link'.$i} : '';
                ?>
                <input type="text" name="wr_link<?php echo $i; ?>" value="<?php echo $link_val; ?>" class="input mb-2" placeholder="https://">
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 파일첨부 -->
            <?php if ($is_file) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">파일첨부</label>
                <?php for ($i = 0; $i < $file_count; $i++) { ?>
                <div class="mb-2">
                    <?php if ($is_edit && isset($file[$i]['source'])) { ?>
                    <div class="flex items-center gap-2 mb-1 text-sm text-mg-text-muted">
                        <span><?php echo $file[$i]['source']; ?></span>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="checkbox" name="bf_file_del<?php echo $i; ?>" value="1" class="w-4 h-4">
                            <span class="text-mg-error">삭제</span>
                        </label>
                    </div>
                    <?php } ?>
                    <input type="file" name="bf_file[]" class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-mg-bg-tertiary file:text-mg-text-primary hover:file:bg-mg-accent/20">
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 옵션 -->
            <?php if ($is_notice || $is_html || $is_secret || $is_mail) { ?>
            <div class="mb-6 p-4 bg-mg-bg-primary rounded-lg">
                <div class="flex flex-wrap gap-4">
                    <?php if ($is_notice) { ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="notice" value="1" <?php echo $notice_checked; ?> class="w-4 h-4 rounded">
                        <span class="text-sm text-mg-text-secondary">공지</span>
                    </label>
                    <?php } ?>
                    <?php if ($is_html) { ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="html" value="html1" <?php echo $html_checked; ?> class="w-4 h-4 rounded">
                        <span class="text-sm text-mg-text-secondary">HTML 사용</span>
                    </label>
                    <?php } ?>
                    <?php if ($is_secret) { ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="secret" value="secret" <?php echo $secret_checked; ?> class="w-4 h-4 rounded">
                        <span class="text-sm text-mg-text-secondary">비밀글</span>
                    </label>
                    <?php } ?>
                    <?php if ($is_mail) { ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="mail" value="mail" class="w-4 h-4 rounded">
                        <span class="text-sm text-mg-text-secondary">답변 메일 알림</span>
                    </label>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- 버튼 -->
            <div class="flex items-center justify-between">
                <a href="<?php echo $list_href; ?>" class="btn btn-secondary">취소</a>
                <button type="submit" id="btn_submit" class="btn btn-primary">
                    <?php echo $is_edit ? '수정하기' : '작성하기'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $html_editor_tail_script; ?>

<script>
// 프롬프트 데이터
var promptData = <?php echo json_encode($prompt_js_data, JSON_UNESCAPED_UNICODE); ?>;

// 프롬프트 선택 시 정보 박스 업데이트
var pmSelect = document.getElementById('pm_id_select');
var infoBox = document.getElementById('prompt_info_box');

function updatePromptInfo() {
    if (!pmSelect || !infoBox) return;

    var pmId = parseInt(pmSelect.value);
    if (!pmId || !promptData[pmId]) {
        infoBox.style.display = 'none';
        var counter = document.getElementById('prompt_char_counter');
        if (counter) counter.style.display = 'none';
        return;
    }

    var p = promptData[pmId];
    infoBox.style.display = '';

    document.getElementById('prompt_desc').textContent = p.content;

    var periodEl = document.getElementById('prompt_period');
    if (periodEl) {
        periodEl.innerHTML = p.start && p.end ? p.start + ' ~ ' + p.end : '';
    }

    document.getElementById('prompt_chars').innerHTML = '최소 <strong>' + p.min_chars + '</strong>자';
    document.getElementById('prompt_reward').innerHTML = '보상: <strong>' + p.point + '</strong>P' + (p.bonus > 0 ? ' + 우수작 ' + p.bonus + 'P' : '');
    document.getElementById('prompt_limit').innerHTML = '1인 <strong>' + p.max_entry + '</strong>회';

    var myCountEl = document.getElementById('prompt_my_count');
    if (myCountEl) {
        if (p.my_count > 0) {
            myCountEl.innerHTML = '내 제출: <strong class="' + (p.my_count >= p.max_entry ? 'text-mg-error' : 'text-mg-accent') + '">' + p.my_count + '</strong>/' + p.max_entry + '회';
        } else {
            myCountEl.innerHTML = '';
        }
    }

    // 글자수 카운터 표시
    var counter = document.getElementById('prompt_char_counter');
    if (counter && p.min_chars > 0) {
        counter.style.display = '';
        document.getElementById('prompt_char_min').innerHTML = '(최소 <strong>' + p.min_chars + '</strong>자)';
    } else if (counter) {
        counter.style.display = 'none';
    }
}

if (pmSelect) {
    pmSelect.addEventListener('change', updatePromptInfo);
    // 초기 로드 시 실행
    updatePromptInfo();
}

// 글자수 카운팅 (에디터 내용 변경 시)
function getContentLength() {
    var content = '';
    // 에디터 영역에서 텍스트 추출 시도
    var editor = document.getElementById('wr_content');
    if (editor) {
        if (editor.tagName === 'TEXTAREA') {
            content = editor.value;
        } else {
            content = editor.textContent || editor.innerText || '';
        }
    }
    // iframe 에디터 (smarteditor 등)
    if (!content) {
        var iframe = document.querySelector('#wr_content_ifr, .se2_inputarea iframe');
        if (iframe && iframe.contentDocument) {
            content = iframe.contentDocument.body.textContent || iframe.contentDocument.body.innerText || '';
        }
    }
    return content.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim().length;
}

function updateCharCount() {
    var countEl = document.getElementById('prompt_char_count');
    if (!countEl) return;
    var len = getContentLength();
    countEl.textContent = len;

    var pmId = pmSelect ? parseInt(pmSelect.value) : 0;
    if (pmId && promptData[pmId] && promptData[pmId].min_chars > 0) {
        countEl.className = len >= promptData[pmId].min_chars ? 'font-medium text-mg-success' : 'font-medium text-mg-error';
    }
}

// 주기적으로 글자수 업데이트
setInterval(updateCharCount, 2000);

function fwrite_submit(f) {
    if (!f.wr_subject.value.trim()) {
        alert('제목을 입력해주세요.');
        f.wr_subject.focus();
        return false;
    }

    <?php echo $editor_js; ?>

    // 프롬프트 글자수 체크
    var pmId = pmSelect ? parseInt(pmSelect.value) : 0;
    if (pmId && promptData[pmId] && promptData[pmId].min_chars > 0) {
        var len = getContentLength();
        if (len < promptData[pmId].min_chars) {
            alert('프롬프트 최소 글자 수(' + promptData[pmId].min_chars + '자)를 충족하지 못했습니다.\n현재 ' + len + '자 입력되었습니다.');
            return false;
        }
    }

    // 제출 횟수 초과 체크
    if (pmId && promptData[pmId] && promptData[pmId].my_count >= promptData[pmId].max_entry) {
        alert('이 프롬프트의 최대 참여 횟수(' + promptData[pmId].max_entry + '회)를 이미 초과했습니다.');
        return false;
    }

    return true;
}

// 캐릭터 선택기 UI 업데이트
document.querySelectorAll('.character-option input[type="radio"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        // 모든 배지 기본 스타일로
        document.querySelectorAll('.character-badge').forEach(function(badge) {
            badge.classList.remove('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
            badge.classList.add('border-mg-bg-tertiary');
        });
        // 선택된 항목 강조
        if (this.checked) {
            var badge = this.parentElement.querySelector('.character-badge');
            badge.classList.remove('border-mg-bg-tertiary');
            badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
        }
    });
    // 초기 상태 설정
    if (radio.checked) {
        var badge = radio.parentElement.querySelector('.character-badge');
        badge.classList.remove('border-mg-bg-tertiary');
        badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
    }
});
</script>
