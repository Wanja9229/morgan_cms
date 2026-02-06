# UI/디자인 시스템 기획

> Morgan Edition - UI/디자인 시스템 설계
> 기반: Tailwind CSS + 커스텀 컴포넌트 시스템
> 참고: guide_admin.html, guide_character.html, guide_board.html

> ⚠️ **스키마 참조**: 디자인 설정 저장은 mg_config 테이블 사용 ([PLAN_DB.md](./PLAN_DB.md) 참조)

---

## 개요

- **프레임워크**: Tailwind CSS
- **테마**: 다크 모드 고정
- **메인 컬러**: #f59f0a (황금/앰버)
- **아이콘**: Material Symbols Outlined
- **폰트**: Spline Sans + Noto Sans KR
- **반응형**: 모바일 퍼스트 (Mobile First)
- **컴포넌트**: 전면 컴포넌트화 (관리자 커스텀 가능)

---

## 0. 디자인 원칙

### 0.1 모바일 퍼스트 (Mobile First)

| 원칙 | 설명 |
|------|------|
| 기본 = 모바일 | CSS 기본값은 모바일 기준으로 작성 |
| 확장 = 데스크탑 | `sm:`, `md:`, `lg:` 등으로 큰 화면 대응 |
| 터치 친화적 | 버튼/링크 최소 44px 터치 영역 확보 |
| 가독성 | 작은 화면에서도 읽기 쉬운 폰트 크기 |

### 0.2 모바일 퍼스트 작성 예시

```html
<!-- 모바일 퍼스트 -->
<div class="flex flex-col md:flex-row">
    <div class="w-full md:w-1/3">사이드바</div>
    <div class="w-full md:w-2/3">콘텐츠</div>
</div>

<!-- 모바일: 세로 정렬 → 태블릿+: 가로 정렬 -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div>카드1</div>
    <div>카드2</div>
</div>

<!-- 모바일: 숨김 → 데스크탑: 표시 -->
<nav class="hidden lg:flex">데스크탑 메뉴</nav>
<button class="lg:hidden">모바일 햄버거</button>
```

### 0.3 터치 영역 기준

```css
/* 최소 터치 영역 44x44px */
.touch-target {
    min-width: 44px;
    min-height: 44px;
}

/* 버튼 기본 패딩 */
.btn-sm { padding: 8px 12px; }   /* 모바일에서도 누르기 쉽게 */
.btn-md { padding: 10px 16px; }
.btn-lg { padding: 12px 24px; }
```

### 0.4 전면 컴포넌트화 원칙

**모든 UI 요소는 PHP 함수로 컴포넌트화**하여:
1. 일관된 디자인 유지
2. 관리자 설정 반영 (컬러, 둥글기, 크기 등)
3. 수정 시 전체 반영
4. 코드 재사용성 향상

---

## 1. 컬러 팔레트

### 1.1 기본 색상

```javascript
// tailwind.config.js
colors: {
    // 메인 컬러
    "primary": "#f59f0a",
    "primary-hover": "#d97706",
    "primary-light": "#fbbf24",

    // 배경
    "background-dark": "#0f0f0f",      // 가장 어두운 배경
    "surface-dark": "#171717",          // 카드, 패널 배경
    "surface-darker": "#121212",        // 더 어두운 영역

    // 입력/요소
    "input-dark": "#1f1f1f",            // 입력 필드 배경
    "border-dark": "#2a2a2a",           // 기본 테두리
    "border-light": "#3a3a3a",          // 호버 테두리

    // 텍스트
    "text-primary": "#ffffff",          // 주요 텍스트
    "text-secondary": "#a0a0a0",        // 보조 텍스트
    "text-muted": "#6b6b6b",            // 비활성 텍스트

    // 상태 색상
    "success": "#22c55e",
    "warning": "#eab308",
    "error": "#ef4444",
    "info": "#3b82f6",
}
```

### 1.2 컬러 용도

| 색상 | 용도 |
|------|------|
| primary | 버튼, 링크, 강조, 활성 상태 |
| background-dark | 페이지 배경 |
| surface-dark | 카드, 모달, 드롭다운 배경 |
| input-dark | 입력 필드, 검색창 배경 |
| border-dark | 일반 테두리 |
| text-primary | 제목, 주요 내용 |
| text-secondary | 설명, 부제목 |

### 1.3 관리자 커스텀 가능 항목

