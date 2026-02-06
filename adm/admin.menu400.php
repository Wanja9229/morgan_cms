<?php
/**
 * Morgan Edition - Admin Menu
 */

$menu['menu400'] = array(
    array('400000', 'Morgan', '' . G5_ADMIN_URL . '/morgan/config.php', 'morgan'),
    // 캐릭터 관리
    array('400100', '캐릭터 관리', '' . G5_ADMIN_URL . '/morgan/character_list.php', 'mg_character'),
    array('400200', '세력/종족 관리', '' . G5_ADMIN_URL . '/morgan/side_class.php', 'mg_side'),
    array('400300', '프로필 양식', '' . G5_ADMIN_URL . '/morgan/profile_field.php', 'mg_profile'),
    // 설정
    array('400400', 'Morgan 설정', '' . G5_ADMIN_URL . '/morgan/config.php', 'mg_config'),
    array('400500', '메인 페이지 빌더', '' . G5_ADMIN_URL . '/morgan/main_builder.php', 'mg_main_builder'),
    // 출석/알림
    array('400600', '출석 통계', '' . G5_ADMIN_URL . '/morgan/attendance.php', 'mg_attendance'),
    array('400700', '알림 관리', '' . G5_ADMIN_URL . '/morgan/notification.php', 'mg_notification'),
    // 상점
    array('400800', '상점 관리', '' . G5_ADMIN_URL . '/morgan/shop_item_list.php', 'mg_shop'),
    array('400900', '상점 카테고리', '' . G5_ADMIN_URL . '/morgan/shop_category.php', 'mg_shop_category'),
    array('401000', '구매/선물 내역', '' . G5_ADMIN_URL . '/morgan/shop_log.php', 'mg_shop_log'),
);
