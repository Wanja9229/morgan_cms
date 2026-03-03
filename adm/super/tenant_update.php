<?php
/**
 * Morgan Super Admin — 테넌트 CRUD 처리
 */

include_once('./_common.php');
sa_check_auth();
sa_verify_token();

$action = $_POST['action'] ?? '';

// TenantManager 로드
require_once(G5_PLUGIN_PATH . '/morgan/tenant/TenantManager.php');
$tm = new TenantManager($_SA_LINK);

switch ($action) {
    // ========================================
    // 테넌트 생성
    // ========================================
    case 'create':
        $subdomain  = trim($_POST['subdomain'] ?? '');
        $name       = trim($_POST['name'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminId    = trim($_POST['admin_id'] ?? 'admin');
        $plan       = $_POST['plan'] ?? 'free';

        if (!$subdomain || !$name || !$adminEmail) {
            sa_alert('필수 항목을 모두 입력해 주세요.');
        }

        $result = $tm->provision($subdomain, $name, $adminEmail, $adminId, $plan);

        if ($result === false) {
            $errors = $tm->getErrors();
            sa_alert(implode("\n", $errors));
        }

        // 성공 — 결과 페이지로 리다이렉트
        $result['subdomain'] = $subdomain;
        $_SESSION['sa_provision_result'] = $result;
        $_SESSION['sa_provision_log'] = $tm->getLog();
        header('Location: ' . sa_url('tenant_created.php'));
        exit;

    // ========================================
    // 테넌트 수정
    // ========================================
    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) sa_alert('잘못된 요청');

        $name          = trim($_POST['name'] ?? '');
        $adminEmail    = trim($_POST['admin_email'] ?? '');
        $plan          = $_POST['plan'] ?? 'free';
        $maxStorageMb  = (int)($_POST['max_storage_mb'] ?? 1024);
        $maxMembers    = (int)($_POST['max_members'] ?? 100);

        if (!$name || !$adminEmail) {
            sa_alert('필수 항목을 입력해 주세요.');
        }

        $sql = sprintf(
            "UPDATE tenants SET name='%s', admin_email='%s', plan='%s', max_storage_mb=%d, max_members=%d WHERE id=%d",
            sa_escape($name),
            sa_escape($adminEmail),
            sa_escape($plan),
            $maxStorageMb,
            $maxMembers,
            $id
        );

        if (sa_query($sql)) {
            sa_redirect(sa_url('tenant_form.php?id=' . $id), '저장되었습니다.');
        } else {
            sa_alert('저장 실패');
        }
        break;

    // ========================================
    // 테넌트 정지
    // ========================================
    case 'suspend':
        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '관리자에 의한 정지');

        if ($tm->suspend($id, $reason)) {
            sa_redirect(sa_url('tenants.php'), '테넌트가 정지되었습니다.');
        } else {
            sa_alert(implode("\n", $tm->getErrors()));
        }
        break;

    // ========================================
    // 테넌트 활성화
    // ========================================
    case 'activate':
        $id = (int)($_POST['id'] ?? 0);

        if ($tm->activate($id)) {
            sa_redirect(sa_url('tenants.php'), '테넌트가 활성화되었습니다.');
        } else {
            sa_alert(implode("\n", $tm->getErrors()));
        }
        break;

    // ========================================
    // 테넌트 삭제 (소프트)
    // ========================================
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);

        if ($tm->softDelete($id)) {
            sa_redirect(sa_url('tenants.php'), '테넌트가 삭제되었습니다.');
        } else {
            sa_alert(implode("\n", $tm->getErrors()));
        }
        break;

    default:
        sa_alert('잘못된 액션');
}