| 항목 | 설명 | 기본값 |
|------|------|--------|
| primary | 메인 컬러 | #f59f0a |
| primary-hover | 메인 호버 | #d97706 |
| background-dark | 배경색 | #0f0f0f |
| surface-dark | 카드 배경 | #171717 |

---

## 2. 타이포그래피

### 2.1 폰트 패밀리

```css
fontFamily: {
    "display": ["Spline Sans", "Noto Sans KR", "sans-serif"],
    "body": ["Noto Sans KR", "sans-serif"],
    "mono": ["JetBrains Mono", "monospace"],
}
```

### 2.2 폰트 크기 체계

| 클래스 | 크기 | 용도 |
|--------|------|------|
| text-xs | 12px | 보조 정보, 태그 |
| text-sm | 14px | 본문, 버튼 |
| text-base | 16px | 기본 본문 |
| text-lg | 18px | 소제목 |
| text-xl | 20px | 섹션 제목 |
| text-2xl | 24px | 페이지 부제목 |
| text-3xl | 30px | 페이지 제목 |

### 2.3 폰트 굵기

| 클래스 | 굵기 | 용도 |
|--------|------|------|
| font-normal | 400 | 본문 |
| font-medium | 500 | 강조 본문 |
| font-semibold | 600 | 소제목, 라벨 |
| font-bold | 700 | 제목, 버튼 |
| font-black | 900 | 대제목 |

---

## 3. 컴포넌트 시스템

### 3.1 컴포넌트화 대상 (전체 목록)

**원칙**: 디자인적 커스텀이 가능한 모든 UI 요소는 컴포넌트화

| 분류 | 컴포넌트 | 커스텀 가능 항목 |
|------|----------|-----------------|
| **버튼** | button | 컬러, 둥글기, 크기, 아이콘 |
| **폼** | input, textarea | 배경색, 테두리, 포커스 색상 |
| | select, dropdown | 배경색, 테두리, 화살표 |
| | checkbox, radio | 체크 색상, 크기 |
| | toggle | on/off 색상, 크기 |
| | file_upload | 영역 스타일, 아이콘 |
| **표시** | card | 배경, 테두리, 둥글기, 그림자 |
| | badge, tag | 컬러 세트, 둥글기 |
| | avatar | 크기, 테두리, 둥글기 |
| | tooltip | 배경, 화살표 |
| | alert | 타입별 컬러 (success/error/warning/info) |
| **네비게이션** | breadcrumb | 구분자, 링크 색상 |
| | pagination | 버튼 스타일, 활성 색상 |
| | tab | 활성 스타일, 언더라인/박스 |
| | menu_item | 호버 효과, 활성 표시 |
| **오버레이** | modal | 배경 블러, 크기, 둥글기 |
| | dropdown_menu | 배경, 그림자, 항목 호버 |
| | toast | 위치, 타입별 컬러 |
| **데이터** | table | 헤더, 로우 호버, 테두리 |
| | list_item | 구분선, 호버 |
| **레이아웃** | header | 높이, 배경, 그림자 |
| | sidebar | 너비, 배경, 메뉴 스타일 |
| | footer | 높이, 배경 |
| | section | 패딩, 배경 |

### 3.2 컴포넌트 구조

```
/component/
├── ui/
│   ├── button.php          # 버튼
│   ├── input.php           # 입력 필드
│   ├── textarea.php        # 텍스트 영역
│   ├── select.php          # 셀렉트 박스
│   ├── dropdown.php        # 드롭다운 메뉴
│   ├── checkbox.php        # 체크박스
│   ├── radio.php           # 라디오 버튼
│   ├── toggle.php          # 토글 스위치
│   ├── file_upload.php     # 파일 업로드
│   ├── card.php            # 카드
│   ├── modal.php           # 모달
│   ├── badge.php           # 뱃지
│   ├── tag.php             # 태그
│   ├── avatar.php          # 아바타
│   ├── tooltip.php         # 툴팁
│   ├── alert.php           # 알림 박스
│   ├── toast.php           # 토스트 메시지
│   ├── pagination.php      # 페이지네이션
│   ├── tab.php             # 탭
│   ├── table.php           # 테이블
│   └── list.php            # 리스트
├── layout/
│   ├── header.php          # 헤더
│   ├── sidebar.php         # 사이드바
│   ├── footer.php          # 푸터
│   ├── breadcrumb.php      # 브레드크럼
│   ├── section.php         # 섹션 래퍼
│   └── container.php       # 컨테이너
└── _config.php             # 컴포넌트 설정 로드
```

### 3.3 컴포넌트 설정 구조

