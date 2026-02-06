# 캐릭터 시스템 기획

> Morgan Edition - 캐릭터 시스템 설계
> 참고: vn_engine(아보카도) 캐릭터 시스템

> ⚠️ **스키마 참조**: 최종 테이블 구조는 [PLAN_DB.md](./PLAN_DB.md) 참조
> 이 문서의 테이블/필드명은 설계 참고용이며, 실제 구현 시 PLAN_DB.md 기준
> (예: ch_side → side_id, side.si_id → side.side_id, class.cl_id → class.class_id)

---

## 개요

- **목표**: 1계정 다캐릭터 구조, 가변적 프로필 항목
- **핵심**: 관리자가 프로필 양식을 자유롭게 커스터마이징
- **UI**: Tailwind CSS 기반 모던 디자인

---

## 1. 캐릭터 기본 구조

### 1.1 캐릭터 테이블 (character)

| 필드 | 타입 | 설명 |
|------|------|------|
| ch_id | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) | 오너(소유자) 회원 ID (FK → member) |
| ch_name | varchar(100) | 캐릭터 이름 |
| ch_state | enum | 상태 (editing/pending/approved/deleted) |
| ch_type | enum | 유형 (main/sub/npc) |
| ch_side | int | 세력 ID (FK → side, nullable) |
| ch_class | int | 종족 ID (FK → class, nullable) |
| ch_thumb | varchar(500) | 대표 이미지 (썸네일) |
| ch_main | tinyint(1) | 대표 캐릭터 여부 |
| ch_datetime | datetime | 등록일 |
| ch_update | datetime | 수정일 |

### 1.2 캐릭터 상태 (ch_state)

| 상태 | 설명 | 권한 |
|------|------|------|
| editing | 수정중 | 유저가 작성/수정 중 |
| pending | 대기 | 승인 대기 상태 |
| approved | 승인 | 활동 가능 |
| deleted | 삭제 | 소프트 삭제 |

### 1.3 캐릭터 유형 (ch_type)

| 유형 | 설명 |
|------|------|
| main | 메인 캐릭터 (계정당 1개 권장) |
| sub | 서브 캐릭터 |
| npc | NPC (세계관 공용 캐릭터) |

---

## 2. 세력/종족 시스템

### 2.1 세력 테이블 (side)

| 필드 | 타입 | 설명 |
|------|------|------|
| si_id | int AUTO_INCREMENT | PK |
| si_name | varchar(100) | 세력명 |
| si_description | text | 세력 설명 |
| si_image | varchar(500) | 세력 이미지 (선택) |
| si_order | int | 정렬 순서 |
| si_use | tinyint | 사용 여부 |

### 2.2 종족 테이블 (class)

| 필드 | 타입 | 설명 |
|------|------|------|
| cl_id | int AUTO_INCREMENT | PK |
| cl_name | varchar(100) | 종족명 |
| cl_description | text | 종족 설명 |
| cl_image | varchar(500) | 종족 이미지 (선택) |
| cl_order | int | 정렬 순서 |
| cl_use | tinyint | 사용 여부 |

### 2.3 세력/종족 설정 (관리자)

| 설정 | 설명 |
|------|------|
| cf_use_side | 세력 시스템 사용 여부 |
| cf_side_title | 세력 명칭 (예: "소속", "진영", "국가") |
| cf_side_required | 세력 선택 필수 여부 |
| cf_use_class | 종족 시스템 사용 여부 |
| cf_class_title | 종족 명칭 (예: "종족", "클래스", "직업") |
| cf_class_required | 종족 선택 필수 여부 |

---

## 3. 가변 프로필 시스템 (핵심)

### 3.1 프로필 항목 정의 테이블 (profile_field)

| 필드 | 타입 | 설명 |
|------|------|------|
| pf_id | int AUTO_INCREMENT | PK |
| pf_code | varchar(50) | 항목 코드 (고유키) |
| pf_name | varchar(100) | 항목명 (표시용) |
| pf_type | enum | 입력 타입 |
| pf_options | text | 선택지 (select용, JSON) |
| pf_placeholder | varchar(200) | 입력 힌트 |
| pf_help | text | 도움말 |
| pf_required | tinyint | 필수 여부 |
| pf_order | int | 정렬 순서 |
| pf_category | varchar(50) | 분류/섹션 (선택) |
| pf_use | tinyint | 사용 여부 |

