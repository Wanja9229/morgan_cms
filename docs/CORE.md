# Morgan Edition - Core Reference

> 작업 전 이 파일을 먼저 확인. 필요 시 상세 문서 참조.
> 최종 업데이트: 2026-02-06

---

## 진행률

### 완료
- [x] Phase 1: 테마 기본 구조 (Tailwind, 다크테마, 사이드바)
- [x] Phase 2: 캐릭터 시스템 (등록, 승인, 프로필, 세력/종족)
- [x] Phase 3: 포인트/출석 (출석체크, 주사위 게임)
- [x] Phase 4: 메인 빌더 (위젯, 드래그앤드롭)
- [x] Phase 5: 상점 시스템 (상품, 인벤토리, 선물)
- [x] Phase 6: 역극(RP) 시스템 (판 세우기, 이음, 완결)
- [x] Phase 7: 이모티콘 시스템 (셋 관리, 구매, 삽입)
- [x] Phase 8: 알림 시스템 (벨 아이콘, 토스트, 트리거)
- [x] 게시판 스킨 4종 (basic, gallery, memo, postit)

### 미완료
- [ ] Phase 9: 개척 시스템 (노동력, 재료, 시설 건설) → `plans/PIONEER.md`
- [ ] Phase 10: 업적 시스템 (트로피, 쇼케이스) → 기획 필요
- [ ] TRPG 스탯 시스템 (멀티 룰셋) → DB만 설계됨
- [ ] 관리자 대시보드 (통계, 승인대기)
- [ ] 마이룸 시스템 (2차)
- [ ] SS Engine (2차, Supabase)
- [ ] VN Engine (2차, 역극→비주얼노벨)

---

## DB 테이블 요약

### 회원/캐릭터
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| g5_member | 회원 (그누보드) | mb_id, mb_nick, mb_point, mb_level |
| mg_character | 캐릭터 | ch_id, mb_id, ch_name, ch_state, ch_type, ch_main |
| mg_character_log | 승인 로그 | ch_id, log_action, log_memo, admin_id |
| mg_profile_field | 프로필 양식 | pf_id, pf_code, pf_type, pf_options |
| mg_profile_value | 프로필 값 | ch_id, pf_id, pv_value |
| mg_side | 세력 | side_id, side_name |
| mg_class | 종족 | class_id, class_name |

### 역극(RP)
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_rp_thread | 역극 | rt_id, rt_title, rt_status(open/closed), mb_id, ch_id |
| mg_rp_reply | 이음 | rr_id, rt_id, rr_content, mb_id, ch_id |
| mg_rp_member | 참여자 | rt_id, mb_id, ch_id, rm_reply_count |

### 상점/인벤토리
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_shop_category | 카테고리 | sc_id, sc_name |
| mg_shop_item | 상품 | si_id, si_name, si_price, si_type, si_effect(JSON) |
| mg_inventory | 보유 아이템 | mb_id, si_id, iv_count |
| mg_item_active | 적용 중 | mb_id, si_id, ia_type |
| mg_gift | 선물 | mb_id_from, mb_id_to, si_id, gf_status |
| mg_shop_log | 구매 로그 | mb_id, si_id, sl_type |

### 이모티콘
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_emoticon_set | 이모티콘 셋 | es_id, es_name, es_price |
| mg_emoticon | 개별 이모티콘 | em_id, es_id, em_code, em_image |
| mg_emoticon_own | 보유 셋 | mb_id, es_id |

### 출석/게임
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_attendance | 출석 | mb_id, at_date, at_game_type, at_point |
| mg_game_dice | 주사위 설정 | gd_min, gd_max |

### 알림/설정
| 테이블 | 용도 | 핵심 컬럼 |
|--------|------|-----------|
| mg_notification | 알림 | mb_id, noti_type, noti_title, noti_read, noti_url |
| mg_config | 설정값 | cf_key, cf_value |
| mg_write_character | 글-캐릭터 연결 | bo_table, wr_id, ch_id |

### TRPG (미구현)
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

### 상세 기획 문서

| 작업 영역 | 참조 파일 | 비고 |
|----------|----------|------|
| DB 스키마 전체 | `plans/DB.md` | 테이블 상세 정의 |
| API 설계 | `plans/API.md` | REST 엔드포인트 |
| UI/테마 | `plans/UI.md` | Tailwind, 컴포넌트 |
| 회원 시스템 | `plans/MEMBER.md` | 필드 정책, 포인트 |
| 캐릭터 시스템 | `plans/CHARACTER.md` | 승인, TRPG 스탯 |
| 게시판/역극 | `plans/BOARD.md` | 스킨, RP 시스템 |
| 상점/인벤토리 | `plans/SHOP.md` | 상품 타입, 선물 |
| 포인트/출석 | `plans/POINT.md` | 미니게임 |
| 관리자 | `plans/ADMIN.md` | 메뉴 구조, 권한 |
| 개척 시스템 | `plans/PIONEER.md` | 노동력, 시설 건설 |
| 2차 모듈 | `MODULES.md` | SS/VN Engine, 익명망 |

### 작업 로그
| 파일 | 내용 |
|------|------|
| `archive/work_logs/2026-02-03.md` | 캐릭터 시스템 완성 |
| `archive/work_logs/2026-02-04.md` | 상점, 포인트 관리 |
| `archive/work_logs/2026-02-05.md` | 알림 시스템, 토스트 |

---

## 알림 타입 (noti_type)

| 타입 | 발생 시점 |
|------|----------|
| `comment` | 내 글에 댓글 |
| `reply` | 내 댓글에 답글 |
| `like` | 내 글 추천 |
| `rp_reply` | 내 역극에 이음 |
| `character_approved` | 캐릭터 승인 |
| `character_rejected` | 캐릭터 반려 |
| `gift_received` | 선물 수신 |
| `gift_accepted` | 선물 수락됨 |
| `emoticon` | 이모티콘 승인/반려 |
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

---

## 빠른 참조

### mg_notify() 사용법
```php
mg_notify($mb_id, $type, $title, $content, $url);
// 예: mg_notify('user1', 'comment', '새 댓글', '게시글 제목', '/bbs/board.php?...');
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

---

## 개발 환경

### Docker 접근
DB 변경이 필요할 때 Docker를 통해 직접 처리 가능:

```bash
# 컬럼 추가 예시
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db -e "ALTER TABLE mg_character ADD COLUMN ch_image varchar(500) DEFAULT NULL;"

# 쿼리 실행
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db -e "SELECT * FROM mg_config;"

# 테이블 구조 확인
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db -e "DESCRIBE mg_character;"
```

**컨테이너 정보:**
- MySQL 컨테이너: `morgan_mysql`
- DB 사용자: `morgan_user`
- DB 비밀번호: `morgan_pass`
- DB 이름: `morgan_db`

---

## 작업 규칙

- **Git push는 사용자가 요청할 때만** 실행. 커밋은 자유롭게 하되, push는 명시적 요청 시에만.
- 작업 완료 후 work_logs에 기록

---

*이 파일은 작업 효율을 위한 요약본입니다. 상세 내용은 plans/ 폴더의 개별 문서를 참조하세요.*
