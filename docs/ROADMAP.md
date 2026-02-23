# Morgan Edition - 개발 로드맵

> 작성일: 2026-02-04
> 최종 업데이트: 2026-02-22

---

## 개요

이 문서는 Morgan Edition CMS의 전체 기능 목록과 구현 현황을 정리한 로드맵입니다.
각 항목의 체크박스는 구현 완료 여부를 나타냅니다.

**범례**
- [x] 완료
- [-] 부분 구현
- [ ] 미구현

---

## 현재 상태: 1차 완료 → QA/유지보수 진행중

> Phase 1~18 핵심 기능 + 잔여 구현(M1~M6) 완료. M7은 2차-B(SS Engine)로 이관.
> 현재 전체 QA 및 유지보수 진행중.

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

### 2.5 전투 스탯 시스템 (SS Engine 연동 → 2차)
- [ ] DB 테이블 재설계 (기존 mg_trpg_* → SS Engine 스탯 구조)
- [ ] 캐릭터별 기본 스탯 4종 (체력/근력/마력/숙련)
- [ ] 포인트로 스탯 구매 (성장 = 창작활동 기반)
- [ ] 장비 슬롯 2개 (무기+방어구, 보정 스탯 연동)
- [ ] 스킬 슬롯 (기본 3, 연구/상점으로 확장)
- [ ] 역할 빌드 (탱커/딜러/힐러 + 세부 분화)
- [ ] 캐릭터 전투 시트 UI (스탯/장비/스킬 관리)
- [ ] 관리자 - 스탯 구매 비용, 장비/스킬 등록

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
- [x] DB 테이블 (mg_game_fortune)
- [x] 운세 게임 클래스 (MG_Game_Fortune.php)
- [x] 별점 + 운세 텍스트 + 별점별 가중치 설정
- [x] 관리자 - 운세 데이터 관리 (모달 CRUD)

### 3.4 미니게임 - 종이뽑기
- [x] DB 테이블 (mg_game_lottery_prize, mg_game_lottery_board, mg_game_lottery_user)
- [x] 종이뽑기 게임 클래스 (MG_Game_Lottery.php)
- [x] 등수별 상품 설정 + 유저 직접 선택 뽑기
- [x] 판 완성 보너스 + 문방구 스타일 디자인

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

## Phase 8: 알림 시스템

### 8.1 알림 기본
- [x] DB 테이블 (mg_notification)
- [x] 알림 헬퍼 함수 (mg_notify, mg_get_notifications 등)
- [x] 알림 트리거 (댓글, 답글, 추천, RP이음, 캐릭터, 선물, 이모티콘)
- [x] 헤더 벨 아이콘 + 드롭다운
- [x] 토스트 알림 (폴링 기반)
- [x] 알림 읽음/삭제 처리
- [x] 알림 목록 페이지 (notification.php)

### 8.2 관리자
- [x] 알림 관리 페이지 (adm/morgan/notification.php)

---

## Phase 9: 관리자 시스템

### 9.1 기본 관리
- [x] Morgan 관리자 레이아웃 (_head.php, _tail.php)
- [x] 기본 설정 (config.php) — 3섹션: 사이트 기본, 미션, 의뢰
- [x] 설정 저장 (config_update.php)

### 9.2 대시보드
- [x] 전체 회원 수
- [x] 승인 대기 캐릭터
- [x] 진행 중 역극
- [x] 오늘 발급 포인트
- [x] 정산 대기 / 오늘 좋아요 통계 카드
- [x] 승인 요청 캐릭터 위젯
- [x] 최신 게시글/역극/포인트/구매 위젯
- [x] 정산 대기열 위젯 + 역극 완결 위젯

### 9.3 스태프 권한
- [x] DB 테이블 (mg_staff_role, mg_staff_member)
- [x] 스태프 목록
- [x] 권한 설정 UI
- [x] 권한별 메뉴 접근 제어

---

## Phase 10: 개척 시스템 (Pioneer)

> 상세: plans/PIONEER.md

### 10.1 개척 기본
- [x] DB 테이블 (mg_user_stamina, mg_material_type, mg_user_material, mg_facility, mg_facility_material_cost, mg_facility_contribution, mg_facility_honor)
- [x] 노동력 시스템 (일일 지급, 패시브 리셋)
- [x] 건축 재료 시스템 (재료 타입, 보유, 획득, 소비)
- [x] 시설 건설 (공동 기여, 노동력+재료, 자동 완공)
- [x] 기능 해금 연동 (게시판, 상점, 선물, 역극)

### 10.2 개척 부가
- [x] 활동 보상 (글쓰기, 댓글, 역극, 출석 → 재료 지급)
- [x] 기여 랭킹 + 명예의 전당 (TOP3 기록)
- [x] 프론트엔드 (목록, 상세, 기여 UI)
- [x] 관리자 (시설 관리, 재료 관리, 수동 지급)

