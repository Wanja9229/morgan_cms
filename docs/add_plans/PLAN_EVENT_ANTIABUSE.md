# 히든 이벤트 시스템 — 어뷰징 방지 설계

> Morgan CMS 히든 아이템/이벤트 시스템의 보안 및 어뷰징 방지를 위한 설계 문서

---

## 1. 위협 모델

히든 이벤트 시스템에서 발생할 수 있는 어뷰징 유형을 먼저 정의한다.

### 1-1. 클라이언트 조작

SPA 구조상 JS 코드가 노출되므로, 유저가 개발자 도구를 통해 확률 판정을 조작하거나 보상 API를 직접 호출할 수 있다. 예를 들어 콘솔에서 fetch로 보상 엔드포인트를 직접 때리거나, 이벤트 출현 확률을 강제로 100%로 변경하는 식이다.

### 1-2. 반복 수령 (리플레이 어택)

같은 이벤트를 새로고침이나 SPA 재진입으로 반복 트리거하여 보상을 중복 수령한다. 특히 SPA 라우팅 콜백에 걸려있으므로, 빠르게 페이지를 왔다갔다 하면서 대량 수령을 시도할 수 있다.

### 1-3. 자동화 (봇/매크로)

스크립트로 페이지 전환을 자동 반복하면서 이벤트 출현을 노린다. 이벤트가 떴을 때 자동 클릭까지 하는 매크로도 가능하다.

### 1-4. 세션 위조

모건 CMS는 세션/쿠키 기반 세미 로그인이므로, 다른 유저의 access_code를 알아내면 해당 유저로 이벤트를 수령할 수 있다. 다만 이건 이벤트 시스템 고유 문제라기보다 인증 시스템 전체의 문제이므로 여기서는 이벤트 시스템 범위 내에서만 다룬다.

### 1-5. 타이밍 어뷰징

이벤트 시작/종료 시간 경계를 이용해 보상을 이중 수령하거나, 서버 시간과 클라이언트 시간 차이를 악용한다.

---

## 2. 핵심 원칙

### 서버 권위 (Server Authority)

모든 판정과 보상은 서버에서 처리한다. 클라이언트는 "이벤트가 있는지 확인"과 "클릭했다"는 신호만 보내고, 확률 계산, 보상 지급, 중복 체크는 전부 서버가 한다.

```
[클라이언트]                    [서버]
페이지 전환 발생
  → GET /api/event/check    →  확률 판정 수행
  ← 이벤트 데이터 or null   ←  결과 반환
유저가 클릭
  → POST /api/event/claim   →  유효성 검증 + 보상 지급
  ← 결과 (성공/실패)        ←  결과 반환
```

### 클라이언트는 연출만

클라이언트 코드에는 확률값, 보상량 등 민감한 데이터를 일절 포함하지 않는다. 서버가 "이 이벤트를 표시해라"고 보내주면 이미지와 위치 정보로 렌더링만 한다.

### 실패해도 안전하게 (Fail-Safe)

검증에 실패하거나 의심스러운 요청이 들어와도, 그냥 "이벤트 없음"이나 "수령 실패"로 조용히 처리한다. 에러 메시지에 구체적인 실패 사유를 노출하지 않는다.

---

## 3. 서버사이드 검증 설계

### 3-1. 이벤트 토큰 시스템

서버가 이벤트를 내려줄 때 일회용 토큰을 함께 발급한다. 클릭 시 이 토큰을 함께 보내야만 보상이 지급된다.

```
DB: event_tokens 테이블
─────────────────────────────────
token_id     VARCHAR(64)  PK     -- 랜덤 생성 (UUID 또는 bin2hex)
event_id     INT                 -- 어떤 이벤트인지
uid          VARCHAR(50)         -- 누구에게 발급했는지 (access_code)
issued_at    DATETIME            -- 발급 시각
expires_at   DATETIME            -- 만료 시각 (발급 후 5분)
claimed      TINYINT DEFAULT 0   -- 수령 여부
claimed_at   DATETIME NULL       -- 수령 시각
```

**흐름:**

1. `/api/event/check` 호출 시 서버가 확률 판정
2. 이벤트 당첨이면 토큰 생성 → DB 저장 → 클라이언트에 토큰 + 이벤트 정보 반환
3. 클릭 시 `/api/event/claim`에 토큰 전송
4. 서버에서 검증: 토큰 존재 여부, 해당 유저 소유인지, 만료 전인지, 미수령인지
5. 모두 통과하면 보상 지급 + claimed = 1 처리

