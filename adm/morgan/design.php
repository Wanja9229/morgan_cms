<?php
/**
 * Morgan Edition - 디자인 관리
 * Tab 1: 디자인 설정 (레이아웃, 색상, 배경 이미지)
 * Tab 2: 메인 페이지 빌더 (위젯 관리)
 */

$sub_menu = "800150";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 탭 라우팅
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
if (!in_array($tab, array('settings', 'builder'))) $tab = 'settings';

// 설정 로드 (디자인 설정 탭에서 필요)
$mg_configs = array();
$sql = "SELECT * FROM {$g5['mg_config_table']}";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $mg_configs[$row['cf_key']] = $row['cf_value'];
}

// 빌더 탭 데이터
if ($tab == 'builder') {
    include_once(MG_PLUGIN_PATH.'/widgets/widget.factory.php');

    $widgets = array();
    $sql = "SELECT * FROM {$g5['mg_main_widget_table']} WHERE widget_use = 1 ORDER BY widget_y ASC, widget_x ASC";
    $result = sql_query($sql);
    while ($widget = sql_fetch_array($result)) {
        $widget['widget_config'] = $widget['widget_config'] ? json_decode($widget['widget_config'], true) : array();
        $widgets[] = $widget;
    }

    $widget_types = mg_get_widget_types();
    $grid_columns = (int)mg_config('grid_columns', 12);
    if ($grid_columns < 1) $grid_columns = 12;
    $grid_rows = (int)mg_config('grid_rows', 40);
}

$g5['title'] = '디자인 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 탭 바 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=settings" class="mg-tab <?php echo $tab == 'settings' ? 'active' : ''; ?>">디자인 설정</a>
    <a href="?tab=builder" class="mg-tab <?php echo $tab == 'builder' ? 'active' : ''; ?>">메인 페이지 빌더</a>
</div>

