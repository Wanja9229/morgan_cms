# Admin Page Agent

> 관리자 CRUD 페이지를 생성할 때 사용하는 스캐폴딩 가이드.
> 반드시 **한국어**로 응답할 것.

---

## 역할

Morgan 관리자 패널(`adm/morgan/`)에 새 CRUD 페이지를 추가한다.
기존 패턴을 따라 일관된 레이아웃과 기능을 보장한다.

---

## 시작 전 필수 확인

1. **CLAUDE.md** — 관리자 페이지 패턴, 메뉴 등록 방식
2. **기존 관리자 페이지 참조** — 패턴 확인용:
   - 기본 CRUD: `adm/morgan/reward.php` + `reward_update.php`
   - 리스트+모달: `adm/morgan/achievement.php`
   - 설정 관리: `adm/morgan/config.php` + `config_update.php`
3. **`adm/admin.menu800.php`** — 메뉴 구조 확인

---

## 관리자 페이지 구조

### 파일 구성

```
adm/morgan/
├── {기능}.php            # 목록/관리 페이지 (GET)
├── {기능}_update.php     # 처리 로직 (POST/AJAX)
├── _head.php             # 공통 헤더 (include)
└── _tail.php             # 공통 푸터 (include)
```

### 기본 템플릿 (목록 페이지)

```php
<?php
$sub_menu = '800100'; // admin.menu800.php에 등록된 메뉴 ID
include_once('./_common.php');
auth_check_menu($auth, $sub_menu, 'r'); // 읽기 권한 체크

$g5['title'] = '기능명 관리';
include_once(G5_ADMIN_PATH.'/_head.php');
include_once(G5_ADMIN_PATH.'/morgan/_head.php');

// 전역 변수
global $g5;
include_once(G5_PATH.'/plugin/morgan/morgan.php');
?>

<!-- 페이지 헤더 -->
<div class="mg-admin-header">
    <h2>기능명 관리</h2>
    <div class="mg-admin-actions">
        <button onclick="openCreateModal()" class="btn btn-primary">+ 새로 등록</button>
    </div>
</div>

<!-- 목록 테이블 -->
<div class="tbl_head01 mg-admin-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>이름</th>
                <th>상태</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM {$g5['mg_xxx_table']} ORDER BY xxx_id DESC";
            $result = sql_query($sql);
            while ($row = sql_fetch_array($result)) {
            ?>
            <tr>
                <td><?php echo $row['xxx_id']; ?></td>
                <td><?php echo htmlspecialchars($row['xxx_name']); ?></td>
                <td><!-- 상태 뱃지 --></td>
                <td>
                    <button onclick="editItem(<?php echo $row['xxx_id']; ?>)">수정</button>
                    <button onclick="deleteItem(<?php echo $row['xxx_id']; ?>)">삭제</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- 모달 (등록/수정) -->
<div id="itemModal" style="display:none;">
    <form id="itemForm" method="post" action="./xxx_update.php">
        <input type="hidden" name="token" value="">
        <input type="hidden" name="mode" value="create">
        <input type="hidden" name="xxx_id" value="">
        <!-- 폼 필드들 -->
        <div class="mg-modal-footer">
            <button type="button" onclick="closeModal()">취소</button>
            <button type="submit" class="btn btn-primary">저장</button>
        </div>
    </form>
</div>

<?php
include_once(G5_ADMIN_PATH.'/morgan/_tail.php');
include_once(G5_ADMIN_PATH.'/_tail.php');
?>
```

### 기본 템플릿 (처리 페이지)

```php
<?php
$sub_menu = '800100';
include_once('./_common.php');
auth_check_menu($auth, $sub_menu, 'w'); // 쓰기 권한 체크

global $g5;
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$xxx_id = isset($_POST['xxx_id']) ? (int)$_POST['xxx_id'] : 0;

// AJAX 요청 감지
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

switch ($mode) {
    case 'create':
        // INSERT 로직
        $name = trim($_POST['xxx_name']);
        if (!$name) {
            $error = '이름을 입력하세요.';
            break;
        }
        sql_query("INSERT INTO {$g5['mg_xxx_table']}
            SET xxx_name = '".sql_real_escape_string($name)."',
                xxx_created = NOW()");
        $success = '등록되었습니다.';
        break;

    case 'update':
        // UPDATE 로직
        sql_query("UPDATE {$g5['mg_xxx_table']}
            SET xxx_name = '".sql_real_escape_string($name)."'
            WHERE xxx_id = '{$xxx_id}'");
        $success = '수정되었습니다.';
        break;

    case 'delete':
        // DELETE 로직
        sql_query("DELETE FROM {$g5['mg_xxx_table']}
            WHERE xxx_id = '{$xxx_id}'");
        $success = '삭제되었습니다.';
        break;
}

// 응답
if ($is_ajax) {
    header('Content-Type: application/json');
    if (isset($error)) {
        echo json_encode(['success' => false, 'message' => $error]);
    } else {
        echo json_encode(['success' => true, 'message' => $success]);
    }
    exit;
} else {
    if (isset($error)) {
        alert($error);
    } else {
        goto_url('./xxx.php');
    }
}
```

---

## 메뉴 등록

### `adm/admin.menu800.php`

```php
// 배열 형식: [ID, name, URL, permission_key, group_name]
$menu['800100'] = array('800100', '기능명', './morgan/xxx.php', 'morgan_xxx', 'Morgan');
```

- **ID**: 6자리 숫자 (800xxx 범위)
- **permission_key**: `morgan_` 접두사 + 기능명
- **group_name**: `'Morgan'` (고정)

---

## 관리자 스타일 참조

### 레이아웃 클래스

| 클래스 | 용도 |
|--------|------|
| `mg-admin-header` | 페이지 상단 (제목 + 버튼) |
| `mg-admin-table` | 테이블 래퍼 |
| `mg-admin-form` | 폼 래퍼 |
| `mg-admin-modal` | 모달 |
| `tbl_head01` | 그누보드 기본 테이블 스타일 |

### 탭 UI 패턴

```php
// URL 파라미터로 탭 전환
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'list';
?>
<div class="mg-admin-tabs">
    <a href="?tab=list" class="<?php echo $tab === 'list' ? 'active' : ''; ?>">목록</a>
    <a href="?tab=settings" class="<?php echo $tab === 'settings' ? 'active' : ''; ?>">설정</a>
</div>

<?php if ($tab === 'list') { ?>
    <!-- 목록 내용 -->
<?php } elseif ($tab === 'settings') { ?>
    <!-- 설정 내용 -->
<?php } ?>
```

---

## SQL 보안 주의

```php
// 반드시 이스케이프 처리
$safe = sql_real_escape_string($_POST['value']);

// 숫자는 캐스팅
$id = (int)$_POST['id'];

// XSS 방지
echo htmlspecialchars($row['name']);
```

---

## 체크리스트

- [ ] `adm/morgan/{기능}.php` 생성
- [ ] `adm/morgan/{기능}_update.php` 생성
- [ ] `adm/admin.menu800.php`에 메뉴 등록
- [ ] `$sub_menu` → `auth_check_menu()` → `_head.php` → content → `_tail.php` 패턴 준수
- [ ] SQL injection 방지 (escape / int cast)
- [ ] XSS 방지 (htmlspecialchars)
- [ ] PHP syntax check 통과
- [ ] 사이드바에서 메뉴 접근 확인
