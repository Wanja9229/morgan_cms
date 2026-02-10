# 상점 시스템 기획

> **구현 완료** (Phase 5)

> Morgan Edition - 상점 시스템 설계
> 참고: vn_engine 상점 + 커뮤니티 특화 확장

> ⚠️ **스키마 참조**: 최종 테이블 구조는 [DB.md](./DB.md) 참조
> 이 문서의 테이블명은 설계 참고용이며, 실제 구현 시 mg_ 접두사 사용
> (예: shop_item → mg_shop_item, inventory → mg_inventory)

---

## 개요

- **목적**: 포인트 사용처, 커뮤니티 기능 아이템 제공
- **재화**: 포인트 단일 재화 (명칭 커스텀 가능, 기본: G)
- **인벤토리**: 계정 단위 관리
- **방향**: 커뮤니티 기능 중심 + 세계관 아이템 확장 가능

---

## 1. 재화 시스템

### 1.1 재화 설정

| 설정 | 설명 | 기본값 |
|------|------|--------|
| cf_point_name | 재화 명칭 | G |
| cf_point_unit | 재화 단위 | (빈 문자열) |

**표시 예시**
- 기본: `1,000 G`
- 커스텀: `1,000 골드`, `1,000 포인트` 등

### 1.2 재화 사용

| 용도 | 설명 |
|------|------|
| 상점 구매 | 아이템 구매 |
| 선물 | 다른 유저에게 아이템 선물 |

---

## 2. 상품 시스템

### 2.1 상품 테이블 (shop_item)

| 필드 | 타입 | 설명 |
|------|------|------|
| si_id | int AUTO_INCREMENT | PK |
| si_category | int | 카테고리 ID (FK) |
| si_name | varchar(100) | 상품명 |
| si_description | text | 상품 설명 |
| si_image | varchar(500) | 상품 이미지 |
| si_price | int | 가격 (포인트) |
| si_type | enum | 상품 타입 |
| si_effect | text | 효과 데이터 (JSON) |
| si_stock | int | 재고 수량 (-1=무제한) |
| si_stock_sold | int | 판매된 수량 |
| si_limit_per_user | int | 1인당 보유 제한 (0=무제한) |
| si_sale_start | datetime | 판매 시작일 (nullable) |
| si_sale_end | datetime | 판매 종료일 (nullable) |
| si_display | tinyint | 노출 여부 |
| si_use | tinyint | 사용 가능 여부 |
| si_order | int | 정렬 순서 |
| si_datetime | datetime | 등록일 |

### 2.2 상품 타입 (si_type)

| 타입 | 코드 | 설명 | 소모 |
|------|------|------|------|
| 칭호 | title | 닉네임 앞/뒤 표시 | 영구 |
| 뱃지 | badge | 프로필 뱃지 | 영구 |
| 이모티콘 셋 | emoticon_set | 이모티콘 묶음 (댓글/게시글 사용) | 영구 |
| 닉네임 색상 | nick_color | 닉네임 색상 변경 | 소모 |
| 닉네임 강조 | nick_effect | 닉네임 효과 (굵게, 그림자 등) | 소모 |
| 프로필 테두리 | profile_border | 캐릭터 썸네일 테두리 | 영구 |
| 장비 | equip | 캐릭터 장착 아이템 (세계관용) | 영구 |
| 가구 | furniture | 마이룸 가구 (2차 구현) | 영구 |
| 기타 | etc | 기타 아이템 | 설정에 따름 |

### 2.3 상품 효과 데이터 (si_effect) 예시

```json
// 칭호
{
  "type": "title",
  "position": "prefix",  // prefix: 앞, suffix: 뒤
  "text": "★초보모험가★",
  "color": "#FFD700"
}

// 닉네임 색상
{
  "type": "nick_color",
  "color": "#FF6B6B"
}

// 닉네임 강조
{
  "type": "nick_effect",
  "effect": "bold",  // bold, shadow, glow
  "value": "#000000"
}

// 프로필 테두리
{
  "type": "profile_border",
  "style": "gradient",
  "colors": ["#FF6B6B", "#4ECDC4"]
}

// 장비 (세계관용)
{
  "type": "equip",
  "slot": "weapon",
  "display_name": "불꽃의 검"
}

// 이모티콘 셋
{
  "type": "emoticon_set",
  "es_id": 1  // mg_emoticon_set 테이블 참조
}
```

