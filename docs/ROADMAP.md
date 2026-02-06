# Morgan Edition - 개발 로드맵

> 작성일: 2026-02-04
> 최종 업데이트: 2026-02-06

---

## 개요

이 문서는 Morgan Edition CMS의 전체 기능 목록과 구현 현황을 정리한 로드맵입니다.
각 항목의 체크박스는 구현 완료 여부를 나타냅니다.

**범례**
- [x] 완료
- [-] 부분 구현
- [ ] 미구현

---

## Phase 1: 기반 시스템

### 1.1 테마 기본 구조
- [x] Morgan 테마 폴더 구조
- [x] head.php / tail.php 레이아웃
- [x] head.sub.php / tail.sub.php (서브 레이아웃)
- [x] Tailwind CSS 기반 스타일링
- [x] 디스코드 스타일 다크 테마
- [x] 반응형 사이드바

### 1.2 회원 시스템
- [x] 로그인 스킨 (login.skin.php)
- [x] 회원가입 스킨 (register_form.skin.php)
- [x] 회원가입 완료 스킨 (register_result.skin.php)
- [x] 회원정보 확인 스킨 (member_confirm.skin.php)
- [x] 포인트 내역 스킨 (point.skin.php)
- [ ] 닉네임 변경 주기 설정
- [ ] 회원 레벨별 권한 설정

### 1.3 게시판 스킨
- [x] basic 스킨 - 목록 (list.skin.php)
- [x] basic 스킨 - 보기 (view.skin.php)
- [x] basic 스킨 - 쓰기 (write.skin.php)
- [x] basic 스킨 - 댓글 (view_comment.skin.php)
- [x] gallery 스킨 (갤러리형)
- [x] memo 스킨 (방명록형)
- [x] postit 스킨 (포스트잇형, lino.it 스타일)

---

## Phase 2: 캐릭터 시스템

### 2.1 캐릭터 기본
- [x] DB 테이블 (mg_character)
- [x] 캐릭터 등록 폼 (character_form.php)
- [x] 캐릭터 등록 처리 (character_form_update.php)
- [x] 캐릭터 보기 (character_view.php)
- [x] 내 캐릭터 목록 (character.php)
- [x] 전체 캐릭터 목록 (character_list.php)

### 2.2 세력/종족
- [x] DB 테이블 (mg_side, mg_class)
- [x] 관리자 - 세력/종족 관리 (side_class.php)
- [x] 세력/종족 CRUD

### 2.3 프로필 양식 (가변 필드)
- [x] DB 테이블 (mg_profile_field, mg_profile_value)
- [x] 관리자 - 프로필 양식 관리 (profile_field.php)
- [x] 필드 타입: text, textarea, select, multiselect, url, image

### 2.4 캐릭터 승인
- [x] DB 테이블 (mg_character_log)
- [x] 관리자 - 캐릭터 목록/승인 (character_list.php)
- [x] 승인/반려 워크플로우
- [x] 반려 사유 입력

### 2.5 TRPG 스탯 시스템
- [ ] DB 테이블 (mg_trpg_ruleset)
- [ ] DB 테이블 (mg_trpg_stat_field)
- [ ] DB 테이블 (mg_character_stat_value)
- [ ] DB 테이블 (mg_character_ruleset)
- [ ] 룰셋 관리 (CoC 7판, D&D 5e, VtM 5판)
- [ ] 관리자 - 스탯 필드 활성화 토글
- [ ] 캐릭터 시트 UI
- [ ] 스탯 기반 판정 시스템

---

## Phase 3: 포인트/출석 시스템

### 3.1 출석체크
- [x] DB 테이블 (mg_attendance)
- [x] 출석 페이지 (attendance.php)
- [x] 출석 처리 (attendance_play.php)
- [x] 출석 스킨 (attendance.skin.php)
- [x] 달력 표시
- [x] 연속 출석 카운트
- [x] 7일 연속 보너스

### 3.2 미니게임 - 주사위
- [x] 게임 인터페이스 (MG_Game_Interface.php)
- [x] 게임 베이스 클래스 (MG_Game_Base.php)
- [x] 게임 팩토리 (MG_Game_Factory.php)
- [x] 주사위 게임 (MG_Game_Dice.php)
- [x] 더블 보너스 (같은 숫자 2배)

### 3.3 미니게임 - 운세뽑기
- [ ] DB 테이블 (mg_game_fortune)
- [ ] 운세 게임 클래스 (MG_Game_Fortune.php)
- [ ] 별점 + 운세 텍스트
- [ ] 관리자 - 운세 데이터 관리

### 3.4 미니게임 - 종이뽑기
- [ ] DB 테이블 (mg_game_lottery_prize)
- [ ] DB 테이블 (mg_game_lottery_board)
- [ ] DB 테이블 (mg_game_lottery_user)
- [ ] 종이뽑기 게임 클래스 (MG_Game_Lottery.php)
- [ ] 등수별 상품 설정
- [ ] 판 완성 보너스

