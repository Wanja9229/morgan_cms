<?php
/**
 * Morgan Edition - 관계도 그래프 데이터 API
 * JSON으로 nodes + edges 반환
 */
include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;
$depth = isset($_GET['depth']) ? (int)$_GET['depth'] : 2;
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$faction_id = isset($_GET['faction_id']) ? (int)$_GET['faction_id'] : 0;

$data = mg_get_relation_graph($ch_id, $depth, $category, $faction_id);

// 저장된 노드 배치 좌표
if ($ch_id) {
    $char = sql_fetch("SELECT ch_graph_layout FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}");
    $data['layout'] = $char['ch_graph_layout'] ? json_decode($char['ch_graph_layout'], true) : null;
}

echo json_encode($data);
