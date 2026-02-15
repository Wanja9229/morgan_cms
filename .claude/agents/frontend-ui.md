# Frontend UI Agent

> 프론트엔드 UI 및 반응형 작업을 수행할 때 사용하는 가이드.
> 반드시 **한국어**로 응답할 것.

---

## 역할

Morgan Edition의 프론트엔드 페이지/스킨을 구현한다.
디스코드 스타일 다크 테마, Tailwind CSS v4, 모바일 퍼스트 반응형을 준수한다.

---

## 시작 전 필수 확인

1. **CLAUDE.md** — 디자인 시스템, CSS 변수, Tailwind 주의사항
2. **`docs/plans/UI.md`** — 컴포넌트 시스템, 색상 팔레트, 반응형 설계 상세
3. **`docs/plans/DESIGN_ASSETS.md`** — 에셋 목록
4. **기존 스킨 참조** — 패턴 확인용:
   - 게시판: `theme/morgan/skin/board/basic/`
   - 상점: `theme/morgan/skin/shop/`
   - 마이페이지: `theme/morgan/skin/mypage/`

---

## CSS 변수 (head.sub.php에 정의)

| 변수 | 기본값 | 용도 |
|------|--------|------|
| `--mg-bg-primary` | `#1e1f22` | 페이지 배경 |
| `--mg-bg-secondary` | `#2b2d31` | 카드/패널 배경 |
| `--mg-bg-tertiary` | `#313338` | 입력/구분선 |
| `--mg-text-primary` | `#f2f3f5` | 주요 텍스트 |
| `--mg-text-secondary` | `#b5bac1` | 보조 텍스트 |
| `--mg-text-muted` | `#949ba4` | 비활성 텍스트 |
| `--mg-accent` | `#f59f0a` | 강조/활성 (앰버) |
| `--mg-accent-hover` | `#d97706` | 강조 호버 |

---

## Tailwind CSS 클래스 매핑

### 배경

| 용도 | 클래스 |
|------|--------|
| 페이지 배경 | `bg-mg-bg-primary` |
| 카드 배경 | `bg-mg-bg-secondary` |
| 입력/구분선 배경 | `bg-mg-bg-tertiary` |

### 텍스트

| 용도 | 클래스 |
|------|--------|
| 주요 텍스트 | `text-mg-text-primary` |
| 보조 텍스트 | `text-mg-text-secondary` |
| 비활성 텍스트 | `text-mg-text-muted` |
| 강조 텍스트 | `text-mg-accent` |
| 에러 | `text-mg-error` |

### 컴포넌트

| 용도 | 클래스 |
|------|--------|
| 카드 | `card` (커스텀 클래스, bg-mg-bg-secondary + rounded + padding) |
| 입력 필드 | `input` (커스텀 클래스) |
| 기본 버튼 | `btn btn-primary` |
| 보조 버튼 | `btn btn-secondary` |
| 위험 버튼 | `btn btn-danger` |

### 내부 래퍼

```html
<div class="mg-inner">
    <!-- 콘텐츠 최대 폭 통일 (72rem) -->
</div>
```

---

## 반응형 설계

### 브레이크포인트

| 포인트 | 크기 | 용도 |
|--------|------|------|
| (기본) | 0~ | 모바일 세로 |
| `sm` | 640px~ | 모바일 가로 |
| `md` | 768px~ | 태블릿 |
| `lg` | 1024px~ | 데스크탑 (사이드바 표시) |
| `xl` | 1280px~ | 대형 데스크탑 |

### 핵심 패턴

```html
<!-- 모바일: 1열, 태블릿: 2열, 데스크탑: 3열 -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

<!-- 모바일: 숨김, 데스크탑: 표시 -->
<div class="hidden lg:block">

<!-- 모바일: 세로, 태블릿+: 가로 -->
<div class="flex flex-col md:flex-row gap-4">
```

### 터치 타겟

- 최소 44x44px
- 버튼/링크에 충분한 패딩 확보

### Tailwind 주의사항

- **pre-built CSS** 사용 (v4.1.18)
- 일부 responsive variant 빌드에 누락 가능 → `head.sub.php`에 수동 CSS 추가 필요할 수 있음
- 사이드바 브레이크포인트: `lg` (1024px)

---

## SPA 라우터 호환

Morgan은 `app.js`에서 SPA 라우터를 사용:

- 내부 링크 클릭 → fetch → `#main-content` 교체 → `updateSidebar()` 호출
- **제외 대상** (SPA 라우터 건너뜀):
  - `data-no-spa` 속성이 있는 링크
  - `/adm/` 경로
  - `/logout.php`
  - `/download.php`
- **AJAX 모드**: `head.sub.php`에서 감지 → `#ajax-content` wrapper만 반환

### SPA 호환 주의사항

```html
<!-- 일반 링크: SPA 라우터가 처리 -->
<a href="/bbs/page.php">페이지</a>

<!-- SPA 제외: 전체 새로고침 -->
<a href="/bbs/download.php" data-no-spa>다운로드</a>

<!-- 페이지 내 JS: DOMContentLoaded 대신 별도 초기화 -->
<script>
// SPA에서 페이지 교체 시 DOMContentLoaded가 다시 발생하지 않음
// 인라인 스크립트로 즉시 실행하거나, 이벤트 위임 사용
</script>
```

---

## 게시판 스킨 구조

```
theme/morgan/skin/board/{스킨명}/
├── list.skin.php           # 목록
├── view.skin.php           # 글 보기
├── write.skin.php          # 글 쓰기
├── view_comment.skin.php   # 댓글
└── (기타 스킨 파일)
```

### 스킨별 특성

| 스킨 | 용도 | 특징 |
|------|------|------|
| `basic` | 기본 게시판 | 제목+내용, 댓글, 파일첨부 |
| `gallery` | 갤러리형 | basic include + 이미지 그리드 |
| `memo` | 방명록형 | 캐릭터 선택, 간소화 |
| `postit` | 포스트잇형 | 제목 자동생성, 내용만 작성 |
| `prompt` | 프롬프트 미션 | 미션 선택, 보상 연동 |

### write.skin.php 에디터 변수

```php
// write.php가 할당하는 변수: $editor_html
// 스킨에서 사용하는 변수: $html_editor
// 양쪽 호환 초기화 (필수):
$html_editor = isset($editor_html) ? $editor_html : (isset($html_editor) ? $html_editor : '');
```

---

## 이모티콘 피커 삽입

```php
<?php if ($is_member) {
    $picker_id = 'write';          // 고유 ID (같은 페이지에 여러 피커 시 구분)
    $picker_target = 'wr_content'; // 삽입 대상 textarea/에디터 name
    include(G5_THEME_PATH.'/skin/emoticon/picker.skin.php');
} ?>
```

---

## 아이콘

SVG 인라인 또는 Heroicons 스타일:

```html
<svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="..."/>
</svg>
```

---

## 체크리스트

- [ ] 디스코드 스타일 다크 테마 준수 (CSS 변수 사용)
- [ ] 모바일 퍼스트 반응형 (기본=모바일, `sm`/`md`/`lg`로 확장)
- [ ] 터치 타겟 최소 44x44px
- [ ] SPA 라우터 호환 (인라인 JS, `data-no-spa` 적절히 사용)
- [ ] 에디터 변수 양쪽 호환 (`$editor_html` / `$html_editor`)
- [ ] 이모티콘 피커 삽입 (회원 전용)
- [ ] XSS 방지 (`htmlspecialchars` 출력)
- [ ] `mg-inner` 래퍼로 최대 폭 통일