### 3.5 출석 통계 (관리자)
- [x] 출석 통계 페이지 (adm/morgan/attendance.php)
- [x] 일별 출석 수
- [x] 기간별 조회
- [x] 출석 상세 목록

---

## Phase 4: 메인 페이지 빌더

### 4.1 위젯 시스템
- [x] 위젯 인터페이스 (widget.interface.php)
- [x] 위젯 팩토리 (widget.factory.php)
- [x] 에디터 위젯 (editor.widget.php)
- [x] 텍스트 위젯 (text.widget.php)
- [x] 이미지 위젯 (image.widget.php)
- [x] 링크 버튼 위젯 (link_button.widget.php)
- [x] 최신글 위젯 (latest.widget.php)
- [x] 공지사항 위젯 (notice.widget.php)
- [x] 슬라이더 위젯 (slider.widget.php)

### 4.2 빌더 UI (관리자)
- [x] DB 테이블 (mg_main_row, mg_main_widget)
- [x] 메인 빌더 페이지 (main_builder.php)
- [x] 저장 처리 (main_builder_update.php)
- [x] 위젯 설정 모달 (main_widget_config.php)
- [x] 이미지 업로드 (main_widget_upload.php)
- [x] 행 추가/삭제/정렬
- [x] 위젯 추가/삭제/정렬
- [x] 드래그앤드롭 UI

### 4.3 프론트 렌더링
- [x] mg_get_main_layout() 함수
- [x] mg_render_main() 함수
- [x] index.php 동적 렌더링
- [x] 위젯별 스킨 파일

---

## Phase 5: 상점 시스템

### 5.1 상점 기본
- [x] DB 테이블 (mg_shop_category)
- [x] DB 테이블 (mg_shop_item)
- [x] DB 테이블 (mg_shop_log)
- [x] 상점 메인 페이지
- [x] 카테고리별 상품 목록
- [x] 상품 상세 페이지
- [x] 구매 처리

### 5.2 상품 종류
- [x] 칭호 (title)
- [x] 뱃지 (badge)
- [x] 닉네임 색상 (nick_color)
- [x] 닉네임 효과 (nick_effect)
- [x] 프로필 테두리 (profile_border)
- [x] 장비 아이템 (equip)
- [x] 이모티콘 셋 (emoticon_set)
- [x] 가구 (furniture)
- [x] 기타 (etc)

### 5.3 인벤토리
- [x] DB 테이블 (mg_inventory)
- [x] DB 테이블 (mg_item_active)
- [ ] DB 테이블 (mg_character_equip)
- [x] 인벤토리 페이지
- [x] 아이템 사용/장착
- [ ] 캐릭터별 장비 적용

### 5.4 선물
- [x] DB 테이블 (mg_gift)
- [x] 선물 보내기
- [x] 선물 수락/거절
- [x] 선물 메시지

### 5.5 관리자
- [x] 카테고리 관리
- [x] 상품 등록/수정/삭제
- [x] 구매/선물 내역
- [x] 재고 관리
- [x] 기간 한정 판매

---

## Phase 6: 역극(RP) 시스템

### 6.1 역극 기본
- [x] DB 테이블 (mg_rp_thread, mg_rp_reply, mg_rp_member)
- [x] 역극 목록 페이지 (rp_list.php)
- [x] 역극 보기 페이지 (rp_view.php, 채팅 UI)
- [x] 판 세우기 (rp_write.php)
- [x] 이음 작성 (rp_reply.php, AJAX)

### 6.2 역극 기능
- [x] 참여자 관리
- [x] 최대 참여자 제한
- [x] 판 완결 (rp_close.php)
- [x] 캐릭터 연결
- [x] 이미지 첨부

### 6.3 관리자
- [x] 역극 목록 관리 (adm/morgan/rp_list.php)
- [x] 역극 설정 (최소 글자수, 참여조건 등)

---

## Phase 7: 이모티콘 시스템

### 7.1 이모티콘 기본
- [x] DB 테이블 (mg_emoticon_set, mg_emoticon, mg_emoticon_own)
- [x] 이모티콘 선택 UI (emoticon picker)
- [x] 게시글/댓글에 이모티콘 삽입
- [x] 이모티콘 렌더링 (mg_render_emoticons)
- [x] 유저 제작 이모티콘 업로드

### 7.2 관리자
- [x] 이모티콘 셋 등록/승인
- [x] 개별 이모티콘 업로드
- [x] 가격 설정
- [x] 상점/인벤토리 연동

---

## Phase 8: 마이룸 시스템 (2차)

### 8.1 마이룸 기본
- [ ] DB 테이블 (mg_furniture_category)
- [ ] DB 테이블 (mg_furniture)
- [ ] DB 테이블 (mg_furniture_own)
- [ ] DB 테이블 (mg_room)
- [ ] 마이룸 보기 페이지
- [ ] 아이소메트릭 2D 렌더링

### 8.2 가구 시스템
- [ ] 가구 카테고리 (침대, 책상, 조명 등)
- [ ] 가구 배치/회전
- [ ] 벽지/바닥 변경
- [ ] 방 크기 확장

