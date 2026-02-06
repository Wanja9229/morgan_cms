# Morgan Edition - 구현 가이드

> 실질적인 개발 작업 순서 및 체크리스트
> 각 단계 완료 시 체크박스에 x 표시

---

## 현재 상태

| 항목 | 상태 |
|------|------|
| 그누보드5 | 설치됨 |
| Docker 환경 | 구성됨 (nginx + php + mysql + phpmyadmin) |
| 접속 URL | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| DB 정보 | morgan_db / morgan_user / morgan_pass |

---

## Phase 1: 기반 구축

### 1.1 개발 환경 확인 및 정리

```bash
# Docker 컨테이너 실행
docker-compose up -d

# 상태 확인
docker-compose ps
```

- [ ] Docker 컨테이너 정상 실행 확인
- [ ] http://localhost:8080 접속 확인
- [ ] 그누보드5 설치 완료 확인 (설치 안됐으면 /install 실행)
- [ ] phpMyAdmin 접속 확인

### 1.2 불필요한 기능 제거/비활성화

> 참고: [OVERVIEW.md - 7.1 제거 대상 기능](./OVERVIEW.md)

#### 1.2.1 폴더/파일 정리 (삭제 또는 백업)

- [ ] `/shop/` - 쇼핑몰 폴더 전체 (백업 후 삭제)
- [ ] `shop.config.php` - 쇼핑몰 설정
- [ ] `orderupgrade.php` - 주문 업그레이드
- [ ] `g4_import*.php` - 그누4 마이그레이션
- [ ] `yc4_import*.php` - 영카트4 마이그레이션
- [ ] `/mobile/` - 모바일 폴더 (반응형으로 대체)

#### 1.2.2 관리자 메뉴 비활성화

- [ ] 쇼핑몰 관련 메뉴 숨김 처리
- [ ] SMS/이메일 발송 메뉴 숨김 처리
- [ ] 소셜 로그인 설정 숨김 처리
- [ ] 본인인증 설정 숨김 처리

### 1.3 Morgan Edition 테마 기본 구조 생성

```
theme/morgan/
├── index.php           # 테마 메인 (진입점)
├── theme.config.php    # 테마 설정
├── head.php            # 헤더
├── tail.php            # 푸터
├── css/
│   └── style.css       # 메인 스타일 (Tailwind 빌드 결과)
├── js/
│   ├── app.js          # 메인 JS
│   └── router.js       # fetch 기반 라우터
├── layouts/
│   ├── main.php        # 메인 레이아웃
│   └── sidebar.php     # 좌측 사이드바
├── skin/               # 테마 전용 스킨
│   ├── board/
│   ├── member/
│   └── content/
└── img/
```

- [ ] `/theme/morgan/` 폴더 생성
- [ ] `theme.config.php` 작성
- [ ] `head.php` 기본 구조 작성
- [ ] `tail.php` 기본 구조 작성
- [ ] `index.php` 작성

### 1.4 Tailwind CSS 연동

#### 1.4.1 Tailwind 설치 (Node.js 방식)

```bash
# 프로젝트 루트에서
npm init -y
npm install -D tailwindcss
npx tailwindcss init
```

#### 1.4.2 Tailwind 설정

```javascript
// tailwind.config.js
module.exports = {
  content: [
    "./theme/morgan/**/*.php",
    "./theme/morgan/**/*.js",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Discord 스타일 다크 테마
        'mg-bg-primary': '#1e1f22',
        'mg-bg-secondary': '#2b2d31',
        'mg-bg-tertiary': '#313338',
        'mg-text-primary': '#f2f3f5',
        'mg-text-secondary': '#b5bac1',
        'mg-text-muted': '#949ba4',
        'mg-accent': '#f59f0a',
        'mg-accent-hover': '#d97706',
      }
    }
  }
}
```

#### 1.4.3 소스 CSS 파일

```css
/* src/css/input.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* CSS 변수 (테마 시스템용) */
:root {
  --mg-bg-primary: #1e1f22;
  --mg-bg-secondary: #2b2d31;
  --mg-bg-tertiary: #313338;
  --mg-text-primary: #f2f3f5;
  --mg-text-secondary: #b5bac1;
  --mg-text-muted: #949ba4;
  --mg-accent: #f59f0a;
  --mg-accent-hover: #d97706;
}
```