### 3.2 프로필 항목 타입 (pf_type)

| 타입 | 설명 | 용도 |
|------|------|------|
| text | 단일 텍스트 | 이름, 나이, 키 등 |
| textarea | 여러 줄 텍스트 | 성격, 배경 스토리 등 |
| select | 단일 선택 | 성별, 혈액형 등 |
| multiselect | 다중 선택 | 취미, 특기 등 |
| url | 외부 링크 | 이미지 URL, SNS 등 |
| image | 이미지 업로드 | 캐릭터 이미지 |

### 3.3 프로필 값 테이블 (profile_value)

| 필드 | 타입 | 설명 |
|------|------|------|
| pv_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID (FK) |
| pf_id | int | 프로필 항목 ID (FK) |
| pv_value | text | 입력값 |

### 3.4 프로필 항목 예시 (샘플 데이터)

```json
[
  { "code": "name_kr", "name": "한글 이름", "type": "text", "required": true },
  { "code": "name_en", "name": "영문 이름", "type": "text", "required": false },
  { "code": "age", "name": "나이", "type": "text", "required": false },
  { "code": "gender", "name": "성별", "type": "select", "options": ["남성", "여성", "기타"], "required": true },
  { "code": "personality", "name": "성격", "type": "textarea", "required": false },
  { "code": "background", "name": "배경 스토리", "type": "textarea", "required": false },
  { "code": "image_head", "name": "두상 이미지", "type": "image", "required": false },
  { "code": "image_body", "name": "전신 이미지", "type": "image", "required": false }
]
```

---

## 4. 이미지 시스템

### 4.1 이미지 설정 (관리자)

| 설정 | 설명 |
|------|------|
| cf_char_image_upload | 직접 업로드 허용 |
| cf_char_image_url | 외부 URL 허용 |
| cf_char_image_max_size | 최대 파일 크기 |
| cf_char_image_types | 허용 확장자 (jpg, png, gif, webp) |

### 4.2 이미지 저장 경로

```
/data/character/{mb_id}/{ch_id}/
├── thumb.jpg      (썸네일)
├── head.jpg       (두상)
├── body.jpg       (전신)
└── extra_*.jpg    (추가 이미지)
```

---

## 5. 승인 시스템

### 5.1 워크플로우

```
[유저 작성] → editing
     ↓
[신청 제출] → pending
     ↓
[관리자/스태프 검토]
     ↓
[승인] → approved  또는  [반려] → editing (사유 전달)
```

### 5.2 승인 로그 테이블 (character_log)

| 필드 | 타입 | 설명 |
|------|------|------|
| cl_id | int AUTO_INCREMENT | PK |
| ch_id | int | 캐릭터 ID |
| cl_action | varchar(20) | 액션 (submit/approve/reject/edit) |
| cl_memo | text | 메모 (반려 사유 등) |
| cl_admin_id | varchar(20) | 처리자 ID |
| cl_datetime | datetime | 처리 일시 |

---

## 6. 페이지 구성

### 6.1 유저 페이지

| 페이지 | 경로 | 설명 |
|------|------|------|
| 내 캐릭터 목록 | /character/my | 본인 캐릭터 관리 |
| 캐릭터 등록 | /character/create | 새 캐릭터 신청 |
| 캐릭터 수정 | /character/edit/{id} | 캐릭터 정보 수정 |
| 캐릭터 프로필 | /character/view/{id} | 캐릭터 상세 보기 |
| 캐릭터 목록 | /character/list | 전체 승인된 캐릭터 |

### 6.2 관리자 페이지

| 페이지 | 경로 | 설명 |
|------|------|------|
| 캐릭터 관리 | /adm/character | 전체 캐릭터 목록/검색 |
| 승인 대기 | /adm/character/pending | 승인 대기 목록 |
| 프로필 양식 관리 | /adm/profile-field | 프로필 항목 설정 |
| 세력 관리 | /adm/side | 세력 추가/수정/삭제 |
| 종족 관리 | /adm/class | 종족 추가/수정/삭제 |

---

## 7. UI/UX 방향

