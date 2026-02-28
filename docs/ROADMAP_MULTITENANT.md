# Morgan Edition - 멀티테넌트 + Cloudflare R2 로드맵

> 작성일: 2026-03-01
> 최종 업데이트: 2026-03-01
> 1차 로드맵: [`docs/ROADMAP.md`](ROADMAP.md)
> 2차 로드맵: [`docs/ROADMAP_PHASE2.md`](ROADMAP_PHASE2.md)

---

## 개요

### 비전

Morgan Edition CMS를 **세미 SaaS**로 전환한다.
기존 VPS(moonveil.org 운영 중)에 코드베이스 1벌만 올려두고, 서브도메인 기반으로 테넌트(커뮤니티)를 분리한다.
각 테넌트는 독립 DB를 가지며, 파일 업로드는 Cloudflare R2로 통합 관리한다.

### 인프라 현황

| 항목 | 현재 상태 |
|------|----------|
| VPS | 기존 서버 (moonveil.org + api.moonveil.org 운영 중) |
| 웹서버 | nginx + PHP-FPM |
| DNS/CDN | Cloudflare (moonveil.org 이미 연동) |
| SSL | moonveil.org → Cloudflare Proxied (Flexible) / api.moonveil.org → Let's Encrypt |
| 도메인 | morgan 포함 도메인 별도 구매 예정 (Cloudflare Registrar 권장) |

### 라우팅 방식

**서브도메인 + Cloudflare Proxied** 방식 채택:

```
*.{도메인} → Cloudflare Proxied (주황 구름) → VPS IP
```

- Cloudflare가 와일드카드 SSL 자동 제공 (Free 플랜 포함)
- Let's Encrypt 와일드카드 인증서 불필요
- DNS 레코드 1개 (`*.{도메인}` A 레코드)만 추가하면 즉시 동작
- VPS nginx는 HTTP(80)만 수신, Cloudflare가 SSL 종단

### 목표

1. **베타 테스터 모집** — 2~3개 커뮤니티에 무료 제공, 피드백 수집
2. **운영 비용 최소화** — 단일 서버, 공유 코드베이스, R2 스토리지
3. **테넌트 격리** — 데이터/파일/세션 완전 분리, 보안 보장
4. **기존 기능 유지** — 1차 Phase(1~18) + 검수 완료 기능 퇴행 없음

### 현재 구조 vs 목표 구조

```
[현재] 단일 사이트 (Cafe24)           [목표] 멀티테넌트 SaaS (기존 VPS)
┌──────────────────────┐             ┌──────────────────────────────────┐
│  moonveil.org        │             │    *.{도메인} (Cloudflare Proxied)│
│  ┌────────────────┐  │             │  ┌──────┐ ┌──────┐ ┌──────┐    │
│  │  morgan_db      │  │             │  │ DB-A │ │ DB-B │ │ DB-C │    │
│  │  (전체 데이터)   │  │     →      │  └──┬───┘ └──┬───┘ └──┬───┘    │
│  └────────────────┘  │             │     │        │        │         │
│  ┌────────────────┐  │             │  ┌──┴────────┴────────┴───┐     │
│  │  /data/         │  │             │  │   Cloudflare R2        │     │
│  │  (로컬 파일)     │  │             │  │   /tenant-a/...        │     │
│  └────────────────┘  │             │  │   /tenant-b/...        │     │
└──────────────────────┘             │  └────────────────────────┘     │
                                     └──────────────────────────────────┘
```

> **{도메인}** = morgan 포함 도메인 구매 예정 (예: `morgan.site`, `morgan-cms.com` 등)
> Cloudflare Registrar에서 원가 구매 → DNS 즉시 연동

---

## 아키텍처

### 요청 흐름

```
[브라우저] ──── alpha.{도메인} ────┐
[브라우저] ──── beta.{도메인}  ────┤
[브라우저] ──── gamma.{도메인} ────┘
                                   │
                                   ▼
                           ┌────────────────┐
                           │  Cloudflare    │
                           │  Proxied (주황) │
                           │  SSL 자동 처리  │
                           └───────┬────────┘
                                   │ HTTP(80)
                                   ▼
                           ┌───────────────┐
                           │  nginx (VPS)   │
                           │  server_name   │
                           │  *.{도메인}     │
                           └───────┬───────┘
                                      │
                                      ▼
                              ┌───────────────┐
                              │  PHP-FPM       │
                              │  common.php    │
                              │       │        │
                              │       ▼        │
                              │  tenant_       │
                              │  bootstrap.php │
                              │       │        │
                              │  ┌────┴────┐   │
                              │  │ 서브도메인│   │
                              │  │ → 테넌트 │   │
                              │  │   ID     │   │
                              │  └────┬────┘   │
                              │       │        │
                              │  ┌────┴────┐   │
                              │  │$g5 DB   │   │
                              │  │연결 교체 │   │
                              │  └────┬────┘   │
                              │       │        │
                              │  ┌────┴────┐   │
                              │  │MG_Storage│  │
                              │  │(R2/로컬) │   │
                              │  └─────────┘   │
                              └────────────────┘
                                      │
                          ┌───────────┼───────────┐
                          ▼           ▼           ▼
                    ┌──────────┐ ┌──────────┐ ┌──────────┐
                    │ MySQL    │ │ MySQL    │ │ MySQL    │
                    │ tenant_a │ │ tenant_b │ │ tenant_c │
                    └──────────┘ └──────────┘ └──────────┘
                                      │
                                      ▼
                              ┌───────────────┐
                              │ Cloudflare R2  │
                              │ ┌───────────┐  │
                              │ │ /a/char/  │  │
                              │ │ /a/emot/  │  │
                              │ │ /b/char/  │  │
                              │ │ /b/emot/  │  │
                              │ └───────────┘  │
                              └───────────────┘
```

