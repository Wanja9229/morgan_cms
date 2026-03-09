# 마이그레이션 작성 가이드

## 파일 규칙

- **경로**: `db/migrations/YYYYMMDD_HHMMSS_snake_case_설명.sql`
- **파일명**: 알파벳순 = 시간순 (자동 순서 실행)
- **멱등성 필수**: 중복 실행해도 안전해야 함
- **마스터 DB 전용**: 파일명에 `master` 포함 → 테넌트 마이그레이션에서 자동 스킵

## 금지 문법

### ADD COLUMN IF NOT EXISTS (사용 금지)

```sql
-- ❌ 절대 사용 금지 — MySQL 5.7 및 Cafe24 등 호스팅 환경에서 미지원
ALTER TABLE mg_character ADD COLUMN IF NOT EXISTS ch_header VARCHAR(500) DEFAULT '';
```

### DROP COLUMN IF EXISTS (사용 금지)

```sql
-- ❌ 동일하게 미지원
ALTER TABLE mg_character DROP COLUMN IF EXISTS ch_old_column;
```

## 올바른 패턴

### 컬럼 추가 (information_schema + PREPARE)

```sql
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'mg_character'
    AND COLUMN_NAME = 'ch_header');
SET @sql = IF(@col = 0,
    'ALTER TABLE mg_character ADD COLUMN ch_header VARCHAR(500) DEFAULT \'\' AFTER ch_image',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
```

### 여러 컬럼 추가 시

변수명에 숫자 접미사를 붙여 구분:

```sql
SET @col1 = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_stat' AND COLUMN_NAME = 'stat_con');
SET @sql1 = IF(@col1 = 0, 'ALTER TABLE mg_battle_stat ADD COLUMN stat_con int DEFAULT 5 AFTER stat_int', 'SELECT 1');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @col2 = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_stat' AND COLUMN_NAME = 'stat_luk');
SET @sql2 = IF(@col2 = 0, 'ALTER TABLE mg_battle_stat ADD COLUMN stat_luk int DEFAULT 5 AFTER stat_con', 'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
```

### 테이블 생성 (안전)

```sql
-- ✅ CREATE TABLE IF NOT EXISTS는 정상 지원
CREATE TABLE IF NOT EXISTS mg_example (
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 데이터 삽입 (안전)

```sql
-- ✅ INSERT IGNORE는 정상 지원
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('key', 'value');
```

### ENUM 확장

```sql
-- ✅ MODIFY COLUMN은 정상 지원 (테이블 리빌드 발생, 대형 테이블 주의)
ALTER TABLE mg_shop_item MODIFY si_type ENUM('title','badge','etc') NOT NULL DEFAULT 'etc';
```

### 인덱스 추가

```sql
-- information_schema로 존재 여부 확인
SET @idx = (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_skill' AND INDEX_NAME = 'uk_sk_code');
SET @sql = IF(@idx = 0, 'ALTER TABLE mg_battle_skill ADD UNIQUE INDEX uk_sk_code (sk_code)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
```

## 마이그레이션 엔진 주의사항

- **위치**: `plugin/morgan/migrate.php`
- **실행 시점**: `morgan.php` 로드 시 세션당 1회 (파일 수 변경 감지)
- **에러 처리**: 실행 결과와 무관하게 "적용됨"으로 기록 (멱등성 전제)
- **멀티테넌트**: 각 테넌트 DB에 독립 실행, `master` 파일명은 자동 스킵
- **신규 테넌트**: `install.sql` + `seed.sql`로 스키마 생성 후, 마이그레이션은 실행 없이 "적용됨"으로 기록

## 체크리스트

마이그레이션 작성 전 확인:

- [ ] `ADD COLUMN IF NOT EXISTS` 사용하지 않았는가?
- [ ] `DROP COLUMN IF EXISTS` 사용하지 않았는가?
- [ ] 멱등성이 보장되는가? (중복 실행 시 에러 없음)
- [ ] `install.sql`에도 새 컬럼/테이블이 반영되었는가? (신규 테넌트용)
- [ ] 한국어 데이터 INSERT 시 `utf8mb4` 설정이 되어있는가?
- [ ] URL은 root-relative 경로를 사용하는가? (`/data/...`)
