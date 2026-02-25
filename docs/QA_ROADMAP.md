# Morgan Edition - QA 로드맵

> 작성일: 2026-02-20
> 최종 업데이트: 2026-02-25 (개척/파견 시스템 검수 + 포인트 보상 + UI 통일)

---

## 개요

1차 기능 개발(Phase 1~18) + 잔여 구현(M1~M6) 완료 후 진행 중인 **QA/검수** 현황.
관리자 페이지 기준으로 정리하며, 각 페이지별 기능 QA + 프론트 연동 검증을 포함.

**범례**
- [x] QA 완료 (사용자가 직접 확인 + 필요 시 수정 반영)
- [C] 코드 검수 완료 (Claude가 코드 리딩 + 버그 수정 → **사용자 확인 대기**)
- [-] 부분 검수 (일부 수정 완료, 추가 확인 필요)
- [ ] 미검수

---

### ⚠️ QA 진행 원칙

> **코드 무결성 QA는 이미 여러 차례 완료됨.**
> 앞으로의 QA는 **사용자가 브라우저에서 직접 확인 → 피드백 → 수정** 방식으로 진행.
>
> Claude는 **사용자가 항목을 지정하거나 피드백을 줄 때만** 해당 항목을 작업한다.
> 혼자서 QA 항목을 순서대로 죽 진행하지 않는다.
>
> **관리자 사이드바 탭(그룹) 단위로 진행한다.**
> 한 그룹의 코드 검수가 끝나면 사용자가 브라우저에서 실제 동작을 확인한다.
> 사용자 확인이 완료된 그룹만 다음으로 넘어간다.
> (Claude가 혼자 여러 그룹을 연달아 진행해도 결국 사용자와 다시 돌아와서 봐야 하므로)
>
> **세션 시작 시**: 이 문서를 읽고 "현재 진행 위치"를 확인한 뒤, 사용자에게 무엇을 할지 물어본다.

---

### 📍 현재 진행 위치: **개척/파견 그룹 QA (Phase E) 코드 검수 완료 → 사용자 확인 대기**

Phase A(설정) + Phase B(회원/캐릭터/인장) + Phase D(활동: 출석/알림/업적) = 완료.
Phase C(콘텐츠): 의뢰 E2E PASS, 미션/역극 코드 검수 완료, 뷰페이지 리디자인, 댓글 버그 수정.
Phase D(재화/상점): **코드 분석 완료(02-25)** — Critical 6건 + 일관성 6건 + 함수화 5건. → `docs/QA_SHOP_ISSUES.md`
Phase E(개척/파견): **코드 검수 완료(02-25)** — DB 테이블 생성, 시드 데이터, UI 통일, 포인트 보상 추가, 마이그레이션 수정.
다음: 사용자 브라우저 확인 → Phase D(상점) Critical 수정.

---

## 1. 설정 그룹

### 1.1 대시보드 (`dashboard.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 통계 카드 5종 (회원/캐릭터대기/역극/포인트/좋아요) | [-] | 반응형 확인 필요 |
| 정산 대기 카드 + 검수 대기 카드 (M4) | [-] | M4에서 추가됨 |
| 승인 요청 캐릭터 위젯 | [ ] | |
| 최신 게시글/역극/포인트/구매 위젯 | [ ] | |
| 정산 대기열 위젯 | [ ] | |
| 역극 완결 위젯 | [ ] | |
| 활성 미션 위젯 (M4) | [-] | M4에서 추가됨 |
| 반응형 레이아웃 (모바일/태블릿/데스크탑) | [ ] | |

### 1.2 기본 설정 (`config.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 사이트 기본 설정 (사이트명, 설명 등) | [ ] | |
| 닉네임 변경 주기 (M3) | [x] | g5_config 연동 확인됨 |
| 가입 기본 레벨 (M3) | [x] | g5_config 연동 확인됨 |
| 기능별 최소 레벨 게이트 6종 (M3) | [x] | RP/의뢰/개척/인장/이모티콘/미션 |
| 미션 설정 (보상 금액, 태그 등) | [ ] | |
| 의뢰 설정 (슬롯, 동시지원, 보상, 페널티) | [-] | M2에서 개편됨 |
| 설정 저장/로드 정상 동작 | [ ] | |

### 1.3 스태프 관리 (`staff.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 역할 생성/수정/삭제 | [x] | M5에서 구현 |
| 26개 권한군 체크 UI | [x] | |
| 스태프 목록 (회원 검색, 역할 배정/해제) | [x] | |
| 다중 역할 지원 | [x] | |
| g5_auth 동기화 | [x] | |
| 관리자 사이드바 권한 필터링 | [x] | |

### 1.4 디자인 관리 (`design.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| **테마 색상 탭** | | |
| 배경색 (color_bg) | [x] | |
| 카드/패널색 (color_card) | [x] | |
| 강조색 (color_accent) | [x] | |
| Border/입력필드 색상 (color_border) | [x] | 라벨 수정 완료 |
| 버튼 배경색 (color_button) | [x] | 라벨 수정 완료 |
| 버튼 글자색 (color_button_text) | [x] | #000 하드코딩 수정 |
| 글자 색상 (color_text) | [x] | 보조/비활성 자동 파생 |
| 색상 초기화 버튼 | [x] | 새 키 포함 확인됨 |
| **배경 이미지 탭** | | |
| 배경 이미지 업로드/삭제 | [ ] | |
| 배경 투명도 슬라이더 | [x] | 의미 반전 버그 해결 |
| 배경 블러 설정 | [ ] | |
| **폰트 탭** | | |
| Google Fonts 24종 한국어 폰트 | [x] | |
| 프리뷰 즉시 반영 | [x] | |
| **빌더 탭** | | |
| GridStack 2D 캔버스 빌더 | [x] | |
| 위젯 추가 모달 (타입 선택) | [x] | |
| 미션 달력 위젯 등록 | [x] | mg_get_widget_types() 등록 |
| 위젯 카드 설정/삭제 버튼 | [x] | 우상단 absolute 배치 |
| 슬라이더 위젯 썸네일 미리보기 | [x] | 모달 내 이미지 프리뷰 |
| 위젯 설정 모달 (각 타입별) | [-] | 슬라이더 확인, 나머지 미검수 |
| 위젯 삭제 | [ ] | |
| 레이아웃 저장/로드 | [-] | 저장은 확인, 로드 후 정렬 미확인 |
| 프론트 렌더링 일치 확인 | [-] | 달력 위젯 확인, 전체 미검수 |

