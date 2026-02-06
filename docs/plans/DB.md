# 데이터베이스 스키마

> Morgan Edition - 전체 테이블 구조
> 그누보드5 기반 + 커스텀 테이블

> ✅ **이 문서가 최종 스키마의 권위적 출처입니다.**
> 다른 기획 문서의 테이블 정의와 충돌 시 이 문서를 기준으로 합니다.
> 관련: [PLAN_API.md](./PLAN_API.md) (API 엔드포인트)

---

## 개요

- **기반**: 그누보드5 (g5_ 접두사)
- **커스텀**: Morgan Edition 테이블 (mg_ 접두사)
- **엔진**: MySQL 8.x / InnoDB
- **문자셋**: utf8mb4_unicode_ci

---

## 테이블 목록

### 그누보드 유지 테이블 (수정)

| 테이블 | 설명 | 비고 |
|--------|------|------|
| g5_member | 회원 | 필드 축소 |
| g5_point | 포인트 내역 | 유지 |
| g5_board | 게시판 설정 | 유지 |
| g5_write_* | 게시판 글 | 유지 |
| g5_config | 기본 설정 | 유지 + 확장 |

### 커스텀 테이블 (신규)

| 테이블 | 설명 |
|--------|------|
| mg_character | 캐릭터 |
| mg_character_log | 캐릭터 승인 로그 |
| mg_character_ruleset | 캐릭터-룰셋 연결 |
| mg_trpg_ruleset | **TRPG 룰셋 정의** (CoC, D&D, VtM...) |
| mg_trpg_stat_field | **스탯/스킬 필드 정의** (관리자 토글) |
| mg_character_stat_value | **캐릭터별 스탯 값** |
| mg_profile_field | 프로필 양식 (RP용) |
| mg_profile_value | 프로필 값 |
| mg_side | 세력 |
| mg_class | 종족 |
| mg_rp_thread | 역극 |
| mg_rp_reply | 역극 이음 |
| mg_rp_member | 역극 참여자 |
| mg_attendance | 출석 |
| mg_game_dice | 주사위 설정 |
| mg_game_fortune | 운세 데이터 |
| mg_game_lottery_prize | 종이뽑기 등수 |
| mg_game_lottery_board | 종이뽑기 판 설정 |
| mg_game_lottery_user | 종이뽑기 유저 진행 |
| mg_shop_category | 상점 카테고리 |
| mg_shop_item | 상점 상품 |
| mg_shop_log | 구매 로그 |
| mg_inventory | 인벤토리 |
| mg_item_active | 아이템 적용 |
| mg_character_equip | 캐릭터 장착 |
| mg_gift | 선물 |
| mg_staff_auth | 스태프 권한 |
| mg_notification | 알림 |
| mg_config | 커스텀 설정 |
| mg_emoticon_set | 이모티콘 셋 |
| mg_emoticon | 이모티콘 개별 이미지 |
| mg_emoticon_own | 이모티콘 보유 |
| mg_furniture_category | 가구 카테고리 (2차) |
| mg_furniture | 가구 아이템 (2차) |
| mg_furniture_own | 가구 보유 (2차) |
| mg_room | 캐릭터 방 (2차) |

---

## 1. 회원 관련

### 1.1 g5_member (수정)

> 그누보드 기본 테이블에서 불필요 필드 제거

**유지 필드**

| 필드 | 타입 | 설명 |
|------|------|------|
| mb_no | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 아이디 (UNIQUE) |
| mb_password | varchar(255) | 비밀번호 (bcrypt) |
| mb_nick | varchar(255) | 닉네임 |
| mb_nick_date | date | 닉네임 변경일 |
| mb_level | tinyint | 회원 등급 (1~10) |
| mb_point | int | 보유 포인트 |
| mb_datetime | datetime | 가입일 |
| mb_ip | varchar(255) | 가입 IP |
| mb_today_login | datetime | 최근 로그인 |
| mb_login_ip | varchar(255) | 로그인 IP |
| mb_leave_date | varchar(8) | 탈퇴일 |
| mb_intercept_date | varchar(8) | 차단일 |
| mb_memo | text | 관리자 메모 |
| mb_1 ~ mb_10 | varchar(255) | 여분 필드 |

**제거 필드**
- mb_name, mb_email, mb_tel, mb_hp (개인정보)
- mb_birth, mb_sex (불필요)
- mb_zip*, mb_addr* (배송 없음)
- mb_certify, mb_adult, mb_dupinfo (본인인증 없음)
- mb_email_certify* (이메일 인증 없음)
- mb_sms, mb_mailling (발송 없음)
- mb_homepage, mb_signature, mb_profile (사용 안 함)
- mb_open* (전체 공개 고정)
- mb_recommend (추천인 없음)

---

## 2. 캐릭터 관련

### 2.1 mg_character

| 필드 | 타입 | 설명 | 비고 |
|------|------|------|------|
| ch_id | int AUTO_INCREMENT | PK | |
| mb_id | varchar(20) | 소유자 | FK → g5_member |
| ch_name | varchar(100) | 캐릭터 이름 | |
| ch_state | enum('editing','pending','approved','deleted') | 상태 | |
| ch_type | enum('main','sub','npc') | 유형 | |
| ch_main | tinyint(1) | 대표 캐릭터 여부 | 0/1 |
| side_id | int | 세력 | FK → mg_side, NULL 허용 |
| class_id | int | 종족 | FK → mg_class, NULL 허용 |
| ch_thumb | varchar(500) | 썸네일 경로 | |
| ch_datetime | datetime | 등록일 | |
| ch_update | datetime | 수정일 | |

**인덱스**
- PRIMARY KEY (ch_id)
- INDEX idx_mb_id (mb_id)
- INDEX idx_state (ch_state)
- INDEX idx_main (mb_id, ch_main)

### 2.2 mg_character_log

| 필드 | 타입 | 설명 |
|------|------|------|
| log_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID (FK) |
| log_action | enum('submit','approve','reject','edit') | 액션 |
| log_memo | text | 메모 (반려 사유 등) |
| admin_id | varchar(20) | 처리자 ID |
| log_datetime | datetime | 처리일시 |