```php
<?php
// /component/_config.php

// DB에서 디자인 설정 로드
function load_component_config() {
    global $g5;

    $config = [];
    $result = sql_query("SELECT dc_key, dc_value FROM {$g5['design_config_table']}");
    while ($row = sql_fetch_array($result)) {
        $config[$row['dc_key']] = $row['dc_value'];
    }

    // 기본값 병합
    return array_merge([
        // 컬러
        'primary_color' => '#f59f0a',
        'primary_hover' => '#d97706',
        'success_color' => '#22c55e',
        'error_color' => '#ef4444',
        'warning_color' => '#eab308',

        // 둥글기
        'button_radius' => '0.5rem',
        'card_radius' => '0.75rem',
        'input_radius' => '0.5rem',
        'modal_radius' => '0.75rem',

        // 그림자
        'card_shadow' => 'none',
        'button_shadow' => 'lg',

        // 크기
        'button_size_default' => 'md',
        'input_size_default' => 'md',

    ], $config);
}

$GLOBALS['morgan_ui'] = load_component_config();
```

### 3.4 컴포넌트 사용 통일 규칙

```php
// 모든 버튼은 morgan_button() 사용
echo morgan_button(['text' => '저장', 'type' => 'primary']);

// 모든 입력은 morgan_input() 사용
echo morgan_input(['name' => 'title', 'label' => '제목']);

// 모든 카드는 morgan_card() 사용
echo morgan_card(['title' => '카드', 'content' => '내용']);

// 직접 HTML 작성 금지 (커스텀 반영 안 됨)
// <button class="...">저장</button>  ← 금지
```

---

## 4. 버튼 컴포넌트

### 4.1 버튼 함수

```php
<?php
/**
 * 버튼 컴포넌트 생성
 *
 * @param array $options 버튼 옵션
 * @return string HTML
 */
function morgan_button($options = []) {
    $defaults = [
        'text' => '버튼',
        'type' => 'primary',      // primary, secondary, ghost, danger
        'size' => 'md',           // sm, md, lg
        'icon' => '',             // Material Symbol 이름
        'icon_position' => 'left', // left, right
        'href' => '',             // 링크 (있으면 <a>, 없으면 <button>)
        'onclick' => '',          // 클릭 이벤트
        'disabled' => false,
        'full_width' => false,
        'class' => '',            // 추가 클래스
        'attributes' => [],       // 추가 속성
    ];

    $opts = array_merge($defaults, $options);

    // 관리자 설정에서 커스텀 스타일 가져오기
    $custom_styles = get_button_custom_styles();

    // 타입별 클래스
    $type_classes = [
        'primary' => 'bg-primary hover:bg-primary-hover text-white shadow-lg shadow-primary/25',
        'secondary' => 'bg-surface-dark hover:bg-input-dark text-white border border-border-dark',
        'ghost' => 'bg-transparent hover:bg-white/5 text-text-secondary hover:text-white',
        'danger' => 'bg-error hover:bg-error/90 text-white',
    ];

    // 사이즈별 클래스
    $size_classes = [
        'sm' => 'px-3 py-1.5 text-xs rounded',
        'md' => 'px-4 py-2.5 text-sm rounded-lg',
        'lg' => 'px-6 py-3 text-base rounded-lg',
    ];

    // HTML 생성
    // ...

    return $html;
}
```

### 4.2 버튼 타입

| 타입 | 클래스 | 용도 |
|------|--------|------|
| primary | `bg-primary text-white` | 주요 액션 (저장, 제출) |
| secondary | `bg-surface-dark border` | 보조 액션 (취소, 닫기) |
| ghost | `bg-transparent` | 텍스트 링크형 |
| danger | `bg-error text-white` | 삭제, 위험 액션 |

### 4.3 버튼 사이즈

| 사이즈 | 패딩 | 용도 |
|--------|------|------|
| sm | px-3 py-1.5 | 인라인, 테이블 내 |
| md | px-4 py-2.5 | 기본 |
| lg | px-6 py-3 | 강조, CTA |

### 4.4 버튼 사용 예시

```php
// 기본 버튼
echo morgan_button(['text' => '저장']);

// 아이콘 버튼
echo morgan_button([
    'text' => '새 글 작성',
    'icon' => 'add',
    'type' => 'primary',
    'size' => 'lg'
]);

// 링크 버튼
echo morgan_button([
    'text' => '목록으로',
    'type' => 'secondary',
    'href' => '/board/list'
]);

// 삭제 버튼
echo morgan_button([
    'text' => '삭제',
    'type' => 'danger',
    'icon' => 'delete',
    'onclick' => 'confirmDelete()'
]);
```