---

## 2. 세계관 그룹

### 2.1 위키 카테고리 (`lore_wiki.php?tab=categories`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 카테고리 CRUD | [x] | 추가/인라인수정/삭제 (문서 있으면 삭제 불가) |
| 정렬 순서 | [x] | 순서 관리 탭, 드래그앤드롭 + 터치 지원 |
| 프론트 카테고리 필터 연동 | [x] | lore.php 카테고리 필 탭 |
| 반응형 (터치 드래그) | [x] | 카테고리/문서 정렬 모두 터치 이벤트 추가, 44px 핸들 |

### 2.2 위키 문서 (`lore_wiki.php?tab=articles`, `lore_article_edit.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 문서 CRUD (섹션별 분류) | [x] | 작성/수정/삭제, 텍스트+이미지 섹션, 순서 이동 |
| Toast UI Editor 연동 | [x] | WYSIWYG + HTML 모드 전환, 이미지 업로드 훅 |
| 상호 링크 | [-] | 패스 — 카테고리 기반 관련문서로 대체 중. 기획 상세 없음, 필요시 추가 |
| 프론트 열람 (lore.php, lore_view.php) | [x] | 카드 그리드 목록 + 상세 (TOC, 관련문서, 브레드크럼) |
| 반응형 (관리자) | [x] | 섹션 그리드 flex-wrap, 이미지 영역 flex-wrap |

### 2.3 타임라인 (`lore_timeline.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 시대(Era) CRUD | [x] | 추가/수정/삭제 모달, 이벤트 있으면 삭제 불가 |
| 사건(Event) CRUD | [x] | 추가/수정/삭제 모달, 이미지 업/삭제, 위키문서 연결 |
| 시대/이벤트 정렬 | [x] | 드래그 정렬 + 터치 지원, 이벤트 시대 간 이동 |
| 프론트 타임라인 시각화 | [x] | 시대 구분자 + 이벤트 카드 + 주요 이벤트 강조 |
| 페이지 설명 설정 | [x] | 타임라인 관리 페이지에서 인라인 편집 |
| 모달 반응형 (grid 폴백) | [x] | `auto-fit minmax(200px,1fr)` |
| 반응형 (터치 타겟/flex-wrap) | [x] | 핸들 44px, 이미지 영역 flex-wrap |

### 2.4 지도 (`lore_map.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 맵 이미지 업로드 | [x] | 관리자 지도 설정 탭 |
| 마커 4종 (pin/circle/diamond/flag) | [x] | 마커별 개별 스타일 선택 |
| 지역 CRUD | [x] | mg_map_region 독립 테이블 |
| 마커 배치 에디터 | [x] | 맵 클릭+드래그, 터치 이벤트 지원 |
| 마커-지역 연결 | [x] | 연결 모달에서 스타일 선택+지역 연결 |
| 프론트 맵 페이지 | [x] | mg_map_region 기반, 팝업 닫기 버튼, 반응형 |
| 페이지 설명 설정 | [x] | 지도 설정 탭에서 수정 가능 |
| 사이드바 2뎁스 | [x] | 전체/연대기/지도 순서, lore_map.php 활성 상태 |
| 프론트 서브탭 3페이지 통일 | [x] | 위키/연대기/지도 탭 일관성 |
| 반응형 검수 | [x] | 모달 패딩, 터치 타겟 44px, 팝업 뷰포트 클램핑 |

---

## 3. 회원/캐릭터 그룹

### 3.1 회원 관리 (`member_list.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 회원 목록/검색 | [ ] | |
| 회원 상세 (포인트, 출석, 캐릭터 등) | [ ] | |
| 포인트 수동 지급/차감 | [ ] | ⚠️ insert_point() 미사용 (로그 미생성) |
| 반응형 | [x] | 테이블 overflow-x:auto, 폼 min-width 축소 |

### 3.2 캐릭터 관리 (`character_list.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 캐릭터 목록/검색 | [ ] | |
| 승인/반려 워크플로우 | [x] | 개별/일괄 반려 + 사유 모달 + editing 되돌림 |
| 반려 사유 입력 + 알림 | [x] | log_memo + character_rejected 알림 발송 |
| 프론트 캐릭터 등록/수정 | [-] | 반려 사유 알림 박스 추가됨 |
| 프론트 캐릭터 목록/상세 | [-] | 반려 배지 + 사유 표시 추가됨 |
| 삭제 시 이미지 정리 | [x] | ch_image + th_ 썸네일 삭제 누락 수정 |
| 반응형 | [x] | 테이블 min-width, 프론트 grid 반응형 수정 |

### 3.3 프로필 필드 관리 (`profile_field.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 필드 CRUD (6종 타입) | [ ] | |
| 필드 정렬 순서 | [x] | 드래그 정렬 (섹션 간 + 섹션 내), 터치 지원, AJAX 저장 |
| 프론트 캐릭터 등록폼 연동 | [ ] | |
| 반응형 | [x] | 그리드 min-width + overflow-x:auto, 드래그 핸들 44px |

### 3.4 소속/유형 관리 (`side_class.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 소속/유형 CRUD | [x] | 소속/유형 명칭 변경, Heroicons+이미지 하이브리드 아이콘 |
| 소속별 유형 연동 | [x] | mg_class.side_id (0=공용, >0=특정 소속 전용) |
| 캐릭터 등록 시 선택 연동 | [x] | JS 필터링 (소속 변경 시 유형 자동 필터) |
| 접기/드래그 정렬 | [x] | 마우스+터치, AJAX 저장 |
| 사용법 가이드 | [x] | 페이지 하단 |