### 7.1 디자인 원칙
- Tailwind CSS 기반
- 디스코드 스타일 다크 테마
- 카드형 레이아웃 (캐릭터 목록)
- 모달 활용 (빠른 미리보기)

### 7.2 캐릭터 카드 컴포넌트

```
┌─────────────────────────┐
│  [썸네일 이미지]         │
├─────────────────────────┤
│  캐릭터 이름             │
│  세력 | 종족             │
│  @오너닉네임             │
├─────────────────────────┤
│  [승인] [main]          │
└─────────────────────────┘
```

### 7.3 프로필 폼 (등록/수정)

```
┌─────────────────────────────────────┐
│  캐릭터 등록                         │
├─────────────────────────────────────┤
│  ┌─────────┐                        │
│  │ 섹션 1  │ 기본 정보              │
│  ├─────────┤                        │
│  │ 이름    │ [________________]     │
│  │ 성별    │ [▼ 선택_______]       │
│  │ 세력    │ [▼ 선택_______]       │
│  │ 종족    │ [▼ 선택_______]       │
│  └─────────┘                        │
│                                     │
│  ┌─────────┐                        │
│  │ 섹션 2  │ 상세 정보              │
│  ├─────────┤                        │
│  │ 성격    │ [________________]     │
│  │ 배경    │ [________________]     │
│  └─────────┘                        │
│                                     │
│  [임시저장]  [신청 제출]             │
└─────────────────────────────────────┘
```

---

## 8. 범용 TRPG 스탯 시스템 (SS Engine 연동)

> 멀티 룰셋 지원: CoC, D&D, VtM 등
> 관리자가 사용할 필드 선택 가능
> 경험치 시스템 폐지 → 포인트 경제로 단일화

### 8.1 시스템 구조

```
┌─────────────────────────────────────────────────────────────┐
│                     mg_trpg_ruleset                          │
│  룰셋 정의 (CoC 7판, D&D 5e, VtM 5판, 커스텀...)             │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   mg_trpg_stat_field                         │
│  스탯/스킬 필드 정의                                         │
│  - sf_type: stat, derived, resource, skill, save, trait     │
│  - sf_use: 관리자가 ON/OFF 토글 ← 핵심!                      │
│  - sf_formula: 파생수치 계산식                               │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                mg_character_stat_value                       │
│  캐릭터별 실제 값 저장                                       │
│  - csv_value: 기본값                                         │
│  - csv_current/max: resource 타입용                          │
└─────────────────────────────────────────────────────────────┘
```

### 8.2 지원 룰셋

| 코드 | 룰셋 | 다이스 | 특징 |
|------|------|--------|------|
| coc7 | Call of Cthulhu 7판 | d100 | 9개 능력치, % 스킬, SAN 시스템 |
| dnd5e | D&D 5th Edition | d20 | 6개 능력치, 숙련도, 세이브 |
| vtm5 | Vampire: The Masquerade 5판 | d10 풀 | 9개 속성, 허기, 인간성 |
| custom | 커스텀 | 자유 | 관리자 정의 |

### 8.3 룰셋별 능력치 비교

**Call of Cthulhu 7판**
| 분류 | 필드 |
|------|------|
| 기본능력치 | STR, CON, SIZ, DEX, APP, INT, POW, EDU, LCK |
| 파생수치 | HP, MP, SAN, MOV, DB, Build |
| 스킬 | 발견, 회피, 은신, 도서관이용, 심리학, 오컬트... |

**D&D 5e**
| 분류 | 필드 |
|------|------|
| 능력치 | STR, DEX, CON, INT, WIS, CHA |
| 전투수치 | HP, AC, Initiative, Speed, Proficiency |
| 세이브 | Str Save, Dex Save, Con Save, Int Save, Wis Save, Cha Save |
| 스킬 | Athletics, Acrobatics, Stealth, Perception, Arcana... |

**VtM 5판**
| 분류 | 필드 |
|------|------|
| 육체 | Strength, Dexterity, Stamina |
| 사회 | Charisma, Manipulation, Composure |
| 정신 | Intelligence, Wits, Resolve |
| 상태 | Health, Willpower, Hunger, Humanity, Blood Potency |
| 스킬 | Athletics, Brawl, Stealth, Intimidation, Persuasion... |

### 8.4 관리자 설정 기능

> 모든 필드가 기본 제공되지만, 관리자가 사용할 항목만 선택

