<?php
/**
 * Morgan Edition - 이모티콘 API (AJAX)
 */

require_once __DIR__.'/../common.php';
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    // 내 보유 이모티콘 (피커용)
    case 'my_emoticons':
        if (!$is_member) {
            echo json_encode(array('sets' => array()));
            exit;
        }

        $my_sets = mg_get_my_emoticons($member['mb_id']);
        $result = array('sets' => array());

        foreach ($my_sets as $es_id => $data) {
            $set = $data['set'];
            $emoticons = array();
            foreach ($data['emoticons'] as $em) {
                $emoticons[] = array(
                    'id' => (int)$em['em_id'],
                    'code' => $em['em_code'],
                    'image' => $em['em_image'],
                );
            }
            $result['sets'][] = array(
                'id' => (int)$set['es_id'],
                'name' => $set['es_name'],
                'preview' => $set['es_preview'] ?: '',
                'emoticons' => $emoticons,
            );
        }

        echo json_encode($result);
        break;

    // 이모티콘 셋 목록 (마켓용)
    case 'shop_list':
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
        $rows = 12;

        $sets = mg_get_emoticon_sets('approved', $page, $rows);

        // 정렬
        if ($sort === 'popular') {
            usort($sets['items'], function($a, $b) {
                return (int)$b['es_sales_count'] - (int)$a['es_sales_count'];
            });
        }

        // 보유 여부 체크
        $items = array();
        foreach ($sets['items'] as $set) {
            $owned = $is_member ? mg_owns_emoticon_set($member['mb_id'], $set['es_id']) : false;
            $items[] = array(
                'id' => (int)$set['es_id'],
                'name' => $set['es_name'],
                'desc' => $set['es_desc'],
                'preview' => $set['es_preview'] ?: '',
                'price' => (int)$set['es_price'],
                'count' => (int)$set['em_count'],
                'sales' => (int)$set['es_sales_count'],
                'creator' => $set['es_creator_id'] ?: '',
                'owned' => $owned,
            );
        }

        echo json_encode(array(
            'items' => $items,
            'total' => $sets['total'],
            'total_page' => $sets['total_page'],
            'page' => $page,
        ));
        break;

    // 셋 상세 (미리보기용)
    case 'set_detail':
        $es_id = isset($_GET['es_id']) ? (int)$_GET['es_id'] : 0;
        $set = mg_get_emoticon_set($es_id);

        if (!$set || $set['es_status'] !== 'approved') {
            echo json_encode(array('error' => '존재하지 않는 이모티콘 셋입니다.'));
            exit;
        }

        $emoticons = mg_get_emoticons($es_id);
        $em_list = array();
        foreach ($emoticons as $em) {
            $em_list[] = array(
                'code' => $em['em_code'],
                'image' => $em['em_image'],
            );
        }

        $owned = $is_member ? mg_owns_emoticon_set($member['mb_id'], $es_id) : false;

        echo json_encode(array(
            'id' => (int)$set['es_id'],
            'name' => $set['es_name'],
            'desc' => $set['es_desc'],
            'preview' => $set['es_preview'] ?: '',
            'price' => (int)$set['es_price'],
            'creator' => $set['es_creator_id'] ?: '',
            'sales' => (int)$set['es_sales_count'],
            'owned' => $owned,
            'emoticons' => $em_list,
        ));
        break;

    default:
        echo json_encode(array('error' => 'Invalid action'));
}