### 4.5 버튼 HTML 출력 예시

```html
<!-- Primary 버튼 -->
<button class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5
               text-sm font-bold text-white shadow-lg shadow-primary/25
               hover:bg-primary-hover transition-all">
    <span class="material-symbols-outlined text-[20px]">add</span>
    새 글 작성
</button>

<!-- Secondary 버튼 -->
<button class="flex items-center gap-2 rounded-lg bg-surface-dark
               border border-border-dark px-4 py-2.5 text-sm font-medium
               text-white hover:bg-input-dark transition-all">
    목록으로
</button>
```

---

## 5. 입력 필드 컴포넌트

### 5.1 입력 필드 함수

```php
function morgan_input($options = []) {
    $defaults = [
        'type' => 'text',
        'name' => '',
        'value' => '',
        'placeholder' => '',
        'label' => '',
        'help' => '',
        'error' => '',
        'icon' => '',
        'required' => false,
        'disabled' => false,
        'class' => '',
    ];
    // ...
}
```

### 5.2 입력 필드 스타일

```html
<!-- 기본 입력 필드 -->
<div class="flex flex-col gap-2">
    <label class="text-sm font-medium text-text-secondary">라벨</label>
    <input type="text"
           class="w-full rounded-lg border-0 bg-input-dark py-2.5 px-4
                  text-white placeholder:text-text-muted
                  focus:ring-2 focus:ring-primary"
           placeholder="입력하세요..."/>
    <p class="text-xs text-text-muted">도움말 텍스트</p>
</div>

<!-- 아이콘 포함 검색 필드 -->
<div class="relative">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <span class="material-symbols-outlined text-text-muted">search</span>
    </div>
    <input type="text"
           class="block w-full rounded-lg border-0 bg-input-dark py-2.5 pl-10 pr-4
                  text-white placeholder:text-text-muted focus:ring-2 focus:ring-primary"
           placeholder="검색..."/>
</div>
```

---

## 6. 카드 컴포넌트

### 6.1 카드 함수

```php
function morgan_card($options = []) {
    $defaults = [
        'title' => '',
        'content' => '',
        'footer' => '',
        'image' => '',
        'hover' => true,
        'class' => '',
    ];
    // ...
}
```

### 6.2 카드 스타일

```html
<!-- 기본 카드 -->
<div class="rounded-xl border border-border-dark bg-surface-dark p-5
            hover:border-border-light transition-colors">
    <h3 class="text-white font-bold mb-2">카드 제목</h3>
    <p class="text-text-secondary text-sm">카드 내용...</p>
</div>

<!-- 이미지 카드 (갤러리용) -->
<div class="group rounded-xl border border-border-dark bg-surface-dark
            overflow-hidden hover:border-primary/50 transition-all">
    <div class="aspect-[4/3] bg-cover bg-center"
         style="background-image: url('...')"></div>
    <div class="p-4">
        <h3 class="text-white font-bold truncate">제목</h3>
        <p class="text-text-muted text-xs mt-1">@작성자</p>
    </div>
</div>
```

---

## 7. 모달 컴포넌트

### 7.1 모달 구조

```html
<!-- 모달 오버레이 -->
<div class="fixed inset-0 z-50 flex items-center justify-center
            bg-black/70 backdrop-blur-sm">
    <!-- 모달 컨텐츠 -->
    <div class="w-full max-w-lg rounded-xl border border-border-dark
                bg-surface-dark shadow-2xl">
        <!-- 헤더 -->
        <div class="flex items-center justify-between border-b
                    border-border-dark px-6 py-4">
            <h2 class="text-lg font-bold text-white">모달 제목</h2>
            <button class="text-text-muted hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <!-- 본문 -->
        <div class="px-6 py-4">
            <!-- 내용 -->
        </div>
        <!-- 푸터 -->
        <div class="flex justify-end gap-3 border-t border-border-dark px-6 py-4">
            <button class="...">취소</button>
            <button class="...">확인</button>
        </div>
    </div>
</div>
```

---

## 8. 테이블 컴포넌트

### 8.1 테이블 스타일

