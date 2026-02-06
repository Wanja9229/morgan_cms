# Morgan Edition - 개발 환경 설정

> 새 환경에서 프로젝트를 세팅하기 위한 가이드

---

## 필수 요구사항

| 프로그램 | 버전 | 용도 |
|----------|------|------|
| Docker Desktop | 최신 | 컨테이너 환경 |
| Node.js | 18+ (현재 22.17.1) | Tailwind CSS 빌드 |
| npm | 8+ (현재 10.9.2) | 패키지 관리 |
| Git | 최신 | 버전 관리 |

---

## 1. 프로젝트 클론

```bash
# 저장소 클론
git clone <repository-url> new_cms
cd new_cms
```

---

## 2. Docker 환경 구성

### 2.1 컨테이너 구성

| 컨테이너 | 이미지 | 포트 | 용도 |
|----------|--------|------|------|
| morgan_nginx | nginx:alpine | 8080 → 80 | 웹서버 |
| morgan_php | PHP 8.2-fpm (빌드) | 9000 (내부) | PHP 처리 |
| morgan_mysql | mysql:8.0 | 3307 → 3306 | 데이터베이스 |
| morgan_pma | phpmyadmin:latest | 8081 → 80 | DB 관리 |

### 2.2 Docker 실행

```bash
# 컨테이너 빌드 및 실행 (최초 1회)
docker-compose up -d --build

# 이후 실행
docker-compose up -d

# 컨테이너 중지
docker-compose down

# 로그 확인
docker-compose logs -f
```

### 2.3 접속 URL

| 서비스 | URL |
|--------|-----|
| 사이트 | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

---

## 3. 데이터베이스 정보

| 항목 | 값 |
|------|-----|
| Host (컨테이너 내부) | mysql |
| Host (외부 접속) | localhost:3307 |
| Database | morgan_db |
| User | morgan_user |
| Password | morgan_pass |
| Root Password | morgan_root |
| Charset | utf8mb4_unicode_ci |

### DB 직접 접근 (CLI)

```bash
# 컨테이너 내부에서 MySQL 실행
docker exec -it morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db

# 외부에서 쿼리 실행
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db -e "SELECT * FROM mg_config;"

# 테이블 구조 확인
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db -e "DESCRIBE mg_character;"
```

---

## 4. Node.js / Tailwind CSS

### 4.1 패키지 설치

```bash
npm install
```

### 4.2 CSS 빌드 명령어

```bash
# 개발 중 (watch 모드)
npm run css:watch

# 빌드 (minify)
npm run css:build

# 빌드 (minify 없이)
npm run css:dev
```

### 4.3 Tailwind 설정 파일

- 입력: `src/css/input.css`
- 출력: `theme/morgan/css/style.css`
- 설정: `tailwind.config.js` (프로젝트 루트)

---

## 5. 디렉토리 구조

```
new_cms/
├── docker/                 # Docker 설정
│   ├── nginx/default.conf
│   ├── php/Dockerfile
│   └── mysql/init.sql
├── docker-compose.yml
├── docs/                   # 문서
│   ├── CORE.md            # 핵심 참조
│   ├── SETUP.md           # 환경 설정 (이 파일)
│   └── plans/             # 기획 문서
├── plugin/morgan/          # Morgan 플러그인 (핵심 코드)
│   ├── morgan.php         # 헬퍼 함수
│   └── install/           # 설치 스크립트
├── theme/morgan/           # Morgan 테마
│   ├── head.php, tail.php
│   ├── css/style.css      # Tailwind 빌드 결과
│   └── skin/              # 스킨 파일
├── adm/morgan/             # 관리자 페이지
├── bbs/                    # 프론트 페이지
├── src/css/input.css       # Tailwind 입력 파일
├── package.json
└── tailwind.config.js
```

---

## 6. 새 환경 세팅 순서

```bash
# 1. 저장소 클론
git clone <repository-url> new_cms
cd new_cms

# 2. Node 패키지 설치
npm install

# 3. Docker 컨테이너 실행
docker-compose up -d --build

# 4. DB 초기화 (최초 1회, 필요시)
# docker/mysql/init.sql이 자동 실행됨
# 추가 SQL이 필요하면:
docker exec morgan_mysql mysql -umorgan_user -pmorgan_pass morgan_db < plugin/morgan/install/install.sql

# 5. CSS 빌드
npm run css:build

# 6. 브라우저에서 확인
# http://localhost:8080
```

---

## 7. 트러블슈팅

### 포트 충돌

```bash
# 사용 중인 포트 확인 (Windows)
netstat -ano | findstr :8080

# docker-compose.yml에서 포트 변경
# "8080:80" → "8082:80"
```

### 컨테이너 재빌드

```bash
# PHP Dockerfile 변경 시
docker-compose up -d --build php

# 전체 재빌드
docker-compose down
docker-compose up -d --build
```

### DB 데이터 초기화

```bash
# 볼륨 삭제 (데이터 완전 삭제)
docker-compose down -v
docker-compose up -d
```

### 권한 문제 (Linux/Mac)

```bash
# data 폴더 권한
chmod -R 777 data/
```

---

## 8. Git 워크플로우

```bash
# 브랜치 전략
main        # 안정 버전
develop     # 개발 브랜치

# 작업 시
git checkout develop
git pull origin develop
# ... 작업 ...
git add .
git commit -m "작업 내용"
git push origin develop
```

---

*작성일: 2026-02-06*
