# 룰렛 시스템 기획서

> 작성일: 2026-03-13
> 상태: 기획 확정

---

## 개요

포인트를 소모하여 돌리는 **운명의 룰렛**. 보상(포인트, 재료, 아이템, 칭호)과 벌칙(닉네임 강제 변경, 프로필 이미지 강제, 벌칙 로그 작성 등)이 혼합된 커뮤니티 이벤트 시스템.

- 룰렛은 **1개만** 운영 (설정은 `mg_config` 기반)
- 전용 게시판에서 룰렛 UI + 결과 타임라인 + 벌칙 로그를 통합 제공
- **잭팟 풀**: 누적 투입 포인트의 일부를 0.001% 확률로 전액 지급

---

## 핵심 규칙

### 기본 흐름
```
포인트 소모 → 룰렛 휠 애니메이션 → 결과 확정
  ├─ 보상(reward)  → 즉시 지급 (포인트/재료/아이템/칭호)
  ├─ 꽝(blank)     → 연출만, 효과 없음
  ├─ 잭팟(jackpot) → 누적 풀 전액 지급, 풀 리셋
  └─ 벌칙(penalty) → 알림 발송 → 수행 또는 아이템 사용
```

### 벌칙 처리 흐름
```
벌칙 당첨 → 알림 수신
  ├─ [무효화 아이템] 사용 → 즉시 소멸
  ├─ [랜덤 떠넘기기] 사용 → 랜덤 활성 회원에게 전달
  ├─ [지목 떠넘기기] 사용 → 특정 회원에게 전달
  └─ 아이템 미사용 → 벌칙 수행 의무 확정

벌칙 넘겨받음 → 알림 수신
  ├─ [무효화 아이템] 사용 → 거부, 즉시 소멸
  └─ 아이템 미사용 → 벌칙 수행 의무 확정
     (재전달 불가 — 1회 전달 제한)

벌칙 수행 의무
  ├─ 시스템 강제형 → 자동 적용 (닉변, 프사 변경 등)
  ├─ 로그 제출형 → 벌칙 로그 게시판에 글 작성 → 완료
  └─ 미수행 시 → 다음 룰렛 차단
```

### 아이템 사용 타이밍
- **무효화 / 떠넘기기**: 벌칙 알림을 받은 시점에서만 사용 가능
- 알림에서 처리하지 않으면 벌칙 수행 의무 확정
- 당장 아이템이 없어도 상점에서 구매 후 처리할 수 있는 정도의 유예
- 벌칙 확정 후에는 아이템 사용 불가 → 수행만 가능

### 떠넘기기 규칙
- **1회 전달 제한**: 넘겨받은 벌칙은 재전달 불가 (무한 핑퐁 방지)
- **랜덤 대상**: 최근 7일 내 로그인한 활성 회원 중 랜덤 (본인 제외)
- **지목 대상**: 아무 회원에게나 가능 (탈퇴/정지 회원 제외)
- **떠넘긴 사람 공개 여부**: 관리자 설정 (익명 / 공개 선택)

### 벌칙 수행 기한
- **무기한** — 수행 전에는 다음 룰렛을 돌릴 수 없으므로 이것만으로 충분한 제한

---

## 보상/벌칙 유형

### 보상 (reward)
| reward_type | 동작 | 비고 |
|-------------|------|------|
| `point` | 포인트 지급 | 즉시 |
| `material` | 재료 지급 | mt_id + 수량 |
| `item` | 상점 아이템 지급 | si_id, 인벤토리 추가 |
| `title` | 칭호 지급 | tp_id |

### 벌칙 (penalty)
| reward_type | 동작 | 시스템 강제? |
|-------------|------|------------|
| `nickname` | 닉네임 강제 변경 (N시간) | **O** — 페이지 로드 시 만료 체크, 자동 복원 |
| `profile_image` | 게시판 닉네임 옆 이미지 강제 변경 | **O** — mg_render_nickname()에서 체크, 프로필/인장은 미변경 |
| `log` | 벌칙 로그 작성 의무 | X — 게시판에 글 작성으로 완료 |
| `log_nickname` | 닉변 + 로그 작성 | **O** (닉변) + 로그 제출 |
| `log_image` | 프사 변경 + 로그 작성 | **O** (이미지) + 로그 제출 |