<?php if ($tab == 'settings') { ?>
<!-- ======================================== -->
<!-- 디자인 설정 탭 -->
<!-- ======================================== -->
<form method="post" action="./config_update.php" enctype="multipart/form-data">
    <input type="hidden" name="_redirect" value="design.php?tab=settings">

    <div class="mg-card">
        <div class="mg-card-header"><h3>레이아웃</h3></div>
        <div class="mg-card-body">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="content_max_width">콘텐츠 최대 너비</label>
                    <input type="text" name="content_max_width" id="content_max_width" value="<?php echo isset($mg_configs['content_max_width']) ? htmlspecialchars($mg_configs['content_max_width']) : '72rem'; ?>" class="mg-form-input" placeholder="72rem">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">모든 페이지 콘텐츠 영역의 최대 너비 (예: 72rem, 1200px, 100%)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="site_font">사이트 폰트</label>
                    <?php
                    $current_font = isset($mg_configs['site_font']) ? $mg_configs['site_font'] : 'Noto Sans KR';
                    $google_kr_fonts = array(
                        // 본문용 (가독성 높음)
                        'Noto Sans KR'     => 'Noto Sans KR (본고딕)',
                        'Noto Serif KR'    => 'Noto Serif KR (본명조)',
                        'Nanum Gothic'     => '나눔고딕',
                        'Nanum Myeongjo'   => '나눔명조',
                        'Gothic A1'        => 'Gothic A1',
                        'IBM Plex Sans KR' => 'IBM Plex Sans KR',
                        'Pretendard Variable' => 'Pretendard (CDN)',
                        // 개성 있는 폰트
                        'Do Hyeon'         => '도현 (둥근 제목용)',
                        'Jua'              => '주아 (둥글둥글)',
                        'Sunflower'        => '해바라기 (가벼운)',
                        'Black Han Sans'   => 'Black Han Sans (굵은 제목용)',
                        'Gaegu'            => '개구 (손글씨)',
                        'Gamja Flower'     => '감자꽃 (손글씨)',
                        'Poor Story'       => 'Poor Story (만화체)',
                        'Stylish'          => 'Stylish (날씬한)',
                        'Song Myung'       => '송명 (명조)',
                        'Hi Melody'        => 'Hi Melody (손글씨)',
                        'Yeon Sung'        => '연성 (필기체)',
                        'East Sea Dokdo'   => '동해독도 (붓글씨)',
                        'Dokdo'            => '독도 (거친 붓)',
                        'Gugi'             => '구기 (기하학)',
                        'Kirang Haerang'   => '기랑해랑 (장식)',
                        'Single Day'       => 'Single Day (캐주얼)',
                        'Cute Font'        => 'Cute Font (귀여운)',
                    );
                    ?>
                    <select name="site_font" id="site_font" class="mg-form-select" onchange="previewFont(this.value)">
                        <?php foreach ($google_kr_fonts as $font_value => $font_label): ?>
                        <option value="<?php echo htmlspecialchars($font_value); ?>" <?php echo $current_font === $font_value ? 'selected' : ''; ?>><?php echo $font_label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">사이트 전체에 적용되는 폰트. Google Fonts 한국어 폰트 목록.</small>
                    <div id="fontPreview" style="margin-top:0.75rem;padding:1rem;background:var(--mg-bg-tertiary);border-radius:0.5rem;font-size:0.9rem;line-height:1.8;">
                        가나다라마바사 아자차카타파하<br>
                        The quick brown fox jumps over the lazy dog<br>
                        <span style="font-size:0.75rem;color:var(--mg-text-muted);">1234567890 !@#$%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header"><h3>색상</h3></div>
        <div class="mg-card-body">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_accent">메인 컬러 (Accent)</label>
                    <input type="color" name="color_accent" id="color_accent" value="<?php echo isset($mg_configs['color_accent']) ? $mg_configs['color_accent'] : '#f59f0a'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">강조 색상, 링크 등</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_button">버튼 배경색</label>
                    <input type="color" name="color_button" id="color_button" value="<?php echo isset($mg_configs['color_button']) ? $mg_configs['color_button'] : '#f59f0a'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">기본 버튼 배경색</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_button_text">버튼 글자색</label>
                    <input type="color" name="color_button_text" id="color_button_text" value="<?php echo isset($mg_configs['color_button_text']) ? $mg_configs['color_button_text'] : '#ffffff'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">버튼 내 텍스트 색상</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_border">Border / 입력필드 색상</label>
                    <input type="color" name="color_border" id="color_border" value="<?php echo isset($mg_configs['color_border']) ? $mg_configs['color_border'] : '#313338'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">테두리, 구분선, 입력필드 배경</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_text">글자 색상</label>
                    <input type="color" name="color_text" id="color_text" value="<?php echo isset($mg_configs['color_text']) ? $mg_configs['color_text'] : '#f2f3f5'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">기본 글자색 (보조/비활성 자동 파생)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_bg_primary">배경 색상 (Primary)</label>
                    <input type="color" name="color_bg_primary" id="color_bg_primary" value="<?php echo isset($mg_configs['color_bg_primary']) ? $mg_configs['color_bg_primary'] : '#1e1f22'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">메인 배경색</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_bg_secondary">배경 색상 (Secondary)</label>
                    <input type="color" name="color_bg_secondary" id="color_bg_secondary" value="<?php echo isset($mg_configs['color_bg_secondary']) ? $mg_configs['color_bg_secondary'] : '#2b2d31'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">카드, 섹션 배경색</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header"><h3>배경 이미지</h3></div>
        <div class="mg-card-body">
            <div class="mg-form-group" style="max-width:500px;">
                <label class="mg-form-label">배경 이미지</label>
                <input type="file" name="bg_image" id="bg_image" accept="image/*" class="mg-form-input" onchange="previewBgImage(this)">
                <input type="hidden" name="bg_image_url" id="bg_image_url" value="<?php echo isset($mg_configs['bg_image']) ? htmlspecialchars($mg_configs['bg_image']) : ''; ?>">
                <small style="color:var(--mg-text-muted);font-size:0.75rem;">메인 콘텐츠 영역 배경 이미지 (최대 10MB, jpg/png/gif/webp)</small>
                <div id="bg_image_preview" style="margin-top:0.75rem;">
                    <?php if (!empty($mg_configs['bg_image'])): ?>
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <img src="<?php echo htmlspecialchars($mg_configs['bg_image']); ?>" alt="배경 미리보기" style="max-width:200px;max-height:100px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">
                        <button type="button" class="mg-btn mg-btn-sm" style="background:var(--mg-error);color:#fff;" onclick="removeBgImage()">삭제</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mg-form-group" style="max-width:500px;margin-top:1rem;">
                <label class="mg-form-label" for="bg_opacity">배경 이미지 투명도</label>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <input type="range" name="bg_opacity" id="bg_opacity" min="0" max="100" value="<?php echo isset($mg_configs['bg_opacity']) ? $mg_configs['bg_opacity'] : '80'; ?>" style="flex:1;">
                    <span id="bg_opacity_value" style="min-width:40px;"><?php echo isset($mg_configs['bg_opacity']) ? $mg_configs['bg_opacity'] : '80'; ?>%</span>
                </div>
                <small style="color:var(--mg-text-muted);font-size:0.75rem;">0%: 원본 그대로 / 100%: 완전 투명</small>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:1rem;align-items:center;">
        <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
        <button type="button" class="mg-btn mg-btn-secondary" onclick="resetColors()">색상 초기화</button>
    </div>
</form>

<script>
document.getElementById('bg_opacity').addEventListener('input', function() {
    document.getElementById('bg_opacity_value').textContent = this.value + '%';
});

function previewBgImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('bg_image_preview').innerHTML =
                '<div style="display:flex;align-items:center;gap:1rem;">' +
                '<img src="' + e.target.result + '" alt="미리보기" style="max-width:200px;max-height:100px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">' +
                '<span style="color:var(--mg-accent);font-size:0.8rem;">새 이미지 선택됨</span>' +
                '</div>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeBgImage() {
    document.getElementById('bg_image_url').value = '__DELETE__';
    document.getElementById('bg_image').value = '';
    document.getElementById('bg_image_preview').innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.8rem;">이미지가 삭제됩니다 (저장 시 적용)</span>';
}

