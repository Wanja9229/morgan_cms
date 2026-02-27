# 베타테스터 가이드 PDF 빌드 가이드

## 파일 구조

```
recruit/
├── README.md                 ← 이 문서
├── capture_admin_guide.py    ← Step 1: 스크린샷 캡처
├── generate_guide_pdf.py     ← Step 2: HTML 빌드 + PDF 변환
├── preview_pdf.py            ← (선택) HTML 미리보기 + 이미지 깨짐 체크
├── pdf_images/               ← 캡처된 스크린샷 (자동 생성)
│   ├── f_home.png            ← 프론트엔드 페이지들
│   ├── tut_char_01_list.png  ← 튜토리얼 (하이라이트 포함)
│   └── ...
├── guide_source.html         ← PDF 원본 HTML (자동 생성)
├── 모건빌더_베타테스터_가이드.pdf  ← 최종 PDF (자동 생성)
└── pdf_preview/              ← HTML 미리보기 스크린샷 (선택)
```

## 환경 준비

```bash
pip install playwright
playwright install chromium
```

## 실행 순서

### Step 1: 스크린샷 캡처

```bash
cd test_screenshots/recruit
python capture_admin_guide.py
```

**전제 조건:**
- Docker 컨테이너 가동 중 (`localhost:8080` 접속 가능)
- admin/admin 계정으로 로그인 가능
- 캐릭터, 파견지, 보상 등 시드 데이터가 등록된 상태

**출력:** `pdf_images/` 폴더에 PNG 27장 생성

**스크린샷 교체만 하는 경우:** 이 단계만 다시 실행하면 됨.
사이트에서 이미지/데이터를 수정한 뒤 이 스크립트만 재실행하면 `pdf_images/`가 덮어쓰기됨.

### Step 2: PDF 생성

```bash
python generate_guide_pdf.py
```

**출력:**
- `guide_source.html` — PDF 원본 HTML
- `모건빌더_베타테스터_가이드.pdf` — 최종 PDF (~8MB)

### (선택) 미리보기 확인

```bash
python preview_pdf.py
```

이미지 로드 상태를 검증하고 `pdf_preview/`에 스크린샷 저장.

## 스크린샷만 교체하고 싶을 때

사이트에서 이미지/데이터 교체 후:

```bash
python capture_admin_guide.py   # pdf_images/ 재생성
python generate_guide_pdf.py    # PDF 재생성
```

이 두 줄이면 끝. 본문 텍스트는 변경되지 않음.

## 캡처 대상 목록

### 프론트엔드 (14장, 뷰포트 1440×900)

| 파일명 | 페이지 | URL |
|--------|--------|-----|
| f_home | 메인 | `/` |
| f_character | 캐릭터 목록 | `/bbs/character_list.php` |
| f_lore | 세계관 위키 | `/bbs/lore.php` |
| f_lore_timeline | 타임라인 | `/bbs/lore_timeline.php` |
| f_shop | 상점 | `/bbs/shop.php` |
| f_inventory | 인벤토리 | `/bbs/inventory.php` |
| f_attendance | 출석체크 | `/bbs/attendance.php` |
| f_pioneer | 개척 | `/bbs/pioneer.php` |
| f_expedition | 파견 | `/bbs/pioneer.php?view=expedition` |
| f_achievement | 업적 | `/bbs/achievement.php` |
| f_notification | 알림 | `/bbs/notification.php` |
| f_seal_edit | 인장 편집기 | `/bbs/seal_edit.php` |
| f_mypage | 마이페이지 | `/bbs/mypage.php` |
| f_rp | 역극 | `/bbs/rp_list.php` |

### 튜토리얼 — 캐릭터 승인 (2장, 하이라이트)

| 파일명 | 내용 | 하이라이트 |
|--------|------|-----------|
| tut_char_01_list | 캐릭터 관리 목록 | ①상태 필터 ②첫 행 선택 |
| tut_char_02_form | 캐릭터 상세 폼 | ①상태 변경 드롭다운 ②저장 버튼 |

### 튜토리얼 — 보상 설정 (3장, 하이라이트)

| 파일명 | 내용 | 하이라이트 |
|--------|------|-----------|
| tut_reward_01_board | 보상 관리 (게시판 탭) | ①탭 메뉴 |
| tut_reward_02_activity | 보상 관리 (활동 탭) | — |
| tut_reward_03_form | 보상 관리 (추가/수정) | ①추가/수정 버튼 |

