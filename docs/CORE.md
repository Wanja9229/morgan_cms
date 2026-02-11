# Morgan Edition - Core Reference

> 작업 전 이 파일을 먼저 확인. 필요 시 상세 문서 참조.
> 최종 업데이트: 2026-02-10

---

## 진행률

### 완료 (Phase 1~13)
- [x] Phase 1: 테마 기본 구조 (Tailwind, 다크테마, 사이드바)
- [x] Phase 2: 캐릭터 시스템 (등록, 승인, 프로필, 세력/종족)
- [x] Phase 3: 포인트/출석 (출석체크, 주사위 게임)
- [x] Phase 4: 메인 빌더 (위젯, 드래그앤드롭)
- [x] Phase 5: 상점 시스템 (상품, 인벤토리, 선물)
- [x] Phase 6: 역극(RP) 시스템 (판 세우기, 이음, 완결)
- [x] Phase 7: 이모티콘 시스템 (셋 관리, 구매, 삽입, 유저 제작)
- [x] Phase 8: 알림 시스템 (벨 아이콘, 토스트, 트리거)
- [x] Phase 9: 개척 시스템 (노동력, 재료, 시설 건설, 명예의 전당)
- [x] Phase 10: 관리자 대시보드 (통계 카드, 위젯, 전체 현황)
- [x] Phase 11: 게시판 스킨 4종 (basic, gallery, memo, postit)
- [x] Phase 12: 보상 시스템 (게시판 보상, RP 보상, 좋아요, 정산, 대시보드 통합)
- [x] Phase 13: 업적 시스템 (DB 4테이블, 트리거, 관리자CRUD, 프론트, 쇼케이스, 토스트)
- [x] Phase 14: 인장 시스템 (DB, 편집/미리보기, 렌더링, 관리자, 마이 페이지)

### 미구현 (Phase 15~19)
- [ ] Phase 15: 세계관 위키 (위키형 설정집 + 타임라인) → 기획 필요
- [ ] Phase 16: 정기 프롬프트 (주간/월간 미션) → `plans/PROMPT_MISSION.md`
- [ ] Phase 17: 캐릭터 관계 (관계도, vis.js) → `plans/CHARACTER_RELATION.md`
- [ ] Phase 18: 연구 트리 (공동 투자, 버프) → `plans/RESEARCH_TREE.md`
- [ ] Phase 19: SS Engine / TRPG 세션 → `plans/SS_ENGINE.md`

### 2차 (별도 패키지)
- [ ] VN Engine (역극→비주얼노벨 변환) → `MODULES.md`
- [ ] 익명망 (캐입 익명 게시판) → `MODULES.md`
- [ ] 마이룸 시스템 (아이소메트릭 방 꾸미기) → DB만 설계됨
- [ ] TRPG 스탯 시스템 (멀티 룰셋 CoC/D&D/VtM) → DB만 설계됨 (`plans/DB.md`)

---

## DB 테이블 요약

> 전체 상세 스키마: `plans/DB.md` | SQL: `plugin/morgan/install/install.sql`

### 회원/캐릭터 (6)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_character | 캐릭터 | ch_id, mb_id, ch_name, ch_state, ch_type, ch_main |
| mg_character_log | 승인 로그 | ch_id, log_action, log_memo, admin_id |
| mg_profile_field | 프로필 양식 | pf_id, pf_code, pf_type, pf_options |
| mg_profile_value | 프로필 값 | ch_id, pf_id, pv_value |
| mg_side | 세력 | side_id, side_name |
| mg_class | 종족 | class_id, class_name |

### 시스템 (4)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_config | 설정값 | cf_key, cf_value |
| mg_attendance | 출석 | mb_id, at_date, at_game_type, at_point |
| mg_notification | 알림 | mb_id, noti_type, noti_title, noti_read, noti_url |
| mg_write_character | 글-캐릭터 연결 | bo_table, wr_id, ch_id |

### 메인 빌더 (2)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_main_row | 메인 페이지 행 | row_id, row_order, row_use |
| mg_main_widget | 메인 위젯 | widget_id, row_id, widget_type, widget_config(JSON) |

