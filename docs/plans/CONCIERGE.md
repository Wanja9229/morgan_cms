# The Concierge — 의뢰 / 매칭 시스템

> 캐릭터 간 창작 협업 의뢰를 등록하고 매칭하는 시스템.
> 최종 업데이트: 2026-02-15

---

## 1. 개요

캐릭터 간 창작 협업(합작, 일러스트, 소설 등)을 위한 의뢰 시스템.
"합작 구해요" 게시판 글을 시스템으로 구조화한 것.

**핵심 루프:**
```
의뢰 등록 (캐릭터 선택 + 내용 작성)
  → 지원 접수 (지원자 캐릭터 선택 + 메시지)
  → 매칭 (의뢰자가 직접 선택 or 추첨)
  → 창작 진행
  → 게시판에 결과물 글 작성 (의뢰 연결)
  → 완료 → 보상 지급
```

**참여 조건:** 캐릭터 승인 완료 회원 (mb_level >= 3)

**의존성:**
- mg_character (캐릭터) — 이미 구현
- 포인트 시스템 — 이미 구현
- 상점 시스템 (mg_shop_*) — 이미 구현
- 알림 시스템 (mg_notify) — 이미 구현

---

## 2. 의뢰 등록

### 2.1 의뢰 폼

| 필드 | 설명 |
|------|------|
| 의뢰 제목 | 필수 |
| 의뢰 내용 | 상세 설명, 원하는 창작 방향 |
| 의뢰 유형 | 합작 / 일러스트 / 소설 / 기타 |
| 모집 인원 | 1~5명 |
| 보상 티어 | 일반 / 긴급 |
| 마감일 | 지원 접수 마감 시점 |
| 캐릭터 | 의뢰자의 캐릭터 선택 |

### 2.2 보상 티어

| 티어 | 등록 비용 | 수행자 보상 | 특이사항 |
|------|-----------|-------------|----------|
| 일반 | 무료 | 50pt | - |
| 긴급 | 100pt 선불 | 100pt | 목록 상단 노출. 만료/취소 시 환불 |

- 동시 등록 가능한 의뢰 수: 기본 2개 (`mg_config('concierge_max_slots', 2)`)
- 보상 포인트: `mg_config('concierge_reward_normal', 50)`, `mg_config('concierge_reward_urgent', 100)`

---

## 3. 지원 및 매칭

### 3.1 지원 흐름

1. 의뢰 목록에서 원하는 의뢰 확인
2. 지원 버튼 → 캐릭터 선택 + 간단한 지원 메시지 작성
3. 의뢰자에게 알림 (`mg_notify()`)
4. 의뢰자가 지원자 목록 확인 → 선택/매칭
5. 결과 → 지원자 전원에게 알림

### 3.2 매칭 방식

**직접 선택 (기본)**
- 의뢰자가 지원자 목록에서 모집 인원만큼 직접 선택

**추첨 매칭 (선택적)**
- 의뢰 등록 시 추첨 모드 선택 가능
- 마감일에 지원자 중 무작위 선정
- 상점 '추첨 확률 UP' 아이템 보유 시 가중치 부여

---

## 4. 완료 판정

게시판 연동 방식:

1. 매칭 완료 후, 수행자가 창작물을 제작
2. 지정 게시판에 글 작성 시 **"의뢰 연결"** 선택 가능 (write hook)
   - 자신이 수행 중인 의뢰 목록이 드롭다운으로 표시
   - 의뢰를 선택하면 글과 의뢰가 연결됨
3. 글 등록 시 해당 의뢰의 상태가 `completed`로 전환
4. 수행자에게 보상 포인트 자동 지급
5. 의뢰자에게 완료 알림

> 모집 인원이 2명 이상일 경우, 모든 수행자가 각각 글을 올려야 최종 완료.
> 또는 의뢰자가 수동으로 완료 처리하는 옵션도 제공.

---

## 5. 알림

기존 `mg_notify()` 활용. 별도 알림 테이블 불필요.

| 알림 유형 | 시점 | 수신 대상 |
|-----------|------|-----------|
| concierge_apply | 의뢰에 지원 접수 | 의뢰자 |
| concierge_match | 매칭 완료 (선정/미선정) | 지원자 전원 |
| concierge_complete | 수행자가 결과물 게시 | 의뢰자 |
| concierge_reward | 보상 지급 | 수행자 |
| concierge_expire | 마감일 경과 | 의뢰자 |

---

## 6. 포인트 / 상점 연동

### 6.1 포인트 흐름

| 행위 | 변동 | 대상 |
|------|------|------|
| 일반 의뢰 등록 | 무료 | 의뢰자 |
| 긴급 의뢰 등록 | -100pt | 의뢰자 (선불) |
| 의뢰 수행 완료 (일반) | +50pt | 수행자 |
| 의뢰 수행 완료 (긴급) | +100pt | 수행자 |
| 긴급 의뢰 만료/취소 | +100pt | 의뢰자 (환불) |