### 3.5 관계 관리 (`relation.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 관계 목록/검색 | [x] | M1에서 코드 정리, 상태필터+검색 |
| 강제 해제/강제 승인 | [x] | AJAX 처리 |
| 프론트 관계도 (vis.js) | [x] | BFS 깊이 탐색 + 배치 저장 |
| 관계 신청/승인/거절 | [x] | 전체 플로우 + 알림 + 업적 트리거 |
| 보낸 신청 취소 | [x] | cfCancelRequest() 추가 |
| 유저 커스텀 아이콘/색상 | [x] | 프리셋 폐지 → 자유 설정 |
| 관계도 레이아웃 저장 | [x] | ch_graph_layout JSON |
| 반응형 | [x] | overflow-x:auto + min-width 추가 |

### 3.6 인장 관리 (`seal.php`) ✅

| 항목 | 상태 | 비고 |
|------|------|------|
| 인장 목록/검색 | [x] | 회원ID/닉네임/한마디 검색, 페이징 |
| 인장 검열/강제 초기화/삭제 | [x] | on/off/reset/delete AJAX |
| 프론트 인장 편집/미리보기 | [x] | 단일 페이지 통합, GridStack 위젯 클릭→속성 패널, 실시간 미리보기 |
| 인장 저장 (레이아웃) | [x] | GnuBoard addslashes → stripslashes 수정, fd.set 명시적 설정 |
| 이미지 업로드/URL | [x] | 600×200 리사이즈, 5형식 지원 |
| 배경/프레임/호버 상점 연동 | [x] | seal_bg, seal_frame, seal_hover 아이템 타입 |
| 트로피 (업적 쇼케이스) | [x] | 아이콘만 표시, 호버 시 이름 표시 |
| 게시글 view 인장 표시 | [x] | 4개 게시판 스킨 + margin-top 간격 |
| 캐릭터 프로필 인장 표시 | [x] | 11개 프로필 스킨 + margin-top 간격, JS↔PHP 렌더링 일치 |
| 역극 이음 compact 모드 | [x] | rp_api.php compact 모드 |
| 요소별 스타일/정렬 | [x] | 글자/배경/테두리색 + 좌/가운데/우 정렬 + 텍스트 패딩 |
| 닉네임/한마디 세로 크기 | [x] | maxH:1→3 변경 |
| **격자 16×4 개편** | [x] | 16×6→16×4, 눈금자, 요소 maxH 조정 |
| **인장 배경색 (무료)** | [x] | seal_bg_color 컬럼, 색상 팔레트+커스텀 피커 |
| **테두리 아이템 5종** | [x] | seal_frame: 골드/실버/네온/점선/그림자 |
| **호버 아이템 4종** | [x] | seal_hover: 앰버/블루 글로우, 스케일업, 그림자 드랍 |
| **프론트 셀 스타일** | [x] | 배경색+보더+라운딩 — 편집 영역과 일치 |
| 관리자 설정 11개 키 | [x] | |
| 관리자 인장 관리 UI | [x] | 헤더 텍스트 제거 |
| 반응형 | [x] | 모바일 폴백(입력 폼) + PC GridStack |

---

## 4. 활동 그룹

### 4.1 출석 관리 (`attendance.php`) ✅

| 항목 | 상태 | 비고 |
|------|------|------|
| 출석 통계 (일별 수, 기간 조회) | [x] | 기간 필터 + 일별/상세 목록 + 페이지네이션 |
| 출석 상세 목록 | [x] | 회원ID/닉네임/게임/포인트/일시/IP |
| 프론트 출석 페이지 | [x] | 게임 UI + 결과 표시 + 월간 달력 |
| 운세뽑기 / 종이뽑기 (M6) | [x] | 운세 CRUD + 확률 가중치, 종이뽑기 판/등수 CRUD |
| 야찌 족보 시스템 | [x] | 6종 족보 포인트 설정 + 리롤 횟수 |
| API 멀티스텝 (주사위) | [x] | roll → reroll(×N) → finalize + 세션 5분 만료 |
| 업적/재료 연동 | [x] | mg_trigger_achievement + mg_reward_material |
| 반응형 | [x] | 900px 미디어 쿼리 |

### 4.2 알림 관리 (`notification.php`) ✅

| 항목 | 상태 | 비고 |
|------|------|------|
| 알림 목록/삭제 | [x] | 관리자: 검색+선택삭제+읽음처리, 프론트: 개별삭제+읽은알림삭제+전체읽음 |
| 트리거별 알림 발송 확인 | [x] | 28종 타입 — 댓글/추천/RP/캐릭터/선물/이모티콘/관계/미션/의뢰/파견/보상/업적/시스템 |
| 프론트 벨 아이콘 + 드롭다운 | [x] | 30초 폴링, 미읽음 뱃지(99+), 클릭→읽음→이동, 전체 읽음 |
| 토스트 알림 | [x] | 폴링 카운트 증가 감지 → 최신 1건 슬라이드 토스트 (5초 자동 퇴장) |
| 반응형 | [x] | 테이블 min-width:750px 추가 |
| 🔧 SQL 인젝션 수정 | [x] | 관리자 검색 $stx → sql_real_escape_string() 적용 |
| 🔧 XSS 수정 | [x] | 검색어 HTML 출력 → htmlspecialchars() 적용 |
| 🔧 타입 목록 보완 | [x] | 관리자+프론트 양쪽 28종 전체 등록 (기존 12종→28종) |

### 4.3 업적 관리 (`achievement.php`) ✅

| 항목 | 상태 | 비고 |
|------|------|------|
| 업적 목록 (카테고리/희귀도) | [x] | 6개 카테고리, 5개 희귀도, 활성/비활성 토글 |
| 단계(Tier) 관리 | [x] | 단계별 목표값/아이콘/보상 CRUD, 인라인 편집 |
| 조건 빌더 + 보상 빌더 | [x] | 15종 조건유형, 3종 보상(포인트/재료/아이템), JSON 유효성 검증 |
| 수동 부여/회수 (일괄) | [x] | 회원 검색→다중선택→일괄 부여, 개별 회수(진행도+쇼케이스 초기화) |
| 프론트 업적 목록/진행률 | [x] | 카테고리 필터, 진행 바, 단계형 다음 단계 표시, 숨김 업적(???) |
| 프로필 쇼케이스 (5슬롯) | [x] | 체크박스 5개 제한, AJAX 저장, mg_render_achievement_showcase() |
| 업적 달성 토스트 알림 | [x] | mg_achievement_notify() + 세션 토스트 + mg_notify 알림 |
| 반응형 | [x] | 폼 그리드 768px 미디어 쿼리 추가 |