### 상점/인벤토리 (6)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_shop_category | 카테고리 | sc_id, sc_name |
| mg_shop_item | 상품 | si_id, si_name, si_price, si_type, si_effect(JSON) |
| mg_shop_log | 구매 로그 | mb_id, si_id, sl_type |
| mg_inventory | 보유 아이템 | mb_id, si_id, iv_count |
| mg_item_active | 적용 중 | mb_id, si_id, ia_type |
| mg_gift | 선물 | mb_id_from, mb_id_to, si_id, gf_status |

### 역극(RP) (5)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_rp_thread | 역극 | rt_id, rt_title, rt_status, mb_id, ch_id |
| mg_rp_reply | 이음 | rr_id, rt_id, rr_content, mb_id, ch_id, rr_context_ch_id |
| mg_rp_member | 참여자 | rt_id, mb_id, ch_id, rm_reply_count |
| mg_rp_completion | 완결 기록 | rc_id, rt_id, ch_id, rc_mutual_count, rc_point, rc_status |
| mg_rp_reply_reward_log | 잇기 보상 추적 | rrl_id, rt_id, rrl_reply_count, rrl_point |

### 이모티콘 (3)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_emoticon_set | 이모티콘 셋 | es_id, es_name, es_price, es_creator_id, es_status |
| mg_emoticon | 개별 이모티콘 | em_id, es_id, em_code, em_image |
| mg_emoticon_own | 보유 셋 | mb_id, es_id |

### 개척(Pioneer) (7)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_material_type | 재료 종류 | mt_id, mt_name, mt_code, mt_icon |
| mg_user_material | 유저 재료 보유 | mb_id, mt_id, um_count |
| mg_user_stamina | 유저 노동력 | mb_id, us_current, us_max, us_last_reset |
| mg_facility | 시설 | fc_id, fc_name, fc_status, fc_unlock_type, fc_stamina_cost |
| mg_facility_material_cost | 시설 재료 비용 | fc_id, mt_id, fmc_required, fmc_current |
| mg_facility_contribution | 기여 기록 | fc_id, mb_id, fcn_type, fcn_amount |
| mg_facility_honor | 명예의 전당 | fc_id, fh_rank, fh_category, mb_id |

### 보상(Reward) (5)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_board_reward | 게시판별 보상 설정 | bo_table, br_mode(auto/request/off), br_point, br_like_use |
| mg_like_log | 좋아요 보상 로그 | mb_id, target_mb_id, bo_table, wr_id |
| mg_like_daily | 일일 좋아요 카운터 | mb_id, ld_date, ld_count |
| mg_reward_type | 보상 유형 (request용) | rwt_id, bo_table, rwt_name, rwt_point |
| mg_reward_queue | 정산 대기열 | rq_id, mb_id, rwt_id, rq_status(pending/approved/rejected) |

### 인장(Seal) (1)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_seal | 인장 | seal_id, mb_id, seal_use, seal_tagline, seal_content, seal_image, seal_link |

### 그누보드 기본
| 테이블 | 용도 |
|--------|------|
| g5_member | 회원 |
| g5_point | 포인트 내역 |
| g5_board | 게시판 설정 |
| g5_write_{bo_table} | 게시판별 글 (notice, qna, owner, vent, log 등) |

### TRPG (미구현, DB만 설계)
| 테이블 | 용도 |
|--------|------|
| mg_trpg_ruleset | 룰셋 정의 (CoC, D&D, VtM) |
| mg_trpg_stat_field | 스탯/스킬 필드 정의 |
| mg_character_stat_value | 캐릭터별 스탯 값 |
| mg_character_ruleset | 캐릭터-룰셋 연결 |

---

## 파일 인덱스

### 핵심 코드
| 파일 | 용도 |
|------|------|
| `plugin/morgan/morgan.php` | 모든 헬퍼 함수 (mg_*) |
| `plugin/morgan/install/install.sql` | DB 스키마 |
| `extend/morgan.extend.php` | 그누보드 훅 연동 |
| `theme/morgan/head.php` | 메인 레이아웃 + 사이드바 |