### 테넌트 식별 흐름

```
HTTP_HOST = "alpha.{도메인}"
         │
         ▼
  서브도메인 추출: "alpha"
         │
         ▼
  mg_master.tenants 조회
  WHERE subdomain = 'alpha'
  AND status = 'active'
         │
         ├─ 없음 → 404 또는 온보딩 페이지
         │
         └─ 있음 → tenant_id, db_name, db_user, db_pass
                  │
                  ▼
           $g5['connect_db'] 교체
           MG_Storage 초기화
           세션 네임스페이스 설정
```

---

## Phase 요약

| ID | Phase | 한줄 요약 | 예상 규모 | 의존성 |
|----|-------|----------|-----------|--------|
| MT-0 | 테넌트 라우터 | 서브도메인 → 테넌트 식별 + DB 전환 | 중 | 없음 |
| MT-1 | DB 격리 | 정적 캐시 격리, 세션 분리, 경로 분리 | 중 | MT-0 |
| MT-2 | 스토리지 추상화 | MG_Storage 클래스 + Cloudflare R2 드라이버 | 중~대 | MT-0 |
| MT-3 | 프로비저닝 | 슈퍼 관리자 + 테넌트 자동 생성/관리 | 중 | MT-0, MT-1 |
| MT-4 | 보안 & 격리 | 파일/세션/리소스 격리 검증 + 제한 | 소~중 | MT-1, MT-2 |
| MT-5 | 베타 배포 | VPS 구성, 온보딩 마법사, 모니터링 | 중 | MT-0~4 |

**범례**
- [x] 완료
- [-] 부분 구현
- [ ] 미구현

---

## Phase MT-0: 테넌트 라우터

> 서브도메인에서 테넌트를 식별하고, 해당 테넌트의 DB로 연결을 교체한다.
> 이 Phase가 전체 멀티테넌트의 기반이며, 가장 먼저 구현해야 한다.

### MT-0.1 마스터 DB & 테넌트 레지스트리

- [ ] 마스터 DB (`mg_master`) 스키마 설계
- [ ] `mg_master.tenants` 테이블 생성 (id, subdomain, db_name, db_user, db_pass, status, plan, created_at, ...)
- [ ] `mg_master.tenant_config` 테이블 생성 (tenant_id, key, value)
- [ ] `mg_master.super_admins` 테이블 생성 (id, username, password_hash, ...)
- [ ] 마스터 DB는 `data/dbconfig_master.php`에 별도 연결 정보 저장

### MT-0.2 테넌트 부트스트랩

- [ ] `plugin/morgan/tenant_bootstrap.php` 작성
- [ ] `$_SERVER['HTTP_HOST']`에서 서브도메인 추출 로직
- [ ] 마스터 DB에서 테넌트 정보 조회 (캐싱: APCu 또는 파일 기반)
- [ ] 테넌트 미존재 시 404 / 온보딩 리다이렉트 분기
- [ ] 테넌트 비활성(suspended/deleted) 시 안내 페이지 표시
- [ ] `common.php` 수정 — dbconfig 로드 직전(line 154)에 부트스트랩 삽입

### MT-0.3 DB 연결 동적 교체

- [ ] 테넌트별 `sql_connect()` 호출로 `$g5['connect_db']` 교체
- [ ] 기존 `data/dbconfig.php`의 `define()` 상수 우회 (부트스트랩에서 먼저 정의)
- [ ] `sql_*` 함수 호환성 검증 — `$link` 파라미터 기본값이 `$g5['connect_db']`이므로 교체만으로 동작
- [ ] 테넌트 컨텍스트 전역 변수 설정 (`$mg_tenant_id`, `$mg_tenant_config`)

### MT-0.4 단일 테넌트 호환 모드

- [ ] 멀티테넌트 미설정 시 기존처럼 동작하는 폴백 로직
- [ ] `MG_MULTITENANT_ENABLED` 상수로 on/off 전환
- [ ] 기존 로컬 개발 환경(Docker) 퇴행 방지

### 수정 대상 파일

| 파일 | 수정 내용 |
|------|----------|
| `common.php` (line 154) | 부트스트랩 include 삽입 |
| `data/dbconfig.php` | 멀티테넌트 모드 감지 로직 추가 |

### 신규 파일

| 파일 | 역할 |
|------|------|
| `plugin/morgan/tenant_bootstrap.php` | 테넌트 식별 + DB 연결 교체 |
| `data/dbconfig_master.php` | 마스터 DB 연결 정보 |

---

## Phase MT-1: DB 격리 & 캐시 분리

> 테넌트 전환 후 발생할 수 있는 데이터 오염을 방지한다.
> 정적 캐시, 세션, 파일 경로를 테넌트별로 분리한다.

### MT-1.1 정적 캐시 격리

- [ ] `mg_config()` 함수 (morgan.php line 538) — `static $cache` → 테넌트 ID 키 적용
- [ ] `get_config()` 함수 (common.lib.php) — 동일 패턴 적용
- [ ] `mg_get_config()` / `mg_set_config()` — 캐시 무효화 로직 추가
- [ ] 기타 `static $cache` 패턴 사용 함수 전수 조사 및 수정

### MT-1.2 세션 격리

- [ ] 세션 이름 테넌트별 분리 (`session_name('MG_' . $tenant_id)`)
- [ ] 세션 쿠키 도메인을 서브도메인별로 설정
- [ ] 세션 저장 경로 분리 (선택: `session_save_path`)
- [ ] 로그인 상태가 테넌트 간 공유되지 않는지 검증

