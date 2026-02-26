<?php
/**
 * Morgan Edition - 파견지 관리
 */

$sub_menu = "801110";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 파견지 목록
$areas = mg_get_expedition_areas();

// 재료 종류 목록 (드롭 테이블용)
$material_types = mg_get_material_types();

// 시설 목록 (해금 조건용)
$facility_list = array();
$fc_result = sql_query("SELECT fc_id, fc_name, fc_status FROM {$g5['mg_facility_table']} ORDER BY fc_order, fc_id");
while ($fc_row = sql_fetch_array($fc_result)) {
    $facility_list[] = $fc_row;
}

// 이벤트 목록 (이벤트 매칭용)
$events = array();
$ev_result = sql_query("SELECT * FROM {$g5['mg_expedition_event_table']} ORDER BY ee_order, ee_id");
while ($ev_row = sql_fetch_array($ev_result)) {
    $events[] = $ev_row;
}

// 파견지별 이벤트 매칭 데이터 로딩
$event_links = array();
$eea_result = sql_query("SELECT eea.*, e.ee_name, e.ee_effect_type FROM {$g5['mg_expedition_event_area_table']} eea
    LEFT JOIN {$g5['mg_expedition_event_table']} e ON eea.ee_id = e.ee_id
    ORDER BY eea.ea_id, eea.eea_id");
while ($eea_row = sql_fetch_array($eea_result)) {
    $ea_id_key = (int)$eea_row['ea_id'];
    if (!isset($event_links[$ea_id_key])) $event_links[$ea_id_key] = array();
    $event_links[$ea_id_key][] = $eea_row;
}
// areas 배열에 이벤트 매칭 추가
foreach ($areas as &$_a) {
    $_a['event_links'] = isset($event_links[(int)$_a['ea_id']]) ? $event_links[(int)$_a['ea_id']] : array();
}
unset($_a);

// 맵 관련 설정
$ui_mode = mg_config('expedition_ui_mode', 'list');
$map_image = mg_config('expedition_map_image', '');
$marker_style = mg_config('map_marker_style', 'pin');

$g5['title'] = '파견지 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 파견지</div>
        <div class="mg-stat-value"><?php echo count($areas); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">활성</div>
        <div class="mg-stat-value"><?php echo count(array_filter($areas, function($a) { return $a['ea_status'] === 'active'; })); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">숨김</div>
        <div class="mg-stat-value"><?php echo count(array_filter($areas, function($a) { return $a['ea_status'] === 'hidden'; })); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">잠김</div>
        <div class="mg-stat-value"><?php echo count(array_filter($areas, function($a) { return $a['ea_status'] === 'locked'; })); ?></div>
    </div>
</div>

<!-- UI 모드 토글 + 추가 버튼 -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <div style="display:flex;align-items:center;gap:0.75rem;">
        <span style="font-size:0.85rem;color:var(--mg-text-secondary);">유저 UI:</span>
        <div style="display:inline-flex;border-radius:8px;overflow:hidden;border:1px solid var(--mg-bg-tertiary);">
            <button type="button" id="btn-mode-list" onclick="setUiMode('list')" style="padding:6px 14px;font-size:0.8rem;border:none;cursor:pointer;transition:background 0.15s;<?php echo $ui_mode !== 'map' ? 'background:var(--mg-accent);color:var(--mg-bg-primary);font-weight:600;' : 'background:var(--mg-bg-primary);color:var(--mg-text-secondary);'; ?>">카드 목록</button>
            <button type="button" id="btn-mode-map" onclick="setUiMode('map')" style="padding:6px 14px;font-size:0.8rem;border:none;cursor:pointer;transition:background 0.15s;<?php echo $ui_mode === 'map' ? 'background:var(--mg-accent);color:var(--mg-bg-primary);font-weight:600;' : 'background:var(--mg-bg-primary);color:var(--mg-text-secondary);'; ?>"<?php echo !$map_image ? ' disabled title="파견 지도 이미지를 먼저 등록하세요"' : ''; ?>>파견 지도</button>
        </div>
    </div>
    <button type="button" class="mg-btn mg-btn-primary" onclick="openAreaModal()">파견지 추가</button>
</div>

<!-- 파견 지도 관리 (맵 모드일 때만 표시) -->
<div id="map-section" style="display:<?php echo $ui_mode === 'map' ? 'block' : 'none'; ?>;">
    <!-- 맵 이미지 업로드 -->
    <div class="mg-card" style="margin-bottom:1rem;">
        <div class="mg-card-header">
            <h3>파견 지도</h3>
            <span style="font-size:0.8rem;color:var(--mg-text-muted);">파견 전용 지도 이미지 (세계관 맵과 별도)</span>
        </div>
        <div class="mg-card-body">
            <?php if ($map_image) { ?>
            <div style="margin-bottom:12px;">
                <img src="<?php echo htmlspecialchars($map_image); ?>" style="max-width:300px;max-height:150px;border-radius:8px;border:1px solid var(--mg-bg-tertiary);">
            </div>
            <?php } ?>
            <form id="map-upload-form" method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/expedition_area_update.php" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
                <input type="hidden" name="action" value="upload_map_image">
                <div class="mg-form-group" style="margin-bottom:0;">
                    <label class="mg-form-label" style="font-size:0.75rem;">지도 이미지 (JPG/PNG/WebP, 최대 20MB)</label>
                    <input type="file" name="map_image_file" accept="image/*" class="mg-form-input" style="width:280px;">
                </div>
                <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">업로드</button>
                <?php if ($map_image) { ?>
                <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteMapImage()">이미지 삭제</button>
                <?php } ?>
            </form>
        </div>
    </div>

    <?php if ($map_image) { ?>
    <!-- 맵 비주얼 에디터 -->
    <div class="mg-card" style="margin-bottom:1rem;">
        <div class="mg-card-header">
            <h3>마커 배치</h3>
            <span style="font-size:0.8rem;color:var(--mg-text-muted);">맵 위를 클릭하여 마커를 배치하세요 · 기존 마커 클릭 시 수정</span>
        </div>
        <div class="mg-card-body" style="padding:0;">
            <div id="map-mobile-notice" style="display:none;padding:0.75rem 1rem;background:var(--mg-bg-tertiary);border-left:3px solid var(--mg-accent);">
                <strong style="font-size:0.85rem;color:var(--mg-text-primary);">PC 환경 권장</strong>
                <p style="font-size:0.8rem;color:var(--mg-text-muted);margin-top:0.25rem;">맵 마커 배치/편집은 PC에서 최적화되어 있습니다.</p>
            </div>
            <script>if(window.innerWidth<768)document.getElementById('map-mobile-notice').style.display='block';</script>
            <div id="map-editor" style="position:relative;overflow:auto;max-height:600px;cursor:crosshair;">
                <img src="<?php echo htmlspecialchars($map_image); ?>" id="map-editor-img" style="display:block;width:100%;min-width:600px;" alt="파견 지도" draggable="false">
                <div id="map-editor-markers"></div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:900px;table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:50px;">순서</th>
                    <th style="width:60px;">아이콘</th>
                    <th style="width:140px;">파견지명</th>
                    <th style="width:70px;">상태</th>
                    <th style="width:80px;">스태미나</th>
                    <th style="width:80px;">소요시간</th>
                    <th style="width:90px;">보상</th>
                    <th style="width:180px;">드롭 아이템</th>
                    <th style="width:70px;">좌표</th>
                    <th style="width:100px;">해금 조건</th>
                    <th style="width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($areas as $area) {
                    $status_badges = array(
                        'active' => '<span class="mg-badge mg-badge-success">활성</span>',
                        'hidden' => '<span class="mg-badge">숨김</span>',
                        'locked' => '<span class="mg-badge mg-badge-warning">잠김</span>',
                    );
                    $status_badge = isset($status_badges[$area['ea_status']]) ? $status_badges[$area['ea_status']] : '';

                    $duration_h = floor($area['ea_duration'] / 60);
                    $duration_m = $area['ea_duration'] % 60;
                    $duration_text = $duration_h > 0 ? $duration_h.'시간' : '';
                    $duration_text .= $duration_m > 0 ? ' '.$duration_m.'분' : '';
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $area['ea_order']; ?></td>
                    <td style="text-align:center;">
                        <?php if ($area['ea_icon']) {
                            echo mg_icon($area['ea_icon'], 'w-6 h-6');
                        } else {
                            echo '<svg class="w-6 h-6" style="display:inline-block;color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
                        } ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($area['ea_name']); ?></strong>
                        <?php if ($area['ea_desc']) { ?>
                        <br><span style="font-size:0.8rem;color:var(--mg-text-muted);"><?php echo mb_substr($area['ea_desc'], 0, 40); ?><?php echo mb_strlen($area['ea_desc']) > 40 ? '...' : ''; ?></span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;"><?php echo $status_badge; ?></td>
                    <td style="text-align:center;"><?php echo $area['ea_stamina_cost']; ?></td>
                    <td style="text-align:center;"><?php echo trim($duration_text); ?></td>
                    <td style="text-align:center;font-size:0.85rem;">
                        <?php if (($area['ea_point_min'] ?? 0) > 0) {
                            $min = (int)$area['ea_point_min'];
                            $max = (int)$area['ea_point_max'];
                            echo $min === $max ? $min.'P' : $min.'~'.$max.'P';
                        } else {
                            echo '<span style="color:var(--mg-text-muted);">-</span>';
                        } ?>
                        <?php if ($area['ea_partner_point'] > 0) { ?>
                        <br><span style="font-size:0.75rem;color:var(--mg-text-muted);">파트너 +<?php echo $area['ea_partner_point']; ?>P</span>
                        <?php } ?>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?php foreach ($area['drops'] as $drop) {
                            $rare_style = $drop['ed_is_rare'] ? 'color:#a78bfa;font-weight:bold;' : '';
                        ?>
                        <span style="display:inline-flex;align-items:center;gap:2px;margin-right:6px;<?php echo $rare_style; ?>" title="<?php echo htmlspecialchars($drop['mt_name']); ?> (<?php echo $drop['ed_min']; ?>~<?php echo $drop['ed_max']; ?>개, <?php echo $drop['ed_chance']; ?>%)">
                            <?php echo mg_icon($drop['mt_icon'], 'w-4 h-4'); ?>
                            <?php echo $drop['ed_chance']; ?>%
                            <?php if ($drop['ed_is_rare']) echo '<span style="font-size:0.7rem;font-weight:bold;">RARE</span>'; ?>
                        </span>
                        <?php } ?>
                        <?php if (empty($area['drops'])) { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;font-size:0.8rem;">
                        <?php if ($area['ea_map_x'] !== null && $area['ea_map_y'] !== null) { ?>
                        <span style="color:var(--mg-accent);" title="X:<?php echo round($area['ea_map_x'],1); ?>% Y:<?php echo round($area['ea_map_y'],1); ?>%"><svg style="display:inline-block;width:14px;height:14px;vertical-align:middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?php if ($area['ea_unlock_facility']) {
                            echo htmlspecialchars($area['unlock_facility_name'] ?: '시설 #'.$area['ea_unlock_facility']);
                        } else {
                            echo '-';
                        } ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editArea(<?php echo $area['ea_id']; ?>)">수정</button>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteArea(<?php echo $area['ea_id']; ?>)">삭제</button>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($areas)) { ?>
                <tr>
                    <td colspan="11" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 파견지가 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 파견지 모달 -->
<div id="area-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:700px;max-height:90vh;overflow-y:auto;">
        <div class="mg-modal-header">
            <h3 id="modal-title">파견지 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="area-form" method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/expedition_area_update.php" enctype="multipart/form-data">
            <input type="hidden" name="w" id="form_w" value="">
            <input type="hidden" name="ea_id" id="form_ea_id" value="">
            <input type="hidden" name="ea_map_x" id="ea_map_x" value="">
            <input type="hidden" name="ea_map_y" id="ea_map_y" value="">

            <div class="mg-modal-body">
                <!-- 좌표 표시 -->
                <div id="coord-display" style="display:none;margin-bottom:1rem;padding:8px 12px;border-radius:8px;background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:0.85rem;color:var(--mg-text-secondary);"><svg style="display:inline-block;width:14px;height:14px;vertical-align:middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> 맵 좌표: <strong id="coord-text" style="color:var(--mg-accent);"></strong></span>
                        <button type="button" style="font-size:0.75rem;color:var(--mg-text-muted);background:none;border:none;cursor:pointer;text-decoration:underline;" onclick="clearCoords()">좌표 초기화</button>
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">파견지명 *</label>
                    <input type="text" name="ea_name" id="ea_name" class="mg-form-input" required>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">설명</label>
                    <textarea name="ea_desc" id="ea_desc" class="mg-form-textarea" rows="2"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">아이콘</label>
                        <?php mg_icon_input('ea_icon', '', array('placeholder' => 'globe-americas, fire 등', 'delete_name' => 'del_ea_icon')); ?>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="ea_order" id="ea_order" class="mg-form-input" value="0">
                    </div>
                </div>

                <!-- 파견지 이미지 -->
                <div class="mg-form-group">
                    <label class="mg-form-label">파견지 이미지</label>
                    <div id="ea_image_preview" style="display:none;margin-bottom:8px;">
                        <img id="ea_image_img" src="" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--mg-bg-tertiary);">
                        <div style="margin-top:4px;">
                            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="removeAreaImage()">이미지 삭제</button>
                        </div>
                    </div>
                    <input type="file" name="ea_image_file" id="ea_image_file" accept="image/*" class="mg-form-input" onchange="previewAreaImage(this)">
                    <input type="hidden" name="ea_image_action" id="ea_image_action" value="">
                    <p style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:4px;">JPG, PNG, GIF, WebP / 최대 5MB / 권장 16:9 비율</p>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">필요 스태미나 *</label>
                        <input type="number" name="ea_stamina_cost" id="ea_stamina_cost" class="mg-form-input" min="1" value="2" required>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">소요시간 (분) *</label>
                        <input type="number" name="ea_duration" id="ea_duration" class="mg-form-input" min="1" value="60" required>
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">보상 포인트 (참가자)</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="number" name="ea_point_min" id="ea_point_min" class="mg-form-input" style="width:100px;" min="0" value="0" placeholder="최소">
                        <span style="color:var(--mg-text-muted);">~</span>
                        <input type="number" name="ea_point_max" id="ea_point_max" class="mg-form-input" style="width:100px;" min="0" value="0" placeholder="최대">
                        <span style="font-size:0.8rem;color:var(--mg-text-muted);">P (0이면 포인트 보상 없음)</span>
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">파트너 보너스PT</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="number" name="ea_partner_point" id="ea_partner_point" class="mg-form-input" style="width:100px;" min="0" value="10">
                        <span style="font-size:0.8rem;color:var(--mg-text-muted);">P (파트너로 선택된 유저에게 지급)</span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">상태</label>
                        <select name="ea_status" id="ea_status" class="mg-form-input">
                            <option value="active">활성</option>
                            <option value="hidden">숨김</option>
                            <option value="locked">잠김 (시설 해금 필요)</option>
                        </select>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">해금 조건 (시설)</label>
                        <select name="ea_unlock_facility" id="ea_unlock_facility" class="mg-form-input">
                            <option value="0">없음</option>
                            <?php foreach ($facility_list as $fc) { ?>
                            <option value="<?php echo $fc['fc_id']; ?>"><?php echo htmlspecialchars($fc['fc_name']); ?> (<?php echo $fc['fc_status']; ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- 드롭 테이블 -->
                <div class="mg-form-group">
                    <label class="mg-form-label">드롭 테이블</label>
                    <div id="drop-table">
                        <!-- JS로 동적 추가 -->
                    </div>
                    <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="addDropRow()" style="margin-top:8px;">+ 드롭 추가</button>
                </div>

                <!-- 이벤트 매칭 -->
                <?php if (!empty($events)) { ?>
                <div class="mg-form-group">
                    <label class="mg-form-label">파견 이벤트</label>
                    <p style="font-size:0.75rem;color:var(--mg-text-muted);margin-bottom:6px;">파견 완료 시 확률적으로 발동되는 이벤트를 매칭합니다.</p>
                    <div id="event-link-table">
                        <!-- JS로 동적 추가 -->
                    </div>
                    <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="addEventLinkRow()" style="margin-top:8px;">+ 이벤트 추가</button>
                </div>
                <?php } ?>
            </div>

            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<!-- 맵 클릭 시 파견지 선택 팝업 -->
<div id="map-place-popup" style="display:none; position:fixed; z-index:100; background:var(--mg-bg-secondary); border:1px solid var(--mg-bg-tertiary); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.5); min-width:220px; max-width:280px; overflow:hidden;">
    <div style="padding:8px 12px; border-bottom:1px solid var(--mg-bg-tertiary); font-size:0.8rem; color:var(--mg-text-muted); display:flex; justify-content:space-between; align-items:center;">
        <span>파견지 배치</span>
        <button type="button" onclick="closePlacePopup()" style="background:none; border:none; color:var(--mg-text-muted); cursor:pointer; font-size:1rem; line-height:1; padding:0 2px;">&times;</button>
    </div>
    <div id="map-place-list" style="max-height:240px; overflow-y:auto;">
        <!-- JS로 채움 -->
    </div>
    <div style="padding:6px 8px; border-top:1px solid var(--mg-bg-tertiary);">
        <button type="button" id="map-place-new-btn" class="mg-btn mg-btn-primary mg-btn-sm" style="width:100%; font-size:0.8rem;">+ 새 파견지 추가</button>
    </div>
</div>

<style>
.adm-map-marker { position:absolute; cursor:grab; z-index:5; user-select:none; }
.adm-map-marker:hover { z-index:10; }
.adm-map-marker svg { filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4)); }
.adm-map-marker .marker-label { position:absolute; top:100%; left:50%; transform:translateX(-50%); white-space:nowrap; font-size:11px; color:var(--mg-text-primary); background:rgba(0,0,0,0.7); padding:1px 6px; border-radius:4px; margin-top:2px; pointer-events:none; }
#map-place-popup .place-item { padding:8px 12px; cursor:pointer; font-size:0.85rem; color:var(--mg-text-primary); display:flex; align-items:center; gap:8px; transition:background 0.1s; }
#map-place-popup .place-item:hover { background:var(--mg-bg-tertiary); }
#map-place-popup .place-item .place-status { font-size:0.7rem; padding:1px 6px; border-radius:4px; }
#map-place-popup .place-empty { padding:12px; text-align:center; font-size:0.8rem; color:var(--mg-text-muted); }
</style>

<script>
var areas = <?php echo json_encode($areas); ?>;
var materialTypes = <?php echo json_encode($material_types); ?>;
var expeditionEvents = <?php echo json_encode($events); ?>;
var MARKER_STYLE = '<?php echo $marker_style; ?>';
var UPDATE_URL = '<?php echo G5_ADMIN_URL; ?>/morgan/expedition_area_update.php';

// === 마커 SVG 생성 ===
function getMarkerSVG(style, color, inner) {
    color = color || 'var(--mg-accent)';
    inner = inner || 'var(--mg-bg-primary)';
    switch (style) {
        case 'circle':
            return '<svg viewBox="0 0 28 28" width="28" height="28"><circle cx="14" cy="14" r="12" fill="'+color+'" stroke="'+inner+'" stroke-width="2.5"/><circle cx="14" cy="14" r="4" fill="'+inner+'"/></svg>';
        case 'diamond':
            return '<svg viewBox="0 0 24 32" width="24" height="32"><path d="M12 1 L23 16 L12 31 L1 16 Z" fill="'+color+'" stroke="'+inner+'" stroke-width="1.5"/><circle cx="12" cy="16" r="3.5" fill="'+inner+'"/></svg>';
        case 'flag':
            return '<svg viewBox="0 0 24 36" width="24" height="36"><rect x="10" y="6" width="2.5" height="26" rx="1" fill="'+color+'"/><path d="M12.5 6 L23 11 L12.5 16 Z" fill="'+color+'"/><circle cx="11.25" cy="4.5" r="2.5" fill="'+color+'"/></svg>';
        default: // pin
            return '<svg viewBox="0 0 24 36" width="24" height="36"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z" fill="'+color+'"/><circle cx="12" cy="12" r="5" fill="'+inner+'"/></svg>';
    }
}

// === 마커 드래그+클릭 인터랙션 ===
function setupMarkerInteraction(marker, area) {
    var isDragging = false, hasMoved = false;
    var startX, startY, origLeft, origTop;

    function startDrag(cx, cy, e) {
        e.preventDefault(); e.stopPropagation();
        isDragging = true; hasMoved = false;
        marker.style.zIndex = '20';
        startX = cx; startY = cy;
        origLeft = parseFloat(marker.style.left);
        origTop = parseFloat(marker.style.top);
    }
    function moveDrag(cx, cy) {
        if (!isDragging) return;
        var img = document.getElementById('map-editor-img');
        var rect = img.getBoundingClientRect();
        var dx = (cx - startX) / rect.width * 100;
        var dy = (cy - startY) / rect.height * 100;
        if (Math.abs(cx - startX) > 3 || Math.abs(cy - startY) > 3) hasMoved = true;
        marker.style.left = Math.max(0, Math.min(100, origLeft + dx)) + '%';
        marker.style.top = Math.max(0, Math.min(100, origTop + dy)) + '%';
    }
    function endDrag() {
        isDragging = false;
        marker.style.cursor = 'grab';
        marker.style.zIndex = '5';
        if (hasMoved) {
            var newX = parseFloat(marker.style.left);
            var newY = parseFloat(marker.style.top);
            var fd = new FormData();
            fd.append('action', 'set_coords');
            fd.append('ea_id', area.ea_id);
            fd.append('ea_map_x', newX.toFixed(2));
            fd.append('ea_map_y', newY.toFixed(2));
            fetch(UPDATE_URL, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) { area.ea_map_x = newX; area.ea_map_y = newY; }
                });
        } else {
            editArea(area.ea_id);
        }
    }

    marker.addEventListener('mousedown', function(e) {
        if (e.button !== 0) return;
        startDrag(e.clientX, e.clientY, e);
        marker.style.cursor = 'grabbing';
        function onMove(ev) { moveDrag(ev.clientX, ev.clientY); }
        function onUp() { document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); endDrag(); }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });
    marker.addEventListener('touchstart', function(e) { var t = e.touches[0]; startDrag(t.clientX, t.clientY, e); }, { passive: false });
    marker.addEventListener('touchmove', function(e) { if (!isDragging) return; e.preventDefault(); var t = e.touches[0]; moveDrag(t.clientX, t.clientY); }, { passive: false });
    marker.addEventListener('touchend', function() { if (!isDragging) return; endDrag(); });
}