### 2.3 mg_profile_field

| 필드 | 타입 | 설명 |
|------|------|------|
| pf_id | int AUTO_INCREMENT | PK |
| pf_code | varchar(50) | 항목 코드 (UNIQUE) |
| pf_name | varchar(100) | 표시명 |
| pf_type | enum('text','textarea','select','multiselect','url','image') | 입력 타입 |
| pf_options | text | 선택지 (JSON) |
| pf_placeholder | varchar(200) | 힌트 텍스트 |
| pf_help | text | 도움말 |
| pf_required | tinyint(1) | 필수 여부 |
| pf_order | int | 정렬 순서 |
| pf_category | varchar(50) | 분류/섹션 |
| pf_use | tinyint(1) | 사용 여부 |

### 2.4 mg_profile_value

| 필드 | 타입 | 설명 |
|------|------|------|
| pv_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID (FK) |
| pf_id | int | 프로필 항목 ID (FK) |
| pv_value | text | 입력값 |

**인덱스**
- PRIMARY KEY (pv_id)
- UNIQUE INDEX idx_ch_pf (ch_id, pf_id)

### 2.5 mg_side (세력)

| 필드 | 타입 | 설명 |
|------|------|------|
| side_id | int AUTO_INCREMENT | PK |
| side_name | varchar(100) | 세력명 |
| side_desc | text | 설명 |
| side_image | varchar(500) | 이미지 |
| side_order | int | 정렬 순서 |
| side_use | tinyint(1) | 사용 여부 |

### 2.6 mg_class (종족)

| 필드 | 타입 | 설명 |
|------|------|------|
| class_id | int AUTO_INCREMENT | PK |
| class_name | varchar(100) | 종족명 |
| class_desc | text | 설명 |
| class_image | varchar(500) | 이미지 |
| class_order | int | 정렬 순서 |
| class_use | tinyint(1) | 사용 여부 |

### 2.7 범용 TRPG 스탯 시스템

> 멀티 룰셋 지원 (CoC, D&D, VtM 등)
> 관리자가 사용할 필드 선택 가능

#### 2.7.1 mg_trpg_ruleset (룰셋 정의)

| 필드 | 타입 | 설명 |
|------|------|------|
| ruleset_id | int AUTO_INCREMENT | PK |
| ruleset_code | varchar(20) | 코드 (coc7, dnd5e, vtm5, custom) |
| ruleset_name | varchar(100) | 표시명 (Call of Cthulhu 7판) |
| ruleset_name_en | varchar(100) | 영문명 |
| ruleset_desc | text | 설명 |
| ruleset_version | varchar(20) | 버전 (7th Edition) |
| dice_system | varchar(50) | 기본 다이스 (d100, d20, d10) |
| is_active | tinyint(1) | 활성화 |
| is_default | tinyint(1) | 기본 룰셋 |
| sort_order | int | 정렬 순서 |

**인덱스**
- PRIMARY KEY (ruleset_id)
- UNIQUE INDEX idx_code (ruleset_code)

#### 2.7.2 mg_trpg_stat_field (스탯/스킬 필드 정의)

| 필드 | 타입 | 설명 |
|------|------|------|
| sf_id | int AUTO_INCREMENT | PK |
| ruleset_id | int | 룰셋 ID (FK) |
| sf_code | varchar(50) | 필드 코드 (str, dex, skill_dodge) |
| sf_name | varchar(100) | 표시명 (근력, 회피) |
| sf_name_en | varchar(100) | 영문명 (Strength, Dodge) |
| sf_type | enum | 타입 (아래 참조) |
| sf_category | varchar(50) | 분류 (기본능력치, 파생수치, 스킬_전투...) |
| sf_value_type | enum('number','text','boolean','select') | 값 타입 |
| sf_min | int | 최소값 (NULL=제한없음) |
| sf_max | int | 최대값 (NULL=제한없음) |
| sf_default | varchar(100) | 기본값 |
| sf_formula | varchar(255) | 파생수치 공식 (예: "(CON+SIZ)/10") |
| sf_depends_on | text | 의존 필드 (JSON: ["con","siz"]) |
| sf_dice_roll | varchar(50) | 생성 다이스 (3d6, 2d6+6, 3d6*5) |
| sf_options | text | select용 옵션 (JSON) |
| sf_description | text | 설명/도움말 |
| sf_icon | varchar(100) | 아이콘 (선택) |
| sf_order | int | 정렬 순서 |
| sf_use | tinyint(1) | **관리자 토글** (사용 여부) |
| sf_required | tinyint(1) | 필수 여부 |
| sf_show_in_card | tinyint(1) | 캐릭터 카드에 표시 |

**sf_type 종류**
| 타입 | 설명 | 예시 |
|------|------|------|
| stat | 기본 능력치 | STR, DEX, CON |
| derived | 파생 수치 | HP, AC, Initiative |
| resource | 변동 자원 | HP (현재/최대), MP, SAN |
| skill | 스킬 | 발견, 은신, Athletics |
| save | 세이브 | Fortitude, Reflex (D&D) |
| trait | 특성 | Humanity (VtM) |
| other | 기타 | 메모, 배경 등 |

**인덱스**
- PRIMARY KEY (sf_id)
- INDEX idx_ruleset (ruleset_id)
- UNIQUE INDEX idx_ruleset_code (ruleset_id, sf_code)
- INDEX idx_use (sf_use)

#### 2.7.3 mg_character_stat_value (캐릭터별 스탯 값)

| 필드 | 타입 | 설명 |
|------|------|------|
| csv_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID (FK) |
| sf_id | int | 스탯 필드 ID (FK) |
| csv_value | text | 값 (숫자/텍스트 모두 저장) |
| csv_current | int | resource 타입용: 현재값 |
| csv_max | int | resource 타입용: 최대값 |
| csv_temp_mod | int | 임시 보정치 (버프/디버프) |
| csv_updated | datetime | 수정일 |