---

## 5. 콘텐츠 그룹

### 5.1 역극 관리 (`rp_list.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 역극 목록/검색 | [C] | 코드 검수 완료 — 상태/제목/회원/캐릭터 검색, 통계 카드 |
| 역극 설정 (최소 글자수, 참여조건) | [C] | 코드 검수 완료 — reward.php activity탭 6개 설정값 |
| 프론트 역극 목록/보기 | [C] | 코드 검수 완료 — rp_close.php false 체크 수정 |
| 이음 작성 (AJAX) | [C] | 코드 검수 완료 — 10단계 검증 + 보상 + 업적 트리거 |
| 판 완결 (자동/수동) | [C] | 코드 검수 완료 — 개별/전체/자동 완결 |
| 이모티콘 피커 (RP 이음) | [C] | 코드 검수 완료 — mg_render_emoticons() 연동 |
| 멀티캐릭터 | [C] | 코드 검수 완료 — rr_context_ch_id 상호이음 |
| 반응형 | [x] | 테이블 min-width:900px 추가 |

### 5.2 게시판 관리 (`board_list.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 게시판 목록 | [C] | 코드 검수 완료 — 제목 검색, 보상 상태 표시 |
| 게시판 그룹 UI 숨김 | [x] | gr_id 하드코딩 'community', 관리자 그룹 드롭다운/컬럼 제거 |
| 게시판별 보상 설정 | [C] | 코드 검수 완료 — reward.php 5개 탭 |
| 프론트 게시판 스킨 5종 | [C] | 코드 검수 완료 — Tailwind 반응형, 캐릭터 연결 |
| 로드비 스킨 4종 (신규) | [x] | lb_terminal/lb_intranet/lb_corkboard/lb_default — 인라인 댓글+모달 글쓰기 |
| 로드비 공유 include 3파일 | [x] | _lb_common.php, _lb_modal_inner.php, _lb_modal_assets.php |
| write 스킨 이모티콘 피커 제거 | [x] | basic/memo/postit/concierge write.skin.php — 에디터 툴바로 통합 |
| 댓글 UI (이모티콘+주사위+캐릭터) | [C] | 코드 검수 완료 — 공용 스킨 + 수정 기능 추가됨 |
| 게시글 좋아요 보상 | [C] | 코드 검수 완료 — mg_like_apply_reward() 연동 |
| 댓글 주사위 (br_dice_use) | [C] | 코드 검수 완료 — once/max 설정, 댓글 자동 생성 |
| 검색 기능 | [x] | sql_num_rows 버그 수정됨 |
| 반응형 | [x] | 테이블 min-width:1000px 추가 |

### 5.3 미션 관리 (`prompt.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 미션 CRUD | [C] | 코드 검수 완료 — Toast UI Editor + 이미지 업로드 훅 |
| 미션 복제 (M4) | [C] | 코드 검수 완료 — clone_id로 데이터 복사 |
| auto/review/vote 모드 | [C] | 코드 검수 완료 — 3종 보상 로직 |
| 태그 필터링 (M4) | [C] | 코드 검수 완료 — 클라이언트 사이드 JS 필터 |
| 미션별 참여 통계 (M4) | [C] | 코드 검수 완료 |
| 일괄 승인 + 선정작 지정 | [x] | "우수작"→"선정작" 용어 변경, 승인/반려/보너스 + 보상 일괄 지급 |
| 프론트 미션 카드 목록 | [C] | 코드 검수 완료 — D-day 표시, 태그 필터 |
| **프론트 자유글쓰기 차단** | [x] | 미션 선택 필수, 미션 없으면 글쓰기 비활성화 |
| write hook 연결 | [C] | 🔧 write_update.php에 mg_prompt_after_write() 호출 추가 |
| 글 삭제 시 엔트리 정리 | [C] | 🔧 delete.php에 mg_prompt_entry + mg_write_character 정리 추가 |
| **관리자 목록 테이블** | [x] | 컬럼 너비 수정 (제목 세로 찍힘 해결) |
| 미션 달력 위젯 | [x] | widget 등록 완료 |
| 반응형 | [x] | 폼 그리드 768px 미디어 쿼리 + 모달 max-height 추가 |

### 5.4 의뢰 관리 (`concierge.php`) ✅

| 항목 | 상태 | 비고 |
|------|------|------|
| 의뢰 목록/검색 | [x] | E2E 검증 — 상태/타입 필터, 만료 자동 처리 |
| 관리자 설정 (M2 개편) | [x] | E2E 검증 — 7개 config 키 |
| 관리자 강제 종료/완료 | [x] | E2E 검증 — 관리자 취소 + 미이행 카운트 + 강제완료 배지 |
| 프론트 3탭 (market/my/results) | [x] | E2E 검증 — 3탭 구조 완료, PHP 경고 0건 |
| 지원/매칭/추첨 | [x] | E2E 검증 — 직접선택 + 추첨(boost x2) |
| 정산/부분제출 | [x] | E2E 검증 — settle + force settle + 미제출분 환불 |
| 페널티 시스템 | [x] | E2E 검증 — force_close 카운트 증가 확인 |
| 전용 게시판 (concierge_result) | [x] | 🔧 테이블 접두사 수정 (`write_` → `g5_write_`) |
| 포인트/업적 연동 | [x] | E2E 검증 — 전체 포인트 정합성 확인 (mb_point = SUM(po_point)) |
| 무보수 의뢰 (0P) | [x] | E2E 검증 — 포인트 변동 없이 정상 완료 |
| 의뢰 수정 유연성 | [x] | E2E 검증 — 제목 변경 OK, 포인트 변경 무시 |
| 자동 만료/자동 강제종료 | [x] | E2E 검증 — 모집마감+수행마감 자동 처리 + 환불 |

> **E2E 시나리오 테스트 (02-23)**
> 10개 시나리오 전부 PASS: 직접매칭, 추첨, 부분제출+정산, 강제종료, 자동만료,
> 자동강제종료, 모집중취소, 수행자강제완료, 무보수의뢰, 수정유연성.
> 발견 버그 2건 모두 수정 완료.