// === 맵 에디터 초기화 ===
<?php if ($map_image) { ?>
var renderMapMarkers;
(function() {
    var mapEditor = document.getElementById('map-editor');
    var mapImg = document.getElementById('map-editor-img');
    var markersEl = document.getElementById('map-editor-markers');

    renderMapMarkers = function() {
        markersEl.innerHTML = '';
        areas.forEach(function(area) {
            if (area.ea_map_x == null || area.ea_map_y == null) return;

            var locked = area.ea_status === 'locked';
            var color = locked ? '#6b7280' : 'var(--mg-accent)';
            var inner = locked ? '#4b5563' : 'var(--mg-bg-primary)';

            var marker = document.createElement('div');
            marker.className = 'adm-map-marker';
            marker.style.left = area.ea_map_x + '%';
            marker.style.top = area.ea_map_y + '%';

            var sz = MARKER_STYLE === 'circle' ? 28 : 24;
            var szH = MARKER_STYLE === 'circle' ? 28 : (MARKER_STYLE === 'diamond' ? 32 : 36);
            marker.style.width = sz + 'px';
            marker.style.height = szH + 'px';
            marker.style.marginLeft = (-sz/2) + 'px';
            marker.style.marginTop = (-szH) + 'px';

            marker.innerHTML = getMarkerSVG(MARKER_STYLE, color, inner) +
                '<div class="marker-label">' + escHtml(area.ea_name) + '</div>';

            setupMarkerInteraction(marker, area);

            markersEl.appendChild(marker);
        });
    }

    // 맵 클릭 → 파견지 선택 팝업 표시
    mapImg.addEventListener('click', function(e) {
        var rect = mapImg.getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width * 100).toFixed(1);
        var y = ((e.clientY - rect.top) / rect.height * 100).toFixed(1);

        x = Math.max(0, Math.min(100, parseFloat(x)));
        y = Math.max(0, Math.min(100, parseFloat(y)));

        showPlacePopup(e.clientX, e.clientY, x, y);
    });

    renderMapMarkers();
})();
<?php } ?>