---

## 3. 카테고리 시스템

### 3.1 카테고리 테이블 (shop_category)

| 필드 | 타입 | 설명 |
|------|------|------|
| sc_id | int AUTO_INCREMENT | PK |
| sc_name | varchar(50) | 카테고리명 |
| sc_description | varchar(200) | 설명 |
| sc_icon | varchar(100) | 아이콘 |
| sc_order | int | 정렬 순서 |
| sc_use | tinyint | 사용 여부 |

### 3.2 기본 카테고리

| ID | 이름 | 설명 |
|----|------|------|
| 1 | 꾸미기 | 칭호, 뱃지, 닉네임 효과 등 |
| 2 | 이모티콘 | 이모티콘, 스티커 |
| 3 | 테두리 | 프로필 테두리 |
| 4 | 장비 | 캐릭터 장착 아이템 |
| 5 | 기타 | 기타 아이템 |

---

## 4. 인벤토리 시스템

### 4.1 인벤토리 테이블 (inventory)

| 필드 | 타입 | 설명 |
|------|------|------|
| iv_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| si_id | int | 상품 ID (FK) |
| iv_count | int | 보유 수량 |
| iv_datetime | datetime | 획득일 |

### 4.2 인벤토리 규칙

- **계정 단위** 관리 (mb_id)
- **보유 제한**: 상품별 최대 보유 수량 (si_limit_per_user)
- **영구 보관**: 기간 만료 없음

---

## 5. 아이템 사용/장착

### 5.1 사용 테이블 (item_active)

| 필드 | 타입 | 설명 |
|------|------|------|
| ia_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| si_id | int | 상품 ID |
| ia_type | varchar(20) | 적용 타입 |
| ch_id | int | 캐릭터 ID (캐릭터별 적용 시, nullable) |
| ia_datetime | datetime | 적용일 |

### 5.2 적용 타입별 처리

| 타입 | 적용 대상 | 소모 여부 |
|------|----------|----------|
| title | 계정 전체 | 영구 |
| badge | 계정 전체 | 영구 |
| emoticon_set | 계정 전체 | 영구 |
| nick_color | 계정 전체 | 소모 (1회) |
| nick_effect | 계정 전체 | 소모 (1회) |
| profile_border | 캐릭터별 | 영구 |
| equip | 캐릭터별 | 영구 |

### 5.3 캐릭터 장착 테이블 (character_equip)

| 필드 | 타입 | 설명 |
|------|------|------|
| ce_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID |
| si_id | int | 상품 ID |
| ce_equipped | tinyint | 장착 여부 (0/1) |
| ce_datetime | datetime | 장착일 |

> 장비는 부위 구분 없이 장착/비장착 상태만 관리

---

## 6. 판매 상태 관리

### 6.1 상품 상태 표시

| 상태 | 조건 | 표시 |
|------|------|------|
| 판매중 | 현재 판매 가능 | (일반 표시) |
| 커밍순 | si_sale_start > 현재시간 | COMING SOON |
| 솔드아웃 | si_stock_sold >= si_stock | SOLD OUT |
| 기간종료 | si_sale_end < 현재시간 | SOLD OUT |
| 비노출 | si_display = 0 | (목록에서 숨김) |

### 6.2 상품 목록 정렬

```
1. 판매중 (si_order 순)
2. 커밍순 (si_sale_start 순)
3. 솔드아웃 (최근 종료순)
```

---

## 7. 선물 시스템

### 7.1 선물 테이블 (gift)

| 필드 | 타입 | 설명 |
|------|------|------|
| gf_id | int AUTO_INCREMENT | PK |
| mb_id_from | varchar(20) | 보내는 사람 |
| mb_id_to | varchar(20) | 받는 사람 |
| si_id | int | 상품 ID |
| gf_message | varchar(200) | 선물 메시지 |
| gf_status | enum | 상태 (pending/accepted/rejected) |
| gf_datetime | datetime | 선물 일시 |

### 7.2 선물 플로우

```
[보내는 사람]
    ↓
상점에서 "선물하기" 선택
    ↓
받는 사람 선택 + 메시지 입력
    ↓
포인트 차감 + 선물 생성 (pending)
    ↓
[받는 사람]
    ↓
알림 수신
    ↓
수락 → 인벤토리에 추가
거절 → 포인트 환불
```