### 8.3 관리자
- [ ] 가구 등록
- [ ] 아이소메트릭 이미지 업로드
- [ ] 그리드 크기 설정

---

## Phase 9: 알림 시스템

### 9.1 알림 기본
- [x] DB 테이블 (mg_notification)
- [x] 알림 헬퍼 함수 (mg_notify, mg_get_notifications 등)
- [x] 알림 트리거 (댓글, 답글, 추천, RP이음, 캐릭터, 선물, 이모티콘)
- [x] 헤더 벨 아이콘 + 드롭다운
- [x] 토스트 알림 (폴링 기반)
- [x] 알림 읽음/삭제 처리
- [x] 알림 목록 페이지 (notification.php)

### 9.2 관리자
- [x] 알림 관리 페이지 (adm/morgan/notification.php)

---

## Phase 10: 관리자 시스템

### 10.1 기본 관리
- [x] Morgan 관리자 레이아웃 (_head.php, _tail.php)
- [x] 기본 설정 (config.php)
- [x] 설정 저장 (config_update.php)

### 10.2 대시보드
- [ ] 오늘 가입자 수
- [ ] 오늘 글 수
- [ ] 승인 대기 캐릭터
- [ ] 미답변 문의

### 10.3 스태프 권한
- [ ] DB 테이블 (mg_staff_auth)
- [ ] 스태프 목록
- [ ] 권한 설정 UI
- [ ] 권한별 메뉴 접근 제어

---

## Phase 11: 개척 시스템 (Pioneer)

> 상세: plans/PIONEER.md

### 11.1 개척 기본
- [ ] DB 테이블 (mg_labor, mg_material, mg_facility 등)
- [ ] 노동력 시스템 (일일 지급, 리셋)
- [ ] 건축 재료 시스템
- [ ] 시설 건설 (공동 기여)
- [ ] 기능 해금 연동

---

## Phase 12: 업적 시스템 (Achievement)

> 상세: plans/ACHIEVEMENT.md

### 12.1 업적 기본
- [ ] DB 테이블 (mg_achievement, mg_user_achievement)
- [ ] 업적 트리거 (활동, 역극, 개척 등)
- [ ] 프로필 쇼케이스 (3~5개 선택)
- [ ] 희귀도 시스템

---

## Phase 13: SS Engine (TRPG 세션 툴) - 2차

> Supabase 실시간 연동, 별도 패키지
> 상세: plans/SS_ENGINE.md

### 11.1 세션 기본
- [ ] 세션 생성/참여
- [ ] 실시간 채팅
- [ ] 캐릭터 연결

### 11.2 TRPG 기능
- [ ] 주사위 굴림 (룰셋별)
- [ ] 스탯 판정
- [ ] 전투 관리
- [ ] 맵/토큰

### 11.3 세션 보상
- [ ] 참여 포인트
- [ ] 완주 포인트
- [ ] GM 보상

---

## 부록: 파일 구조

```
new_cms/
├── adm/morgan/                 # Morgan 관리자
│   ├── _head.php, _tail.php    # 레이아웃
│   ├── config.php              # 기본 설정
│   ├── character_*.php         # 캐릭터 관리
│   ├── profile_field.php       # 프로필 양식
│   ├── side_class.php          # 세력/종족
│   ├── main_builder.php        # 메인 빌더
│   ├── attendance.php          # 출석 통계
│   └── notification.php        # 알림 관리
│
├── bbs/                        # 프론트 페이지
│   ├── character*.php          # 캐릭터 관련
│   └── attendance*.php         # 출석 관련
│
├── plugin/morgan/              # Morgan 플러그인
│   ├── morgan.php              # 메인 플러그인
│   ├── install/                # 설치 SQL
│   ├── widgets/                # 위젯 클래스
│   └── games/                  # 미니게임 클래스
│
├── theme/morgan/               # Morgan 테마
│   ├── head.php, tail.php      # 메인 레이아웃
│   ├── index.php               # 메인 페이지
│   └── skin/                   # 스킨
│       ├── member/             # 회원 스킨
│       ├── board/basic/        # 게시판 스킨
│       ├── widget/             # 위젯 스킨
│       └── attendance/         # 출석 스킨
│
└── docs/                       # 기획 문서
    ├── CORE.md                 # 핵심 참조 (DB요약, 인덱스)
    ├── ROADMAP.md              # 진행률 (이 파일)
    ├── MODULES.md              # 2차 모듈 전략
    ├── plans/                  # 상세 기획
    │   ├── DB.md, API.md, UI.md
    │   ├── CHARACTER.md, BOARD.md, SHOP.md
    │   └── PIONEER.md, ACHIEVEMENT.md
    └── archive/                # 작업 로그 보관
```

---

## 변경 이력

| 날짜 | 내용 |
|------|------|
| 2026-02-04 | 로드맵 문서 생성, 전체 현황 정리 |
| 2026-02-06 | Phase 6~9 완료 반영, 개척/업적 Phase 추가, docs 구조 정리 |

---

*이 문서는 개발 진행에 따라 지속적으로 업데이트됩니다.*
