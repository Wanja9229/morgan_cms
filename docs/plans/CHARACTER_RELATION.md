# 캐릭터 관계 시스템 기획

## 핵심 컨셉

캐릭터 간 관계를 **신청→승인** 방식으로 맺고, vis.js Network로 시각화.
아보카도의 1:1 커플 시스템을 **다대다 관계망**으로 확장한 것.
관계명, 아이콘, 방향성을 자유롭게 설정하여 커플·라이벌·가족·사제 등 모든 관계 표현.

---

## 흐름

```
A 유저 → B 캐릭터에게 관계 신청 (관계명 + 아이콘 + 메모 입력)
                    ↓
          B 유저에게 알림 발송
                    ↓
     B 유저 승인 → 관계 성립 (양방향 표시)
     B 유저 거절 → 삭제
                    ↓
         관계도 페이지에서 vis.js 네트워크로 시각화
```

### 관계의 두 가지 시점

관계는 **양쪽이 각자의 시점**을 가진다.

예시: A캐릭터 ↔ B캐릭터
- A 시점: "첫사랑" (♡ 아이콘)
- B 시점: "귀찮은 소꿉친구" (💢 아이콘)

신청 시 A가 자기 쪽 라벨을 입력하고, B가 승인할 때 B 쪽 라벨을 입력한다.
한쪽만 입력하면 양쪽 동일 라벨로 표시 (편의).

---

## 관계 아이콘 시스템

### 기본 제공 아이콘 세트

관리자가 ON/OFF 가능. 유저는 관계 설정 시 아이콘 팔레트에서 선택.

| 카테고리 | 아이콘 | 의미 | 선 색상 기본값 |
|----------|--------|------|---------------|
| 애정 | ♡ | 연인/커플 | #e74c3c (빨강) |
| 애정 | ♡♡ | 깊은 사랑 | #c0392b (진빨강) |
| 우정 | ☆ | 친구 | #3498db (파랑) |
| 우정 | ★ | 절친/베프 | #2980b9 (진파랑) |
| 가족 | 🏠 | 가족 | #27ae60 (초록) |
| 적대 | ⚔ | 라이벌/적 | #e67e22 (주황) |
| 사제 | 📖 | 스승/제자 | #9b59b6 (보라) |
| 기타 | 🔗 | 동료/지인 | #95a5a6 (회색) |
| 기타 | ❓ | 복잡한 관계 | #f39c12 (노랑) |
| 커스텀 | (관리자 추가) | (관리자 정의) | (관리자 설정) |

### 아이콘 표시 위치

vis.js에서 엣지(연결선) 위에 라벨과 함께 아이콘 표시.

```
[A 캐릭터] ──── ♡ 첫사랑 / 💢 귀찮은 놈 ──── [B 캐릭터]
```

- 엣지 중앙: 아이콘
- 엣지 라벨: A쪽 관계명 / B쪽 관계명 (다르면 슬래시 구분)
- 엣지 색상: 아이콘 카테고리 기본색 (커스텀 가능)
- 엣지 굵기: 일반 2px, 커플 관계 3px

### 관리자 커스텀

관리자 페이지에서:
- 기본 아이콘 세트 ON/OFF
- 커스텀 아이콘 추가 (이모지 또는 업로드 이미지 16x16)
- 카테고리별 기본 색상 변경
- 세계관에 맞는 관계 카테고리 추가 (예: "혈족", "계약자", "팩 멤버")

---

## 관계 신청/승인 프로세스

### 1단계: 신청

캐릭터 프로필 페이지 또는 관계 관리 페이지에서 신청.

```
┌─────────────────────────────────────────┐
│  관계 신청                               │
├─────────────────────────────────────────┤
│                                         │
│  내 캐릭터:  [드롭다운: 내 캐릭터 목록]    │
│  대상:      [캐릭터 검색 (자동완성)]       │
│                                         │
│  ── 내 쪽 설정 ──                        │
│  관계명:    [        첫사랑         ]     │
│  아이콘:    ♡ ☆ ⚔ 📖 🔗 ❓ ...  (팔레트) │
│  한줄메모:  [  어릴 때부터 좋아했다   ]     │
│                                         │
│  ── 상대 쪽 (선택) ──                    │
│  상대 관계명: [                    ]      │
│  (비워두면 상대가 승인 시 직접 입력)       │
│                                         │
│         [ 신청하기 ]                      │
│                                         │
└─────────────────────────────────────────┘
```