```html
<div class="overflow-hidden rounded-xl border border-border-dark bg-surface-dark">
    <table class="w-full text-left text-sm text-text-secondary">
        <thead class="bg-input-dark text-xs uppercase font-semibold text-white">
            <tr>
                <th class="px-6 py-4">항목</th>
                <th class="px-6 py-4">상태</th>
                <th class="px-6 py-4 text-right">액션</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border-dark">
            <tr class="group hover:bg-background-dark/50 transition-colors">
                <td class="px-6 py-4">
                    <span class="font-bold text-white">항목명</span>
                </td>
                <td class="px-6 py-4">
                    <span class="rounded bg-success/10 px-2 py-1 text-xs
                                 font-medium text-success">활성</span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2 opacity-0
                                group-hover:opacity-100 transition-opacity">
                        <button class="p-2 hover:bg-white/10 rounded">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

---

## 9. 토글 스위치

### 9.1 토글 스타일

```html
<label class="relative inline-flex cursor-pointer items-center">
    <input type="checkbox" class="peer sr-only"/>
    <div class="peer h-6 w-11 rounded-full bg-input-dark
                after:absolute after:left-[2px] after:top-[2px]
                after:h-5 after:w-5 after:rounded-full
                after:bg-text-muted after:transition-all after:content-['']
                peer-checked:bg-primary
                peer-checked:after:translate-x-full
                peer-checked:after:bg-white
                peer-focus:outline-none"></div>
</label>
```

---

## 10. 뱃지/태그

### 10.1 뱃지 스타일

```html
<!-- 기본 뱃지 -->
<span class="inline-flex items-center rounded-md bg-white/5 px-2 py-1
             text-xs font-medium text-white ring-1 ring-inset ring-white/10">
    기본
</span>

<!-- 컬러 뱃지 -->
<span class="rounded bg-primary/10 px-2 py-1 text-xs font-bold text-primary">
    메인
</span>
<span class="rounded bg-success/10 px-2 py-1 text-xs font-bold text-success">
    승인
</span>
<span class="rounded bg-warning/10 px-2 py-1 text-xs font-bold text-warning">
    대기
</span>
<span class="rounded bg-error/10 px-2 py-1 text-xs font-bold text-error">
    삭제
</span>
```

---

## 11. 레이아웃

### 11.1 유저단 레이아웃

```
┌─────────────────────────────────────────────────────────────┐
│  Header (고정)                                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────┐  ┌───────────────────────────────────────────┐│
│  │ Sidebar │  │  Main Content (fetch 로딩)                 ││
│  │ (선택)  │  │                                           ││
│  │         │  │                                           ││
│  └─────────┘  └───────────────────────────────────────────┘│
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  Footer                                                     │
└─────────────────────────────────────────────────────────────┘
```

### 11.2 관리자단 레이아웃

```
┌─────────────────────────────────────────────────────────────┐
│  Header                                    [유저] [로그아웃] │
├────────────┬────────────────────────────────────────────────┤
│            │  Breadcrumb                                    │
│  Sidebar   │  ─────────────────────────────────────────────│
│  (고정)    │  Page Title                                    │
│            │  ─────────────────────────────────────────────│
│  메뉴들    │                                                │
│            │  Main Content                                  │
│            │                                                │
│            │                                                │
│            │                                                │
└────────────┴────────────────────────────────────────────────┘
```

---

## 12. 반응형 설계 (Mobile First)

### 12.1 브레이크포인트

| 브레이크포인트 | 크기 | 대상 기기 | 설명 |
|---------------|------|----------|------|
| (기본) | 0~ | 모바일 세로 | **기본값, 모든 스타일 여기서 시작** |
| sm | 640px~ | 모바일 가로 | 가로 모드 최적화 |
| md | 768px~ | 태블릿 | 2컬럼 레이아웃 시작 |
| lg | 1024px~ | 데스크탑 | 사이드바 표시 |
| xl | 1280px~ | 대형 데스크탑 | 여유 있는 레이아웃 |
| 2xl | 1536px~ | 와이드 스크린 | 최대 너비 제한 |

### 12.2 레이아웃 변화

```
[모바일 기본]
┌─────────────────┐
│ 햄버거 │ 로고   │
├─────────────────┤
│                 │
│  풀 너비 콘텐츠  │
│                 │
├─────────────────┤
│     푸터        │
└─────────────────┘

[태블릿 md+]
┌─────────────────────────┐
│ 로고 │ 메뉴 │ 유저      │
├─────────────────────────┤
│                         │
│   2컬럼 그리드 가능      │
│                         │
└─────────────────────────┘

