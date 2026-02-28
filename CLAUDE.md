# Morgan Edition CMS

## 비전

현재 그누보드5 기반 CMS. 향후 그누보드 불필요 부분을 제거하여 **자체 CMS**로 전환 예정.
Morgan 자체 시스템(캐릭터, 상점, 역극, 개척, 보상, 업적, 인장, 세계관 위키 등)에 집중하는 방향.

## 기본 규칙

1. **모든 응답은 한국어로 작성** (코드, 명령어, 변수명 제외)
2. **Git push는 사용자 요청 시에만** 실행
3. **PHP syntax check** 후 커밋 — 편집한 모든 파일 대상으로 실행
4. Phase 작업 시 반드시 해당 plan 문서를 먼저 읽을 것: `docs/plans/{해당_시스템}.md`
5. 새 테이블 추가 시 `morgan.php`에 `$g5` + `$mg` 양쪽 등록

## 환경

- Docker: nginx(`morgan_nginx`:8080), php-fpm(`morgan_php`), mysql(`morgan_mysql`:3307)
- DB: `morgan_db` / `morgan_user` / `morgan_pass`
- PHP syntax check: `docker exec morgan_php php -l //var/www/html/path/to/file.php` (Windows: `//` 필수)
- SQL 실행: `docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "SQL"`
- 한국어 INSERT 시: `--default-character-set=utf8mb4` 플래그 필수
- 배포: GitHub Actions → git-ftp push (Cafe24)

## DB 변경 (직접 실행)

DB 변경이 필요할 때 Docker 컨테이너에 직접 실행한다. 별도 승인 불필요.

### 실행 방법
```bash
# DDL (테이블 생성/변경)
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "CREATE TABLE IF NOT EXISTS ..."

# DML (데이터 삽입/수정) — 한국어 포함 시 charset 플래그 필수
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass --default-character-set=utf8mb4 morgan_db -e "INSERT INTO ..."
```

### 마이그레이션 파일 병행
로컬 적용과 동시에 `db/migrations/YYYYMMDD_HHMMSS_설명.sql` 파일도 작성한다.

- **파일명**: `YYYYMMDD_HHMMSS_snake_case_설명.sql` (알파벳순 = 시간순)
- **멱등성 보장**: `INSERT IGNORE`, `IF NOT EXISTS` 등 사용 (중복 실행해도 안전)
- URL은 root-relative 경로 사용 (`/data/...` — 도메인 제외)
- `mg_migrations` 테이블이 적용 이력 추적
- `morgan.php` 로드 시 세션당 1회 자동 체크 → 미적용 파일 순서대로 실행
- 관련 파일: `db/migrations/*.sql`, `plugin/morgan/migrate.php`

## 코드 패턴

### 핵심 함수
| 패턴 | 사용법 |
|------|--------|
| 테이블 등록 | `$g5['mg_*_table']` (morgan.php line ~40-57) + `$mg['*_table']` 호환 (line ~75-112) |
| 설정 | `mg_config('key', 'default')` / `mg_get_config()` / `mg_set_config()` |
| 알림 | `mg_notify($mb_id, $type, $title, $content, $url)` |
| 포인트 | `insert_point($mb_id, $amount, $content, $rel_table, $rel_id, $rel_action)` |
| 이모티콘 렌더링 | `mg_render_emoticons($html)` — 댓글/게시글 출력 시 사용 |

### 관리자 페이지 구조
```php
$sub_menu = '800100';  // admin.menu800.php의 메뉴 ID
auth_check_menu($auth, $sub_menu, 'r');  // 권한 체크
include_once('./_head.php');
// ... 페이지 내용 ...
include_once('./_tail.php');
```
- 관리자 메뉴 등록: `adm/admin.menu800.php` — 배열 `[ID, name, URL, permission_key, group_name]`

### 관리자 설정 추가 시
1. `adm/morgan/config.php`에 UI 추가
2. `adm/morgan/config_update.php`의 `$config_keys` 배열에 키 추가
3. 프론트에서 `mg_config('key', 'default')`로 읽기

### 게시판 관련
- per-board write 테이블 (`write_{bo_table}`), JOIN 불가 → 개별 쿼리
- RP 함수: `$mg` 글로벌 사용, admin/API는 `$g5` 글로벌 사용

## 디자인 시스템

디스코드 스타일 다크테마, Tailwind CSS v4.