function previewFont(fontName) {
    var preview = document.getElementById('fontPreview');
    if (!preview) return;
    // Pretendard는 별도 CDN
    if (fontName === 'Pretendard Variable') {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css';
        document.head.appendChild(link);
    } else {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(fontName) + ':wght@300;400;500;700&display=swap';
        document.head.appendChild(link);
    }
    preview.style.fontFamily = "'" + fontName + "', sans-serif";
}

// 초기 미리보기 적용
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('site_font');
    if (sel) previewFont(sel.value);
});

function resetColors() {
    if (!confirm('모든 색상을 기본값으로 초기화하시겠습니까?')) return;
    document.getElementById('color_accent').value = '#f59f0a';
    document.getElementById('color_button').value = '#f59f0a';
    document.getElementById('color_button_text').value = '#ffffff';
    document.getElementById('color_border').value = '#313338';
    document.getElementById('color_text').value = '#f2f3f5';
    document.getElementById('color_bg_primary').value = '#1e1f22';
    document.getElementById('color_bg_secondary').value = '#2b2d31';
}
</script>

<?php } elseif ($tab == 'builder') { ?>
<!-- ======================================== -->
<!-- 메인 페이지 빌더 탭 -->
<!-- ======================================== -->

<!-- GridStack CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@12/dist/gridstack.min.css">
<script src="https://cdn.jsdelivr.net/npm/gridstack@12/dist/gridstack-all.js"></script>
<style>
.mg-builder-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.mg-builder-actions {
    display: flex;
    gap: 0.5rem;
}

/* GridStack 캔버스 커스텀 */
.grid-stack {
    --_col-w: calc(100% / var(--gs-columns, 12));
    --_row-h: var(--gs-cell-height, 50px);
    background-color: #0d0e10;
    background-image:
        repeating-linear-gradient(to right, transparent, transparent calc(var(--_col-w) - 1px), #3b3f48 calc(var(--_col-w) - 1px), #3b3f48 var(--_col-w)),
        repeating-linear-gradient(to bottom, transparent, transparent calc(var(--_row-h) - 1px), #3b3f48 calc(var(--_row-h) - 1px), #3b3f48 var(--_row-h));
    min-height: calc(var(--gs-cell-height, 80px) * var(--gs-rows, 40));
    border: 1px solid #4a4f58;
    border-radius: 0.5rem;
}
.grid-stack-item-content {
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-primary);
    border-radius: 0.5rem;
    padding: 0.5rem 0.5rem 0.5rem 0.75rem;
    overflow: hidden;
    position: relative;
}
.grid-stack-item-content:hover {
    border-color: var(--mg-accent);
}
.gs-item-header {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}
.gs-item-type {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--mg-accent);
}
.gs-item-size {
    font-size: 0.625rem;
    color: var(--mg-text-muted);
    background: var(--mg-bg-primary);
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
}
.gs-item-preview {
    font-size: 0.8rem;
    color: var(--mg-text-secondary);
    overflow: hidden;
    margin-top: 0.25rem;
}
.gs-item-preview img {
    max-width: 100%;
    max-height: 60px;
    border-radius: 4px;
    object-fit: cover;
    border: 1px solid var(--mg-bg-tertiary);
}
.gs-item-footer {
    position: absolute;
    top: 0.375rem;
    right: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.gs-item-actions {
    display: flex;
    gap: 0.25rem;
}
.mg-widget-btn {
    background: var(--mg-bg-tertiary);
    border: none;
    color: var(--mg-text-muted);
    cursor: pointer;
    padding: 0.375rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    transition: all 0.2s;
}
.mg-widget-btn:hover {
    background: var(--mg-bg-primary);
    color: var(--mg-text-primary);
}
.mg-widget-btn.danger:hover {
    background: var(--mg-error);
    color: #fff;
}

/* 빈 상태 */
.mg-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: var(--mg-text-muted);
}

/* Builder Modal (unique prefix to avoid conflict with _head.php) */
.mgb-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s;
}
.mgb-modal-overlay.show { opacity: 1; visibility: visible; }
.mgb-modal {
    background: var(--mg-bg-secondary);
    border-radius: 0.5rem;
    width: 100%; max-width: 500px; max-height: 80vh;
    overflow: hidden;
    transform: scale(0.9);
    transition: transform 0.2s;
    border: 1px solid var(--mg-bg-tertiary);
}
.mgb-modal-overlay.show .mgb-modal { transform: scale(1); }
.mgb-modal.lg { max-width: 700px; }
.mgb-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}
.mgb-modal-title { font-weight: 600; }
.mgb-modal-close {
    background: none; border: none;
    color: var(--mg-text-muted); cursor: pointer;
    padding: 0.25rem; font-size: 1.5rem; line-height: 1;
}
.mgb-modal-close:hover { color: var(--mg-text-primary); }
.mgb-modal-body { padding: 1.25rem; max-height: 60vh; overflow-y: auto; }
.mgb-modal-footer {
    display: flex; justify-content: flex-end; gap: 0.5rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--mg-bg-tertiary);
}