[데스크탑 lg+]
┌─────────────────────────────────┐
│ 로고 │ 메뉴           │ 유저   │
├────────┬────────────────────────┤
│        │                        │
│사이드바│     메인 콘텐츠         │
│        │                        │
└────────┴────────────────────────┘
```

### 12.3 주요 반응형 패턴

```html
<!-- 네비게이션 -->
<nav class="hidden lg:flex">데스크탑 메뉴</nav>
<button class="lg:hidden p-2">
    <span class="material-symbols-outlined">menu</span>
</button>

<!-- 사이드바 -->
<aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full
              lg:translate-x-0 lg:static transition-transform">
    사이드바
</aside>

<!-- 그리드 -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    카드들...
</div>

<!-- 테이블 → 카드 변환 -->
<table class="hidden md:table">테이블</table>
<div class="md:hidden space-y-4">모바일 카드 목록</div>

<!-- 폰트 크기 조정 -->
<h1 class="text-2xl md:text-3xl lg:text-4xl">제목</h1>

<!-- 패딩 조정 -->
<div class="px-4 md:px-6 lg:px-8">콘텐츠</div>
```

### 12.4 터치 vs 마우스

```css
/* 모바일: 터치 친화적 */
.btn-mobile {
    min-height: 44px;
    padding: 12px 16px;
}

/* 데스크탑: 호버 효과 */
@media (hover: hover) {
    .btn:hover {
        background-color: var(--primary-hover);
    }
}

/* 모바일: 호버 대신 active */
@media (hover: none) {
    .btn:active {
        background-color: var(--primary-hover);
    }
}
```

---

## 13. 아이콘 시스템

### 13.1 Material Symbols

```html
<!-- CDN -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet"/>

<!-- 사용 -->
<span class="material-symbols-outlined">icon_name</span>
```

### 13.2 주요 아이콘

| 아이콘 | 이름 | 용도 |
|--------|------|------|
| dashboard | 대시보드 | |
| group | 회원 | |
| person | 캐릭터 | |
| dns | 게시판 | |
| forum | 역극 | |
| paid | 포인트 | |
| storefront | 상점 | |
| help | 문의 | |
| settings | 설정 | |
| add | 추가 | |
| edit | 수정 | |
| delete | 삭제 | |
| search | 검색 | |
| close | 닫기 | |

---

## 14. 애니메이션

### 14.1 트랜지션

```css
/* 기본 트랜지션 */
.transition-colors { transition: color, background-color 150ms ease; }
.transition-all { transition: all 150ms ease; }
.transition-opacity { transition: opacity 150ms ease; }

/* 호버 효과 */
.hover\:scale-105:hover { transform: scale(1.05); }
```

### 14.2 로딩 스피너

```html
<div class="animate-spin h-5 w-5 border-2 border-primary
            border-t-transparent rounded-full"></div>
