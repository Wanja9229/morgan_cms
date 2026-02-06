# API 설계

> Morgan Edition - RESTful API 설계
> fetch 기반 비동기 통신용

> ⚠️ **스키마 참조**: 테이블 구조는 [PLAN_DB.md](./PLAN_DB.md) 참조
> **아키텍처**: 세미 SPA (헤더/푸터 고정, 콘텐츠만 fetch 통신)

---

## 개요

- **방식**: RESTful API
- **인증**: 세션 기반 (그누보드 세션 활용)
- **응답 형식**: JSON
- **Base URL**: `/api`
- **아키텍처**: 세미 SPA (헤더/푸터 고정, 콘텐츠 영역만 fetch 통신)

---

## 0. 프론트엔드 아키텍처

### 0.1 세미 SPA 구조

```
┌─────────────────────────────────────────────────────────────┐
│  [헤더] - PHP 렌더링 (고정)                                  │
│  로고, 네비게이션, 유저 정보, 알림 아이콘                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  [콘텐츠 영역] - fetch API로 동적 로딩                       │
│                                                             │
│  • 페이지 이동 시 전체 새로고침 없이 콘텐츠만 교체            │
│  • URL은 history.pushState로 변경 (뒤로가기 지원)            │
│  • 초기 로딩은 PHP SSR, 이후 네비게이션은 fetch              │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [푸터] - PHP 렌더링 (고정)                                  │
└─────────────────────────────────────────────────────────────┘
```

### 0.2 페이지 로딩 방식

**초기 접속 (SSR)**
```
1. 사용자가 URL 직접 접속 (/board/notice)
2. PHP가 전체 페이지 렌더링 (헤더 + 콘텐츠 + 푸터)
3. JavaScript 초기화
```

**내부 네비게이션 (CSR)**
```
1. 사용자가 메뉴 클릭
2. JavaScript가 클릭 이벤트 가로채기 (preventDefault)
3. history.pushState로 URL 변경
4. fetch로 API 호출
5. 콘텐츠 영역만 교체
6. 헤더/푸터 유지
```

### 0.3 API 응답 타입

**데이터 전용 (JSON)**
- 대부분의 API 엔드포인트
- 프론트엔드에서 템플릿으로 렌더링

```javascript
// 예: 게시판 목록
fetch('/api/board/notice')
  .then(res => res.json())
  .then(data => {
    renderBoardList(data.data.items);
  });
```

**HTML 프래그먼트 (선택적)**
- 복잡한 UI의 경우 서버에서 HTML 반환 가능
- `Accept: text/html` 헤더로 구분

```javascript
// 예: 복잡한 위젯
fetch('/api/board/notice', {
  headers: { 'Accept': 'text/html' }
})
  .then(res => res.text())
  .then(html => {
    document.getElementById('content').innerHTML = html;
  });
```

### 0.4 라우터 구조

```javascript
// routes.js
const routes = {
  '/': { api: '/api/dashboard', template: 'dashboard' },
  '/board/:table': { api: '/api/board/{table}', template: 'board-list' },
  '/board/:table/:id': { api: '/api/board/{table}/{id}', template: 'board-view' },
  '/rp': { api: '/api/rp', template: 'rp-list' },
  '/rp/:id': { api: '/api/rp/{id}', template: 'rp-view' },
  '/shop': { api: '/api/shop', template: 'shop' },
  '/inventory': { api: '/api/shop/inventory', template: 'inventory' },
  '/character': { api: '/api/character/my', template: 'my-characters' },
  '/character/:id': { api: '/api/character/{id}', template: 'character-view' },
  // ...
};
```

### 0.5 헤더 실시간 업데이트

헤더의 동적 요소는 별도 API로 주기적 업데이트:

```javascript
// 30초마다 헤더 정보 갱신
setInterval(() => {
  fetch('/api/auth/me')
    .then(res => res.json())
    .then(data => {
      updateHeaderPoint(data.data.mb_point);
      updateNotificationBadge(data.data.unread_count);
    });
}, 30000);
```

### 0.6 로딩 상태 처리

```javascript
async function navigateTo(url) {
  // 1. 로딩 표시
  showContentLoader();

  // 2. URL 변경
  history.pushState({}, '', url);

  // 3. API 호출
  const route = matchRoute(url);
  const response = await fetch(route.api);
  const data = await response.json();

  // 4. 콘텐츠 렌더링
  renderTemplate(route.template, data);

  // 5. 로딩 숨김
  hideContentLoader();
}
```