// === 드롭 테이블 ===
function addDropRow(data) {
    var container = document.getElementById('drop-table');
    var idx = container.children.length;
    var mt_options = '<option value="">재료 선택</option>';
    materialTypes.forEach(function(mt) {
        var selected = (data && data.mt_id == mt.mt_id) ? ' selected' : '';
        mt_options += '<option value="' + mt.mt_id + '"' + selected + '>' + mt.mt_name + '</option>';
    });

    var row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:6px;align-items:center;margin-bottom:6px;flex-wrap:wrap;';
    row.innerHTML =
        '<select name="drop_mt_id[]" class="mg-form-input" style="width:120px;">' + mt_options + '</select>' +
        '<input type="number" name="drop_min[]" class="mg-form-input" style="width:60px;" min="0" value="' + (data ? data.ed_min : 1) + '" placeholder="최소">' +
        '<span style="color:var(--mg-text-muted);">~</span>' +
        '<input type="number" name="drop_max[]" class="mg-form-input" style="width:60px;" min="0" value="' + (data ? data.ed_max : 1) + '" placeholder="최대">' +
        '<input type="number" name="drop_chance[]" class="mg-form-input" style="width:65px;" min="1" max="100" value="' + (data ? data.ed_chance : 100) + '" placeholder="%">' +
        '<span style="font-size:0.75rem;color:var(--mg-text-muted);">%</span>' +
        '<label style="font-size:0.8rem;display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="checkbox" name="drop_rare[' + idx + ']" value="1"' + (data && data.ed_is_rare == 1 ? ' checked' : '') + '> 레어</label>' +
        '<button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="this.parentElement.remove()" style="padding:2px 8px;">✕</button>';
    container.appendChild(row);
}

