<?php
/**
 * Morgan Edition - 세계관 지도 관리
 */

$sub_menu = '800178';
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$map_image = mg_config('expedition_map_image', '');
$marker_style = mg_config('map_marker_style', 'pin');

// 맵 좌표가 있는 파견지 목록 (미리보기용)
$areas = mg_get_expedition_areas();
$map_areas = array();
foreach ($areas as $area) {
    if ($area['ea_map_x'] !== null && $area['ea_map_y'] !== null) {
        $map_areas[] = array(
            'ea_id'    => $area['ea_id'],
            'ea_name'  => $area['ea_name'],
            'ea_map_x' => (float)$area['ea_map_x'],
            'ea_map_y' => (float)$area['ea_map_y'],
            'ea_status' => $area['ea_status'],
        );
    }
}

$g5['title'] = '지도 관리';
require_once __DIR__.'/_head.php';
?>

<form method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/lore_map_update.php" enctype="multipart/form-data">

<div class="mg-card">
    <div class="mg-card-header">
        <h3>세계관 지도 설정</h3>
    </div>
    <div class="mg-card-body">

        <!-- 맵 이미지 업로드 -->
        <div class="mg-form-group" style="max-width:600px;">
            <label class="mg-form-label">지도 이미지</label>
            <?php if ($map_image) { ?>
            <div id="map-current" style="margin-bottom:1rem;">
                <img src="<?php echo htmlspecialchars($map_image); ?>" alt="현재 지도" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid var(--mg-bg-tertiary);">
                <div style="margin-top:0.5rem;">
                    <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteMapImage()">이미지 삭제</button>
                </div>
            </div>
            <?php } ?>
            <input type="file" name="map_image_file" id="map_image_file" accept="image/*" class="mg-form-input" onchange="previewNewMap(this)">
            <div id="map-preview" style="display:none;margin-top:0.75rem;">
                <img id="map-preview-img" src="" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid var(--mg-bg-tertiary);">
                <div style="margin-top:4px;font-size:0.8rem;color:var(--mg-accent);">새 이미지 선택됨</div>
            </div>
            <input type="hidden" name="map_image_action" id="map_image_action" value="">
            <p style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.5rem;">JPG, PNG, GIF, WebP / 최대 10MB / 권장: 1920x1080px 이상</p>
        </div>

        <!-- 마커 스타일 -->
        <div class="mg-form-group" style="max-width:400px;margin-top:1.5rem;">
            <label class="mg-form-label">마커 스타일</label>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <?php
                $styles = array(
                    'pin' => '드롭핀',
                    'circle' => '원형',
                    'diamond' => '다이아몬드',
                    'flag' => '깃발',
                );
                foreach ($styles as $key => $label) { ?>
                <label style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;padding:8px 12px;border-radius:8px;border:2px solid <?php echo $marker_style === $key ? 'var(--mg-accent)' : 'var(--mg-bg-tertiary)'; ?>;background:var(--mg-bg-primary);min-width:70px;">
                    <input type="radio" name="map_marker_style" value="<?php echo $key; ?>" <?php echo $marker_style === $key ? 'checked' : ''; ?> style="display:none;" onchange="selectStyle(this)">
                    <svg width="30" height="40" viewBox="0 0 30 40" id="style-svg-<?php echo $key; ?>">
                    <?php if ($key === 'pin') { ?>
                        <path d="M15 0C8.4 0 3 5.4 3 12c0 9 12 24 12 24s12-15 12-24C27 5.4 21.6 0 15 0z" fill="var(--mg-accent)"/>
                        <circle cx="15" cy="12" r="5" fill="var(--mg-bg-primary)"/>
                    <?php } elseif ($key === 'circle') { ?>
                        <circle cx="15" cy="16" r="12" fill="var(--mg-accent)" stroke="var(--mg-bg-primary)" stroke-width="3"/>
                        <circle cx="15" cy="16" r="4" fill="var(--mg-bg-primary)"/>
                    <?php } elseif ($key === 'diamond') { ?>
                        <path d="M15 2 L27 18 L15 34 L3 18 Z" fill="var(--mg-accent)" stroke="var(--mg-bg-primary)" stroke-width="2"/>
                        <circle cx="15" cy="18" r="4" fill="var(--mg-bg-primary)"/>
                    <?php } elseif ($key === 'flag') { ?>
                        <rect x="13" y="8" width="3" height="28" rx="1" fill="var(--mg-accent)"/>
                        <path d="M16 8 L28 14 L16 20 Z" fill="var(--mg-accent)"/>
                        <circle cx="14.5" cy="6" r="3" fill="var(--mg-accent)"/>
                    <?php } ?>
                    </svg>
                    <span style="font-size:0.75rem;color:var(--mg-text-secondary);"><?php echo $label; ?></span>
                </label>
                <?php } ?>
            </div>
            <p style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.5rem;">모든 마커에 동일하게 적용됩니다</p>
        </div>

    </div>