#### 1.4.4 빌드 스크립트

```json
// package.json scripts
{
  "scripts": {
    "css:build": "tailwindcss -i ./src/css/input.css -o ./theme/morgan/css/style.css",
    "css:watch": "tailwindcss -i ./src/css/input.css -o ./theme/morgan/css/style.css --watch"
  }
}
```

- [ ] Node.js/npm 설치 확인
- [ ] Tailwind CSS 설치
- [ ] tailwind.config.js 설정
- [ ] input.css 작성
- [ ] 빌드 테스트
- [ ] package.json scripts 설정

### 1.5 기본 레이아웃 구현 (디스코드 스타일)

> 참고: [PLAN_UI.md - 레이아웃 구조](./PLAN_UI.md)

```
┌─────────────────────────────────────────────────────┐
│  Header (로고, 검색, 유저 메뉴)                      │
├────────┬────────────────────────────────────────────┤
│        │                                            │
│ 사이드 │  Main Content Area                         │
│  바    │  (fetch 기반 동적 로딩)                     │
│        │                                            │
│ 56px   │                                            │
│        │                                            │
├────────┴────────────────────────────────────────────┤
│  Footer (선택적)                                     │
└─────────────────────────────────────────────────────┘
```

- [ ] 헤더 컴포넌트 구현
- [ ] 좌측 사이드바 구현 (56px 고정폭, 아이콘 메뉴)
- [ ] 메인 콘텐츠 영역 구현
- [ ] 반응형 처리 (모바일: 사이드바 숨김/토글)
- [ ] 다크 테마 기본 적용

### 1.6 fetch 기반 비동기 통신 구조

> 참고: [PLAN_API.md](./PLAN_API.md)

#### 1.6.1 API 엔드포인트 구조

```
/api/
├── index.php           # API 라우터
├── config.php          # API 설정
├── auth.php            # 인증 처리
├── member/             # 회원 API
├── board/              # 게시판 API
├── character/          # 캐릭터 API
└── common/             # 공통 API
```

#### 1.6.2 기본 API 응답 형식

```php
// 성공
{
  "success": true,
  "data": { ... },
  "message": "처리 완료"
}

// 실패
{
  "success": false,
  "error": {
    "code": "AUTH_REQUIRED",
    "message": "로그인이 필요합니다"
  }
}
```

#### 1.6.3 프론트엔드 fetch 래퍼

```javascript
// js/api.js
const API = {
  async request(endpoint, options = {}) {
    const response = await fetch(`/api/${endpoint}`, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      ...options
    });
    return response.json();
  },

  get: (endpoint) => API.request(endpoint),
  post: (endpoint, data) => API.request(endpoint, {
    method: 'POST',
    body: JSON.stringify(data)
  })
};
```

- [ ] `/api/` 폴더 구조 생성
- [ ] API 라우터 (index.php) 구현
- [ ] 기본 응답 헬퍼 함수 작성
- [ ] 프론트엔드 API 래퍼 작성
- [ ] CSRF 토큰 처리 구현

---

## Phase 2: 핵심 기능

### 2.1 회원 시스템 간소화

> 참고: [PLAN_MEMBER.md](./PLAN_MEMBER.md)

#### 2.1.1 회원가입 간소화

- [ ] 필수 필드만 남기기: 아이디, 비밀번호, 닉네임
- [ ] 이메일/휴대폰 필드 제거 또는 선택으로 변경
- [ ] 본인인증 로직 제거
- [ ] CAPTCHA 연동 (reCAPTCHA v3 또는 hCaptcha)
- [ ] 회원가입 스킨 제작 (`/theme/morgan/skin/member/`)

#### 2.1.2 로그인 시스템

- [ ] 로그인 스킨 제작
- [ ] 자동 로그인(Remember me) 구현
- [ ] 로그인 실패 제한 (5회 실패 시 5분 대기)
- [ ] 세션 관리 강화

#### 2.1.3 회원 API

```
POST /api/member/register    # 회원가입
POST /api/member/login       # 로그인
POST /api/member/logout      # 로그아웃
GET  /api/member/me          # 내 정보
PUT  /api/member/me          # 정보 수정
```

- [ ] 회원가입 API
- [ ] 로그인/로그아웃 API
- [ ] 내 정보 조회/수정 API