---

## Phase 11: 보상 시스템 (Reward)

> 상세: plans/REWARD.md

### 11.1 게시판별 보상 설정
- [x] DB 테이블 (mg_board_reward, mg_reward_type, mg_reward_queue)
- [x] 보상 모드: auto / request / off (게시판별)
- [x] Auto 모드: 글자수 보너스, 이미지 보너스, 재료 드롭 확률
- [x] Request 모드: 보상 유형 select + 정산 대기열
- [x] 보상 관리 페이지 (관리자)

### 11.2 역극 재화 시스템
- [x] DB 테이블 (mg_rp_completion, mg_rp_reply_reward_log)
- [x] 판 개설 비용 차감 (-500P, 관리자 설정)
- [x] 잇기 누적 보상 (10개당 참여자 전원 +30P)
- [x] 캐릭터별 완결 판정 (판장 수동 + 자동 완결)
- [x] 완결 보상 조건: 상호 n회 이상 이음 (기본 5회)
- [x] 자동 완결: n일 무활동 시 (기본 7일, 패시브 체크)
- [x] 완결 모니터링 관리 페이지 + 대시보드 위젯

### 11.3 좋아요 재화 연동
- [x] DB 테이블 (mg_like_log, mg_like_daily)
- [x] 일일 횟수 제한 (기본 5회)
- [x] 양방향 보상 (누른 사람 10P, 받은 사람 30P)
- [x] 게시글 UI 변경 (남은 횟수 표시)
- [x] 게시판별 좋아요 보상 ON/OFF 토글
- [x] 관리자 설정 + 로그 페이지

### 11.4 정산 시스템 (Request 모드)
- [x] 글쓰기 폼에 보상 유형 드롭다운 추가
- [x] 정산 대기열 관리 페이지 (승인/반려/일괄승인)
- [x] 반려 시 사유 입력 + 알림 발송

### 11.5 대시보드 통합
- [x] 정산 대기 / 오늘 좋아요 통계 카드
- [x] 정산 대기열 위젯 (최근 pending 5건)
- [x] 역극 완결 위젯 (최근 5건)

---

## Phase 12: 업적 시스템 (Achievement)

> 상세: plans/ACHIEVEMENT.md

### 12.1 업적 기본
- [x] DB 테이블 (mg_achievement, mg_achievement_tier, mg_user_achievement, mg_user_achievement_display)
- [x] 핵심 함수 12개 (mg_trigger_achievement, mg_grant/revoke 등)
- [x] 업적 트리거 삽입 (글쓰기, 댓글, RP, 출석, 상점, 좋아요, 개척)
- [x] 희귀도 시스템 (common~legendary)

### 12.2 관리자
- [x] 업적 관리 페이지 (목록, 단계, 달성자, 수동 부여)
- [x] 조건 빌더 + 보상 빌더 UI
- [x] 수동 부여/회수 (일괄 지원)

### 12.3 프론트
- [x] 업적 목록 페이지 (진행률, 카테고리 필터)
- [x] 프로필 쇼케이스 (5슬롯 선택, AJAX 저장)
- [x] 캐릭터 프로필에 쇼케이스 표시

### 12.4 대시보드/알림
- [x] 대시보드 통계 카드 + 최근 업적 달성 위젯
- [x] 업적 달성 토스트 알림 (세션 기반)

---

## Phase 13: 인장 시스템 (Seal / Signature Card)

> 게시글·역극 하단에 자동 표시되는 유저 시그니처 카드.
> 상세: plans/SEAL.md

### 13.1 인장 기본
- [x] DB 테이블 (mg_seal)
- [x] 인장 편집 페이지 (한마디, 자유 영역, 이미지, 링크)
- [x] mg_render_seal() 렌더링 함수 (full/compact)
- [x] 게시글 view 하단 자동 표시
- [x] 캐릭터 프로필 하단 표시

### 13.2 꾸미기 연동
- [x] seal_bg, seal_frame 상점 아이템 타입 추가
- [x] 인장 편집에서 스킨 선택
- [x] 역극 이음 compact 모드

### 13.3 트로피/관리
- [x] 업적 쇼케이스 → 인장 트로피 슬롯 렌더링
- [x] 관리자 인장 목록/검열/강제 초기화
- [x] 인장 설정 (mg_config)

### 13.4 마이 페이지
- [x] 마이 페이지 허브 (bbs/mypage.php)
- [x] 사이드바 마이 페이지 아이콘 추가

---

## Phase 14: 세계관 위키 (Lore Wiki)

> 세계관 설정을 위키형으로 관리 + 타임라인 연표.
> 상세: plans/LORE_WIKI.md