### 2단계: 알림

대상 캐릭터 소유주에게 알림 발송 (기존 알림 시스템 연동).

```
🔔 [다니엘]이(가) [레이첼]에게 관계를 신청했습니다.
   관계: ♡ 첫사랑
   [승인] [거절] [상세보기]
```

### 3단계: 승인

```
┌─────────────────────────────────────────┐
│  관계 승인                               │
├─────────────────────────────────────────┤
│                                         │
│  신청자: 다니엘 → 레이첼                  │
│  상대 관계명: ♡ 첫사랑                    │
│  상대 메모: "어릴 때부터 좋아했다"         │
│                                         │
│  ── 내 쪽 설정 ──                        │
│  관계명:    [ 귀찮은 소꿉친구      ]       │
│  아이콘:    ♡ ☆ ⚔ 📖 🔗 ❓ ...          │
│  한줄메모:  [ 자꾸 따라다녀서 귀찮다 ]     │
│                                         │
│      [ 승인 ]  [ 거절 ]                  │
│                                         │
└─────────────────────────────────────────┘
```

### 4단계: 관계 수정/해제

성립된 관계는 **어느 쪽이든** 자기 쪽 라벨/아이콘/메모 수정 가능.
관계 해제는 **어느 쪽이든** 가능하며, 해제 시 상대에게 알림.
(관리자도 강제 해제 가능)

---

## vis.js Network 시각화

### 표시 모드

#### A. 개인 관계도 (캐릭터 프로필 페이지)

해당 캐릭터를 중심으로 1~2depth 관계만 표시.

```
                    [소피아]
                   ☆ 친구 /
                  /
[빅터] ── ⚔ 라이벌 ── [다니엘] ── ♡ 첫사랑 ── [레이첼]
                        |                        |
                   📖 스승 ──── [앨리스]     🏠 자매 ──── [클레어]
```

- 중심 캐릭터: 크게, 고정 위치
- 직접 관계: 1depth, 중간 크기
- 간접 관계: 2depth, 작게, 흐릿하게 (옵션)
- 클릭 시 해당 캐릭터의 관계도 페이지로 이동

#### B. 전체 관계도 (커뮤니티 페이지)

커뮤니티 전체 캐릭터의 관계망을 한눈에.

- vis.js physics 시뮬레이션으로 자동 배치
- 관계 있는 캐릭터끼리 가깝게 클러스터링
- 고립된 캐릭터는 외곽에 배치
- 줌/패닝/드래그 지원
- 세력(faction) 별로 노드 배경색 구분 (옵션)

#### C. 세력 관계도 (옵션)

캐릭터 단위가 아니라 세력 단위로 집계.
세력 간 관계 수를 엣지 굵기로 표현.

### 노드 디자인

```
┌──────────┐
│ [썸네일]  │  ← 캐릭터 프로필 이미지 (원형 클리핑)
│  다니엘   │  ← 캐릭터 이름
│ 뱀파이어  │  ← 종족/세력 (작은 글씨, 옵션)
└──────────┘
```

vis.js 노드 설정:
```javascript
{
    id: ch_id,
    label: '다니엘',
    title: '다니엘\n뱀파이어 · 야경단',  // 호버 툴팁
    shape: 'circularImage',
    image: '/path/to/thumb.jpg',
    size: 30,                              // 중심 캐릭터는 45
    borderWidth: 2,
    color: {
        border: '#8a0000',                 // 세력 색상
        background: '#1a1a1a'
    },
    font: {
        color: '#ffffff',
        size: 12
    }
}
```

### 엣지 디자인

```javascript
{
    from: ch_id_a,
    to: ch_id_b,
    label: '♡ 첫사랑',                     // 아이콘 + A쪽 관계명
    title: 'A→B: ♡ 첫사랑\nB→A: 💢 귀찮은 소꿉친구',  // 호버 상세
    color: { color: '#e74c3c' },           // 카테고리 색상
    width: 2,
    font: {
        color: '#ffffff',
        size: 10,
        strokeWidth: 3,
        strokeColor: '#000000'             // 배경 대비 가독성
    },
    smooth: {
        type: 'curvedCW',                  // 곡선 (겹치는 엣지 구분)
        roundness: 0.2
    }
}
```