### 꽝 (blank)
- 확률 높게 설정 (관리자 가중치 조정)
- 연출만 있고 효과 없음

### 잭팟 (jackpot)
- 누적 풀 전액 지급
- 확률: 약 0.001% (가중치로 조정)
- 당첨 시 풀 리셋, 전체 알림/축하 연출

---

## 잭팟 풀

- 매 스핀마다 `roulette_cost` 전액이 풀에 누적
- `mg_config('roulette_jackpot_pool')` 에 현재 풀 금액 저장
- 룰렛 페이지 최상단에 **현재 누적 금액 크게 표시**
- 잭팟 당첨 시:
  - 풀 전액을 당첨자에게 포인트 지급
  - 풀 0으로 리셋
  - 전체 알림 + 특별 연출
  - 로그에 기록

---

## 시스템 강제 벌칙 구현 (크론 없음)

### 닉네임 강제 변경
1. 당첨 시: `g5_member.mb_nick` 변경, 원래 닉네임은 `rl_original_value`에 백업
2. `rl_expires_at` = NOW() + duration_hours
3. **복원**: `morgan.php` 로드 시 세션당 1회 체크
   ```
   SELECT * FROM mg_roulette_log
   WHERE mb_id = :mb_id AND rl_status = 'active'
   AND rp_reward_type IN ('nickname', 'log_nickname')
   AND rl_expires_at IS NOT NULL AND rl_expires_at < NOW()
   ```
   → 매칭되면 `mb_nick` 복원 + `rl_status` 업데이트
4. 닉변 중 회원정보 수정에서 닉네임 변경 차단

### 프로필 이미지 강제 변경
1. 당첨 시: `mg_roulette_log`에 벌칙 이미지 URL 기록 (실제 `mb_icon`은 미변경)
2. **표시**: `mg_render_nickname()` 또는 게시판 닉네임 렌더링 시
   ```
   활성 image 벌칙 존재? → 벌칙 이미지로 교체 표시
   ```
3. 프로필 페이지, 인장, 캐릭터 뷰에서는 원본 유지
4. 만료 시 자동 해제 (닉변과 동일한 만료 체크)

---

## 전용 게시판 구조

`bo_table = 'roulette'` — 전용 게시판 1개

### 페이지 레이아웃
```
┌─────────────────────────────────────────┐
│  💰 현재 잭팟 풀: 125,400 포인트        │  ← 최상단, 큰 숫자
├─────────────────────────────────────────┤
│  [ 🎰 룰렛 ]  [ 📜 결과 피드 ]         │  ← 탭 전환
├─────────────────────────────────────────┤
│                                         │
│  [룰렛 탭]                              │
│        [ 룰렛 휠 UI ]                   │
│        비용: 100 포인트                  │
│        [ 돌리기 버튼 ]                   │
│        내 벌칙 상태 표시                 │
│                                         │
│  [결과 피드 탭]                          │
│  🎉 유저A — 500 포인트 획득!            │
│  😈 유저B — "이름 모를 고양이" 벌칙      │
│  ⬜ 유저C — 꽝!                         │
│  🏆 유저D — 잭팟! 50,000 포인트!!       │
│  (페이지네이션)                          │
│                                         │
├─────────────────────────────────────────┤
│  벌칙 로그 게시판                        │  ← 하단 고정, 탭과 무관
│  [글목록 — 벌칙 수행 로그들]             │
│  벌칙 로그 작성 버튼                     │
└─────────────────────────────────────────┘
```

### 벌칙 로그 작성
- 벌칙 로그 작성 시 `rl_id`를 연결 → 해당 벌칙 완료 처리
- 로그 제출형 벌칙이 아닌 경우 (시스템 강제형만) 로그 작성 불필요

---

## 상점 아이템

기존 `mg_shop_item` 테이블에 `si_type` 추가:

| si_type | 이름 | 효과 |
|---------|------|------|
| `roulette_nullify` | 벌칙 무효화권 | 벌칙 즉시 소멸 |
| `roulette_transfer_random` | 랜덤 떠넘기기권 | 랜덤 활성 회원에게 전달 |
| `roulette_transfer_target` | 지목 떠넘기기권 | 특정 회원에게 전달 |

