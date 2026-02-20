<?php
/**
 * Morgan Edition 관리자 메뉴
 *
 * 배열 구조: [ID, 이름, URL, 권한키, 그룹명(옵션)]
 * 그룹명이 있으면 사이드바에서 섹션 구분자로 사용
 */

$mg_admin_url = G5_ADMIN_URL . '/morgan';

$menu["menu800"] = array(
    array('800000', 'Morgan Edition', $mg_admin_url . '/dashboard.php', 'morgan'),
    // 설정
    array('800050', '대시보드', $mg_admin_url . '/dashboard.php', 'mg_dashboard', '설정'),
    array('800100', '기본 설정', $mg_admin_url . '/config.php', 'mg_config'),
    array('800060', '스태프 관리', $mg_admin_url . '/staff.php', 'mg_staff'),
    array('800150', '디자인 관리', $mg_admin_url . '/design.php', 'mg_main_builder'),
    // 세계관
    array('800160', '위키 카테고리', $mg_admin_url . '/lore_category.php', 'mg_lore', '세계관'),
    array('800170', '위키 문서', $mg_admin_url . '/lore_article.php', 'mg_lore'),
    array('800175', '타임라인', $mg_admin_url . '/lore_timeline.php', 'mg_lore'),
    array('800178', '지도', $mg_admin_url . '/lore_map.php', 'mg_lore'),
    // 회원 / 캐릭터
    array('800190', '회원 관리', $mg_admin_url . '/member_list.php', 'mg_member', '회원 / 캐릭터'),
    array('800200', '캐릭터 관리', $mg_admin_url . '/character_list.php', 'mg_character'),
    array('800300', '프로필 필드 관리', $mg_admin_url . '/profile_field.php', 'mg_profile'),
    array('800400', '진영/클래스 관리', $mg_admin_url . '/side_class.php', 'mg_side_class'),
    array('801700', '관계 관리', $mg_admin_url . '/relation.php', 'mg_relation'),
    array('801300', '인장 관리', $mg_admin_url . '/seal.php', 'mg_seal'),
    // 활동
    array('800500', '출석 관리', $mg_admin_url . '/attendance.php', 'mg_attendance', '활동'),
    array('800600', '알림 관리', $mg_admin_url . '/notification.php', 'mg_notification'),
    array('801200', '업적 관리', $mg_admin_url . '/achievement.php', 'mg_achievement'),
    // 콘텐츠
    array('800650', '역극 관리', $mg_admin_url . '/rp_list.php', 'mg_rp', '콘텐츠'),
    array('800180', '게시판 관리', $mg_admin_url . '/board_list.php', 'mg_board'),
    array('801500', '미션 관리', $mg_admin_url . '/prompt.php', 'mg_prompt'),
    array('801800', '의뢰 관리', $mg_admin_url . '/concierge.php', 'mg_concierge'),
    // 재화 / 상점
    array('800550', '포인트 관리', $mg_admin_url . '/point_manage.php', 'mg_point', '재화 / 상점'),
    array('800570', '보상 관리', $mg_admin_url . '/reward.php', 'mg_reward'),
    array('800700', '상점 관리', $mg_admin_url . '/shop_item_list.php', 'mg_shop'),
    array('800900', '구매/선물 내역', $mg_admin_url . '/shop_log.php', 'mg_shop_log'),
    array('800950', '이모티콘 관리', $mg_admin_url . '/emoticon_list.php', 'mg_emoticon'),
    // 개척
    array('801000', '시설 관리', $mg_admin_url . '/pioneer_facility.php', 'mg_pioneer', '개척'),
    array('801100', '재료 관리', $mg_admin_url . '/pioneer_material.php', 'mg_pioneer_material'),
    array('801110', '파견지 관리', $mg_admin_url . '/expedition_area.php', 'mg_expedition'),
    array('801120', '파견 로그', $mg_admin_url . '/expedition_log.php', 'mg_expedition_log'),
);