### 7.3 선물 메시지 제한

| 설정 | 값 |
|------|-----|
| 최대 길이 | 200자 |
| 금지어 필터 | 적용 |

---

## 8. 구매 시스템

### 8.1 구매 로그 테이블 (shop_log)

| 필드 | 타입 | 설명 |
|------|------|------|
| sl_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 구매자 |
| si_id | int | 상품 ID |
| sl_price | int | 구매 가격 |
| sl_type | enum | 유형 (purchase/gift_send/gift_receive) |
| sl_datetime | datetime | 구매 일시 |

### 8.2 구매 검증

```
1. 포인트 충분한지 확인
2. 재고 확인 (si_stock)
3. 보유 제한 확인 (si_limit_per_user)
4. 판매 기간 확인 (si_sale_start ~ si_sale_end)
5. 판매 상태 확인 (si_display, si_use)
```

---

## 9. UI 구성

### 9.1 상점 메인

```
┌─────────────────────────────────────────────────────┐
│  상점                              보유: 1,234 G    │
├─────────────────────────────────────────────────────┤
│  [꾸미기] [이모티콘] [테두리] [장비] [기타]          │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐    │
│  │ 이미지 │  │ 이미지 │  │ 이미지 │  │ 이미지 │    │
│  │ 상품명 │  │ 상품명 │  │ 상품명 │  │ COMING │    │
│  │ 100 G  │  │ 200 G  │  │ SOLD   │  │  SOON  │    │
│  │ [구매] │  │ [구매] │  │  OUT   │  │ 02/10~ │    │
│  └────────┘  └────────┘  └────────┘  └────────┘    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 9.2 상품 상세 모달

```
┌─────────────────────────────────────┐
│  [상품 이미지]                       │
│                                     │
│  ★초보모험가★ 칭호                  │
│  ─────────────────────              │
│  닉네임 앞에 표시되는 귀여운 칭호     │
│                                     │
│  가격: 500 G                        │
│  재고: 48/50                        │
│                                     │
│  [구매하기]  [선물하기]              │
└─────────────────────────────────────┘
```

### 9.3 인벤토리

```
┌─────────────────────────────────────────────────────┐
│  인벤토리                                           │
├─────────────────────────────────────────────────────┤
│  [전체] [꾸미기] [이모티콘] [테두리] [장비]          │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌────────┐  ┌────────┐  ┌────────┐                │
│  │ 이미지 │  │ 이미지 │  │ 이미지 │                │
│  │ 상품명 │  │ 상품명 │  │ 상품명 │                │
│  │ x3     │  │ 사용중 │  │ x1     │                │
│  │ [사용] │  │ [해제] │  │ [사용] │                │
│  └────────┘  └────────┘  └────────┘                │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 10. 관리자 기능

### 10.1 상품 관리

| 기능 | 설명 |
|------|------|
| 상품 등록 | 이름, 가격, 이미지, 효과 등 |
| 상품 수정 | 정보 변경 |
| 재고 관리 | 수량 조정 |
| 노출 관리 | 표시/숨김 |
| 판매 기간 | 시작/종료일 설정 |

### 10.2 카테고리 관리

| 기능 | 설명 |
|------|------|
| 카테고리 추가 | 이름, 아이콘 |
| 순서 변경 | 드래그앤드롭 |
| 삭제 | 상품 없는 카테고리만 |

### 10.3 구매/선물 로그

| 기능 | 설명 |
|------|------|
| 구매 내역 | 전체/회원별 조회 |
| 선물 내역 | 보낸/받은 내역 |
| 통계 | 인기 상품, 매출 등 |

---

## 11. 미니게임 연동

포인트 시스템(PLAN_POINT.md)의 미니게임과 연동:

| 연동 | 설명 |
|------|------|
| 종이뽑기 등수 상품 | glp_item_id → si_id |
| 판 완성 보너스 | glb_bonus_item_id → si_id |

---

## 12. 꾸미기 아이템 확장 기획 (역극 + 게시판 공통)

> 2026-02-09 가능성 확인 완료. 현재 인프라로 적용 가능.

### 12.1 신규 상품 타입

