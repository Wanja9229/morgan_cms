<?php
/**
 * Morgan Edition - Tail (Footer)
 *
 * 푸터 영역 및 HTML 종료
 */

if (!defined('_GNUBOARD_')) exit;

// SPA-like: AJAX 요청이면 레이아웃 건너뛰기
if (isset($is_ajax_request) && $is_ajax_request) {
    echo '</div>'; // #ajax-content 닫기
    return;
}
?>
    </main>
    <!-- End Main Content -->

    <!-- Right Sidebar (Member Widget) -->
    <aside id="widget-sidebar" class="hidden lg:block w-72 bg-mg-bg-secondary fixed right-0 top-12 bottom-0 p-4 border-l border-mg-bg-tertiary overflow-y-auto">

        <?php if ($is_member) { ?>
        <!-- 로그인 상태: 회원 정보 + 대표 캐릭터 통합 -->
        <?php
        $_show_main_char = function_exists('mg_config') ? mg_config('show_main_character', '1') : '1';
        $main_char = null;
        if ($_show_main_char == '1' && function_exists('mg_get_main_character')) {
            $main_char = mg_get_main_character($member['mb_id']);
        }
        ?>
        <div class="card mb-4">
            <div class="flex items-center gap-3 mb-3">
                <?php if ($main_char && !empty($main_char['ch_thumb'])) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $main_char['ch_id']; ?>" class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-mg-bg-tertiary overflow-hidden ring-2 ring-mg-accent/30 hover:ring-mg-accent transition-all">
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$main_char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                    </div>
                </a>
                <?php } else { ?>
                <div class="w-12 h-12 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-accent font-bold text-lg flex-shrink-0">
                    <?php echo mb_substr($member['mb_nick'], 0, 1); ?>
                </div>
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-mg-text-primary truncate"><?php echo get_text($member['mb_nick']); ?></p>
                    <?php if ($main_char) { ?>
                    <p class="text-xs text-mg-text-muted truncate"><?php echo htmlspecialchars($main_char['ch_name']); ?><?php if (!empty($main_char['ch_side'])) echo ' · '.htmlspecialchars($main_char['ch_side']); ?></p>
                    <?php } else { ?>
                    <p class="text-xs text-mg-text-muted">대표 캐릭터 없음</p>
                    <?php } ?>
                </div>
                <a href="<?php echo G5_BBS_URL; ?>/character.php" class="flex-shrink-0 p-1.5 rounded-md hover:bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-accent transition-colors" title="캐릭터 관리">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
            </div>

            <!-- 포인트 -->
            <div class="flex items-center justify-between py-2 border-t border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-secondary"><?php echo function_exists('mg_point_name') ? mg_point_name() : '포인트'; ?></span>
                <span class="text-sm font-semibold text-mg-accent"><?php echo function_exists('mg_point_format') ? mg_point_format($member['mb_point']) : number_format($member['mb_point']).'P'; ?></span>
            </div>

            <!-- 출석체크 버튼 -->
            <a href="<?php echo G5_BBS_URL; ?>/attendance.php" class="flex items-center justify-center gap-2 w-full py-2 mt-2 bg-mg-accent/10 hover:bg-mg-accent/20 text-mg-accent rounded-md text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                출석체크
            </a>

            <!-- 마이 페이지 버튼 -->
            <a href="<?php echo G5_BBS_URL; ?>/mypage.php" class="flex items-center justify-center gap-2 w-full py-2 mt-2 bg-mg-bg-tertiary hover:bg-mg-bg-tertiary/80 text-mg-text-secondary hover:text-mg-text-primary rounded-md text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                마이 페이지
            </a>

            <!-- 로그아웃 버튼 -->
            <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="flex items-center justify-center gap-2 w-full py-2 mt-2 text-mg-text-muted hover:text-mg-error rounded-md text-sm transition-colors" data-no-spa>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                로그아웃
            </a>
        </div>

        <!-- 인벤토리 미니 -->
        <?php
        // 인벤토리 데이터 가져오기
        $inventory_items = array();
        $inventory_count = 0;
        if (function_exists('mg_get_inventory')) {
            $inv_data = mg_get_inventory($member['mb_id'], 0, 1, 3);
            $inventory_items = isset($inv_data['items']) ? $inv_data['items'] : array();
            $inventory_count = isset($inv_data['total']) ? $inv_data['total'] : 0;
        }
        ?>
        <div class="card mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    인벤토리
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">전체보기</a>
            </div>
            <?php if (count($inventory_items) > 0) { ?>
            <div class="grid grid-cols-3 gap-2">
                <?php foreach ($inventory_items as $inv_item) { ?>
                <div class="aspect-square bg-mg-bg-tertiary rounded-lg flex items-center justify-center relative group cursor-pointer hover:ring-2 hover:ring-mg-accent transition-all" title="<?php echo htmlspecialchars($inv_item['si_name']); ?>">
                    <?php if (!empty($inv_item['si_image'])) { ?>
                    <img src="<?php echo $inv_item['si_image']; ?>" alt="" class="w-full h-full object-cover rounded-lg">
                    <?php } else { ?>
                    <svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <?php } ?>
                    <?php if ($inv_item['iv_count'] > 1) { ?>
                    <span class="absolute bottom-0.5 right-0.5 bg-mg-bg-primary/90 text-xs px-1 rounded text-mg-text-secondary"><?php echo $inv_item['iv_count']; ?></span>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php for ($i = count($inventory_items); $i < 3; $i++) { ?>
                <div class="aspect-square bg-mg-bg-tertiary/50 rounded-lg border border-dashed border-mg-bg-tertiary"></div>
                <?php } ?>
            </div>
            <?php if ($inventory_count > 3) { ?>
            <p class="text-xs text-mg-text-muted text-center mt-2">+<?php echo $inventory_count - 3; ?>개 더 보유</p>
            <?php } ?>
            <?php } else { ?>
            <div class="text-center py-4">
                <p class="text-xs text-mg-text-muted mb-2">보유한 아이템이 없습니다</p>
                <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">상점 가기</a>
            </div>
            <?php } ?>
        </div>

        <!-- 선물함 미니 -->
        <?php
        // 대기 중인 선물 개수
        $pending_gifts = array();
        $gift_count = 0;
        if (function_exists('mg_get_pending_gifts')) {
            $pending_gifts = mg_get_pending_gifts($member['mb_id'], 3);
            $gift_count = count($pending_gifts);
        }
        ?>
        <div class="card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                    선물함
                    <?php if ($gift_count > 0) { ?>
                    <span class="bg-mg-error text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo $gift_count; ?></span>
                    <?php } ?>
                </h3>
                <a href="<?php echo G5_BBS_URL; ?>/gift.php" class="text-xs text-mg-accent hover:text-mg-accent-hover">전체보기</a>
            </div>
            <?php if ($gift_count > 0) { ?>
            <ul class="space-y-2">
                <?php foreach ($pending_gifts as $gift) { ?>
                <li class="flex items-center gap-2 p-2 bg-mg-bg-primary rounded-lg">
                    <div class="w-8 h-8 bg-mg-bg-tertiary rounded flex-shrink-0 flex items-center justify-center">
                        <?php if (!empty($gift['si_image'])) { ?>
                        <img src="<?php echo $gift['si_image']; ?>" alt="" class="w-full h-full object-cover rounded">
                        <?php } else { ?>
                        <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                        <?php } ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-mg-text-primary truncate"><?php echo htmlspecialchars($gift['si_name'] ?: '선물'); ?></p>
                        <p class="text-xs text-mg-text-muted">from <?php echo htmlspecialchars($gift['from_nick'] ?: $gift['mb_id_from']); ?></p>
                    </div>
                </li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <div class="text-center py-4">
                <svg class="w-8 h-8 text-mg-text-muted mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-xs text-mg-text-muted">받은 선물이 없습니다</p>
            </div>
            <?php } ?>
        </div>

        <!-- 라디오 위젯 -->
        <?php
        $_mg_radio_on = false;
        if (isset($g5['mg_radio_config_table'])) {
            $_mg_rcfg = sql_fetch("SELECT is_active FROM {$g5['mg_radio_config_table']} WHERE config_id = 1");
            if ($_mg_rcfg && $_mg_rcfg['is_active']) $_mg_radio_on = true;
        }
        if ($_mg_radio_on) {
        ?>
        <div class="card mt-4" id="mg-radio-widget">
            <!-- 날씨 -->
            <div class="flex items-center gap-2 mb-2" id="radio-weather" style="display:none;">
                <span id="radio-weather-icon" style="font-size:1.1rem;"></span>
                <span id="radio-weather-temp" class="text-sm text-mg-text-primary font-semibold"></span>
                <span id="radio-weather-desc" class="text-xs text-mg-text-muted"></span>
                <a href="https://openweathermap.org/" target="_blank" rel="noopener" id="radio-owm-credit" class="text-mg-text-muted" style="display:none;margin-left:auto;font-size:0.6rem;opacity:0.6;">OWM</a>
            </div>
            <!-- 멘트 -->
            <div class="overflow-hidden mb-2" style="height:20px;">
                <div id="radio-marquee" class="text-xs text-mg-text-muted whitespace-nowrap"></div>
            </div>
            <!-- 플레이어 (트랙 없으면 숨김) -->
            <div id="radio-player-section" style="display:none;">
                <!-- 현재 곡 -->
                <div class="flex items-center gap-2 mb-2 border-t border-mg-bg-tertiary pt-2">
                    <span class="text-mg-accent" style="font-size:0.75rem;">♪</span>
                    <span id="radio-track-title" class="text-xs text-mg-text-primary truncate flex-1">(정지)</span>
                </div>
                <!-- 컨트롤 -->
                <div class="flex items-center gap-2">
                    <button id="radio-play-btn" type="button" class="p-1.5 rounded hover:bg-mg-bg-tertiary text-mg-text-secondary transition-colors" title="재생/정지" style="font-size:0.85rem;line-height:1;">▶</button>
                    <button id="radio-next-btn" type="button" class="p-1.5 rounded hover:bg-mg-bg-tertiary text-mg-text-secondary transition-colors" title="다음 곡" style="font-size:0.75rem;line-height:1;">⏭</button>
                    <input type="range" id="radio-volume" min="0" max="100" value="30" style="flex:1;accent-color:var(--mg-accent);height:4px;">
                    <button id="radio-video-btn" type="button" class="p-1.5 rounded hover:bg-mg-bg-tertiary text-mg-text-secondary transition-colors" title="영상 보기"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                </div>
                <!-- 영상 (접힘) -->
                <div id="radio-video-wrap" style="height:0;overflow:hidden;transition:height .3s;border-radius:6px;">
                    <div id="radio-player"></div>
                </div>
            </div>
        </div>
        <style>
        @keyframes mg-marquee { 0%{transform:translateX(100%)} 100%{transform:translateX(-100%)} }
        #radio-volume { -webkit-appearance: none; background: var(--mg-bg-tertiary); border-radius: 2px; }
        #radio-volume::-webkit-slider-thumb { -webkit-appearance:none; width:12px; height:12px; border-radius:50%; background:var(--mg-accent); cursor:pointer; }
        </style>
        <script src="https://www.youtube.com/iframe_api"></script>
        <script>
        (function(){
            var MR = {
                player: null, tracks: [], ments: [], mentIdx: 0,
                trackIdx: 0, playing: false, playMode: 'sequential',
                mentMode: 'sequential', mentInterval: 12, mentTimer: null,
                weatherIcons: {
                    sunny:'☀️', partly_cloudy:'⛅', cloudy:'☁️', rain:'🌧️',
                    shower:'🌦️', snow:'❄️', fog:'🌫️', thunderstorm:'⛈️'
                },
                weatherNames: {
                    sunny:'맑음', partly_cloudy:'구름조금', cloudy:'흐림', rain:'비',
                    shower:'소나기', snow:'눈', fog:'안개', thunderstorm:'천둥번개'
                }
            };
            window._MR = MR;

            // API 로드
            fetch('<?php echo G5_BBS_URL; ?>/radio_api.php?action=status')
                .then(function(r){ return r.json(); })
                .then(function(d){
                    if (!d.success) return;
                    var data = d.data;
                    MR.tracks = data.tracks || [];
                    MR.ments = data.ments || [];
                    MR.playMode = data.play_mode;
                    MR.mentMode = data.ment_mode;
                    MR.mentInterval = data.ment_interval || 12;

                    // 날씨
                    if (data.weather) {
                        var wEl = document.getElementById('radio-weather');
                        wEl.style.display = '';
                        document.getElementById('radio-weather-icon').textContent = MR.weatherIcons[data.weather.type] || '☀️';
                        document.getElementById('radio-weather-temp').textContent = data.weather.temp + '°C';
                        document.getElementById('radio-weather-desc').textContent = MR.weatherNames[data.weather.type] || '';
                        if (data.weather_mode === 'api') {
                            var owm = document.getElementById('radio-owm-credit');
                            if (owm) owm.style.display = '';
                        }
                    }

                    // 멘트 시작
                    if (MR.ments.length > 0) {
                        startMent();
                    }

                    // 트랙 있으면 플레이어 표시
                    if (MR.tracks.length > 0) {
                        var ps = document.getElementById('radio-player-section');
                        if (ps) ps.style.display = '';
                    }

                    // 랜덤 모드면 셔플
                    if (MR.playMode === 'random' && MR.tracks.length > 1) {
                        for (var i = MR.tracks.length - 1; i > 0; i--) {
                            var j = Math.floor(Math.random() * (i + 1));
                            var tmp = MR.tracks[i]; MR.tracks[i] = MR.tracks[j]; MR.tracks[j] = tmp;
                        }
                    }
                });

            // 멘트 로테이션
            function startMent() {
                showMent();
                MR.mentTimer = setInterval(function(){
                    if (MR.mentMode === 'random') {
                        MR.mentIdx = Math.floor(Math.random() * MR.ments.length);
                    } else {
                        MR.mentIdx = (MR.mentIdx + 1) % MR.ments.length;
                    }
                    showMent();
                }, MR.mentInterval * 1000);
            }

            function showMent() {
                var el = document.getElementById('radio-marquee');
                if (!el || MR.ments.length === 0) return;
                el.textContent = MR.ments[MR.mentIdx];
                el.style.animation = 'none';
                el.offsetHeight;
                el.style.animation = 'mg-marquee ' + Math.max(6, MR.mentInterval) + 's linear infinite';
            }

            // YouTube Player
            window.onYouTubeIframeAPIReady = function() {
                MR.player = new YT.Player('radio-player', {
                    height: '120',
                    width: '100%',
                    playerVars: {
                        autoplay: 0, controls: 0, disablekb: 1,
                        modestbranding: 1, rel: 0, fs: 0
                    },
                    events: {
                        onReady: function(e) {
                            e.target.setVolume(parseInt(document.getElementById('radio-volume').value));
                        },
                        onStateChange: function(e) {
                            if (e.data === YT.PlayerState.ENDED) {
                                nextTrack();
                            }
                        },
                        onError: function() {
                            nextTrack();
                        }
                    }
                });
            };

            function playTrack(idx) {
                if (!MR.player || MR.tracks.length === 0) return;
                MR.trackIdx = idx % MR.tracks.length;
                var t = MR.tracks[MR.trackIdx];
                document.getElementById('radio-track-title').textContent = t.title;
                MR.player.loadVideoById(t.vid);
                MR.playing = true;
                document.getElementById('radio-play-btn').textContent = '⏸';
            }

            function nextTrack() {
                if (MR.tracks.length === 0) return;
                playTrack(MR.trackIdx + 1);
            }

            // 재생/정지
            document.getElementById('radio-play-btn').addEventListener('click', function() {
                if (!MR.player || MR.tracks.length === 0) return;
                if (MR.playing) {
                    MR.player.pauseVideo();
                    MR.playing = false;
                    this.textContent = '▶';
                } else {
                    if (MR.player.getPlayerState && MR.player.getPlayerState() === YT.PlayerState.PAUSED) {
                        MR.player.playVideo();
                        MR.playing = true;
                        this.textContent = '⏸';
                    } else {
                        playTrack(MR.trackIdx);
                    }
                }
            });

            // 다음 곡
            document.getElementById('radio-next-btn').addEventListener('click', function() {
                nextTrack();
            });

            // 볼륨
            document.getElementById('radio-volume').addEventListener('input', function() {
                if (MR.player && MR.player.setVolume) MR.player.setVolume(parseInt(this.value));
            });

            // 영상 토글
            document.getElementById('radio-video-btn').addEventListener('click', function() {
                var wrap = document.getElementById('radio-video-wrap');
                if (parseInt(wrap.style.height) === 0) {
                    wrap.style.height = '120px';
                    this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
                } else {
                    wrap.style.height = '0';
                    this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
                }
            });
        })();
        </script>
        <?php } ?>

        <?php } else { ?>
        <!-- 비로그인 상태 -->
        <div class="card text-center">
            <div class="py-4">
                <svg class="w-12 h-12 text-mg-text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-sm text-mg-text-secondary mb-4">로그인하고 커뮤니티에 참여하세요</p>
                <a href="<?php echo G5_BBS_URL; ?>/login.php" class="btn btn-primary w-full mb-2">로그인</a>
                <a href="<?php echo G5_BBS_URL; ?>/register.php" class="btn btn-secondary w-full">회원가입</a>
            </div>
        </div>
        <?php } ?>

    </aside>

</div>
<!-- End Main Layout -->

<!-- Footer -->
<footer class="bg-mg-bg-secondary border-t border-mg-bg-tertiary py-4 ml-0 lg:ml-14">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-2 text-sm text-mg-text-muted">
            <?php $mg_footer_name = function_exists('mg_config') ? mg_config('site_name', $config['cf_title']) : $config['cf_title']; ?>
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($mg_footer_name); ?>. Powered by Morgan Edition.</p>
            <nav class="flex gap-4">
                <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=provision" class="hover:text-mg-text-primary transition-colors">이용약관</a>
                <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=privacy" class="hover:text-mg-text-primary transition-colors">개인정보처리방침</a>
            </nav>
        </div>
    </div>
</footer>

</div>
<!-- End App Container -->

<!-- Morgan Edition JS -->
<script src="<?php echo G5_THEME_URL; ?>/js/app.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script src="<?php echo G5_THEME_URL; ?>/js/emoticon-picker.js?ver=<?php echo G5_JS_VER; ?>"></script>

<?php
// 그누보드 JS 출력
if (function_exists('get_javascript_file')) {
    echo get_javascript_file();
}

// 업적 달성 토스트
if (!empty($_SESSION['mg_achievement_toast'])) {
    $toast = $_SESSION['mg_achievement_toast'];
    unset($_SESSION['mg_achievement_toast']);
    $toast_rarity_colors = array(
        'common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6',
        'epic' => '#a855f7', 'legendary' => '#f59e0b',
    );
    $toast_rarity_labels = array(
        'common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare',
        'epic' => 'Epic', 'legendary' => 'Legendary',
    );
    $toast_color = $toast_rarity_colors[$toast['rarity'] ?? 'common'] ?? '#949ba4';
    $toast_label = $toast_rarity_labels[$toast['rarity'] ?? 'common'] ?? '';
?>
<div id="mg-achievement-toast" style="position:fixed;bottom:-200px;left:50%;transform:translateX(-50%);z-index:9999;min-width:320px;max-width:420px;background:var(--mg-bg-primary,#1e1f22);border:2px solid <?php echo $toast_color; ?>;border-radius:12px;padding:16px 20px;box-shadow:0 0 30px <?php echo $toast_color; ?>40,0 8px 32px rgba(0,0,0,.5);transition:bottom .6s cubic-bezier(.34,1.56,.64,1);pointer-events:auto;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="flex-shrink:0;width:48px;height:48px;border-radius:8px;border:2px solid <?php echo $toast_color; ?>;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.3);">
            <?php if (!empty($toast['icon'])) { ?>
            <img src="<?php echo htmlspecialchars($toast['icon']); ?>" alt="" style="width:36px;height:36px;object-fit:contain;">
            <?php } else { ?>
            <span style="font-size:24px;">&#127942;</span>
            <?php } ?>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:<?php echo $toast_color; ?>;margin-bottom:2px;"><?php echo $toast_label; ?> Achievement Unlocked!</div>
            <div style="font-size:15px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($toast['name']); ?></div>
            <?php if (!empty($toast['desc'])) { ?>
            <div style="font-size:12px;color:var(--mg-text-secondary,#b5bac1);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($toast['desc']); ?></div>
            <?php } ?>
            <?php if (!empty($toast['reward'])) { ?>
            <div style="font-size:11px;color:<?php echo $toast_color; ?>;margin-top:4px;font-weight:500;">&#127873; <?php echo htmlspecialchars($toast['reward']); ?></div>
            <?php } ?>
        </div>
        <button onclick="document.getElementById('mg-achievement-toast').style.bottom='-200px'" style="flex-shrink:0;background:none;border:none;color:var(--mg-text-muted,#949ba4);cursor:pointer;padding:4px;font-size:18px;line-height:1;">&times;</button>
    </div>
</div>
<script>
(function(){
    var t = document.getElementById('mg-achievement-toast');
    if (!t) return;
    setTimeout(function(){ t.style.bottom = '24px'; }, 300);
    setTimeout(function(){ t.style.bottom = '-200px'; }, 6000);
})();
</script>
<?php } ?>

<?php
// ─── 히든 이벤트 위젯 (로그인 + 이벤트 활성 시만) ───
if ($is_member) {
    $he_active = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_hidden_event_table']} WHERE is_active = 1");
    if ((int)($he_active['cnt'] ?? 0) > 0) {
?>
<div id="mg-hidden-event-overlay" style="display:none;position:fixed;z-index:9000;pointer-events:none;">
    <img id="mg-he-image" src="" alt="" style="pointer-events:auto;cursor:pointer;position:absolute;width:80px;height:80px;object-fit:contain;filter:drop-shadow(0 0 8px rgba(245,159,10,0.6));animation:mg-he-float 2s ease-in-out infinite;transition:transform .2s,opacity .4s;">
</div>
<style>
@keyframes mg-he-float {
    0%,100% { transform: translateY(0) rotate(-3deg); }
    50% { transform: translateY(-12px) rotate(3deg); }
}
#mg-he-image:hover { transform: scale(1.2) !important; }
</style>
<script>
(function(){
    var HE = {
        overlay: null,
        img: null,
        token: null,
        cooldown: false,
        fadeTimer: null,
        BBS_URL: '<?php echo G5_BBS_URL; ?>',

        init: function() {
            this.overlay = document.getElementById('mg-hidden-event-overlay');
            this.img = document.getElementById('mg-he-image');
            if (!this.overlay || !this.img) return;

            var self = this;
            this.img.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.claim();
            });

            // SPA 페이지 전환 시 체크
            window.addEventListener('mg:pageLoaded', function() {
                self.check();
            });

            // 최초 로드 시 1회 체크
            setTimeout(function() { self.check(); }, 2000);
        },

        check: function() {
            if (this.cooldown) return;
            this.cooldown = true;
            var self = this;

            // 5초 쿨다운
            setTimeout(function() { self.cooldown = false; }, 5000);

            fetch(this.BBS_URL + '/hidden_event_api.php?action=check', {
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.event) {
                    self.show(data.event);
                }
            })
            .catch(function() {});
        },

        show: function(ev) {
            this.token = ev.token;
            this.img.src = ev.image_url;

            // 랜덤 위치 (viewport 20%~80%)
            var vw = window.innerWidth;
            var vh = window.innerHeight;
            var x = Math.floor(vw * 0.2 + Math.random() * vw * 0.6);
            var y = Math.floor(vh * 0.2 + Math.random() * vh * 0.4);

            this.overlay.style.left = x + 'px';
            this.overlay.style.top = y + 'px';
            this.img.style.opacity = '1';
            this.overlay.style.display = 'block';

            // 5초 후 자동 페이드아웃
            var self = this;
            if (this.fadeTimer) clearTimeout(this.fadeTimer);
            this.fadeTimer = setTimeout(function() {
                self.hide();
            }, 5000);
        },

        hide: function() {
            var self = this;
            this.img.style.opacity = '0';
            setTimeout(function() {
                self.overlay.style.display = 'none';
                self.token = null;
            }, 400);
        },

        claim: function() {
            if (!this.token) return;
            var self = this;
            if (this.fadeTimer) clearTimeout(this.fadeTimer);

            // 클릭 즉시 축소 애니메이션
            this.img.style.transform = 'scale(0.3)';
            this.img.style.opacity = '0';

            var formData = new FormData();
            formData.append('action', 'claim');
            formData.append('token', this.token);

            fetch(this.BBS_URL + '/hidden_event_api.php?action=claim', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                setTimeout(function() {
                    self.overlay.style.display = 'none';
                    self.img.style.transform = '';
                }, 400);

                if (data.success) {
                    self.showRewardToast(data.reward_name);
                }
                self.token = null;
            })
            .catch(function() {
                self.overlay.style.display = 'none';
                self.img.style.transform = '';
                self.token = null;
            });
        },

        showRewardToast: function(rewardName) {
            // 보상 토스트
            var toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;bottom:-80px;left:50%;transform:translateX(-50%);z-index:9999;background:var(--mg-bg-primary,#1e1f22);border:2px solid var(--mg-accent,#f59f0a);border-radius:12px;padding:12px 20px;box-shadow:0 0 20px rgba(245,159,10,0.3),0 8px 24px rgba(0,0,0,.5);transition:bottom .5s cubic-bezier(.34,1.56,.64,1);white-space:nowrap;pointer-events:auto;';
            toast.innerHTML = '<div style="display:flex;align-items:center;gap:10px;">'
                + '<span style="font-size:20px;">&#127873;</span>'
                + '<div>'
                + '<div style="font-size:11px;font-weight:600;color:var(--mg-accent,#f59f0a);text-transform:uppercase;letter-spacing:.5px;">Hidden Event!</div>'
                + '<div style="font-size:14px;font-weight:700;color:#fff;">' + (rewardName || '보상') + ' 획득!</div>'
                + '</div></div>';
            document.body.appendChild(toast);

            setTimeout(function() { toast.style.bottom = '24px'; }, 50);
            setTimeout(function() {
                toast.style.bottom = '-80px';
                setTimeout(function() { toast.remove(); }, 500);
            }, 4000);
        }
    };

    // DOM 준비 후 초기화
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { HE.init(); });
    } else {
        HE.init();
    }
})();
</script>
<?php } } ?>

</body>
</html>