**인덱스**
- PRIMARY KEY (csv_id)
- UNIQUE INDEX idx_ch_sf (ch_id, sf_id)
- INDEX idx_ch_id (ch_id)

#### 2.7.4 mg_character_ruleset (캐릭터-룰셋 연결)

> 캐릭터가 어떤 룰셋을 사용하는지 지정

| 필드 | 타입 | 설명 |
|------|------|------|
| cr_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID (FK, UNIQUE) |
| ruleset_id | int | 룰셋 ID (FK) |
| cr_created | datetime | 생성일 |

---

### 2.8 룰셋별 기본 데이터

#### CoC 7판 (Call of Cthulhu)

```sql
-- 룰셋 등록
INSERT INTO mg_trpg_ruleset (ruleset_code, ruleset_name, ruleset_name_en, dice_system, is_active, is_default) VALUES
('coc7', 'Call of Cthulhu 7판', 'Call of Cthulhu 7th Edition', 'd100', 1, 1);

-- 기본 능력치
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_min, sf_max, sf_dice_roll, sf_order, sf_use) VALUES
(1, 'str', '근력', 'Strength', 'stat', '기본능력치', 'number', 1, 99, '3d6*5', 1, 1),
(1, 'con', '체력', 'Constitution', 'stat', '기본능력치', 'number', 1, 99, '3d6*5', 2, 1),
(1, 'siz', '체격', 'Size', 'stat', '기본능력치', 'number', 1, 99, '(2d6+6)*5', 3, 1),
(1, 'dex', '민첩', 'Dexterity', 'stat', '기본능력치', 'number', 1, 99, '3d6*5', 4, 1),
(1, 'app', '외모', 'Appearance', 'stat', '기본능력치', 'number', 1, 99, '3d6*5', 5, 1),
(1, 'int', '지능', 'Intelligence', 'stat', '기본능력치', 'number', 1, 99, '(2d6+6)*5', 6, 1),
(1, 'pow', '정신력', 'Power', 'stat', '기본능력치', 'number', 1, 99, '3d6*5', 7, 1),
(1, 'edu', '교육', 'Education', 'stat', '기본능력치', 'number', 1, 99, '(2d6+6)*5', 8, 1),
(1, 'lck', '행운', 'Luck', 'stat', '기본능력치', 'number', 1, 99, '3d6*5', 9, 1);

-- 파생/자원 수치
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_formula, sf_depends_on, sf_order, sf_use) VALUES
(1, 'hp', '체력', 'Hit Points', 'resource', '파생수치', 'number', 'ROUND((CON+SIZ)/10)', '["con","siz"]', 1, 1),
(1, 'mp', '마력', 'Magic Points', 'resource', '파생수치', 'number', 'ROUND(POW/5)', '["pow"]', 2, 1),
(1, 'san', '정신력', 'Sanity', 'resource', '파생수치', 'number', 'POW', '["pow"]', 3, 1),
(1, 'mov', '이동력', 'Move Rate', 'derived', '파생수치', 'number', 'MOVRATE(STR,DEX,SIZ)', '["str","dex","siz"]', 4, 1),
(1, 'db', '피해보너스', 'Damage Bonus', 'derived', '파생수치', 'text', 'DAMAGEBONUS(STR,SIZ)', '["str","siz"]', 5, 1),
(1, 'build', '체구', 'Build', 'derived', '파생수치', 'number', 'BUILD(STR,SIZ)', '["str","siz"]', 6, 1);

-- 스킬 (일부 예시)
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_default, sf_min, sf_max, sf_order, sf_use) VALUES
(1, 'skill_dodge', '회피', 'Dodge', 'skill', '스킬_전투', 'number', 'DEX/2', 1, 99, 1, 1),
(1, 'skill_fighting', '격투', 'Fighting (Brawl)', 'skill', '스킬_전투', 'number', '25', 1, 99, 2, 1),
(1, 'skill_firearms_handgun', '사격(권총)', 'Firearms (Handgun)', 'skill', '스킬_전투', 'number', '20', 1, 99, 3, 1),
(1, 'skill_spot', '발견', 'Spot Hidden', 'skill', '스킬_탐색', 'number', '25', 1, 99, 1, 1),
(1, 'skill_listen', '청취', 'Listen', 'skill', '스킬_탐색', 'number', '20', 1, 99, 2, 1),
(1, 'skill_library', '도서관이용', 'Library Use', 'skill', '스킬_탐색', 'number', '20', 1, 99, 3, 1),
(1, 'skill_psychology', '심리학', 'Psychology', 'skill', '스킬_사회', 'number', '10', 1, 99, 1, 1),
(1, 'skill_persuade', '설득', 'Persuade', 'skill', '스킬_사회', 'number', '10', 1, 99, 2, 1),
(1, 'skill_occult', '오컬트', 'Occult', 'skill', '스킬_지식', 'number', '5', 1, 99, 1, 1),
(1, 'skill_cthulhu_mythos', '크툴루신화', 'Cthulhu Mythos', 'skill', '스킬_특수', 'number', '0', 0, 99, 1, 1);
```

#### D&D 5e