---

## 6. 재화/상점 그룹

> **코드 분석 완료 (02-25)**. Critical 6건 + 일관성 6건 + 함수화 5건 식별.
> 상세: `docs/QA_SHOP_ISSUES.md`

### 6.1 포인트 관리 (`point_manage.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 포인트 내역 조회 | [C] | 코드 검수 완료 — 검색/필터/페이징 정상 |
| 수동 지급/차감 | [C] | 🔧 point_manage_update.php $member 미정의 수정 필요 |
| URL 인코딩 | [C] | 🔧 sfl 파라미터 urlencode 누락 |

### 6.2 보상 관리 (`reward.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 게시판별 보상 설정 (5탭) | [C] | 코드 검수 완료 |
| auto/request 모드 설정 | [C] | 코드 검수 완료 |
| 정산 대기열 (승인/반려/일괄) | [C] | 🔧 일괄 전체실패 시 success:true 반환 |
| 주사위 설정 (br_dice_use/once/max) | [C] | 🔧 새 게시판 생성 시 주사위 미저장 (board_form_update.php) |
| 좋아요 보상 설정 | [C] | 코드 검수 완료 |
| XSS (reward.php:771) | [C] | 🔧 JS 인라인에 sql_escape 사용 → htmlspecialchars 필요 |
| PHP 8 (reward_update.php:145) | [C] | 🔧 sql_fetch() false 체크 누락 |
| 반응형 | [x] | 반려 모달 max-height 추가 |

### 6.3 상점 관리 (`shop_item_list.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 카테고리 관리 | [C] | 코드 검수 완료 |
| **상점 탭 재정리** | [x] | 프로필(skin/bg/border), 인장(bg/frame/hover) 분리, border/equip 탭 제거 |
| 상품 CRUD (16종 타입) | [C] | 🔧 form.php 타입 10개 vs morgan.php 17개 불일치 |
| 기간 한정 판매 | [C] | 코드 검수 완료 |
| 프론트 상점 페이지 | [C] | 코드 검수 완료 |
| 구매 처리 | [C] | 코드 검수 완료 — insert_point rel 파라미터 보완 필요 |
| 인벤토리/장착 | [C] | 🔧 mg_get_inventory() 반환값 조건부 문제 |
| 선물 보내기/수락/거절 | [C] | 🔧 gf_type NULL vs 'shop' 불일치 |
| 반응형 | [x] | 테이블 min-width:900px 추가 |

### 6.4 구매/선물 내역 (`shop_log.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 구매 로그 조회 | [C] | 🔧 equip 타입 라벨 누락 |
| 선물 로그 조회 | [C] | 코드 검수 완료 |
| 반응형 | [x] | 테이블 min-width:800px 추가 |

### 6.5 이모티콘 관리 (`emoticon_list.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 이모티콘 셋 CRUD | [C] | 코드 검수 완료 |
| 개별 이모티콘 업로드 | [C] | 코드 검수 완료 |
| 유저 제작 이모티콘 승인 | [C] | 🔧 자동생성 코드 중복 재검증 없음 |
| 수수료 계산 | [C] | 🔧 commission_rate ≥100 시 음수 수익 |
| 이모티콘 코드 포맷 | [C] | 🔧 `:code:` 포맷 검증 없음 (유저 입력) |
| 프론트 이모티콘 피커 | [-] | 검수에서 write 4종+RP 적용됨 |
| 반응형 | [x] | 테이블 min-width:800px 추가 |

---

## 7. 개척 그룹

### 7.1 시설 관리 (`pioneer_facility.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 시설 CRUD | [C] | 코드 검수 완료 — 추가/수정/삭제/시작/강제완공 |
| 재료 비용 설정 | [C] | 코드 검수 완료 — 재료 타입별 필요량 |
| 기능 해금 연동 | [C] | 코드 검수 완료 — 6종 해금 타입(게시판/상점/선물/업적/연대기/분수대) |
| 프론트 개척 페이지 | [C] | 코드 검수 완료 — 시설+파견 통합, 시설 상세 모달 |
| 기여 랭킹 + 명예의 전당 | [C] | 코드 검수 완료 — mg_record_facility_honor() |
| **뷰 모드 토글 통일** | [C] | 🔧 카드뷰/거점뷰 인라인 토글 (파견지와 동일 패턴), AJAX 모드 저장 |
| **거점 이미지 AJAX** | [C] | 🔧 업로드/삭제 AJAX 분리, 이미지 삭제 시 카드뷰 자동 전환 |
| 맵 마커 배치 | [C] | 코드 검수 완료 — 맵 클릭 좌표 저장 + 좌표 제거 |

### 7.2 재료 관리 (`pioneer_material.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 재료 타입 CRUD | [ ] | |
| 활동 보상 재료 드롭 확인 | [ ] | |

### 7.3 파견지 관리 (`expedition_area.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 파견지 CRUD | [C] | 🔧 DB 테이블 3개 생성 + 5개 파견지 + 12개 드롭 규칙 시드 |
| 드롭 테이블 설정 | [C] | 코드 검수 완료 — 재료별 드롭률/수량/레어 |
| 맵 모드 좌표 설정 | [C] | 코드 검수 완료 — 전용 파견 지도 이미지 (세계관 맵과 분리) |
| 프론트 파견 보내기/수령 | [C] | 코드 검수 완료 — PHP 레벨 시나리오 검증 (시작→완료→수령) |
| 파트너 선택/보상 보너스 | [C] | 코드 검수 완료 — 관계 기반, 1일1회 동일파트너 제한, +20% 보너스 |
| 맵 모드 UI | [C] | 🔧 카드목록/파견지도 토글 실제 전환, 지도 없으면 버튼 disabled |
| **아이콘 mg_icon_input()** | [C] | 🔧 Heroicons명 텍스트 입력 → mg_icon_input() 교체 (이미지 업로드 지원) |
| **참가자 포인트 보상** | [C] | 🔧 ea_point_min/ea_point_max 추가, 파견 완료 시 참가자에게 포인트 지급 |
| **파트너 PT 명확화** | [C] | 🔧 "파트너 보너스PT"로 라벨 변경, 참가자 보상과 분리 표시 |
| 반응형 | [x] | 모달 max-height:90vh 추가 |
| **지역 데이터 분리** | [ ] | **향후 — 맵 지역(mg_map_region)과 파견지 데이터 분리 검토** |

