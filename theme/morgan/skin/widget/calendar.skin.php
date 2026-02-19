<?php
/**
 * Morgan Edition - Mission Calendar Widget Skin
 *
 * 사용 가능한 변수:
 * $config - 위젯 설정
 * $title - 제목
 * $missions - 미션 배열 [{pm_id, title, cycle, status, start, end, point, bonus, bo_table}, ...]
 */

if (!defined('_GNUBOARD_')) exit;

$_cal_id = 'mg-cal-' . uniqid();
?>
<div class="card mg-widget mg-widget-calendar h-full flex flex-col" id="<?php echo $_cal_id; ?>">
    <h2 class="card-header">
        <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <?php echo htmlspecialchars($title); ?>
    </h2>

    <!-- 달력 네비게이션 -->
    <div class="flex items-center justify-between mb-3">
        <button type="button" class="cal-nav-btn text-mg-text-secondary hover:text-mg-accent p-1" data-dir="-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <span class="cal-month-label text-sm font-semibold text-mg-text-primary"></span>
        <button type="button" class="cal-nav-btn text-mg-text-secondary hover:text-mg-accent p-1" data-dir="1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>

    <!-- 달력 그리드 -->
    <div class="cal-grid mb-3">
        <div class="grid grid-cols-7 text-center text-xs text-mg-text-muted mb-1">
            <span>일</span><span>월</span><span>화</span><span>수</span><span>목</span><span>금</span><span>토</span>
        </div>
        <div class="cal-days grid grid-cols-7 text-center text-sm gap-y-1"></div>
    </div>

    <!-- 선택된 날짜의 미션 목록 -->
    <div class="cal-detail flex-1 overflow-auto border-t border-mg-bg-tertiary pt-2" style="min-height:60px;">
        <div class="cal-detail-list space-y-1"></div>
    </div>
</div>

