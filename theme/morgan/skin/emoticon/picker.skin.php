<?php
/**
 * Morgan Edition - 이모티콘 피커 컴포넌트
 *
 * 이 파일은 textarea 근처에 include하여 사용합니다.
 * 필요 변수: $picker_id (고유 ID, 기본값 'default')
 *            $picker_target (코드를 삽입할 textarea name/id)
 */

if (!defined('_GNUBOARD_')) exit;

// 이모티콘 비활성 시 렌더링 안 함
if (function_exists('mg_config') && mg_config('emoticon_use', '1') != '1') return;

if (!isset($picker_id)) $picker_id = 'default';
if (!isset($picker_target)) $picker_target = 'wr_content';
?>

<div class="mg-emoticon-picker-wrap" id="mgEmoticonWrap_<?php echo $picker_id; ?>" style="position:relative;display:inline-block;">
    <button type="button"
            class="mg-emoticon-btn"
            onclick="MgEmoticonPicker.toggle('<?php echo $picker_id; ?>', '<?php echo $picker_target; ?>')"
            title="이모티콘">
        <i data-lucide="smile" style="width:20px;height:20px;"></i>
    </button>

    <div class="mg-emoticon-popup" id="mgEmoticonPopup_<?php echo $picker_id; ?>" style="display:none;">
        <div class="mg-emoticon-popup-header">
            <span>이모티콘</span>
            <button type="button" onclick="MgEmoticonPicker.close('<?php echo $picker_id; ?>')" class="mg-emoticon-popup-close">&times;</button>
        </div>
        <div class="mg-emoticon-popup-tabs" id="mgEmoticonTabs_<?php echo $picker_id; ?>">
            <!-- 탭은 JS로 동적 생성 -->
        </div>
        <div class="mg-emoticon-popup-grid" id="mgEmoticonGrid_<?php echo $picker_id; ?>">
            <div class="mg-emoticon-popup-empty">보유한 이모티콘이 없습니다.</div>
        </div>
        <div class="mg-emoticon-popup-footer">
            <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=emoticon" class="mg-emoticon-popup-link">이모티콘 상점</a>
        </div>
    </div>
</div>