```

---

## 15. 관리자 커스텀 설정

### 15.1 디자인 설정 테이블 (design_config)

| 필드 | 타입 | 설명 |
|------|------|------|
| dc_key | varchar(50) | 설정 키 |
| dc_value | text | 설정 값 |

### 15.2 커스텀 가능 항목

| 키 | 설명 | 기본값 |
|----|------|--------|
| primary_color | 메인 컬러 | #f59f0a |
| background_color | 배경색 | #0f0f0f |
| button_radius | 버튼 둥글기 | 0.5rem |
| card_radius | 카드 둥글기 | 0.75rem |

### 15.3 설정 적용 함수

```php
function get_design_config($key, $default = '') {
    global $g5;
    $result = sql_fetch("SELECT dc_value FROM {$g5['design_config_table']}
                         WHERE dc_key = '{$key}'");
    return $result['dc_value'] ?? $default;
}

function get_primary_color() {
    return get_design_config('primary_color', '#f59f0a');
}
```

---

## 16. 테마 시스템

### 16.1 테마 개요

**목적**: 기본 테마 외에 추가 테마를 설치/판매할 수 있는 생태계 구성

**원칙**:
- 테마는 **스타일(CSS)**만 변경
- **기능/로직/컴포넌트 구조**는 건드리지 않음
- **관리자 페이지**는 테마 적용 제외 (항상 기본 스타일)

### 16.2 테마가 제어하는 것

| 영역 | 제어 범위 |
|------|----------|
| CSS 변수 | 색상, 폰트, 간격, 둥글기, 그림자 |
| 레이아웃 | 헤더/푸터/사이드바 배치 (선택적) |
| 컴포넌트 스타일 | CSS 오버라이드 (선택적) |

### 16.3 테마가 건드리지 않는 것

| 영역 | 이유 |
|------|------|
| 컴포넌트 HTML 구조 | PHP 함수로 고정됨 |
| JavaScript 로직 | 기능 일관성 유지 |
| 관리자 페이지 | 운영 안정성 |
| API/DB 구조 | 시스템 무결성 |

### 16.4 테마 폴더 구조

```
/theme/
├── default/                    # 기본 테마 (Morgan Edition 기본)
│   ├── theme.json              # 테마 정보
│   ├── screenshot.png          # 미리보기 이미지
│   ├── variables.css           # CSS 변수 정의
│   ├── style.css               # 추가 스타일
│   └── layouts/                # 레이아웃 템플릿 (선택)
│       ├── header.php
│       ├── footer.php
│       └── sidebar.php
│
├── ocean-blue/                 # 예시: 판매/배포용 테마
│   ├── theme.json
│   ├── screenshot.png
│   ├── variables.css
│   └── style.css
│
└── custom/                     # 사용자 직접 수정용
    └── ...
```

### 16.5 theme.json 구조

```json
{
  "name": "Ocean Blue",
  "version": "1.0.0",
  "author": "테마 제작자",
  "author_url": "https://example.com",
  "description": "시원한 블루 톤의 테마",
  "preview": "screenshot.png",
  "morgan_version": "1.0.0",
  "supports": {
    "dark_mode": true,
    "custom_header": true,
    "custom_footer": false
  }
}
```

### 16.6 CSS 변수 기반 테마

**기본 테마 (default/variables.css)**

```css
:root {
  /* === 메인 컬러 === */
  --color-primary: #f59f0a;
  --color-primary-hover: #d97706;
  --color-primary-light: #fbbf24;
  --color-primary-rgb: 245, 159, 10;  /* rgba() 용 */

  /* === 배경 === */
  --color-bg: #0f0f0f;
  --color-surface: #171717;
  --color-surface-dark: #121212;
  --color-input: #1f1f1f;

  /* === 테두리 === */
  --color-border: #2a2a2a;
  --color-border-light: #3a3a3a;

  /* === 텍스트 === */
  --color-text: #ffffff;
  --color-text-secondary: #a0a0a0;
  --color-text-muted: #6b6b6b;

  /* === 상태 === */
  --color-success: #22c55e;
  --color-warning: #eab308;
  --color-error: #ef4444;
  --color-info: #3b82f6;

  /* === 폰트 === */
  --font-display: 'Spline Sans', 'Noto Sans KR', sans-serif;
  --font-body: 'Noto Sans KR', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;

  /* === 사이즈 === */
  --radius-sm: 0.25rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --radius-xl: 1rem;

  /* === 그림자 === */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.5);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.5);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.5);
  --shadow-primary: 0 4px 14px rgba(var(--color-primary-rgb), 0.25);

  /* === 레이아웃 === */
  --header-height: 64px;
  --sidebar-width: 256px;
  --content-max-width: 1280px;
}
```

**커스텀 테마 예시 (ocean-blue/variables.css)**

```css
:root {
  /* 변경할 변수만 덮어씀 */
  --color-primary: #0ea5e9;
  --color-primary-hover: #0284c7;
  --color-primary-light: #38bdf8;
  --color-primary-rgb: 14, 165, 233;

  --color-bg: #0c1222;
  --color-surface: #131d35;
  --color-surface-dark: #0a1628;
  --color-input: #1a2744;

  --color-border: #1e3a5f;
  --color-border-light: #2a4a6f;

  --shadow-primary: 0 4px 14px rgba(14, 165, 233, 0.25);
}
```

### 16.7 컴포넌트에서 변수 사용

```php
<?php
// 컴포넌트는 CSS 변수를 참조하도록 작성
function morgan_button($options = []) {
    // 타입별 클래스 (CSS 변수 활용)
    $type_classes = [
        'primary' => 'btn-primary',  // CSS에서 var(--color-primary) 사용
        'secondary' => 'btn-secondary',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
    ];
    // ...
}
```

```css
/* component/ui/button.css */
.btn-primary {
    background-color: var(--color-primary);
    color: var(--color-text);
    box-shadow: var(--shadow-primary);
}
.btn-primary:hover {
    background-color: var(--color-primary-hover);
}
```

### 16.8 레이아웃 오버라이드 (선택적)

테마가 레이아웃을 커스텀하려면 `layouts/` 폴더에 파일 생성:

```php
<?php
// /theme/ocean-blue/layouts/header.php
// 기본 구조는 유지하되 마크업만 변경 가능