</div>

<?php if ($map_image && !empty($map_areas)) { ?>
<!-- 맵 미리보기 -->
<div class="mg-card" style="margin-top:1rem;">
    <div class="mg-card-header">
        <h3>마커 미리보기</h3>
        <span style="font-size:0.8rem;color:var(--mg-text-muted);"><?php echo count($map_areas); ?>개 마커 등록됨 · 마커 편집은 파견지 관리에서</span>
    </div>
    <div class="mg-card-body" style="padding:0;">
        <div style="position:relative;overflow:auto;max-height:500px;">
            <img src="<?php echo htmlspecialchars($map_image); ?>" style="display:block;width:100%;min-width:500px;" alt="지도 미리보기" draggable="false">
            <?php foreach ($map_areas as $ma) {
                $color = $ma['ea_status'] === 'locked' ? '#6b7280' : 'var(--mg-accent)';
                $inner = $ma['ea_status'] === 'locked' ? '#4b5563' : 'var(--mg-bg-primary)';
            ?>
            <div style="position:absolute;left:<?php echo $ma['ea_map_x']; ?>%;top:<?php echo $ma['ea_map_y']; ?>%;width:30px;height:30px;margin-left:-15px;margin-top:-30px;" title="<?php echo htmlspecialchars($ma['ea_name']); ?>">
                <svg viewBox="0 0 24 36" width="30" height="36">
                    <?php if ($marker_style === 'circle') { ?>
                    <circle cx="12" cy="14" r="10" fill="<?php echo $color; ?>" stroke="<?php echo $inner; ?>" stroke-width="2.5"/><circle cx="12" cy="14" r="3.5" fill="<?php echo $inner; ?>"/>
                    <?php } elseif ($marker_style === 'diamond') { ?>
                    <path d="M12 1 L23 16 L12 31 L1 16 Z" fill="<?php echo $color; ?>" stroke="<?php echo $inner; ?>" stroke-width="1.5"/><circle cx="12" cy="16" r="3.5" fill="<?php echo $inner; ?>"/>
                    <?php } elseif ($marker_style === 'flag') { ?>
                    <rect x="10" y="6" width="2.5" height="26" rx="1" fill="<?php echo $color; ?>"/><path d="M12.5 6 L23 11 L12.5 16 Z" fill="<?php echo $color; ?>"/><circle cx="11.25" cy="4.5" r="2.5" fill="<?php echo $color; ?>"/>
                    <?php } else { ?>
                    <path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z" fill="<?php echo $color; ?>"/><circle cx="12" cy="12" r="5" fill="<?php echo $inner; ?>"/>
                    <?php } ?>
                </svg>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>

<div style="margin-top:1.5rem;text-align:center;">
    <button type="submit" class="mg-btn mg-btn-primary">저장</button>
</div>

</form>

<script>
function previewNewMap(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('map-preview-img').src = e.target.result;
            document.getElementById('map-preview').style.display = 'block';
            document.getElementById('map_image_action').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function deleteMapImage() {
    document.getElementById('map_image_action').value = '__DELETE__';
    var cur = document.getElementById('map-current');
    if (cur) cur.innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.85rem;">이미지가 삭제됩니다 (저장 시 적용)</span>';
}

function selectStyle(radio) {
    document.querySelectorAll('[name="map_marker_style"]').forEach(function(r) {
        r.closest('label').style.borderColor = 'var(--mg-bg-tertiary)';
    });
    radio.closest('label').style.borderColor = 'var(--mg-accent)';
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