### MT-1.3 파일 경로 분리

- [ ] 테넌트별 data 디렉토리 구조: `data/tenants/{tenant_id}/character/`, `data/tenants/{tenant_id}/emoticon/` 등
- [ ] Morgan 경로 상수를 `define()` 대신 전역 변수로 전환 (또는 부트스트랩에서 먼저 정의)
- [ ] 경로 상수 전환 대상 (morgan.php):

| 상수 | 라인 | 멀티테넌트 경로 |
|------|------|----------------|
| `MG_CHAR_IMAGE_PATH` | 109 | `data/tenants/{id}/character` |
| `MG_CHAR_IMAGE_URL` | 110 | 동일 (또는 R2 URL) |
| `MG_SHOP_IMAGE_PATH` | 204 | `data/tenants/{id}/shop` |
| `MG_EMOTICON_PATH` | 208 | `data/tenants/{id}/emoticon` |
| `MG_SEAL_IMAGE_PATH` | 212 | `data/tenants/{id}/seal` |
| `MG_LORE_IMAGE_PATH` | 216 | `data/tenants/{id}/lore` |
| `MG_PROMPT_IMAGE_PATH` | 220 | `data/tenants/{id}/prompt` |

- [ ] 그누보드 기본 경로 (`G5_DATA_PATH`) — 테넌트 오버라이드 불필요 (게시판 첨부파일은 DB 격리로 충분)

### MT-1.4 테이블 프리픽스

- [ ] 테넌트별 완전 분리 DB 방식 확정 (동일 프리픽스 `g5_` + `mg_` 사용)
- [ ] `$g5['write_prefix']` 유지 (테넌트 DB 내에서 동일 구조)
- [ ] install.sql이 각 테넌트 DB에 그대로 적용 가능한지 검증

### 수정 대상 파일

| 파일 | 수정 내용 |
|------|----------|
| `plugin/morgan/morgan.php` (line 538-551) | `mg_config()` 캐시 격리 |
| `plugin/morgan/morgan.php` (line 109-221) | 경로 상수 → 테넌트별 분기 |
| `lib/common.lib.php` | `get_config()` 캐시 격리 |
| `common.php` (line 210-401) | 세션 설정 테넌트 분기 |

---

## Phase MT-2: 스토리지 추상화 (Cloudflare R2)

> 파일 업로드를 로컬/R2 양쪽 지원하는 추상화 레이어를 구축한다.
> R2 활성화 시 모든 업로드가 Cloudflare R2로 전송되며, URL도 R2 퍼블릭 URL로 전환된다.

### MT-2.1 MG_Storage 인터페이스

- [ ] `plugin/morgan/storage/MG_Storage.php` — 추상 인터페이스 정의

```php
interface MG_StorageInterface {
    public function put($path, $file_or_data, $options = []);   // 업로드
    public function get($path);                                  // 다운로드 (바이너리)
    public function delete($path);                               // 삭제
    public function exists($path);                               // 존재 확인
    public function url($path);                                  // 공개 URL 반환
    public function move($from, $to);                            // 이동/리네임
}
```

- [ ] `MG_Storage` 팩토리 클래스 (설정에 따라 Local/R2 인스턴스 반환)
- [ ] 전역 `mg_storage()` 헬퍼 함수 (싱글턴 접근)

### MT-2.2 로컬 드라이버

- [ ] `plugin/morgan/storage/LocalStorage.php`
- [ ] 기존 `move_uploaded_file()` + `@unlink()` 패턴을 래핑
- [ ] 멀티테넌트 시 `data/tenants/{tenant_id}/` 경로 자동 적용
- [ ] 단일 테넌트 시 기존 `data/` 경로 유지

### MT-2.3 Cloudflare R2 드라이버

- [ ] `plugin/morgan/storage/R2Storage.php`
- [ ] S3 호환 API 사용 (AWS SDK 불필요, 순수 HTTP + 서명)
- [ ] 필수 오퍼레이션: `PutObject`, `DeleteObject`, `HeadObject`, `GetObject`
- [ ] AWS Signature V4 서명 구현 (또는 경량 라이브러리 도입)
- [ ] R2 키 경로 구조: `{tenant_id}/{resource_type}/{filename}`
- [ ] 퍼블릭 버킷 URL 또는 R2 커스텀 도메인 지원

### MT-2.4 관리자 설정 UI

- [ ] `adm/morgan/storage_config.php` — R2 설정 페이지
- [ ] 설정 항목:
  - `mg_storage_driver` (local / r2)
  - `mg_r2_account_id`
  - `mg_r2_access_key_id`
  - `mg_r2_secret_access_key`
  - `mg_r2_bucket_name`
  - `mg_r2_endpoint` (자동 생성: `https://{account_id}.r2.cloudflarestorage.com`)
  - `mg_r2_public_url` (커스텀 도메인 또는 R2 퍼블릭 URL)
- [ ] 연결 테스트 버튼 (HeadBucket API 호출)
- [ ] `config_update.php`의 `$config_keys` 배열에 키 추가

### MT-2.5 업로드 포인트 전환

기존 15개 업로드 포인트를 `MG_Storage` 인터페이스로 전환:

