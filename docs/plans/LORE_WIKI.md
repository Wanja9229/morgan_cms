# Phase 15: 세계관 위키 (Lore Wiki)

## Context

세계관 설정을 관리하는 위키형 시스템. 관리자가 문서(Article)와 타임라인(Timeline)을 작성·관리하고, 유저는 프런트에서 열람.
**핵심 원칙**: 텍스트와 이미지 업로드만 지원. 표, 카드, 복잡한 서식 없음.
구간(섹션) 기반 — 각 섹션에 이름을 붙이고, 텍스트 또는 이미지를 넣는 방식.

참고: `F:\projects\vn_engine\moonveil\pages` (moonveil 위키) 구조 분석 기반.

---

## DB 테이블 (5개)

### mg_lore_category
| 컬럼 | 타입 | 설명 |
|------|------|------|
| lc_id | int AUTO_INCREMENT PK | 카테고리 ID |
| lc_name | varchar(100) NOT NULL | 카테고리명 (예: 종족, 지역, 인물, 조직) |
| lc_order | int DEFAULT 0 | 정렬 순서 |
| lc_use | tinyint DEFAULT 1 | 사용 여부 |

### mg_lore_article
| 컬럼 | 타입 | 설명 |
|------|------|------|
| la_id | int AUTO_INCREMENT PK | 문서 ID |
| lc_id | int NOT NULL | 카테고리 FK |
| la_title | varchar(200) NOT NULL | 문서 제목 |
| la_subtitle | varchar(300) DEFAULT NULL | 부제목 |
| la_thumbnail | varchar(500) DEFAULT NULL | 썸네일 이미지 경로 |
| la_summary | text DEFAULT NULL | 목록용 한줄 요약 |
| la_order | int DEFAULT 0 | 카테고리 내 정렬 |
| la_use | tinyint DEFAULT 1 | 공개 여부 |
| la_hit | int DEFAULT 0 | 조회수 |
| la_created | datetime DEFAULT CURRENT_TIMESTAMP | 작성일 |
| la_updated | datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE | 수정일 |

### mg_lore_section
| 컬럼 | 타입 | 설명 |
|------|------|------|
| ls_id | int AUTO_INCREMENT PK | 섹션 ID |
| la_id | int NOT NULL | 문서 FK |
| ls_name | varchar(200) NOT NULL | 섹션 제목 (TOC에 표시) |
| ls_type | enum('text','image') DEFAULT 'text' | 콘텐츠 타입 |
| ls_content | text DEFAULT NULL | 텍스트 내용 (type=text) |
| ls_image | varchar(500) DEFAULT NULL | 이미지 경로 (type=image) |
| ls_image_caption | varchar(300) DEFAULT NULL | 이미지 캡션 |
| ls_order | int DEFAULT 0 | 섹션 순서 |

### mg_lore_era
| 컬럼 | 타입 | 설명 |
|------|------|------|
| le_id | int AUTO_INCREMENT PK | 시대 ID |
| le_name | varchar(200) NOT NULL | 시대명 (예: 창세기, 대전쟁기) |
| le_period | varchar(100) DEFAULT NULL | 기간 표기 (예: 0~500년) |
| le_order | int DEFAULT 0 | 정렬 순서 |
| le_use | tinyint DEFAULT 1 | 사용 여부 |

### mg_lore_event
| 컬럼 | 타입 | 설명 |
|------|------|------|
| lv_id | int AUTO_INCREMENT PK | 이벤트 ID |
| le_id | int NOT NULL | 시대 FK |
| lv_year | varchar(50) DEFAULT NULL | 연도 표기 (예: 132년, ???년) |
| lv_title | varchar(200) NOT NULL | 이벤트 제목 |
| lv_content | text DEFAULT NULL | 이벤트 설명 |
| lv_image | varchar(500) DEFAULT NULL | 이벤트 이미지 (선택) |
| lv_is_major | tinyint DEFAULT 0 | 주요 이벤트 여부 (강조 표시) |
| lv_order | int DEFAULT 0 | 시대 내 순서 |
| lv_use | tinyint DEFAULT 1 | 사용 여부 |

---

## 파일 구조

