# 라디오 위젯 기획

> Morgan CMS 우측 사이드바에 배치되는 라디오/날씨 위젯 설계 문서

---

## 1. 개요

사이드바에 상시 노출되는 라디오 위젯으로, 유튜브 기반 음악 재생 + 날씨 표시 + 멘트 로테이션 기능을 제공한다. SPA 구조상 사이드바 DOM이 유지되므로 페이지 전환 시에도 음악이 끊기지 않는다.

### 전제 조건 (구현 전 확인 필요)

- **사이드바 DOM 유지 여부**: 모든 페이지 전환 시 사이드바가 다시 렌더링되지 않는지 확인 필요. 만약 특정 페이지(마이페이지, 관리자 등)에서 레이아웃이 통째로 바뀌면 iframe이 리셋됨.
- 사이드바가 리렌더되는 페이지가 있다면, 해당 페이지에서는 라디오 위젯을 별도 처리하거나 레이아웃 구조를 조정해야 함.

---

## 2. 위젯 구조

```
┌─────────────────────────┐
│  🌤 22°C  맑음           │  ← 날씨 영역
├─────────────────────────┤
│  ♪ 노래 제목             │  ← 현재 재생 정보
│  ▶ ■  🔊 ──○──  📺     │  ← 컨트롤 (재생/정지, 볼륨, 영상 토글)
├─────────────────────────┤
│  ┌─────────────────┐    │
│  │                 │    │  ← 유튜브 영상 영역 (접기/펼치기)
│  │   YouTube       │    │
│  │                 │    │
│  └─────────────────┘    │
├─────────────────────────┤
│ ◀ 오늘도 좋은 하루 되세  │  ← 멘트 흐름 영역 (marquee)
└─────────────────────────┘
```

### 위젯 요소별 설명

**날씨 영역**
- 기온(°C) + 날씨 상태 아이콘 + 텍스트
- 날씨 타입: 맑음, 구름조금, 흐림, 비, 소나기, 눈, 안개, 천둥번개
- 아이콘은 CSS 또는 이모지로 처리 (외부 의존성 없이)

**재생 컨트롤**
- 재생/정지 토글 버튼
- 볼륨 슬라이더
- 영상 토글 버튼 (📺 아이콘)
- 현재 곡 제목 표시

**유튜브 영상 영역**
- 기본: 접힌 상태
- 📺 버튼 클릭 시 펼침/접힘
- 접힐 때 `display:none` 사용 금지 → `height:0; overflow:hidden` 으로 처리 (유튜브 정책상 iframe이 DOM에 존재해야 재생 유지)

**멘트 영역**
- CSS 애니메이션으로 텍스트가 좌로 흘러감
- 관리자가 등록한 멘트를 순차 또는 랜덤 로테이션
- 1차에서는 페이지 로드 시 한 번 fetch, 클라이언트에서 로테이션
- 2차에서 WebSocket/Supabase 연동 시 실시간 멘트 추가

---

## 3. 기능 상세

### 3-1. 유튜브 재생

**YouTube IFrame API 사용**

```javascript
// 기본 흐름
var player;
function onYouTubeIframeAPIReady() {
    player = new YT.Player('radio-player', {
        height: '180',
        width: '100%',
        videoId: '{VIDEO_ID}',
        playerVars: {
            autoplay: 0,
            controls: 0,      // 유튜브 기본 컨트롤 숨김
            disablekb: 1,
            modestbranding: 1
        },
        events: {
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange
        }
    });
}
```

**곡 정보 관리**
- 관리자가 유튜브 URL 입력 → 서버에서 video ID 파싱 후 저장
- 플레이리스트 지원: 여러 곡 등록 → 순차 재생 또는 랜덤 재생
- 곡 전환 시 `player.loadVideoById()`로 iframe 재생성 없이 교체

**자동 재생 제한 대응**
- 브라우저 정책상 유저 인터랙션 없이 자동 재생 불가
- 첫 진입 시 정지 상태 → 유저가 재생 버튼 클릭해야 시작
- 한 번 재생 시작하면 페이지 전환해도 계속 재생 (SPA 구조)

### 3-2. 날씨 시스템

**두 가지 모드 (관리자가 선택)**

| 모드 | 설명 | 설정 |
|------|------|------|
| API 자동 | OpenWeatherMap에서 실제 날씨 가져옴 | 도시명 또는 좌표 설정 |
| 수동 설정 | 관리자가 직접 기온/날씨 입력 | 세계관 날씨용 |