| 구분 | 업로드 포인트 | 현재 함수/위치 | 전환 방식 |
|------|-------------|---------------|----------|
| 1 | 캐릭터 이미지 | `mg_upload_character_image()` (line 358) | 헬퍼 내부 교체 |
| 2 | 인장 이미지 | `mg_upload_seal_image()` (line 7175) | 헬퍼 내부 교체 |
| 3 | 세계관 위키 이미지 | `mg_upload_lore_image()` (line 7420) | 헬퍼 내부 교체 |
| 4 | 미션 배너 | `mg_upload_prompt_banner()` (line 7766) | 헬퍼 내부 교체 |
| 5 | 아이콘 (소속/종족) | `mg_handle_icon_upload()` (line 2452) | 헬퍼 내부 교체 |
| 6 | 이모티콘 (관리자) | adm/morgan/ 직접 업로드 | 개별 수정 |
| 7 | 이모티콘 (유저 제작) | bbs/emoticon 직접 업로드 | 개별 수정 |
| 8 | 상점 아이템 | adm/morgan/shop 직접 업로드 | 개별 수정 |
| 9 | 상점 카테고리 | adm/morgan/shop_category 직접 업로드 | 개별 수정 |
| 10 | 메인 위젯 | adm/morgan/main_builder 직접 업로드 | 개별 수정 |
| 11 | 세계관 맵 | adm/morgan/lore_map 직접 업로드 | 개별 수정 |
| 12 | 개척 재료/시설 | adm/morgan/pioneer 직접 업로드 | 개별 수정 |
| 13 | 탐색 파견 | adm/morgan/expedition 직접 업로드 | 개별 수정 |
| 14 | 에디터 (ToastUI) | plugin/editor/toastui 직접 업로드 | 개별 수정 |
| 15 | 게시판 첨부파일 | bbs/write_update.php (그누보드 코어) | 그누보드 훅 또는 직접 수정 |

- [ ] 헬퍼 함수 내부 교체 (1~5번 — 5개, 가장 효율적)
- [ ] 관리자 직접 업로드 교체 (6~13번 — 8개)
- [ ] 에디터/게시판 교체 (14~15번 — 2개, 그누보드 코어 연동 주의)

### MT-2.6 URL 출력 전환

- [ ] 로컬 모드: 기존 `MG_CHAR_IMAGE_URL . '/' . $filename` 유지
- [ ] R2 모드: `mg_storage()->url($path)` → R2 퍼블릭 URL 반환
- [ ] DB에 저장된 기존 경로(`/data/character/...`)와의 호환성 처리
- [ ] `mg_file_url($path)` 헬퍼 함수 추가 (스토리지 드라이버에 따라 URL 자동 분기)

### MT-2.7 삭제 전환

- [ ] 기존 `@unlink()` 호출 (20+곳) → `mg_storage()->delete()` 전환
- [ ] 삭제 실패 시 로그 기록 (R2 네트워크 오류 대비)

### MT-2.8 마이그레이션 도구

- [ ] 기존 로컬 파일 → R2 일괄 업로드 CLI 스크립트
- [ ] `adm/morgan/storage_migrate.php` — 관리자 UI에서 마이그레이션 실행
- [ ] 진행률 표시 (AJAX 폴링)
- [ ] 실패 파일 재시도 로직

### 신규 파일

| 파일 | 역할 |
|------|------|
| `plugin/morgan/storage/MG_Storage.php` | 인터페이스 + 팩토리 |
| `plugin/morgan/storage/LocalStorage.php` | 로컬 드라이버 |
| `plugin/morgan/storage/R2Storage.php` | R2 드라이버 |
| `plugin/morgan/storage/S3Signature.php` | AWS Sig V4 서명 유틸 |
| `adm/morgan/storage_config.php` | R2 설정 UI |
| `adm/morgan/storage_config_update.php` | R2 설정 저장 |
| `adm/morgan/storage_migrate.php` | 마이그레이션 UI |

---

## Phase MT-3: 슈퍼 관리자 & 프로비저닝

> 테넌트를 생성/관리하는 슈퍼 관리자 패널.
> 새 테넌트 생성 시 DB 자동 생성 + 기본 데이터 INSERT + 관리자 계정 생성을 자동화한다.

### MT-3.1 슈퍼 관리자 인증

- [ ] 슈퍼 관리자 로그인 페이지 (`adm/super/login.php`)
- [ ] 마스터 DB `super_admins` 테이블 기반 인증
- [ ] 일반 관리자 (`/adm/`)와 완전 분리된 세션
- [ ] 슈퍼 관리자 접근은 특정 URL (`/adm/super/`) 또는 특정 서브도메인 (`admin.{도메인}`)

### MT-3.2 테넌트 목록 & CRUD

- [ ] 테넌트 목록 페이지 (상태, 생성일, DB명, 회원 수, 저장 용량)
- [ ] 테넌트 생성 폼 (서브도메인, 관리자 이메일, 플랜 선택)
- [ ] 테넌트 정지/활성화/삭제
- [ ] 테넌트 설정 편집 (플랜, 리소스 제한, 커스텀 도메인)

### MT-3.3 자동 프로비저닝

테넌트 생성 시 자동 실행되는 프로세스:

```
[테넌트 생성 버튼 클릭]
    │
    ├── 1. MySQL DB 생성
    │   CREATE DATABASE mg_tenant_{id}
    │   CREATE USER 'mg_t_{id}'@'%'
    │   GRANT ALL ON mg_tenant_{id}.*
    │
    ├── 2. 스키마 초기화
    │   install.sql 실행 (65개 테이블)
    │   그누보드 기본 테이블 생성 (45개)
    │   기본 설정 INSERT
    │
    ├── 3. 관리자 계정 생성
    │   g5_member INSERT (level 10)
    │   초기 비밀번호 생성 + 이메일 발송
    │
    ├── 4. 기본 데이터 INSERT
    │   기본 게시판 (공지, QA 등)
    │   기본 상점 카테고리
    │   기본 이모티콘 세트 (공용)
    │   기본 위키 카테고리
    │
    ├── 5. 파일 디렉토리 생성
    │   data/tenants/{id}/character/
    │   data/tenants/{id}/emoticon/
    │   data/tenants/{id}/...
    │
    └── 6. 마스터 DB 레코드 INSERT
        tenants 테이블에 신규 레코드
```