```sql
-- 룰셋 등록
INSERT INTO mg_trpg_ruleset (ruleset_code, ruleset_name, ruleset_name_en, dice_system, is_active) VALUES
('dnd5e', 'D&D 5판', 'Dungeons & Dragons 5th Edition', 'd20', 1);

-- 기본 능력치
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_min, sf_max, sf_dice_roll, sf_order, sf_use) VALUES
(2, 'str', '근력', 'Strength', 'stat', '능력치', 'number', 1, 30, '4d6k3', 1, 1),
(2, 'dex', '민첩', 'Dexterity', 'stat', '능력치', 'number', 1, 30, '4d6k3', 2, 1),
(2, 'con', '건강', 'Constitution', 'stat', '능력치', 'number', 1, 30, '4d6k3', 3, 1),
(2, 'int', '지능', 'Intelligence', 'stat', '능력치', 'number', 1, 30, '4d6k3', 4, 1),
(2, 'wis', '지혜', 'Wisdom', 'stat', '능력치', 'number', 1, 30, '4d6k3', 5, 1),
(2, 'cha', '매력', 'Charisma', 'stat', '능력치', 'number', 1, 30, '4d6k3', 6, 1);

-- 파생 수치
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_formula, sf_order, sf_use) VALUES
(2, 'hp', '체력', 'Hit Points', 'resource', '전투수치', 'number', NULL, 1, 1),
(2, 'ac', '방어도', 'Armor Class', 'derived', '전투수치', 'number', '10+FLOOR((DEX-10)/2)', 2, 1),
(2, 'initiative', '선제권', 'Initiative', 'derived', '전투수치', 'number', 'FLOOR((DEX-10)/2)', 3, 1),
(2, 'speed', '이동속도', 'Speed', 'derived', '전투수치', 'number', '30', 4, 1),
(2, 'proficiency', '숙련보너스', 'Proficiency Bonus', 'derived', '전투수치', 'number', 'PROFICIENCY(LEVEL)', 5, 1);

-- 세이빙 스로우
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_order, sf_use) VALUES
(2, 'save_str', '근력 세이브', 'Strength Save', 'save', '세이브', 'number', 1, 1),
(2, 'save_dex', '민첩 세이브', 'Dexterity Save', 'save', '세이브', 'number', 2, 1),
(2, 'save_con', '건강 세이브', 'Constitution Save', 'save', '세이브', 'number', 3, 1),
(2, 'save_int', '지능 세이브', 'Intelligence Save', 'save', '세이브', 'number', 4, 1),
(2, 'save_wis', '지혜 세이브', 'Wisdom Save', 'save', '세이브', 'number', 5, 1),
(2, 'save_cha', '매력 세이브', 'Charisma Save', 'save', '세이브', 'number', 6, 1);

-- 스킬 (숙련 표시)
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_order, sf_use) VALUES
(2, 'skill_acrobatics', '곡예', 'Acrobatics', 'skill', '스킬_민첩', 'number', 1, 1),
(2, 'skill_athletics', '운동', 'Athletics', 'skill', '스킬_근력', 'number', 1, 1),
(2, 'skill_perception', '감지', 'Perception', 'skill', '스킬_지혜', 'number', 1, 1),
(2, 'skill_stealth', '은신', 'Stealth', 'skill', '스킬_민첩', 'number', 2, 1),
(2, 'skill_investigation', '조사', 'Investigation', 'skill', '스킬_지능', 'number', 1, 1),
(2, 'skill_arcana', '비전', 'Arcana', 'skill', '스킬_지능', 'number', 2, 1);
```

#### VtM 5판 (Vampire: The Masquerade)

```sql
-- 룰셋 등록
INSERT INTO mg_trpg_ruleset (ruleset_code, ruleset_name, ruleset_name_en, dice_system, is_active) VALUES
('vtm5', '뱀파이어: 더 마스커레이드 5판', 'Vampire: The Masquerade 5th Edition', 'd10', 1);

-- Attributes (Physical)
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_min, sf_max, sf_order, sf_use) VALUES
(3, 'strength', '근력', 'Strength', 'stat', '육체', 'number', 1, 5, 1, 1),
(3, 'dexterity', '민첩', 'Dexterity', 'stat', '육체', 'number', 1, 5, 2, 1),
(3, 'stamina', '체력', 'Stamina', 'stat', '육체', 'number', 1, 5, 3, 1);

-- Attributes (Social)
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_min, sf_max, sf_order, sf_use) VALUES
(3, 'charisma', '카리스마', 'Charisma', 'stat', '사회', 'number', 1, 5, 1, 1),
(3, 'manipulation', '조종', 'Manipulation', 'stat', '사회', 'number', 1, 5, 2, 1),
(3, 'composure', '침착', 'Composure', 'stat', '사회', 'number', 1, 5, 3, 1);

-- Attributes (Mental)
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_min, sf_max, sf_order, sf_use) VALUES
(3, 'intelligence', '지능', 'Intelligence', 'stat', '정신', 'number', 1, 5, 1, 1),
(3, 'wits', '재치', 'Wits', 'stat', '정신', 'number', 1, 5, 2, 1),
(3, 'resolve', '결의', 'Resolve', 'stat', '정신', 'number', 1, 5, 3, 1);

-- Vampire 특수 수치
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_min, sf_max, sf_order, sf_use) VALUES
(3, 'health', '체력', 'Health', 'resource', '상태', 'number', 0, 10, 1, 1),
(3, 'willpower', '의지력', 'Willpower', 'resource', '상태', 'number', 0, 10, 2, 1),
(3, 'hunger', '허기', 'Hunger', 'resource', '상태', 'number', 0, 5, 3, 1),
(3, 'humanity', '인간성', 'Humanity', 'trait', '상태', 'number', 0, 10, 4, 1),
(3, 'blood_potency', '혈위', 'Blood Potency', 'trait', '상태', 'number', 0, 10, 5, 1);

-- Skills (일부)
INSERT INTO mg_trpg_stat_field (ruleset_id, sf_code, sf_name, sf_name_en, sf_type, sf_category, sf_value_type, sf_min, sf_max, sf_order, sf_use) VALUES
(3, 'skill_athletics', '운동', 'Athletics', 'skill', '스킬_육체', 'number', 0, 5, 1, 1),
(3, 'skill_brawl', '격투', 'Brawl', 'skill', '스킬_육체', 'number', 0, 5, 2, 1),
(3, 'skill_stealth', '은신', 'Stealth', 'skill', '스킬_육체', 'number', 0, 5, 3, 1),
(3, 'skill_intimidation', '위협', 'Intimidation', 'skill', '스킬_사회', 'number', 0, 5, 1, 1),
(3, 'skill_persuasion', '설득', 'Persuasion', 'skill', '스킬_사회', 'number', 0, 5, 2, 1),
(3, 'skill_occult', '오컬트', 'Occult', 'skill', '스킬_정신', 'number', 0, 5, 1, 1);
```

---

### 2.9 관리자 기능: 필드 활성화

