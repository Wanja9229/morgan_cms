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
<div class="mg-widget-calendar" id="<?php echo $_cal_id; ?>">
    <!-- 헤더: 제목 + 네비게이션 -->
    <div class="cal-header">
        <button type="button" class="cal-nav-btn" data-dir="-1">
            <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
        </button>
        <span class="cal-month-label"></span>
        <button type="button" class="cal-nav-btn" data-dir="1">
            <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
        </button>
    </div>

    <!-- 요일 헤더 -->
    <div class="cal-weekdays">
        <span>일</span><span>월</span><span>화</span><span>수</span><span>목</span><span>금</span><span>토</span>
    </div>

    <!-- 날짜 그리드 -->
    <div class="cal-days"></div>

    <!-- 날짜 클릭 시 미션 팝오버 -->
    <div class="cal-popover" style="display:none;">
        <div class="cal-popover-header">
            <span class="cal-popover-date"></span>
            <button type="button" class="cal-popover-close">&times;</button>
        </div>
        <div class="cal-popover-body"></div>
    </div>
</div>

<style>
.mg-widget-calendar {
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 0.5rem;
    font-size: 0.8rem;
    background: var(--mg-bg-secondary);
    border-radius: 0.5rem;
    border: 1px solid var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.35rem;
}
.mg-widget-calendar .cal-month-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--mg-text-primary);
}
.mg-widget-calendar .cal-nav-btn {
    background: none;
    border: none;
    color: var(--mg-text-muted);
    cursor: pointer;
    padding: 2px;
    border-radius: 4px;
    display: flex;
    align-items: center;
}
.mg-widget-calendar .cal-nav-btn:hover {
    color: var(--mg-accent);
    background: var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    font-size: 0.65rem;
    color: var(--mg-text-muted);
    margin-bottom: 2px;
}
.mg-widget-calendar .cal-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    flex: 1;
}
.mg-widget-calendar .cal-day {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1px 0;
    border-radius: 4px;
    cursor: default;
    font-size: 0.75rem;
    line-height: 1;
    min-height: 0;
}
.mg-widget-calendar .cal-day.other-month {
    opacity: 0.3;
}
.mg-widget-calendar .cal-day.today .cal-num {
    color: var(--mg-accent);
    font-weight: 700;
}
.mg-widget-calendar .cal-day.has-mission {
    cursor: pointer;
}
.mg-widget-calendar .cal-day.has-mission:hover {
    background: var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-day.selected {
    background: var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-dots {
    display: flex;
    gap: 1px;
    height: 3px;
    margin-top: 1px;
}
.mg-widget-calendar .cal-dot {
    width: 3px;
    height: 3px;
    border-radius: 50%;
}
.mg-widget-calendar .cal-dot.active { background: var(--mg-accent); }
.mg-widget-calendar .cal-dot.closed { background: var(--mg-text-muted); }

/* 팝오버 */
.mg-widget-calendar .cal-popover {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 6px;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.3);
    z-index: 10;
    max-height: 60%;
    overflow: auto;
}
.mg-widget-calendar .cal-popover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.35rem 0.5rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--mg-text-secondary);
}
.mg-widget-calendar .cal-popover-close {
    background: none;
    border: none;
    color: var(--mg-text-muted);
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
    padding: 0 2px;
}
.mg-widget-calendar .cal-popover-close:hover { color: var(--mg-text-primary); }
.mg-widget-calendar .cal-popover-body {
    padding: 0.35rem 0.5rem;
}
.mg-widget-calendar .cal-mission-item {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 4px;
    border-radius: 4px;
    font-size: 0.7rem;
    text-decoration: none;
    color: var(--mg-text-primary);
}
.mg-widget-calendar .cal-mission-item:hover {
    background: var(--mg-bg-tertiary);
}
.mg-widget-calendar .cal-mission-dot {
    width: 5px;
    height: 5px;
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
    var popover = container.querySelector('.cal-popover');
    var popoverDate = container.querySelector('.cal-popover-date');
    var popoverBody = container.querySelector('.cal-popover-body');
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

        var monthNames = ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'];
        monthLabel.textContent = year + '년 ' + monthNames[month];

        var firstDay = new Date(year, month, 1).getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var prevDays = new Date(year, month, 0).getDate();

        var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

        var html = '';

        // 이전 달
        for (var i = firstDay - 1; i >= 0; i--) {
            html += '<div class="cal-day other-month"><span class="cal-num">' + (prevDays - i) + '</span></div>';
        }

        // 현재 달
        for (var d = 1; d <= daysInMonth; d++) {
            var dateKey = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            var dayMissions = dateMap[dateKey] || [];
            var isToday = dateKey === todayStr;
            var classes = 'cal-day';
            if (isToday) classes += ' today';
            if (dayMissions.length > 0) classes += ' has-mission';

            var dots = '';
            if (dayMissions.length > 0) {
                dots = '<div class="cal-dots">';
                dayMissions.slice(0, 3).forEach(function(m) {
                    dots += '<span class="cal-dot ' + (m.status === 'active' ? 'active' : 'closed') + '"></span>';
                });
                dots += '</div>';
            }

            html += '<div class="' + classes + '" data-date="' + dateKey + '"><span class="cal-num">' + d + '</span>' + dots + '</div>';
        }

        // 다음 달
        var totalCells = firstDay + daysInMonth;
        var remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (var j = 1; j <= remaining; j++) {
            html += '<div class="cal-day other-month"><span class="cal-num">' + j + '</span></div>';
        }

        daysGrid.innerHTML = html;
        closePopover();

        // 클릭 이벤트
        daysGrid.querySelectorAll('.cal-day.has-mission').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                var date = this.getAttribute('data-date');
                // 토글
                if (selectedDate === date) {
                    closePopover();
                    return;
                }
                selectedDate = date;
                daysGrid.querySelectorAll('.cal-day').forEach(function(d) { d.classList.remove('selected'); });
                this.classList.add('selected');
                showPopover(date);
            });
        });
    }

    function showPopover(dateKey) {
        var dayMissions = dateMap[dateKey] || [];
        if (dayMissions.length === 0) { closePopover(); return; }

        var parts = dateKey.split('-');
        popoverDate.textContent = parseInt(parts[1]) + '/' + parseInt(parts[2]) + ' (' + dayMissions.length + '건)';

        var html = '';
        dayMissions.forEach(function(m) {
            var dotClass = m.status === 'active' ? 'active' : 'closed';
            var dateRange = '';
            if (m.start) dateRange += m.start.slice(5).replace('-', '/');
            if (m.end) dateRange += '~' + m.end.slice(5).replace('-', '/');
            var href = '<?php echo G5_BBS_URL; ?>/board.php?bo_table=' + encodeURIComponent(m.bo_table) + '&pm_id=' + m.pm_id;

            html += '<a href="' + href + '" class="cal-mission-item" data-no-spa>'
                + '<span class="cal-mission-dot ' + dotClass + '" style="background:' + (m.status === 'active' ? 'var(--mg-accent)' : 'var(--mg-text-muted)') + ';"></span>'
                + '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + escapeHtml(m.title) + '</span>'
                + '<span style="color:var(--mg-accent);font-weight:600;flex-shrink:0;">' + m.point.toLocaleString() + 'P</span>'
                + '</a>';
        });
        popoverBody.innerHTML = html;
        popover.style.display = '';
    }

    function closePopover() {
        selectedDate = null;
        popover.style.display = 'none';
        daysGrid.querySelectorAll('.cal-day').forEach(function(d) { d.classList.remove('selected'); });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // 팝오버 닫기
    container.querySelector('.cal-popover-close').addEventListener('click', closePopover);

    // 네비게이션
    navBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var dir = parseInt(this.getAttribute('data-dir'));
            var newMonth = currentMonth + dir;
            var newYear = currentYear;
            if (newMonth < 0) { newMonth = 11; newYear--; }
            if (newMonth > 11) { newMonth = 0; newYear++; }
            renderMonth(newYear, newMonth);
        });
    });

    // 초기 렌더링
    renderMonth(currentYear, currentMonth);
})();
</script>