- [ ] `plugin/morgan/tenant/TenantManager.php` — 프로비저닝 로직
- [ ] `plugin/morgan/install/install.sql` 활용 (기존 파일 재사용)
- [ ] 그누보드 기본 테이블 스키마 추출 + 별도 SQL 파일
- [ ] 기본 데이터 시드 SQL (`plugin/morgan/install/seed.sql`)
- [ ] 프로비저닝 진행률 API (AJAX)

### MT-3.4 테넌트 모니터링

- [ ] 테넌트별 통계 대시보드 (회원 수, 게시글 수, 저장 용량)
- [ ] 최근 활동 로그
- [ ] 에러 로그 (테넌트별 PHP 에러 분리)
- [ ] 리소스 사용량 경고 (저장 용량 80% 초과 시)

### 신규 파일

| 파일 | 역할 |
|------|------|
| `adm/super/login.php` | 슈퍼 관리자 로그인 |
| `adm/super/index.php` | 슈퍼 관리자 대시보드 |
| `adm/super/tenants.php` | 테넌트 목록 |
| `adm/super/tenant_form.php` | 테넌트 생성/편집 |
| `adm/super/tenant_update.php` | 테넌트 CRUD 처리 |
| `adm/super/tenant_stats.php` | 테넌트 통계 |
| `plugin/morgan/tenant/TenantManager.php` | 프로비저닝 로직 |
| `plugin/morgan/tenant/TenantConfig.php` | 테넌트 설정 관리 |
| `plugin/morgan/install/seed.sql` | 기본 데이터 시드 |
| `plugin/morgan/install/gnuboard_schema.sql` | 그누보드 기본 테이블 |

---

## Phase MT-4: 보안 & 격리

> 테넌트 간 데이터 유출을 방지하고, 리소스 남용을 차단한다.

### MT-4.1 파일시스템 격리

- [ ] 테넌트 A의 파일에 테넌트 B가 URL로 직접 접근 불가 확인
- [ ] nginx 설정으로 `/data/tenants/{id}/` 경로 접근 시 테넌트 검증
- [ ] 심볼릭 링크 탈출 방지
- [ ] 업로드 시 경로 검증 (path traversal 차단)

### MT-4.2 세션 & 쿠키 격리

- [ ] 세션 쿠키 도메인: `.alpha.{도메인}` (서브도메인별)
- [ ] 세션 ID 네임스페이스 분리
- [ ] CSRF 토큰 테넌트별 독립
- [ ] 크로스 테넌트 세션 하이재킹 방지 테스트

### MT-4.3 리소스 제한

- [ ] 테넌트별 저장 용량 상한 (기본 1GB, 플랜별 차등)
- [ ] 테넌트별 회원 수 상한 (기본 100명, 플랜별 차등)
- [ ] 테넌트별 게시판 수 상한
- [ ] 업로드 파일 크기 제한 (테넌트 설정 연동)
- [ ] 제한 초과 시 안내 메시지 + 슈퍼 관리자 알림

### MT-4.4 네트워크 보안

- [ ] CORS 헤더 테넌트별 설정 (Origin 제한)
- [ ] CSP 헤더 R2 도메인 허용
- [ ] Rate limiting (nginx level, 테넌트별 가중치 선택적)

### MT-4.5 데이터 격리 검증

- [ ] 테넌트 A 로그인 → 테넌트 B API 호출 시 차단 확인
- [ ] SQL injection을 통한 크로스 테넌트 접근 불가 확인 (DB 분리이므로 기본 차단)
- [ ] R2 경로 조작을 통한 타 테넌트 파일 접근 불가 확인

---

## Phase MT-5: 베타 배포 인프라

> 기존 VPS + Cloudflare Proxied 기반 배포. 테넌트 온보딩 플로우, 모니터링, 백업 전략.

### MT-5.1 도메인 & DNS 구성

- [ ] morgan 포함 도메인 구매 (Cloudflare Registrar 권장, 원가 판매)
- [ ] Cloudflare에 도메인 추가 → 네임서버 연결
- [ ] DNS 레코드 추가:

| Type | Name | Content | Proxy |
|------|------|---------|-------|
| A | `@` | VPS IP | Proxied (주황) |
| A | `*` | VPS IP | Proxied (주황) |

- [ ] Cloudflare SSL/TLS 모드: **Flexible** (VPS nginx는 HTTP만 수신)
- [ ] Cloudflare Edge Certificate: 와일드카드 자동 발급 확인

### MT-5.2 기존 VPS nginx 설정

기존 VPS(moonveil.org 운영 중)에 server block 추가:

```nginx
# Morgan 멀티테넌트 (Cloudflare Proxied → HTTP 수신)
server {
    listen 80;
    server_name *.{도메인} {도메인};

    root /var/www/morgan;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht { deny all; }
    location ~ /data/tenants/ {
        # 테넌트 파일 접근 제어 (MT-4에서 상세 설정)
        internal;
    }
}
```

- [ ] nginx server block 추가 (기존 moonveil 설정과 공존)
- [ ] PHP-FPM 풀 설정 점검 (프로세스 수, 메모리 제한)
- [ ] MySQL max_connections 조정 (테넌트 수 × 동시접속 고려)
- [ ] 코드베이스 배치: `/var/www/morgan/`

