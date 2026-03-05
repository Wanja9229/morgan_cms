<?php
/**
 * Morgan Edition - 콘텐츠 페이지 관리
 * 이용약관, 개인정보처리방침 등 내용 관리 페이지
 */

$sub_menu = "800100";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$co_id = isset($_GET['co_id']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['co_id']) : '';

// 허용된 콘텐츠 ID
$allowed_ids = array('provision', 'privacy');
if (!in_array($co_id, $allowed_ids)) {
    alert('잘못된 접근입니다.');
}

// POST 처리: 저장
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && $_POST['mode'] === 'update') {
    auth_check_menu($auth, $sub_menu, 'w');

    $co_subject = trim($_POST['co_subject'] ?? '');
    $co_content = $_POST['co_content'] ?? '';

    if (!$co_subject) {
        alert('제목을 입력해주세요.');
    }

    // 기존 행 존재 여부 ($co_id는 preg_replace로 영숫자+밑줄만 허용)
    $exists = sql_fetch("SELECT co_id FROM {$g5['content_table']} WHERE co_id = '{$co_id}'");

    if ($exists['co_id']) {
        sql_query("UPDATE {$g5['content_table']}
            SET co_subject = '{$co_subject}',
                co_content = '{$co_content}',
                co_html = 1
            WHERE co_id = '{$co_id}'");
    } else {
        sql_query("INSERT INTO {$g5['content_table']}
            (co_id, co_subject, co_content, co_html, co_skin, co_mobile_skin)
            VALUES ('{$co_id}', '{$co_subject}', '{$co_content}', 1, 'basic', 'basic')");
    }

    $url = G5_ADMIN_URL.'/morgan/content_page.php?co_id='.$co_id.'&msg=saved';
    goto_url($url);
}

// 데이터 로드
$co = sql_fetch("SELECT * FROM {$g5['content_table']} WHERE co_id = '{$co_id}'");
if (!isset($co['co_id'])) {
    $co = array('co_id' => $co_id, 'co_subject' => '', 'co_content' => '');
}

$labels = array(
    'provision' => '서비스 이용약관',
    'privacy'   => '개인정보 처리방침',
);

$g5['title'] = ($labels[$co_id] ?? '콘텐츠') . ' 편집';

include_once('./_head.php');
?>

<h2 class="mg-page-title"><?php echo $g5['title']; ?></h2>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
<div style="background:var(--mg-success);color:#fff;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem;">
    저장되었습니다.
</div>
<?php endif; ?>

<form method="post" action="">
    <input type="hidden" name="mode" value="update">

    <div class="mg-card">
        <div class="mg-card-header">
            <h3><?php echo htmlspecialchars($labels[$co_id] ?? $co_id); ?></h3>
        </div>
        <div class="mg-card-body">

            <div class="mg-form-group">
                <label class="mg-form-label" for="co_subject">제목</label>
                <input type="text" name="co_subject" id="co_subject"
                    value="<?php echo htmlspecialchars($co['co_subject'] ?? ''); ?>"
                    class="mg-form-input" style="max-width:400px;">
            </div>

            <div class="mg-form-group" style="margin-top:1rem;">
                <label class="mg-form-label" for="co_content">내용 (HTML)</label>
                <textarea name="co_content" id="co_content" class="mg-form-input"
                    rows="20" style="font-family:monospace;font-size:0.8125rem;line-height:1.6;"><?php echo htmlspecialchars($co['co_content'] ?? ''); ?></textarea>
                <small style="color:var(--mg-text-muted);font-size:0.75rem;">HTML 태그를 사용할 수 있습니다. (&lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt; 등)</small>
            </div>

        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:0.75rem;">
        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
        <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=<?php echo $co_id; ?>" target="_blank" class="mg-btn" style="background:var(--mg-bg-tertiary);color:var(--mg-text-secondary);">미리보기</a>
    </div>
</form>

<?php
include_once('./_tail.php');