/* Widget Type Grid */
.mg-type-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
.mg-type-item {
    background: var(--mg-bg-tertiary);
    border: 2px solid transparent;
    border-radius: 0.5rem;
    padding: 1rem; text-align: center;
    cursor: pointer; transition: all 0.2s;
}
.mg-type-item:hover { border-color: var(--mg-accent); }
.mg-type-item.selected {
    border-color: var(--mg-accent);
    background: rgba(245, 159, 10, 0.1);
}
.mg-type-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
.mg-type-name { font-size: 0.875rem; font-weight: 500; }
.mg-type-desc { font-size: 0.7rem; color: var(--mg-text-muted); margin-top: 0.25rem; }

/* ===== Mobile Responsive ===== */
@media (max-width: 640px) {
    .mg-builder-header { flex-direction: column; align-items: stretch; gap: 0.75rem; }
    .mg-builder-actions { display: flex; width: 100%; }
    .mg-type-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="mg-card">
    <div class="mg-card-header">
        <div class="mg-builder-header" style="margin-bottom:0;">
            <span>메인 페이지 빌더</span>
            <div class="mg-builder-actions">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="openAddModal()">+ 위젯 추가</button>
                <button type="button" class="mg-btn mg-btn-primary" onclick="saveLayout()">레이아웃 저장</button>
            </div>
        </div>
    </div>
    <div class="mg-card-body">
        <div id="builder-mobile-notice" style="display:none;padding:1rem;margin-bottom:1rem;background:var(--mg-bg-tertiary);border-radius:0.5rem;border-left:3px solid var(--mg-accent);">
            <strong style="font-size:0.85rem;color:var(--mg-text-primary);">PC 환경 권장</strong>
            <p style="font-size:0.8rem;color:var(--mg-text-muted);margin-top:0.25rem;">위젯 드래그 배치는 PC에서 최적화되어 있습니다. 모바일에서는 일부 기능이 제한될 수 있습니다.</p>
        </div>
        <script>if(window.innerWidth<1024)document.getElementById('builder-mobile-notice').style.display='block';</script>
        <!-- 그리드 설정 -->
        <div style="display:flex;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;align-items:stretch;">
            <div style="background:var(--mg-bg-tertiary);padding:0.75rem 1rem;border-radius:0.5rem;width:100%;">
                <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-bottom:0.5rem;">그리드 설정</div>
                <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <label style="font-size:0.8rem;color:var(--mg-text-secondary);white-space:nowrap;">가로 칸 수</label>
                        <select id="gridColumnsInput" class="mg-form-select" style="width:auto;padding:0.375rem 0.5rem;">
                            <?php foreach (array(12, 16, 24, 32, 48) as $col_opt): ?>
                            <option value="<?php echo $col_opt; ?>" <?php echo $grid_columns == $col_opt ? 'selected' : ''; ?>><?php echo $col_opt; ?>칸</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <label style="font-size:0.8rem;color:var(--mg-text-secondary);white-space:nowrap;">세로 칸 수</label>
                        <input type="number" id="gridRowsInput" value="<?php echo $grid_rows; ?>" min="4" max="100" step="1" class="mg-form-input" style="width:70px;padding:0.375rem 0.5rem;">
                    </div>
                    <button type="button" class="mg-btn mg-btn-sm mg-btn-primary" onclick="saveGridSettings()">적용</button>
                </div>
                <div style="font-size:0.7rem;color:var(--mg-text-muted);margin-top:0.5rem;">
                    가로 칸 수를 늘리면 한 칸의 크기가 작아져 더 세밀한 배치가 가능합니다. 칸 수 변경 시 기존 위젯 위치가 자동 변환됩니다.
                </div>
            </div>
        </div>

        <!-- GridStack 캔버스 -->
        <?php if (empty($widgets)): ?>
        <div class="mg-empty-state" id="emptyState">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:1rem;opacity:0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <p>위젯이 없습니다. '위젯 추가' 버튼을 클릭하세요.</p>
        </div>
        <?php endif; ?>
        <div class="grid-stack" id="gridCanvas" style="max-width:600px;margin:0 auto;--gs-cell-height:50px;--gs-columns:<?php echo $grid_columns; ?>;--gs-rows:<?php echo $grid_rows; ?>;">
        </div>
    </div>
</div>

<!-- 위젯 추가 모달 -->
<div class="mgb-modal-overlay" id="addModal">
    <div class="mgb-modal">
        <div class="mgb-modal-header">
            <span class="mgb-modal-title">위젯 추가</span>
            <button type="button" class="mgb-modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="mgb-modal-body">
            <div class="mg-form-group">
                <label class="mg-form-label">위젯 타입 선택</label>
                <div class="mg-type-grid">
                    <?php
                    $type_icons = array('text' => 'document-text', 'image' => 'photo', 'link_button' => 'link', 'latest' => 'queue-list', 'notice' => 'bell', 'slider' => 'squares-2x2', 'editor' => 'pencil-square');
                    foreach ($widget_types as $type => $info):
                        $icon_name = isset($type_icons[$type]) ? $type_icons[$type] : 'cube';
                    ?>
                    <div class="mg-type-item" data-type="<?php echo $type; ?>" onclick="selectType('<?php echo $type; ?>')">
                        <div class="mg-type-icon">
                            <?php echo mg_icon($icon_name, 'w-6 h-6'); ?>
                        </div>
                        <div class="mg-type-name"><?php echo $info['name']; ?></div>
                        <div class="mg-type-desc"><?php echo $info['desc']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="display:flex;gap:1rem;margin-top:1rem;">
                <div class="mg-form-group" style="flex:1;">
                    <label class="mg-form-label">가로 칸 수 (W) <small style="color:var(--mg-text-muted);">/ <?php echo $grid_columns; ?>칸</small></label>
                    <input type="number" id="addWidgetW" class="mg-form-input" value="<?php echo (int)($grid_columns / 2); ?>" min="1" max="<?php echo $grid_columns; ?>" style="padding:0.375rem 0.5rem;">
                </div>
                <div class="mg-form-group" style="flex:1;">
                    <label class="mg-form-label">세로 칸 수 (H)</label>
                    <input type="number" id="addWidgetH" class="mg-form-input" value="2" min="1" max="40" style="padding:0.375rem 0.5rem;">
                </div>
            </div>
        </div>
        <div class="mgb-modal-footer">
            <button type="button" class="mg-btn mg-btn-secondary" onclick="closeAddModal()">취소</button>
            <button type="button" class="mg-btn mg-btn-primary" onclick="addWidget()">추가</button>
        </div>
    </div>
</div>

<!-- 위젯 설정 모달 -->
<div class="mgb-modal-overlay" id="editModal">
    <div class="mgb-modal lg">
        <div class="mgb-modal-header">
            <span class="mgb-modal-title">위젯 설정</span>
            <button type="button" class="mgb-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="widgetConfigForm" onsubmit="saveWidgetConfig(event)">
            <div class="mgb-modal-body" id="editModalBody">
                <!-- AJAX로 로드 -->
            </div>
            <div class="mgb-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeEditModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
var grid = null;
var selectedType = null;
var currentWidgetId = null;

// ====================================
// GridStack 초기화
// ====================================
<?php
// PHP에서 위젯 데이터를 JSON으로 출력
$type_icons_map = array('text' => 'document-text', 'image' => 'photo', 'link_button' => 'link', 'latest' => 'queue-list', 'notice' => 'bell', 'slider' => 'squares-2x2', 'editor' => 'pencil-square');
$widgets_json = array();
foreach ($widgets as $w) {
    $cfg = $w['widget_config'] ?: array();
    $type_name = isset($widget_types[$w['widget_type']]) ? $widget_types[$w['widget_type']]['name'] : $w['widget_type'];

    // 미리보기 텍스트
    $preview = '';
    switch ($w['widget_type']) {
        case 'text':
            $preview = strip_tags($cfg['content'] ?? '') ?: '(내용 없음)';
            break;
        case 'image':
            if (!empty($cfg['image_url'])) $preview = '<img src="' . htmlspecialchars($cfg['image_url']) . '" alt="미리보기">';
            else $preview = '(이미지 없음)';
            break;
        case 'link_button':
            if (!empty($cfg['content_type']) && $cfg['content_type'] == 'image' && !empty($cfg['image_url'])) {
                $preview = '<img src="' . htmlspecialchars($cfg['image_url']) . '" alt="미리보기" style="max-height:40px;">';
            } else {
                $preview = htmlspecialchars(($cfg['text'] ?? '버튼') . ' → ' . ($cfg['link_url'] ?? '#'));
            }
            break;
        case 'latest': case 'notice':
            $preview = htmlspecialchars($cfg['title'] ?? (!empty($cfg['bo_table']) ? $cfg['bo_table'] : '(게시판 미선택)'));
            break;
        case 'slider':
            $slides = $cfg['slides'] ?? array();
            if (is_string($slides)) $slides = json_decode($slides, true) ?: array();
            if (!empty($slides) && !empty($slides[0]['image'])) {
                $preview = '<img src="' . htmlspecialchars($slides[0]['image']) . '" alt="" style="max-height:40px;"> ' . count($slides) . '개 슬라이드';
            } else {
                $preview = count($slides) . '개 슬라이드';
            }
            break;
        case 'editor':
            $preview = htmlspecialchars($cfg['title'] ?? strip_tags($cfg['content'] ?? '') ?: '(내용 없음)');
            break;
        default:
            $preview = '(설정 필요)';
    }
    $preview = mb_substr($preview, 0, 80);

    $widgets_json[] = array(
        'id' => (int)$w['widget_id'],
        'x'  => (int)($w['widget_x'] ?? 0),
        'y'  => (int)($w['widget_y'] ?? 0),
        'w'  => (int)($w['widget_w'] ?: ($w['widget_cols'] ?? 6)),
        'h'  => (int)($w['widget_h'] ?: 2),
        'type' => $w['widget_type'],
        'type_name' => $type_name,
        'preview' => $preview,
    );
}
?>

var widgetData = <?php echo json_encode($widgets_json, JSON_UNESCAPED_UNICODE); ?>;

function buildWidgetContent(w) {
    return '<div class="gs-item-header">'
        + '<span class="gs-item-type">' + w.type_name + '</span>'
        + '</div>'
        + '<div class="gs-item-preview">' + w.preview + '</div>'
        + '<div class="gs-item-footer">'
        + '<span class="gs-item-size">' + w.w + '×' + w.h + '</span>'
        + '<div class="gs-item-actions">'
        + '<button type="button" class="mg-widget-btn" onclick="editWidget(' + w.id + ')">설정</button>'
        + '<button type="button" class="mg-widget-btn danger" onclick="deleteWidget(' + w.id + ')">삭제</button>'
        + '</div>'
        + '</div>';
}

// 관리자 빌더: 정사각형 셀 (셀 높이 = 셀 너비 = 캔버스/columns)
var currentGridColumns = <?php echo $grid_columns; ?>;
var currentGridRows = <?php echo $grid_rows; ?>;

function calcSquareCellHeight() {
    var canvas = document.getElementById('gridCanvas');
    if (!canvas) return 50;
    return Math.floor(canvas.clientWidth / currentGridColumns);
}

function updateCanvasSize(cellH) {
    var canvas = document.getElementById('gridCanvas');
    canvas.style.setProperty('--gs-cell-height', cellH + 'px');
    canvas.style.minHeight = (cellH * currentGridRows) + 'px';
}

document.addEventListener('DOMContentLoaded', function() {
    var squareH = calcSquareCellHeight();

    grid = GridStack.init({
        column: currentGridColumns,
        maxRow: currentGridRows,
        cellHeight: squareH,
        disableOneColumnMode: true,
        animate: true,
        removable: false,
        margin: 4
    }, '#gridCanvas');

    grid.float(true);
    updateCanvasSize(squareH);

    // 리사이즈 시 정사각형 유지
    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            var newH = calcSquareCellHeight();
            grid.cellHeight(newH);
            updateCanvasSize(newH);
        }, 200);
    });

    // 위젯 로드 (content 없이 추가 후 innerHTML 수동 설정)
    widgetData.forEach(function(w) {
        grid.addWidget({
            x: w.x, y: w.y, w: w.w, h: w.h,
            id: String(w.id)
        });
    });

    // addWidget 후 content를 innerHTML로 직접 설정
    grid.getGridItems().forEach(function(el) {
        var node = el.gridstackNode;
        if (!node) return;
        var w = widgetData.find(function(d) { return String(d.id) === String(node.id); });
        if (w) {
            var contentEl = el.querySelector('.grid-stack-item-content');
            if (contentEl) contentEl.innerHTML = buildWidgetContent(w);
        }
    });

    // 리사이즈/이동 시 사이즈 라벨 업데이트
    grid.on('resizestop dragstop', function(event, el) {
        var node = el.gridstackNode;
        var sizeEl = el.querySelector('.gs-item-size');
        if (sizeEl && node) {
            sizeEl.textContent = node.w + '×' + node.h;
        }
    });
});