### 14.1 위키 기본
- [x] DB 테이블 (mg_lore_article, mg_lore_section, mg_lore_era, mg_lore_event, mg_lore_link)
- [x] 위키 문서 CRUD (섹션별 분류, 상호 링크)
- [x] 프론트 열람 페이지 (wiki.php, wiki_view.php)
- [x] 관리자 페이지 (lore.php, lore_update.php)

### 14.2 타임라인
- [x] 세계관 연대기 관리 (시대 + 사건)
- [x] 프론트 타임라인 시각화 (timeline.php)

### 14.3 세계관 맵
- [x] 세계관 맵 페이지 (lore_map.php — 파견지 마커 + 팝업)
- [x] 관리자 맵 설정 (lore_map.php — 이미지 업로드, 마커 스타일 선택)
- [x] 맵 마커 4종 (pin/circle/diamond/flag), 잠금 지역 반투명 처리
- [x] 위키 탭에 "세계관 맵" 추가 (전체/타임라인/맵)

### 14.4 부가
- [x] 사이드바 세계관 아이콘 + 2뎁스 패널
- [x] 콘텐츠 최대 폭 통일 (72rem)

---

## Phase 15: 미션 시스템 (Prompt Mission)

> 주간/월간 미션 게시판 스킨. 포인트 수급처 + 스토리 진행용.
> 상세: plans/PROMPT_MISSION.md

### 15.1 미션 기본
- [x] DB 테이블 (mg_prompt, mg_prompt_entry)
- [x] 관리자 미션 CRUD (등록/수정/종료/삭제)
- [x] mission 게시판 스킨 (list/write/view)
- [x] 글 작성 시 미션 선택 + 엔트리 자동 생성
- [x] auto 모드: 제출 즉시 보상 지급
- [x] 미션 상세 모달 (HTML 설명 렌더링)
- [x] 관리자 Toast UI Editor 연동

### 15.2 검수 모드
- [x] review 모드: 관리자 승인/반려 + 사유 알림
- [x] 일괄 승인 + 우수작 선정
- [x] 보상 일괄 지급 (포인트 + 재료)
- [ ] vote 모드: 추천수 기준 상위 N명 보상

### 15.3 미션 부가
- [x] 배너 이미지 업로드
- [x] 달그늘 예시 미션 5개 (시드 데이터)
- [ ] 기한 만료 자동 종료 (패시브)
- [ ] 태그 필터링
- [ ] 미션 복제 (재활용)
- [ ] 대시보드 위젯 (활성 미션, 검수 대기)
- [ ] 미션별 참여 통계

---

## Phase 16: 캐릭터 관계 시스템 (Character Relation)

> 캐릭터 간 관계를 신청→승인으로 맺고, vis.js Network로 시각화.
> 상세: plans/CHARACTER_RELATION.md

### 16.1 관계 기본 (백엔드)
- [x] DB 테이블 (mg_relation, mg_relation_icon)
- [x] 기본 아이콘 세트 9종 (애정/우정/가족/적대/사제/기타)
- [x] 핵심 함수 12개 (CRUD, 신청/승인/거절/해제, 그래프 데이터)
- [x] 승인/거절 + 알림 시스템 연동
- [x] 관계 수정/해제
- [x] 유저 커스텀 아이콘/색상 (프리셋 → 자유 설정 리팩토링, cr_icon_a/b, cr_color)

### 16.2 관계도 시각화 + UI 재배치
- [x] vis.js Network 그래프 렌더링 구현
- [x] 카테고리/세력 필터, 검색
- [x] 캐릭터 뷰페이지 내 인라인 관계도 (독립 페이지 → 뷰페이지 통합)
- [x] 캐릭터 뷰페이지에 관계 신청 버튼 + 모달 (아이콘 팔레트, 라벨/메모)
- [x] 캐릭터 관리에 관계 탭 (받은 신청/내 관계/보낸 신청)
- [x] 사이드바 독립 아이콘 제거
- [x] 독립 페이지 리다이렉트 처리 (relation.php, relation_graph.php)
- [x] 관계도 노드 배치 저장 (ch_graph_layout — JSON, 드래그 후 위치 유지)

### 16.3 관계 관리자
- [x] 아이콘 관리 CRUD (커스텀 카테고리)
- [x] 관계 목록/강제 해제/강제 승인
- [ ] 관계도 설정 (depth, 물리 시뮬레이션, 최대 노드)
- [ ] 통계 (카테고리 분포, TOP 10)

---

## Phase 17: 탐색 파견 + 댓글 주사위

### 17.1 댓글 주사위
> 댓글 영역 🎲 버튼으로 서버사이드 랜덤 생성. 역극 모집, 이벤트, TRPG 판정용.
> 상세: plans/DICE_SYSTEM.md

