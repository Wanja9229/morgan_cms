# 2차-E: VN Engine (역극 → 비주얼 노벨 변환)

> 작성일: 2026-02-16
> 상태: 기획 완료
> 의존성: 역극(RP) 시스템, 캐릭터 시스템

---

## 개요

역극(RP)으로 쌓인 대화 데이터를 비주얼 노벨 형식으로 자동 변환하여 리플레이할 수 있는 시스템.
기존 VN Engine 코드베이스(`vn_engine/`)를 Morgan CMS에 통합한다.

### 핵심 가치

**"내가 쓴 역극이 비주얼 노벨이 된다"**

- 역극 완결 후 한 번의 클릭으로 VN 리플레이 생성
- 캐릭터 정보(이미지, 이름) 자동 연동 — 별도 등록 불필요
- 웹에서 바로 플레이 가능 (별도 설치 불필요)

### 기존 대체재와 차이점

| 기존 방식 | VN Engine |
|----------|-----------|
| 역극 → 수동으로 VN 제작 | 역극 → **자동 변환** |
| 캐릭터 이미지 일일이 지정 | CMS 캐릭터 데이터 **자동 연동** |
| 별도 툴 필요 (티라노, 렌파이) | **웹에서 바로 플레이** |
| 리플레이 = 로그 텍스트 읽기 | 리플레이 = **VN 형식 재생** |

---

## 기존 VN Engine 분석

### 파일 구조 (vn_engine/)
```
vn_engine/
├── api.php (2180줄)       — 핵심 API (모든 데이터 CRUD)
├── play.php (39KB)        — 웹 기반 플레이어
├── story.php (50KB)       — 스토리 에디터 (노드 편집)
├── characters.php (24KB)  — 캐릭터 관리
├── backgrounds.php (17KB) — 배경 관리
├── bgm.php (25KB)         — BGM 관리
├── flowchart.php (29KB)   — 플로우차트 시각화
├── settings.php (25KB)    — UI 설정
├── install.php (15KB)     — DB 초기화
└── docs/                  — 기능 정의서
```

### 데이터 포맷 (JSON 노드 기반)

```json
{
  "node_id": 1,
  "type": "dialogue",
  "speaker": "캐릭터명",
  "content": "대사 텍스트",
  "char_key": "protagonist",
  "expression": "smile",
  "char_position": "center",
  "bg_key": "room1",
  "bgm_key": "bgm_calm",
  "next_node_id": 2
}
```

### 기존 DB 테이블
- `vn_projects` — 프로젝트
- `vn_scenes` — 씬 (장 구분)
- `vn_nodes` — 대사/선택지 노드
- `vn_branches` — 분기
- `vn_characters` — 캐릭터 (키, 이름, 색상, 이미지)
- `vn_expressions` — 표정 (캐릭터별)
- `vn_backgrounds` — 배경
- `vn_bgm` — BGM

---

## RP → VN 데이터 매핑

### RP 데이터 구조
```
mg_rp_thread → VN 프로젝트 (1:1)
├── rt_title → 프로젝트 제목
├── rt_content → 시작 나레이션
└── ch_id → 판장 캐릭터

mg_rp_reply → VN 노드 (1:1, 순서대로)
├── rr_content → node.content (대사)
├── ch_id → JOIN → ch_name, ch_thumb (화자)
├── rr_image → 배경 CG 또는 인라인 이미지
├── rr_id → node 순서 (ASC)
└── rr_context_ch_id → 대화 상대 (연출용)

mg_character → VN 캐릭터
├── ch_name → char_name
├── ch_thumb → default_img (두상)
└── ch_image → 전신 이미지 (있으면)
```

### 매핑 난이도

| 요소 | RP → VN | 난이도 | 비고 |
|------|---------|--------|------|
| 대사 | `rr_content` → `node.content` | 쉬움 | 직접 매핑 |
| 화자 | `ch_name` → `node.speaker` | 쉬움 | JOIN으로 해결 |
| 캐릭터 이미지 | `ch_thumb` → `char.default_img` | 쉬움 | 경로 변환만 |
| 대사 순서 | `rr_id ASC` → `node.ui_order` | 쉬움 | 자동 |
| 첨부 이미지 | `rr_image` → CG/배경 | 중간 | 규칙 필요 |
| 배경/BGM | RP에 없음 | — | 기본값 or 수동 |
| 분기/선택지 | RP에 없음 | — | 선형 변환만 |
| 표정 | RP에 없음 | — | 기본 이미지만 |

---

## 구현 계획

### E.0 VN Engine 이식

VN Engine을 Morgan CMS 내부로 통합.

- [ ] `plugin/vn_engine/` 디렉토리에 코드 이식
- [ ] VN 테이블을 `mg_vn_*` 접두어로 변경, morgan.php에 등록
- [ ] DB 마이그레이션 파일 작성 (install.php → SQL)
- [ ] 인증 연동 (Morgan 회원 시스템 사용)
- [ ] 이미지 경로를 `data/vn/` 구조로 통일