---

## 1. 공통 사항

### 1.1 응답 형식

**성공 응답**
```json
{
  "success": true,
  "data": { ... },
  "message": "성공 메시지"
}
```

**에러 응답**
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "에러 메시지"
  }
}
```

### 1.2 공통 에러 코드

| 코드 | HTTP | 설명 |
|------|------|------|
| AUTH_REQUIRED | 401 | 로그인 필요 |
| FORBIDDEN | 403 | 권한 없음 |
| NOT_FOUND | 404 | 리소스 없음 |
| VALIDATION_ERROR | 422 | 입력값 검증 실패 |
| SERVER_ERROR | 500 | 서버 에러 |

### 1.3 페이지네이션

```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total_items": 150,
      "total_pages": 8
    }
  }
}
```

---

## 2. 인증 API

### 2.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| POST | /api/auth/login | 로그인 |
| POST | /api/auth/logout | 로그아웃 |
| GET | /api/auth/check | 세션 확인 |
| GET | /api/auth/me | 현재 유저 정보 |

### 2.2 상세

**POST /api/auth/login**
```json
// Request
{
  "mb_id": "user123",
  "mb_password": "password"
}

// Response
{
  "success": true,
  "data": {
    "mb_id": "user123",
    "mb_nick": "닉네임",
    "mb_level": 2,
    "mb_point": 1234,
    "is_admin": false,
    "is_staff": true
  }
}
```

**GET /api/auth/me**
```json
// Response
{
  "success": true,
  "data": {
    "mb_id": "user123",
    "mb_nick": "닉네임",
    "mb_level": 2,
    "mb_point": 1234,
    "is_admin": false,
    "is_staff": true,
    "staff_permissions": ["member", "character", "board"],
    "main_character": {
      "ch_id": 1,
      "ch_name": "캐릭터명",
      "ch_thumbnail": "/upload/character/1/thumb.jpg"
    }
  }
}
```

---

## 3. 회원 API

### 3.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| POST | /api/member/register | 회원가입 |
| GET | /api/member/{mb_id} | 회원 정보 조회 |
| PUT | /api/member/{mb_id} | 회원 정보 수정 |
| PUT | /api/member/{mb_id}/password | 비밀번호 변경 |
| PUT | /api/member/{mb_id}/nick | 닉네임 변경 |

### 3.2 상세

**POST /api/member/register**
```json
// Request
{
  "mb_id": "newuser",
  "mb_password": "password123",
  "mb_password_confirm": "password123",
  "mb_nick": "새유저"
}

// Response
{
  "success": true,
  "message": "회원가입이 완료되었습니다."
}
```

---

## 4. 캐릭터 API

### 4.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/character | 캐릭터 목록 (전체) |
| GET | /api/character/my | 내 캐릭터 목록 |
| GET | /api/character/{ch_id} | 캐릭터 상세 |
| POST | /api/character | 캐릭터 생성 |
| PUT | /api/character/{ch_id} | 캐릭터 수정 |
| DELETE | /api/character/{ch_id} | 캐릭터 삭제 |
| PUT | /api/character/{ch_id}/main | 대표 캐릭터 설정 |
| GET | /api/character/field | 프로필 양식 조회 |

### 4.2 상세

**GET /api/character/my**
```json
// Response
{
  "success": true,
  "data": {
    "characters": [
      {
        "ch_id": 1,
        "ch_name": "캐릭터1",
        "ch_thumbnail": "/upload/character/1/thumb.jpg",
        "ch_status": "approved",
        "ch_main": true,
        "side_name": "세력A",
        "class_name": "종족B"
      },
      {
        "ch_id": 2,
        "ch_name": "캐릭터2",
        "ch_thumbnail": "/upload/character/2/thumb.jpg",
        "ch_status": "pending",
        "ch_main": false,
        "side_name": null,
        "class_name": null
      }
    ]
  }
}
```

**POST /api/character**
```json
// Request
{
  "ch_name": "새캐릭터",
  "ch_thumbnail": "base64...",
  "side_id": 1,
  "class_id": 2,
  "profile_fields": {
    "1": "필드1 값",
    "2": "필드2 값",
    "3": "필드3 값"
  }
}