- [x] 기존 write 테이블 wr_1/wr_2 활용 (별도 테이블 불필요)
- [x] mg_board_reward에 dice 설정 컬럼 3개 (br_dice_use, br_dice_once, br_dice_max)
- [x] 🎲 버튼 + 서버사이드 rand() 댓글 자동 등록 (comment_dice.php)
- [x] 주사위 댓글 별도 스타일 (수정/삭제 불가, 앰버 배경)
- [x] 최고값 ★ 하이라이트 표시
- [x] 게시판별 주사위 ON/OFF 토글 (관리자 보상 모달)
- [x] 1인 1회 제한 (한 글에서 한 번)
- [x] 최대값 설정 (관리자, 기본 100)

### 17.2 이모티콘 피커 보완
- [x] emoticon-picker.js SmartEditor2 iframe 삽입 지원
- [x] 게시글 작성 폼 4종에 이모티콘 피커 추가 (basic, postit, memo, prompt)
- [x] RP 이음 폼에 이모티콘 피커 추가 (참여자별 고유 picker)
- [x] postit/prompt 댓글 스킨 → basic include로 통합

### 17.3 탐색 파견
> 개척 시스템 확장. 스태미나로 파견 보내 재료 수급.
> 상세: plans/PIONEER_EXPEDITION.md

- [x] DB 테이블 (mg_expedition_area, mg_expedition_drop, mg_expedition_log)
- [x] 파견지 관리 + 드롭 테이블 설정 (관리자)
- [x] 파견 보내기 / 수령 로직 (타이머 기반)
- [x] 스태미나 소모 + 확률 기반 보상
- [x] 파견 시 파트너 선택 (관계 기반, 동의 없이, 1일 1회 제한)
- [x] 파트너 보상 포인트 +20% 보너스 + 알림
- [x] "나를 선택한 사람" 조회
- [x] 파견 페이지 (진행 중 타이머, STEP 형 파견 UI)
- [x] 수령 화면 (드롭 결과, 레어 강조)
- [x] 개척 메인 탭 추가 (시설 건설 / 탐색 파견)
- [x] 관리자 파견 로그 조회
- [x] 맵 모드 UI (ea_map_x/y 좌표, expedition_ui_mode config, 세계관 맵 연동)

---

## Phase 18: 의뢰 매칭 시스템 (The Concierge)

> 캐릭터 간 창작 협업 의뢰 등록 → 지원 → 매칭 → 게시판 결과물 → 완료.
> 상세: plans/CONCIERGE.md

### 18.1 의뢰 기본
- [x] 기획서 작성 (`docs/plans/CONCIERGE.md`)
- [x] DB 테이블 (mg_concierge, mg_concierge_apply, mg_concierge_result)
- [x] 의뢰 CRUD (등록/목록/상세/수정/삭제)
- [x] 지원 기능 (캐릭터 선택 + 메시지)
- [x] 직접 선택 매칭
- [x] 보상 단일화 (긴급 티어 제거, concierge_reward 단일 키)

### 18.2 완료 판정
- [x] 의뢰 수행 전용 게시판 (concierge_result, 스킨 4파일)
- [x] 결과물 등록 → 자동 완료 + 보상 지급
- [x] 업적 쌍방 트리거 (수행자 + 의뢰자)

### 18.3 확장
- [x] 추첨 매칭 (가중치 아이템 연동)
- [ ] 상점 아이템 (슬롯 추가, 추첨 확률 UP, 하이라이트) → 상점 작업 시 병행
- [x] 마감 자동 만료
- [x] 미이행 강제 종료 (force_close) + 페널티 시스템 (N회 누적 → NN일 이용 제한, 관리자 설정)
- [x] 동시 지원 제한 (기본 3개, 관리자 설정)
- [x] 기본 슬롯 2→1 변경

### 18.4 관리자
- [x] 의뢰 조회/관리 페이지
- [x] 관리자 설정 (사용 여부, 최대 슬롯, 동시 지원 수, 보상 금액, 페널티 횟수/기간)

---

## 검수 작업 (QoL / Bug Fix)

> 1차 기능 개발 완료 후 진행한 UI/UX 개선 및 버그 수정.

### 사이드바 네비게이션 정리
- [x] 역극·미션을 사이드바 1뎁스로 독립 (기존: 게시판 하위 항목)
- [x] 의뢰를 사이드바 1뎁스에 추가 (서류가방 아이콘, config 게이트)
- [x] 캐릭터 목록을 세계관 위키 아래로 이동
- [x] "새글" → "알림" 링크로 변경 (notification.php)
- [x] 역극/게시판 아이콘 교체 (역극: 채팅 → 게시판: 펜)
- [x] "프롬프트" → "미션" 전체 용어 변경 (사이드바, 관리자, 스킨)
- [x] SPA 라우터 활성 상태 동기화 (미션, 의뢰 포함)