### 6.2 상점 아이템 (기존 mg_shop 활용)

| 아이템 | 가격 | 효과 |
|--------|------|------|
| 의뢰 슬롯 추가 | 300pt | 동시 등록 가능 의뢰 수 +1 |
| 추첨 확률 UP | 200pt | 추첨 매칭 시 가중치 x2 (1회용) |
| 의뢰 하이라이트 | 150pt | 목록 상단 24시간 고정 노출 |

---

## 7. 의뢰 상태 흐름

```
recruiting (모집 중)
    ↓ 지원자 접수 & 의뢰자 선택
matched (매칭 완료, 창작 진행 중)
    ↓ 수행자가 게시판에 결과물 등록
completed (완료) → 보상 지급
```

- 마감일 초과 시 자동 `expired` 전환
- 긴급 의뢰 만료/취소 시 선불 포인트 환불
- 의뢰자 직접 취소 가능 (`cancelled`)

---

## 8. DB 스키마

### mg_concierge — 의뢰

| 컬럼 | 타입 | 설명 |
|------|------|------|
| cc_id | int PK AUTO_INCREMENT | 의뢰 ID |
| mb_id | varchar(20) | 의뢰자 회원 ID |
| ch_id | int | 의뢰자 캐릭터 ID |
| cc_title | varchar(255) | 의뢰 제목 |
| cc_content | text | 의뢰 내용 |
| cc_type | enum('collaboration','illustration','novel','other') | 의뢰 유형 |
| cc_max_members | int DEFAULT 1 | 모집 인원 (1~5) |
| cc_tier | enum('normal','urgent') DEFAULT 'normal' | 보상 티어 |
| cc_match_mode | enum('direct','lottery') DEFAULT 'direct' | 매칭 방식 |
| cc_deadline | datetime | 지원 마감일 |
| cc_status | enum('recruiting','matched','completed','expired','cancelled') DEFAULT 'recruiting' | 상태 |
| cc_highlight | datetime NULL | 하이라이트 만료 시각 |
| cc_datetime | datetime DEFAULT CURRENT_TIMESTAMP | 등록일 |

### mg_concierge_apply — 지원

| 컬럼 | 타입 | 설명 |
|------|------|------|
| ca_id | int PK AUTO_INCREMENT | 지원 ID |
| cc_id | int | 의뢰 ID |
| mb_id | varchar(20) | 지원자 회원 ID |
| ch_id | int | 지원자 캐릭터 ID |
| ca_message | text | 지원 메시지 |
| ca_status | enum('pending','selected','rejected') DEFAULT 'pending' | 상태 |
| ca_has_boost | tinyint(1) DEFAULT 0 | 추첨 확률 UP 적용 여부 |
| ca_datetime | datetime DEFAULT CURRENT_TIMESTAMP | 지원일 |

### mg_concierge_result — 완료 연결

| 컬럼 | 타입 | 설명 |
|------|------|------|
| cr_id | int PK AUTO_INCREMENT | |
| cc_id | int | 의뢰 ID |
| ca_id | int | 지원(수행자) ID |
| bo_table | varchar(20) | 게시판 테이블명 |
| wr_id | int | 게시글 ID |
| cr_datetime | datetime DEFAULT CURRENT_TIMESTAMP | 완료일 |

---

## 9. 프론트 페이지

| 파일 | 설명 |
|------|------|
| bbs/concierge.php | 의뢰 목록 (필터: 상태, 유형) |
| bbs/concierge_view.php | 의뢰 상세 + 지원자 목록 + 지원 폼 |
| bbs/concierge_write.php | 의뢰 작성/수정 |
| bbs/concierge_api.php | AJAX (apply, match, complete, cancel, lottery) |

### 게시판 write hook

글 작성 시 의뢰 연결 드롭다운:
```php
// write.skin.php 또는 write_update.php에서
// 현재 유저의 matched 상태 의뢰 목록 조회
// 선택 시 mg_concierge_result에 INSERT + 의뢰 완료 처리
```

---

## 10. 관리자

| 파일 | 설명 |
|------|------|
| adm/morgan/concierge.php | 의뢰 목록 조회/관리 (검색, 상태 변경, 삭제) |

- 별도 CRUD 불필요 (의뢰는 유저가 등록)
- 관리자는 조회 + 문제 의뢰 삭제/상태 변경 정도

---

## 11. 설계 원칙

> **단순함 > 정밀함 | 자동화 > 수동 검토 | 명확함 > 복잡한 밸런싱**

- 보상은 고정값으로 통일
- 주관적 평가 배제 → 운영 부담 최소화
- 사후 관리: 신고 시스템 기반 (문제 발생 시에만 처리)
- 어뷰징 확인 시 포인트 회수 + 경고

---

*이 문서는 Phase 20 기획서입니다. 구현 시 상세 API 로직은 기존 Morgan CMS 패턴을 따릅니다.*