// Response
{
  "success": true,
  "data": {
    "ch_id": 3,
    "ch_status": "pending"
  },
  "message": "캐릭터가 등록되었습니다. 승인 대기 중입니다."
}
```

**GET /api/character/field**
```json
// Response
{
  "success": true,
  "data": {
    "fields": [
      {
        "pf_id": 1,
        "pf_name": "이름",
        "pf_type": "text",
        "pf_required": true,
        "pf_option": null
      },
      {
        "pf_id": 2,
        "pf_name": "나이",
        "pf_type": "text",
        "pf_required": false,
        "pf_option": null
      },
      {
        "pf_id": 3,
        "pf_name": "성별",
        "pf_type": "select",
        "pf_required": true,
        "pf_option": ["남성", "여성", "기타"]
      }
    ],
    "sides": [
      { "side_id": 1, "side_name": "세력A" },
      { "side_id": 2, "side_name": "세력B" }
    ],
    "classes": [
      { "class_id": 1, "class_name": "종족A" },
      { "class_id": 2, "class_name": "종족B" }
    ],
    "config": {
      "side_use": true,
      "side_required": false,
      "side_label": "세력",
      "class_use": true,
      "class_required": true,
      "class_label": "종족"
    }
  }
}
```

---

## 5. 게시판 API

### 5.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/board/{bo_table} | 글 목록 |
| GET | /api/board/{bo_table}/{wr_id} | 글 상세 |
| POST | /api/board/{bo_table} | 글 작성 |
| PUT | /api/board/{bo_table}/{wr_id} | 글 수정 |
| DELETE | /api/board/{bo_table}/{wr_id} | 글 삭제 |
| POST | /api/board/{bo_table}/{wr_id}/comment | 댓글 작성 |
| DELETE | /api/board/{bo_table}/{wr_id}/comment/{comment_id} | 댓글 삭제 |
| POST | /api/board/{bo_table}/{wr_id}/good | 추천 |

### 5.2 상세

**GET /api/board/{bo_table}**
```
Query Parameters:
- page: 페이지 번호 (기본 1)
- per_page: 페이지당 개수 (기본 20)
- search_field: 검색 필드 (wr_subject, wr_content, wr_name, mb_id)
- search_text: 검색어
```

```json
// Response
{
  "success": true,
  "data": {
    "board": {
      "bo_table": "notice",
      "bo_subject": "공지사항",
      "bo_skin": "list",
      "write_level": 10,
      "comment_level": 1
    },
    "items": [
      {
        "wr_id": 1,
        "wr_subject": "제목",
        "wr_content_summary": "내용 요약...",
        "wr_name": "작성자",
        "wr_datetime": "2026-02-03 10:30:00",
        "wr_hit": 123,
        "wr_good": 5,
        "wr_comment": 3,
        "wr_is_secret": false,
        "thumbnail": "/upload/board/notice/thumb_1.jpg"
      }
    ],
    "pagination": { ... }
  }
}
```

**POST /api/board/{bo_table}**
```json
// Request
{
  "wr_subject": "제목",
  "wr_content": "내용",
  "wr_is_secret": false,
  "wr_is_anonymous": false,
  "files": ["base64...", "base64..."]
}