### 핵심 함수 (`plugin/morgan/morgan.php` 추가)
- 테이블 등록: `$g5['mg_lore_category_table']` 등 5개 + `$mg` 호환
- 상수: `MG_LORE_IMAGE_PATH`, `MG_LORE_IMAGE_URL`
- `mg_get_lore_categories()` — 활성 카테고리 목록 (lc_use=1, ORDER BY lc_order)
- `mg_get_lore_articles($lc_id = 0, $page = 1, $per_page = 12)` — 카테고리별 문서 목록 (0=전체)
- `mg_get_lore_article($la_id)` — 문서 + 섹션 전체 조회 (JOIN)
- `mg_get_lore_timeline()` — 시대 + 이벤트 전체 조회 (2중 배열)
- `mg_upload_lore_image($file, $type, $id)` — 이미지 업로드 (article/section/event)

### 프런트엔드 (`bbs/`)
- `bbs/lore.php` — 위키 메인 (카테고리 탭 + 문서 카드 그리드)
- `bbs/lore_view.php` — 문서 상세 (자동 TOC + 섹션 렌더링 + 사이드바 관련 문서)
- `bbs/lore_timeline.php` — 타임라인 페이지 (시대별 이벤트 세로 타임라인)

### 관리자 (`adm/morgan/`)
- `adm/morgan/lore_article.php` — 문서 관리 (목록 + 카테고리 필터)
- `adm/morgan/lore_article_edit.php` — 문서 편집 (제목/부제/카테고리/요약/썸네일 + 섹션 동적 추가·삭제·정렬)
- `adm/morgan/lore_article_update.php` — 문서 저장 (POST)
- `adm/morgan/lore_timeline.php` — 타임라인 관리 (시대 + 이벤트 CRUD)
- `adm/morgan/lore_timeline_update.php` — 타임라인 저장 (POST)
- `adm/morgan/lore_category.php` — 카테고리 관리 (간단한 목록형 CRUD)
- `adm/morgan/lore_image_upload.php` — 이미지 업로드 AJAX

---

## 관리자 UI 상세

### 문서 편집 (`lore_article_edit.php`)
```
┌─────────────────────────────────────────────┐
│ [카테고리 선택 ▼] [공개 ON/OFF]  [정렬순서] │
│ 제목: [________________________]             │
│ 부제목: [________________________]           │
│ 한줄 요약: [________________________]        │
│ 썸네일: [이미지 선택] [미리보기]              │
├─────────────────────────────────────────────┤
│ ■ 섹션 관리                                  │
│                                              │
│ [섹션 1] ▲▼                                  │
│   섹션명: [개요___________]                  │
│   타입: (●) 텍스트  ( ) 이미지               │
│   내용: [textarea........................]   │
│         [.............................]       │
│                               [삭제]         │
│                                              │
│ [섹션 2] ▲▼                                  │
│   섹션명: [지도___________]                  │
│   타입: ( ) 텍스트  (●) 이미지               │
│   이미지: [파일 선택] [미리보기]              │
│   캡션: [세계 전도_______]                   │
│                               [삭제]         │
│                                              │
│ [+ 섹션 추가]                                │
├─────────────────────────────────────────────┤
│              [저장]  [목록]                   │
└─────────────────────────────────────────────┘
```

- 섹션은 JS로 동적 추가·삭제, ▲▼ 버튼으로 순서 변경
- 이미지는 AJAX 업로드 → 즉시 미리보기
- 저장 시 전체 폼 POST (섹션 배열: `sections[0][name]`, `sections[0][type]`, ...)
- 기존 업적 관리자(`achievement.php`) CRUD 패턴 참고