> 관리자가 sf_use 토글로 사용할 필드만 선택

**관리 화면 예시**
```
┌────────────────────────────────────────────────────────────┐
│ TRPG 스탯 설정 - Call of Cthulhu 7판                       │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  [기본능력치]                                              │
│  ☑ STR (근력)     ☑ CON (체력)     ☑ SIZ (체격)          │
│  ☑ DEX (민첩)     ☑ APP (외모)     ☑ INT (지능)          │
│  ☑ POW (정신력)   ☑ EDU (교육)     ☑ LCK (행운)          │
│                                                            │
│  [파생수치]                                                │
│  ☑ HP (체력)      ☑ MP (마력)      ☑ SAN (정신력)        │
│  ☐ MOV (이동력)   ☐ DB (피해보너스) ☐ Build (체구)        │
│                                                            │
│  [스킬_전투]                                               │
│  ☑ 회피           ☑ 격투           ☐ 사격(권총)          │
│  ☐ 사격(소총)     ☐ 투척                                  │
│                                                            │
│  [스킬_탐색]                                               │
│  ☑ 발견           ☑ 청취           ☑ 도서관이용          │
│  ...                                                       │
│                                                            │
│  [전체 선택] [전체 해제] [기본값으로 복원]     [저장]       │
└────────────────────────────────────────────────────────────┘
```

**PHP 예시**
```php
// 활성화된 필드만 가져오기
function get_active_stat_fields($ruleset_id) {
    global $g5;

    $sql = "SELECT * FROM {$g5['mg_trpg_stat_field_table']}
            WHERE ruleset_id = {$ruleset_id}
            AND sf_use = 1
            ORDER BY sf_category, sf_order";

    return sql_fetch_all($sql);
}

// 필드 활성화 토글
function toggle_stat_field($sf_id, $use) {
    global $g5;

    $use = $use ? 1 : 0;
    sql_query("UPDATE {$g5['mg_trpg_stat_field_table']}
               SET sf_use = {$use}
               WHERE sf_id = {$sf_id}");
}

// 캐릭터 시트 렌더링
function render_character_sheet($ch_id) {
    $char = mg_get_character($ch_id);
    $ruleset_id = get_character_ruleset($ch_id);
    $fields = get_active_stat_fields($ruleset_id);
    $values = get_character_stat_values($ch_id);

    // 카테고리별 그룹핑
    $grouped = [];
    foreach ($fields as $field) {
        $grouped[$field['sf_category']][] = $field;
    }

    // 렌더링...
}
```

---

## 3. 역극 관련

### 3.1 mg_rp_thread

| 필드 | 타입 | 설명 |
|------|------|------|
| rt_id | int AUTO_INCREMENT | PK |
| rt_title | varchar(200) | 제목 |
| rt_content | text | 시작글 |
| rt_image | varchar(500) | 첨부 이미지 |
| mb_id | varchar(20) | 판장 회원 ID |
| ch_id | int | 판장 캐릭터 ID |
| rt_max_member | int | 최대 참여자 (0=무제한) |
| rt_status | enum('open','closed','deleted') | 상태 |
| rt_reply_count | int | 이음 수 |
| rt_datetime | datetime | 생성일 |
| rt_update | datetime | 최근 활동일 |

**인덱스**
- PRIMARY KEY (rt_id)
- INDEX idx_status (rt_status)
- INDEX idx_update (rt_update)

### 3.2 mg_rp_reply

| 필드 | 타입 | 설명 |
|------|------|------|
| rr_id | int AUTO_INCREMENT | PK |
| rt_id | int | 역극 ID (FK) |
| rr_content | text | 내용 |
| rr_image | varchar(500) | 첨부 이미지 |
| mb_id | varchar(20) | 작성자 회원 ID |
| ch_id | int | 작성 캐릭터 ID |
| rr_datetime | datetime | 작성일 |

**인덱스**
- PRIMARY KEY (rr_id)
- INDEX idx_rt_id (rt_id)

### 3.3 mg_rp_member

| 필드 | 타입 | 설명 |
|------|------|------|
| rm_id | int AUTO_INCREMENT | PK |
| rt_id | int | 역극 ID |
| mb_id | varchar(20) | 참여자 회원 ID |
| ch_id | int | 참여 캐릭터 ID |
| rm_reply_count | int | 이음 횟수 |
| rm_datetime | datetime | 참여 시작일 |

**인덱스**
- PRIMARY KEY (rm_id)
- UNIQUE INDEX idx_rt_mb (rt_id, mb_id)

---

## 4. 포인트/출석 관련

### 4.1 g5_point (그누보드 유지)

| 필드 | 타입 | 설명 |
|------|------|------|
| po_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| po_datetime | datetime | 일시 |
| po_content | varchar(255) | 내용 |
| po_point | int | 포인트 (+/-) |
| po_use_point | int | 사용 포인트 |
| po_expired | tinyint(1) | 만료 여부 |
| po_expire_date | date | 만료일 |
| po_rel_table | varchar(50) | 관련 테이블 |
| po_rel_id | varchar(50) | 관련 ID |
| po_rel_action | varchar(50) | 관련 액션 |

### 4.2 mg_attendance

| 필드 | 타입 | 설명 |
|------|------|------|
| at_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| at_date | date | 출석 날짜 |
| at_datetime | datetime | 출석 시간 |
| at_game_type | varchar(20) | 미니게임 종류 |
| at_result | varchar(100) | 결과 데이터 |
| at_point | int | 획득 포인트 |

**인덱스**
- PRIMARY KEY (at_id)
- UNIQUE INDEX idx_mb_date (mb_id, at_date)

### 4.3 mg_game_dice

| 필드 | 타입 | 설명 |
|------|------|------|
| gd_id | int | PK (1 고정) |
| gd_min | int | 최소 포인트 |
| gd_max | int | 최대 포인트 |
| gd_use | tinyint(1) | 사용 여부 |

### 4.4 mg_game_fortune