// 필수: 기본 헤더 데이터 로드
$header_data = morgan_get_header_data();
?>

<header class="ocean-custom-header">
    <!-- 커스텀 레이아웃 -->
    <div class="header-inner">
        <a href="/" class="logo">
            <?php echo $header_data['site_name']; ?>
        </a>
        <!-- 메뉴, 유저 정보 등은 컴포넌트 함수 사용 -->
        <?php echo morgan_nav_menu(); ?>
        <?php echo morgan_user_menu(); ?>
    </div>
</header>
```

### 16.9 테마 로드 순서

```php
<?php
// /lib/theme.lib.php

function morgan_load_theme() {
    $current_theme = get_config('theme', 'default');
    $theme_path = G5_THEME_PATH . '/' . $current_theme;

    // 1. 기본 CSS 로드
    echo '<link rel="stylesheet" href="' . G5_CSS_URL . '/base.css">';

    // 2. 기본 테마 변수 (항상 로드 - fallback)
    echo '<link rel="stylesheet" href="' . G5_THEME_URL . '/default/variables.css">';

    // 3. 선택된 테마 변수 (덮어쓰기)
    if ($current_theme !== 'default') {
        echo '<link rel="stylesheet" href="' . G5_THEME_URL . '/' . $current_theme . '/variables.css">';
    }

    // 4. 선택된 테마 스타일
    $theme_style = $theme_path . '/style.css';
    if (file_exists($theme_style)) {
        echo '<link rel="stylesheet" href="' . G5_THEME_URL . '/' . $current_theme . '/style.css">';
    }
}

function morgan_get_layout($layout_name) {
    $current_theme = get_config('theme', 'default');
    $theme_layout = G5_THEME_PATH . '/' . $current_theme . '/layouts/' . $layout_name . '.php';
    $default_layout = G5_THEME_PATH . '/default/layouts/' . $layout_name . '.php';

    // 테마에 레이아웃 있으면 사용, 없으면 기본
    if (file_exists($theme_layout)) {
        return $theme_layout;
    }
    return $default_layout;
}
```

### 16.10 테마 적용 제외 영역

```php
<?php
// 관리자 페이지는 테마 적용 안 함
if (defined('_ADMIN_')) {
    // 관리자용 고정 스타일만 로드
    echo '<link rel="stylesheet" href="' . G5_ADMIN_URL . '/css/admin.css">';
} else {
    // 유저단은 테마 적용
    morgan_load_theme();
}
```

### 16.11 테마 개발 가이드라인

**테마 제작자용 규칙:**

| 규칙 | 설명 |
|------|------|
| CSS 변수 우선 | 색상/크기는 반드시 CSS 변수로 |
| !important 금지 | 특수한 경우 외에는 사용 금지 |
| 컴포넌트 구조 유지 | HTML 클래스명 변경 금지 |
| 반응형 유지 | 모바일 퍼스트 브레이크포인트 준수 |
| 접근성 유지 | 색상 대비, 포커스 스타일 유지 |

**테마 검증 체크리스트:**

- [ ] 모든 페이지에서 깨짐 없이 표시되는가
- [ ] 모바일/태블릿/데스크탑 모두 정상인가
- [ ] 다크 모드에서 가독성이 확보되는가
- [ ] 버튼/링크 호버 상태가 명확한가
- [ ] 에러/성공 메시지가 잘 보이는가

### 16.12 테마 설치/관리 (2차 개발)

> 테마 마켓, 자동 설치 등은 2차 개발에서 구현

현재 1차:
- 테마 폴더 직접 업로드
- 관리자 설정에서 테마 선택

2차 개발 예정:
- 테마 마켓 연동
- 원클릭 설치
- 테마 업데이트 알림
- 테마 커스터마이저 (실시간 미리보기)

---

## 17. TODO

- [ ] Tailwind config 파일 생성
- [ ] 컴포넌트 PHP 파일 생성
- [ ] 아이콘 스프라이트 또는 CDN 선택
- [ ] 디자인 설정 관리 페이지
- [ ] 반응형 테스트
- [ ] 기본 테마 (default) 폴더 구조 생성
- [ ] CSS 변수 파일 작성
- [ ] 테마 로드 함수 구현

---

*작성일: 2026-02-03*
*수정일: 2026-02-03*
*상태: 1차 기획 완료 (테마 시스템 추가), 문서 검토 완료*