// ====================================
// 레이아웃 저장 (GridStack 2D 좌표)
// ====================================
function saveLayout() {
    var items = grid.getGridItems().map(function(el) {
        var node = el.gridstackNode;
        return { id: parseInt(node.id), x: node.x, y: node.y, w: node.w, h: node.h };
    });

    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'save_layout', widgets: items})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) alert('레이아웃이 저장되었습니다.');
        else alert(data.message || '저장 실패');
    })
    .catch(err => alert('저장 중 오류가 발생했습니다.'));
}

// ====================================
// 그리드 설정 저장
// ====================================
function saveGridSettings() {
    var gridColumns = parseInt(document.getElementById('gridColumnsInput').value, 10);
    var gridRows = parseInt(document.getElementById('gridRowsInput').value, 10);

    if (isNaN(gridColumns) || gridColumns < 12 || gridColumns > 48) {
        alert('가로 칸 수는 12~48 사이로 입력해주세요.');
        return;
    }
    if (isNaN(gridRows) || gridRows < 4 || gridRows > 100) {
        alert('세로 칸 수는 4~100 사이로 입력해주세요.');
        return;
    }

    // 칸 수 변경 시 위젯 좌표도 서버에서 자동 변환됨
    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'save_grid_settings', grid_columns: gridColumns, grid_rows: gridRows})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('그리드 설정이 저장되었습니다. 페이지를 새로고침합니다.');
            location.reload();
        } else {
            alert(data.message || '저장 실패');
        }
    })
    .catch(err => alert('저장 중 오류가 발생했습니다.'));
}