| 필드 | 타입 | 설명 |
|------|------|------|
| gf_id | int AUTO_INCREMENT | PK |
| gf_star | tinyint | 별 개수 (1~5) |
| gf_text | varchar(255) | 운세 텍스트 |
| gf_point | int | 획득 포인트 |
| gf_use | tinyint(1) | 사용 여부 |

### 4.5 mg_game_lottery_prize

| 필드 | 타입 | 설명 |
|------|------|------|
| glp_id | int AUTO_INCREMENT | PK |
| glp_rank | tinyint | 등수 (1~5) |
| glp_name | varchar(50) | 상 이름 |
| glp_count | int | 개수 |
| glp_point | int | 포인트 보상 |
| glp_item_id | int | 상품 ID (FK, nullable) |
| glp_use | tinyint(1) | 사용 여부 |

### 4.6 mg_game_lottery_board

| 필드 | 타입 | 설명 |
|------|------|------|
| glb_id | int | PK (1 고정) |
| glb_size | int | 판 크기 (50, 100 등) |
| glb_bonus_point | int | 완성 보너스 포인트 |
| glb_bonus_item_id | int | 완성 보너스 아이템 (nullable) |
| glb_use | tinyint(1) | 사용 여부 |

### 4.7 mg_game_lottery_user

| 필드 | 타입 | 설명 |
|------|------|------|
| glu_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| glb_id | int | 현재 진행 중인 판 ID (FK) |
| glu_picked | text | 뽑은 번호들 (JSON) |
| glu_count | int | 뽑은 개수 |
| glu_completed_count | int | 완료한 판 수 |

**인덱스**
- PRIMARY KEY (glu_id)
- UNIQUE INDEX idx_mb_id (mb_id)

---

## 5. 상점 관련

### 5.1 mg_shop_category

| 필드 | 타입 | 설명 |
|------|------|------|
| sc_id | int AUTO_INCREMENT | PK |
| sc_name | varchar(50) | 카테고리명 |
| sc_desc | varchar(200) | 설명 |
| sc_icon | varchar(100) | 아이콘 |
| sc_order | int | 정렬 순서 |
| sc_use | tinyint(1) | 사용 여부 |

### 5.2 mg_shop_item

| 필드 | 타입 | 설명 |
|------|------|------|
| si_id | int AUTO_INCREMENT | PK |
| sc_id | int | 카테고리 ID (FK) |
| si_name | varchar(100) | 상품명 |
| si_desc | text | 설명 |
| si_image | varchar(500) | 이미지 |
| si_price | int | 가격 |
| si_type | enum('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','furniture','etc') | 타입 |
| si_effect | text | 효과 데이터 (JSON) |
| si_stock | int | 재고 (-1=무제한) |
| si_stock_sold | int | 판매 수량 |
| si_limit_per_user | int | 1인당 제한 (0=무제한) |
| si_sale_start | datetime | 판매 시작일 |
| si_sale_end | datetime | 판매 종료일 |
| si_consumable | tinyint(1) | 소모품 여부 |
| si_display | tinyint(1) | 노출 여부 |
| si_use | tinyint(1) | 사용 가능 여부 |
| si_order | int | 정렬 순서 |
| si_datetime | datetime | 등록일 |

**인덱스**
- PRIMARY KEY (si_id)
- INDEX idx_category (sc_id)
- INDEX idx_display (si_display, si_use)

### 5.3 mg_shop_log

| 필드 | 타입 | 설명 |
|------|------|------|
| sl_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 구매자 |
| si_id | int | 상품 ID |
| sl_price | int | 구매 가격 |
| sl_type | enum('purchase','gift_send','gift_receive') | 유형 |
| sl_datetime | datetime | 일시 |

### 5.4 mg_inventory

| 필드 | 타입 | 설명 |
|------|------|------|
| iv_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| si_id | int | 상품 ID (FK) |
| iv_count | int | 보유 수량 |
| iv_datetime | datetime | 획득일 |

**인덱스**
- PRIMARY KEY (iv_id)
- UNIQUE INDEX idx_mb_si (mb_id, si_id)

### 5.5 mg_item_active

| 필드 | 타입 | 설명 |
|------|------|------|
| ia_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| si_id | int | 상품 ID |
| ia_type | varchar(20) | 적용 타입 |
| ch_id | int | 캐릭터 ID (캐릭터별 적용 시, nullable) |
| ia_datetime | datetime | 적용일 |

**인덱스**
- PRIMARY KEY (ia_id)
- INDEX idx_mb_type (mb_id, ia_type)

### 5.6 mg_character_equip

| 필드 | 타입 | 설명 |
|------|------|------|
| ce_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID |
| si_id | int | 상품 ID |
| ce_equipped | tinyint(1) | 장착 여부 |
| ce_datetime | datetime | 장착일 |

### 5.7 mg_gift

| 필드 | 타입 | 설명 |
|------|------|------|
| gf_id | int AUTO_INCREMENT | PK |
| mb_id_from | varchar(20) | 보내는 사람 |
| mb_id_to | varchar(20) | 받는 사람 |
| si_id | int | 상품 ID |
| gf_message | varchar(200) | 메시지 |
| gf_status | enum('pending','accepted','rejected') | 상태 |
| gf_datetime | datetime | 선물 일시 |

**인덱스**
- PRIMARY KEY (gf_id)
- INDEX idx_to_status (mb_id_to, gf_status)

---

## 6. 마이룸 관련 (2차 구현, DB만 1차)

### 6.1 mg_furniture_category (가구 카테고리)

| 필드 | 타입 | 설명 |
|------|------|------|
| fc_id | int AUTO_INCREMENT | PK |
| fc_name | varchar(50) | 카테고리명 (침대, 책상, 조명 등) |
| fc_order | int | 정렬 순서 |
| fc_use | tinyint(1) | 사용 여부 |

### 6.2 mg_furniture (가구 아이템)