### 2.2 커스텀 테이블 생성

> 참고: [PLAN_DB.md](./PLAN_DB.md)

```sql
-- 기본 테이블 우선 생성
-- 1. 캐릭터 관련
CREATE TABLE mg_character (...);
CREATE TABLE mg_character_field (...);
CREATE TABLE mg_character_data (...);

-- 2. 포인트 관련 (그누보드 확장)
-- g5_point 테이블 활용, 필요시 확장

-- 3. 테마/설정
CREATE TABLE mg_theme (...);
CREATE TABLE mg_config (...);
```

- [ ] mg_character 테이블 생성
- [ ] mg_character_field 테이블 생성
- [ ] mg_character_data 테이블 생성
- [ ] mg_theme 테이블 생성
- [ ] mg_config 테이블 생성
- [ ] 외래키 및 인덱스 설정

### 2.3 게시판 시스템

> 참고: [PLAN_BOARD.md](./PLAN_BOARD.md)

#### 2.3.1 게시판 스킨 제작

```
theme/morgan/skin/board/
├── log/            # 로그 게시판 스킨
│   ├── list.php
│   ├── view.php
│   ├── write.php
│   └── comment.php
└── roleplay/       # 역극 게시판 스킨
    ├── list.php
    ├── view.php
    └── write.php
```

- [ ] 로그 게시판 스킨 - 목록
- [ ] 로그 게시판 스킨 - 보기
- [ ] 로그 게시판 스킨 - 글쓰기
- [ ] 역극 게시판 스킨 - 목록
- [ ] 역극 게시판 스킨 - 보기 (릴레이 형식)
- [ ] 역극 게시판 스킨 - 글쓰기 (캐릭터 선택)

#### 2.3.2 게시판 API

```
GET  /api/board/{bo_table}/list
GET  /api/board/{bo_table}/view/{wr_id}
POST /api/board/{bo_table}/write
PUT  /api/board/{bo_table}/update/{wr_id}
DELETE /api/board/{bo_table}/delete/{wr_id}
```

- [ ] 게시글 목록 API
- [ ] 게시글 상세 API
- [ ] 게시글 작성 API
- [ ] 게시글 수정 API
- [ ] 게시글 삭제 API

### 2.4 관리자 페이지 기본

> 참고: [PLAN_ADMIN.md](./PLAN_ADMIN.md)

- [ ] 관리자 레이아웃 (기존 그누보드 스타일 유지, 최소 수정)
- [ ] Morgan Edition 설정 메뉴 추가
- [ ] 캐릭터 관리 메뉴 추가
- [ ] 테마 설정 메뉴 추가

---

## Phase 3: 캐릭터 시스템

> 참고: [PLAN_CHARACTER.md](./PLAN_CHARACTER.md)

### 3.1 캐릭터 CRUD

#### 3.1.1 캐릭터 기본 기능

- [ ] 캐릭터 생성 페이지
- [ ] 캐릭터 목록 페이지 (내 캐릭터)
- [ ] 캐릭터 상세 페이지 (프로필)
- [ ] 캐릭터 수정 페이지
- [ ] 캐릭터 삭제 기능
- [ ] 대표 캐릭터 설정

#### 3.1.2 캐릭터 API

```
GET  /api/character/list           # 내 캐릭터 목록
GET  /api/character/{ch_id}        # 캐릭터 상세
POST /api/character/create         # 캐릭터 생성
PUT  /api/character/{ch_id}        # 캐릭터 수정
DELETE /api/character/{ch_id}      # 캐릭터 삭제
PUT  /api/character/{ch_id}/main   # 대표 캐릭터 설정
```

- [ ] 캐릭터 CRUD API 구현
- [ ] 이미지 업로드 처리
- [ ] 캐릭터 권한 검증 (본인 캐릭터만 수정 가능)

### 3.2 가변 프로필 양식

#### 3.2.1 프로필 양식 관리 (관리자)

- [ ] 프로필 필드 CRUD (mg_character_field)
- [ ] 필드 타입 지원: text, textarea, select, number, date, image
- [ ] 필드 순서 드래그 정렬
- [ ] 필드 그룹화 (기본정보, 외형, 성격 등)
- [ ] 필수/선택 설정