// ====================================
// 타입 선택
// ====================================
function selectType(type) {
    selectedType = type;
    document.querySelectorAll('.mg-type-item').forEach(function(el) {
        el.classList.toggle('selected', el.dataset.type === type);
    });
}

// ====================================
// 모달 열기/닫기
// ====================================
function openAddModal() {
    selectedType = null;
    document.querySelectorAll('.mg-type-item').forEach(el => el.classList.remove('selected'));
    document.getElementById('addModal').classList.add('show');
}
function closeAddModal() { document.getElementById('addModal').classList.remove('show'); }
function closeEditModal() { document.getElementById('editModal').classList.remove('show'); currentWidgetId = null; }

// ====================================
// 위젯 추가 (w + h)
// ====================================
function addWidget() {
    if (!selectedType) {
        alert('위젯 타입을 선택하세요.');
        return;
    }

    var w = parseInt(document.getElementById('addWidgetW').value, 10);
    var h = parseInt(document.getElementById('addWidgetH').value, 10);

    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add_widget&widget_type=' + selectedType + '&widget_w=' + w + '&widget_h=' + h
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    });
}

// ====================================
// 위젯 설정 (AJAX 모달)
// ====================================
function editWidget(widgetId) {
    currentWidgetId = widgetId;
    document.getElementById('editModalBody').innerHTML = '<p style="text-align:center;color:var(--mg-text-muted);">로딩 중...</p>';
    document.getElementById('editModal').classList.add('show');

    fetch('./main_widget_config.php?widget_id=' + widgetId)
    .then(r => r.text())
    .then(html => {
        var container = document.getElementById('editModalBody');
        container.innerHTML = html;
        var scripts = container.querySelectorAll('script');
        scripts.forEach(function(script) {
            var newScript = document.createElement('script');
            newScript.textContent = script.textContent;
            document.body.appendChild(newScript);
            document.body.removeChild(newScript);
        });
    });
}

