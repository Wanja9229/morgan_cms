-- 인장 시스템 개편: seal_bg_color 컬럼 + seal_hover ENUM 추가 + 상점 아이템 시드

-- 1. mg_seal 테이블에 배경색 컬럼 추가
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'mg_seal' AND column_name = 'seal_bg_color');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_seal ADD COLUMN seal_bg_color VARCHAR(7) DEFAULT \'\' AFTER seal_text_color', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. si_type ENUM에 seal_hover 추가
ALTER TABLE mg_shop_item MODIFY COLUMN si_type ENUM('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame','seal_hover','profile_skin','profile_bg','char_slot','etc') NOT NULL DEFAULT 'etc';

-- 3. seal_frame 아이템 5종
INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '골드 프레임', 'seal_frame', 300, '우아한 이중 금색 테두리', 1, 0, '{"border_style":"double","border_width":"3px","border_color":"#d4a843","border_radius":"12px"}', 1, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_frame' AND si_name='골드 프레임');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '실버 프레임', 'seal_frame', 250, '깔끔한 은색 테두리', 1, 0, '{"border_style":"solid","border_width":"2px","border_color":"#c0c0c0","border_radius":"12px"}', 2, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_frame' AND si_name='실버 프레임');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '네온 프레임', 'seal_frame', 350, '형광 초록 네온 테두리', 1, 0, '{"border_style":"solid","border_width":"2px","border_color":"#00ff88","border_radius":"16px"}', 3, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_frame' AND si_name='네온 프레임');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '점선 프레임', 'seal_frame', 200, '앰버 색상 점선 테두리', 1, 0, '{"border_style":"dashed","border_width":"2px","border_color":"#f59e0b","border_radius":"12px"}', 4, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_frame' AND si_name='점선 프레임');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '그림자 프레임', 'seal_frame', 350, '은은한 그림자 효과 테두리', 1, 0, '{"border_style":"solid","border_width":"1px","border_color":"#3f4147","border_radius":"12px","box_shadow":"0 4px 20px rgba(0,0,0,0.4)"}', 5, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_frame' AND si_name='그림자 프레임');

-- 4. seal_bg 아이템 5종 (Vanta.js 효과 재활용)
INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '인장 배경: 안개', 'seal_bg', 250, '인장 배경에 안개 효과 적용', 1, 0, '{"bg_id":"fog","bg_color":"#1a1a2e"}', 1, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_bg' AND si_name='인장 배경: 안개');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '인장 배경: 물결', 'seal_bg', 200, '인장 배경에 물결 효과 적용', 1, 0, '{"bg_id":"waves","bg_color":"#0a192f"}', 2, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_bg' AND si_name='인장 배경: 물결');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '인장 배경: 셀', 'seal_bg', 180, '인장 배경에 유기적 셀 효과 적용', 1, 0, '{"bg_id":"cells","bg_color":"#0d1117"}', 3, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_bg' AND si_name='인장 배경: 셀');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '인장 배경: 네트워크', 'seal_bg', 220, '인장 배경에 네트워크 효과 적용', 1, 0, '{"bg_id":"net","bg_color":"#111827"}', 4, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_bg' AND si_name='인장 배경: 네트워크');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '인장 배경: 파문', 'seal_bg', 200, '인장 배경에 파문 효과 적용', 1, 0, '{"bg_id":"ripple","bg_color":"#1e1e2e"}', 5, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_bg' AND si_name='인장 배경: 파문');

-- 5. seal_hover 아이템 4종
INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '앰버 글로우', 'seal_hover', 200, '인장 요소에 앰버 색 발광 효과', 1, 0, '{"hover_id":"glow_amber","css":"box-shadow: 0 0 12px rgba(245,159,10,0.5); transition: all 0.2s ease;"}', 1, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_hover' AND si_name='앰버 글로우');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '블루 글로우', 'seal_hover', 200, '인장 요소에 파란색 발광 효과', 1, 0, '{"hover_id":"glow_blue","css":"box-shadow: 0 0 12px rgba(59,130,246,0.5); transition: all 0.2s ease;"}', 2, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_hover' AND si_name='블루 글로우');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '스케일업', 'seal_hover', 150, '인장 요소에 확대 효과', 1, 0, '{"hover_id":"scale","css":"transform: scale(1.05); transition: all 0.2s ease;"}', 3, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_hover' AND si_name='스케일업');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '그림자 드랍', 'seal_hover', 150, '인장 요소에 그림자 효과', 1, 0, '{"hover_id":"shadow","css":"box-shadow: 0 4px 12px rgba(0,0,0,0.5); transition: all 0.2s ease;"}', 4, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='seal_hover' AND si_name='그림자 드랍');