// === 이벤트 매칭 행 추가 ===
function addEventLinkRow(data) {
    var container = document.getElementById('event-link-table');
    if (!container) return;
    var ee_options = '<option value="">이벤트 선택</option>';
    var type_labels = { point_bonus:'포인트+', point_penalty:'포인트-', material_bonus:'재료+', material_penalty:'재료-', reward_loss:'보상감소' };
    expeditionEvents.forEach(function(ev) {
        var selected = (data && data.ee_id == ev.ee_id) ? ' selected' : '';
        var label = ev.ee_name + ' (' + (type_labels[ev.ee_effect_type] || ev.ee_effect_type) + ')';
        ee_options += '<option value="' + ev.ee_id + '"' + selected + '>' + escHtml(label) + '</option>';
    });

    var row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:6px;align-items:center;margin-bottom:6px;flex-wrap:wrap;';
    row.innerHTML =
        '<select name="evt_ee_id[]" class="mg-form-input" style="flex:1;min-width:150px;">' + ee_options + '</select>' +
        '<input type="number" name="evt_chance[]" class="mg-form-input" style="width:70px;" min="1" max="100" value="' + (data ? data.eea_chance : 10) + '" placeholder="%">' +
        '<span style="font-size:0.75rem;color:var(--mg-text-muted);">%</span>' +
        '<button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="this.parentElement.remove()" style="padding:2px 8px;">✕</button>';
    container.appendChild(row);
}