### 헤더 UI 개선
- [x] 현재 접속자 수 뱃지 추가 (config 토글)
- [x] 접속자 모달 → 드롭다운 패널로 변경 (버튼 바로 아래, blur 제거)

### 역극 개선
- [x] 참여자 목록에서 판장(저자) 제외
- [x] 답글 0인 참여자 비노출 (DB 쿼리 + JS + PHP 필터)
- [x] 마지막 답글 삭제 시 참여자 레코드 자동 정리
- [x] 답글 폼 구조 개선: 툴바 행(캐릭터+이모티콘) + 콘텐츠 행(textarea+이미지+전송)
- [x] 이미지 버튼 가로세로 가운데 정렬

### 댓글 UI 개선
- [x] 이모티콘 버튼을 캐릭터 선택과 같은 행으로 이동
- [x] 주사위 버튼을 이모티콘 옆으로 이동
- [x] textarea에 h-full 추가 (높이 채우기)

### 게시판/검색 버그 수정
- [x] 검색 Fatal error 수정 (sql_num_rows on false)
- [x] 접속자 버튼 모바일 미표시 수정 (sm:inline-flex CSS 추가)

### 미션 개선
- [x] 미션 상세 모달 추가 (HTML 설명 렌더링, 참여 현황)
- [x] 관리자 미션 설명 에디터 → Toast UI Editor
- [x] 미션 글쓰기 페이지 외부 이모티콘 버튼 제거

### 기타 개선
- [x] 페이지 타이틀 형식 통일 ("사이트명 | 페이지명") + SPA 타이틀 잔류 버그 수정
- [x] 배포 방식 전환 (git-ftp → lftp mirror 전체 배포)
- [x] DB 마이그레이션 엔진 (plugin/morgan/migrate.php — mg_migrations 테이블, 세션당 1회 자동 체크)
- [x] Toast UI Editor 플러그인 설치 (plugin/editor/toastui/ — 다크테마, 이미지 업로드)
- [x] 시드 데이터 마이그레이션 3건 (이모티콘 샘플, 위키 달그늘 데이터, 캐릭터 세력/종족/프로필)

### 소속/유형 관리 개편
- [x] 진영/클래스 → 소속/유형 명칭 변경 (13개 파일)
- [x] 소속별 유형 연동 (mg_class.side_id: 0=공용, >0=특정 소속 전용)
- [x] 캐릭터 폼 JS 필터링 (소속 선택 시 유형 자동 필터)
- [x] 관리자 side_class.php 접기/드래그 정렬 + 사용법 가이드

### 스모크 테스트 (베타 배포 전 코드 레벨 검증)
- [x] 상점: mg_buy_item/mg_send_gift/mg_accept_gift 반환값 비교 수정 ($result === true → $result['success'])
- [x] 상점: 관리자 상품 등록/수정에 sc_id(카테고리) 누락 수정
- [x] 역극: 관리자 뷰 링크 404 수정 (plugin/morgan/rp/view.php → bbs/rp_view.php)
- [x] 역극: 멀티캐릭터 ch_id 덮어쓰기 확인 (표시 이슈만, 데이터 유실 없음 → 베타 후 개선)
- [x] 게시판: $comment_id 미정의 변수 수정
- [x] 출석: insert_point() rel 파라미터 누락 수정 (중복 방지)
- [x] 게시판: 댓글 수정 기능 구현 (인라인 수정 폼, w=cu 모드)

---

## 1차 마무리 (잔여 구현)

> 1차 Phase에서 빠졌거나 후순위로 밀린 기능들을 순차 구현.
> 검수 작업(위)은 버그/UI 개선, 여기는 미구현 기능 채우기.

### M1. 관계 시스템 정리 (from Phase 16.3) ✅
- [x] 데드코드 제거 (relation_icon.php, 미사용 함수 2개, category 파라미터)
- [x] LEFT JOIN mg_relation_icon 8개 제거 (ri_id 항상 0, 유저 커스텀으로 전환 완료)
- [x] 파견 파트너 코드 ri_ → cr_ 필드 참조 수정
- [x] 관계 승인 시 업적 트리거 추가 (relation_count)
- ~~관계도 설정 / 통계~~ → 관리자 개입 최소화 방침으로 스킵

### M2. 컨시어지 시스템 개편 (from Phase 18) ✅
- [x] 긴급(urgent) 티어 전면 제거, 보상 단일화
- [x] 기본 슬롯 2→1, 동시 지원 최대 3개 제한 (관리자 설정)
- [x] 미이행 강제 종료 (force_close) + N회 누적 시 NN일 이용 제한 (관리자 설정)
- [x] 의뢰 수행 전용 게시판 (concierge_result) + 스킨 4파일 + 마이그레이션
- [x] 업적 쌍방 트리거 (수행자 + 의뢰자)
- [x] 마이그레이션 러너 .php 파일 카운트 버그 수정
- [ ] 상점 아이템 (슬롯 추가, 추첨 확률 UP, 하이라이트) → 상점 작업 시 병행