양쪽 관계명이 다를 경우 엣지 라벨:
- 짧으면: `♡ 첫사랑 / 💢 귀찮은 놈`
- 길면: 호버 시 툴팁으로 상세 표시, 라벨은 아이콘만

### 인터랙션

| 동작 | 결과 |
|------|------|
| 노드 클릭 | 캐릭터 프로필 팝업 or 해당 캐릭터 중심 관계도 전환 |
| 노드 더블클릭 | 캐릭터 프로필 페이지로 이동 |
| 엣지 클릭 | 관계 상세 팝업 (양쪽 관계명, 메모, 설정일, 수정 버튼) |
| 마우스 휠 | 줌 인/아웃 |
| 드래그 (빈 공간) | 패닝 |
| 드래그 (노드) | 노드 위치 이동 |
| 호버 (노드) | 캐릭터명, 종족, 세력 툴팁 |
| 호버 (엣지) | 양쪽 관계명, 메모 툴팁 |

### 필터/검색

```
┌────────────────────────────────────────────────┐
│ 🔍 캐릭터 검색 [          ]                     │
│                                                │
│ 필터: [♡ 애정] [☆ 우정] [🏠 가족] [⚔ 적대]     │
│       [📖 사제] [🔗 기타] [전체]                │
│                                                │
│ 세력: [뱀파이어] [라이칸] [헌터] [전체]           │
│                                                │
│ 표시: ○ 내 캐릭터 중심  ○ 전체 관계도            │
└────────────────────────────────────────────────┘
```

필터 선택 시 해당 카테고리 엣지만 표시, 나머지는 투명하게.

---

## DB 스키마

### mg_relation_icon — 관계 아이콘 정의

