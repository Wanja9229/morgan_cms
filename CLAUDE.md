# Morgan Edition CMS

## 비전

현재 그누보드5 기반 CMS. 향후 그누보드 불필요 부분을 제거하여 **자체 CMS**로 전환 예정.
Morgan 자체 시스템(캐릭터, 상점, 역극, 개척, 보상, 업적, 인장, 세계관 위키 등)에 집중하는 방향.

## 환경

- Docker: nginx(`morgan_nginx`:8080), php-fpm(`morgan_php`), mysql(`morgan_mysql`:3307)
- DB: `morgan_db` / `morgan_user` / `morgan_pass`
- PHP syntax check: `docker exec morgan_php php -l //var/www/html/path/to/file.php` (Windows: `//` 필수)
- SQL 실행: `docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "SQL"`
- 한국어 INSERT 시: `--default-character-set=utf8mb4` 플래그 필수
- 배포: GitHub Actions → git-ftp push (Cafe24)

## 코드 패턴

- **테이블 등록**: `$g5['mg_*_table']` (morgan.php line ~40-57) + `$mg['*_table']` 호환 (line ~75-112)
- **설정**: `mg_config('key', 'default')` / `mg_get_config()` / `mg_set_config()`
- **알림**: `mg_notify($mb_id, $type, $title, $content, $url)`
- **포인트**: `insert_point($mb_id, $amount, $content, $rel_table, $rel_id, $rel_action)`
- **관리자 페이지**: `$sub_menu` → `auth_check_menu()` → `_head.php` → content → `_tail.php`
- **관리자 메뉴**: `adm/admin.menu800.php` - 배열 `[ID, name, URL, permission_key, group_name]`
- **RP 함수**: `$mg` 글로벌 사용, admin/API는 `$g5` 글로벌 사용
- **게시판**: per-board write 테이블 (`write_{bo_table}`), JOIN 불가 → 개별 쿼리

## 디자인 시스템

디스코드 스타일 다크테마, Tailwind CSS v4.

**실제 CSS 변수** (head.sub.php):
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

**Tailwind 주의사항**:
- pre-built CSS 사용 (v4.1.18). 일부 responsive variant (`lg:ml-14` 등) 빌드에 누락 → `head.sub.php`에 수동 CSS 추가됨
- 사이드바 브레이크포인트: `lg` (1024px). 이하에서 햄버거 메뉴
- 터치 타겟 최소 44x44px
- 상세: `docs/plans/UI.md`, `docs/plans/DESIGN_ASSETS.md` 참조

**SPA 라우터** (`app.js`):
- 내부 링크 클릭 → fetch → `#main-content` 교체 → `updateSidebar()` 호출
- 제외: `data-no-spa` 속성, `/adm/`, `/logout.php`, `/download.php`
- AJAX 모드: `head.sub.php`에서 감지 → `#ajax-content` wrapper

## Phase 진행률

### 완료 (Phase 1~18)
1. 테마 기본 구조 (Tailwind, 다크테마, 사이드바)
2. 캐릭터 시스템 (등록, 승인, 프로필, 세력/종족)
3. 포인트/출석 (출석체크, 주사위 게임)
4. 메인 빌더 (위젯, 드래그앤드롭)
5. 상점 시스템 (상품, 인벤토리, 선물)
6. 역극(RP) 시스템 (판 세우기, 이음, 완결)
7. 이모티콘 시스템 (셋 관리, 구매, 삽입, 유저 제작)
8. 알림 시스템 (벨 아이콘, 토스트, 트리거)
9. 개척 시스템 (노동력, 재료, 시설 건설, 명예의 전당)
10. 관리자 대시보드 (통계 카드, 위젯, 최근활동)
11. 게시판 스킨 4종 (basic, gallery, memo, postit)
12. 보상 시스템 (게시판 보상, RP 보상, 좋아요, 정산)
13. 업적 시스템 (DB 4테이블, 트리거, 쇼케이스, 토스트)
14. 인장 시스템 (시그니처카드, 편집/미리보기, 마이페이지)
15. 세계관 위키 (DB 5테이블: lore_category/article/section/era/event, admin CRUD, 프론트, 타임라인)
16. 프롬프트 미션 (DB 2테이블: prompt/prompt_entry, admin CRUD+리뷰, 게시판 스킨, write hook)
17. 캐릭터 관계 (관계도, vis.js 그래프, UI 재배치)
18. 댓글 주사위 + 이모티콘 피커 보완
19. 탐색 파견 (DB 3테이블, 관리자 CRUD+로그, 프론트 AJAX UI, 관계 기반 파트너+20% 보너스)

### 미구현 (1차)
- **Phase 20**: 의뢰 매칭 (창작 협업 의뢰/지원/매칭) → `docs/plans/CONCIERGE.md`

### 2차 작업 (별도 진행)
- **2차-A**: 연구 트리 (공동 투자, 시설 해금) → `docs/plans/RESEARCH_TREE.md`
- **2차-B**: SS Engine (세미 턴제 RPG) → `docs/plans/SS_ENGINE_DESIGN.md`
- **2차-C**: 진영 컨텐츠 (익명망 + 카드배틀 + 점령전) → `docs/plans/FACTION.md`

### 프리미엄 모듈 (별도 패키지)
- VN Engine, 마이룸 → `docs/MODULES.md`

## 작업 규칙

1. **모든 응답은 한국어로 작성** (코드/명령어 제외)
2. **Phase 작업 시 반드시 해당 plan 문서를 먼저 읽을 것**: `docs/plans/{해당_시스템}.md`를 확인한 후 작업 시작. DB 스키마, API 설계, UI 기획이 모두 포함되어 있음.
3. **Git push는 사용자 요청 시에만** 실행
4. **PHP syntax check** 후 커밋
5. 새 테이블 추가 시 `morgan.php`에 `$g5` + `$mg` 양쪽 등록

## 상세 문서

| 문서 | 경로 |
|------|------|
| 핵심 참조 (DB, 파일, 헬퍼) | `docs/CORE.md` |
| 전체 로드맵 | `docs/ROADMAP.md` |
| 2차 모듈 전략 | `docs/MODULES.md` |
| 환경 세팅 | `docs/SETUP.md` |
| UI 디자인 시스템 | `docs/plans/UI.md` |
| 디자인 에셋 목록 | `docs/plans/DESIGN_ASSETS.md` |
| DB 전체 스키마 | `docs/plans/DB.md` |
| Phase별 기획서 | `docs/plans/{시스템명}.md` |
