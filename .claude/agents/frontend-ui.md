# Frontend UI — 프론트엔드 스킨/페이지 작업

한국어로 응답.

## CSS 변수 (head.sub.php :root)

```
--mg-bg-primary: #1e1f22    (페이지 배경)
--mg-bg-secondary: #2b2d31  (카드)
--mg-bg-tertiary: #313338   (입력/구분선)
--mg-text-primary: #f2f3f5  --mg-text-secondary: #b5bac1  --mg-text-muted: #949ba4
--mg-accent: #f59f0a        --mg-accent-hover: #d97706
--mg-button / --mg-button-hover (관리자 설정 가능, 기본=accent)
--mg-content-width: 72rem   (mg_config으로 변경 가능)
```

## Tailwind 클래스 → CSS 변수 매핑

배경: `bg-mg-bg-primary`, `bg-mg-bg-secondary`, `bg-mg-bg-tertiary`
텍스트: `text-mg-text-primary`, `text-mg-text-secondary`, `text-mg-text-muted`, `text-mg-accent`
에러: `text-mg-error`, `bg-mg-error`

## 커스텀 유틸리티 (head.sub.php에 정의)

- `.mg-inner` — `max-width: var(--mg-content-width); margin: 0 auto;`
- `.card` — bg-mg-bg-secondary + rounded + padding
- `.input` — bg-mg-bg-tertiary 기반 입력 필드
- `.btn.btn-primary` — `background: var(--mg-button)` (head.sub.php에서 !important 오버라이드)
- `.btn.btn-secondary` — 보조 버튼

## 반응형

- 기본=모바일, `sm:640`, `md:768`, `lg:1024` (사이드바 표시), `xl:1280`
- Tailwind pre-built (v4.1.18) — 일부 variant 누락 시 head.sub.php에 수동 CSS 추가
- 사이드바 너비: 56px (아이콘만), lg 이하 숨김+햄버거

## SPA 라우터 (app.js)

- 내부 링크 → fetch → `#main-content` 교체 → `updateSidebar()`
- 제외: `data-no-spa` 속성, `/adm/`, `/logout.php`, `/download.php`
- AJAX 요청 감지 시 `#ajax-content` wrapper만 반환

## 게시판 스킨 — `theme/morgan/skin/board/{스킨}/`

| 스킨 | 특성 |
|------|------|
| basic | 범용, 다른 스킨의 기반 (gallery는 basic include) |
| memo | 캐릭터 선택기 포함, 제목+내용 |
| postit | 제목 자동생성(날짜), 내용만 |
| prompt | 프롬프트 미션 선택, 보상 연동 |

## write.skin.php 필수 패턴

에디터 변수 호환 (write.php는 `$editor_html`, 스킨은 `$html_editor` 참조):
```php
$html_editor = isset($editor_html) ? $editor_html : (isset($html_editor) ? $html_editor : '');
```

이모티콘 피커 (회원 전용, 내용 필드 아래):
```php
<?php if ($is_member) {
    $picker_id = 'write'; $picker_target = 'wr_content';
    include(G5_THEME_PATH.'/skin/emoticon/picker.skin.php');
} ?>
```

## 프론트 페이지 (bbs/)

- morgan.php 로드: `include_once(G5_PATH.'/plugin/morgan/morgan.php');`
- 래퍼: `<div class="mg-inner">` 필수
- 카드: `<div class="card">` 사용
