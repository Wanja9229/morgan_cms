# Admin Page — 관리자 CRUD 스캐폴딩

한국어로 응답.

## 파일 위치

- 페이지: `adm/morgan/{기능}.php`
- 처리: `adm/morgan/{기능}_update.php`
- 메뉴: `adm/admin.menu800.php`

## 페이지 헤더 패턴 (모든 관리자 페이지 동일)

```php
$sub_menu = "80XXXX";
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'r'); // 'r'읽기 / 'w'쓰기
include_once(G5_PATH.'/plugin/morgan/morgan.php');
$g5['title'] = '기능명 관리';
include_once(G5_ADMIN_PATH.'/_head.php');
include_once(G5_ADMIN_PATH.'/morgan/_head.php');
```

## 페이지 푸터 패턴

```php
include_once(G5_ADMIN_PATH.'/morgan/_tail.php');
include_once(G5_ADMIN_PATH.'/_tail.php');
```

## 메뉴 등록 — `adm/admin.menu800.php`

배열: `[ID, 이름, URL, 권한키, 그룹명(선택)]`

현재 ID 범위 (다음 등록 시 빈 번호 사용):
- 800000~800200: 설정/세계관/회원
- 800300~800600: 프로필/진영/출석/포인트/보상/알림
- 800650~800950: 역극/게시판/상점/이모티콘
- 801000~801100: 개척
- 801200: 업적
- 801300: 인장
- 801500: 프롬프트
- 801600~801700: 관계
- **다음 사용 가능: 801800+**

그룹명이 있으면 사이드바 섹션 구분자로 표시됨.

## 탭 패턴

```php
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'default';
```

## 처리 페이지 (update) 패턴

- `$mode`로 분기 (create/update/delete)
- AJAX 감지: `$_SERVER['HTTP_X_REQUESTED_WITH']`
- AJAX → `json_encode(['success'=>true/false, 'message'=>'...'])` + exit
- 일반 → `alert()` 또는 `goto_url()`

## 보안

- SQL: `sql_real_escape_string()`, 숫자는 `(int)` 캐스팅
- XSS: 출력 시 `htmlspecialchars()`
- 권한: `auth_check_menu($auth, $sub_menu, 'w')` (쓰기 작업)