function saveWidgetConfig(e) {
    e.preventDefault();
    var formData = new FormData(document.getElementById('widgetConfigForm'));
    formData.append('action', 'update_widget_config');
    formData.append('widget_id', currentWidgetId);

    fetch('./main_builder_update.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || '오류가 발생했습니다.');
    });
}

// ====================================
// 위젯 삭제
// ====================================
function deleteWidget(widgetId) {
    if (!confirm('이 위젯을 삭제하시겠습니까?')) return;

    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_widget', widget_id: widgetId})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // GridStack에서도 제거
            var el = grid.getGridItems().find(function(item) {
                return item.gridstackNode && item.gridstackNode.id == widgetId;
            });
            if (el) grid.removeWidget(el);
            else location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    });
}

// ESC로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeAddModal(); closeEditModal(); }
});

// ====================================
// 위젯 이미지 업로드 함수들 (전역)
// ====================================
function uploadWidgetImage() {
    var fileInput = document.getElementById('image_file_upload');
    var status = document.getElementById('image_upload_status');
    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
        if (status) status.innerHTML = '<span style="color:var(--mg-error);">파일을 선택해주세요.</span>';
        return;
    }
    var formData = new FormData();
    formData.append('image', fileInput.files[0]);
    if (status) status.innerHTML = '<span style="color:var(--mg-accent);">업로드 중...</span>';
    fetch('./main_widget_upload.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(text => {
        try {
            var data = JSON.parse(text);
            if (data.success) {
                var urlInput = document.getElementById('widget_image_url');
                if (urlInput) urlInput.value = data.url;
                var preview = document.getElementById('image_preview');
                if (preview) preview.innerHTML = '<img src="' + data.url + '" alt="미리보기" style="max-width:100%;max-height:200px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">';
                if (status) status.innerHTML = '<span style="color:var(--mg-success);">업로드 완료!</span>';
            } else {
                if (status) status.innerHTML = '<span style="color:var(--mg-error);">' + (data.message || '업로드 실패') + '</span>';
            }
        } catch(e) { if (status) status.innerHTML = '<span style="color:var(--mg-error);">서버 응답 오류</span>'; }
    })
    .catch(err => { if (status) status.innerHTML = '<span style="color:var(--mg-error);">업로드 오류: ' + err.message + '</span>'; });
}