### 7.4 파견 기록 (`expedition_log.php`)

| 항목 | 상태 | 비고 |
|------|------|------|
| 파견 기록 조회/필터 | [C] | 🔧 Warning 수정 (cnt 키), "파견 로그"→"파견 기록" 명칭 변경 |

---

## 8. 프론트 공통 QA

관리자 페이지에 직접 매핑되지 않지만 검수가 필요한 프론트 영역.

| 항목 | 상태 | 비고 |
|------|------|------|
| SPA 라우터 (app.js) | [-] | 사이드바 활성 상태 동기화 완료 |
| 사이드바 네비게이션 | [x] | 검수에서 전체 정리됨 |
| 헤더 (접속자 수, 알림 벨) | [-] | 접속자 드롭다운 + 뱃지 추가됨 |
| 반응형 레이아웃 (모바일/태블릿) | [ ] | 전체 페이지 대상 확인 필요 |
| 페이지 타이틀 형식 통일 | [x] | "사이트명 \| 페이지명" |
| 로그인/회원가입 스킨 | [ ] | |
| 마이 페이지 허브 (mypage.php) | [ ] | |
| 테마 색상 프론트 반영 | [x] | 전체 색상 체인 검증 완료 |
| 배경 이미지 프론트 반영 | [x] | 투명도 반전 수정 |
| 폰트 프론트 반영 | [x] | Google Fonts 24종 |

---

## 9. 코드 검수에서 수정한 버그 목록

> Claude 코드 검수 중 발견하여 이미 수정 완료된 항목. 사용자 확인과 별개로 적용됨.

