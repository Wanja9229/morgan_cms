# Verify & Deploy Agent

> 코드 검증 및 배포 절차를 수행할 때 사용하는 가이드.
> 반드시 **한국어**로 응답할 것.

---

## 역할

구현 완료 후 코드 검증, 문서 업데이트, 커밋/배포를 수행한다.

---

## 1단계: PHP Syntax Check

모든 수정/생성된 PHP 파일에 대해 실행:

```bash
# Windows Docker 환경 (경로 앞 // 필수)
docker exec morgan_php php -l //var/www/html/path/to/file.php
```

### 주요 검증 대상

| 영역 | 경로 |
|------|------|
| 플러그인 | `plugin/morgan/morgan.php` |
| 관리자 | `adm/morgan/*.php` |
| 프론트 | `bbs/*.php` |
| 스킨 | `theme/morgan/skin/**/*.php` |
| 확장 | `extend/morgan.extend.php` |

### 에러 발생 시

```bash
# 에러 파일 확인
docker exec morgan_php php -l //var/www/html/plugin/morgan/morgan.php

# 에러 예시: Parse error: syntax error, unexpected ... in /var/www/html/...
# → 해당 라인 수정 후 재검증
```

---

## 2단계: DB 검증

```bash
# 테이블 존재 확인
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "SHOW TABLES LIKE 'mg_%';"

# 테이블 구조 확인
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "DESCRIBE mg_xxx;"

# 데이터 확인
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "SELECT COUNT(*) FROM mg_xxx;"
```

---

## 3단계: 기능 확인

### 프론트엔드

- [ ] http://localhost:8080 에서 페이지 정상 로드
- [ ] 모바일/데스크탑 반응형 확인
- [ ] SPA 라우터로 페이지 전환 시 정상 동작
- [ ] JavaScript 콘솔 에러 없음

### 관리자

- [ ] http://localhost:8080/adm/ 에서 메뉴 접근 가능
- [ ] CRUD 동작 확인 (등록/수정/삭제)
- [ ] 권한 체크 동작 확인

### 에러 로그 확인

```bash
# PHP 에러 로그
docker exec morgan_php tail -50 /var/log/php-fpm/error.log

# Nginx 에러 로그
docker exec morgan_nginx tail -50 /var/log/nginx/error.log
```

---

## 4단계: 문서 업데이트

### ROADMAP.md 체크박스

```markdown
# 완료된 항목
- [x] 기능명

# 미완료 항목
- [ ] 기능명
```

파일 위치: `docs/ROADMAP.md`

### CORE.md 업데이트 (필요 시)

- 새 테이블 추가 → DB 테이블 요약 섹션
- 새 파일 추가 → 파일 인덱스 섹션
- 새 알림 타입 → 알림 타입 섹션

파일 위치: `docs/CORE.md`

### install.sql

새 테이블 추가 시 `plugin/morgan/install/install.sql`에 CREATE TABLE 구문 추가:

```sql
-- 반드시 이 엔진/캐릭터셋 사용
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

---

## 5단계: 커밋

### 커밋 전 체크

- [ ] 모든 PHP 파일 syntax check 통과
- [ ] 불필요한 디버그 코드 (var_dump, console.log) 제거
- [ ] 민감 정보 (.env, 비밀번호 등) 미포함 확인

### 커밋 메시지 컨벤션

```
feat: Phase N {기능명} 구현 ({범위 요약})
fix: {버그 설명} 수정
docs: {문서명} 업데이트
refactor: {대상} 리팩토링
```

예시:
```
feat: Phase 19 탐색 파견 시스템 (DB + 백엔드 + 관리자)
fix: morgan.php null 배열 접근 오류 수정
docs: ROADMAP Phase 17-18 완료 반영
```

### Git 규칙

- **push는 사용자 요청 시에만**
- main 브랜치에 직접 작업 (현재 단일 브랜치 운영)

---

## 6단계: 배포 (사용자 요청 시)

### 배포 파이프라인

```
git push origin main
    ↓
GitHub Actions 트리거
    ↓
git-ftp push → Cafe24 호스팅
```

### 배포 전 확인

- [ ] 로컬에서 모든 기능 정상 확인
- [ ] install.sql 변경 시 프로덕션 DB에도 ALTER/CREATE 실행 필요 여부 체크
- [ ] 프로덕션 환경에서의 경로 차이 확인

---

## 빠른 검증 명령어 모음

```bash
# PHP syntax check (전체)
docker exec morgan_php find //var/www/html/plugin/morgan -name "*.php" -exec php -l {} \;
docker exec morgan_php find //var/www/html/adm/morgan -name "*.php" -exec php -l {} \;

# 특정 파일
docker exec morgan_php php -l //var/www/html/plugin/morgan/morgan.php

# DB 테이블 목록
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "SHOW TABLES;"

# 최근 에러 로그
docker exec morgan_php tail -20 /var/log/php-fpm/error.log 2>/dev/null || echo "로그 파일 없음"
```
