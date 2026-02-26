# 재화/상점 그룹 QA — 발견 이슈 상세

> 작성일: 2026-02-25
> 상태: 전체 수정 완료 (02-26 검증). Critical 6 + High 6 + Refactor 5 + Low 5.

---

## Critical (즉시 수정)

### C1. point_manage_update.php:70,122 — $member 미정의 위험 [완료]
- `insert_point()` 5번째 인자에 `$member['mb_id']` 사용
- **수정**: `isset($member['mb_id']) ? ... : ($_SESSION['ss_mb_id'] ?? 'admin')` 패턴 적용

### C2. reward.php:877 — XSS (JS 인라인에 SQL escape) [완료]
- `sql_real_escape_string($rc['mb_id'])` → JS onclick 속성에 삽입
- **수정**: `htmlspecialchars($rc['mb_id'])` 로 변경

### C3. reward_update.php:132 — PHP 8 Fatal (sql_fetch false) [완료]
- `$rc = sql_fetch(...)` 후 `$rc['rc_id']` 접근 — false 시 Fatal
- **수정**: `if (!$rc || !$rc['rc_id'])` 패턴 적용

### C4. board_form_update.php — 새 게시판 생성 시 주사위 미저장 [완료]
- 수정 모드에서만 `br_dice_use/once/max` 저장
- **수정**: 추가 모드에도 `INSERT INTO mg_board_reward ... ON DUPLICATE KEY UPDATE` 추가 (line 127-129)

### C5. morgan.php:3248 — 이모티콘 수수료율 음수 수익 [완료]
- `commission_rate` 설정이 100 이상이면 `creator_revenue`가 음수
- **수정**: `$commission_rate = min(99, max(0, (int)mg_config(...)))` 적용

### C6. emoticon_create_update.php:223 — 자동생성 코드 중복 [완료]
- 코드 중복 시 `_숫자` 접미사 붙이는데, 수정된 코드도 중복일 수 있음
- **수정**: `do...while (mg_emoticon_code_exists($code) && $suffix < 999)` 루프 적용

---

## 일관성 문제 (High)

### H1. mg_get_inventory() 반환값 조건부 [완료 02-26]
- **파일**: morgan.php — mg_get_inventory()
- page/limit 있으면 `{items, total}`, 없으면 순수 배열
- **수정**: 항상 `{items, total}` 반환으로 통일 + `inventory.php` 호출처 수정

### H2. mg_send_gift() gf_type 미명시 [완료]
- **파일**: morgan.php — mg_send_gift()
- **수정**: `gf_type='shop'` 명시

### H3. 상점 타입 정의 분산 (4곳) [완료]
- **수정**: `mg_get_item_types()` 함수를 단일 소스로 통합, shop_item_form.php에서 호출

### H4. insert_point() rel 파라미터 생략 [완료 02-26]
- `mg_buy_item()`, `mg_send_gift()`, 이모티콘 구매/판매에서 4개 인자만 전달
- **수정**: 모든 호출처에 6개 인자(`rel_table`, `rel_id`, `rel_action`) 전달

### H5. 이모티콘 파일 크기 제한 불일치 [완료 02-26]
- 관리자: 1MB 하드코딩 / 유저: `mg_upload_max_icon()` (2MB)
- **수정**: 관리자도 `mg_upload_max_icon()` 함수로 통일

### H6. point_manage.php:369 — URL sfl 미인코딩 [완료]
- **수정**: `urlencode($sfl)` 적용

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