| 날짜 | 파일 | 수정 내용 |
|------|------|----------|
| 02-21 | `bbs/shop_buy.php` | mg_buy_item() 반환값 비교 `=== true` → `$result['success']` |
| 02-21 | `bbs/shop_gift.php` | mg_send_gift() 반환값 비교 동일 수정 |
| 02-21 | `bbs/gift_action.php` | mg_accept/reject_gift() 반환값 비교 동일 수정 |
| 02-21 | `adm/morgan/rp_list.php` | 뷰 링크 404 수정 (plugin/morgan → bbs) |
| 02-21 | `view_comment.skin.php` | $comment_id 미정의 + 댓글 수정 기능 구현 |
| 02-21 | `MG_Game_Base.php` | insert_point() rel 파라미터 누락 수정 |
| 02-21 | `adm/morgan/shop_item_form.php` | sc_id (카테고리) 필드 누락 추가 |
| 02-21 | `adm/morgan/shop_item_update.php` | sc_id INSERT/UPDATE 누락 추가 |
| 02-23 | `adm/morgan/concierge.php` | `$items` 변수 충돌 수정 — `_head.php`가 admin 메뉴로 덮어씀 → `$cc_list`로 변경 |
| 02-23 | `bbs/concierge.php` | results 탭 "Undefined array key cnt" — 디버그 코드 제거 (원인은 테이블명) |
| 02-23 | DB `write_concierge_result` | 테이블 접두사 누락 수정 — `write_concierge_result` → `g5_write_concierge_result` RENAME |
| 02-23 | `db/migrations/20260219_*_concierge_result_board.php` | 테이블명 하드코딩 → `$g5['write_prefix']` 사용으로 수정 |
| 02-23 | `bbs/seal_edit.php` | 격자 16×6→16×4, 눈금자 UI, 배경/테두리/호버 아이템 선택 UI |
| 02-23 | `bbs/seal_edit_update.php` | seal_bg_color 저장, y/h 범위 4로 조정 |
| 02-23 | `plugin/morgan/morgan.php` | mg_render_seal() 16×4, seal_bg_color, frame 스타일 확장, hover CSS, 프론트 셀 스타일, 트로피 아이콘 전용 |
| 02-23 | `plugin/morgan/morgan.php` | 상점 탭 재정리: profile(skin/bg/border), seal(bg/frame/hover), seal_hover 라벨, stamp 아이콘 |
| 02-23 | `adm/morgan/prompt.php` | 테이블 컬럼 너비 수정 + "우수작"→"선정작" 6곳 |
| 02-23 | `adm/morgan/prompt_update.php` | "우수작"→"선정작" |
| 02-23 | `theme/morgan/skin/board/prompt/list.skin.php` | "우수작"→"선정작" 2곳 |
| 02-23 | `theme/morgan/skin/board/prompt/write.skin.php` | 자유글쓰기 차단, 미션 선택 필수, 미션 없으면 비활성화 |
| 02-22 | `bbs/character_form.php` | 보낸 관계 신청 취소 버튼 추가 |
| 02-22 | `bbs/rp_close.php` | `$existing['rc_id']` PHP 8 false 체크 |
| 02-22 | `bbs/rp_api.php` | 배열 접근 null 안전 개선 |
| 02-22 | `bbs/write_update.php` | mg_prompt_after_write() 호출 추가 (미션 엔트리 생성) |
| 02-22 | `bbs/delete.php` | 글 삭제 시 mg_prompt_entry + mg_write_character 정리 |
| 02-22 | `bbs/seal_edit.php` | 탭 UI → 단일 페이지 통합 (위젯 클릭→속성 패널) |
| 02-22 | `bbs/seal_edit.php` | GridStack minRow:6 + 모바일 폴백 레이아웃 보존 |
| 02-22 | `bbs/seal_edit.php` | fd.set('seal_layout') 명시적 설정 (FormData 누락 방지) |
| 02-22 | `bbs/seal_edit.php` | 닉네임/한마디 maxH:1→3, 텍스트 요소 패딩 추가 |
| 02-22 | `bbs/seal_edit_update.php` | stripslashes() 추가 (GnuBoard addslashes → JSON 파싱 실패 수정) |
| 02-22 | `plugin/morgan/morgan.php` | mg_render_seal() CSS Grid 기본 레이아웃 추가, 인라인 스타일 통일 |
| 02-22 | `plugin/morgan/morgan.php` | mg_render_seal() full 모드 margin-top:1.5rem 추가 |
| 02-22 | `plugin/morgan/morgan.php` | mg_render_seal() 텍스트 요소 패딩 + 정렬 렌더링 |
| 02-22 | `bbs/mypage.php` | 바로가기 중복 제거, 인장 링크 data-no-spa, 가운데 정렬 |
| 02-22 | `adm/morgan/seal.php` | 페이지 헤더 텍스트 블록 제거 |
| 02-22 | `adm/morgan/notification.php` | SQL 인젝션 수정 ($stx sql_real_escape_string 적용) |
| 02-22 | `adm/morgan/notification.php` | XSS 수정 ($stx htmlspecialchars 적용) |
| 02-22 | `adm/morgan/notification.php` | 알림 타입 목록 28종으로 보완 (12→28) |
| 02-22 | `bbs/notification.php` | 알림 타입 라벨 28종으로 보완 (12→28) |
| 02-22 | `plugin/morgan/morgan.php` | mg_icon_input() — 관리자 아이콘 입력 PHP 컴포넌트 (6개 파일 중복 제거) |
| 02-22 | `plugin/morgan/morgan.php` | mg_handle_icon_upload() — 아이콘 업로드 공용 함수 |
| 02-22 | `adm/morgan/achievement.php` | 아이콘 입력 → mg_icon_input() 교체 (업적+단계 2세트, JS 5개 제거) |
| 02-22 | `adm/morgan/pioneer_facility.php` | 아이콘 입력 → mg_icon_input() 교체 (JS 3개 제거) |
| 02-22 | `adm/morgan/pioneer_material.php` | 아이콘 입력 → mg_icon_input() 교체 (JS 3개 제거) |
| 02-22 | `adm/morgan/shop_category.php` | 아이콘 입력 → mg_icon_input() 교체 (compact 모드) |
| 02-22 | `adm/morgan/side_class.php` | 아이콘 입력 → mg_icon_input() 교체 (소속+유형 2세트) |
| 02-22 | `adm/morgan/achievement_update.php` | 업로드 코드 → mg_handle_icon_upload() + 잔여 코드 정리 |
| 02-22 | `bbs/achievement.php` | 업적 아이콘 `<img>` → mg_icon() 렌더링 (Heroicons 지원) |
| 02-22 | `plugin/morgan/morgan.php` | mg_get_max_characters() — 기본값 + 슬롯 보너스 계산 |
| 02-22 | `plugin/morgan/morgan.php` | mg_use_item() char_slot 즉시 소모 + 영구 적용 |
| 02-22 | `plugin/morgan/morgan.php` | mg_unuse_item() char_slot 해제 차단 |
| 02-22 | `bbs/character.php` | max_characters → mg_get_max_characters() 교체 |
| 02-22 | `bbs/character_form.php` | max_characters → mg_get_max_characters() 교체 + 상점 안내 |
| 02-25 | `adm/morgan/pioneer_facility.php` | 뷰 모드 토글 통일 — select 드롭다운 → 인라인 토글 버튼 (파견지와 동일) |
| 02-25 | `adm/morgan/pioneer_facility_update.php` | AJAX 핸들러 추가 — set_view_mode, delete_base_image |
| 02-25 | `adm/morgan/expedition_area.php` | 전용 파견 지도 이미지 분리 (세계관 맵 독립), UI 모드 토글 실제 전환 |
| 02-25 | `adm/morgan/expedition_area.php` | 아이콘 입력 → mg_icon_input() 교체, 보상 포인트 필드 추가, 파트너 보너스PT 라벨 |
| 02-25 | `adm/morgan/expedition_area_update.php` | 아이콘 업로드 처리 + ea_point_min/ea_point_max 저장 |
| 02-25 | `adm/morgan/expedition_log.php` | Warning: Undefined array key "cnt" 수정 + "파견 로그"→"파견 기록" 명칭 변경 |
| 02-25 | `adm/admin.menu800.php` | "파견 로그"→"파견 기록" 메뉴명 변경 |
| 02-25 | `plugin/morgan/morgan.php` | mg_get_expedition_areas() — unlock_facility_name 항상 초기화 |
| 02-25 | `plugin/morgan/morgan.php` | mg_claim_expedition() — 참가자 포인트 보상 로직 추가 (ea_point_min~max, 파트너 보너스 +20%) |
| 02-25 | `theme/morgan/skin/pioneer/expedition.skin.php` | 카드/맵팝업/파견모달/보상모달에 포인트 보상 표시, 이력에 포인트 파싱 |
| 02-25 | `theme/morgan/skin/pioneer/expedition.skin.php` | "스테미나은"→"스테미나는" 문법 오류 수정 |
| 02-25 | `db/migrations/` | 3건 수정 — ADD COLUMN IF NOT EXISTS→PREPARE/EXECUTE, DELIMITER 제거 |
| 02-25 | `db/migrations/20260225_160000_title_gacha_system.sql` | 칭호 뽑기 INSERT IGNORE→NOT EXISTS 패턴 (중복 방지) |

---

## 10. 미구현 잔여 항목

> ROADMAP.md에서 아직 체크되지 않은 1차 범위 기능들.

| 항목 | 출처 | 우선도 | 비고 |
|------|------|--------|------|
| ~~M8: 인장 그리드 빌더~~ | Phase 13 확장 | ~~중~~ | ✅ 완료 — GridStack 16×6, 단일 페이지 통합 |
| ~~M9: 캐릭터 슬롯 아이템~~ | 상점 확장 | ~~중~~ | ✅ 완료 — char_slot 타입, mg_get_max_characters(), 기본 1개 |
| 의뢰 상점 아이템 연동 | Phase 18.3 | 저 | 슬롯 추가, 추첨 확률 UP |
| 캐릭터 장비 시스템 | Phase 5.3 | - | 2차-B(SS Engine)로 이관 |

---

## 11. 권장 QA 순서

관리자 페이지 단위로 순차 검수. **사용자가 브라우저에서 확인 → 피드백 → Claude 수정**.