### M3. 회원 설정 보완 (from Phase 1.2) ✅
- [x] 닉네임 변경 주기 관리자 설정 (cf_nick_modify → g5_config 연동)
- [x] 가입 시 기본 레벨 설정 (cf_register_level → g5_config 연동)
- [x] 기능별 최소 레벨 게이트 (RP, 의뢰, 개척, 인장, 이모티콘, 미션 — 기본값 2)
- [x] mg_check_member_level() 헬퍼 함수 + 6개 진입점 적용

### M4. 미션 확장 (from Phase 15.2~15.3) ✅
- [x] vote 모드: 추천수 기준 상위 N명 보너스 + 전원 기본 보상 (mg_prompt_vote_settle)
- [x] 기한 만료 자동 종료 (mg_prompt_check_expired — 기존 구현 확인)
- [x] 태그 필터링 (프론트 칩 UI + JS 필터)
- [x] 미션 복제 (관리자 목록 → clone_id로 프리필)
- [x] 대시보드 위젯 (활성 미션/검수 대기 stat 카드 + 검수 대기 위젯)
- [x] 미션별 참여 통계 (평균 글자수, 총 보상액, 참여자 수)
- [x] 메인 달력 위젯 (MG_Calendar_Widget, 월별 미션 기간 표시)

### M5. 스태프 권한 시스템 (from Phase 9.3) ✅
- [x] DB 테이블 (mg_staff_role, mg_staff_member)
- [x] 역할 관리 (생성/수정/삭제, 26개 권한군 단일 체크)
- [x] 스태프 목록 (회원 검색, 역할 배정/해제, 다중 역할)
- [x] 권한별 메뉴 접근 제어 (g5_auth 동기화, 사이드바 필터링)

### M6. 미니게임 추가 (from Phase 3.3~3.4) ✅
- [x] 운세뽑기 (DB, 게임 클래스, 별점 가중치, 모달 CRUD)
- [x] 종이뽑기 (DB 3테이블, 게임 클래스, 유저 직접 선택, 문방구 디자인)
- [x] 주사위 간소화 (크리티컬 제거, 야찌→퍼펙트, 탭 분리)

### M7. 캐릭터 장비 (from Phase 5.3) → 2차-B(SS Engine)로 이관
- SS Engine 장비 시스템과 완전히 겹치므로 통합 진행
- 상세: `docs/plans/SS_ENGINE_DESIGN.md` 섹션 1.5

### M8. 인장 그리드 빌더 (from Phase 13)
> 인장을 고정 레이아웃 → 2D 격자 캔버스 자유 배치 방식으로 전환.
> 메인 페이지 빌더의 GridStack + CSS Grid 패턴을 재활용.
> 상세: `docs/plans/SEAL.md` 하단 "추가사항: 인장 그리드 빌더"

- [ ] mg_seal 테이블에 `seal_layout` JSON 컬럼 추가 (요소별 x/y/w/h 저장)
- [ ] 인장 편집 페이지에 GridStack 기반 2D 캔버스 편집기 (10×6 고정 격자, 2~3배 확대)
- [ ] 배치 가능 요소: 캐릭터 이미지, 닉네임, 칭호, 한마디, 자유 텍스트, 이미지, 링크, 트로피, 꾸미기 스티커(상점)
- [ ] mg_render_seal() full 모드를 CSS Grid 기반 렌더링으로 교체
- [ ] compact 모드는 기존 고정 레이아웃 유지 (아바타+닉+칭호 한 줄)
- [ ] 모바일 편집 불가 (PC 전용 편집기, 안내 메시지 표시)
- [ ] 인장 렌더링 제한: 일부 게시글 뷰페이지 + 캐릭터 상세 페이지만 (RP 답글 제거)
- [ ] 꾸미기 스티커 상점 아이템 타입 추가 (seal_sticker)

---

# 2차 작업

> 2차 작업은 별도 문서로 분리되었습니다: **[`docs/ROADMAP_PHASE2.md`](ROADMAP_PHASE2.md)**

| ID | 모듈 | 한줄 요약 | 기획서 |
|----|------|----------|--------|
| 2차-A | 연구 트리 | 재화 공동 투입 → 커뮤니티 버프 + 시설 해금 | `plans/RESEARCH_TREE.md` |
| 2차-B | SS Engine | 세미 턴제 RPG + 던전 탐사 (Supabase Realtime) | `plans/SS_ENGINE_DESIGN.md` |
| 2차-C | 진영 컨텐츠 | 익명망 + 카드배틀 + 점령전 | `plans/FACTION.md` |
| 2차-D | 마이룸 | 아이소메트릭 2D 방 꾸미기 | `MODULES.md` |
| 2차-E | VN Engine | 역극 → 비주얼 노벨 자동 변환 | `plans/VN_ENGINE.md` |
| 2차-F | SRPG | 그리드 기반 Co-op PvE 택틱스 전투 | `plans/morgan_srpg_system_plan.md` |