// === 이미지 미리보기 ===
function previewAreaImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('ea_image_img').src = e.target.result;
            document.getElementById('ea_image_preview').style.display = 'block';
            document.getElementById('ea_image_action').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeAreaImage() {
    document.getElementById('ea_image_preview').style.display = 'none';
    document.getElementById('ea_image_img').src = '';
    document.getElementById('ea_image_file').value = '';
    document.getElementById('ea_image_action').value = '__DELETE__';
}

// === 좌표 표시 ===
function setCoordDisplay(x, y) {
    var display = document.getElementById('coord-display');
    var text = document.getElementById('coord-text');
    if (x !== null && x !== '' && y !== null && y !== '') {
        text.textContent = 'X: ' + parseFloat(x).toFixed(1) + '%, Y: ' + parseFloat(y).toFixed(1) + '%';
        display.style.display = 'block';
    } else {
        display.style.display = 'none';
    }
}

function clearCoords() {
    document.getElementById('ea_map_x').value = '';
    document.getElementById('ea_map_y').value = '';
    document.getElementById('coord-display').style.display = 'none';
}

// === 모달 열기 (새 파견지, 맵 클릭 시 좌표 전달) ===
function openAreaModal(mapX, mapY) {
    document.getElementById('modal-title').textContent = '파견지 추가';
    document.getElementById('form_w').value = '';
    document.getElementById('form_ea_id').value = '';
    document.getElementById('area-form').reset();
    document.getElementById('drop-table').innerHTML = '';
    document.getElementById('ea_image_preview').style.display = 'none';
    document.getElementById('ea_image_img').src = '';
    document.getElementById('ea_image_action').value = '';
    mgIconReset('ea_icon');
    document.getElementById('ea_point_min').value = 0;
    document.getElementById('ea_point_max').value = 0;

    // 이벤트 매칭 초기화
    var evtContainer = document.getElementById('event-link-table');
    if (evtContainer) evtContainer.innerHTML = '';

    if (mapX !== undefined && mapY !== undefined) {
        document.getElementById('ea_map_x').value = mapX;
        document.getElementById('ea_map_y').value = mapY;
        setCoordDisplay(mapX, mapY);
    } else {
        document.getElementById('ea_map_x').value = '';
        document.getElementById('ea_map_y').value = '';
        setCoordDisplay(null, null);
    }

    document.getElementById('area-modal').style.display = 'flex';
}

