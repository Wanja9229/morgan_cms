<?php
/**
 * Morgan Edition - 캐릭터 프로필 보기
 */

include_once('./_common.php');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;

if (!$ch_id) {
    alert('잘못된 접근입니다.');
}

// 캐릭터 정보 조회
$sql = "SELECT c.*, s.side_name, s.side_desc, cl.class_name, cl.class_desc, m.mb_nick
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
        LEFT JOIN {$g5['mg_class_table']} cl ON c.class_id = cl.class_id
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        WHERE c.ch_id = {$ch_id}";
$char = sql_fetch($sql);

if (!$char['ch_id']) {
    alert('존재하지 않는 캐릭터입니다.');
}

// 비공개 캐릭터 체크 (editing 상태는 본인만)
if ($char['ch_state'] == 'editing' || $char['ch_state'] == 'deleted') {
    if (!$is_member || $member['mb_id'] != $char['mb_id']) {
        alert('비공개 캐릭터입니다.');
    }
}

// 본인 캐릭터인지
$is_owner = $is_member && $member['mb_id'] == $char['mb_id'];

// 프로필 값 조회
$sql = "SELECT pf.*, pv.pv_value
        FROM {$g5['mg_profile_field_table']} pf
        LEFT JOIN {$g5['mg_profile_value_table']} pv ON pf.pf_id = pv.pf_id AND pv.ch_id = {$ch_id}
        WHERE pf.pf_use = 1
        ORDER BY pf.pf_order, pf.pf_id";
$result = sql_query($sql);

$profile_fields = array();
while ($row = sql_fetch_array($result)) {
    if (!empty($row['pv_value'])) {
        $profile_fields[] = $row;
    }
}

// 카테고리별 그룹핑
$grouped_fields = array();
foreach ($profile_fields as $field) {
    $category = $field['pf_category'] ?: '기본정보';
    $grouped_fields[$category][] = $field;
}

$g5['title'] = $char['ch_name'].' - 캐릭터 프로필';

include_once(G5_THEME_PATH.'/head.php');
?>

<div class="max-w-4xl mx-auto">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" class="inline-flex items-center gap-1 text-sm text-mg-text-muted hover:text-mg-accent transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>뒤로</span>
    </a>

    <!-- 프로필 헤더 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="md:flex">
            <!-- 이미지 -->
            <div class="md:w-64 lg:w-80 flex-shrink-0">
                <div class="aspect-square bg-mg-bg-tertiary">
                    <?php if ($char['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="<?php echo $char['ch_name']; ?>" class="w-full h-full object-cover">
                    <?php } else { ?>
                    <div class="w-full h-full flex items-center justify-center text-mg-text-muted">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- 기본 정보 -->
            <div class="flex-1 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <!-- 배지 -->
                        <div class="flex items-center gap-2 mb-2">
                            <?php if ($char['ch_main']) { ?>
                            <span class="bg-mg-accent text-white text-xs px-2 py-0.5 rounded-full">대표</span>
                            <?php } ?>
                            <?php
                            $state_labels = array(
                                'editing' => array('수정중', 'bg-gray-500'),
                                'pending' => array('승인대기', 'bg-yellow-500'),
                                'approved' => array('승인됨', 'bg-green-500'),
                            );
                            $state = $state_labels[$char['ch_state']] ?? array('', '');
                            if ($state[0]) {
                            ?>
                            <span class="<?php echo $state[1]; ?> text-white text-xs px-2 py-0.5 rounded-full"><?php echo $state[0]; ?></span>
                            <?php } ?>
                        </div>

                        <!-- 이름 -->
                        <h1 class="text-3xl font-bold text-mg-text-primary"><?php echo $char['ch_name']; ?></h1>

                        <!-- 세력/종족 -->
                        <div class="flex items-center gap-3 mt-2 text-mg-text-secondary">
                            <?php if ($char['side_name']) { ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                </svg>
                                <?php echo $char['side_name']; ?>
                            </span>
                            <?php } ?>
                            <?php if ($char['class_name']) { ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                <?php echo $char['class_name']; ?>
                            </span>
                            <?php } ?>
                        </div>

                        <!-- 오너 정보 -->
                        <div class="mt-4 text-sm text-mg-text-muted">
                            <span class="text-mg-text-secondary">@<?php echo $char['mb_nick']; ?></span>
                            <span class="mx-2">·</span>
                            <span><?php echo date('Y.m.d', strtotime($char['ch_datetime'])); ?> 등록</span>
                        </div>
                    </div>

                    <!-- 수정 버튼 (본인만) -->
                    <?php if ($is_owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="inline-flex items-center gap-1 text-sm bg-mg-bg-tertiary hover:bg-mg-bg-primary text-mg-text-secondary px-3 py-1.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>수정</span>
                    </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 프로필 상세 -->
    <?php if (count($grouped_fields) > 0) { ?>
    <div class="space-y-4">
        <?php foreach ($grouped_fields as $category => $fields) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary"><?php echo $category; ?></h2>
            </div>
            <div class="p-4">
                <dl class="space-y-4">
                    <?php foreach ($fields as $field) { ?>
                    <div>
                        <dt class="text-sm font-medium text-mg-text-muted mb-1"><?php echo $field['pf_name']; ?></dt>
                        <dd class="text-mg-text-primary">
                            <?php
                            if ($field['pf_type'] == 'url') {
                                echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank" class="text-mg-accent hover:underline">'.htmlspecialchars($field['pv_value']).'</a>';
                            } elseif ($field['pf_type'] == 'textarea') {
                                echo nl2br(htmlspecialchars($field['pv_value']));
                            } else {
                                echo htmlspecialchars($field['pv_value']);
                            }
                            ?>
                        </dd>
                    </div>
                    <?php } ?>
                </dl>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 활동 내역 (추후 구현) -->
    <!--
    <div class="mt-6 bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
            <h2 class="font-medium text-mg-text-primary">최근 활동</h2>
        </div>
        <div class="p-8 text-center text-mg-text-muted">
            <p>활동 내역이 없습니다.</p>
        </div>
    </div>
    -->
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
