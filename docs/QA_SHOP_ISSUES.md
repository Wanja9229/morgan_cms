# 재화/상점 그룹 QA — 발견 이슈 상세

> 작성일: 2026-02-25
> 상태: 전체 수정 완료 (02-25). Critical 6 + High 5 + Refactor 5 + Low 5.

---

## Critical (즉시 수정)

### C1. point_manage_update.php:70,122 — $member 미정의 위험
- `insert_point()` 5번째 인자에 `$member['mb_id']` 사용
- adm/_common.php가 $member를 세팅하지만, 명시적 보장 없음
- **수정**: `$member['mb_id']` → `$_SESSION['ss_mb_id'] ?? 'admin'` 또는 명시적 체크

### C2. reward.php:771 — XSS (JS 인라인에 SQL escape)
- `sql_real_escape_string($rc['mb_id'])` → JS onclick 속성에 삽입
- SQL escape는 JS 컨텍스트 특수문자를 보호하지 못함
- **수정**: `htmlspecialchars()` 사용 또는 data 속성으로 분리

### C3. reward_update.php:145 — PHP 8 Fatal (sql_fetch false)
- `$rc = sql_fetch(...)` 후 `$rc['rc_id']` 접근 — false 시 Fatal
- **수정**: `if (!$rc || !$rc['rc_id'])` 패턴

### C4. board_form_update.php — 새 게시판 생성 시 주사위 미저장
- 수정 모드에서만 `br_dice_use/once/max` 저장
- 추가 모드(새 게시판)에서는 보상 레코드 생성 누락
- **수정**: 추가 로직에도 `INSERT INTO mg_board_reward ... ON DUPLICATE KEY UPDATE` 추가

### C5. morgan.php:3156 — 이모티콘 수수료율 음수 수익
- `commission_rate` 설정이 100 이상이면 `creator_revenue`가 음수
- 음수 포인트가 크리에이터에게 지급됨
- **수정**: `$commission_rate = min(99, max(0, (int)mg_config(...)))`

### C6. emoticon_create_update.php:212 — 자동생성 코드 중복
- 코드 중복 시 `_숫자` 접미사 붙이는데, 수정된 코드도 중복일 수 있음
- **수정**: while 루프로 미사용 코드 찾을 때까지 반복

---

## 일관성 문제 (High)

### H1. mg_get_inventory() 반환값 조건부
- **파일**: morgan.php:1452-1507
- page/limit 있으면 `{items, total}`, 없으면 순수 배열
- **수정**: 항상 `{items, total}` 반환하거나 별도 함수 분리

### H2. mg_send_gift() gf_type 미명시
- **파일**: morgan.php:1745 vs 1801
- `mg_send_gift()`: gf_type=NULL (상점 구매 선물)
- `mg_send_gift_from_inventory()`: gf_type='inventory'
- `mg_reject_gift()`: NULL이면 'shop'으로 취급 (동작은 함)
- **수정**: `mg_send_gift()`에서 `gf_type='shop'` 명시

### H3. 상점 타입 정의 분산 (4곳)
- `shop_item_form.php:18` — 10개
- `shop_item_update.php` — 효과 처리용
- `shop_log.php:67` — 라벨용 (equip 누락)
- `morgan.php:471` — 17개 (정식)
- **수정**: `mg_get_item_types()` 함수를 단일 소스로 → 나머지는 호출

### H4. insert_point() rel 파라미터 생략
- `mg_buy_item()`, `mg_send_gift()` 등에서 4개 인자만 전달
- rel_table, rel_id, rel_action 누락 → 포인트 로그 추적성 약함
- **수정**: 모든 호출처에 6개 인자 전달

### H5. 이모티콘 파일 크기 제한 불일치
- 관리자: 1MB (`emoticon_form_update.php:100`)
- 유저: 2MB (`emoticon_create_update.php` → `mg_upload_max_icon()`)
- **수정**: 동일한 상수/설정으로 통일

### H6. point_manage.php:369 — URL sfl 미인코딩
- `$sfl` 변수가 urlencode 없이 URL에 삽입
- **수정**: `urlencode($sfl)` 추가

---

## 함수화 리팩토링 (Medium) — 전부 수정 완료

### R1. 선물 검증 공통화 [완료]
- `_mg_validate_gift($mb_id_from, $mb_id_to)` 헬퍼 추출
- `mg_send_gift()`, `mg_send_gift_from_inventory()` 적용

### R2. 선물 조회 공통화 [완료]
- `_mg_get_pending_gift($gf_id, $mb_id)` 헬퍼 추출
- `mg_accept_gift()`, `mg_reject_gift()` 적용

### R3. 상점 타입 정의 단일 소스 [완료]
- `mg_get_item_types()` 함수로 17개 타입 정의 (name/desc/group)
- `$mg['shop_type_labels']`는 함수에서 자동 추출
- `shop_item_form.php` 하드코딩 제거 → `mg_get_item_types()` 호출

### R4. exclusive_types 설정화 [완료]
- `mg_get_exclusive_types()` 함수 추출
- `mg_use_item()` 내 하드코딩 제거

### R5. 이모티콘 코드 검증 공통화 [완료]
- `mg_validate_emoticon_code($code)` — `:영문숫자_:` 정규식 강제 + 자동 소문자화
- 관리자(`emoticon_form_update.php`), 유저(`emoticon_create_update.php`) 양쪽 적용
- 코드 업데이트/신규 등록 모두 검증 통과

---

## Low (코드 품질) — 전부 수정/확인 완료

- `json_decode()` null 안전 처리 [완료] — `?? array()` / `?: array()` 추가 (morgan.php 전역)
- `sql_query()` 결과 false 체크 [완료] — reward.php 주요 6개 루프에 `if ($result)` 가드 추가
- `reward_update.php:259` 일괄 승인 [완료] — `success => $approved > 0` 으로 변경
- `mg_reward_material()` 반환값 [확인] — null|array 설계 의도적. 호출처 모두 정상 처리
- `point_manage_update.php:86-131` [유지] — 비JS 환경 폴백으로 기능 가능, 제거 불필요