- **획득 경로**: 상점 포인트 구매 (관리자가 가격 설정)
- 상점 아이템이므로 보상/RP/미션 등 기존 보상 경로에서도 지급 가능

---

## DB 설계

### 테이블

```sql
-- 룰렛 보상/벌칙 항목 (관리자 설정)
CREATE TABLE mg_roulette_prize (
    rp_id          INT AUTO_INCREMENT PRIMARY KEY,
    rp_name        VARCHAR(100) NOT NULL COMMENT '표시명',
    rp_desc        TEXT COMMENT '상세 설명 (벌칙 내용 등)',
    rp_type        ENUM('reward','penalty','blank','jackpot') NOT NULL DEFAULT 'blank',
    rp_icon        VARCHAR(200) COMMENT 'game-icons 아이콘명',
    rp_image       VARCHAR(500) COMMENT '벌칙 프사 이미지 (penalty용)',
    rp_color       VARCHAR(7) NOT NULL DEFAULT '#6b7280' COMMENT '룰렛 칸 색상',
    rp_reward_type ENUM('point','material','item','title','nickname','profile_image','log','log_nickname','log_image','none') NOT NULL DEFAULT 'none',
    rp_reward_value TEXT COMMENT 'JSON: {amount:100} / {mt_id:3,count:5} / {si_id:10} / {nickname:"이름 모를 고양이"}',
    rp_duration_hours INT NOT NULL DEFAULT 0 COMMENT '시간제 벌칙 지속시간 (0=미적용)',
    rp_require_log TINYINT(1) NOT NULL DEFAULT 0 COMMENT '벌칙 로그 제출 필요',
    rp_weight      INT NOT NULL DEFAULT 10 COMMENT '확률 가중치',
    rp_order       INT NOT NULL DEFAULT 0,
    rp_use         TINYINT(1) NOT NULL DEFAULT 1,
    rp_created     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 룰렛 사용 이력
CREATE TABLE mg_roulette_log (
    rl_id            INT AUTO_INCREMENT PRIMARY KEY,
    mb_id            VARCHAR(20) NOT NULL,
    rp_id            INT NOT NULL COMMENT '당첨 항목',
    rl_source        ENUM('spin','transfer_random','transfer_target') NOT NULL DEFAULT 'spin',
    rl_from_mb_id    VARCHAR(20) DEFAULT NULL COMMENT '떠넘기기 원래 주인',
    rl_status        ENUM('pending','active','completed','nullified','transferred') NOT NULL DEFAULT 'pending',
    -- pending: 알림 수신, 아이템 사용 가능
    -- active: 벌칙 수행 의무 확정 (아이템 사용 불가)
    -- completed: 수행 완료 / 보상 지급 완료
    -- nullified: 무효화 아이템 사용
    -- transferred: 떠넘기기로 다른 사람에게 전달됨
    rl_transfer_count TINYINT NOT NULL DEFAULT 0 COMMENT '전달 횟수 (1 이상이면 재전달 불가)',
    rl_original_nick VARCHAR(255) DEFAULT NULL COMMENT '닉변 시 원래 닉네임',
    rl_penalty_image VARCHAR(500) DEFAULT NULL COMMENT '프사 강제 변경 이미지',
    rl_bo_table      VARCHAR(20) DEFAULT NULL COMMENT '벌칙 로그 게시판',
    rl_wr_id         INT DEFAULT NULL COMMENT '벌칙 로그 글 ID',
    rl_expires_at    DATETIME DEFAULT NULL COMMENT '시간제 벌칙 만료',
    rl_cost          INT NOT NULL DEFAULT 0 COMMENT '이 스핀에 소모된 포인트',
    rl_datetime      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mb_status (mb_id, rl_status),
    INDEX idx_expires (rl_status, rl_expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### mg_config 키

| 키 | 기본값 | 설명 |
|----|--------|------|
| `roulette_use` | `0` | 활성화 여부 |
| `roulette_cost` | `100` | 1회 비용 |
| `roulette_daily_limit` | `3` | 1일 제한 횟수 (0=무제한) |
| `roulette_cooldown` | `0` | 쿨다운 (분) |
| `roulette_board` | `roulette` | 벌칙 로그 게시판 bo_table |
| `roulette_jackpot_pool` | `0` | 현재 잭팟 풀 |
| `roulette_transfer_reveal` | `0` | 떠넘기기 시 보낸 사람 공개 (0=익명, 1=공개) |
| `roulette_pending_hours` | `24` | 미확인 벌칙 자동 확정 시간 |

### mg_shop_item.si_type 추가

기존 ENUM에 3개 추가:
- `roulette_nullify`
- `roulette_transfer_random`
- `roulette_transfer_target`

---

## 파일 구조

```
bbs/
  roulette.php              -- 메인 페이지 (룰렛 UI + 결과 + 게시판)
  roulette_spin.php         -- AJAX: 스핀 처리
  roulette_action.php       -- AJAX: 무효화/떠넘기기 처리
  roulette_complete.php     -- 벌칙 로그 작성 완료 hook