<style>
.mg-widget-calendar .cal-day {
    position: relative;
    padding: 4px 0;
    border-radius: 6px;
    cursor: default;
    transition: background 0.15s;
    line-height: 1.4;
}
.mg-widget-calendar .cal-day.has-mission {
    cursor: pointer;
}
.mg-widget-calendar .cal-day.has-mission:hover {
    background: var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-day.today {
    font-weight: 700;
    color: var(--mg-accent);
}
.mg-widget-calendar .cal-day.selected {
    background: var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-day.other-month {
    color: var(--mg-text-muted);
    opacity: 0.4;
}
.mg-widget-calendar .cal-dots {
    display: flex;
    justify-content: center;
    gap: 2px;
    height: 6px;
    margin-top: 1px;
}
.mg-widget-calendar .cal-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
}
.mg-widget-calendar .cal-dot.active {
    background: var(--mg-accent);
}
.mg-widget-calendar .cal-dot.closed {
    background: var(--mg-text-muted);
}
.mg-widget-calendar .cal-mission-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 0.8125rem;
    transition: background 0.15s;
}
.mg-widget-calendar .cal-mission-item:hover {
    background: var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-mission-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
</style>

<script>
(function() {
    var container = document.getElementById('<?php echo $_cal_id; ?>');
    if (!container) return;

    var missions = <?php echo json_encode($missions, JSON_UNESCAPED_UNICODE); ?>;
    var today = new Date();
    var currentYear = today.getFullYear();
    var currentMonth = today.getMonth();
    var selectedDate = null;

    var monthLabel = container.querySelector('.cal-month-label');
    var daysGrid = container.querySelector('.cal-days');
    var detailList = container.querySelector('.cal-detail-list');
    var navBtns = container.querySelectorAll('.cal-nav-btn');

    // 미션을 날짜별 맵으로 변환
    function buildDateMap() {
        var map = {};
        missions.forEach(function(m) {
            if (!m.start && !m.end) return;
            var start = m.start ? new Date(m.start + 'T00:00:00') : new Date(m.end + 'T00:00:00');
            var end = m.end ? new Date(m.end + 'T00:00:00') : start;
            for (var d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                var key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                if (!map[key]) map[key] = [];
                map[key].push(m);
            }
        });
        return map;
    }

    var dateMap = buildDateMap();

    function renderMonth(year, month) {
        currentYear = year;
        currentMonth = month;

        var monthNames = ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'];
        monthLabel.textContent = year + '년 ' + monthNames[month];

        var firstDay = new Date(year, month, 1).getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var prevDays = new Date(year, month, 0).getDate();

        var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

        var html = '';

        // 이전 달 빈 칸
        for (var i = firstDay - 1; i >= 0; i--) {
            html += '<div class="cal-day other-month"><span>' + (prevDays - i) + '</span><div class="cal-dots"></div></div>';
        }

        // 현재 달
        for (var d = 1; d <= daysInMonth; d++) {
            var dateKey = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            var dayMissions = dateMap[dateKey] || [];
            var isToday = dateKey === todayStr;
            var isSelected = selectedDate === dateKey;
            var classes = 'cal-day';
            if (isToday) classes += ' today';
            if (isSelected) classes += ' selected';
            if (dayMissions.length > 0) classes += ' has-mission';

            var dots = '';
            if (dayMissions.length > 0) {
                dots = '<div class="cal-dots">';
                var shown = dayMissions.slice(0, 3);
                shown.forEach(function(m) {
                    dots += '<span class="cal-dot ' + (m.status === 'active' ? 'active' : 'closed') + '"></span>';
                });
                dots += '</div>';
            } else {
                dots = '<div class="cal-dots"></div>';
            }

            html += '<div class="' + classes + '" data-date="' + dateKey + '"><span>' + d + '</span>' + dots + '</div>';
        }

        // 다음 달 빈 칸
        var totalCells = firstDay + daysInMonth;
        var remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (var j = 1; j <= remaining; j++) {
            html += '<div class="cal-day other-month"><span>' + j + '</span><div class="cal-dots"></div></div>';
        }

        daysGrid.innerHTML = html;

        // 클릭 이벤트
        daysGrid.querySelectorAll('.cal-day.has-mission').forEach(function(el) {
            el.addEventListener('click', function() {
                selectedDate = this.getAttribute('data-date');
                // 선택 표시 갱신
                daysGrid.querySelectorAll('.cal-day').forEach(function(d) { d.classList.remove('selected'); });
                this.classList.add('selected');
                renderDetail(selectedDate);
            });
        });

        // 기본: 오늘 날짜 또는 첫 미션 날짜의 디테일 표시
        if (!selectedDate) {
            if (dateMap[todayStr] && dateMap[todayStr].length > 0) {
                selectedDate = todayStr;
            }
        }
        renderDetail(selectedDate);
    }

    function renderDetail(dateKey) {
        if (!dateKey || !dateMap[dateKey]) {
            // 이번 달 전체 미션 요약
            var monthMissions = missions.filter(function(m) {
                var s = m.start ? new Date(m.start) : null;
                var e = m.end ? new Date(m.end) : null;
                var mStart = new Date(currentYear, currentMonth, 1);
                var mEnd = new Date(currentYear, currentMonth + 1, 0);
                return (s && s <= mEnd && e && e >= mStart) || (!s && !e);
            });
            if (monthMissions.length === 0) {
                detailList.innerHTML = '<div class="text-xs text-mg-text-muted text-center py-2">이번 달 미션이 없습니다.</div>';
                return;
            }
            var html = '';
            monthMissions.forEach(function(m) {
                html += renderMissionItem(m);
            });
            detailList.innerHTML = html;
            return;
        }

        var dayMissions = dateMap[dateKey];
        var html = '<div class="text-xs text-mg-text-muted mb-1">' + dateKey + '</div>';
        dayMissions.forEach(function(m) {
            html += renderMissionItem(m);
        });
        detailList.innerHTML = html;
    }

    function renderMissionItem(m) {
        var dotClass = m.status === 'active' ? 'active' : 'closed';
        var statusLabel = m.status === 'active' ? '진행중' : '종료';
        var dateRange = '';
        if (m.start) dateRange += m.start.slice(5).replace('-', '/');
        if (m.end) dateRange += '~' + m.end.slice(5).replace('-', '/');
        var href = '<?php echo G5_BBS_URL; ?>/board.php?bo_table=' + encodeURIComponent(m.bo_table) + '&pm_id=' + m.pm_id;

        return '<a href="' + href + '" class="cal-mission-item">'
            + '<span class="cal-mission-dot ' + dotClass + '"></span>'
            + '<span class="flex-1 truncate text-mg-text-primary">' + escapeHtml(m.title) + '</span>'
            + '<span class="text-xs text-mg-text-muted">' + dateRange + '</span>'
            + '<span class="text-xs text-mg-accent font-medium">' + m.point.toLocaleString() + 'P</span>'
            + '</a>';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // 네비게이션
    navBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var dir = parseInt(this.getAttribute('data-dir'));
            var newMonth = currentMonth + dir;
            var newYear = currentYear;
            if (newMonth < 0) { newMonth = 11; newYear--; }
            if (newMonth > 11) { newMonth = 0; newYear++; }
            selectedDate = null;
            renderMonth(newYear, newMonth);
        });
    });

    // 초기 렌더링
    renderMonth(currentYear, currentMonth);
})();
</script>