### 타임라인 관리 (`lore_timeline.php`)
```
┌─────────────────────────────────────────────┐
│ ■ 시대 관리                [+ 시대 추가]     │
│                                              │
│ ┌ 창세기 (0~500년) ──────── [편집] [삭제] ┐ │
│ │                     [+ 이벤트 추가]      │ │
│ │  132년 · 최초의 불꽃 ★      [편집][삭제] │ │
│ │  245년 · 종족의 분화          [편집][삭제] │ │
│ │  389년 · 대륙 형성 ★         [편집][삭제] │ │
│ └──────────────────────────────────────────┘ │
│                                              │
│ ┌ 대전쟁기 (501~800년) ──── [편집] [삭제] ┐ │
│ │  ...                                     │ │
│ └──────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

- 시대/이벤트 편집은 인라인 모달 (기존 업적 관리자 패턴)
- ★ = 주요 이벤트 (is_major=1)
- 이벤트에 이미지 첨부 가능 (선택)

---

## 프런트엔드 UI 상세

### 위키 메인 (`lore.php`)
```
┌─────────────────────────────────────────────┐
│ 세계관 위키                                   │
│                                              │
│ [전체] [종족] [지역] [인물] [조직] [타임라인→]│
│                                              │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐     │
│ │ 썸네일    │ │ 썸네일    │ │ 썸네일    │     │
│ │          │ │          │ │          │     │
│ │ 제목     │ │ 제목     │ │ 제목     │     │
│ │ 한줄요약  │ │ 한줄요약  │ │ 한줄요약  │     │
│ └──────────┘ └──────────┘ └──────────┘     │
└─────────────────────────────────────────────┘
```

- 카테고리 탭 필터링 (URL param `?category=X`, 기존 업적 카테고리 탭 패턴)
- 마지막 탭: "타임라인 →" 링크 (`lore_timeline.php`로 이동)
- 카드: 썸네일 + 제목 + 한줄요약 (클릭 → `lore_view.php?la_id=X`)
- 썸네일 없으면 기본 placeholder 아이콘

### 문서 상세 (`lore_view.php`)
```
┌───────────────────────────────────┬─────────┐
│                                   │ 목차     │
│ [카테고리명]                       │ ─ 개요   │
│ 제목                              │ ─ 역사   │
│ 부제목                            │ ─ 문화   │
│                                   │ ─ 지도   │
│ ───────────────────────────────── │         │
│                                   │─────────│
│ ■ 개요                            │ 관련 문서│
│ 텍스트 내용이 여기에 표시.          │ · 문서A  │
│ 줄바꿈은 nl2br()로 처리.           │ · 문서B  │
│                                   │         │
│ ■ 지도                            │         │
│ [이미지]                          │         │
│ 캡션: 세계 전도                    │         │
└───────────────────────────────────┴─────────┘
```

- 좌측 본문: 카테고리 뱃지 + 제목 + 부제 + 섹션들
- 우측 사이드바: 자동 생성 TOC (클릭→smooth scroll) + 같은 카테고리 관련 문서
- 텍스트 섹션: `nl2br(htmlspecialchars())` (줄바꿈 보존, XSS 방지)
- 이미지 섹션: 이미지 + 캡션
- 모바일: 사이드바 → 본문 상단 토글형 목차로 변환

### 타임라인 (`lore_timeline.php`)
```
┌─────────────────────────────────────────────┐
│ 타임라인                  [← 위키로 돌아가기] │
│                                              │
│ ════ 창세기 (0~500년) ════                    │
│                                              │
│        132년                                 │
│     ●──┤ 최초의 불꽃                          │
│        │ 설명 텍스트...                       │
│        │ [이미지]                             │
│        │                                     │
│        245년                                 │
│     ○──┤ 종족의 분화                          │
│        │ 설명 텍스트...                       │
│                                              │
│ ════ 대전쟁기 (501~800년) ════                │
│        ...                                   │
└─────────────────────────────────────────────┘
```

- 세로 타임라인 (CSS: 중앙선 + 노드)
- 주요 이벤트(is_major): accent 색상 ● 큰 노드 + 강조 텍스트
- 일반 이벤트: 작은 ○ 노드
- 시대 구분: 시대명 + 기간 표기
- Morgan 다크 테마: `bg-mg-bg-secondary`, `border-mg-bg-tertiary`, accent 색상

---

## 사이드바 아이콘

`theme/morgan/head.php`에 위키 아이콘 추가:
- 위치: 캐릭터 목록 아이콘 뒤, 구분선 앞 (비로그인도 표시)
- 아이콘: 책(BookOpen) SVG
- 링크: `lore.php`
- 활성 감지: `lore.php`, `lore_view.php`, `lore_timeline.php`

---

## 관리자 메뉴

`adm/admin.menu800.php`에 추가 (인장 뒤, 그룹 '세계관'):
```php
array('801400', '위키 카테고리', $mg_admin_url.'/lore_category.php', 'mg_lore', '세계관'),
array('801410', '위키 문서',     $mg_admin_url.'/lore_article.php',  'mg_lore', '세계관'),
array('801420', '타임라인',      $mg_admin_url.'/lore_timeline.php', 'mg_lore', '세계관'),
```

---

## mg_config 설정값

| 키 | 기본값 | 설명 |
|----|--------|------|
| lore_use | 1 | 전체 ON/OFF |
| lore_image_max_size | 2048 | 이미지 최대 크기 (KB) |
| lore_thumbnail_max_size | 500 | 썸네일 최대 크기 (KB) |
| lore_articles_per_page | 12 | 목록 페이지당 문서 수 |

---

## 구현 순서 (4단계)

### 15.1 DB + 테이블 등록 + 핵심 함수
- `install.sql`에 5개 테이블 CREATE TABLE
- `morgan.php`에 테이블 등록 5개 + 상수 + 핵심 함수 5개
- mg_config 기본값 4개 삽입
- `config.php` / `config_update.php`에 위키 설정 섹션 추가

### 15.2 관리자 페이지
- `lore_category.php` — 카테고리 CRUD (목록형, 인라인 편집)
- `lore_article.php` + `lore_article_edit.php` + `lore_article_update.php` — 문서 CRUD + 섹션 동적 관리
- `lore_timeline.php` + `lore_timeline_update.php` — 시대/이벤트 CRUD
- `lore_image_upload.php` — 이미지 업로드 AJAX
- `admin.menu800.php`에 메뉴 3개 추가

### 15.3 프런트엔드
- `lore.php` — 위키 메인 (카테고리 탭 + 문서 카드 그리드)
- `lore_view.php` — 문서 상세 (TOC + 섹션 렌더링 + 사이드바)
- `lore_timeline.php` — 타임라인 (시대별 세로 타임라인)

### 15.4 사이드바 + 마무리
- `head.php` 사이드바에 위키 아이콘 추가
- PHP syntax check 전체
- 테스트 데이터 삽입으로 동작 확인

---

## 수정 파일 목록

| # | 파일 | 작업 |
|---|------|------|
| 1 | `plugin/morgan/install/install.sql` | 5개 테이블 CREATE TABLE |
| 2 | `plugin/morgan/morgan.php` | 테이블 등록 5개, 상수, 핵심 함수 5개 |
| 3 | `adm/admin.menu800.php` | 세계관 메뉴 3개 추가 |
| 4 | `adm/morgan/lore_category.php` | **신규** — 카테고리 관리 |
| 5 | `adm/morgan/lore_article.php` | **신규** — 문서 목록/관리 |
| 6 | `adm/morgan/lore_article_edit.php` | **신규** — 문서 편집 폼 |
| 7 | `adm/morgan/lore_article_update.php` | **신규** — 문서 저장 |
| 8 | `adm/morgan/lore_timeline.php` | **신규** — 타임라인 관리 |
| 9 | `adm/morgan/lore_timeline_update.php` | **신규** — 타임라인 저장 |
| 10 | `adm/morgan/lore_image_upload.php` | **신규** — 이미지 업로드 AJAX |
| 11 | `adm/morgan/config.php` | 위키 설정 섹션 추가 |
| 12 | `adm/morgan/config_update.php` | 위키 설정키 4개 추가 |
| 13 | `bbs/lore.php` | **신규** — 위키 메인 |
| 14 | `bbs/lore_view.php` | **신규** — 문서 상세 |
| 15 | `bbs/lore_timeline.php` | **신규** — 타임라인 |
| 16 | `theme/morgan/head.php` | 사이드바 위키 아이콘 추가 |

---

## 검증

1. PHP syntax check — 신규/수정 파일 전체
2. DB 테이블 생성 확인 (DESCRIBE 5개 테이블)
3. 관리자 카테고리 CRUD 동작
4. 관리자 문서 생성 → 섹션 추가(텍스트+이미지) → 저장 → 재편집
5. 관리자 타임라인 시대/이벤트 CRUD 동작
6. 프런트 위키 메인 → 카테고리 탭 필터링
7. 프런트 문서 상세 → TOC 스크롤 + 섹션 렌더링 + 사이드바
8. 프런트 타임라인 → 시대별 이벤트 표시 + 주요 이벤트 강조
9. 이미지 업로드 → 표시 확인
10. lore_use=0 → 사이드바 아이콘 숨김 + 페이지 접근 차단