### Phase A: 핵심 설정 (✅ 완료)
1. ~~디자인 관리~~ — 색상/배경/폰트/빌더 ✅
2. ~~기본 설정 (M3)~~ — 회원 레벨 게이트 ✅
3. ~~스태프 관리 (M5)~~ — 권한 시스템 ✅

### Phase B: 회원/캐릭터 (✅ 완료)
4. ~~캐릭터 관리~~ — 등록/승인/반려 플로우 ✅
5. ~~프로필 필드~~ — 6종 타입 동작 확인 ✅
6. ~~소속/유형~~ — CRUD + 캐릭터 선택 연동 ✅
7. ~~관계 관리~~ — 관계도 vis.js + 신청/승인 ✅
8. ~~인장 관리~~ — 단일 페이지 통합, 저장 수정, 렌더링 일치 ✅

### Phase C: 콘텐츠 (코드 검수 완료, 사용자 확인 대기)
9. 게시판 — 5종 스킨 + 댓글 + 좋아요 + 주사위
10. 역극 — 판 세우기/이음/완결 + 이모티콘
11. 미션 — 3종 모드(auto/review/vote) + 태그/복제
12. 의뢰 — 등록/지원/매칭/완료 + 페널티

### Phase D: 재화/활동
13. 상점 — 9종 상품타입 + 구매/인벤토리/선물
14. 보상 — 게시판별 설정 + 정산 대기열
15. 출석 — 미니게임 3종 + 통계
16. 알림 — 트리거별 발송 + 벨/토스트
17. 업적 — 조건빌더 + 트리거 + 쇼케이스
18. 이모티콘 — 셋 관리 + 유저 제작 승인

### Phase E: 세계관/개척
19. 위키 — 카테고리/문서/링크 + 프론트
20. 타임라인 — 시대/사건 + 시각화
21. 지도 — 맵 이미지 + 마커 + 파견지 연동
22. 개척 — 시설/재료/노동력/기여
23. 파견 — 파견지/드롭/파트너/맵모드

### Phase F: 전체 통합
24. 반응형 레이아웃 (375px / 768px / 1200px)
25. SPA 라우터 전체 페이지 네비게이션
26. 대시보드 위젯 전체 + 통계 카드 정확성

---

## 12. 이전 세션 작업 이력

> 이전 세션에서 완료된 검수/QA 작업.

| 날짜 | 세션 | 내용 |
|------|------|------|
| ~02-19 | 검수 | 사이드바 정리, 타이틀 통일, 역극/댓글 UI, Toast UI Editor |
| ~02-19 | 검수 | 마이그레이션 엔진, 시드 데이터, 배포 방식 전환 |
| 02-19 | M1~M4 | 관계 정리, 컨시어지 개편, 회원 설정, 미션 확장 |
| 02-20 (오전) | M5~M6 | 스태프 권한, 미니게임 추가, 주사위 3D 리뉴얼 |
| 02-20 (오전) | 빌더 | 메인 페이지 빌더 GridStack 리뉴얼, 폰트 커스텀 |
| 02-20 (오후) | QA | 색상 감사(버튼글자/텍스트), 배경 투명도, 달력위젯, 위젯 UI |
| 02-21 | QA | 스모크 테스트 버그 수정 (상점/역극/댓글/출석), 댓글 수정 기능 |
| 02-22 | QA | 소속/유형·관계·인장 코드 검수, 게시판·역극·미션·의뢰 코드 검수 |
| 02-22 | QA+기능 | QA 1차 마무리: 알림 보안수정, 출석 반응형, 업적 아이콘. 아이콘 입력 컴포넌트화(mg_icon_input). 캐릭터 슬롯 아이템+기본제한 1개. 마이그레이션 기록 정리 |
| 02-22 | 기능 | 로드비(Lordby) 게시판 스킨 4종 신규 (lb_terminal/lb_intranet/lb_corkboard/lb_default) — 인라인 댓글+모달 글쓰기, 공유 include 3파일 분리. 게시판 그룹 UI 숨김(gr_id 하드코딩 community). write 스킨 이모티콘 피커 제거(4종). 의뢰 탭 1차 수정(등록=전체마켓, 진행=나의 의뢰) — 기획 재정리 필요로 중단 |
| 02-23 | 기능+QA | 의뢰 시스템 안정성 패치(settle/force_close/auto-expiry/edit flexibility) + 카드 UI 개편(3탭) + E2E 시나리오 테스트 10종 전체 PASS. 버그 2건 수정: 관리자 $items 변수 충돌, write_concierge_result 테이블 접두사 누락 |
| 02-23 | 기능 | 인장 시스템 개편: 16×6→16×4 격자, 눈금자, 배경색(무료), 테두리 아이템 5종, 호버 아이템 4종, 전 요소 스타일 가능, 프론트 셀 스타일(배경+보더+라운딩). 상점 탭 재정리(프로필/인장 분리). 미션 QA: "우수작"→"선정작", 자유글쓰기 차단, 관리자 목록 컬럼 너비 수정. 트로피 텍스트 제거(아이콘+호버) |
| 02-25 | QA+기능 | 재화/상점 코드 분석 완료: Critical 6+일관성 6+함수화 5 식별 → QA_SHOP_ISSUES.md. 개척 시설 관리 뷰 모드 토글 통일(카드뷰/거점뷰 인라인, 파견지와 동일 패턴), 거점 이미지 AJAX 분리. 파견 시스템: DB 테이블 3개 생성+시드(5파견지+12드롭), 전용 지도 이미지 분리(세계관 맵 독립), UI 모드 실제 전환 구현, 파견 기록 Warning 수정+명칭 변경, 해금 조건 Warning 수정. 마이그레이션 3건 수정(ADD COLUMN IF NOT EXISTS→PREPARE/EXECUTE, DELIMITER 제거). 칭호 뽑기 아이템 중복 제거. 문법 오류 수정(스테미나은→는). 파견 아이콘 mg_icon_input() 교체. **참가자 포인트 보상 신규**: ea_point_min/ea_point_max 컬럼, 파견 완료 시 참가자 포인트 지급(+파트너 보너스 20%), 보상 모달에 포인트 표시. 파트너PT→파트너 보너스PT 라벨 명확화 |

---

*이 문서는 QA 진행에 따라 지속적으로 업데이트됩니다.*
