<?php
/**
 * Morgan Edition - Credits (크레딧)
 */

include_once('./_common.php');

$g5['title'] = '크레딧';
include_once(G5_THEME_PATH.'/head.php');

$site_name = function_exists('mg_config') ? mg_config('site_name', 'Morgan Edition') : 'Morgan Edition';
?>

<div id="ajax-content">
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-mg-text-primary mb-2">Credits</h1>
    <p class="text-sm text-mg-text-muted mb-8"><?php echo htmlspecialchars($site_name); ?>은(는) 아래의 오픈소스 프로젝트와 에셋에 의해 만들어졌습니다.</p>

    <!-- Icons & Assets -->
    <section class="mb-8">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3 flex items-center gap-2">
            <i data-lucide="palette" class="w-5 h-5 text-mg-accent"></i>
            아이콘 & 에셋
        </h2>
        <div class="space-y-3">
            <div class="bg-mg-bg-secondary rounded-lg p-4 border border-mg-bg-tertiary">
                <div class="flex items-center justify-between mb-1">
                    <a href="https://lucide.dev/" target="_blank" rel="noopener" class="font-medium text-mg-text-primary hover:text-mg-accent transition-colors">Lucide Icons</a>
                    <span class="text-xs px-2 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted">ISC License</span>
                </div>
                <p class="text-sm text-mg-text-secondary">UI 전반에 사용되는 아이콘 라이브러리</p>
            </div>
            <div class="bg-mg-bg-secondary rounded-lg p-4 border border-mg-bg-tertiary">
                <div class="flex items-center justify-between mb-1">
                    <a href="https://game-icons.net/" target="_blank" rel="noopener" class="font-medium text-mg-text-primary hover:text-mg-accent transition-colors">Game-icons.net</a>
                    <span class="text-xs px-2 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted">CC BY 3.0</span>
                </div>
                <p class="text-sm text-mg-text-secondary">전투 스킬 아이콘 — Authors: Lorc, Delapouite, et al.</p>
            </div>
        </div>
    </section>

    <!-- Fonts -->
    <section class="mb-8">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3 flex items-center gap-2">
            <i data-lucide="type" class="w-5 h-5 text-mg-accent"></i>
            폰트
        </h2>
        <div class="space-y-3">
            <div class="bg-mg-bg-secondary rounded-lg p-4 border border-mg-bg-tertiary">
                <div class="flex items-center justify-between mb-1">
                    <a href="https://github.com/orioncactus/pretendard" target="_blank" rel="noopener" class="font-medium text-mg-text-primary hover:text-mg-accent transition-colors">Pretendard</a>
                    <span class="text-xs px-2 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted">OFL 1.1</span>
                </div>
                <p class="text-sm text-mg-text-secondary">기본 UI 폰트</p>
            </div>
            <div class="bg-mg-bg-secondary rounded-lg p-4 border border-mg-bg-tertiary">
                <div class="flex items-center justify-between mb-1">
                    <a href="https://github.com/neodgm/neodgm" target="_blank" rel="noopener" class="font-medium text-mg-text-primary hover:text-mg-accent transition-colors">NeoDGM</a>
                    <span class="text-xs px-2 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted">OFL 1.1</span>
                </div>
                <p class="text-sm text-mg-text-secondary">레트로 프로필 스킨 (Win98, DOS, JRPG)</p>
            </div>
        </div>
    </section>

    <!-- JavaScript Libraries -->
    <section class="mb-8">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3 flex items-center gap-2">
            <i data-lucide="code-2" class="w-5 h-5 text-mg-accent"></i>
            라이브러리
        </h2>
        <div class="space-y-3">
            <?php
            $libs = array(
                array('name' => 'Tailwind CSS', 'url' => 'https://tailwindcss.com/', 'license' => 'MIT', 'desc' => '유틸리티 기반 CSS 프레임워크'),
                array('name' => 'SortableJS', 'url' => 'https://sortablejs.github.io/Sortable/', 'license' => 'MIT', 'desc' => '드래그 앤 드롭 정렬'),
                array('name' => 'GridStack.js', 'url' => 'https://gridstackjs.com/', 'license' => 'MIT', 'desc' => '인장(시그니처 카드) 그리드 빌더'),
                array('name' => 'vis-network', 'url' => 'https://visjs.github.io/vis-network/', 'license' => 'MIT / Apache 2.0', 'desc' => '캐릭터 관계도 시각화'),
                array('name' => 'Toast UI Editor', 'url' => 'https://ui.toast.com/tui-editor', 'license' => 'MIT', 'desc' => '마크다운 에디터'),
                array('name' => 'Vanta.js', 'url' => 'https://www.vantajs.com/', 'license' => 'MIT', 'desc' => '프로필 배경 이펙트'),
                array('name' => 'Three.js', 'url' => 'https://threejs.org/', 'license' => 'MIT', 'desc' => '3D 렌더링 (Vanta.js 의존성)'),
                array('name' => 'p5.js', 'url' => 'https://p5js.org/', 'license' => 'LGPL 2.1', 'desc' => '2D 프로필 배경 이펙트'),
            );
            foreach ($libs as $lib) {
            ?>
            <div class="bg-mg-bg-secondary rounded-lg p-4 border border-mg-bg-tertiary">
                <div class="flex items-center justify-between mb-1">
                    <a href="<?php echo $lib['url']; ?>" target="_blank" rel="noopener" class="font-medium text-mg-text-primary hover:text-mg-accent transition-colors"><?php echo $lib['name']; ?></a>
                    <span class="text-xs px-2 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted"><?php echo $lib['license']; ?></span>
                </div>
                <p class="text-sm text-mg-text-secondary"><?php echo $lib['desc']; ?></p>
            </div>
            <?php } ?>
        </div>
    </section>

    <!-- Base Platform -->
    <section class="mb-8">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3 flex items-center gap-2">
            <i data-lucide="box" class="w-5 h-5 text-mg-accent"></i>
            기반 플랫폼
        </h2>
        <div class="bg-mg-bg-secondary rounded-lg p-4 border border-mg-bg-tertiary">
            <div class="flex items-center justify-between mb-1">
                <a href="https://sir.kr/" target="_blank" rel="noopener" class="font-medium text-mg-text-primary hover:text-mg-accent transition-colors">그누보드5 (GNUBOARD5)</a>
                <span class="text-xs px-2 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted">LGPL 2.1</span>
            </div>
            <p class="text-sm text-mg-text-secondary">PHP 기반 커뮤니티 CMS</p>
        </div>
    </section>

    <!-- Built with -->
    <div class="text-center text-sm text-mg-text-muted pt-4 border-t border-mg-bg-tertiary">
        Built with Morgan Edition CMS
    </div>
</div>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