**토큰 만료 시간은 5분이 적정.** 너무 짧으면 느린 유저가 못 받고, 너무 길면 토큰 수집 후 일괄 수령 시도 여지가 생긴다.

### 3-2. 수령 제한 (Rate Limit)

유저별, 시간대별 수령 횟수를 제한한다.

```
DB: event_claim_log 테이블
─────────────────────────────────
log_id       INT AUTO_INCREMENT PK
uid          VARCHAR(50)
event_id     INT
reward_type  VARCHAR(20)        -- 'point' / 'material'
reward_amount INT
claimed_at   DATETIME
ip_address   VARCHAR(45)
```

**제한 규칙:**

| 제한 항목 | 값 | 이유 |
|----------|-----|------|
| 같은 이벤트 1일 수령 | 1회 | 같은 보물을 하루에 여러 번 줍는 건 부자연스러움 |
| 전체 이벤트 1일 수령 | 5회 | 하루 종일 돌아다녀도 5개가 최대 |
| 1시간 내 수령 | 3회 | 단시간 집중 파밍 방지 |
| 이벤트 체크 요청 | 분당 10회 | 페이지 광속 전환 방지 |

**1일 기준은 서버 시간 자정(KST 00:00) 리셋.**

### 3-3. 확률 판정 서버사이드 처리

```php
// 서버에서만 실행되는 확률 판정
function checkEventAppearance($uid, $event) {
    // 1. 이미 오늘 이 이벤트를 수령했는지
    if (hasClaimedToday($uid, $event['id'])) {
        return false;
    }
    
    // 2. 오늘 전체 수령 한도 도달했는지
    if (getTodayClaimCount($uid) >= 5) {
        return false;
    }
    
    // 3. 최근 1시간 내 수령 한도
    if (getRecentClaimCount($uid, 3600) >= 3) {
        return false;
    }
    
    // 4. 확률 판정 (서버에서만)
    $roll = mt_rand(1, 10000); // 0.01% 단위
    $threshold = $event['probability'] * 100; // DB에 저장된 확률(%)
    
    return $roll <= $threshold;
}
```

**클라이언트 코드에 확률값을 절대 내려보내지 않는다.** API 응답은 "이벤트 있음(토큰+이미지+위치)" 또는 "이벤트 없음" 두 가지뿐이다.

---

## 4. 요청 검증 체크리스트

`/api/event/claim` 엔드포인트에서 보상 지급 전 순서대로 검증한다.

```
1. 세션 유효성     → 로그인된 유저인가?
2. 토큰 존재       → 해당 token_id가 DB에 있는가?
3. 토큰 소유자     → 토큰의 uid가 현재 세션의 uid와 일치하는가?
4. 토큰 미수령     → claimed = 0 인가?
5. 토큰 미만료     → expires_at > NOW() 인가?
6. 이벤트 활성     → 이벤트가 현재 활성 기간 내인가?
7. 일일 한도       → 오늘 수령 횟수가 한도 이내인가?
8. 시간당 한도     → 최근 1시간 수령 횟수가 한도 이내인가?
```

**하나라도 실패하면 즉시 거부.** 응답은 단순히 `{ "success": false }` 만 반환하고 구체적 사유는 노출하지 않는다. 로그에만 실패 사유를 기록한다.

---

## 5. 자동화/봇 방어

### 5-1. 요청 간격 검증

정상 유저는 페이지 전환 → 이벤트 확인 → 클릭까지 최소 수 초가 걸린다. 비정상적으로 빠른 패턴을 감지한다.

```php
// 이벤트 체크 요청 간격이 1초 미만이면 무시
$lastCheck = getLastCheckTime($uid);
if ($lastCheck && (time() - $lastCheck) < 1) {
    return ['event' => null]; // 조용히 빈 응답
}

// 토큰 발급~수령 간격이 0.5초 미만이면 거부
// (이미지 로드 + 클릭까지 최소 0.5초는 걸림)
$token = getToken($tokenId);
$elapsed = time() - strtotime($token['issued_at']);
if ($elapsed < 0.5) {
    logSuspicious($uid, 'too_fast_claim');
    return ['success' => false];
}
```

### 5-2. 행동 패턴 모니터링