### CSS 변수 (head.sub.php)
| 변수 | 기본값 | 용도 |
|------|--------|------|
| `--mg-bg-primary` | `#1e1f22` | 페이지 배경 |
| `--mg-bg-secondary` | `#2b2d31` | 카드/패널 배경 |
| `--mg-bg-tertiary` | `#313338` | 입력/구분선 |
| `--mg-text-primary` | `#f2f3f5` | 주요 텍스트 |
| `--mg-text-secondary` | `#b5bac1` | 보조 텍스트 |
| `--mg-text-muted` | `#949ba4` | 비활성 텍스트 |
| `--mg-accent` | `#f59f0a` | 강조/활성 (앰버) |
| `--mg-accent-hover` | `#d97706` | 강조 호버 |

### Tailwind 주의사항
- **pre-built CSS 사용 (v4.1.18)**. 동적으로 생성되는 클래스는 빌드에 포함되지 않음
- 누락된 클래스는 `head.sub.php` 하단 `<style>` 블록에 수동 CSS로 추가해야 함
- 이미 추가된 수동 CSS: `sm:inline-flex`, `sm:inline`, `px-2.5`, `lg:ml-14` 등
- 새 Tailwind 클래스가 동작하지 않으면 → head.sub.php에 수동 CSS 추가 필요
- 사이드바 브레이크포인트: `lg` (1024px). 이하에서 햄버거 메뉴
- 터치 타겟 최소 44x44px

### SPA 라우터 (`app.js`)
- 내부 링크 클릭 → fetch → `#main-content` 교체 → `updateSidebar()` 호출
- 제외: `data-no-spa` 속성, `/adm/`, `/logout.php`, `/download.php`
- AJAX 모드: `head.sub.php`에서 감지 → `#ajax-content` wrapper
- **사이드바 항목 추가/변경 시**: `head.php` (HTML) + `app.js` (updateSidebar 라우팅) 양쪽 수정 필요

## 자주 참조하는 파일

작업 시 반복적으로 읽게 되는 핵심 파일들:

### 레이아웃/네비게이션
| 파일 | 내용 | 언제 참조 |
|------|------|----------|
| `theme/morgan/head.php` | 사이드바 HTML, 헤더, 페이지 감지 변수 | 사이드바 항목 추가/변경 |
| `theme/morgan/js/app.js` | SPA 라우터, updateSidebar(), 사이드바 활성 상태 | SPA 라우팅 수정 |
| `theme/morgan/head.sub.php` | CSS 변수, 수동 CSS 오버라이드, JS/CSS CDN | 스타일 수정, CSS 누락 |

### 스킨
| 파일 | 내용 | 비고 |
|------|------|------|
| `theme/morgan/skin/board/basic/view_comment.skin.php` | 댓글 폼 (이모티콘+주사위+캐릭터) | 5개 게시판 스킨이 모두 include |
| `theme/morgan/skin/rp/list.skin.php` | RP 목록+답글 폼+참여자 관리 | PHP+JS 혼합, 큰 파일 |
| `theme/morgan/skin/board/prompt/list.skin.php` | 미션 카드 목록+상세 모달 | |
| `theme/morgan/skin/emoticon/picker.skin.php` | 이모티콘 피커 컴포넌트 | `$picker_id`, `$picker_target` 필수 |

### 백엔드
| 파일 | 내용 | 비고 |
|------|------|------|
| `plugin/morgan/morgan.php` | 테이블 등록, 헬퍼 함수, 마이그레이션 훅 | 모든 Morgan 기능의 진입점 |
| `adm/morgan/config.php` | 관리자 설정 UI (3섹션) | config_update.php와 세트 |
| `adm/morgan/config_update.php` | 설정 저장 ($config_keys 배열) | 새 설정 키 추가 시 |

## 알려진 패턴 & 주의사항

### 댓글 스킨 공유
5개 게시판 스킨(basic, gallery, memo, postit, prompt)의 댓글은 모두 `board/basic/view_comment.skin.php`를 include한다. 댓글 관련 수정은 이 파일 하나만 수정하면 전체 반영됨.

### 이모티콘 피커 사용법
```php
$picker_id = 'comment';        // 고유 ID (한 페이지에 여러 피커 가능)
$picker_target = 'wr_content'; // 삽입 대상 textarea의 name 속성
include(G5_THEME_PATH.'/skin/emoticon/picker.skin.php');
```
- `mg-emoticon-btn` CSS 클래스로 버튼 스타일 통일

### 주사위 버튼 노출 조건
주사위 🎲 버튼은 해당 게시판의 보상 설정에서 `br_dice_use`가 활성화되어야 표시됨. 관리자 → 보상 관리 → 해당 게시판 → 주사위 사용 ON.

### 사이드바 1뎁스 항목 추가 순서
`head.php`의 사이드바 순서: 홈 → 세계관 → 캐릭터 목록 → (구분선) → 게시판 → 역극 → 미션 → 의뢰 → (구분선) → 상점 → 알림 → 개척