### E.1 자동 변환 API

RP 스레드를 VN 프로젝트로 자동 변환하는 핵심 로직.

- [ ] `bbs/rp_to_vn.php` — 변환 API 엔드포인트
- [ ] 변환 로직:
  1. RP 스레드 정보 → VN 프로젝트 생성
  2. 참여 캐릭터 → VN 캐릭터 자동 등록 (ch_thumb 연동)
  3. RP 시작글 → 첫 번째 나레이션 노드
  4. RP 이음(reply) → dialogue 노드 순서대로 생성
  5. 첨부 이미지 → CG 노드 또는 배경 전환
- [ ] 중복 변환 방지 (이미 변환된 스레드 체크)
- [ ] 변환 상태 저장 (`mg_rp_thread`에 `vn_project_id` 컬럼 추가)

### E.2 RP 페이지 UI

- [ ] 완결된 역극에 "VN으로 보기" 버튼 추가 (`rp/list.skin.php`)
- [ ] 미변환 시: "VN 생성" → 변환 진행 → 완료 후 플레이어 오픈
- [ ] 변환 완료 시: 바로 플레이어 오픈
- [ ] 변환 진행 중 로딩 UI

### E.3 VN 플레이어 임베드

- [ ] `play.php`를 iframe 또는 SPA 모달로 임베드
- [ ] RP 페이지에서 바로 재생 가능
- [ ] 공유 링크 생성 (외부 접속 가능)
- [ ] 자동/수동 진행, 속도 조절

### E.4 변환 후 편집 (선택)

자동 변환 후 수동으로 연출을 보강할 수 있는 기능.

- [ ] VN 에디터 접근 (story.php 연동)
- [ ] 배경 이미지 설정
- [ ] BGM 설정
- [ ] 특정 노드에 선택지 추가 (분기)
- [ ] 나레이션 노드 추가/삭제

### E.5 관리자

- [ ] VN 프로젝트 목록/삭제
- [ ] 기본 배경/BGM 셋 관리
- [ ] 변환 설정 (자동 변환 허용, 최소 이음 수 등)

---

## 변환 흐름

```
[RP 완결 스레드]
    │
    ▼ ("VN 생성" 버튼 클릭)
[변환 API 호출]
    │
    ├── mg_rp_thread → vn_project 생성
    │
    ├── mg_rp_member → 참여 캐릭터 추출
    │     └── mg_character JOIN → vn_character 등록
    │         (ch_name, ch_thumb 자동 매핑)
    │
    ├── rt_content → 나레이션 노드 #1
    │
    ├── mg_rp_reply (ORDER BY rr_id ASC)
    │     └── 각 이음 → dialogue 노드 생성
    │         ├── ch_name → speaker
    │         ├── rr_content → content
    │         ├── rr_image → CG 노드 (있으면)
    │         └── rr_context_ch_id → 캐릭터 배치 힌트
    │
    └── vn_project_id → mg_rp_thread에 저장

    ▼
[play.php?key=...] → VN 플레이어 렌더링
```

---

## 예상 공수

| 작업 | 규모 | 비고 |
|------|------|------|
| E.0 VN Engine 이식 | 중 | 기존 코드 활용, DB/인증 연동 |
| E.1 자동 변환 API | 소~중 | 핵심 로직, SQL + 루프 |
| E.2 RP 페이지 UI | 소 | 버튼 + AJAX |
| E.3 플레이어 임베드 | 소 | iframe/모달 |
| E.4 변환 후 편집 | 중 | 기존 에디터 연동 |
| E.5 관리자 | 소 | CRUD 페이지 |

**총 예상: 중간 규모 Phase** (기존 Phase 중 가벼운 축)

---

## 제한사항

| 제한 | 설명 | 대안 |
|------|------|------|
| 분기/선택지 | RP는 선형 대화 → 자동 분기 불가 | 변환 후 수동 편집 (E.4) |
| BGM/배경 | RP에 없는 데이터 | 기본값 설정 + 수동 추가 |
| 표정 | RP에 표정 데이터 없음 | 기본 이미지만 사용 |
| 긴 역극 | 이음 수백 개 → 노드 수백 개 | 씬 자동 분할 (N개 단위) |

---

## Morgan CMS 연동

| 연동 대상 | 연동 내용 |
|----------|----------|
| **역극 시스템** | 원본 데이터 소스, 완결 스레드 대상 |
| **캐릭터 시스템** | 이미지·이름 자동 매칭, 별도 등록 불필요 |
| **포인트 시스템** | VN 조회수 기반 보상 (선택적) |
| **인장 시스템** | VN 플레이어 내 인장 표시 (선택적) |

---

*이 문서는 2차 작업 계획의 일부입니다. 사용자 피드백에 따라 우선순위가 조정될 수 있습니다.*