| 필드 | 타입 | 설명 |
|------|------|------|
| fn_id | int AUTO_INCREMENT | PK |
| fc_id | int | 카테고리 ID (FK) |
| si_id | int | 상점 아이템 ID (FK, nullable) |
| fn_name | varchar(100) | 가구 이름 |
| fn_image | varchar(500) | 아이소메트릭 이미지 (PNG) |
| fn_width | tinyint | 그리드 가로 칸 (1~4) |
| fn_height | tinyint | 그리드 세로 칸 (1~4) |
| fn_z_index | int | 기본 레이어 순서 |
| fn_is_floor | tinyint(1) | 바닥 아이템 여부 |
| fn_is_wall | tinyint(1) | 벽 아이템 여부 |
| fn_price | int | 가격 (상점 연동 안 할 경우) |
| fn_use | tinyint(1) | 사용 여부 |
| fn_datetime | datetime | 등록일 |

### 6.3 mg_room (캐릭터 방)

| 필드 | 타입 | 설명 |
|------|------|------|
| room_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID (FK, UNIQUE) |
| room_size | tinyint | 방 크기 (1=8x8, 2=10x10, 3=12x12) |
| room_wallpaper | varchar(500) | 벽지 이미지 |
| room_floor | varchar(500) | 바닥 이미지 |
| room_data | text | 가구 배치 데이터 (JSON) |
| room_datetime | datetime | 생성일 |
| room_update | datetime | 수정일 |

### 6.4 room_data JSON 구조

```json
{
  "furniture": [
    {
      "fn_id": 1,
      "x": 3,
      "y": 2,
      "rotation": 0
    },
    {
      "fn_id": 5,
      "x": 5,
      "y": 4,
      "rotation": 90
    }
  ]
}
```

### 6.5 mg_furniture_own (가구 보유)

| 필드 | 타입 | 설명 |
|------|------|------|
| fo_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| fn_id | int | 가구 ID (FK) |
| fo_count | int | 보유 수량 |
| fo_datetime | datetime | 획득일 |

**인덱스**
- PRIMARY KEY (fo_id)
- UNIQUE INDEX idx_mb_fn (mb_id, fn_id)

---

## 7. 이모티콘 관련

### 7.1 mg_emoticon_set (이모티콘 셋)

| 필드 | 타입 | 설명 |
|------|------|------|
| es_id | int AUTO_INCREMENT | PK |
| es_name | varchar(100) | 셋 이름 |
| es_desc | text | 설명 |
| es_preview | varchar(500) | 미리보기 이미지 |
| es_price | int | 가격 (포인트) |
| es_order | int | 정렬 순서 |
| es_use | tinyint(1) | 사용 여부 |
| es_datetime | datetime | 등록일 |

### 7.2 mg_emoticon (이모티콘)

| 필드 | 타입 | 설명 |
|------|------|------|
| em_id | int AUTO_INCREMENT | PK |
| es_id | int | 셋 ID (FK) |
| em_code | varchar(50) | 이모티콘 코드 (예: :smile:) |
| em_image | varchar(500) | 이미지 경로 |
| em_order | int | 정렬 순서 |

**인덱스**
- PRIMARY KEY (em_id)
- INDEX idx_es_id (es_id)
- UNIQUE INDEX idx_code (em_code)

### 7.3 mg_emoticon_own (이모티콘 보유)

| 필드 | 타입 | 설명 |
|------|------|------|
| eo_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 회원 ID |
| es_id | int | 셋 ID (FK) |
| eo_datetime | datetime | 구매일 |

**인덱스**
- PRIMARY KEY (eo_id)
- UNIQUE INDEX idx_mb_es (mb_id, es_id)

---

## 8. 파일 관련

### 8.1 g5_board_file (그누보드 유지)

> 게시판 첨부파일은 그누보드 기본 테이블 사용

### 8.2 mg_file (커스텀 파일 관리)

> 역극, 캐릭터 등 게시판 외 파일 통합 관리

| 필드 | 타입 | 설명 |
|------|------|------|
| file_id | int AUTO_INCREMENT | PK |
| file_table | varchar(30) | 관련 테이블 (rp_thread, rp_reply, character 등) |
| file_table_id | int | 관련 테이블 PK |
| file_name | varchar(255) | 원본 파일명 |
| file_path | varchar(500) | 저장 경로 |
| file_ext | varchar(10) | 확장자 |
| file_size | int | 파일 크기 (bytes) |
| file_width | int | 이미지 너비 (nullable) |
| file_height | int | 이미지 높이 (nullable) |
| file_datetime | datetime | 업로드 일시 |

**인덱스**
- PRIMARY KEY (file_id)
- INDEX idx_table (file_table, file_table_id)

---

## 9. 관리/시스템 관련

### 9.1 mg_staff_auth

| 필드 | 타입 | 설명 |
|------|------|------|
| sa_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 스태프 회원 ID (UNIQUE) |
| sa_permissions | text | 권한 목록 (JSON) |
| sa_datetime | datetime | 등록일 |

### 9.2 mg_notification

| 필드 | 타입 | 설명 |
|------|------|------|
| noti_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 수신자 |
| noti_type | varchar(30) | 알림 종류 |
| noti_title | varchar(200) | 제목 |
| noti_content | text | 내용 |
| noti_link | varchar(500) | 연결 URL |
| noti_read | tinyint(1) | 읽음 여부 |
| noti_datetime | datetime | 생성일 |

**인덱스**
- PRIMARY KEY (noti_id)
- INDEX idx_mb_read (mb_id, noti_read)
- INDEX idx_datetime (noti_datetime)

**알림 종류 (noti_type)**
- character_approved: 캐릭터 승인
- character_rejected: 캐릭터 반려
- comment: 댓글 알림
- rp_reply: 역극 이음
- gift_received: 선물 수신
- point_received: 포인트 지급
- system: 시스템 알림

### 9.3 mg_config

| 필드 | 타입 | 설명 |
|------|------|------|
| cf_id | int AUTO_INCREMENT | PK |
| cf_key | varchar(50) | 설정 키 (UNIQUE) |
| cf_value | text | 설정 값 |
| cf_desc | varchar(200) | 설명 |

**기본 설정 키**