// === 수정 ===
function editArea(ea_id) {
    var area = areas.find(function(a) { return a.ea_id == ea_id; });
    if (!area) return;

    document.getElementById('modal-title').textContent = '파견지 수정';
    document.getElementById('form_w').value = 'u';
    document.getElementById('form_ea_id').value = ea_id;
    document.getElementById('ea_name').value = area.ea_name;
    document.getElementById('ea_desc').value = area.ea_desc || '';
    mgIconSet('ea_icon', area.ea_icon || '');
    document.getElementById('ea_order').value = area.ea_order;
    document.getElementById('ea_stamina_cost').value = area.ea_stamina_cost;
    document.getElementById('ea_duration').value = area.ea_duration;
    document.getElementById('ea_point_min').value = area.ea_point_min || 0;
    document.getElementById('ea_point_max').value = area.ea_point_max || 0;
    document.getElementById('ea_partner_point').value = area.ea_partner_point;
    document.getElementById('ea_status').value = area.ea_status;
    document.getElementById('ea_unlock_facility').value = area.ea_unlock_facility || 0;

    // 이미지
    if (area.ea_image) {
        document.getElementById('ea_image_img').src = area.ea_image;
        document.getElementById('ea_image_preview').style.display = 'block';
    } else {
        document.getElementById('ea_image_preview').style.display = 'none';
        document.getElementById('ea_image_img').src = '';
    }
    document.getElementById('ea_image_file').value = '';
    document.getElementById('ea_image_action').value = '';

    // 좌표
    document.getElementById('ea_map_x').value = area.ea_map_x || '';
    document.getElementById('ea_map_y').value = area.ea_map_y || '';
    setCoordDisplay(area.ea_map_x, area.ea_map_y);

    // 드롭
    document.getElementById('drop-table').innerHTML = '';
    if (area.drops) {
        area.drops.forEach(function(drop) { addDropRow(drop); });
    }

    // 이벤트 매칭
    var evtContainer = document.getElementById('event-link-table');
    if (evtContainer) {
        evtContainer.innerHTML = '';
        if (area.event_links) {
            area.event_links.forEach(function(link) { addEventLinkRow(link); });
        }
    }

    document.getElementById('area-modal').style.display = 'flex';
}