// Response
{
  "success": true,
  "data": {
    "wr_id": 10
  },
  "message": "글이 등록되었습니다."
}
```

---

## 6. 역극 API

### 6.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/rp | 역극 목록 |
| GET | /api/rp/{rt_id} | 역극 상세 (댓글 포함) |
| POST | /api/rp | 역극 생성 (판 세우기) |
| PUT | /api/rp/{rt_id} | 역극 수정 |
| DELETE | /api/rp/{rt_id} | 역극 삭제 |
| PUT | /api/rp/{rt_id}/status | 역극 상태 변경 |
| POST | /api/rp/{rt_id}/reply | 이음 (댓글) |
| DELETE | /api/rp/{rt_id}/reply/{rr_id} | 이음 삭제 |
| GET | /api/rp/{rt_id}/members | 참여자 목록 |
| GET | /api/rp/check-create | 판 세우기 가능 여부 확인 |

### 6.2 상세

**GET /api/rp**
```
Query Parameters:
- page: 페이지 번호
- status: 상태 필터 (open/closed/all)
- my: 내 참여 역극만 (true/false)
```

```json
// Response
{
  "success": true,
  "data": {
    "items": [
      {
        "rt_id": 1,
        "rt_title": "역극 제목",
        "rt_status": "open",
        "rt_datetime": "2026-02-03 10:00:00",
        "rt_update": "2026-02-03 15:30:00",
        "rt_reply_count": 24,
        "rt_max_member": 5,
        "owner": {
          "mb_id": "user123",
          "ch_id": 1,
          "ch_name": "캐릭터명",
          "ch_thumbnail": "/upload/character/1/thumb.jpg"
        },
        "members_count": 3,
        "preview_members": [
          { "ch_thumbnail": "/upload/character/1/thumb.jpg" },
          { "ch_thumbnail": "/upload/character/2/thumb.jpg" },
          { "ch_thumbnail": "/upload/character/3/thumb.jpg" }
        ]
      }
    ],
    "pagination": { ... }
  }
}
```

**GET /api/rp/{rt_id}**
```json
// Response
{
  "success": true,
  "data": {
    "thread": {
      "rt_id": 1,
      "rt_title": "역극 제목",
      "rt_content": "시작글 내용...",
      "rt_image": "/upload/rp/1/image.jpg",
      "rt_status": "open",
      "rt_datetime": "2026-02-03 10:00:00",
      "rt_max_member": 5,
      "owner": {
        "mb_id": "user123",
        "ch_id": 1,
        "ch_name": "캐릭터명",
        "ch_thumbnail": "/upload/character/1/thumb.jpg"
      },
      "is_owner": false,
      "is_member": true
    },
    "replies": [
      {
        "rr_id": 1,
        "rr_content": "이음 내용...",
        "rr_image": null,
        "rr_datetime": "2026-02-03 10:30:00",
        "is_owner": false,
        "author": {
          "mb_id": "user456",
          "ch_id": 2,
          "ch_name": "참여캐릭터",
          "ch_thumbnail": "/upload/character/2/thumb.jpg"
        }
      }
    ],
    "members": [
      {
        "mb_id": "user123",
        "ch_id": 1,
        "ch_name": "캐릭터명",
        "ch_thumbnail": "/upload/character/1/thumb.jpg",
        "reply_count": 12,
        "is_owner": true
      }
    ],
    "my_characters": [
      { "ch_id": 3, "ch_name": "내캐릭터", "ch_thumbnail": "..." }
    ]
  }
}
```

**POST /api/rp**
```json
// Request
{
  "rt_title": "역극 제목",
  "rt_content": "시작글 내용",
  "rt_image": "base64...",
  "ch_id": 1,
  "rt_max_member": 5
}

// Response
{
  "success": true,
  "data": {
    "rt_id": 10
  },
  "message": "역극이 생성되었습니다."
}
```

**GET /api/rp/check-create**
```json
// Response (판 세우기 가능)
{
  "success": true,
  "data": {
    "can_create": true
  }
}

// Response (판 세우기 불가)
{
  "success": true,
  "data": {
    "can_create": false,
    "reason": "이전 역극 3개에 이음이 필요합니다.",
    "required_replies": 3,
    "current_replies": 1
  }
}
```

---

## 7. 포인트 API

### 7.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/point/history | 포인트 내역 |
| GET | /api/point/balance | 잔액 조회 |
| POST | /api/point/attendance | 출석체크 |
| GET | /api/point/attendance/status | 오늘 출석 여부 |
| GET | /api/point/game/lottery | 종이뽑기 현황 |

### 7.2 상세

**GET /api/point/history**
```
Query Parameters:
- page: 페이지 번호
- type: 필터 (earn/use/all)
```

```json
// Response
{
  "success": true,
  "data": {
    "balance": 1234,
    "items": [
      {
        "po_id": 100,
        "po_content": "출석체크 (주사위)",
        "po_point": 42,
        "po_datetime": "2026-02-03 10:30:00",
        "po_rel_table": "attendance",
        "po_rel_action": "dice"
      }
    ],
    "pagination": { ... }
  }
}
```

**POST /api/point/attendance**
```json
// Response (주사위)
{
  "success": true,
  "data": {
    "game_type": "dice",
    "result": {
      "value": 42,
      "point": 42
    },
    "new_balance": 1276
  },
  "message": "출석체크 완료! 42P 획득"
}

// Response (운세뽑기)
{
  "success": true,
  "data": {
    "game_type": "fortune",
    "result": {
      "star": 4,
      "text": "행운이 따르는 날!",
      "point": 50
    },
    "new_balance": 1284
  },
  "message": "출석체크 완료! 50P 획득"
}