| 타입 코드 | 설명 | 적용 대상 | 소모 |
|-----------|------|----------|------|
| `rp_background` | 역극/게시판 배경 꾸미기 | 판 단위 | 영구 |
| `chat_bubble` | 대화창(말풍선) 꾸미기 | 캐릭터별 | 영구 |

> `si_type` enum에 추가 필요

### 12.2 효과 데이터 (si_effect) 설계안

```json
// 역극/게시판 배경
{
  "type": "rp_background",
  "bg_image": "/data/shop/bg_001.jpg",
  "bg_color": "#1a1a2e",
  "bg_opacity": 0.3
}

// 대화창 (말풍선) 꾸미기
{
  "type": "chat_bubble",
  "bubble_color": "#2d1b69",
  "bubble_border_image": "/data/shop/border_001.png",
  "bubble_border_color": "#8b5cf6",
  "text_color": "#e2e8f0"
}

// 프로필 아이콘 (기존 profile_border 확장)
{
  "type": "profile_border",
  "style": "image",
  "border_image": "/data/shop/frame_001.png",
  "border_color": "#FFD700",
  "border_style": "gradient",
  "colors": ["#FF6B6B", "#4ECDC4"]
}
```

### 12.3 적용 우선순위

| 항목 | 우선순위 | 설명 |
|------|---------|------|
| 역극 배경 | 판장 > 댓글 작성자 | 판장 아이템이 있으면 판장 것 적용, 없으면 댓글 작성자 것 |
| 게시판 배경 | 글 작성자 | 게시글 작성자의 아이템 적용 |
| 말풍선 | 각자 본인 | 각 댓글 작성자의 아이템이 본인 말풍선에 적용 |
| 프로필 아이콘 | 각자 본인 | 아바타 테두리/프레임에 적용 |

### 12.4 기존 인프라 활용

| 구성요소 | 현황 | 비고 |
|---------|------|------|
| `mg_item_active` | 장착 아이템 관리 테이블 | mb_id + si_id + ia_type + ch_id |
| `mg_get_active_items($mb_id, $type)` | 장착 중 아이템 조회 | 타입별 필터링 지원 |
| `si_effect` JSON | 효과 데이터 유연 저장 | 새 타입 추가만으로 확장 가능 |
| 배타적 장착 로직 | 같은 타입 중복 장착 방지 | 이미 구현됨 |

### 12.5 구현 시 필요 작업

1. `si_type` enum에 `rp_background`, `chat_bubble` 추가 (DB 스키마)
2. 관리자 상품 등록 폼에 새 타입별 효과 입력 필드 추가
3. RP 댓글 API 응답에 작성자 활성 아이템 효과 데이터 포함
4. `renderReply()` JS에서 아이템 효과를 inline style로 적용
5. 게시판 스킨에도 동일 로직 적용 (게시글 배경, 댓글 말풍선)

### 12.6 적용 지점 (CSS 클래스 참고)

| 요소 | 현재 클래스 | 오버라이드 대상 |
|------|-----------|---------------|
| 메신저 배경 | `.rp-messenger-content` | background-image, background-color |
| 원글 작성자 말풍선 | `bg-mg-accent/20 rounded-2xl` | background, border-image, color |
| 참여자 말풍선 | `bg-mg-bg-tertiary rounded-2xl` | background, border-image, color |
| 아바타 테두리 | `rounded-full border-2 border-mg-accent/30` | border-image, border-color |
| 아바타 프레임 | 없음 (추가 필요) | 이미지 오버레이 wrapper |

---

## 13. TODO

- [ ] 상품 타입별 효과 적용 로직 설계
- [ ] 상점 UI 상세 설계
- [ ] 인벤토리 UI 설계
- [ ] 선물 알림 시스템
- [ ] 관리자 상품 등록 폼 설계
- [ ] 캐릭터 장착 UI 설계
- [ ] 꾸미기 아이템 신규 타입 추가 (`rp_background`, `chat_bubble`)
- [ ] 역극/게시판 렌더링 시 활성 아이템 조회 + 스타일 적용
- [ ] 게시판 스킨에 꾸미기 아이템 연동

---

*작성일: 2026-02-03*
*수정일: 2026-02-09*
*상태: 1차 기획 완료, 12절 꾸미기 확장 기획 추가*