**API 모드 상세**
- OpenWeatherMap 무료 티어 사용 (분당 60회, 월 1,000,000회)
- 호출 주기: 1시간 1회 (크론잡 또는 첫 요청 시 캐시 체크)
- 서버에서 호출 후 캐시 → 클라이언트는 캐시된 데이터만 가져감
- API 키는 서버에만 저장, 클라이언트 노출 금지

```
캐시 흐름:
1. 클라이언트가 /api/weather 요청
2. 서버가 캐시 확인 (1시간 이내면 캐시 반환)
3. 캐시 만료 시 OpenWeatherMap 호출 → 캐시 갱신 → 반환
```

**수동 모드 상세**
- 관리자 페이지에서 기온(숫자) + 날씨 타입(셀렉트박스) 입력
- 저장 즉시 반영 (다음 페이지 로드 시)

**날씨 타입 매핑**

| 타입 | 아이콘 | API 매핑 (OpenWeatherMap) |
|------|--------|--------------------------|
| sunny | ☀️ | Clear |
| partly_cloudy | ⛅ | Clouds (few/scattered) |
| cloudy | ☁️ | Clouds (broken/overcast) |
| rain | 🌧️ | Rain, Drizzle |
| shower | 🌦️ | Rain (shower) |
| snow | ❄️ | Snow |
| fog | 🌫️ | Mist, Fog, Haze |
| thunderstorm | ⛈️ | Thunderstorm |

### 3-3. 멘트 시스템

**1차 구현 (정적 로테이션)**
- 관리자가 멘트 여러 줄 등록 (최대 20개 정도)
- 페이지 최초 로드 시 전체 멘트 목록 fetch
- 클라이언트에서 일정 간격(10~15초)으로 다음 멘트로 전환
- 순차 또는 랜덤 모드 선택 가능 (관리자 설정)

```javascript
// 클라이언트 로테이션 예시
let messages = []; // fetch로 가져온 멘트 배열
let currentIdx = 0;

function rotateMessage() {
    if (messages.length === 0) return;
    const marquee = document.getElementById('radio-marquee');
    marquee.textContent = messages[currentIdx];
    currentIdx = (currentIdx + 1) % messages.length;
    // CSS 애니메이션 리셋
    marquee.style.animation = 'none';
    marquee.offsetHeight; // reflow 트리거
    marquee.style.animation = '';
}

setInterval(rotateMessage, 12000); // 12초마다 교체
```

**2차 구현 (실시간, WebSocket/Supabase 연동 시)**
- 관리자가 실시간으로 멘트 입력 → 즉시 모든 접속자에게 반영
- 기존 로테이션은 유지하되, 실시간 멘트가 들어오면 우선 표시
- 실시간 멘트에는 특별한 스타일(색상, 깜빡임 등) 적용 가능

---

## 4. DB 설계

```sql
-- 라디오 설정 (단일 row, 전역 설정)
CREATE TABLE morgan_radio_config (
    config_id       INT PRIMARY KEY DEFAULT 1,
    is_active       TINYINT DEFAULT 1,          -- 라디오 위젯 사용 여부
    -- 플레이리스트 모드
    play_mode       ENUM('sequential', 'random') DEFAULT 'sequential',
    -- 날씨 설정
    weather_mode    ENUM('api', 'manual') DEFAULT 'manual',
    weather_city    VARCHAR(100) NULL,           -- API 모드 시 도시명
    weather_lat     DECIMAL(9,6) NULL,           -- API 모드 시 좌표
    weather_lon     DECIMAL(9,6) NULL,
    weather_api_key VARCHAR(100) NULL,           -- OpenWeatherMap API 키
    manual_temp     INT NULL,                    -- 수동 모드 시 기온
    manual_weather  VARCHAR(20) NULL,            -- 수동 모드 시 날씨 타입
    -- 멘트 설정
    ment_mode       ENUM('sequential', 'random') DEFAULT 'sequential',
    ment_interval   INT DEFAULT 12,              -- 멘트 교체 간격 (초)
    -- 캐시
    weather_cache   JSON NULL,                   -- API 응답 캐시
    weather_cached_at DATETIME NULL,             -- 캐시 시각
    --
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 플레이리스트
CREATE TABLE morgan_radio_playlist (
    track_id        INT AUTO_INCREMENT PRIMARY KEY,
    youtube_url     VARCHAR(255) NOT NULL,
    youtube_vid     VARCHAR(20) NOT NULL,        -- 파싱된 video ID
    title           VARCHAR(200) NOT NULL,       -- 곡 제목 (관리자 입력)
    sort_order      INT DEFAULT 0,
    is_active       TINYINT DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 멘트 목록
CREATE TABLE morgan_radio_ments (
    ment_id         INT AUTO_INCREMENT PRIMARY KEY,
    content         VARCHAR(200) NOT NULL,       -- 멘트 텍스트
    sort_order      INT DEFAULT 0,
    is_active       TINYINT DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5. API 엔드포인트

```
GET  /api/radio/status
     → 현재 라디오 상태 전체 (설정 + 플레이리스트 + 날씨 + 멘트)
     → 페이지 최초 로드 시 1회 호출