### 관리자 페이지 (`adm/morgan/`)
| 파일 | 용도 |
|------|------|
| `dashboard.php` | 대시보드 (통계, 위젯, 최근활동) |
| `character.php / character_update.php` | 캐릭터 관리 (승인/반려) |
| `board_skin.php` | 게시판 스킨 관리 |
| `shop.php / shop_update.php` | 상점 관리 |
| `point.php` | 포인트/출석 관리 |
| `emoticon.php / emoticon_update.php` | 이모티콘 관리 |
| `rp_list.php` | 역극 관리 |
| `pioneer.php / pioneer_update.php` | 개척 시설 관리 |
| `reward.php / reward_update.php` | 보상/정산 관리 |
| `main_builder.php / main_builder_update.php` | 메인 페이지 빌더 |
| `seal.php` | 인장 관리 |
| `config.php / config_update.php` | Morgan 설정 (인장 설정 포함) |

### 사용자 페이지 (`bbs/`)
| 파일 | 용도 |
|------|------|
| `rp.php / rp_create.php / rp_view.php` | 역극 목록/생성/뷰 |
| `rp_api.php` | 역극 API (이음, 참여, 완결) |
| `good.php` | 좋아요 처리 + 보상 |
| `character_*.php` | 캐릭터 등록/수정/프로필 |
| `shop.php` | 상점/인벤토리/선물 |
| `attendance.php` | 출석체크 |
| `pioneer.php` | 개척 시설 목록/기여 |
| `mypage.php` | 마이 페이지 (유저 허브) |
| `seal_edit.php` | 인장 편집 |
| `seal_edit_update.php` | 인장 저장 (AJAX) |
| `seal_image_upload.php` | 인장 이미지 업로드 (AJAX) |

### 게시판 스킨 (`theme/morgan/skin/board/`)
| 스킨 | 용도 |
|------|------|
| `basic/` | 기본 게시판 (공지, 자유 등) |
| `gallery/` | 갤러리형 (로그) |
| `memo/` | 짧은 글/문의 (오너게, QnA) |
| `postit/` | 포스트잇형 (앓이란) |

### 상세 기획 문서

| 작업 영역 | 참조 파일 | 상태 |
|----------|----------|------|
| DB 스키마 전체 | `plans/DB.md` | Phase 1~8 확정, 9~12 추가 필요 |
| API 설계 | `plans/API.md` | 참고용 |
| UI/테마 | `plans/UI.md` | 참고용 |
| 회원 시스템 | `plans/MEMBER.md` | 구현 완료 |
| 캐릭터 시스템 | `plans/CHARACTER.md` | 구현 완료 |
| 게시판/역극 | `plans/BOARD.md` | 구현 완료 |
| 상점/인벤토리 | `plans/SHOP.md` | 구현 완료 |
| 포인트/출석 | `plans/POINT.md` | 구현 완료 |
| 관리자 | `plans/ADMIN.md` | 구현 완료 |
| 개척 시스템 | `plans/PIONEER.md` | 구현 완료 |
| 개척 확장 | `plans/PIONEER_EXPEDITION.md` | 참고용 (2차) |
| 보상 시스템 | `plans/REWARD.md` | 구현 완료 |
| 업적 시스템 | `plans/ACHIEVEMENT.md` | 구현 완료 |
| 인장 시스템 | `plans/SEAL.md` | 구현 완료 |
| 정기 프롬프트 | `plans/PROMPT_MISSION.md` | 미구현 (Phase 15) |
| 캐릭터 관계 | `plans/CHARACTER_RELATION.md` | 미구현 (Phase 16) |
| 연구 트리 | `plans/RESEARCH_TREE.md` | 미구현 (Phase 17) |
| SS Engine | `plans/SS_ENGINE.md` | 미구현 (Phase 18) |
| 디자인 에셋 | `plans/DESIGN_ASSETS.md` | 참고용 |
| 2차 모듈 | `MODULES.md` | 참고용 |

---

## 알림 타입 (noti_type)