// Response (종이뽑기)
{
  "success": true,
  "data": {
    "game_type": "lottery",
    "result": {
      "number": 23,
      "rank": 2,
      "rank_name": "2등상",
      "point": 200,
      "item": null,
      "board_completed": false,
      "board_progress": "33/100"
    },
    "new_balance": 1434
  },
  "message": "출석체크 완료! 2등상 당첨! 200P 획득"
}

// Response (종이뽑기 - 판 완성)
{
  "success": true,
  "data": {
    "game_type": "lottery",
    "result": {
      "number": 77,
      "rank": 5,
      "rank_name": "5등상",
      "point": 10,
      "item": null,
      "board_completed": true,
      "board_bonus_point": 500,
      "board_bonus_item": {
        "si_id": 5,
        "si_name": "특별 칭호"
      },
      "new_board_started": true
    },
    "new_balance": 1944
  },
  "message": "판 완성! 보너스 500P + 특별 칭호 획득!"
}
```

**GET /api/point/game/lottery**
```json
// Response
{
  "success": true,
  "data": {
    "board": {
      "glb_id": 1,
      "glb_size": 100,
      "glb_bonus_point": 500
    },
    "progress": {
      "picked_count": 32,
      "picked_numbers": [1, 5, 7, 11, 23, ...],
      "remaining": 68
    },
    "prizes": [
      { "rank": 1, "name": "1등상", "point": 1000, "count": 1 },
      { "rank": 2, "name": "2등상", "point": 200, "count": 3 },
      { "rank": 3, "name": "3등상", "point": 100, "count": 5 },
      { "rank": 4, "name": "4등상", "point": 50, "count": 10 },
      { "rank": 5, "name": "5등상", "point": 10, "count": 81 }
    ]
  }
}
```

---

## 8. 상점 API

### 8.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/shop | 상품 목록 |
| GET | /api/shop/category | 카테고리 목록 |
| GET | /api/shop/{si_id} | 상품 상세 |
| POST | /api/shop/{si_id}/purchase | 구매 |
| POST | /api/shop/{si_id}/gift | 선물 |
| GET | /api/shop/inventory | 인벤토리 |
| POST | /api/shop/inventory/{iv_id}/use | 아이템 사용 |
| DELETE | /api/shop/inventory/{iv_id}/use | 아이템 해제 |
| GET | /api/shop/gift/received | 받은 선물 |
| PUT | /api/shop/gift/{gf_id}/accept | 선물 수락 |
| PUT | /api/shop/gift/{gf_id}/reject | 선물 거절 |

### 8.2 상세

**GET /api/shop**
```
Query Parameters:
- category: 카테고리 ID
- page: 페이지 번호
```

```json
// Response
{
  "success": true,
  "data": {
    "balance": 1234,
    "items": [
      {
        "si_id": 1,
        "si_name": "초보모험가 칭호",
        "si_description": "닉네임 앞에 표시되는 칭호",
        "si_image": "/upload/shop/1.png",
        "si_price": 500,
        "si_type": "title",
        "si_stock": 50,
        "si_stock_sold": 12,
        "status": "available",
        "owned": false,
        "owned_count": 0
      },
      {
        "si_id": 2,
        "si_name": "한정판 뱃지",
        "si_image": "/upload/shop/2.png",
        "si_price": 1000,
        "si_type": "badge",
        "status": "sold_out"
      },
      {
        "si_id": 3,
        "si_name": "특별 이모티콘",
        "si_image": "/upload/shop/3.png",
        "si_price": 300,
        "si_type": "emoticon",
        "status": "coming_soon",
        "si_sale_start": "2026-02-10 00:00:00"
      }
    ],
    "pagination": { ... }
  }
}
```

**POST /api/shop/{si_id}/purchase**
```json
// Response
{
  "success": true,
  "data": {
    "item": {
      "si_id": 1,
      "si_name": "초보모험가 칭호"
    },
    "price": 500,
    "new_balance": 734
  },
  "message": "구매가 완료되었습니다."
}
```

**POST /api/shop/{si_id}/gift**
```json
// Request
{
  "mb_id_to": "friend123",
  "message": "선물이야!"
}