GET  /api/radio/weather
     → 날씨 정보만 (API 모드 시 캐시 or 갱신)
     → 위젯 내부에서 1시간 간격 polling (API 모드일 때만)

POST /api/admin/radio/config
     → 라디오 설정 변경 (관리자)

POST /api/admin/radio/playlist
     → 플레이리스트 CRUD (관리자)

POST /api/admin/radio/ment
     → 멘트 CRUD (관리자)
```

---

## 6. 관리자 UI

### 라디오 관리 페이지 구성

**기본 설정 섹션**
- 라디오 위젯 ON/OFF 토글
- 재생 모드: 순차 / 랜덤

**플레이리스트 섹션**
- 유튜브 URL 입력 → 자동으로 video ID 파싱 + 제목 입력
- 드래그앤드롭 또는 번호로 순서 변경
- 개별 곡 활성/비활성 토글
- 삭제

**날씨 설정 섹션**
- 모드 선택: API 자동 / 수동 설정 (라디오 버튼)
- API 모드 시: 도시명 입력 (예: "Seoul") + API 키 입력
- 수동 모드 시: 기온 입력 + 날씨 타입 셀렉트박스

**멘트 설정 섹션**
- 멘트 목록 (추가/수정/삭제)
- 로테이션 모드: 순차 / 랜덤
- 교체 간격 설정 (초 단위, 기본 12초)

---

## 7. 주의사항

### 유튜브 관련
- 유튜브 영상이 삭제/비공개 전환되면 재생 실패 → `onError` 이벤트에서 다음 곡으로 자동 스킵 처리
- 일부 영상은 외부 재생이 차단되어 있음 → 등록 시 체크하거나, 재생 실패 시 스킵
- 유튜브 API 로드 실패 시 (네트워크 문제 등) 위젯이 깨지지 않도록 graceful 처리

### 성능
- `/api/radio/status`는 페이지 최초 로드 시 1회만 호출
- 날씨 API는 서버 캐시로 처리, 클라이언트가 직접 호출하지 않음
- 멘트 로테이션은 순수 클라이언트 로직, 서버 부하 없음
- 유튜브 iframe은 1개만 유지 (곡 전환 시 새 iframe 생성 금지)

### SPA 연동 체크포인트
- [ ] 모든 페이지 전환 시 사이드바 DOM 유지되는지 확인
- [ ] 사이드바 리렌더 되는 예외 페이지가 있는지 확인
- [ ] 예외 페이지가 있다면: 라디오 상태(재생 위치, 볼륨)를 sessionStorage에 백업 → 복원 로직 추가
- [ ] 모바일에서 사이드바가 숨겨질 때 iframe 처리 확인

---

## 8. 구현 우선순위

### Phase 1 (1차 구현, 2~3일)

1. DB 테이블 생성
2. 관리자 UI (설정 + 플레이리스트 + 멘트 CRUD)
3. 사이드바 라디오 위젯 HTML/CSS
4. 유튜브 IFrame API 연동 (재생/정지/볼륨/영상 토글)
5. 플레이리스트 순차/랜덤 재생
6. 날씨 표시 (수동 모드 우선)
7. 멘트 정적 로테이션

### Phase 1.5 (여유 있을 때, 반나절)

8. OpenWeatherMap API 연동 + 서버 캐시
9. 날씨 모드 전환 (API/수동)

### Phase 2 (실시간 인프라 연동 시)

10. 멘트 실시간 반영 (WebSocket/Supabase)
11. 실시간 멘트 전용 스타일
12. 관리자 실시간 멘트 입력 UI