### MT-5.3 배포 파이프라인

- [ ] GitHub Actions → VPS 배포 (git pull 또는 rsync)
- [ ] 배포 시 마이그레이션 자동 실행 (전체 테넌트 순회)
- [ ] 롤백 스크립트 (이전 커밋으로 git reset)
- [ ] 배포 알림 (Discord webhook 또는 이메일)
- [ ] 기존 Cafe24 배포(git-ftp)와 병행 가능 (1차 사이트는 Cafe24 유지)

### MT-5.4 테넌트 온보딩 플로우

```
[{도메인} 메인 랜딩 페이지]
    │
    ├── "시작하기" 버튼 클릭
    │
    ▼
[온보딩 마법사]
    │
    ├── Step 1: 커뮤니티 기본 정보
    │   - 커뮤니티 이름
    │   - 서브도메인 선택 (중복 검사)
    │   - 관리자 이메일
    │
    ├── Step 2: 테마 선택
    │   - 배경 이미지 (기본 제공 or 업로드)
    │   - 로고 업로드
    │   - 커뮤니티 소개
    │
    ├── Step 3: 기능 선택
    │   - 활성화할 시스템 체크박스
    │   - (역극, 캐릭터, 상점, 위키 등)
    │
    └── Step 4: 완료
        - DB 자동 생성 (30초~1분)
        - 관리자 초기 비밀번호 이메일 발송
        - "{subdomain}.{도메인}으로 접속하세요!" 안내
```

- [ ] `bbs/tenant_onboard.php` — 온보딩 마법사 UI
- [ ] `bbs/tenant_onboard_api.php` — 생성 API (AJAX)
- [ ] 이메일 발송 (PHP mail 또는 SMTP)
- [ ] 서브도메인 유효성 검사 (영문 소문자, 숫자, 하이픈만 허용)

### MT-5.5 모니터링

- [ ] 전체 시스템 헬스 체크 (cron, 5분 간격)
- [ ] 테넌트별 에러 로그 분리 (`logs/tenants/{id}/error.log`)
- [ ] 디스크 사용량 모니터링 + 경고
- [ ] MySQL 슬로우 쿼리 로그
- [ ] (선택) 외부 모니터링 서비스 연동 (UptimeRobot 등)

### MT-5.6 백업 전략

- [ ] 테넌트별 mysqldump (일일 cron)
- [ ] R2 버킷 버저닝 활성화 (Cloudflare 설정)
- [ ] 백업 보관 기간: 7일 (로테이션)
- [ ] 테넌트 복원 스크립트 (`scripts/restore_tenant.sh`)
- [ ] 슈퍼 관리자에서 수동 백업/복원 트리거

### MT-5.7 랜딩 페이지

- [ ] `{도메인}` 메인 랜딩 페이지 (정적 HTML)
- [ ] 기능 소개, 스크린샷, 베타 신청 CTA
- [ ] 기존 테넌트 사례 (베타 피드백)
- [ ] FAQ, 가격 안내 (베타: 무료)

### 신규 파일

| 파일 | 역할 |
|------|------|
| `bbs/tenant_onboard.php` | 온보딩 마법사 UI |
| `bbs/tenant_onboard_api.php` | 온보딩 API |
| `scripts/backup_tenants.sh` | 일일 백업 스크립트 |
| `scripts/restore_tenant.sh` | 테넌트 복원 |
| `scripts/deploy.sh` | 배포 스크립트 |
| `public/index.html` | 랜딩 페이지 |

---

## DB 변경사항

### 마스터 DB 스키마 (`mg_master`)