---

## 부록: 파일 구조

```
new_cms/
├── adm/morgan/                 # Morgan 관리자
│   ├── _head.php, _tail.php    # 레이아웃
│   ├── config.php              # 기본 설정 (사이트/미션/의뢰)
│   ├── character_*.php         # 캐릭터 관리
│   ├── profile_field.php       # 프로필 양식
│   ├── side_class.php          # 소속/유형
│   ├── main_builder.php        # 메인 빌더
│   ├── attendance.php          # 출석 통계
│   ├── notification.php        # 알림 관리
│   ├── reward.php              # 보상 관리 (5탭)
│   ├── reward_update.php       # 보상 설정 저장/AJAX
│   ├── achievement.php         # 업적 관리
│   ├── seal.php                # 인장 관리
│   ├── lore.php                # 세계관 위키 관리
│   ├── lore_update.php         # 위키 처리
│   ├── lore_map.php            # 세계관 맵 관리
│   ├── lore_map_update.php     # 맵 설정 저장
│   ├── prompt.php              # 미션 관리 (Toast UI Editor)
│   ├── prompt_update.php       # 미션 처리
│   ├── relation.php            # 캐릭터 관계 관리
│   ├── concierge.php           # 의뢰 관리
│   ├── expedition_area.php     # 파견지 관리
│   ├── expedition_area_update.php # 파견지 저장
│   └── expedition_log.php      # 파견 로그
│
├── bbs/                        # 프론트 페이지
│   ├── character*.php          # 캐릭터 관련
│   ├── attendance*.php         # 출석 관련
│   ├── rp_*.php                # 역극 관련
│   ├── good.php                # 좋아요 (보상 연동)
│   ├── achievement.php         # 업적 목록
│   ├── relation_api.php        # 관계 AJAX API
│   ├── relation_graph_api.php  # 관계도 그래프 데이터 API
│   ├── concierge.php           # 의뢰 목록
│   ├── concierge_view.php      # 의뢰 상세
│   ├── concierge_write.php     # 의뢰 등록
│   ├── concierge_api.php       # 의뢰 AJAX API
│   ├── expedition_api.php      # 파견 AJAX API
│   ├── mypage.php              # 마이 페이지 허브
│   ├── wiki.php                # 세계관 위키
│   ├── wiki_view.php           # 위키 문서 보기
│   ├── lore_map.php            # 세계관 맵 (파견지 마커)
│   ├── timeline.php            # 세계관 타임라인
│   ├── notification.php        # 알림 목록
│   ├── comment_dice.php        # 댓글 주사위 처리
│   └── search.php              # 검색 (sql_num_rows 수정됨)
│
├── plugin/morgan/              # Morgan 플러그인
│   ├── morgan.php              # 메인 플러그인 (테이블 등록, 헬퍼 함수)
│   ├── migrate.php             # DB 마이그레이션 엔진 (mg_migrations 테이블)
│   ├── install/                # 설치 SQL
│   ├── widgets/                # 위젯 클래스
│   └── games/                  # 미니게임 클래스
│
├── plugin/editor/toastui/      # Toast UI Editor 플러그인
│   ├── editor.lib.php          # 에디터 초기화
│   ├── morgan-dark.css         # 다크테마 CSS
│   └── imageUpload/            # 이미지 업로드 핸들러
│
├── theme/morgan/               # Morgan 테마
│   ├── head.php                # 메인 레이아웃 (사이드바, 헤더)
│   ├── tail.php                # 푸터
│   ├── head.sub.php            # 서브 레이아웃 (CSS 변수, 수동 CSS)
│   ├── index.php               # 메인 페이지
│   ├── js/app.js               # SPA 라우터 + 사이드바 상태
│   └── skin/                   # 스킨
│       ├── member/             # 회원 스킨
│       ├── board/basic/        # 기본 게시판 (댓글 스킨 공유)
│       ├── board/gallery/      # 갤러리형
│       ├── board/memo/         # 방명록형
│       ├── board/postit/       # 포스트잇형
│       ├── board/prompt/       # 미션 게시판
│       ├── board/concierge/   # 의뢰 수행 게시판
│       ├── shop/               # 상점/인벤토리/선물
│       ├── rp/                 # 역극
│       ├── notification/       # 알림
│       ├── pioneer/            # 개척
│       ├── emoticon/           # 이모티콘 (picker.skin.php)
│       ├── mypage/             # 마이 페이지
│       ├── widget/             # 위젯 스킨
│       └── attendance/         # 출석 스킨
│
├── db/migrations/              # DB 마이그레이션 파일
│
├── CLAUDE.md                   # AI 에이전트 프로젝트 컨텍스트
│
└── docs/                       # 기획 문서
    ├── CORE.md                 # 핵심 참조 (DB요약, 인덱스)
    ├── ROADMAP.md              # 1차 진행률 (이 파일)
    ├── ROADMAP_PHASE2.md       # 2차 로드맵 (A~F)
    ├── MODULES.md              # 2차 모듈 전략
    ├── plans/                  # 상세 기획
    │   ├── DB.md, API.md, UI.md
    │   ├── CHARACTER.md, MEMBER.md, POINT.md
    │   ├── BOARD.md, SHOP.md, ADMIN.md
    │   ├── PIONEER.md, PIONEER_EXPEDITION.md
    │   ├── REWARD.md, ACHIEVEMENT.md
    │   ├── SEAL.md, LORE_WIKI.md
    │   ├── PROMPT_MISSION.md, DICE_SYSTEM.md
    │   ├── CHARACTER_RELATION.md
    │   ├── CONCIERGE.md
    │   ├── RESEARCH_TREE.md, SS_ENGINE_DESIGN.md
    │   ├── FACTION.md, VN_ENGINE.md
    │   ├── morgan_srpg_system_plan.md
    │   └── DESIGN_ASSETS.md
    └── archive/                # 작업 로그 보관
```