adm/morgan/
  roulette.php              -- 관리자: 항목 관리 (CRUD)
  roulette_config.php       -- 관리자: 설정 (또는 기존 config.php에 섹션 추가)

plugin/morgan/
  roulette.php              -- 헬퍼 함수, 만료 체크, 렌더링 hook

theme/morgan/skin/roulette/
  wheel.skin.php            -- 룰렛 휠 UI + 애니메이션
  result.skin.php           -- 결과 타임라인
  log_list.skin.php         -- 벌칙 로그 게시판
```

---

## 룰렛 휠 UI

- CSS + JS 기반 원형 룰렛 (Canvas 또는 CSS transform rotate)
- 항목 수에 따라 자동 분할
- 칸별 색상은 `rp_color`
- 스핀 결과는 서버에서 먼저 확정 → 클라이언트는 해당 칸에 멈추도록 애니메이션
- 잭팟 당첨 시 특별 이펙트 (화면 전체 연출)

---

## 알림 연동

| 상황 | 알림 내용 | 액션 버튼 |
|------|----------|----------|
| 벌칙 당첨 | "룰렛에서 벌칙에 당첨되었습니다: {벌칙명}" | 무효화 / 떠넘기기 / 수행 |
| 벌칙 떠넘김 받음 | "누군가(또는 유저명)가 벌칙을 떠넘겼습니다: {벌칙명}" | 무효화 / 수행 |
| 잭팟 당첨 (전체) | "🏆 {유저명}님이 잭팟 {금액} 포인트를 획득!" | — |
| 벌칙 만료 | "닉네임/프로필 이미지가 원래대로 복원되었습니다" | — |

---

## pending → active 전환

- `rl_status = 'pending'`: 알림을 받은 상태, 아이템 사용 가능

### 전환 조건 (OR — 먼저 발생하는 쪽)
1. **알림 읽음 시** → 즉시 active 전환 (읽는 그 순간에 아이템 사용 UI 제공)
2. **N시간 경과 시** → 미확인이어도 자동 active 전환 (페이지 로드 시 체크)
   - 기본값: 24시간
   - 관리자 설정: `roulette_pending_hours`

### 아이템 사용 타이밍
- **알림 읽음으로 active 전환 시**: 전환 직후 아이템 사용 UI 노출 (무효화/떠넘기기)
- **시간 경과로 자동 전환 시**: 아이템 사용 기회 없이 벌칙 확정
- active 전환 시 시스템 강제 벌칙 즉시 적용 (닉변, 프사)

> 이 구조로 "알림을 안 읽고 포인트 모으기" 편법 차단

---

## 구현 우선순위

1. **DB + 관리자 CRUD** — 테이블, 항목 등록/수정
2. **스핀 로직** — 가중치 확률, 포인트 차감, 보상 지급
3. **룰렛 휠 UI** — 애니메이션, 결과 표시
4. **벌칙 시스템** — 닉변/프사 강제, 만료 복원
5. **잭팟** — 풀 누적, 당첨 처리, 연출
6. **아이템 연동** — 무효화/떠넘기기 알림 UI
7. **벌칙 로그 게시판** — 글 작성 → 벌칙 완료 연동
8. **타임라인** — 최근 결과 실시간 피드