```sql
-- 테넌트 레지스트리
CREATE TABLE IF NOT EXISTS tenants (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subdomain   VARCHAR(63) NOT NULL UNIQUE,
    name        VARCHAR(200) NOT NULL,
    db_name     VARCHAR(64) NOT NULL,
    db_user     VARCHAR(32) NOT NULL,
    db_pass     VARCHAR(128) NOT NULL,
    status      ENUM('active','suspended','deleted') DEFAULT 'active',
    plan        ENUM('free','basic','pro') DEFAULT 'free',
    admin_email VARCHAR(200) NOT NULL,
    max_storage_mb  INT UNSIGNED DEFAULT 1024,    -- 저장 용량 상한 (MB)
    max_members     INT UNSIGNED DEFAULT 100,     -- 회원 수 상한
    r2_enabled      TINYINT(1) DEFAULT 0,
    custom_domain   VARCHAR(200) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subdomain (subdomain),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 테넌트별 추가 설정
CREATE TABLE IF NOT EXISTS tenant_config (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    cf_key      VARCHAR(100) NOT NULL,
    cf_value    TEXT,
    UNIQUE KEY uk_tenant_key (tenant_id, cf_key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 슈퍼 관리자
CREATE TABLE IF NOT EXISTS super_admins (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    email       VARCHAR(200) NOT NULL,
    last_login  DATETIME DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 프로비저닝 로그
CREATE TABLE IF NOT EXISTS provision_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    action      ENUM('create','suspend','activate','delete','backup','restore') NOT NULL,
    detail      TEXT,
    admin_id    INT UNSIGNED,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (admin_id) REFERENCES super_admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 테넌트 DB 스키마

각 테넌트 DB는 기존과 동일:
- 그누보드 기본 테이블 45개 (`g5_*`)
- Morgan 커스텀 테이블 65개 (`mg_*`)
- `install.sql` 그대로 실행

---

## 수정/생성 파일 전체 목록

### 신규 파일 (~25개)

| # | 파일 | Phase | 역할 |
|---|------|-------|------|
| 1 | `plugin/morgan/tenant_bootstrap.php` | MT-0 | 테넌트 식별 + DB 교체 |
| 2 | `data/dbconfig_master.php` | MT-0 | 마스터 DB 연결 정보 |
| 3 | `plugin/morgan/storage/MG_Storage.php` | MT-2 | 스토리지 인터페이스 + 팩토리 |
| 4 | `plugin/morgan/storage/LocalStorage.php` | MT-2 | 로컬 드라이버 |
| 5 | `plugin/morgan/storage/R2Storage.php` | MT-2 | R2 드라이버 |
| 6 | `plugin/morgan/storage/S3Signature.php` | MT-2 | AWS Sig V4 서명 |
| 7 | `adm/morgan/storage_config.php` | MT-2 | R2 설정 UI |
| 8 | `adm/morgan/storage_config_update.php` | MT-2 | R2 설정 저장 |
| 9 | `adm/morgan/storage_migrate.php` | MT-2 | 파일 마이그레이션 UI |
| 10 | `plugin/morgan/tenant/TenantManager.php` | MT-3 | 프로비저닝 로직 |
| 11 | `plugin/morgan/tenant/TenantConfig.php` | MT-3 | 테넌트 설정 관리 |
| 12 | `adm/super/login.php` | MT-3 | 슈퍼 관리자 로그인 |
| 13 | `adm/super/index.php` | MT-3 | 슈퍼 관리자 대시보드 |
| 14 | `adm/super/tenants.php` | MT-3 | 테넌트 목록 |
| 15 | `adm/super/tenant_form.php` | MT-3 | 테넌트 CRUD |
| 16 | `adm/super/tenant_update.php` | MT-3 | 테넌트 처리 |
| 17 | `adm/super/tenant_stats.php` | MT-3 | 테넌트 통계 |
| 18 | `plugin/morgan/install/seed.sql` | MT-3 | 기본 데이터 시드 |
| 19 | `plugin/morgan/install/gnuboard_schema.sql` | MT-3 | 그누보드 테이블 스키마 |
| 20 | `bbs/tenant_onboard.php` | MT-5 | 온보딩 마법사 |
| 21 | `bbs/tenant_onboard_api.php` | MT-5 | 온보딩 API |
| 22 | `scripts/backup_tenants.sh` | MT-5 | 백업 스크립트 |
| 23 | `scripts/restore_tenant.sh` | MT-5 | 복원 스크립트 |
| 24 | `scripts/deploy.sh` | MT-5 | 배포 스크립트 |
| 25 | `db/migrations/XXXXXXXX_XXXXXX_multitenant_master.sql` | MT-0 | 마스터 DB 마이그레이션 |

### 수정 파일 (~20개)

| # | 파일 | Phase | 수정 내용 |
|---|------|-------|----------|
| 1 | `common.php` | MT-0 | 부트스트랩 include 삽입 (line 154) |
| 2 | `data/dbconfig.php` | MT-0 | 멀티테넌트 모드 감지 로직 |
| 3 | `plugin/morgan/morgan.php` | MT-1,2 | 캐시 격리, 경로 분기, 스토리지 통합 |
| 4 | `lib/common.lib.php` | MT-1 | `get_config()` 캐시 격리 |
| 5 | `adm/morgan/config.php` | MT-2 | 스토리지 설정 섹션 추가 |
| 6 | `adm/morgan/config_update.php` | MT-2 | 스토리지 키 추가 |
| 7 | `adm/admin.menu800.php` | MT-2,3 | 스토리지/슈퍼관리자 메뉴 추가 |
| 8~17 | 관리자 업로드 페이지 10개 | MT-2 | `mg_storage()` 전환 |
| 18 | `plugin/editor/toastui/editor.lib.php` | MT-2 | 에디터 업로드 전환 |
| 19 | `theme/morgan/head.sub.php` | MT-0 | 테넌트 컨텍스트 JS 변수 출력 |
| 20 | `CLAUDE.md` | MT-0 | 멀티테넌트 관련 문서 갱신 |

---

## 작업량 추정

| Phase | 예상 시간 | 핵심 작업 | 비고 |
|-------|-----------|----------|------|
| MT-0 테넌트 라우터 | 12~16h | 부트스트랩, DB 교체, 호환 모드 | **최우선, 전체 기반** |
| MT-1 DB 격리 | 8~12h | 캐시, 세션, 경로 분리 | MT-0 직후 |
| MT-2 스토리지 추상화 | 16~22h | MG_Storage, R2 드라이버, 15개 포인트 | MT-0과 병행 가능 |
| MT-3 프로비저닝 | 12~16h | 슈퍼 관리자, 자동 생성, 시드 | MT-0,1 완료 후 |
| MT-4 보안 격리 | 6~10h | 검증, 제한, 테스트 | MT-1,2 완료 후 |
| MT-5 베타 배포 | 8~12h | 도메인, nginx, 온보딩, 모니터링 | 전체 완료 후 (기존 VPS 활용) |
| **합계** | **62~88h** | | |

### 권장 진행 순서

```
Phase MT-0 (테넌트 라우터)          ████████████████  [1순위]
    │
    ├── Phase MT-1 (DB 격리)        ████████████      [2순위, 직후]
    │       │
    │       └── Phase MT-3 (프로비저닝) ████████████████  [4순위]
    │
    └── Phase MT-2 (스토리지/R2)    ████████████████████  [3순위, MT-0과 병행 가능]
            │
            └── Phase MT-4 (보안)   ██████████        [5순위]
                    │
                    └── Phase MT-5 (베타 배포) ████████████  [6순위, 기존 VPS 활용]