// === 삭제 ===
function deleteArea(ea_id) {
    if (!confirm('이 파견지를 삭제하시겠습니까?\n관련 드롭 테이블도 함께 삭제됩니다.')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = UPDATE_URL;
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="ea_id" value="' + ea_id + '">';
    document.body.appendChild(form);
    form.submit();
}

// === UI 모드 전환 ===
function setUiMode(mode) {
    var fd = new FormData();
    fd.append('action', 'set_ui_mode');
    fd.append('mode', mode);

    fetch(UPDATE_URL, { method: 'POST', credentials: 'same-origin', body: fd }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            // 버튼 스타일 전환
            var btnList = document.getElementById('btn-mode-list');
            var btnMap = document.getElementById('btn-mode-map');
            var mapSection = document.getElementById('map-section');
            if (mode === 'map') {
                btnMap.style.background = 'var(--mg-accent)';
                btnMap.style.color = 'var(--mg-bg-primary)';
                btnMap.style.fontWeight = '600';
                btnList.style.background = 'var(--mg-bg-primary)';
                btnList.style.color = 'var(--mg-text-secondary)';
                btnList.style.fontWeight = '';
                if (mapSection) mapSection.style.display = 'block';
            } else {
                btnList.style.background = 'var(--mg-accent)';
                btnList.style.color = 'var(--mg-bg-primary)';
                btnList.style.fontWeight = '600';
                btnMap.style.background = 'var(--mg-bg-primary)';
                btnMap.style.color = 'var(--mg-text-secondary)';
                btnMap.style.fontWeight = '';
                if (mapSection) mapSection.style.display = 'none';
            }
        }
    });
}