| 컬럼 | 타입 | 설명 |
|------|------|------|
| ri_id | int PK AUTO_INCREMENT | 아이콘 ID |
| ri_category | varchar(30) | 카테고리 (love, friendship, family, rival, mentor, etc) |
| ri_icon | varchar(20) | 이모지 또는 아이콘 코드 |
| ri_label | varchar(50) | 표시명 (연인, 친구, 가족...) |
| ri_color | varchar(7) | 엣지 기본 색상 (#e74c3c) |
| ri_width | tinyint DEFAULT 2 | 엣지 기본 굵기 |
| ri_image | varchar(200) NULL | 커스텀 이미지 경로 (이모지 대신) |
| ri_order | int DEFAULT 0 | 팔레트 정렬순 |
| ri_active | tinyint DEFAULT 1 | 사용 여부 |

### mg_relation — 관계 데이터 (핵심)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| cr_id | int PK AUTO_INCREMENT | 관계 ID |
| ch_id_a | int | 신청자 캐릭터 ID |
| ch_id_b | int | 대상 캐릭터 ID |
| ri_id | int | 아이콘 ID (mg_relation_icon) |
| cr_label_a | varchar(50) | A쪽 관계명 ("첫사랑") |
| cr_label_b | varchar(50) NULL | B쪽 관계명 ("귀찮은 소꿉친구"), NULL이면 A와 동일 |
| cr_icon_a | varchar(20) NULL | A쪽 개별 아이콘 (NULL이면 ri_id 기본값) |
| cr_icon_b | varchar(20) NULL | B쪽 개별 아이콘 |
| cr_memo_a | text NULL | A쪽 관계 메모 |
| cr_memo_b | text NULL | B쪽 관계 메모 |
| cr_color | varchar(7) NULL | 커스텀 엣지 색상 (NULL이면 아이콘 기본색) |
| cr_status | enum('pending','active','rejected') DEFAULT 'pending' | 상태 |
| cr_datetime | datetime | 신청일 |
| cr_accept_datetime | datetime NULL | 승인일 |

**인덱스:**
- `idx_relation_a` (ch_id_a, cr_status)
- `idx_relation_b` (ch_id_b, cr_status)
- `UNIQUE idx_relation_pair` (ch_id_a, ch_id_b) — 같은 쌍 중복 방지

**방향성 규칙:**
- ch_id_a < ch_id_b로 정규화 저장 (중복 방지)
- 신청자가 누구였는지는 별도 처리하지 않음 (cr_label_a/b로 구분)
- 또는 신청자 필드를 따로 두되, 정규화는 유지

### 기존 테이블 연동

| 테이블 | 연동 | 설명 |
|--------|------|------|
| mg_character | ch_id 참조 | 캐릭터 썸네일, 이름, 세력 |
| mg_notification | 알림 발송 | type: 'relation_request', 'relation_accepted' |
| mg_faction | 세력 색상 | 관계도 노드 배경색 |

---

## 관리자 기능

### 관계 아이콘 관리

| 기능 | 설명 |
|------|------|
| 기본 아이콘 ON/OFF | 세계관에 안 맞는 카테고리 비활성화 |
| 커스텀 아이콘 추가 | 이모지 입력 또는 16x16 이미지 업로드 |
| 카테고리 추가 | "혈족", "팩 멤버" 등 세계관 맞춤 |
| 색상/굵기 변경 | 카테고리별 기본값 수정 |

### 관계 관리

| 기능 | 설명 |
|------|------|
| 전체 관계 목록 | 필터: 상태별, 카테고리별, 캐릭터별 |
| 강제 해제 | 부적절한 관계 관리자 삭제 |
| 강제 승인 | NPC 관계 등 관리자가 직접 설정 |
| 관계 통계 | 카테고리별 분포, 가장 많은 관계 캐릭터 TOP 10 |

### 관계도 설정

| 설정 | 기본값 | 설명 |
|------|-------|------|
| 전체 관계도 공개 | ON | 비로그인 열람 가능 여부 |
| 개인 관계도 depth | 2 | 프로필에서 몇 단계까지 표시 |
| 세력 색상 표시 | ON | 노드에 세력 배경색 |
| 물리 시뮬레이션 | ON | vis.js physics (OFF면 수동 배치) |
| 최대 노드 수 | 200 | 성능 제한 (초과 시 페이지네이션) |

---

## 프론트 구현 가이드

### vis.js CDN

```html
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<link href="https://unpkg.com/vis-network/styles/vis-network.min.css" rel="stylesheet">
```

### 기본 초기화

```javascript
// 데이터 로드 (AJAX)
$.getJSON('/ajax/relation_graph.php', { ch_id: centerCharId, depth: 2 }, function(data) {
    
    const nodes = new vis.DataSet(data.nodes.map(n => ({
        id: n.ch_id,
        label: n.ch_name,
        title: n.ch_name + '\n' + n.faction_name,
        shape: 'circularImage',
        image: n.ch_thumb || '/img/default_thumb.png',
        size: n.ch_id === centerCharId ? 45 : 30,
        borderWidth: n.ch_id === centerCharId ? 4 : 2,
        color: {
            border: n.faction_color || '#333',
            background: '#1a1a1a',
            highlight: { border: '#8a0000' }
        },
        font: { color: '#fff', size: 12 }
    })));

    const edges = new vis.DataSet(data.edges.map(e => ({
        from: e.ch_id_a,
        to: e.ch_id_b,
        label: e.icon + ' ' + e.label_display,
        title: buildEdgeTooltip(e),
        color: { color: e.edge_color, highlight: '#fff' },
        width: e.edge_width,
        font: {
            color: '#fff',
            size: 10,
            strokeWidth: 3,
            strokeColor: '#000'
        },
        smooth: { type: 'curvedCW', roundness: 0.15 }
    })));

    const container = document.getElementById('relation-graph');
    const network = new vis.Network(container, { nodes, edges }, {
        physics: {
            barnesHut: {
                gravitationalConstant: -3000,
                centralGravity: 0.3,
                springLength: 200,
                springConstant: 0.04,
                damping: 0.09
            },
            stabilization: { iterations: 150 }
        },
        interaction: {
            hover: true,
            tooltipDelay: 200,
            zoomView: true,
            dragView: true
        },
        nodes: {
            borderWidthSelected: 4,
            chosen: true
        }
    });

    // 이벤트
    network.on('click', function(params) {
        if (params.nodes.length > 0) {
            showCharacterPopup(params.nodes[0]);
        } else if (params.edges.length > 0) {
            showRelationPopup(params.edges[0]);
        }
    });

    network.on('doubleClick', function(params) {
        if (params.nodes.length > 0) {
            location.href = '/member/viewer.php?ch_id=' + params.nodes[0];
        }
    });
});
```

### 백엔드 API (relation_graph.php)

```
GET /ajax/relation_graph.php
    ?ch_id=123          (선택: 중심 캐릭터, 없으면 전체)
    &depth=2            (1~3, 기본 2)
    &category=love,family  (선택: 필터)
    &faction_id=5       (선택: 세력 필터)
```

응답:
```json
{
    "nodes": [
        {
            "ch_id": 123,
            "ch_name": "다니엘",
            "ch_thumb": "/data/character/thumb_123.jpg",
            "faction_name": "야경단",
            "faction_color": "#8a0000"
        }
    ],
    "edges": [
        {
            "cr_id": 45,
            "ch_id_a": 123,
            "ch_id_b": 456,
            "icon": "♡",
            "label_a": "첫사랑",
            "label_b": "귀찮은 소꿉친구",
            "label_display": "첫사랑 / 귀찮은 소꿉친구",
            "edge_color": "#e74c3c",
            "edge_width": 2,
            "memo_a": "어릴 때부터 좋아했다",
            "memo_b": "자꾸 따라다녀서 귀찮다",
            "category": "love"
        }
    ]
}
```

### 성능 고려

| 규모 | 노드 수 | 대응 |
|------|---------|------|
| 소규모 | ~50 | physics ON, 문제 없음 |
| 중규모 | 50~200 | physics 안정화 후 정지 (`stabilization: true`) |
| 대규모 | 200+ | 페이지네이션 또는 depth 제한, physics OFF + 고정 좌표 |

대규모 커뮤니티 대응:
- 전체 관계도는 세력 단위 축소 뷰 제공
- 캐릭터 단위는 depth 1~2로 제한
- 관리자가 최대 노드 수 설정 가능

---

## 이전 프로젝트(VN엔진) 코드 재활용

### 가져올 수 있는 것

| 항목 | 이전 | Morgan 적용 |
|------|------|------------|
| 관계 CRUD | relation_list/update/delete.php | 구조 동일, 승인 프로세스 추가 |
| 캐릭터 검색 자동완성 | `get_ajax_character()` | 그대로 사용 가능 |
| 호감도 | 0~5 하트 | 아이콘 시스템으로 대체 |
| 관계 링크 | rm_link (URL 목록) | 유지 (역극/창작물 링크) |
| 관계 메모 | rm_memo (텍스트) | 양쪽 메모로 확장 |
| 커플 시스템 | couple 테이블 (별도) | 관계 카테고리 "커플"로 통합 |

### 바뀌는 것

| 항목 | 이전 | Morgan |
|------|------|--------|
| 관계 방식 | 일방적 등록 | 신청→승인 |
| 시각화 | 리스트 출력 | vis.js Network |
| 아이콘 | 없음 | 카테고리별 아이콘 팔레트 |
| 양방향 라벨 | 없음 (A 시점만) | A/B 각자 라벨 |
| 커플 | 별도 테이블+D-Day | 관계 카테고리 통합 |

---

## 구현 우선순위

```
[1단계] 관계 CRUD + 신청/승인 (1.5일)
├── mg_relation, mg_relation_icon 테이블 생성
├── 기본 아이콘 세트 INSERT
├── 신청 폼 (캐릭터 검색, 아이콘 팔레트, 관계명)
├── 승인/거절 처리
├── 알림 시스템 연동 (type: relation_request, relation_accepted)
└── 관계 수정/해제

[2단계] vis.js 개인 관계도 (1일)
├── relation_graph.php API
├── 캐릭터 프로필에 관계도 탭 추가
├── depth 1~2 노드/엣지 로드
├── 노드 클릭/더블클릭 이벤트
└── 모바일 터치 대응

[3단계] 전체 관계도 페이지 (0.5일)
├── 커뮤니티 메뉴에 "관계도" 페이지 추가
├── 필터 (카테고리, 세력)
├── 검색 (캐릭터명 → 포커스 이동)
└── 성능 제한 (최대 노드 수)

[4단계] 관리자 기능 (1일)
├── 아이콘 관리 CRUD
├── 관계 목록/강제 해제
├── 관계도 설정
└── 통계 (카테고리 분포, TOP 10)
```

**총 예상: 4일**

---

*버전: 1.0*
*관련 문서: PIONEER.md (세력 시스템), Morgan CMS 캐릭터 모듈*
*참조: F:\projects\vn_engine\mypage\character\relation_*.php*
*라이브러리: vis.js Network (https://visjs.github.io/vis-network/docs/network/)*