### 튜토리얼 — 파견지 세팅 (3장, 하이라이트)

| 파일명 | 내용 | 하이라이트 |
|--------|------|-----------|
| tut_exped_01_list | 파견지 관리 목록 | ①추가 버튼 ②첫 행 선택 |
| tut_exped_02_form | 파견지 상세 폼 | — |
| tut_exped_03_event | 파견 이벤트 관리 | — |

### 관리자 추가 (5장)

| 파일명 | 페이지 |
|--------|--------|
| adm_config | 관리자 설정 |
| adm_board_list | 게시판 관리 |
| adm_shop_list | 상점 아이템 관리 |
| adm_emoticon | 이모티콘 관리 |
| adm_achievement | 업적 관리 |

## PDF 본문 구성 & 참고 출처

### 문서 구조

| 섹션 | 내용 | 사용 이미지 |
|------|------|------------|
| 표지 | "모건 빌더 베타 테스터 가이드" | — |
| 모건 빌더란? | 소개, 핵심 특징 | f_home |
| 기본 시스템 | 캐릭터 + 승인 튜토리얼, 게시판, 세계관 위키 | f_character, tut_char_*, f_lore, f_lore_timeline |
| 활동/보상 (P7 확장) | 포인트 흐름도, 출석, 게시판별 보상, 보상 튜토리얼, 업적, 주사위, 인장, 알림 | f_attendance, tut_reward_*, f_achievement, f_seal_edit, f_notification |
| 커뮤니티 콘텐츠 | RP, 상점/인벤토리, 개척, 파견 + 세팅 튜토리얼, 기타 | f_rp, f_shop, f_inventory, f_pioneer, f_expedition, tut_exped_*, f_mypage |
| 관리자 설정 개요 | 메뉴별 기능 표 | adm_config, adm_board_list, adm_shop_list |
| 준비 중인 콘텐츠 | 2차 로드맵 요약 | — |
| 기술 환경 + 데모 | 기술 스택, 데모 계정 | — |

### 본문 텍스트 출처

PDF 본문은 `generate_guide_pdf.py` 안의 HTML 문자열에 직접 작성되어 있음.
수정하려면 해당 파일의 `HTML = f'''...'''` 블록을 편집 후 재실행.

참고한 프로젝트 문서:
- `CLAUDE.md` — Phase 진행률, 시스템 목록
- `docs/ROADMAP.md` — 1차 Phase 1~18 기능 목록
- `docs/ROADMAP_PHASE2.md` — 2차 준비 중 콘텐츠
- `docs/plans/DB.md` — 테이블/시스템 관계
- 관리자 페이지 실제 UI — 튜토리얼 단계별 설명

### 스타일

- 폰트: Noto Sans KR (Google Fonts CDN, PDF 생성 시 자동 로드)
- 색상: 앰버(#f59f0a) 강조, 디스코드 다크(#1e1f22) 표지
- 용지: A4, 마진 18/14/20/14mm
- 하이라이트: 주황 outline + 번호 뱃지 + 콜아웃 화살표

## 본문 텍스트 수정

`generate_guide_pdf.py`의 `HTML = f'''...'''` 블록이 PDF 전체 내용.
Python f-string이므로 `{`, `}` 는 `{{`, `}}`로 이스케이프됨에 주의.
이미지 경로는 `{img('파일명')}` 헬퍼로 `pdf_images/파일명.png` 생성.

### 섹션 추가/삭제

HTML 주석(`<!-- ═══ 섹션명 ═══ -->`)으로 구분되어 있음.
`page-break` 클래스가 있는 `<h1>`은 새 페이지에서 시작.

### 튜토리얼 추가

```html
<div class="tut-box">
  <h3>튜토리얼: 제목</h3>
  <div class="step">
    <div class="step-num">1</div>
    <div class="step-text"><strong>경로</strong> 설명</div>
  </div>
  <div class="shot large"><img src="pdf_images/이미지명.png"></div>
  <div class="caption">캡션</div>
</div>
```

## 알려진 이슈

- `adm_achievement.png`: 업적 관리 페이지가 "File not found" 반환 (URL 확인 필요)
- 웹폰트 로딩: PDF 변환 시 3초 대기(`wait_for_timeout(3000)`) — 네트워크 느리면 증가 필요
- PDF 한글 파일명: Windows 콘솔에서 깨져 보이지만 실제 파일은 정상