function uploadLinkImage() {
    var fileInput = document.getElementById('link_image_file_upload');
    var status = document.getElementById('link_image_upload_status');
    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
        if (status) status.innerHTML = '<span style="color:var(--mg-error);">파일을 선택해주세요.</span>';
        return;
    }
    var formData = new FormData();
    formData.append('image', fileInput.files[0]);
    if (status) status.innerHTML = '<span style="color:var(--mg-accent);">업로드 중...</span>';
    fetch('./main_widget_upload.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(text => {
        try {
            var data = JSON.parse(text);
            if (data.success) {
                var urlInput = document.getElementById('link_widget_image_url');
                if (urlInput) urlInput.value = data.url;
                var preview = document.getElementById('link_image_preview');
                if (preview) preview.innerHTML = '<img src="' + data.url + '" alt="미리보기" style="max-width:100%;max-height:150px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">';
                if (status) status.innerHTML = '<span style="color:var(--mg-success);">업로드 완료!</span>';
            } else {
                if (status) status.innerHTML = '<span style="color:var(--mg-error);">' + (data.message || '업로드 실패') + '</span>';
            }
        } catch(e) { if (status) status.innerHTML = '<span style="color:var(--mg-error);">서버 응답 오류</span>'; }
    })
    .catch(err => { if (status) status.innerHTML = '<span style="color:var(--mg-error);">업로드 오류</span>'; });
}

function uploadSlideImage(input, idx) {
    if (!input.files || !input.files[0]) return;
    var formData = new FormData();
    formData.append('image', input.files[0]);
    var slideItem = input.closest('.slider-slide-item');
    var statusEl = slideItem ? slideItem.querySelector('.slide-upload-status') : null;
    if (!statusEl && slideItem) {
        statusEl = document.createElement('div');
        statusEl.className = 'slide-upload-status';
        statusEl.style.cssText = 'font-size:0.8rem;margin-top:0.25rem;';
        input.parentNode.appendChild(statusEl);
    }
    if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-accent);">업로드 중...</span>';
    fetch('./main_widget_upload.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(text => {
        try {
            var data = JSON.parse(text);
            if (data.success) {
                var urlInput = document.getElementById('slide_image_' + idx);
                if (urlInput) urlInput.value = data.url;
                if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-success);">업로드 완료!</span>';
            } else {
                if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-error);">' + (data.message || '업로드 실패') + '</span>';
            }
        } catch(e) { if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-error);">서버 응답 오류</span>'; }
    })
    .catch(err => { if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-error);">업로드 오류</span>'; });
}

function toggleLinkContentType() {
    var type = document.getElementById('link_content_type');
    if (!type) return;
    var textFields = document.getElementById('link_text_fields');
    var imageFields = document.getElementById('link_image_fields');
    if (textFields) textFields.style.display = type.value === 'text' ? '' : 'none';
    if (imageFields) imageFields.style.display = type.value === 'image' ? '' : 'none';
}
</script>

<?php } ?>

<?php
require_once __DIR__.'/_tail.php';
?>
