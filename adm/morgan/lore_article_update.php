<?php
/**
 * Morgan Edition - 위키 문서 저장 처리
 */

$sub_menu = "800160";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    goto_url('./lore_wiki.php?tab=articles');
}

// === POST 데이터 수집 ===
$la_id = isset($_POST['la_id']) ? (int)$_POST['la_id'] : 0;
$lc_id = (int)$_POST['lc_id'];
$la_title = sql_real_escape_string(trim($_POST['la_title']));
$la_subtitle = sql_real_escape_string(trim($_POST['la_subtitle']));
$la_summary = sql_real_escape_string(trim($_POST['la_summary']));
$la_thumbnail = sql_real_escape_string(trim($_POST['la_thumbnail']));
$la_order = (int)$_POST['la_order'];
$la_use = (int)$_POST['la_use'];

// 유효성 검사
if (!$la_title) {
    alert('제목을 입력해주세요.', './lore_article_edit.php' . ($la_id ? '?la_id='.$la_id : ''));
}
if (!$lc_id) {
    alert('카테고리를 선택해주세요.', './lore_article_edit.php' . ($la_id ? '?la_id=' . $la_id : ''));
}

// === 문서 저장 ===
if ($la_id > 0) {
    // 수정: 기존 문서 확인
    $exists = sql_fetch("SELECT la_id FROM {$g5['mg_lore_article_table']} WHERE la_id = {$la_id}");
    if (!$exists['la_id']) {
        alert('문서를 찾을 수 없습니다.', './lore_wiki.php?tab=articles');
    }

    sql_query("UPDATE {$g5['mg_lore_article_table']} SET
        lc_id = {$lc_id},
        la_title = '{$la_title}',
        la_subtitle = '{$la_subtitle}',
        la_summary = '{$la_summary}',
        la_thumbnail = '{$la_thumbnail}',
        la_order = {$la_order},
        la_use = {$la_use}
        WHERE la_id = {$la_id}");
} else {
    // 신규 등록
    sql_query("INSERT INTO {$g5['mg_lore_article_table']}
        (lc_id, la_title, la_subtitle, la_summary, la_thumbnail, la_order, la_use)
        VALUES
        ({$lc_id}, '{$la_title}', '{$la_subtitle}', '{$la_summary}', '{$la_thumbnail}', {$la_order}, {$la_use})");

    $la_id = sql_insert_id();
}

// === 섹션 처리 ===
// 기존 섹션 삭제 (이미지 파일은 유지 - 새 섹션에서 재사용될 수 있음)
sql_query("DELETE FROM {$g5['mg_lore_section_table']} WHERE la_id = {$la_id}");

// 새 섹션 삽입
$sections = isset($_POST['sections']) ? $_POST['sections'] : array();
if (is_array($sections)) {
    foreach ($sections as $idx => $sec) {
        $ls_name = sql_real_escape_string(trim($sec['name']));
        $ls_type = (isset($sec['type']) && $sec['type'] == 'image') ? 'image' : 'text';
        $ls_content = sql_real_escape_string(trim($sec['content']));
        $ls_order = (int)$sec['order'];

        // 이미지 URL 처리
        $ls_image = '';
        if ($ls_type == 'image') {
            // 새로 업로드된 이미지 URL 우선, 없으면 기존 이미지 유지
            if (!empty($sec['image'])) {
                $ls_image = trim($sec['image']);
            } elseif (!empty($sec['existing_image'])) {
                $ls_image = trim($sec['existing_image']);
            }
        }
        $ls_image = sql_real_escape_string($ls_image);

        $ls_image_caption = sql_real_escape_string(trim($sec['image_caption']));

        // 섹션명이 비어있으면 기본값
        if (!$ls_name) {
            $ls_name = sql_real_escape_string('섹션 ' . ($idx + 1));
        }

        sql_query("INSERT INTO {$g5['mg_lore_section_table']}
            (la_id, ls_name, ls_type, ls_content, ls_image, ls_image_caption, ls_order)
            VALUES
            ({$la_id}, '{$ls_name}', '{$ls_type}', '{$ls_content}', '{$ls_image}', '{$ls_image_caption}', {$ls_order})");
    }
}

goto_url('./lore_wiki.php?tab=articles');
