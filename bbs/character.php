<?php
/**
 * Morgan Edition - 내 캐릭터 관리
 */

include_once('./_common.php');

// 로그인 체크
if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$g5['title'] = '내 캐릭터';

// 내 캐릭터 목록 조회
$sql = "SELECT c.*, s.side_name, cl.class_name
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
        LEFT JOIN {$g5['mg_class_table']} cl ON c.class_id = cl.class_id
        WHERE c.mb_id = '{$member['mb_id']}'
        AND c.ch_state != 'deleted'
        ORDER BY c.ch_main DESC, c.ch_datetime DESC";
$result = sql_query($sql);

$characters = array();
while ($row = sql_fetch_array($result)) {
    $characters[] = $row;
}

// 최대 캐릭터 수 (설정에서 가져오기)
$max_characters = (int)mg_config('max_characters', 10);
$current_count = count($characters);
$can_create = $current_count < $max_characters;

include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 페이지 헤더 -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-mg-text-primary">내 캐릭터</h1>
            <p class="text-sm text-mg-text-muted mt-1"><?php echo $current_count; ?> / <?php echo $max_characters; ?>개</p>
        </div>
        <?php if ($can_create) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="inline-flex items-center gap-2 bg-mg-accent hover:bg-mg-accent-hover text-white px-4 py-2 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>새 캐릭터</span>
        </a>
        <?php } else { ?>
        <span class="text-sm text-mg-text-muted">최대 캐릭터 수에 도달했습니다</span>
        <?php } ?>
    </div>

    <!-- 캐릭터 목록 -->
    <?php if (count($characters) > 0) { ?>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($characters as $char) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden hover:border-mg-accent/50 transition-colors group">
            <!-- 썸네일 -->
            <div class="aspect-square bg-mg-bg-tertiary relative overflow-hidden">
                <?php if ($char['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="<?php echo $char['ch_name']; ?>" class="w-full h-full object-cover">
                <?php } else { ?>
                <div class="w-full h-full flex items-center justify-center text-mg-text-muted">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <?php } ?>

                <!-- 상태 배지 -->
                <div class="absolute top-2 left-2 flex gap-1">
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
                    ?>
                    <span class="<?php echo $state[1]; ?> text-white text-xs px-2 py-0.5 rounded-full"><?php echo $state[0]; ?></span>
                </div>

                <!-- 호버 액션 -->
                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="bg-mg-bg-secondary hover:bg-mg-bg-tertiary text-mg-text-primary p-2 rounded-lg transition-colors" title="수정">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $char['ch_id']; ?>" class="bg-mg-bg-secondary hover:bg-mg-bg-tertiary text-mg-text-primary p-2 rounded-lg transition-colors" title="프로필">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- 정보 -->
            <div class="p-4">
                <h3 class="font-bold text-mg-text-primary text-lg truncate"><?php echo $char['ch_name']; ?></h3>
                <div class="flex items-center gap-2 mt-1 text-sm text-mg-text-muted">
                    <?php if ($char['side_name']) { ?>
                    <span><?php echo $char['side_name']; ?></span>
                    <?php } ?>
                    <?php if ($char['side_name'] && $char['class_name']) { ?>
                    <span class="text-mg-bg-tertiary">|</span>
                    <?php } ?>
                    <?php if ($char['class_name']) { ?>
                    <span><?php echo $char['class_name']; ?></span>
                    <?php } ?>
                </div>
                <p class="text-xs text-mg-text-muted mt-2">
                    <?php echo date('Y.m.d', strtotime($char['ch_datetime'])); ?> 등록
                </p>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } else { ?>
    <!-- 빈 상태 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary py-16 px-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
            <svg class="w-8 h-8 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-mg-text-primary mb-2">아직 캐릭터가 없습니다</h3>
        <p class="text-mg-text-muted mb-6">첫 번째 캐릭터를 만들어보세요!</p>
        <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="inline-flex items-center gap-2 bg-mg-accent hover:bg-mg-accent-hover text-white px-6 py-2.5 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>캐릭터 만들기</span>
        </a>
    </div>
    <?php } ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
