-- 상점 카테고리 개편: si_type enum에 concierge_extra 추가
-- 1차(그룹): 꾸미기, 프로필, 인장, 이용권, 재료, 가구, 기타
-- 2차(타입): si_type 기반 서브 필터링

ALTER TABLE mg_shop_item MODIFY COLUMN si_type
    ENUM('title','badge','nick_color','nick_effect','profile_border','equip',
         'emoticon_set','emoticon_reg','furniture','material','seal_bg',
         'seal_frame','seal_hover','profile_skin','profile_bg','char_slot',
         'concierge_extra','etc')
    NOT NULL DEFAULT 'etc' COMMENT '타입';