| 타입 | 발생 시점 |
|------|----------|
| `comment` | 내 글에 댓글 |
| `reply` | 내 댓글에 답글 |
| `like` | 내 글 추천 |
| `rp_reply` | 내 역극에 이음 |
| `rp_completion` | 역극 완결 처리 |
| `character_approved` | 캐릭터 승인 |
| `character_rejected` | 캐릭터 반려 |
| `gift_received` | 선물 수신 |
| `gift_accepted` | 선물 수락됨 |
| `emoticon` | 이모티콘 승인/반려 |
| `reward_approved` | 보상 승인됨 |
| `reward_rejected` | 보상 반려됨 |
| `facility_complete` | 시설 완공 |
| `pioneer` | 재료 드롭/노동력 관련 |
| `system` | 시스템 알림 |

---

## 상품 타입 (si_type)

| 타입 | 용도 | si_effect 예시 |
|------|------|---------------|
| `title` | 칭호 | `{"text":"전설의 용사"}` |
| `badge` | 뱃지 | `{"icon":"star","color":"gold"}` |
| `nick_color` | 닉네임 색상 | `{"color":"#ff6b6b"}` |
| `nick_effect` | 닉네임 효과 | `{"effect":"glow"}` |
| `profile_border` | 프로필 테두리 | `{"border":"rainbow"}` |
| `equip` | 장비 | 캐릭터별 장착 |
| `emoticon_set` | 이모티콘 셋 | `{"es_id":5}` |
| `emoticon_reg` | 이모티콘 등록권 | 유저 이모티콘 제작 |
| `material` | 재료 아이템 | `{"mt_code":"wood","amount":5}` |
| `seal_bg` | 인장 배경 스킨 | `{"bg_image":"url"}` |
| `seal_frame` | 인장 프레임 스킨 | `{"border_color":"#fff"}` |

---

## 빠른 참조

### mg_notify() 사용법
```php
mg_notify($mb_id, $type, $title, $content, $url);
// 예: mg_notify('user1', 'comment', '새 댓글', '게시글 제목', '/bbs/board.php?...');
```

### insert_point() 사용법
```php
insert_point($mb_id, $amount, $content, $rel_table, $rel_id, $rel_action);
// 예: insert_point('user1', 100, '글 작성 보상', 'write_free', '15', '글쓰기');
```

### mg_config() 사용법
```php
$val = mg_config('key', 'default');  // 단일 값 읽기
$all = mg_get_config();              // 전체 설정 배열
mg_set_config('key', 'value');       // 설정 저장
```

### 캐릭터 상태
- `editing`: 작성 중
- `pending`: 승인 대기
- `approved`: 승인됨
- `deleted`: 삭제됨

### 역극 상태
- `open`: 진행 중
- `closed`: 완결 (vn_engine 변환 대상)
- `deleted`: 삭제됨

### 시설 상태
- `locked`: 미해금
- `building`: 건설 중
- `complete`: 완공

### 보상 모드 (br_mode)
- `auto`: 자동 지급 (글 작성 시 즉시)
- `request`: 요청 후 관리자 승인
- `off`: 보상 없음

---

## 개발 환경

### Docker 접근
```bash
# SQL 실행
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db -e "SQL"

# PHP syntax check (Windows: 경로 앞 // 필요)
docker exec morgan_php php -l //var/www/html/path/to/file.php

# 테이블 구조 확인
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db -e "DESCRIBE mg_character;"
```

**컨테이너 정보:**
- MySQL: `morgan_mysql` (3307)
- PHP-FPM: `morgan_php`
- Nginx: `morgan_nginx` (8080)
- DB: `morgan_db` / `morgan_user` / `morgan_pass`

---

## 작업 규칙

- **Git push는 사용자가 요청할 때만** 실행
- 테이블 등록: `$g5['mg_*_table']` (morgan.php line ~40-57) + `$mg['*_table']` 호환 (line ~75-112)
- 관리자 메뉴: `adm/admin.menu800.php` - 배열 `[ID, name, URL, permission_key, group_name]`
- 관리자 페이지 패턴: `$sub_menu` → `auth_check_menu()` → `_head.php` → content → `_tail.php`
- 게시판별 write 테이블 (`write_{bo_table}`)은 JOIN 불가 → 개별 쿼리 필요

---

*이 파일은 작업 효율을 위한 요약본입니다. 상세 내용은 plans/ 폴더의 개별 문서를 참조하세요.*
