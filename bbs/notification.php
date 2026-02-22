<?php
/**
 * Morgan Edition - 알림 목록 페이지
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = 20;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all, unread
$unread_only = ($filter === 'unread');

$notifications = mg_get_notifications($member['mb_id'], $page, $rows, $unread_only);
$unread_count = mg_get_unread_notification_count($member['mb_id']);

// 페이지 진입 시 전체 읽음 처리 (데이터 조회 후 실행하여 현재 페이지에서는 기존 상태 표시)
if ($unread_count > 0) {
    mg_mark_all_notifications_read($member['mb_id']);
}

// 알림 타입 라벨
$noti_type_labels = array(
    'comment' => '댓글',
    'reply' => '답글',
    'like' => '좋아요',
    'character_approved' => '캐릭터 승인',
    'character_rejected' => '캐릭터 반려',
    'character_unapproved' => '승인 취소',
    'character_deleted' => '캐릭터 삭제',
    'gift_received' => '선물 수신',
    'gift_accepted' => '선물 수락',
    'gift_rejected' => '선물 거절',
    'emoticon' => '이모티콘',
    'rp_reply' => 'RP 이음',
    'expedition' => '파견',
    'concierge_apply' => '의뢰 지원',
    'concierge_match' => '의뢰 매칭',
    'concierge_reward' => '의뢰 보상',
    'concierge_complete' => '의뢰 완료',
    'concierge_force_close' => '의뢰 강제종료',
    'reward' => '보상',
    'achievement' => '업적',
    'prompt_submit' => '미션 제출',
    'prompt_reward' => '미션 보상',
    'prompt_reject' => '미션 반려',
    'relation_request' => '관계 신청',
    'relation_accepted' => '관계 수락',
    'relation_rejected' => '관계 거절',
    'relation_deleted' => '관계 해제',
    'system' => '시스템',
);

$g5['title'] = '알림';
include_once(G5_THEME_PATH.'/head.php');

$skin_file = G5_THEME_PATH.'/skin/notification/list.skin.php';
include_once($skin_file);

include_once(G5_THEME_PATH.'/tail.php');