완전 자동 실시간 차단까지는 필요 없고, 의심 로그를 남겨서 관리자가 확인할 수 있게 한다.

```
DB: event_suspicious_log 테이블
─────────────────────────────────
log_id       INT AUTO_INCREMENT PK
uid          VARCHAR(50)
reason       VARCHAR(100)       -- 'too_fast', 'rate_exceeded', 'expired_token' 등
details      TEXT NULL           -- 상세 정보 (JSON)
created_at   DATETIME
```

**기록 대상:**
- 만료된 토큰으로 수령 시도
- 타인의 토큰으로 수령 시도
- 분당 요청 한도 초과
- 0.5초 이내 초고속 수령 시도
- 존재하지 않는 토큰으로 요청

**자동 차단은 하지 않는다.** 자캐 커뮤니티 특성상 오탐으로 유저가 차단되면 커뮤니티 분위기에 치명적이다. 의심 로그가 일정 건수(예: 하루 10건) 이상 쌓이면 관리자에게 알림만 보내고, 판단은 관리자가 한다.

---

## 6. 데이터 무결성

### 6-1. 트랜잭션 처리

보상 지급과 토큰 소비는 반드시 하나의 트랜잭션 안에서 처리한다.

```php
try {
    $pdo->beginTransaction();
    
    // 1. 토큰 상태 변경 (FOR UPDATE로 락)
    $stmt = $pdo->prepare("
        SELECT * FROM event_tokens 
        WHERE token_id = ? AND uid = ? AND claimed = 0 AND expires_at > NOW()
        FOR UPDATE
    ");
    $stmt->execute([$tokenId, $uid]);
    $token = $stmt->fetch();
    
    if (!$token) {
        $pdo->rollback();
        return ['success' => false];
    }
    
    // 2. 보상 지급 (포인트 증가)
    $pdo->prepare("
        UPDATE {user_table} SET wr_3 = wr_3 + ? WHERE wr_1 = ?
    ")->execute([$reward, $uid]);
    
    // 3. 토큰 수령 처리
    $pdo->prepare("
        UPDATE event_tokens SET claimed = 1, claimed_at = NOW() WHERE token_id = ?
    ")->execute([$tokenId]);
    
    // 4. 수령 로그
    $pdo->prepare("
        INSERT INTO event_claim_log (uid, event_id, reward_type, reward_amount, claimed_at, ip_address)
        VALUES (?, ?, ?, ?, NOW(), ?)
    ")->execute([$uid, $token['event_id'], $rewardType, $reward, $_SERVER['REMOTE_ADDR']]);
    
    $pdo->commit();
    return ['success' => true, 'reward' => $reward];
    
} catch (Exception $e) {
    $pdo->rollback();
    logError('event_claim_failed', $e->getMessage());
    return ['success' => false];
}
```

**`SELECT ... FOR UPDATE`가 핵심.** 동시에 같은 토큰으로 두 번 요청이 들어와도 하나만 성공한다.

### 6-2. 만료 토큰 정리

미수령 만료 토큰이 계속 쌓이면 테이블이 비대해지므로 주기적으로 정리한다.