```
# 사이트 기본
site_name           # 사이트명
site_logo           # 로고 이미지
site_favicon        # 파비콘
site_description    # 메타 설명

# 테마
theme               # 현재 테마 폴더명 (기본: default)

# 디자인 (테마 내 커스텀용)
design_main_color   # 메인 컬러 (테마 변수 오버라이드)
design_default_image # 기본 이미지

# 회원
member_captcha_use  # CAPTCHA 사용
member_nick_change_days # 닉네임 변경 주기

# 캐릭터
char_use_side       # 세력 사용
char_side_title     # 세력 명칭
char_side_required  # 세력 필수
char_use_class      # 종족 사용
char_class_title    # 종족 명칭
char_class_required # 종족 필수
char_image_upload   # 이미지 업로드 허용
char_image_url      # 외부 URL 허용
char_image_max_size # 최대 파일 크기

# 역극
rp_require_reply    # 판 세우기 전 필요 이음 수
rp_max_member_default # 기본 최대 참여자
rp_max_member_limit # 참여자 상한선
rp_content_min      # 최소 글자 수

# 포인트
point_name          # 재화 명칭
point_unit          # 재화 단위
attendance_game     # 출석 미니게임 종류

# 상점
shop_use            # 상점 사용 여부

# SS Engine 세션 보상
session_participate_point  # 세션 참여 보상 (기본 500)
session_complete_point     # 세션 완주 보상 (기본 1000)
session_long_bonus         # 장시간(2h+) 보너스 (기본 500)
session_gm_point           # GM 진행 보상 (기본 2000)

# CoC 스탯 포인트 비용
stat_upgrade_cost          # 스탯 +1 비용 (기본 500)
luck_reroll_cost           # 행운 재굴림 비용 (기본 200)
san_recovery_cost          # SAN 5회복 비용 (기본 100)
hp_full_recovery_cost      # HP 완전회복 비용 (기본 300)
```

---

## 10. 결정 사항 (확정)

| 항목 | 결정 | 비고 |
|------|------|------|
| 캐릭터 경험치 | **폐지** - ch_exp 필드 제거 | 포인트 경제로 단일화 |
| TRPG 스탯 | **범용 시스템** 채택 | CoC, D&D, VtM 등 멀티 룰셋 지원 |
| 스탯 테이블 | mg_trpg_ruleset + stat_field + stat_value | EAV 패턴 변형 |
| 관리자 설정 | sf_use 토글 | 룰셋 기본 필드 중 사용할 것만 선택 |
| profile_field | RP 프로필 전용 | 스탯/스킬은 TRPG 시스템으로 분리 |
| 이모티콘 | **포함** | 관리자가 셋 등록, 유저가 구매하여 사용 |
| 게시판 포인트 | g5_board 확장 | 그누보드 기본 구조 활용 |
| 파일 관리 | g5_board_file + mg_file | 게시판은 기존, 나머지는 통합 |
| 금지어 필터 | g5_config 활용 | 그누보드 기본 방식 |
| 마이룸 | DB 구조 1차, UI/기능 2차 | 아이소메트릭 2D 스프라이트 방식 |
| 캐입 익명 게시판 | 별도 패키지 | 애드온으로 분리 |
| TRPG 세션 툴 | 별도 패키지 | 코코폴리아 스타일, Supabase 연동 |

---

## 11. g5_board 확장 필드

> 게시판별 포인트 설정 (그누보드 방식 확장)

| 추가 필드 | 타입 | 설명 |
|----------|------|------|
| bo_point_write | int | 글 작성 포인트 |
| bo_point_comment | int | 댓글 작성 포인트 |
| bo_point_limit | int | 일일 포인트 획득 제한 (0=무제한) |

---

## 12. ER 다이어그램 (관계)

```
g5_member (1) ─────┬───── (N) mg_character
                   │              │
                   │              ├── (1) mg_character_ruleset ── (1) mg_trpg_ruleset
                   │              │                                       │
                   │              │                                       └── (N) mg_trpg_stat_field
                   │              │                                               (관리자 sf_use 토글)
                   │              │
                   │              ├── (N) mg_character_stat_value ── (1) mg_trpg_stat_field
                   │              │       (캐릭터별 스탯/스킬 값)
                   │              │
                   │              ├── (N) mg_profile_value ── (1) mg_profile_field
                   │              │       (RP용 프로필: 성격, 배경 등)
                   │              │
                   │              ├── (N) mg_character_log
                   │              │
                   │              └── (N) mg_character_equip ── (1) mg_shop_item
                   │
                   ├───── (N) mg_rp_thread
                   │              │
                   │              ├── (N) mg_rp_reply
                   │              │
                   │              └── (N) mg_rp_member
                   │
                   ├───── (N) g5_point
                   │
                   ├───── (1) mg_attendance (per day)
                   │
                   ├───── (1) mg_game_lottery_user
                   │
                   ├───── (N) mg_inventory ── (1) mg_shop_item
                   │
                   ├───── (N) mg_item_active
                   │
                   ├───── (N) mg_gift (from/to)
                   │
                   ├───── (N) mg_notification
                   │
                   └───── (1) mg_staff_auth


mg_side (1) ────── (N) mg_character
mg_class (1) ───── (N) mg_character

mg_shop_category (1) ── (N) mg_shop_item ── (N) mg_shop_log

mg_emoticon_set (1) ── (N) mg_emoticon
                │
                └── (N) mg_emoticon_own ── (1) g5_member

mg_furniture_category (1) ── (N) mg_furniture
                                    │
                                    ├── (N) mg_furniture_own ── (1) g5_member
                                    │
                                    └── (N) mg_room.room_data (JSON 참조)

mg_character (1) ── (1) mg_room
```

---

## 13. TODO

- [x] 불명확 사항 결정
- [ ] SQL 생성 스크립트 작성
- [ ] 샘플 데이터 스크립트 작성
- [ ] 마이그레이션 스크립트 (그누보드 기본 → 수정)

---

*작성일: 2026-02-03*
*수정일: 2026-02-03*
*상태: 스키마 확정, 문서 검토 완료*
