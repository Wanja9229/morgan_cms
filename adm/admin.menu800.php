<?php
/**
 * Morgan Edition 관리자 메뉴
 */

$mg_admin_url = G5_ADMIN_URL . '/morgan';

$menu["menu800"] = array(
    array('800000', 'Morgan Edition', $mg_admin_url . '/config.php', 'morgan'),
    // 설정
    array('800100', '기본 설정', $mg_admin_url . '/config.php', 'mg_config'),
    array('800150', '메인 페이지 빌더', $mg_admin_url . '/main_builder.php', 'mg_main_builder'),
    array('800180', '게시판 관리', $mg_admin_url . '/board_list.php', 'mg_board'),
    // 캐릭터
    array('800200', '캐릭터 관리', $mg_admin_url . '/character_list.php', 'mg_character'),
    array('800300', '프로필 필드 관리', $mg_admin_url . '/profile_field.php', 'mg_profile'),
    array('800400', '진영/클래스 관리', $mg_admin_url . '/side_class.php', 'mg_side_class'),
    // 활동
    array('800500', '출석 관리', $mg_admin_url . '/attendance.php', 'mg_attendance'),
    array('800550', '포인트 관리', $mg_admin_url . '/point_manage.php', 'mg_point'),
    array('800600', '알림 관리', $mg_admin_url . '/notification.php', 'mg_notification'),
    // 역극
    array('800650', '역극 관리', $mg_admin_url . '/rp_list.php', 'mg_rp'),
    // 상점
    array('800700', '상점 관리', $mg_admin_url . '/shop_item_list.php', 'mg_shop'),
    array('800900', '구매/선물 내역', $mg_admin_url . '/shop_log.php', 'mg_shop_log'),
    // 이모티콘
    array('800950', '이모티콘 관리', $mg_admin_url . '/emoticon_list.php', 'mg_emoticon'),
    // 개척
    array('801000', '시설 관리', $mg_admin_url . '/pioneer_facility.php', 'mg_pioneer'),
    array('801100', '재료 관리', $mg_admin_url . '/pioneer_material.php', 'mg_pioneer_material'),
);