```sql
-- 크론잡 또는 관리자 페이지에서 실행 (1일 1회)
DELETE FROM event_tokens 
WHERE claimed = 0 AND expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

-- 수령 완료 토큰도 30일 지나면 정리 (로그는 event_claim_log에 남아있으니)
DELETE FROM event_tokens 
WHERE claimed = 1 AND claimed_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 7. 관리자 도구

### 7-1. 모니터링 대시보드

관리자 페이지에 다음 정보를 표시한다.

- **오늘의 이벤트 현황**: 이벤트별 출현 횟수, 수령 횟수, 수령률
- **유저별 수령 현황**: 오늘 누가 몇 개 받았는지
- **의심 로그**: event_suspicious_log 최근 내역
- **보상 총량**: 오늘/이번 주/이번 달 총 지급 포인트/재료

### 7-2. 비상 조치 기능

- **이벤트 즉시 비활성화**: 어뷰징 발견 시 해당 이벤트를 즉시 끌 수 있는 토글
- **보상 회수**: 특정 유저의 부정 수령분을 포인트에서 차감 (수동)
- **유저 이벤트 차단**: 특정 유저를 이벤트 시스템에서 일시 제외

### 7-3. 밸런스 조정

이벤트별로 다음을 관리자가 직접 조절할 수 있게 한다.

- 등장 확률 (0.01% ~ 100%)
- 1일 최대 수령 횟수 (이벤트별, 전체)
- 보상량
- 활성 기간 (시작일~종료일)
- 활성 시간대 (예: 20시~23시만 출현)

---

## 8. 구현 우선순위

### Phase 1: 기본 보안 (필수, 이벤트 시스템과 동시 구현)

- 서버사이드 확률 판정
- 토큰 기반 수령 검증
- 유저별 일일 수령 제한
- 트랜잭션 기반 보상 지급

### Phase 2: 모니터링 (베타 운영 시작 시)

- 수령 로그 테이블 + 관리자 조회 페이지
- 의심 행동 로그
- 이벤트 즉시 비활성화 토글

### Phase 3: 고도화 (운영 데이터 축적 후)

- 요청 간격 검증
- 시간대별 활성 설정
- 유저별 이벤트 차단
- 보상 회수 기능

---

## 9. 테이블 설계 요약

```sql
-- 1. 이벤트 정의
CREATE TABLE morgan_events (
    event_id        INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(100) NOT NULL,
    image_path      VARCHAR(255) NOT NULL,
    click_text      TEXT,
    sound_path      VARCHAR(255) NULL,
    effect_type     VARCHAR(50) DEFAULT 'none',
    reward_type     ENUM('point', 'material') NOT NULL,
    reward_amount   INT NOT NULL,
    probability     DECIMAL(5,2) NOT NULL,        -- 0.01 ~ 100.00 (%)
    position_type   VARCHAR(20) DEFAULT 'random',  -- 'random', 'fixed'
    position_x      VARCHAR(10) NULL,              -- % 값 (예: '85%')
    position_y      VARCHAR(10) NULL,
    daily_limit     INT DEFAULT 1,                 -- 이벤트별 유저당 일일 수령 한도
    active_from     DATETIME NULL,
    active_until    DATETIME NULL,
    time_start      TIME NULL,                     -- 시간대 제한 (선택)
    time_end        TIME NULL,
    is_active       TINYINT DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. 일회용 토큰
CREATE TABLE morgan_event_tokens (
    token_id        VARCHAR(64) PRIMARY KEY,
    event_id        INT NOT NULL,
    uid             VARCHAR(50) NOT NULL,
    issued_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at      DATETIME NOT NULL,
    claimed         TINYINT DEFAULT 0,
    claimed_at      DATETIME NULL,
    INDEX idx_uid_claimed (uid, claimed),
    INDEX idx_expires (expires_at)
);

-- 3. 수령 로그
CREATE TABLE morgan_event_claims (
    claim_id        INT AUTO_INCREMENT PRIMARY KEY,
    uid             VARCHAR(50) NOT NULL,
    event_id        INT NOT NULL,
    reward_type     VARCHAR(20) NOT NULL,
    reward_amount   INT NOT NULL,
    claimed_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address      VARCHAR(45),
    INDEX idx_uid_date (uid, claimed_at),
    INDEX idx_event_date (event_id, claimed_at)
);

-- 4. 의심 행동 로그
CREATE TABLE morgan_event_suspicious (
    log_id          INT AUTO_INCREMENT PRIMARY KEY,
    uid             VARCHAR(50) NOT NULL,
    reason          VARCHAR(100) NOT NULL,
    details         JSON NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_uid (uid),
    INDEX idx_date (created_at)
);
```

---

## 10. 요약

| 위협 | 대응 | 구현 시점 |
|------|------|----------|
| 클라이언트 확률 조작 | 서버사이드 확률 판정 | Phase 1 |
| 보상 API 직접 호출 | 일회용 토큰 검증 | Phase 1 |
| 중복 수령 | 토큰 소비 + SELECT FOR UPDATE | Phase 1 |
| 일일 대량 파밍 | 유저별 일일/시간당 수령 한도 | Phase 1 |
| 봇/매크로 | 요청 간격 검증 + 의심 로그 | Phase 2~3 |
| 세션 위조 | 토큰-세션 UID 교차 검증 | Phase 1 |
| 타이밍 어뷰징 | 토큰 만료(5분) + 서버 시간 기준 | Phase 1 |
| 운영 중 긴급 상황 | 이벤트 즉시 비활성화 토글 | Phase 2 |