#### 3.2.2 프로필 입력 (유저)

- [ ] 동적 폼 렌더링
- [ ] 필드 타입별 입력 컴포넌트
- [ ] 실시간 유효성 검사
- [ ] 이미지 미리보기

### 3.3 샘플 데이터 자동 삽입

```php
// 설치 시 또는 초기화 시 실행
function mg_insert_sample_data() {
  // 샘플 프로필 필드
  // 샘플 테마
  // 샘플 게시판 그룹
}
```

- [ ] 샘플 프로필 필드 데이터 정의
- [ ] 샘플 데이터 삽입 함수 구현
- [ ] 설치 과정에서 자동 실행 연동

### 3.4 캐릭터-게시판 연동

- [ ] 글 작성 시 캐릭터 선택 UI
- [ ] 역극 게시판: 캐릭터 필수 선택
- [ ] 로그 게시판: 캐릭터 선택 (선택사항)
- [ ] 게시글에 캐릭터 정보 표시

---

## Phase 4: 고도화

### 4.1 포인트 시스템 확장

> 참고: [PLAN_POINT.md](./PLAN_POINT.md)

- [ ] 출석체크 기능
- [ ] 포인트 내역 페이지
- [ ] 포인트 랭킹

### 4.2 Supabase 실시간 연동

> 참고: [PLAN_API.md - 실시간 통신](./PLAN_API.md)

- [ ] Supabase 프로젝트 생성
- [ ] 환경 설정 (API Key, URL)
- [ ] 실시간 알림 기능
- [ ] 접속자 목록 표시

### 4.3 상점/인벤토리 시스템

> 참고: [PLAN_SHOP.md](./PLAN_SHOP.md)

- [ ] 상점 페이지
- [ ] 인벤토리 페이지
- [ ] 아이템 구매/사용

### 4.4 게임 요소 추가

- [ ] 칭호 시스템
- [ ] 레벨/경험치 시스템
- [ ] (선택) 진영/클래스 시스템

### 4.5 성능 최적화

- [ ] 쿼리 최적화
- [ ] 캐싱 적용 (Redis 또는 파일 캐시)
- [ ] 이미지 최적화 (썸네일 자동 생성)
- [ ] CDN 연동 검토

### 4.6 배포 준비

- [ ] 실서버 환경 설정
- [ ] HTTPS 설정
- [ ] 백업 정책 수립
- [ ] 모니터링 설정

---

## Phase 5: SS Engine (2차 개발)

> 참고: [PLAN_SS_ENGINE.md](./PLAN_SS_ENGINE.md)

별도 문서 참고. 킬러 콘텐츠로 우선순위 높음.

---

## 부록: 자주 사용하는 명령어

### Docker

```bash
# 시작
docker-compose up -d

# 중지
docker-compose down

# 로그 확인
docker-compose logs -f php

# PHP 컨테이너 접속
docker exec -it morgan_php bash

# MySQL 접속
docker exec -it morgan_mysql mysql -u morgan_user -p morgan_db
```

### Tailwind CSS

```bash
# 개발 중 (watch 모드)
npm run css:watch

# 빌드
npm run css:build
```

### Git (나중에 설정 시)

```bash
# 커밋
git add . && git commit -m "메시지"

# 브랜치 생성
git checkout -b feature/기능명
```

---

## 부록: 파일 구조 최종 목표

```
new_cms/
├── api/                    # API 엔드포인트
│   ├── index.php
│   ├── member/
│   ├── board/
│   ├── character/
│   └── common/
├── theme/
│   └── morgan/             # Morgan Edition 테마
│       ├── head.php
│       ├── tail.php
│       ├── css/
│       ├── js/
│       ├── layouts/
│       ├── skin/
│       └── img/
├── plugin/
│   └── morgan/             # Morgan Edition 플러그인 (핵심 로직)
│       ├── lib/            # 라이브러리
│       ├── install/        # 설치 스크립트
│       └── config.php
├── extend/
│   └── morgan.extend.php   # 그누보드 확장
├── src/                    # 소스 (빌드 전)
│   └── css/
│       └── input.css
├── docs/                   # 기획 문서
├── docker/                 # Docker 설정
└── docker-compose.yml
```

---

*작성일: 2026-02-03*
*버전: 1.0*
*상태: Phase 1 진행 준비*