// Response
{
  "success": true,
  "data": {
    "gf_id": 10,
    "item": {
      "si_id": 1,
      "si_name": "초보모험가 칭호"
    },
    "price": 500,
    "new_balance": 734
  },
  "message": "선물이 전송되었습니다."
}
```

**GET /api/shop/inventory**
```
Query Parameters:
- category: 카테고리 필터
```

```json
// Response
{
  "success": true,
  "data": {
    "items": [
      {
        "iv_id": 1,
        "si_id": 1,
        "si_name": "초보모험가 칭호",
        "si_image": "/upload/shop/1.png",
        "si_type": "title",
        "iv_count": 1,
        "is_active": true,
        "active_on": null
      },
      {
        "iv_id": 2,
        "si_id": 5,
        "si_name": "빨간 테두리",
        "si_image": "/upload/shop/5.png",
        "si_type": "profile_border",
        "iv_count": 1,
        "is_active": true,
        "active_on": {
          "ch_id": 1,
          "ch_name": "캐릭터명"
        }
      }
    ]
  }
}
```

**POST /api/shop/inventory/{iv_id}/use**
```json
// Request (캐릭터별 적용 아이템)
{
  "ch_id": 1
}

// Response
{
  "success": true,
  "message": "아이템이 적용되었습니다."
}
```

---

## 9. 관리자 API

### 9.1 회원 관리

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/member | 회원 목록 |
| GET | /api/adm/member/{mb_id} | 회원 상세 |
| PUT | /api/adm/member/{mb_id} | 회원 정보 수정 |
| PUT | /api/adm/member/{mb_id}/level | 회원 레벨 변경 |
| DELETE | /api/adm/member/{mb_id} | 회원 삭제 |
| GET | /api/adm/auth | 스태프 권한 목록 |
| PUT | /api/adm/auth/{mb_id} | 스태프 권한 설정 |

### 9.2 캐릭터 관리

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/character | 캐릭터 목록 |
| GET | /api/adm/character/pending | 승인 대기 목록 |
| PUT | /api/adm/character/{ch_id}/approve | 승인 |
| PUT | /api/adm/character/{ch_id}/reject | 반려 |
| DELETE | /api/adm/character/{ch_id} | 삭제 |
| GET | /api/adm/character/field | 프로필 양식 목록 |
| POST | /api/adm/character/field | 양식 추가 |
| PUT | /api/adm/character/field/{pf_id} | 양식 수정 |
| DELETE | /api/adm/character/field/{pf_id} | 양식 삭제 |
| PUT | /api/adm/character/field/order | 양식 순서 변경 |
| GET | /api/adm/side | 세력 목록 |
| POST | /api/adm/side | 세력 추가 |
| PUT | /api/adm/side/{side_id} | 세력 수정 |
| DELETE | /api/adm/side/{side_id} | 세력 삭제 |
| GET | /api/adm/class | 종족 목록 |
| POST | /api/adm/class | 종족 추가 |
| PUT | /api/adm/class/{class_id} | 종족 수정 |
| DELETE | /api/adm/class/{class_id} | 종족 삭제 |

### 9.3 게시판 관리

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/board | 게시판 목록 |
| POST | /api/adm/board | 게시판 생성 |
| PUT | /api/adm/board/{bo_table} | 게시판 설정 수정 |
| DELETE | /api/adm/board/{bo_table} | 게시판 삭제 |
| POST | /api/adm/board/{bo_table}/copy | 게시판 복사 |
| GET | /api/adm/board/group | 게시판 그룹 목록 |
| POST | /api/adm/board/group | 그룹 생성 |

### 9.4 역극 관리

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/rp | 역극 목록 |
| DELETE | /api/adm/rp/{rt_id} | 역극 삭제 |
| PUT | /api/adm/rp/{rt_id}/status | 상태 변경 |
| GET | /api/adm/rp/config | 역극 설정 조회 |
| PUT | /api/adm/rp/config | 역극 설정 변경 |

### 9.5 포인트 관리

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/point | 포인트 내역 |
| POST | /api/adm/point/give | 포인트 지급 |
| POST | /api/adm/point/take | 포인트 차감 |
| GET | /api/adm/point/config | 포인트 설정 |
| PUT | /api/adm/point/config | 포인트 설정 변경 |
| GET | /api/adm/point/game | 미니게임 설정 |
| PUT | /api/adm/point/game | 미니게임 설정 변경 |
| GET | /api/adm/point/game/fortune | 운세 목록 |
| POST | /api/adm/point/game/fortune | 운세 추가 |
| PUT | /api/adm/point/game/fortune/{gf_id} | 운세 수정 |
| DELETE | /api/adm/point/game/fortune/{gf_id} | 운세 삭제 |
| GET | /api/adm/point/game/lottery | 종이뽑기 설정 |
| PUT | /api/adm/point/game/lottery | 종이뽑기 설정 변경 |

### 9.6 상점 관리

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/shop | 상품 목록 |
| POST | /api/adm/shop | 상품 등록 |
| PUT | /api/adm/shop/{si_id} | 상품 수정 |
| DELETE | /api/adm/shop/{si_id} | 상품 삭제 |
| PUT | /api/adm/shop/{si_id}/stock | 재고 조정 |
| GET | /api/adm/shop/category | 카테고리 목록 |
| POST | /api/adm/shop/category | 카테고리 추가 |
| PUT | /api/adm/shop/category/{sc_id} | 카테고리 수정 |
| DELETE | /api/adm/shop/category/{sc_id} | 카테고리 삭제 |
| GET | /api/adm/shop/log | 구매/선물 로그 |

### 9.7 대시보드

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/dashboard | 대시보드 데이터 |

```json
// Response
{
  "success": true,
  "data": {
    "today": {
      "new_members": 3,
      "pending_characters": 5,
      "total_posts": 24,
      "unanswered_qna": 2
    },
    "posts_by_board": [
      { "bo_table": "notice", "bo_subject": "공지사항", "count": 1 },
      { "bo_table": "log", "bo_subject": "로그", "count": 8 },
      { "bo_table": "owner", "bo_subject": "오너게시판", "count": 12 }
    ],
    "recent_qna": [
      {
        "wr_id": 10,
        "wr_subject": "문의드립니다",
        "wr_name": "user123",
        "wr_datetime": "2026-02-03 10:30:00"
      }
    ]
  }
}
```

### 9.8 환경설정 (관리자 전용)

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/config | 기본 설정 조회 |
| PUT | /api/adm/config | 기본 설정 변경 |
| GET | /api/adm/config/design | 디자인 설정 조회 |
| PUT | /api/adm/config/design | 디자인 설정 변경 |
| GET | /api/adm/theme | 테마 목록 조회 |
| PUT | /api/adm/theme | 테마 변경 |

### 9.9 이모티콘 관리

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/adm/emoticon | 이모티콘 셋 목록 |
| POST | /api/adm/emoticon | 이모티콘 셋 등록 |
| GET | /api/adm/emoticon/{es_id} | 셋 상세 (이모티콘 목록 포함) |
| PUT | /api/adm/emoticon/{es_id} | 셋 수정 |
| DELETE | /api/adm/emoticon/{es_id} | 셋 삭제 |
| POST | /api/adm/emoticon/{es_id}/image | 이모티콘 이미지 추가 |
| DELETE | /api/adm/emoticon/{es_id}/image/{em_id} | 이모티콘 이미지 삭제 |

### 9.10 테마 API

**GET /api/adm/theme**
```json
// Response
{
  "success": true,
  "data": {
    "current": "default",
    "themes": [
      {
        "folder": "default",
        "name": "Default",
        "version": "1.0.0",
        "author": "Morgan Edition",
        "description": "기본 테마",
        "screenshot": "/theme/default/screenshot.png",
        "active": true
      },
      {
        "folder": "ocean-blue",
        "name": "Ocean Blue",
        "version": "1.0.0",
        "author": "테마 제작자",
        "description": "시원한 블루 톤의 테마",
        "screenshot": "/theme/ocean-blue/screenshot.png",
        "active": false
      }
    ]
  }
}
```

**PUT /api/adm/theme**
```json
// Request
{
  "theme": "ocean-blue"
}

