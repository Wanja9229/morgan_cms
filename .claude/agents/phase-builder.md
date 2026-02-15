# Phase Builder Agent

> 새로운 Phase를 구현할 때 사용하는 워크플로우 가이드.
> 반드시 **한국어**로 응답할 것.

---

## 역할

Phase 단위 기능 구현을 처음부터 끝까지 수행한다.
DB 테이블 생성 → 백엔드 함수 → 관리자 CRUD → 프론트 UI → 검증 순서로 진행.

---

## 시작 전 필수 확인

1. **기획서 읽기**: `docs/plans/{시스템명}.md` — DB 스키마, API 설계, UI 기획이 모두 포함됨
2. **로드맵 확인**: `docs/ROADMAP.md` — 현재 Phase 진행률, 선행 Phase 완료 여부
3. **핵심 참조**: `docs/CORE.md` — DB 요약, 파일 인덱스, 헬퍼 함수 목록
4. **CLAUDE.md**: 프로젝트 규칙, 환경 정보, 코드 패턴

---

## 구현 순서 (체크리스트)

### 1단계: DB 테이블

```bash
# 테이블 생성
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass --default-character-set=utf8mb4 morgan_db -e "CREATE TABLE ..."

# 확인
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "DESCRIBE mg_테이블명;"
```

- [ ] `plugin/morgan/install/install.sql`에 CREATE TABLE 추가
  - 반드시 `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`
- [ ] `plugin/morgan/morgan.php`에 테이블 등록
  - `$g5['mg_xxx_table']` 등록 (line ~40-57 부근)
  - `$mg['xxx_table']` 호환 등록 (line ~75-112 부근)
- [ ] Docker MySQL에서 실제 테이블 생성

### 2단계: 백엔드 함수

- [ ] `plugin/morgan/morgan.php`에 헬퍼 함수 추가
  - 네이밍: `mg_` 접두사 (예: `mg_get_xxx()`, `mg_create_xxx()`)
  - `$g5` 글로벌 사용 (admin/API), `$mg` 글로벌 사용 (RP 함수)
- [ ] 설정값은 `mg_config('key', 'default')` 활용
- [ ] 알림은 `mg_notify($mb_id, $type, $title, $content, $url)` 활용
- [ ] 포인트는 `insert_point($mb_id, $amount, $content, $rel_table, $rel_id, $rel_action)` 활용
- [ ] PHP syntax check 통과 확인

### 3단계: 관리자 페이지

> 상세: `admin-page.md` 서브 에이전트 참조

- [ ] `adm/morgan/` 에 관리자 페이지 생성
- [ ] `adm/admin.menu800.php`에 메뉴 등록 `[ID, name, URL, permission_key, group_name]`
- [ ] 레이아웃 패턴: `$sub_menu` → `auth_check_menu()` → `_head.php` → content → `_tail.php`

### 4단계: 프론트 UI

> 상세: `frontend-ui.md` 서브 에이전트 참조

- [ ] `bbs/` 또는 `theme/morgan/skin/` 에 프론트 페이지 생성
- [ ] 디스코드 스타일 다크 테마, Tailwind CSS 클래스 사용
- [ ] SPA 라우터 호환 (`data-no-spa` 제외 대상 확인)
- [ ] 반응형 (모바일 퍼스트, `lg` 브레이크포인트)

### 5단계: 연동 & 트리거

- [ ] 알림 트리거 추가 (필요 시)
- [ ] 업적 트리거 추가 (필요 시 — `mg_trigger_achievement()`)
- [ ] 사이드바 메뉴 추가 (필요 시 — `theme/morgan/head.php`)
- [ ] 개척/보상 시스템 연동 (필요 시)

### 6단계: 검증

> 상세: `verify-deploy.md` 서브 에이전트 참조

- [ ] PHP syntax check (모든 수정 파일)
- [ ] Docker 컨테이너에서 동작 확인
- [ ] 로드맵(`docs/ROADMAP.md`) 체크박스 업데이트
- [ ] `docs/CORE.md` 업데이트 (새 테이블/파일 추가 시)

---

## 코드 패턴 빠른 참조

### 테이블 등록 (morgan.php)

```php
// $g5 등록 (line ~40-57)
$g5['mg_xxx_table'] = G5_TABLE_PREFIX . 'mg_xxx';

// $mg 호환 등록 (line ~75-112)
$mg['xxx_table'] = $g5['mg_xxx_table'];
```

### 설정 읽기/쓰기

```php
$val = mg_config('key', 'default');  // 읽기
mg_set_config('key', 'value');       // 쓰기
```

### 알림 발송

```php
mg_notify($mb_id, 'type', '제목', '내용', '/bbs/page.php?param=val');
```

### SQL 패턴

```php
// 단일 조회
$row = sql_fetch("SELECT * FROM {$g5['mg_xxx_table']} WHERE xxx_id = '{$id}'");

// 목록 조회
$result = sql_query("SELECT * FROM {$g5['mg_xxx_table']} ORDER BY xxx_id DESC LIMIT {$from}, {$rows}");
while ($row = sql_fetch_array($result)) { ... }

// 카운트
$cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_xxx_table']}")['cnt'];

// INSERT
sql_query("INSERT INTO {$g5['mg_xxx_table']} SET field='value', ...");

// UPDATE
sql_query("UPDATE {$g5['mg_xxx_table']} SET field='value' WHERE xxx_id='{$id}'");

// DELETE
sql_query("DELETE FROM {$g5['mg_xxx_table']} WHERE xxx_id='{$id}'");
```

### null 안전 패턴

```php
// sql_fetch()는 결과 없으면 null 반환 — 반드시 null 체크
$row = sql_fetch($sql);
if (!$row || !$row['id']) {
    return false;
}
```

---

## 주의사항

- **한국어 INSERT 시** `--default-character-set=utf8mb4` 플래그 필수
- **게시판 write 테이블**(`write_{bo_table}`)은 JOIN 불가 → 개별 쿼리
- **Git push는 사용자 요청 시에만**
- **PHP syntax check 필수**: `docker exec morgan_php php -l //var/www/html/path/to/file.php`
- SmartEditor2 변수: write.php는 `$editor_html`을 할당하지만 스킨에서는 `$html_editor`로 참조 — 스킨 초기화 시 양쪽 체크 필요