### PHP 8 호환성
- `sql_num_rows()`에 `false` 전달 시 Fatal error → 반드시 `$result !== false` 체크
- `htmlspecialchars()` null 전달 시 deprecation → `??` 연산자로 기본값 처리

## Phase 진행률

### 1차 완료 (Phase 1~18) — 검수 및 테스트 진행중
1. 테마 기본 구조 (Tailwind, 다크테마, 사이드바)
2. 캐릭터 시스템 (등록, 승인, 프로필, 세력/종족)
3. 포인트/출석 (출석체크, 주사위 게임)
4. 메인 빌더 (위젯, 드래그앤드롭)
5. 상점 시스템 (상품, 인벤토리, 선물)
6. 역극(RP) 시스템 (판 세우기, 이음, 완결)
7. 이모티콘 시스템 (셋 관리, 구매, 삽입, 유저 제작)
8. 알림 시스템 (벨 아이콘, 토스트, 트리거)
9. 관리자 대시보드 (통계 카드, 위젯, 최근활동)
10. 개척 시스템 (노동력, 재료, 시설 건설, 명예의 전당)
11. 보상 시스템 (게시판 보상, RP 보상, 좋아요, 정산)
12. 업적 시스템 (DB 4테이블, 트리거, 쇼케이스, 토스트)
13. 인장 시스템 (시그니처카드, 편집/미리보기, 마이페이지)
14. 세계관 위키 (DB 5테이블, admin CRUD, 프론트, 타임라인)
15. 미션 시스템 (DB 2테이블, admin CRUD+리뷰, 게시판 스킨, write hook)
16. 캐릭터 관계 (관계도, vis.js 그래프, UI 재배치)
17. 탐색 파견 + 댓글 주사위 + 이모티콘 피커 보완
18. 의뢰 매칭 (DB 3테이블, 의뢰/지원/매칭/추첨, write hook, 보상 티어)

### 2차 작업 (별도 진행) → `docs/ROADMAP_PHASE2.md`
- **2차-A**: 연구 트리 (공동 투자, 시설 해금) → `docs/plans/RESEARCH_TREE.md`
- **2차-B**: SS Engine (세미 턴제 RPG) → `docs/plans/SS_ENGINE_DESIGN.md`
- **2차-C**: 진영 컨텐츠 (익명망 + 카드배틀 + 점령전) → `docs/plans/FACTION.md`
- **2차-D**: 마이룸 (아이소메트릭 2D 방 꾸미기) → `docs/MODULES.md`
- **2차-E**: VN Engine (비주얼 노벨) → `docs/plans/VN_ENGINE.md`
- **2차-F**: SRPG (그리드 Co-op PvE) → `docs/plans/morgan_srpg_system_plan.md`
- **2차-G**: 스킨 SDK — 배경 레이어 분리 + 파일 기반 커스텀 스킨 시스템
- **2차-H**: 레이아웃 시스템 — 헤더/사이드바/위젯 교체형 멀티 레이아웃 (Discord/TopNav/Classic 3종)

### 멀티테넌트 + R2 (SaaS 전환) → `docs/ROADMAP_MULTITENANT.md`
- **MT-0**: 테넌트 라우터 (서브도메인 식별, DB 동적 교체)
- **MT-1**: DB 격리 (캐시/세션/경로 테넌트 분리)
- **MT-2**: 스토리지 추상화 (Cloudflare R2 드라이버, 15개 업로드 포인트 전환)
- **MT-3**: 프로비저닝 (슈퍼 관리자, 테넌트 자동 생성)
- **MT-4**: 보안 & 격리 (파일/세션/리소스 제한)
- **MT-5**: 베타 배포 인프라 (VPS, 온보딩, 모니터링, 백업)

## 상세 문서

| 문서 | 경로 |
|------|------|
| 핵심 참조 (DB, 파일, 헬퍼) | `docs/CORE.md` |
| 전체 로드맵 | `docs/ROADMAP.md` |
| 2차 로드맵 | `docs/ROADMAP_PHASE2.md` |
| 멀티테넌트 + R2 로드맵 | `docs/ROADMAP_MULTITENANT.md` |
| 2차 모듈 전략 | `docs/MODULES.md` |
| 환경 세팅 | `docs/SETUP.md` |
| UI 디자인 시스템 | `docs/plans/UI.md` |
| 디자인 에셋 목록 | `docs/plans/DESIGN_ASSETS.md` |
| DB 전체 스키마 | `docs/plans/DB.md` |
| Phase별 기획서 | `docs/plans/{시스템명}.md` |