// Response
{
  "success": true,
  "message": "테마가 변경되었습니다."
}
```

---

## 10. 이모티콘 API (유저용)

### 10.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/emoticon | 내가 보유한 이모티콘 셋 목록 |
| GET | /api/emoticon/{es_id} | 셋 내 이모티콘 목록 |
| GET | /api/emoticon/all | 전체 이모티콘 (보유+미보유) |

### 10.2 상세

**GET /api/emoticon**
```json
// Response
{
  "success": true,
  "data": {
    "sets": [
      {
        "es_id": 1,
        "es_name": "기본 감정팩",
        "es_preview": "/upload/emoticon/1/preview.png",
        "emoticons": [
          { "em_id": 1, "em_code": ":smile:", "em_image": "/upload/emoticon/1/smile.png" },
          { "em_id": 2, "em_code": ":sad:", "em_image": "/upload/emoticon/1/sad.png" },
          { "em_id": 3, "em_code": ":angry:", "em_image": "/upload/emoticon/1/angry.png" }
        ]
      }
    ]
  }
}
```

**이모티콘 사용 방식**
- 댓글/게시글 작성 시 이모티콘 선택 UI 표시
- 선택한 이모티콘 코드 (`:smile:`) 가 본문에 삽입
- 렌더링 시 코드를 이미지로 변환

---

## 11. 파일 업로드 API

### 11.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| POST | /api/upload/image | 이미지 업로드 |
| POST | /api/upload/file | 파일 업로드 |
| DELETE | /api/upload/{file_id} | 파일 삭제 |

### 11.2 상세

**POST /api/upload/image**
```json
// Request (multipart/form-data)
{
  "file": (binary),
  "type": "character|board|rp|shop",
  "resize": true,
  "max_width": 800
}