---

## 변경 이력

| 날짜 | 내용 |
|------|------|
| 2026-02-04 | 로드맵 문서 생성, 전체 현황 정리 |
| 2026-02-06 | Phase 6~9 완료 반영, 개척/업적 Phase 추가, docs 구조 정리 |
| 2026-02-10 | Phase 10~14 완료 반영 (대시보드, 개척, 보상, 업적, 인장) |
| 2026-02-12 | Phase 15~16 완료 (세계관 위키, 프롬프트 미션) |
| 2026-02-13 | Phase 17 완료 (캐릭터 관계 + 댓글 주사위 + 이모티콘 피커 보완) |
| 2026-02-15 | Phase 18~19 완료 (탐색 파견, 의뢰 매칭). 연구 트리/SS Engine/진영/마이룸을 2차로 분리 |
| 2026-02-16 | Phase 번호 재정렬 (1~18), 마이룸 → 2차-D로 이동, 검수 작업 섹션 추가, 현재 상태 표시 |
| 2026-02-16 | 2차-E: VN Engine 기획 추가 (docs/plans/VN_ENGINE.md) |
| 2026-02-19 | 검수 누락 반영: 세계관 맵, 관계 커스텀/레이아웃 저장, 파견 맵 모드, 타이틀 통일, 마이그레이션 엔진, Toast UI, 시드 데이터 |
| 2026-02-19 | "1차 마무리 (잔여 구현)" 섹션 추가 — M1~M7 순차 구현 목록 정리 |
| 2026-02-19 | M1 완료 (관계 시스템 정리), M2 완료 (컨시어지 개편: 긴급 제거, 전용 게시판, 페널티) |
| 2026-02-19 | M3 완료 (회원 설정 보완: 닉변경주기, 가입레벨, 기능별 최소레벨 게이트 6종) |
| 2026-02-19 | M4 완료 (미션 확장: vote 정산, 태그 필터, 복제, 대시보드 위젯, 통계, 달력 위젯) |
| 2026-02-20 | 출석 주사위 3D 리뉴얼 (Dice-Box threejs, 야찌 족보, 리롤 quaternion lerp) |
| 2026-02-20 | M5 완료 (스태프 권한: 역할 기반 단일 권한, 사이드바 필터링, g5_auth 동기화) |
| 2026-02-20 | M6 완료 (운세뽑기 + 종이뽑기 + 주사위 간소화). M7 → 2차-B(SS Engine)로 이관 |
| 2026-02-20 | 메인 페이지 빌더 2D 그리드 캔버스 리뉴얼 (GridStack + CSS Grid, 가로 칸 수 커스텀, 정사각형 셀) |
| 2026-02-20 | M8 추가: 인장 그리드 빌더 기획 (고정 레이아웃 → 2D 격자 자유 배치) |
| 2026-02-22 | 소속/유형 관리 개편 (명칭 변경, 소속별 유형 연동, 접기/드래그 정렬) |
| 2026-02-22 | 스모크 테스트: 상점 반환값 비교, RP 뷰 링크, $comment_id, insert_point rel 파라미터, sc_id 누락 수정 |
| 2026-02-23 | 파일 업로드 용량 통합 (16곳 → mg_upload_max_file/mg_upload_max_icon 2키) |
| 2026-02-23 | 2차 작업 로드맵 분리 → ROADMAP_PHASE2.md, 2차-F SRPG 기획 추가 |

---

*이 문서는 개발 진행에 따라 지속적으로 업데이트됩니다.*
