-- 프로필 스킨 상점 아이템 타입 추가
ALTER TABLE mg_shop_item MODIFY COLUMN si_type
  ENUM('title','badge','nick_color','nick_effect','profile_border','equip',
       'emoticon_set','emoticon_reg','furniture','material',
       'seal_bg','seal_frame','profile_skin','etc') NOT NULL DEFAULT 'etc';