```

**MT-2(스토리지)는 MT-0 완료 직후 MT-1과 병행 진행 가능** — 스토리지 추상화는 DB 격리와 독립적.

---

## 베타 운영 전략

### 플랜 구조 (베타 기간)

| 플랜 | 가격 | 회원 상한 | 저장 용량 | 비고 |
|------|------|-----------|----------|------|
| Free (베타) | 무료 | 100명 | 1GB | 베타 테스터 전용 |
| Basic | 미정 | 300명 | 5GB | 정식 출시 후 |
| Pro | 미정 | 무제한 | 20GB | 정식 출시 후 |

### 베타 제한사항

- 최대 테넌트 수: 초기 5~10개 (서버 리소스 모니터링 후 확대)
- 백업: 일일 1회 자동
- SLA: 없음 (베타이므로)
- 데이터 보존: 베타 종료 후 30일간 보존, 이후 이관 또는 삭제

### 테넌트 기능 제한 (플랜별)

| 기능 | Free | Basic | Pro |
|------|------|-------|-----|
| 기본 기능 (게시판, 캐릭터, 상점 등) | O | O | O |
| R2 스토리지 | X (로컬) | O | O |
| 커스텀 도메인 | X | O | O |
| 2차 모듈 (VN, SS Engine 등) | 체험 | 개별 구매 | 전체 포함 |
| 기술 지원 | 커뮤니티 | 이메일 | 우선 지원 |

---

## 기존 2차 로드맵과의 관계

멀티테넌트 인프라(이 문서)와 2차 컨텐츠 모듈(`ROADMAP_PHASE2.md`)은 **독립적으로 병행 진행** 가능.

### 진행 우선순위

```
[선행] 멀티테넌트 인프라 (MT-0 ~ MT-2)
    │
    ├── [병행] 2차 모듈 개발 (A~H)     ← 단일 테넌트에서 먼저 개발/테스트
    │
    └── [후행] 멀티테넌트 배포 (MT-3 ~ MT-5)
            │
            └── [통합] 2차 모듈을 테넌트별 활성화/비활성화 옵션으로 제공
```

### 모듈 활성화 설정

각 테넌트는 사용할 2차 모듈을 선택할 수 있다:

```php
// 테넌트별 모듈 활성화 확인
if (mg_config('module_vn_engine', '0')) { /* VN Engine 로드 */ }
if (mg_config('module_ss_engine', '0')) { /* SS Engine 로드 */ }
if (mg_config('module_faction', '0'))   { /* 진영 컨텐츠 로드 */ }
```

이를 위해 각 2차 모듈은 `morgan.php`에서 조건부 로드되도록 설계해야 한다.

---

## 기술 참고사항

### Cloudflare R2 API

R2는 S3 호환 API를 제공하므로 AWS SDK 없이 순수 HTTP 요청으로 구현 가능:

```
PUT https://{account_id}.r2.cloudflarestorage.com/{bucket}/{key}
Authorization: AWS4-HMAC-SHA256 ...
Content-Type: image/jpeg

[binary data]
```

필요한 API 엔드포인트:
- `PutObject` — 파일 업로드
- `DeleteObject` — 파일 삭제
- `HeadObject` — 존재 확인 + 메타데이터
- `HeadBucket` — 버킷 연결 테스트

퍼블릭 URL 구조:
- R2 퍼블릭 접근: `https://pub-{hash}.r2.dev/{key}`
- 커스텀 도메인: `https://cdn.{도메인}/{key}`

### `$g5['connect_db']` 교체 안전성

`lib/common.lib.php`의 모든 `sql_*` 함수가 `$g5['connect_db']`를 기본값으로 사용:

```php
function sql_query($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null) {
    global $g5;
    if(!$link) $link = $g5['connect_db'];  // ← 이 변수만 교체하면 전체 전환
    ...
}
```

따라서 `tenant_bootstrap.php`에서 `$g5['connect_db']`를 테넌트 DB 연결로 교체하면,
이후 모든 `sql_query()`, `sql_fetch()` 등이 자동으로 테넌트 DB를 사용한다.

### `define()` 상수 우회 전략

PHP의 `define()`은 재정의 불가. 해결 방법:

1. **부트스트랩 선행 정의**: `tenant_bootstrap.php`가 `dbconfig.php`보다 먼저 로드되어 상수를 먼저 정의
2. **조건부 정의**: `dbconfig.php`에서 `defined()` 체크 후 정의
   ```php
   if (!defined('G5_MYSQL_DB')) define('G5_MYSQL_DB', 'morgan_db');
   ```
3. **전역 변수 전환**: 경로 상수는 `$mg_paths['char_image']` 같은 전역 변수로 대체하고,
   기존 `define()` 상수는 폴백 용도로 유지

권장: **1번 + 2번 조합**. `dbconfig.php`를 최소 수정하여 하위 호환 유지.

### 세션 격리 구현

```php
// tenant_bootstrap.php 에서
$session_name = 'MG_' . strtoupper($tenant_id);
session_name($session_name);

// 쿠키 도메인을 서브도메인으로 한정
ini_set('session.cookie_domain', $subdomain . '.' . MG_TENANT_DOMAIN);
```

---

## 변경 이력

| 날짜 | 내용 |
|------|------|
| 2026-03-01 | 초안 작성 (멀티테넌트 + R2 통합 로드맵) |
| 2026-03-01 | 인프라 방식 확정: 기존 VPS + Cloudflare Proxied 서브도메인, 도메인 별도 구매 |

---

*이 문서는 개발 진행에 따라 지속적으로 업데이트됩니다.*