// Response
{
  "success": true,
  "data": {
    "file_id": "abc123",
    "url": "/upload/temp/abc123.jpg",
    "thumbnail": "/upload/temp/thumb_abc123.jpg",
    "width": 800,
    "height": 600
  }
}
```

---

## 12. 검색 API

### 12.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/search | 통합 검색 |
| GET | /api/search/member | 회원 검색 |
| GET | /api/search/character | 캐릭터 검색 |

### 12.2 상세

**GET /api/search/member**
```
Query Parameters:
- q: 검색어 (mb_id 또는 mb_nick)
- limit: 결과 수 (기본 10)
```

```json
// Response (선물하기 등에서 사용)
{
  "success": true,
  "data": {
    "items": [
      {
        "mb_id": "user123",
        "mb_nick": "닉네임123"
      }
    ]
  }
}
```

---

## 13. 알림 API

### 13.1 엔드포인트

| Method | Endpoint | 설명 |
|--------|----------|------|
| GET | /api/notification | 알림 목록 |
| GET | /api/notification/unread-count | 읽지 않은 알림 수 |
| PUT | /api/notification/{noti_id}/read | 알림 읽음 처리 |
| PUT | /api/notification/read-all | 전체 읽음 처리 |

### 13.2 알림 종류

| 종류 | 설명 |
|------|------|
| character_approved | 캐릭터 승인 |
| character_rejected | 캐릭터 반려 |
| comment | 댓글 알림 |
| rp_reply | 역극 이음 알림 |
| gift_received | 선물 수신 |
| point_received | 포인트 지급 |

---

## 14. 실시간 연동 (Supabase)

### 14.1 Realtime 채널

| 채널 | 이벤트 | 설명 |
|------|--------|------|
| rp:{rt_id} | new_reply | 역극 새 이음 |
| notification:{mb_id} | new | 새 알림 |

### 14.2 연동 방식

```javascript
// 역극 실시간 이음
const channel = supabase
  .channel(`rp:${rt_id}`)
  .on('broadcast', { event: 'new_reply' }, (payload) => {
    // 새 이음 UI에 추가
  })
  .subscribe();

// 알림
const notiChannel = supabase
  .channel(`notification:${mb_id}`)
  .on('broadcast', { event: 'new' }, (payload) => {
    // 알림 카운트 업데이트
  })
  .subscribe();
```

---

## 15. API 보안

### 15.1 인증 확인

- 모든 API는 세션 기반 인증
- 로그인 필요 API: 세션 없으면 401 반환
- 권한 필요 API: 권한 없으면 403 반환

### 15.2 Rate Limiting

| 엔드포인트 | 제한 |
|-----------|------|
| /api/auth/login | 5회/분 |
| /api/member/register | 3회/시간 |
| /api/point/attendance | 1회/일 |
| 일반 API | 60회/분 |

### 15.3 CSRF 보호

- 모든 POST/PUT/DELETE 요청에 CSRF 토큰 필요
- 헤더: `X-CSRF-Token: {token}`

---

## 16. TODO

- [ ] API 응답 코드 상세 정의
- [ ] 에러 코드 전체 목록
- [ ] Rate Limiting 구현 방식
- [ ] API 문서 자동화 (Swagger/OpenAPI)
- [ ] 테스트 케이스 작성

---

*작성일: 2026-02-03*
*수정일: 2026-02-03*
*상태: 1차 기획 완료, 문서 검토 완료*