```
┌────────────────────────────────────────────────────────────┐
│ TRPG 스탯 설정                                             │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  사용 룰셋: [Call of Cthulhu 7판 ▼]                        │
│                                                            │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│                                                            │
│  [기본능력치] ────────────────────────────────────────────  │
│  ☑ STR (근력)     ☑ CON (체력)     ☑ SIZ (체격)          │
│  ☑ DEX (민첩)     ☐ APP (외모)     ☑ INT (지능)          │
│  ☑ POW (정신력)   ☑ EDU (교육)     ☑ LCK (행운)          │
│                                                            │
│  [파생수치] ──────────────────────────────────────────────  │
│  ☑ HP (체력)      ☑ MP (마력)      ☑ SAN (정신력)        │
│  ☐ MOV (이동력)   ☐ DB (피해보너스) ☐ Build (체구)        │
│                                                            │
│  [스킬] 사용: 12개 / 전체: 47개   [스킬 설정 →]            │
│                                                            │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│                                                            │
│  [전체 선택] [전체 해제] [기본값 복원]           [저장]     │
└────────────────────────────────────────────────────────────┘
```

**설정 저장 위치:** `mg_trpg_stat_field.sf_use` (0/1)

### 8.5 캐릭터 시트 흐름

```
1. 캐릭터 생성 시 룰셋 선택 (또는 사이트 기본 룰셋)
   → mg_character_ruleset에 저장

2. 해당 룰셋에서 sf_use=1인 필드만 표시
   → 관리자가 비활성화한 필드는 안 보임

3. 유저가 값 입력
   → mg_character_stat_value에 저장

4. 파생수치는 sf_formula로 자동 계산
   → 예: HP = ROUND((CON+SIZ)/10)
```

### 8.6 포인트 경제 연동

> 경험치(ch_exp) 폐지, 포인트로 단일화

**포인트 획득 방법:**
| 활동 | 포인트 | 비고 |
|------|--------|------|
| 출석 체크 | 100P | 일일 1회 |
| 세션 참여 | 500P | 세션당 |
| 세션 완주 | 1000P | 끝까지 참여 |
| 게시글 작성 | 50P | 게시판 설정 |
| 댓글 작성 | 10P | - |

**포인트 사용처 (룰셋 공통):**
| 항목 | 비용 | 효과 |
|------|------|------|
| 스탯 +1 | 500P | 영구 능력치 상승 |
| 행운/운 재굴림 | 200P | 새 값 부여 |
| 자원 회복 | 100P | HP/SAN/Hunger 등 |
| 아이템 구매 | 가변 | 아이템샵 연동 |

### 8.7 세션에서 스탯 활용

> SS Engine 세션에서 룰셋에 맞는 판정 시스템 제공

```javascript
// 판정 버튼 클릭 시
function rollCheck(statCode) {
  const ruleset = currentCharacter.ruleset;
  const value = getStatValue(statCode);

  switch (ruleset) {
    case 'coc7':
      // d100 판정, 성공 레벨 (대성공/익스트림/하드/일반/실패/대실패)
      return rollCoC(value);

    case 'dnd5e':
      // d20 + 수정치 판정
      const modifier = Math.floor((value - 10) / 2);
      return rollD20(modifier);

    case 'vtm5':
      // d10 풀 (속성+스킬), 헝거 다이스 포함
      return rollVtM(value, currentCharacter.hunger);
  }
}
```

---

## 9. 보류 항목 (추후 검토)

| 항목 | 설명 |
|------|------|
| 칭호 시스템 | 업적 기반 칭호 |
| 커플 시스템 | 캐릭터 간 관계 설정 |
| 직업 시스템 | CoC 직업 템플릿 |
| PvP 시스템 | 캐릭터 간 대결 |

---

## 9. TODO

- [x] 테이블 스키마 확정 → PLAN_DB.md
- [ ] 프로필 양식 관리 UI 설계
- [ ] 캐릭터 등록 폼 UI 설계
- [ ] 승인 워크플로우 구현
- [ ] 샘플 데이터 스크립트 작성
- [ ] API 엔드포인트 설계

---

*작성일: 2026-02-03*
*수정일: 2026-02-03*
*상태: 1차 기획 완료, 문서 검토 완료*