// === 파견 지도 이미지 삭제 ===
function deleteMapImage() {
    if (!confirm('파견 지도 이미지를 삭제하시겠습니까?\n맵 모드가 비활성화됩니다.')) return;

    var fd = new FormData();
    fd.append('action', 'delete_map_image');

    fetch(UPDATE_URL, { method: 'POST', credentials: 'same-origin', body: fd }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '삭제 실패');
        }
    });
}

// === 모달 닫기 ===
function closeModal() {
    document.getElementById('area-modal').style.display = 'none';
}

document.getElementById('area-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeModal();
});

// === 맵 클릭 → 파견지 선택 팝업 ===
var _placePopupCoords = { x: 0, y: 0 };

function showPlacePopup(clientX, clientY, mapX, mapY) {
    _placePopupCoords = { x: mapX, y: mapY };

    var popup = document.getElementById('map-place-popup');
    var listEl = document.getElementById('map-place-list');

    // 좌표 미배치된 기존 파견지 목록
    var unplaced = areas.filter(function(a) {
        return a.ea_map_x == null || a.ea_map_y == null || a.ea_map_x === '' || a.ea_map_y === '';
    });

    listEl.innerHTML = '';
    if (unplaced.length === 0) {
        listEl.innerHTML = '<div class="place-empty">배치 가능한 파견지가 없습니다</div>';
    } else {
        unplaced.forEach(function(area) {
            var statusColors = { active: '#22c55e', hidden: '#6b7280', locked: '#ef4444' };
            var statusLabels = { active: '활성', hidden: '숨김', locked: '잠김' };
            var item = document.createElement('div');
            item.className = 'place-item';
            item.innerHTML = '<span style="flex:1;">' + escHtml(area.ea_name) + '</span>' +
                '<span class="place-status" style="background:' + (statusColors[area.ea_status] || '#6b7280') + '22; color:' + (statusColors[area.ea_status] || '#6b7280') + ';">' + (statusLabels[area.ea_status] || area.ea_status) + '</span>';
            item.onclick = function() { placeExistingArea(area.ea_id, mapX, mapY); };
            listEl.appendChild(item);
        });
    }

    // "새 파견지 추가" 버튼 핸들러
    document.getElementById('map-place-new-btn').onclick = function() {
        closePlacePopup();
        openAreaModal(mapX, mapY);
    };

    // 팝업 위치 (클릭 좌표 기준, 뷰포트 안에 들어오도록)
    popup.style.display = 'block';
    var pw = popup.offsetWidth;
    var ph = popup.offsetHeight;
    var left = clientX + 12;
    var top = clientY - 20;
    if (left + pw > window.innerWidth - 10) left = clientX - pw - 12;
    if (top + ph > window.innerHeight - 10) top = window.innerHeight - ph - 10;
    if (top < 10) top = 10;
    popup.style.left = left + 'px';
    popup.style.top = top + 'px';
}

function closePlacePopup() {
    document.getElementById('map-place-popup').style.display = 'none';
}

function placeExistingArea(ea_id, mapX, mapY) {
    closePlacePopup();

    var fd = new FormData();
    fd.append('action', 'set_coords');
    fd.append('ea_id', ea_id);
    fd.append('ea_map_x', mapX);
    fd.append('ea_map_y', mapY);

    fetch(UPDATE_URL, { method: 'POST', credentials: 'same-origin', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                // areas 배열 갱신
                var area = areas.find(function(a) { return a.ea_id == ea_id; });
                if (area) {
                    area.ea_map_x = mapX;
                    area.ea_map_y = mapY;
                }
                // 마커 다시 렌더링
                if (typeof renderMapMarkers === 'function') renderMapMarkers();
            } else {
                alert(data.message || '좌표 저장 실패');
            }
        })
        .catch(function() { alert('서버 요청 실패'); });
}

// 팝업 외부 클릭 시 닫기 (맵 이미지 클릭은 showPlacePopup이 갱신하므로 제외)
document.addEventListener('mousedown', function(e) {
    var popup = document.getElementById('map-place-popup');
    if (popup.style.display !== 'block') return;
    if (popup.contains(e.target)) return;
    var mapImg = document.getElementById('map-editor-img');
    if (mapImg && mapImg.contains(e.target)) return; // 맵 클릭은 새 팝업으로 대체됨
    closePlacePopup();
});

function escHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
