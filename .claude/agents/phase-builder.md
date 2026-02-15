# Phase Builder — 새 Phase 구현 워크플로우

한국어로 응답. 작업 전 반드시 `docs/plans/{시스템}.md` 읽기.

## 구현 순서

DB → morgan.php 등록 → 백엔드 함수 → 관리자 → 프론트 → 연동 → 검증

## DB 규칙

- `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- 테이블명: `mg_` 접두사
- PK 네이밍: `{약어}_id` (예: `fc_id`, `rt_id`, `ch_id`)
- 한국어 INSERT 시 `--default-character-set=utf8mb4` 필수
- install.sql 위치: `plugin/morgan/install/install.sql`

## morgan.php 테이블 등록 (2곳 필수)

```
$g5 블록: line 23~81  → $g5['mg_xxx_table'] = 'mg_xxx';
$mg 블록: line 100~158 → $mg['xxx_table'] = $g5['mg_xxx_table'];
```

시스템별 주석 구분 유지 (예: `// 개척 시스템`, `// 보상 시스템`).

## 백엔드 함수 패턴

- 함수명: `mg_` 접두사 (현재 159개, 마지막 line ~5740)
- 글로벌: admin/API는 `$g5`, RP 관련은 `$mg`
- sql_fetch null 체크 필수: `if (!$row || !$row['id'])`
- 설정: `mg_config('key', 'default')`
- 알림: `mg_notify($mb_id, $type, $title, $content, $url)`
- 포인트: `insert_point($mb_id, $amount, $content, $rel_table, $rel_id, $rel_action)`

## 관리자 페이지 → `admin-page.md` 참조

## 프론트 UI → `frontend-ui.md` 참조

## 연동 체크

- 알림 트리거 → `mg_notify()`
- 업적 트리거 → `mg_trigger_achievement()`
- 사이드바 메뉴 → `theme/morgan/head.php`
- 개척/보상 연동 → `mg_get_board_reward()`, 개척 재료 드롭

## 검증 → `verify-deploy.md` 참조

## 마무리

- `docs/ROADMAP.md` 체크박스 갱신
- `docs/CORE.md` 테이블/파일 인덱스 갱신 (새 항목 시)
